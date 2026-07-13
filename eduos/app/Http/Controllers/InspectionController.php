<?php

namespace App\Http\Controllers;

use App\Modules\Platform\Models\Alert;
use App\Modules\Registry\Models\School;
use App\Modules\SchoolOps\Models\Inspection;
use App\Modules\SchoolOps\Models\SchoolStock;
use Illuminate\Http\Request;

/** Inspection & accountability (Problems 61–70, demo depth): spot checks vs ledger. */
class InspectionController extends Controller
{
    public function index()
    {
        return view('inspections.index', [
            'inspections' => Inspection::with(['school', 'title'])->orderByDesc('inspected_on')->paginate(15),
            'schools' => School::where('status', 'OPERATIONAL')->orderBy('name_official')->get(),
            'titles' => \App\Modules\Catalogue\Models\TextbookTitle::where('status', 'APPROVED')->get(),
            'assignments' => \App\Modules\SchoolOps\Models\InspectionAssignment::with(['school', 'inspector'])
                ->orderBy('status')->orderBy('due_on')->limit(30)->get(),
            'inspectors' => \App\Models\User::whereIn('role', ['INSPECTOR', 'ADMIN'])->where('is_active', 1)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'textbook_title_id' => 'required|exists:textbook_titles,id',
            'counted_qty' => 'required|integer|min:0',
            'findings' => 'nullable|string|max:500',
            'evidence' => 'nullable|image|max:4096',
        ]);
        // VER SOD (spec L12861): the inspector must be independent of dispatch and receipt at this school
        $recent = \App\Modules\Custody\Models\Shipment::where('destination_school_id', $data['school_id'])
            ->where('updated_at', '>=', now()->subDays(90))->pluck('id');
        $conflicted = \App\Modules\Custody\Models\CustodyEvent::whereIn('shipment_id', $recent)
            ->whereIn('event_type', ['DISPATCHED', 'RECEIVED', 'RECEIVED_PARTIAL'])
            ->where('actor', auth()->user()->name)->exists();
        if ($conflicted) {
            return back()->with('flash_error', 'Separation of duties: you dispatched or received deliveries for this school in the last 90 days — an independent inspector must verify it (VER rule).');
        }
        if ($request->hasFile('evidence')) {
            $data['evidence_path'] = $request->file('evidence')->store('evidence', 'public');
        }
        unset($data['evidence']);
        $recorded = SchoolStock::where('school_id', $data['school_id'])
            ->where('textbook_title_id', $data['textbook_title_id'])->sum('quantity');
        $variance = $data['counted_qty'] - $recorded;
        $outcome = $variance === 0 ? 'CONFORM' : (abs($variance) <= max(5, $recorded * 0.02) ? 'MINOR_FINDINGS' : 'MAJOR_FINDINGS');

        $inspection = Inspection::create($data + [
            'inspector' => auth()->user()->name,
            'inspected_on' => now()->toDateString(),
            'recorded_qty' => $recorded,
            'outcome' => $outcome,
        ]);

        $inspection->forceFill(['ver_no' => sprintf('VER-%s-%04d', now()->format('Y'), $inspection->id)])->save();

        // VER-01: recording a check completes any open assignment for this school
        \App\Modules\SchoolOps\Models\InspectionAssignment::where('school_id', $data['school_id'])
            ->where('status', 'ASSIGNED')->update(['status' => 'DONE']);

        if ($outcome === 'MAJOR_FINDINGS') {
            \App\Modules\Platform\Models\ExceptionCase::open('INSPECTION', 'HIGH',
                "Major inspection variance at {$inspection->school->name_official}", $inspection->id);
            Alert::create([
                'severity' => 'CRITICAL',
                'title' => 'Inspection: major stock variance',
                'message' => "Spot check at {$inspection->school->name_official}: counted {$data['counted_qty']} vs {$recorded} recorded (variance {$variance}).",
                'link' => '/inspections',
            ]);
        }

        return back()->with('flash', "Inspection recorded — {$outcome} (counted {$data['counted_qty']} vs {$recorded} on ledger).");
    }

    public function resolve(Request $request, Inspection $inspection)
    {
        $action = $request->validate(['corrective_action' => 'required|string|max:500'])['corrective_action'];
        $inspection->update(['corrective_action' => $action, 'resolved_at' => now()]);

        return back()->with('flash', 'Corrective action recorded; inspection closed.');
    }
    /** VER-01: supervisors assign schools to inspectors with a due date. */
    public function assign(Request $request)
    {
        $data = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'inspector_id' => 'required|exists:users,id',
            'due_on' => 'required|date',
        ]);
        \App\Modules\SchoolOps\Models\InspectionAssignment::create($data + ['assigned_by' => auth()->user()->name]);

        return back()->with('flash', 'Inspection assigned to the verification queue.');
    }

    /** VER-05: printable inspection report. */
    public function report(Inspection $inspection)
    {
        $inspection->load(['school', 'title']);

        return view('inspections.report', compact('inspection'));
    }

}
