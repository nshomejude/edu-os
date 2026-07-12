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
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'textbook_title_id' => 'required|exists:textbook_titles,id',
            'counted_qty' => 'required|integer|min:0',
            'findings' => 'nullable|string|max:500',
        ]);
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

        if ($outcome === 'MAJOR_FINDINGS') {
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
}
