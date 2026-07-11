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
        ]);

        return back()->with('flash', "Order {$order->order_no} placed ({$order->quantity} copies @ {$order->unit_price_fcfa} FCFA).");
    }

    /** Delivery registers the print batch and links it to the order (traceable procurement). */
    public function markDelivered(ProcurementOrder $order)
    {
        if ($order->status === 'DELIVERED') {
            return back()->with('flash_error', 'Already delivered.');
        }
        $batch = PrintBatch::create([
            'batch_no' => sprintf('BAT-%s-%05d', now()->format('Y'), PrintBatch::count() + 1),
            'textbook_title_id' => $order->textbook_title_id,
            'printer' => $order->supplier->name,
            'quantity' => $order->quantity,
        ]);
        PassportEvent::create([
            'print_batch_id' => $batch->id, 'event_type' => 'PRINTED',
            'location' => $order->supplier->name, 'actor' => auth()->user()->name,
            'occurred_at' => now(),
        ]);
        $order->update(['status' => 'DELIVERED', 'print_batch_id' => $batch->id]);

        return back()->with('flash', "Delivery registered as batch {$batch->batch_no}; ready for warehouse receipt.");
    }
}
