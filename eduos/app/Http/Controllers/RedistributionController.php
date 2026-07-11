<?php

namespace App\Http\Controllers;

use App\Modules\Custody\Models\CustodyEvent;
use App\Modules\Custody\Models\RedistributionProposal;
use App\Modules\Custody\Models\Shipment;
use App\Modules\Custody\Models\StockRecord;
use App\Modules\Registry\Models\School;
use App\Modules\SchoolOps\Models\SchoolStock;

class RedistributionController extends Controller
{
    /** FR-NWD-11: propose transfers from surplus stock to shortage schools. Human approval required. */
    public function index()
    {
        return view('redistribution.index', [
            'proposals' => RedistributionProposal::with(['fromWarehouse', 'toSchool', 'title'])
                ->orderByRaw("status = 'PROPOSED' desc")->orderByDesc('created_at')->get(),
        ]);
    }

    public function generate()
    {
        $created = 0;
        // Shortage: operational schools with enrolment but no stock of an approved title in their grade band
        $surplus = StockRecord::with(['warehouse.region', 'title'])
            ->where('stock_class', 'AVAILABLE')->where('quantity', '>', 3000)->get();

        foreach ($surplus->take(12) as $stock) {
            $school = School::where('status', 'OPERATIONAL')
                ->where('region_id', $stock->warehouse->region_id)
                ->whereDoesntHave('region', fn ($q) => $q->whereRaw('1=0'))
                ->whereNotIn('id', SchoolStock::where('textbook_title_id', $stock->textbook_title_id)->pluck('school_id'))
                ->first();
            if (! $school) {
                continue;
            }
            $exists = RedistributionProposal::where([
                'to_school_id' => $school->id, 'textbook_title_id' => $stock->textbook_title_id, 'status' => 'PROPOSED',
            ])->exists();
            if ($exists) {
                continue;
            }
            $qty = min(500, intdiv($stock->quantity, 10));
            RedistributionProposal::create([
                'from_warehouse_id' => $stock->warehouse_id,
                'to_school_id' => $school->id,
                'textbook_title_id' => $stock->textbook_title_id,
                'quantity' => $qty,
                'reason' => "Surplus {$stock->quantity} at {$stock->warehouse->name}; {$school->name_official} holds no stock of this title (same region).",
            ]);
            $created++;
        }

        return back()->with('flash', "{$created} redistribution proposals generated. Nothing moves without approval.");
    }

    public function approve(RedistributionProposal $proposal)
    {
        if ($proposal->status !== 'PROPOSED') {
            return back()->with('flash_error', 'Already decided.');
        }
        $available = StockRecord::where([
            'warehouse_id' => $proposal->from_warehouse_id,
            'textbook_title_id' => $proposal->textbook_title_id,
            'stock_class' => 'AVAILABLE',
        ])->value('quantity') ?? 0;
        if ($available < $proposal->quantity) {
            return back()->with('flash_error', 'Stock no longer available at proposed level.');
        }

        StockRecord::post($proposal->from_warehouse_id, $proposal->textbook_title_id, 'AVAILABLE', -$proposal->quantity);
        StockRecord::post($proposal->from_warehouse_id, $proposal->textbook_title_id, 'RESERVED', $proposal->quantity);

        $shipment = Shipment::create([
            'shipment_no' => sprintf('SHP-%s-%06d', now()->format('Y'), Shipment::count() + 126),
            'origin_name' => $proposal->fromWarehouse->name,
            'origin_warehouse_id' => $proposal->from_warehouse_id,
            'destination_name' => $proposal->toSchool->name_official,
            'destination_school_id' => $proposal->to_school_id,
            'textbook_title_id' => $proposal->textbook_title_id,
            'status' => 'CONFIRMED', 'books' => $proposal->quantity,
            'shipped_on' => now()->toDateString(),
        ]);
        CustodyEvent::create([
            'shipment_id' => $shipment->id, 'event_type' => 'CONFIRMED',
            'actor' => auth()->user()->name,
            'notes' => "Redistribution proposal #{$proposal->id} approved",
            'occurred_at' => now(),
        ]);
        $proposal->update(['status' => 'APPROVED', 'shipment_id' => $shipment->id]);

        return back()->with('flash', "Approved: shipment {$shipment->shipment_no} created and stock reserved.");
    }

    public function reject(RedistributionProposal $proposal)
    {
        $proposal->update(['status' => 'REJECTED']);

        return back()->with('flash', 'Proposal rejected.');
    }
}
