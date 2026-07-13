<?php

namespace App\Http\Controllers;

use App\Modules\Catalogue\Models\TextbookTitle;
use App\Modules\Custody\Models\CustodyEvent;
use App\Modules\Custody\Models\Shipment;
use App\Modules\Custody\Models\StockRecord;
use App\Modules\Custody\Models\Warehouse;
use App\Modules\Platform\Models\Alert;
use App\Modules\Registry\Models\School;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function index(Request $request)
    {
        $shipments = Shipment::with(['originWarehouse', 'destinationSchool'])
            ->when($request->q, fn ($q, $v) => $q->where('shipment_no', 'like', "%{$v}%"))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->orderByDesc('shipment_no')
            ->paginate(12)->withQueryString();

        return view('shipments.index', [
            'shipments' => $shipments,
            'counts' => [
                'open' => Shipment::whereNotIn('status', ['CLOSED', 'RECEIVED_FULL', 'CANCELLED'])->count(),
                'transit' => Shipment::whereIn('status', ['DISPATCHED', 'IN_TRANSIT'])->count(),
                'discrepancy' => Shipment::where('status', 'RECEIVED_WITH_DISCREPANCY')->count(),
                'delivered' => Shipment::where('status', 'RECEIVED_FULL')->count(),
            ],
        ]);
    }

    public function show(Shipment $shipment)
    {
        $shipment->load(['originWarehouse', 'destinationSchool', 'title', 'custodyEvents']);

        return view('shipments.show', compact('shipment'));
    }

    public function create()
    {
        return view('shipments.create', [
            'warehouses' => Warehouse::orderBy('name')->get(),
            'schools' => School::orderBy('name_official')->get(),
            'titles' => TextbookTitle::where('status', 'APPROVED')->orderBy('ntid')->get(),
        ]);
    }

    /** Creating a shipment reserves stock (FR-NWD-07). */
    public function store(Request $request)
    {
        $data = $request->validate([
            'origin_warehouse_id' => 'required|exists:warehouses,id',
            'destination_school_id' => 'required|exists:schools,id',
            'textbook_title_id' => 'required|exists:textbook_titles,id',
            'books' => 'required|integer|min:1',
        ]);

        $school = School::find($data['destination_school_id']);
        if ($school->status !== 'OPERATIONAL') {
            return back()->withInput()->with('flash_error', 'SCHOOL_NOT_OPERATIONAL: destination must be an operational school (FR-NSR-06).');
        }

        $available = StockRecord::where([
            'warehouse_id' => $data['origin_warehouse_id'],
            'textbook_title_id' => $data['textbook_title_id'],
            'stock_class' => 'AVAILABLE',
        ])->value('quantity') ?? 0;
        if ($available < $data['books']) {
            return back()->withInput()->with('flash_error', "Insufficient AVAILABLE stock: {$available} on hand, {$data['books']} requested (FR-NWD-07 shortage).");
        }

        $wh = Warehouse::find($data['origin_warehouse_id']);
        StockRecord::post($wh->id, $data['textbook_title_id'], 'AVAILABLE', -$data['books']);
        StockRecord::post($wh->id, $data['textbook_title_id'], 'RESERVED', $data['books']);

        $shipment = Shipment::create([
            'shipment_no' => sprintf('SHP-%s-%06d', now()->format('Y'), Shipment::count() + 126),
            'origin_name' => $wh->name, 'origin_warehouse_id' => $wh->id,
            'destination_name' => $school->name_official, 'destination_school_id' => $school->id,
            'textbook_title_id' => $data['textbook_title_id'],
            'status' => 'CONFIRMED', 'books' => $data['books'],
            'shipped_on' => now()->toDateString(),
        ]);
        CustodyEvent::create([
            'shipment_id' => $shipment->id, 'event_type' => 'CONFIRMED',
            'actor' => auth()->user()->name ?? 'System',
            'notes' => "Stock reserved at {$wh->name}", 'occurred_at' => now(),
        ]);

        return redirect()->route('shipments.show', $shipment)->with('flash', "Shipment {$shipment->shipment_no} confirmed; {$data['books']} books reserved.");
    }

    /** SHIP-06: approval before dispatch (warehouse-approve tier, separation of duties). */
    public function approve(Shipment $shipment)
    {
        if ($shipment->status !== 'CONFIRMED' || $shipment->approved_at) {
            return back()->with('flash_error', 'Only unapproved CONFIRMED shipments can be approved.');
        }
        $shipment->update(['approved_by' => auth()->user()->name, 'approved_at' => now()]);
        CustodyEvent::create([
            'shipment_id' => $shipment->id, 'event_type' => 'APPROVED',
            'actor' => auth()->user()->name, 'occurred_at' => now(),
        ]);

        return back()->with('flash', 'Shipment approved for dispatch.');
    }

    /** CONFIRMED → DISPATCHED with named custody (FR-NWD-SM-01). */
    public function dispatchShipment(Request $request, Shipment $shipment)
    {
        if (! in_array($shipment->status, ['CONFIRMED', 'LOADED'])) {
            return back()->with('flash_error', "ILLEGAL_TRANSITION: {$shipment->status} → DISPATCHED.");
        }
        if (! $shipment->approved_at) {
            return back()->with('flash_error', 'Dispatch requires prior approval (SHIP-06).');
        }
        $data = $request->validate([
            'carrier' => 'required|string|max:120',
            'waybill' => 'required|string|max:60',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'route_note' => 'nullable|string|max:200',
        ]);
        StockRecord::post($shipment->origin_warehouse_id, $shipment->textbook_title_id, 'RESERVED', -$shipment->books);
        StockRecord::post($shipment->origin_warehouse_id, $shipment->textbook_title_id, 'IN_TRANSIT_OUT', $shipment->books);
        $shipment->update(['status' => 'IN_TRANSIT']);
        \App\Modules\Catalogue\Models\Copy::advance($shipment->textbook_title_id, 'IN_WAREHOUSE', 'IN_TRANSIT', $shipment->books, null, $shipment->id);
        CustodyEvent::create([
            'shipment_id' => $shipment->id, 'event_type' => 'DISPATCHED',
            'actor' => auth()->user()->name ?? 'System',
            'notes' => "Carrier {$data['carrier']}, waybill {$data['waybill']}",
            'occurred_at' => now(),
        ]);

        // LOG-06: create the trip; bind vehicle/driver when supplied
        $trip = \App\Modules\Logistics\Models\Trip::create([
            'shipment_id' => $shipment->id,
            'vehicle_id' => $data['vehicle_id'] ?? null,
            'driver_id' => $data['driver_id'] ?? null,
            'status' => 'EN_ROUTE', 'departed_at' => now(),
            'route_note' => $data['route_note'] ?? null,
        ]);
        $trip->vehicle?->update(['status' => 'ON_TRIP']);
        $trip->driver?->update(['status' => 'ON_TRIP']);

        return back()->with('flash', "Shipment dispatched with {$data['carrier']} (waybill {$data['waybill']}).");
    }

    /** Receipt: variance opens a DiscrepancyCase — never silently absorbed (FR-NWD-SM-02). */
    public function receive(Request $request, Shipment $shipment)
    {
        if (! in_array($shipment->status, ['IN_TRANSIT', 'DISPATCHED', 'ARRIVED'])) {
            return back()->with('flash_error', "ILLEGAL_TRANSITION: {$shipment->status} → RECEIVED.");
        }
        $data = $request->validate(['received_books' => 'required|integer|min:0']);
        $received = min($data['received_books'], $shipment->books);
        $variance = $received - $shipment->books;

        StockRecord::post($shipment->origin_warehouse_id, $shipment->textbook_title_id, 'IN_TRANSIT_OUT', -$shipment->books);

        $shipment->update([
            'received_books' => $received,
            'status' => $variance === 0 ? 'RECEIVED_FULL' : 'RECEIVED_WITH_DISCREPANCY',
        ]);
        // Trip closes on receipt (LOG)
        $trip = \App\Modules\Logistics\Models\Trip::where('shipment_id', $shipment->id)->latest('id')->first();
        if ($trip && $trip->status !== 'ARRIVED') {
            $trip->update(['status' => 'ARRIVED', 'arrived_at' => now()]);
            $trip->vehicle?->update(['status' => 'AVAILABLE']);
            $trip->driver?->update(['status' => 'AVAILABLE']);
        }

        CustodyEvent::create([
            'shipment_id' => $shipment->id, 'event_type' => 'RECEIVED',
            'actor' => auth()->user()->name ?? 'System',
            'notes' => "Counted {$received} of {$shipment->books}", 'occurred_at' => now(),
        ]);

        if ($shipment->destination_warehouse_id) {
            StockRecord::post($shipment->destination_warehouse_id, $shipment->textbook_title_id, 'AVAILABLE', $received);
            \App\Modules\Catalogue\Models\Copy::whereIn('id',
                \App\Modules\Catalogue\Models\Copy::where('shipment_id', $shipment->id)
                    ->where('lifecycle_state', 'IN_TRANSIT')->limit($received)->pluck('id')
            )->update(['lifecycle_state' => 'IN_WAREHOUSE', 'shipment_id' => null]);
        }

        if ($shipment->destination_school_id) {
            \App\Modules\SchoolOps\Models\SchoolStock::create([
                'school_id' => $shipment->destination_school_id,
                'textbook_title_id' => $shipment->textbook_title_id,
                'quantity' => $received, 'condition' => 'GOOD',
            ]);
            \App\Modules\Catalogue\Models\Copy::advance($shipment->textbook_title_id, 'IN_TRANSIT', 'AT_SCHOOL', $received, $shipment->destination_school_id, $shipment->id);
        }

        if ($variance !== 0) {
            CustodyEvent::create([
                'shipment_id' => $shipment->id, 'event_type' => 'DISCREPANCY_OPENED',
                'actor' => 'System', 'notes' => "Variance {$variance}; frozen in QUARANTINE", 'occurred_at' => now(),
            ]);
            StockRecord::post($shipment->origin_warehouse_id, $shipment->textbook_title_id, 'QUARANTINE', abs($variance));
            Alert::create([
                'severity' => 'CRITICAL',
                'title' => "Discrepancy on {$shipment->shipment_no}",
                'message' => "Received {$received} of {$shipment->books} at {$shipment->destination_name}. Variance frozen in QUARANTINE pending reconciliation (FR-NWD-SM-02).",
                'link' => "/shipments/{$shipment->id}",
            ]);

            return back()->with('flash_error', "Received with variance {$variance}: discrepancy case opened and quarantined.");
        }

        return back()->with('flash', 'Shipment received in full. Custody chain closed clean.');
    }

    /** SHIP-04: printable picking list. */
    public function picking(Shipment $shipment)
    {
        $shipment->load(['title', 'originWarehouse']);
        $copies = \App\Modules\Catalogue\Models\Copy::whereHas('batch', fn ($q) => $q->where('textbook_title_id', $shipment->textbook_title_id))
            ->where('lifecycle_state', 'IN_WAREHOUSE')->orderBy('id')->limit(min($shipment->books, 40))->get();

        return view('shipments.picking', compact('shipment', 'copies'));
    }

    /** POD-05: printable digital proof of delivery. */
    public function pod(Shipment $shipment)
    {
        abort_unless(in_array($shipment->status, ['RECEIVED_FULL', 'RECEIVED_WITH_DISCREPANCY', 'CLOSED']), 404);
        $shipment->load(['title', 'custodyEvents', 'destinationSchool']);

        return view('shipments.pod', compact('shipment'));
    }

    /** SHIP-11: delivery schedule — open shipments by date. */
    public function schedule()
    {
        $upcoming = Shipment::whereIn('status', ['CONFIRMED', 'LOADED', 'DISPATCHED', 'IN_TRANSIT'])
            ->with(['destinationSchool'])->orderBy('shipped_on')->get()->groupBy(fn ($s) => $s->shipped_on->format('Y-m-d'));

        return view('shipments.schedule', compact('upcoming'));
    }

    /** SHIP-12: distribution network — origin → destination volumes. */
    public function network()
    {
        $lanes = Shipment::selectRaw('origin_name, destination_name, count(*) n, sum(books) books')
            ->groupBy('origin_name', 'destination_name')->orderByDesc('books')->limit(40)->get();

        return view('shipments.network', compact('lanes'));
    }

    /** Cancel before dispatch: reverses the reservation (FR-NWD state machine). */
    public function cancel(Shipment $shipment)
    {
        if (! in_array($shipment->status, ['CONFIRMED', 'LOADED'])) {
            return back()->with('flash_error', "ILLEGAL_TRANSITION: {$shipment->status} → CANCELLED (only pre-dispatch).");
        }
        StockRecord::post($shipment->origin_warehouse_id, $shipment->textbook_title_id, 'RESERVED', -$shipment->books);
        StockRecord::post($shipment->origin_warehouse_id, $shipment->textbook_title_id, 'AVAILABLE', $shipment->books);
        $shipment->update(['status' => 'CANCELLED']);
        CustodyEvent::create([
            'shipment_id' => $shipment->id, 'event_type' => 'CANCELLED',
            'actor' => auth()->user()->name, 'notes' => 'Reservation reversed to AVAILABLE',
            'occurred_at' => now(),
        ]);

        return back()->with('flash', 'Shipment cancelled; reserved stock returned to AVAILABLE.');
    }

    /** Discrepancy resolution: accept-short / found / write-off — closes the case with named custody. */
    public function resolve(Request $request, Shipment $shipment)
    {
        if ($shipment->status !== 'RECEIVED_WITH_DISCREPANCY' || $shipment->resolved_at) {
            return back()->with('flash_error', 'No open discrepancy on this shipment.');
        }
        $res = $request->validate(['resolution' => 'required|in:ACCEPT_SHORT,FOUND,WRITE_OFF'])['resolution'];
        $variance = abs($shipment->variance());

        StockRecord::post($shipment->origin_warehouse_id, $shipment->textbook_title_id, 'QUARANTINE', -$variance);
        if ($res === 'FOUND') {
            StockRecord::post($shipment->origin_warehouse_id, $shipment->textbook_title_id, 'AVAILABLE', $variance);
        }
        // ACCEPT_SHORT and WRITE_OFF exit the ledger via the loss sink (recorded on the custody chain)

        $shipment->update(['discrepancy_resolution' => $res, 'resolved_at' => now(), 'status' => 'CLOSED']);
        CustodyEvent::create([
            'shipment_id' => $shipment->id, 'event_type' => 'DISCREPANCY_RESOLVED',
            'actor' => auth()->user()->name,
            'notes' => "{$variance} books resolved as {$res}",
            'occurred_at' => now(),
        ]);
        Alert::where('link', "/shipments/{$shipment->id}")->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('flash', "Discrepancy resolved as {$res}; case closed, quarantine cleared.");
    }
}
