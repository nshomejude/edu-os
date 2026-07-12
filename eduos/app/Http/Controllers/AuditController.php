<?php

namespace App\Http\Controllers;

use App\Modules\Catalogue\Models\PassportEvent;
use App\Modules\Custody\Models\CustodyEvent;
use App\Modules\Custody\Models\StockTransaction;
use Illuminate\Http\Request;

/** ADM-03: unified, chained, filterable audit trail. */
class AuditController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->q);

        $custody = CustodyEvent::with('shipment')
            ->when($q, fn ($w) => $w->where(fn ($x) => $x->where('event_type', 'like', "%{$q}%")->orWhere('actor', 'like', "%{$q}%")))
            ->orderByDesc('id')->limit(40)->get()
            ->map(fn ($e) => (object) [
                'at' => $e->occurred_at, 'stream' => 'CUSTODY',
                'what' => $e->event_type.' — '.$e->shipment?->shipment_no,
                'actor' => $e->actor, 'chained' => (bool) $e->hash, 'intact' => $e->hash ? $e->verifyChainLink() : null,
            ]);
        $passport = PassportEvent::with('batch')
            ->when($q, fn ($w) => $w->where(fn ($x) => $x->where('event_type', 'like', "%{$q}%")->orWhere('actor', 'like', "%{$q}%")))
            ->orderByDesc('id')->limit(40)->get()
            ->map(fn ($e) => (object) [
                'at' => $e->occurred_at, 'stream' => 'PASSPORT',
                'what' => $e->event_type.' — '.$e->batch?->batch_no.' @ '.$e->location,
                'actor' => $e->actor, 'chained' => (bool) $e->hash, 'intact' => $e->hash ? $e->verifyChainLink() : null,
            ]);
        $stock = StockTransaction::with(['warehouse', 'title'])
            ->when($q, fn ($w) => $w->where('actor', 'like', "%{$q}%"))
            ->orderByDesc('id')->limit(40)->get()
            ->map(fn ($t) => (object) [
                'at' => $t->created_at, 'stream' => 'STOCK',
                'what' => sprintf('%+d %s %s @ %s (bal %d)', $t->delta, $t->stock_class, $t->title?->ntid, $t->warehouse?->name, $t->balance_after),
                'actor' => $t->actor, 'chained' => false, 'intact' => null,
            ]);

        $events = $custody->concat($passport)->concat($stock)->sortByDesc('at')->take(80)->values();

        return view('audit.index', compact('events', 'q'));
    }
}
