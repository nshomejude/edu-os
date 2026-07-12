<?php

namespace App\Http\Controllers;

use App\Modules\Custody\Models\CustodyEvent;
use App\Modules\Custody\Models\Shipment;
use App\Modules\Custody\Models\StockRecord;
use App\Modules\Custody\Models\Warehouse;
use App\Modules\Planning\Models\Allocation;
use App\Modules\Planning\Models\DistributionCampaign;
use App\Modules\Registry\Models\Enrolment;
use App\Modules\SchoolOps\Models\SchoolStock;
use Illuminate\Http\Request;

/**
 * PLAN module (screens 21–28): distribution campaigns with the mandated
 * separation — draft → review → approval → execution creating shipments.
 */
class PlanController extends Controller
{
    public function index()
    {
        return view('plan.index', [
            'campaigns' => DistributionCampaign::withCount('allocations')->orderByDesc('id')->get(),
        ]);
    }

    /** PLAN-02: create draft; allocations auto-generated from enrolment-based demand (PLAN-04). */
    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:160']);
        $campaign = DistributionCampaign::create($data + [
            'academic_year' => '2025/2026', 'created_by' => auth()->user()->name,
        ]);

        // Enrolment-based demand: validated learners per grade minus school stock
        $titles = \App\Modules\Catalogue\Models\TextbookTitle::where('status', 'APPROVED')->get();
        $generated = 0;
        foreach ($titles as $title) {
            $learners = Enrolment::where('class_level', $title->grade_code)
                ->where('validation_status', 'VALIDATED')
                ->selectRaw('school_id, sum(boys + girls) n')->groupBy('school_id')->pluck('n', 'school_id');
            foreach ($learners as $schoolId => $need) {
                $have = SchoolStock::where('school_id', $schoolId)
                    ->where('textbook_title_id', $title->id)->sum('quantity');
                $gap = min(2000, max(0, $need - $have));   // per-line cap for demo scale
                if ($gap > 0) {
                    Allocation::create([
                        'distribution_campaign_id' => $campaign->id,
                        'school_id' => $schoolId, 'textbook_title_id' => $title->id,
                        'quantity' => $gap,
                    ]);
                    $generated++;
                }
            }
        }

        return redirect()->route('plan.show', $campaign)
            ->with('flash', "Campaign drafted with {$generated} allocation lines from enrolment-based demand.");
    }

    public function show(DistributionCampaign $campaign)
    {
        $allocations = $campaign->allocations()->with(['school.region', 'title', 'shipment'])->get();

        return view('plan.show', [
            'campaign' => $campaign,
            'allocations' => $allocations,
            'totals' => [
                'lines' => $allocations->count(),
                'books' => $allocations->sum('quantity'),
                'schools' => $allocations->pluck('school_id')->unique()->count(),
                'executed' => $allocations->whereNotNull('shipment_id')->count(),
            ],
        ]);
    }

    /** PLAN-05: adjust a line while in DRAFT/REVIEW. */
    public function updateLine(Request $request, Allocation $allocation)
    {
        if (! in_array($allocation->campaign->status, ['DRAFT', 'REVIEW'])) {
            return back()->with('flash_error', 'Lines are frozen after approval.');
        }
        $qty = $request->validate(['quantity' => 'required|integer|min:0'])['quantity'];
        $qty === 0 ? $allocation->delete() : $allocation->update(['quantity' => $qty]);

        return back()->with('flash', 'Allocation line updated.');
    }

    /** DRAFT → REVIEW → APPROVED with separation of duties: approver ≠ creator. */
    public function transition(Request $request, DistributionCampaign $campaign)
    {
        $to = $request->validate(['to' => 'required|in:REVIEW,APPROVED,CLOSED'])['to'];
        $legal = match ($campaign->status) {
            'DRAFT' => ['REVIEW'],
            'REVIEW' => ['APPROVED'],
            'EXECUTING' => ['CLOSED'],
            default => [],
        };
        if (! in_array($to, $legal)) {
            return back()->with('flash_error', "ILLEGAL_TRANSITION: {$campaign->status} → {$to}.");
        }
        if ($to === 'APPROVED') {
            if ($campaign->created_by === auth()->user()->name && auth()->user()->role !== 'ADMIN') {
                return back()->with('flash_error', 'Separation of duties: the campaign creator cannot approve it (spec §3).');
            }
            $campaign->update(['status' => 'APPROVED', 'approved_by' => auth()->user()->name, 'approved_at' => now()]);

            return back()->with('flash', 'Campaign approved — allocations are frozen and ready for execution.');
        }
        $campaign->update(['status' => $to]);

        return back()->with('flash', "Campaign → {$to}.");
    }

    /** Execution: creates CONFIRMED shipments per school from the nearest-region warehouse. */
    public function execute(DistributionCampaign $campaign)
    {
        if ($campaign->status !== 'APPROVED') {
            return back()->with('flash_error', 'Only APPROVED campaigns can execute.');
        }
        $created = 0;
        $skipped = 0;
        foreach ($campaign->allocations()->whereNull('shipment_id')->get() as $line) {
            $wh = Warehouse::where('region_id', $line->school->region_id)->first()
                ?? Warehouse::where('tier', 'NATIONAL')->first();
            $available = (int) StockRecord::where([
                'warehouse_id' => $wh->id, 'textbook_title_id' => $line->textbook_title_id, 'stock_class' => 'AVAILABLE',
            ])->value('quantity');
            $qty = min($line->quantity, $available);
            if ($qty < 1) {
                $skipped++;

                continue;
            }
            StockRecord::post($wh->id, $line->textbook_title_id, 'AVAILABLE', -$qty);
            StockRecord::post($wh->id, $line->textbook_title_id, 'RESERVED', $qty);
            $shipment = Shipment::create([
                'shipment_no' => sprintf('SHP-%s-%06d', now()->format('Y'), Shipment::count() + 126),
                'origin_name' => $wh->name, 'origin_warehouse_id' => $wh->id,
                'destination_name' => $line->school->name_official, 'destination_school_id' => $line->school_id,
                'textbook_title_id' => $line->textbook_title_id,
                'status' => 'CONFIRMED', 'books' => $qty, 'shipped_on' => now()->toDateString(),
            ]);
            CustodyEvent::create([
                'shipment_id' => $shipment->id, 'event_type' => 'CONFIRMED',
                'actor' => auth()->user()->name,
                'notes' => "Campaign \"{$campaign->name}\" allocation #{$line->id}",
                'occurred_at' => now(),
            ]);
            $line->update(['shipment_id' => $shipment->id]);
            $created++;
        }
        $campaign->update(['status' => 'EXECUTING']);

        return back()->with('flash', "Execution: {$created} shipments created".($skipped ? ", {$skipped} lines skipped for stock shortage (visible for redistribution)" : '').'.');
    }
}
