<?php

namespace App\Http\Controllers;

use App\Modules\Catalogue\Models\PassportEvent;
use App\Modules\Catalogue\Models\PrintBatch;
use App\Modules\Custody\Models\StockRecord;
use App\Modules\Custody\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::with('region')
            ->withSum('stockRecords as total_stock', 'quantity')
            ->orderBy('tier')->orderBy('name')->get();

        return view('warehouses.index', [
            'warehouses' => $warehouses,
            'nationalStock' => StockRecord::sum('quantity'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'tier' => 'required|in:NATIONAL,REGIONAL,DIVISIONAL',
            'region_id' => 'required|exists:regions,id',
        ]);
        $region = \App\Modules\Registry\Models\Region::find($data['region_id']);
        $seq = Warehouse::where('region_id', $region->id)->count() + 1;
        $data['wh_id'] = sprintf('CM-WH-%s-%03d', $region->code, $seq);
        $wh = Warehouse::create($data);

        return redirect()->route('warehouses.show', $wh)->with('flash', "Warehouse {$wh->wh_id} registered.");
    }

    public function show(Warehouse $warehouse)
    {
        $stock = StockRecord::with('title')->where('warehouse_id', $warehouse->id)
            ->orderBy('textbook_title_id')->get()->groupBy('textbook_title_id');
        $pendingBatches = PrintBatch::with('title')
            ->where('qa_status', '!=', 'FAILED')
            ->whereColumn('received_qty', '<', 'quantity')->get();
        $shipments = \App\Modules\Custody\Models\Shipment::where('origin_warehouse_id', $warehouse->id)
            ->orderByDesc('shipped_on')->limit(8)->get();

        return view('warehouses.show', compact('warehouse', 'stock', 'pendingBatches', 'shipments'));
    }

    /** Goods receipt against a print batch (FR-NWD-02). */
    public function receive(Request $request, Warehouse $warehouse)
    {
        $data = $request->validate([
            'print_batch_id' => 'required|exists:print_batches,id',
            'quantity' => 'required|integer|min:1',
        ]);
        $batch = PrintBatch::findOrFail($data['print_batch_id']);

        if ($batch->qa_status === 'FAILED') {
            return back()->with('flash_error', 'QA_BLOCKED: failed batches cannot enter the warehouse (FR-NWD-02).');
        }
        $expected = $batch->quantity - $batch->received_qty;
        $qty = min($data['quantity'], $expected);

        StockRecord::post($warehouse->id, $batch->textbook_title_id, 'AVAILABLE', $qty);
        $batch->increment('received_qty', $qty);
        if ($batch->qa_status === 'PENDING') {
            $batch->update(['qa_status' => 'PASSED']);
        }
        PassportEvent::create([
            'print_batch_id' => $batch->id, 'event_type' => 'WAREHOUSE_RECEIPT',
            'location' => $warehouse->name, 'actor' => auth()->user()->name ?? 'System',
            'occurred_at' => now(),
        ]);

        $msg = "Received {$qty} copies of batch {$batch->batch_no} into {$warehouse->name}.";
        if ($data['quantity'] > $expected) {
            $msg .= ' Over-receipt beyond batch balance was rejected.';
        }

        return back()->with('flash', $msg);
    }
}
