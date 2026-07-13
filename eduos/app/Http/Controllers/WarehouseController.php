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

    /** INV-10: stock lines below the configured threshold. */
    public function lowStock()
    {
        $threshold = StockRecord::lowStockThreshold();
        $rows = StockRecord::with(['warehouse', 'title'])
            ->where('stock_class', 'AVAILABLE')->where('quantity', '<', $threshold)
            ->orderBy('quantity')->get();

        return view('warehouses.low-stock', compact('rows', 'threshold'));
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
        // Per-copy lifecycle: PRINTED → IN_WAREHOUSE (copy-tracked titles)
        \App\Modules\Catalogue\Models\Copy::advance($batch->textbook_title_id, 'PRINTED', 'IN_WAREHOUSE', $qty);

        $msg = "Received {$qty} copies of batch {$batch->batch_no} into {$warehouse->name}.";
        if ($data['quantity'] > $expected) {
            $msg .= ' Over-receipt beyond batch balance was rejected.';
        }

        return back()->with('flash', $msg);
    }

    /** Cycle count (FR-NWD-04): counted vs ledger; variance posts an audited adjustment. */
    public function count(Request $request, Warehouse $warehouse)
    {
        $data = $request->validate([
            'textbook_title_id' => 'required|exists:textbook_titles,id',
            'counted_qty' => 'required|integer|min:0',
        ]);
        $ledger = (int) StockRecord::where([
            'warehouse_id' => $warehouse->id, 'textbook_title_id' => $data['textbook_title_id'], 'stock_class' => 'AVAILABLE',
        ])->value('quantity');
        \App\Modules\Custody\Models\WarehouseCount::create($data + [
            'warehouse_id' => $warehouse->id, 'ledger_qty' => $ledger, 'actor' => auth()->user()->name,
        ]);
        $variance = $data['counted_qty'] - $ledger;
        if ($variance !== 0) {
            StockRecord::post($warehouse->id, $data['textbook_title_id'], 'AVAILABLE', $variance);
            \App\Modules\Platform\Models\Alert::create([
                'severity' => abs($variance) > max(50, $ledger * 0.005) ? 'CRITICAL' : 'WARNING',
                'title' => "Cycle count variance at {$warehouse->name}",
                'message' => "Counted {$data['counted_qty']} vs {$ledger} on ledger (adjustment {$variance} posted by ".auth()->user()->name.').',
                'link' => "/warehouses/{$warehouse->id}",
            ]);
        }

        return back()->with($variance === 0 ? 'flash' : 'flash_error',
            $variance === 0 ? 'Count reconciled with ledger.' : "Variance {$variance}: adjustment posted and audit alert raised.");
    }

    /** Inter-warehouse transfer as a custody shipment (destination = warehouse). */
    public function transfer(Request $request, Warehouse $warehouse)
    {
        $data = $request->validate([
            'destination_warehouse_id' => 'required|exists:warehouses,id',
            'textbook_title_id' => 'required|exists:textbook_titles,id',
            'books' => 'required|integer|min:1',
        ]);
        if ((int) $data['destination_warehouse_id'] === $warehouse->id) {
            return back()->with('flash_error', 'Destination must differ from origin.');
        }
        $available = (int) StockRecord::where([
            'warehouse_id' => $warehouse->id, 'textbook_title_id' => $data['textbook_title_id'], 'stock_class' => 'AVAILABLE',
        ])->value('quantity');
        if ($available < $data['books']) {
            return back()->with('flash_error', "Insufficient AVAILABLE stock ({$available}).");
        }
        $dest = Warehouse::find($data['destination_warehouse_id']);
        StockRecord::post($warehouse->id, $data['textbook_title_id'], 'AVAILABLE', -$data['books']);
        StockRecord::post($warehouse->id, $data['textbook_title_id'], 'RESERVED', $data['books']);
        $shipment = \App\Modules\Custody\Models\Shipment::create([
            'shipment_no' => sprintf('SHP-%s-%06d', now()->format('Y'), \App\Modules\Custody\Models\Shipment::count() + 126),
            'origin_name' => $warehouse->name, 'origin_warehouse_id' => $warehouse->id,
            'destination_name' => $dest->name, 'destination_warehouse_id' => $dest->id,
            'textbook_title_id' => $data['textbook_title_id'],
            'status' => 'CONFIRMED', 'books' => $data['books'], 'shipped_on' => now()->toDateString(),
        ]);
        \App\Modules\Custody\Models\CustodyEvent::create([
            'shipment_id' => $shipment->id, 'event_type' => 'CONFIRMED',
            'actor' => auth()->user()->name, 'notes' => "Inter-warehouse transfer to {$dest->name}",
            'occurred_at' => now(),
        ]);

        return redirect()->route('shipments.show', $shipment)->with('flash', "Transfer {$shipment->shipment_no} confirmed to {$dest->name}.");
    }
    /** INV-08: manual stock adjustment with a reason code; journalled, approval-tier gated. */
    public function adjust(Request $request, Warehouse $warehouse)
    {
        $data = $request->validate([
            'textbook_title_id' => 'required|exists:textbook_titles,id',
            'delta' => 'required|integer|between:-100000,100000|not_in:0',
            'reason' => 'required|in:DAMAGE,LOSS,THEFT,CORRECTION,FOUND',
            'note' => 'nullable|string|max:200',
        ]);
        $rec = StockRecord::post($warehouse->id, $data['textbook_title_id'], 'AVAILABLE', (int) $data['delta']);
        \App\Modules\Custody\Models\StockTransaction::latest('id')->first()?->update([
            'context' => "ADJUSTMENT {$data['reason']}".(! empty($data['note']) ? " — {$data['note']}" : ''),
        ]);

        return back()->with('flash', sprintf('Adjustment posted: %+d (%s). AVAILABLE balance now %d.', $data['delta'], $data['reason'], $rec->quantity));
    }

}
