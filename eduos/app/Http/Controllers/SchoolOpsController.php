<?php

namespace App\Http\Controllers;

use App\Modules\Registry\Models\Enrolment;
use App\Modules\Registry\Models\School;
use App\Modules\SchoolOps\Models\Assignment;
use App\Modules\SchoolOps\Models\Campaign;
use App\Modules\SchoolOps\Models\CampaignSubmission;
use App\Modules\SchoolOps\Models\SchoolStock;
use Illuminate\Http\Request;

class SchoolOpsController extends Controller
{
    /** Assignment: class-level by default; student-level when a learner is selected (FR-NTR-SM-02 upgrade). */
    public function assign(Request $request, School $school)
    {
        $data = $request->validate([
            'textbook_title_id' => 'required|exists:textbook_titles,id',
            'class_level' => 'required|string|max:4',
            'quantity' => 'required|integer|min:1',
            'student_id' => 'nullable|exists:students,id',
        ]);
        if (! empty($data['student_id'])) {
            $student = \App\Modules\Registry\Models\Student::find($data['student_id']);
            if ($student->school_id !== $school->id) {
                return back()->with('flash_error', 'Learner belongs to a different school (row-level scoping).');
            }
            $data['quantity'] = 1;                      // one book per named learner
            $data['class_level'] = $student->class_level;
        }

        $onHand = SchoolStock::where('school_id', $school->id)
            ->where('textbook_title_id', $data['textbook_title_id'])->sum('quantity');
        $assigned = Assignment::where('school_id', $school->id)
            ->where('textbook_title_id', $data['textbook_title_id'])
            ->where('status', 'ASSIGNED')->sum('quantity');
        if ($onHand - $assigned < $data['quantity']) {
            return back()->with('flash_error', 'Insufficient unassigned stock: '.($onHand - $assigned).' available.');
        }

        Assignment::create($data + [
            'school_id' => $school->id,
            'academic_year' => '2025/2026',
            'actor' => auth()->user()->name,
        ]);
        \App\Modules\Catalogue\Models\Copy::advance($data['textbook_title_id'], 'AT_SCHOOL', 'ASSIGNED', $data['quantity'], $school->id);

        return back()->with('flash', "Assigned {$data['quantity']} books to {$data['class_level']}.");
    }

    /** Return with mandatory condition (FR-NTR-11). */
    public function returnBooks(Request $request, Assignment $assignment)
    {
        $data = $request->validate(['condition_on_return' => 'required|in:GOOD,FAIR,POOR,UNUSABLE']);
        if ($assignment->status !== 'ASSIGNED') {
            return back()->with('flash_error', 'Already returned.');
        }
        $assignment->update(['status' => 'RETURNED'] + $data);

        // Per-copy lifecycle on return: good → AT_SCHOOL, poor → UNDER_REPAIR, unusable → retired path
        $copyTo = match ($data['condition_on_return']) {
            'UNUSABLE' => 'RETIRED',
            'POOR' => 'UNDER_REPAIR',
            default => 'AT_SCHOOL',
        };
        // ASSIGNED copies are not school-filtered on 'from' (they left AT_SCHOOL); advance by title
        \App\Modules\Catalogue\Models\Copy::advance($assignment->textbook_title_id, 'ASSIGNED', $copyTo, $assignment->quantity, $assignment->school_id);

        if (in_array($data['condition_on_return'], ['POOR', 'UNUSABLE'])) {
            SchoolStock::where('school_id', $assignment->school_id)
                ->where('textbook_title_id', $assignment->textbook_title_id)
                ->first()?->update(['condition' => $data['condition_on_return'] === 'UNUSABLE' ? 'POOR' : 'FAIR']);
        }

        return back()->with('flash', "Return recorded ({$assignment->quantity} books, condition {$data['condition_on_return']}).");
    }

    public function campaigns()
    {
        return view('campaigns.index', [
            'campaigns' => Campaign::withCount('submissions')->orderByDesc('opened_at')->get(),
            'schoolsTotal' => School::where('status', 'OPERATIONAL')->count(),
        ]);
    }

    public function openCampaign(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:160']);
        $c = Campaign::create($data + ['academic_year' => '2025/2026', 'status' => 'OPEN', 'opened_at' => now()]);

        return redirect()->route('campaigns.show', $c)->with('flash', "Campaign \"{$c->name}\" opened.");
    }

    public function showCampaign(Campaign $campaign)
    {
        $subs = $campaign->submissions()->with(['school', 'title'])->get();
        $schools = School::where('status', 'OPERATIONAL')->orderBy('name_official')->get();
        $titles = \App\Modules\Catalogue\Models\TextbookTitle::where('status', 'APPROVED')->get();
        $accounted = $subs->sum('counted');
        $expected = $subs->sum('expected');

        return view('campaigns.show', compact('campaign', 'subs', 'schools', 'titles', 'accounted', 'expected'));
    }

    /** School submits counted stock; expected auto-derived from school stock ledger (FR-NTR-12). */
    public function submitCount(Request $request, Campaign $campaign)
    {
        if ($campaign->status !== 'OPEN') {
            return back()->with('flash_error', 'Campaign window is closed.');
        }
        $data = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'textbook_title_id' => 'required|exists:textbook_titles,id',
            'counted' => 'required|integer|min:0',
        ]);
        $expected = SchoolStock::where('school_id', $data['school_id'])
            ->where('textbook_title_id', $data['textbook_title_id'])->sum('quantity');

        CampaignSubmission::updateOrCreate(
            ['campaign_id' => $campaign->id, 'school_id' => $data['school_id'], 'textbook_title_id' => $data['textbook_title_id']],
            ['expected' => $expected, 'counted' => $data['counted'], 'submitted_by' => auth()->user()->name]
        );

        return back()->with('flash', "Count recorded: {$data['counted']} vs {$expected} expected.");
    }

    public function closeCampaign(Campaign $campaign)
    {
        $campaign->update(['status' => 'CLOSED', 'closed_at' => now()]);
        $missing = $campaign->submissions->sum(fn ($s) => max(0, $s->expected - $s->counted));

        // Wire reconciliation to per-copy lifecycle: unaccounted copies at a school → LOST
        foreach ($campaign->submissions as $sub) {
            $gap = $sub->expected - $sub->counted;
            if ($gap > 0) {
                \App\Modules\Catalogue\Models\Copy::advance($sub->textbook_title_id, 'AT_SCHOOL', 'LOST', $gap, $sub->school_id);
            }
        }
        \App\Modules\Platform\Models\Alert::create([
            'severity' => $missing > 0 ? 'WARNING' : 'INFO',
            'title' => "Campaign \"{$campaign->name}\" closed",
            'message' => "Reconciliation: {$campaign->submissions->sum('counted')} counted vs {$campaign->submissions->sum('expected')} expected; {$missing} unaccounted.",
            'link' => "/campaigns/{$campaign->id}",
        ]);

        return back()->with('flash', 'Campaign closed; reconciliation alert issued.');
    }

    /** Enrolment return: submit (school) then validate (division/admin) — FR-NSR-03. */
    public function submitEnrolment(Request $request, School $school)
    {
        $data = $request->validate([
            'class_level' => 'required|string|max:4',
            'boys' => 'required|integer|min:0',
            'girls' => 'required|integer|min:0',
        ]);
        Enrolment::updateOrCreate(
            ['school_id' => $school->id, 'academic_year' => '2026/2027', 'class_level' => $data['class_level']],
            $data + ['validation_status' => 'SUBMITTED']
        );

        return back()->with('flash', "Enrolment return for {$data['class_level']} submitted for validation.");
    }

    public function validateEnrolment(Enrolment $enrolment)
    {
        $enrolment->update(['validation_status' => 'VALIDATED']);

        return back()->with('flash', 'Enrolment return validated.');
    }
}
