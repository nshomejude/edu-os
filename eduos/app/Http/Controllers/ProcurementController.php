<?php

namespace App\Http\Controllers;

use App\Modules\Catalogue\Models\PassportEvent;
use App\Modules\Catalogue\Models\PrintBatch;
use App\Modules\Catalogue\Models\ProcurementOrder;
use App\Modules\Catalogue\Models\Supplier;
use App\Modules\Catalogue\Models\TextbookTitle;
use Illuminate\Http\Request;

/** Procurement & printing (Problems 8–17, demo depth): orders → delivery → batch. */
class ProcurementController extends Controller
{
    public function index()
    {
        return view('procurement.index', [
            'orders' => ProcurementOrder::with(['supplier', 'title', 'batch'])->orderByDesc('id')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'titles' => TextbookTitle::where('status', 'APPROVED')->orderBy('ntid')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'textbook_title_id' => 'required|exists:textbook_titles,id',
            'quantity' => 'required|integer|min:1',
            'unit_price_fcfa' => 'required|integer|min:1',
            'contract_ref' => 'required|string|max:60',
        ]);
        $order = ProcurementOrder::create($data + [
            'order_no' => sprintf('PO-%s-%04d', now()->format('Y'), ProcurementOrder::count() + 1),
            'status' => 'SUBMITTED',   // PROC lifecycle: ministry approval precedes any delivery
        ]);

        return back()->with('flash', "Order {$order->order_no} placed ({$order->quantity} copies @ {$order->unit_price_fcfa} FCFA).");
    }

    /** PROC-03: order details. */
    public function showOrder(ProcurementOrder $order)
    {
        $order->load(['supplier', 'title', 'batch.passportEvents']);

        return view('procurement.order', compact('order'));
    }

    /** PROC-05: supplier details with order history. */
    public function showSupplier(Supplier $supplier)
    {
        $orders = ProcurementOrder::with('title')->where('supplier_id', $supplier->id)->orderByDesc('id')->get();

        return view('procurement.supplier', compact('supplier', 'orders'));
    }

    public function storeSupplier(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:160|unique:suppliers,name',
            'type' => 'required|in:PRINTER,PUBLISHER,LOGISTICS',
            'contact' => 'nullable|string|max:160',
        ]);
        Supplier::create($data);

        return back()->with('flash', "Supplier {$data['name']} registered.");
    }

    public function updateSupplier(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => 'required|string|max:160',
            'type' => 'required|in:PRINTER,PUBLISHER,LOGISTICS',
            'contact' => 'nullable|string|max:160',
        ]);
        $supplier->update($data);

        return back()->with('flash', "Supplier {$supplier->name} updated.");
    }

    /** Delivery registers the print batch and links it to the order (traceable procurement). */
    public function markDelivered(Request $request, ProcurementOrder $order)
    {
        if (! in_array($order->status, ['APPROVED', 'PARTIALLY_DELIVERED'])) {
            return back()->with('flash_error', "Order is {$order->status} — deliveries post only against APPROVED orders (PROC lifecycle).");
        }
        $remaining = $order->quantity - (int) $order->delivered_total;
        $data = $request->validate([
            'delivered_qty' => 'nullable|integer|min:1|max:'.$remaining,
            'damaged_qty' => 'nullable|integer|min:0|max:'.$remaining,
        ]);
        // PROC-06/07: partial deliveries accumulate; damaged units are rejected at the gate
        $delivered = (int) ($data['delivered_qty'] ?? $remaining);
        $damaged = min((int) ($data['damaged_qty'] ?? 0), $delivered);
        $good = $delivered - $damaged;
        $batch = PrintBatch::create([
            'batch_no' => sprintf('BAT-%s-%05d', now()->format('Y'), PrintBatch::count() + 1),
            'textbook_title_id' => $order->textbook_title_id,
            'printer' => $order->supplier->name,
            'quantity' => $good,
            'procurement_order_id' => $order->id,
        ]);
        $order->forceFill([
            'delivered_total' => (int) $order->delivered_total + $delivered,
            'status' => ((int) $order->delivered_total + $delivered) >= $order->quantity ? 'DELIVERED' : 'PARTIALLY_DELIVERED',
        ])->save();
        if ($damaged > 0) {
            $order->forceFill(['damaged_qty' => $damaged])->save();
            \App\Modules\Platform\Models\Alert::create([
                'severity' => 'WARNING',
                'title' => "Supplier delivery rejects — {$order->order_no}",
                'message' => "{$damaged} of {$order->quantity} units rejected as damaged at delivery verification from {$order->supplier->name}; batch {$batch->batch_no} registered with {$good} good units.",
                'link' => '/procurement',
            ]);
        }
        PassportEvent::create([
            'print_batch_id' => $batch->id, 'event_type' => 'PRINTED',
            'location' => $order->supplier->name, 'actor' => auth()->user()->name,
            'occurred_at' => now(),
        ]);
        // Mint per-copy NCIDs for copy-tracked titles (same policy as direct batch registration)
        $title = $order->title;
        if ($title->tracking_granularity === 'COPY') {
            $minted = min($batch->quantity, 500);
            $rows = [];
            for ($i = 1; $i <= $minted; $i++) {
                $rows[] = [
                    'ncid' => \App\Support\CheckDigit::append(sprintf('%s-%05d-%06d', $title->ntid, $batch->id, $i)),
                    'print_batch_id' => $batch->id, 'lifecycle_state' => 'PRINTED',
                    'condition' => 'NEW', 'created_at' => now(), 'updated_at' => now(),
                ];
            }
            \App\Modules\Catalogue\Models\Copy::insert($rows);
        }
        $order->update(['print_batch_id' => $batch->id]);   // status governed by delivered_total above

        return back()->with('flash', "Delivery registered as batch {$batch->batch_no}; ready for warehouse receipt.");
    }
    /** PROC lifecycle: ministry approves a submitted order before any delivery can post. */
    public function approveOrder(ProcurementOrder $order)
    {
        if ($order->status !== 'SUBMITTED') {
            return back()->with('flash_error', 'Only SUBMITTED orders can be approved.');
        }
        $order->update(['status' => 'APPROVED']);

        return back()->with('flash', "Order {$order->order_no} approved by ".auth()->user()->name.' — deliveries can now be verified.');
    }

}
