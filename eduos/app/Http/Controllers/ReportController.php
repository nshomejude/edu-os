<?php

namespace App\Http\Controllers;

use App\Modules\Custody\Models\Shipment;
use App\Modules\Custody\Models\StockRecord;
use App\Modules\Registry\Models\Region;
use App\Modules\Registry\Models\School;

class ReportController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $regions = Region::orderByDesc('books_distributed')->get();

        // Drill-down: region → divisions → school stock coverage
        $drill = null;
        if ($request->region) {
            $region = Region::where('code', $request->region)->first();
            if ($region) {
                $drill = [
                    'region' => $region,
                    'divisions' => \App\Modules\Registry\Models\Division::where('region_id', $region->id)
                        ->with(['subdivisions.schools'])->get()
                        ->map(function ($d) {
                            $schools = $d->subdivisions->flatMap->schools;
                            $ids = $schools->pluck('id');
                            return [
                                'name' => $d->name,
                                'schools' => $schools->count(),
                                'stock' => \App\Modules\SchoolOps\Models\SchoolStock::whereIn('school_id', $ids)->sum('quantity'),
                                'learners' => \App\Modules\Registry\Models\Enrolment::whereIn('school_id', $ids)
                                    ->where('validation_status', 'VALIDATED')->sum(\Illuminate\Support\Facades\DB::raw('boys + girls')),
                            ];
                        }),
                ];
            }
        }

        // Delivery performance by status
        $byStatus = Shipment::selectRaw('status, count(*) as n, sum(books) as books')
            ->groupBy('status')->orderByDesc('n')->get();

        // Loss / discrepancy analysis (feeds OUT-1 verification)
        $discrepancies = Shipment::where('status', 'RECEIVED_WITH_DISCREPANCY')
            ->with('destinationSchool')->orderByDesc('shipped_on')->get();
        $totalShipped = (int) Shipment::whereNotNull('received_books')->sum('books');
        $totalReceived = (int) Shipment::whereNotNull('received_books')->sum('received_books');
        $confirmRate = $totalShipped > 0 ? round($totalReceived / $totalShipped * 100, 1) : null;

        // Stock position by class
        $stockByClass = StockRecord::selectRaw('stock_class, sum(quantity) as qty')
            ->groupBy('stock_class')->pluck('qty', 'stock_class');

        // Coverage proxy: schools with any stock
        $schoolsTotal = School::count();
        $schoolsServed = \App\Modules\SchoolOps\Models\SchoolStock::distinct('school_id')->count('school_id');

        return view('reports.index', compact(
            'regions', 'byStatus', 'discrepancies', 'confirmRate',
            'totalShipped', 'totalReceived', 'stockByClass', 'schoolsTotal', 'schoolsServed', 'drill'
        ));
    }
    /** REP-01: coverage drill-down — learner-to-book ratio, national → region → school. */
    public function coverage(\Illuminate\Http\Request $request)
    {
        $learnersBySchool = \App\Modules\Registry\Models\Enrolment::where('validation_status', 'VALIDATED')
            ->selectRaw('school_id, sum(boys + girls) n')->groupBy('school_id')->pluck('n', 'school_id');
        $booksBySchool = \App\Modules\SchoolOps\Models\SchoolStock::selectRaw('school_id, sum(quantity) n')
            ->groupBy('school_id')->pluck('n', 'school_id');
        $pct = fn ($books, $learners) => $learners > 0 ? round($books / $learners * 100, 1) : 100.0;

        $region = $request->region ? Region::where('code', $request->region)->first() : null;

        if ($region) {
            $rows = School::where('region_id', $region->id)->orderBy('name_official')->get()
                ->map(fn ($s) => (object) [
                    'id' => $s->id, 'name' => $s->name_official,
                    'learners' => (int) ($learnersBySchool[$s->id] ?? 0),
                    'books' => (int) ($booksBySchool[$s->id] ?? 0),
                ])
                ->filter(fn ($r) => $r->learners > 0 || $r->books > 0)
                ->each(function ($r) use ($pct) {
                    $r->ratio = $pct($r->books, $r->learners);
                    $r->shortfall = max(0, $r->learners - $r->books);
                })->sortBy('ratio')->values();
        } else {
            $schoolRegion = School::pluck('region_id', 'id');
            $agg = [];
            foreach (Region::orderBy('name_en')->get() as $reg) {
                $agg[$reg->id] = (object) ['code' => $reg->code, 'name' => $reg->name_en, 'learners' => 0, 'books' => 0];
            }
            foreach ($learnersBySchool as $sid => $n) {
                $rid = $schoolRegion[$sid] ?? null;
                if ($rid && isset($agg[$rid])) {
                    $agg[$rid]->learners += (int) $n;
                }
            }
            foreach ($booksBySchool as $sid => $n) {
                $rid = $schoolRegion[$sid] ?? null;
                if ($rid && isset($agg[$rid])) {
                    $agg[$rid]->books += (int) $n;
                }
            }
            $rows = collect(array_values($agg))->each(function ($r) use ($pct) {
                $r->ratio = $pct($r->books, $r->learners);
                $r->shortfall = max(0, $r->learners - $r->books);
            })->sortBy('ratio')->values();
        }

        $totals = ['learners' => (int) $learnersBySchool->sum(), 'books' => (int) $booksBySchool->sum()];
        $totals['ratio'] = $totals['learners'] > 0 ? round($totals['books'] / $totals['learners'] * 100, 1) : 100.0;
        $chart = $region ? [] : $rows->take(10)->mapWithKeys(fn ($r) => [$r->name => $r->ratio])->all();

        return view('reports.coverage', compact('rows', 'region', 'totals', 'chart'));
    }

    /** Campaign fulfilment: allocated vs shipped vs received, delivery speed, open discrepancies. */
    public function campaignPerformance()
    {
        $rows = \App\Modules\Planning\Models\DistributionCampaign::with('allocations.shipment')->orderByDesc('id')->get()
            ->map(function ($c) {
                $shipments = $c->allocations->pluck('shipment')->filter();
                $received = $shipments->whereNotNull('received_books');
                $days = $received
                    ->map(fn ($s) => $s->shipped_on ? $s->shipped_on->diffInDays($s->updated_at) : null)
                    ->filter(fn ($v) => $v !== null);
                $allocated = (int) $c->allocations->sum('quantity');

                return (object) [
                    'id' => $c->id, 'name' => $c->name, 'year' => $c->academic_year, 'status' => $c->status,
                    'allocated' => $allocated,
                    'shipped' => (int) $shipments->sum('books'),
                    'received' => (int) $received->sum('received_books'),
                    'fulfilment' => $allocated > 0 ? round($received->sum('received_books') / $allocated * 100, 1) : 0,
                    'avgDays' => $days->isNotEmpty() ? round($days->avg(), 1) : null,
                    'discrepancies' => $shipments->where('status', 'RECEIVED_WITH_DISCREPANCY')->whereNull('resolved_at')->count(),
                ];
            });

        return view('reports.campaigns', compact('rows'));
    }

    /** Loss analytics per destination region + supplier scorecards. */
    public function performance()
    {
        $done = Shipment::whereNotNull('received_books')->get();
        $regionOf = School::pluck('region_id', 'id');
        $regions = Region::pluck('name_en', 'id');
        $agg = [];
        foreach ($done as $s) {
            $key = $s->destination_school_id
                ? ($regions[$regionOf[$s->destination_school_id] ?? 0] ?? 'Inter-warehouse')
                : 'Inter-warehouse';
            $agg[$key] = $agg[$key] ?? (object) ['region' => $key, 'shipped' => 0, 'received' => 0];
            $agg[$key]->shipped += (int) $s->books;
            $agg[$key]->received += (int) $s->received_books;
        }
        $lanes = collect(array_values($agg))->each(function ($l) {
            $l->lost = max(0, $l->shipped - $l->received);
            $l->rate = $l->shipped > 0 ? round($l->lost / $l->shipped * 100, 1) : 0;
        })->sortByDesc('rate')->values();
        $lossChart = $lanes->take(10)->mapWithKeys(fn ($l) => [$l->region => $l->rate])->all();

        $suppliers = \App\Modules\Catalogue\Models\Supplier::all()->map(function ($sup) {
            $orders = \App\Modules\Catalogue\Models\ProcurementOrder::where('supplier_id', $sup->id)->get();
            if ($orders->isEmpty()) {
                return null;
            }
            $delivered = $orders->where('status', 'DELIVERED');
            $qty = (int) $delivered->sum('quantity');

            return (object) [
                'id' => $sup->id, 'name' => $sup->name,
                'orders' => $orders->count(), 'qty' => $qty,
                'value' => (int) $orders->sum(fn ($o) => $o->quantity * $o->unit_price_fcfa),
                'damageRate' => $qty > 0 ? round($delivered->sum('damaged_qty') / $qty * 100, 1) : 0,
                'leadDays' => $delivered->isNotEmpty()
                    ? round($delivered->avg(fn ($o) => $o->created_at->diffInDays($o->updated_at)), 1) : null,
            ];
        })->filter()->sortByDesc('qty')->values();

        $lostCopies = \App\Modules\Catalogue\Models\Copy::where('lifecycle_state', 'LOST')->count();
        $lostAssignments = \App\Modules\SchoolOps\Models\Assignment::where('status', 'LOST')->count();

        return view('reports.performance', compact('lanes', 'lossChart', 'suppliers', 'lostCopies', 'lostAssignments'));
    }

}
