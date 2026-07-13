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
    /** Merge the four streams with shared text/date filters. */
    private function trail(Request $request, int $perStream, ?int $take)
    {
        $q = trim((string) $request->q);
        $from = $request->date('from');
        $to = $request->date('to')?->endOfDay();
        $span = fn ($query, string $col) => $query
            ->when($from, fn ($w) => $w->where($col, '>=', $from))
            ->when($to, fn ($w) => $w->where($col, '<=', $to));

        $custody = $span(CustodyEvent::with('shipment'), 'occurred_at')
            ->when($q, fn ($w) => $w->where(fn ($x) => $x->where('event_type', 'like', "%{$q}%")->orWhere('actor', 'like', "%{$q}%")))
            ->orderByDesc('id')->limit($perStream)->get()
            ->map(fn ($e) => (object) [
                'at' => $e->occurred_at, 'stream' => 'CUSTODY',
                'what' => $e->event_type.' — '.$e->shipment?->shipment_no,
                'actor' => $e->actor, 'chained' => (bool) $e->hash, 'intact' => $e->hash ? $e->verifyChainLink() : null,
            ]);
        $passport = $span(PassportEvent::with('batch'), 'occurred_at')
            ->when($q, fn ($w) => $w->where(fn ($x) => $x->where('event_type', 'like', "%{$q}%")->orWhere('actor', 'like', "%{$q}%")))
            ->orderByDesc('id')->limit($perStream)->get()
            ->map(fn ($e) => (object) [
                'at' => $e->occurred_at, 'stream' => 'PASSPORT',
                'what' => $e->event_type.' — '.$e->batch?->batch_no.' @ '.$e->location,
                'actor' => $e->actor, 'chained' => (bool) $e->hash, 'intact' => $e->hash ? $e->verifyChainLink() : null,
            ]);
        $stock = $span(StockTransaction::with(['warehouse', 'title']), 'created_at')
            ->when($q, fn ($w) => $w->where('actor', 'like', "%{$q}%"))
            ->orderByDesc('id')->limit($perStream)->get()
            ->map(fn ($t) => (object) [
                'at' => $t->created_at, 'stream' => 'STOCK',
                'what' => sprintf('%+d %s %s @ %s (bal %d) · %s', $t->delta, $t->stock_class, $t->title?->ntid, $t->warehouse?->name, $t->balance_after, $t->context),
                'actor' => $t->actor, 'chained' => false, 'intact' => null,
            ]);
        $auth = $span(AuthEvent::query(), 'created_at')
            ->when($q, fn ($w) => $w->where(fn ($x) => $x->where('event', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")))
            ->orderByDesc('id')->limit($perStream)->get()
            ->map(fn ($e) => (object) [
                'at' => $e->created_at, 'stream' => 'AUTH',
                'what' => str_replace('_', ' ', $e->event).' — '.$e->email.($e->ip ? ' from '.$e->ip : ''),
                'actor' => $e->email, 'chained' => false, 'intact' => null,
            ]);

        $events = $custody->concat($passport)->concat($stock)->concat($auth)->sortByDesc('at')->values();

        return $take ? $events->take($take)->values() : $events;
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->q);
        $events = $this->trail($request, 40, 120);

        return view('audit.index', compact('events', 'q'));
    }

    /** REP: full CSV export — same filters, no screen cap (per-stream safety limit 10 000). */
    public function export(Request $request)
    {
        $events = $this->trail($request, 10000, null);
        $csv = "at,stream,event,actor,chained,intact\n";
        foreach ($events as $e) {
            $csv .= sprintf("%s,%s,\"%s\",\"%s\",%s,%s\n",
                $e->at, $e->stream, str_replace('"', "'", $e->what), str_replace('"', "'", (string) $e->actor),
                $e->chained ? 'yes' : 'no', $e->intact === null ? '' : ($e->intact ? 'intact' : 'broken'));
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit-trail.csv"',
        ]);
    }
}
