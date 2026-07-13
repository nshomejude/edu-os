<?php

namespace App\Http\Controllers;

use App\Modules\Catalogue\Models\Copy;
use App\Modules\Platform\Models\Alert;
use App\Modules\Platform\Models\Setting;
use App\Modules\Registry\Models\School;
use App\Modules\SchoolOps\Models\Assignment;
use App\Modules\SchoolOps\Models\CollectionRound;
use App\Modules\SchoolOps\Models\SchoolStock;
use Illuminate\Http\Request;

/** End-of-year collection cycle: mass returns with condition regrading; closing declares losses. */
class CollectionController extends Controller
{
    public function index()
    {
        $open = CollectionRound::where('status', 'OPEN')->latest('id')->first();
        $outstanding = collect();
        if ($open) {
            $outstanding = Assignment::with('school')
                ->where('academic_year', $open->academic_year)->where('status', 'ASSIGNED')
                ->get()->groupBy('school_id')
                ->map(fn ($g) => (object) [
                    'school' => $g->first()->school,
                    'assignments' => $g->count(),
                    'books' => $g->sum('quantity'),
                ])->values();
        }

        return view('collections.index', [
            'open' => $open,
            'outstanding' => $outstanding,
            'rounds' => CollectionRound::orderByDesc('id')->limit(10)->get(),
            'schools' => School::where('status', 'OPERATIONAL')->orderBy('name_official')->get(),
            'year' => Setting::get('academic_year', '2025/2026'),
        ]);
    }

    /** Open the round for the configured academic year (one at a time). */
    public function open()
    {
        if (CollectionRound::where('status', 'OPEN')->exists()) {
            return back()->with('flash_error', 'A collection round is already open — close it first.');
        }
        $round = CollectionRound::create([
            'academic_year' => Setting::get('academic_year', '2025/2026'),
            'opened_by' => auth()->user()->name, 'opened_at' => now(),
        ]);

        return back()->with('flash', "Collection round opened for {$round->academic_year} — schools must return or account for every assigned book.");
    }

    /** Mass return for one school: every open assignment comes back at the stated condition. */
    public function bulkReturn(Request $request)
    {
        $data = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'condition_on_return' => 'required|in:GOOD,FAIR,POOR,UNUSABLE',
        ]);
        \Illuminate\Support\Facades\Gate::authorize('operate-school', School::findOrFail($data['school_id']));
        $round = CollectionRound::where('status', 'OPEN')->latest('id')->first();
        if (! $round) {
            return back()->with('flash_error', 'No collection round is open.');
        }

        $assignments = Assignment::where('school_id', $data['school_id'])
            ->where('academic_year', $round->academic_year)->where('status', 'ASSIGNED')->get();
        if ($assignments->isEmpty()) {
            return back()->with('flash_error', 'No open assignments for that school in this round.');
        }

        $copyTo = match ($data['condition_on_return']) {
            'UNUSABLE' => 'RETIRED', 'POOR' => 'UNDER_REPAIR', default => 'AT_SCHOOL',
        };
        $books = 0;
        foreach ($assignments as $a) {
            $a->update(['status' => 'RETURNED', 'condition_on_return' => $data['condition_on_return']]);
            Copy::advance($a->textbook_title_id, 'ASSIGNED', $copyTo, $a->quantity, $a->school_id);
            $books += $a->quantity;
        }
        if (in_array($data['condition_on_return'], ['POOR', 'UNUSABLE'])) {
            SchoolStock::where('school_id', $data['school_id'])
                ->update(['condition' => $data['condition_on_return'] === 'UNUSABLE' ? 'POOR' : 'FAIR']);
        }
        $round->increment('returned_count', $assignments->count());

        return back()->with('flash', "Collected {$books} books across {$assignments->count()} assignments at condition {$data['condition_on_return']}.");
    }

    /** Close the round: whatever was not returned is formally declared LOST and leaves school stock. */
    public function close(CollectionRound $round)
    {
        if ($round->status !== 'OPEN') {
            return back()->with('flash_error', 'Round already closed.');
        }
        $outstanding = Assignment::where('academic_year', $round->academic_year)->where('status', 'ASSIGNED')->get();
        $lostBooks = 0;
        foreach ($outstanding as $a) {
            $a->update(['status' => 'LOST']);
            Copy::advance($a->textbook_title_id, 'ASSIGNED', 'LOST', $a->quantity, $a->school_id);
            $stock = SchoolStock::where('school_id', $a->school_id)
                ->where('textbook_title_id', $a->textbook_title_id)->first();
            $stock?->update(['quantity' => max(0, $stock->quantity - $a->quantity)]);
            \App\Modules\SchoolOps\Models\ReplacementCharge::create([
                'school_id' => $a->school_id, 'textbook_title_id' => $a->textbook_title_id,
                'quantity' => $a->quantity, 'academic_year' => $round->academic_year,
                'amount_fcfa' => $a->quantity * (int) Setting::get('replacement_fee_fcfa', '1500'),
            ]);
            $lostBooks += $a->quantity;
        }
        $round->update(['status' => 'CLOSED', 'closed_at' => now(), 'lost_count' => $outstanding->count()]);

        if ($outstanding->isNotEmpty()) {
            Alert::create([
                'severity' => 'CRITICAL',
                'title' => "Collection round {$round->academic_year} closed with losses",
                'message' => "{$lostBooks} books across {$outstanding->count()} assignments were not returned and are now formally LOST. School stock has been written down accordingly.",
                'link' => '/collections',
            ]);
        }

        return back()->with('flash', "Round closed — {$round->returned_count} assignments returned, {$outstanding->count()} declared lost ({$lostBooks} books).");
    }
    /** Replacement-charge ledger for books lost at collection close. */
    public function charges()
    {
        $charges = \App\Modules\SchoolOps\Models\ReplacementCharge::with(['school', 'title'])->orderByDesc('id')
            ->when(in_array(auth()->user()->role, ['SCHOOL_HEAD', 'TEACHER']),
                fn ($q) => $q->where('school_id', auth()->user()->school_id ?? 0))
            ->get();

        return view('charges.index', [
            'charges' => $charges,
            'outstanding' => (int) $charges->where('status', 'OUTSTANDING')->sum('amount_fcfa'),
            'settled' => (int) $charges->where('status', 'SETTLED')->sum('amount_fcfa'),
            'fee' => (int) Setting::get('replacement_fee_fcfa', '1500'),
        ]);
    }

    public function settle(\App\Modules\SchoolOps\Models\ReplacementCharge $charge)
    {
        if ($charge->status === 'SETTLED') {
            return back()->with('flash_error', 'Already settled.');
        }
        $charge->update(['status' => 'SETTLED', 'settled_by' => auth()->user()->name, 'settled_at' => now()]);

        // Close the loop: issue replacements from free school stock, else queue a requirement (PLAN-03)
        $onHand = SchoolStock::where('school_id', $charge->school_id)
            ->where('textbook_title_id', $charge->textbook_title_id)->sum('quantity');
        $assigned = Assignment::where('school_id', $charge->school_id)
            ->where('textbook_title_id', $charge->textbook_title_id)->where('status', 'ASSIGNED')->sum('quantity');
        if ($onHand - $assigned >= $charge->quantity) {
            Assignment::create([
                'school_id' => $charge->school_id, 'textbook_title_id' => $charge->textbook_title_id,
                'class_level' => 'REPL', 'quantity' => $charge->quantity,
                'academic_year' => Setting::get('academic_year', '2025/2026'),
                'actor' => auth()->user()->name,
            ]);
            Copy::advance($charge->textbook_title_id, 'AT_SCHOOL', 'ASSIGNED', $charge->quantity, $charge->school_id);
            $followUp = "replacement of {$charge->quantity} book(s) issued from school stock";
        } else {
            $req = \App\Modules\Planning\Models\SchoolRequirement::firstOrNew([
                'school_id' => $charge->school_id, 'textbook_title_id' => $charge->textbook_title_id,
                'academic_year' => Setting::get('academic_year', '2025/2026'),
            ]);
            $req->quantity = ($req->exists ? $req->quantity : 0) + $charge->quantity;
            $req->note = 'Replacement need from settled loss charges';
            $req->submitted_by = auth()->user()->name;
            $req->status = 'SUBMITTED';
            $req->save();
            $followUp = "no free school stock — {$charge->quantity} book(s) queued as a requirement for the next campaign";
        }

        return back()->with('flash', 'Settlement recorded: '.number_format($charge->amount_fcfa).' FCFA; '.$followUp.'.');
    }

}
