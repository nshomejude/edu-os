<?php

namespace App\Http\Controllers;

use App\Modules\Catalogue\Models\PassportEvent;
use App\Modules\Custody\Models\CustodyEvent;
use App\Modules\Custody\Models\StockTransaction;
use App\Modules\Platform\Models\AuthEvent;
use Illuminate\Http\Request;

/** ADM-03: unified, chained, filterable audit trail across four event streams. */
class AuditController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->q);
        $from = $request->date('from');
        $to = $request->date('to')?->endOfDay();
        $span = fn ($query, string $col) => $query
            ->when($from, fn ($w) => $w->where($col, '>=', $from))
            ->when($to, fn ($w) => $w->where($col, '<=', $to));

        $custody = $span(CustodyEvent::with('shipment'), 'occurred_at')
            ->when($q, fn ($w) => $w->where(fn ($x) => $x->where('event_type', 'like', "%{$q}%")->orWhere('actor', 'like', "%{$q}%")))
            ->orderByDesc('id')->limit(40)->get()
            ->map(fn ($e) => (object) [
                'at' => $e->occurred_at, 'stream' => 'CUSTODY',
                'what' => $e->event_type.' — '.$e->shipment?->shipment_no,
                'actor' => $e->actor, 'chained' => (bool) $e->hash, 'intact' => $e->hash ? $e->verifyChainLink() : null,
            ]);
        $passport = $span(PassportEvent::with('batch'), 'occurred_at')
            ->when($q, fn ($w) => $w->where(fn ($x) => $x->where('event_type', 'like', "%{$q}%")->orWhere('actor', 'like', "%{$q}%")))
            ->orderByDesc('id')->limit(40)->get()
            ->map(fn ($e) => (object) [
                'at' => $e->occurred_at, 'stream' => 'PASSPORT',
                'what' => $e->event_type.' — '.$e->batch?->batch_no.' @ '.$e->location,
                'actor' => $e->actor, 'chained' => (bool) $e->hash, 'intact' => $e->hash ? $e->verifyChainLink() : null,
            ]);
        $stock = $span(StockTransaction::with(['warehouse', 'title']), 'created_at')
            ->when($q, fn ($w) => $w->where('actor', 'like', "%{$q}%"))
            ->orderByDesc('id')->limit(40)->get()
            ->map(fn ($t) => (object) [
                'at' => $t->created_at, 'stream' => 'STOCK',
                'what' => sprintf('%+d %s %s @ %s (bal %d) · %s', $t->delta, $t->stock_class, $t->title?->ntid, $t->warehouse?->name, $t->balance_after, $t->context),
                'actor' => $t->actor, 'chained' => false, 'intact' => null,
            ]);
        $auth = $span(AuthEvent::query(), 'created_at')
            ->when($q, fn ($w) => $w->where(fn ($x) => $x->where('event', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")))
            ->orderByDesc('id')->limit(40)->get()
            ->map(fn ($e) => (object) [
                'at' => $e->created_at, 'stream' => 'AUTH',
                'what' => str_replace('_', ' ', $e->event).' — '.$e->email.($e->ip ? ' from '.$e->ip : ''),
                'actor' => $e->email, 'chained' => false, 'intact' => null,
            ]);

        $events = $custody->concat($passport)->concat($stock)->concat($auth)->sortByDesc('at')->take(120)->values();

        return view('audit.index', compact('events', 'q'));
    }
}
