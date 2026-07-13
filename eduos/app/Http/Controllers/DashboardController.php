<?php

namespace App\Http\Controllers;

use App\Modules\Custody\Models\NationalStat;
use App\Modules\Custody\Models\Shipment;
use App\Modules\Registry\Models\Region;

class DashboardController extends Controller
{
    public function index()
    {
        // DASH-02..07: operational roles get scoped dashboards; national roles keep DASH-01
        $role = auth()->user()->role ?? 'ADMIN';
        if (! in_array($role, ['ADMIN', 'PROGRAMME_ADMIN', 'AUDITOR', 'READONLY'])) {
            return $this->roleDashboard($role);
        }
        // Live KPIs from the ledger (seeded NationalStat remains only as a fallback)
        $printed = (int) \App\Modules\Catalogue\Models\PrintBatch::sum('quantity');
        $transit = (int) Shipment::whereIn('status', ['DISPATCHED', 'IN_TRANSIT', 'ARRIVED'])->sum('books');
        $delivered = (int) Shipment::whereNotNull('received_books')->sum('received_books');
        $pending = (int) Shipment::whereIn('status', ['CONFIRMED', 'LOADED'])->sum('books');
        $mk = fn ($v, $d = null) => (object) ['value' => $v, 'delta_pct' => $d];
        $stats = collect([
            'total_textbooks' => $mk($printed),
            'in_transit' => $mk($transit, $printed ? round($transit / $printed * 100, 1) : 0),
            'delivered' => $mk($delivered, $printed ? round($delivered / $printed * 100, 1) : 0),
            'pending' => $mk($pending, $printed ? round($pending / $printed * 100, 1) : 0),
        ]);
        if ($printed === 0) {
            $stats = NationalStat::all()->keyBy('key');
        }

        // Live regional distribution: received books by destination-school region
        $live = Shipment::whereNotNull('received_books')
            ->join('schools', 'shipments.destination_school_id', '=', 'schools.id')
            ->selectRaw('schools.region_id, sum(received_books) n')->groupBy('schools.region_id')->pluck('n', 'region_id');
        $regions = Region::orderByDesc('books_distributed')->limit(9)->get()
            ->each(function ($r) use ($live) {
                // seeded historical base + live ledger movements
                $r->books_distributed += (int) ($live[$r->id] ?? 0);
            })->sortByDesc('books_distributed')->values();
        $shipments = Shipment::orderByDesc('shipment_no')->limit(5)->get();

        $total = max(1, (int) ($stats['delivered']->value ?? 0) + (int) ($stats['in_transit']->value ?? 0) + (int) ($stats['pending']->value ?? 0));
        $deliveredPct = round(($stats['delivered']->value ?? 0) / $total * 100, 1);
        $transitPct = round(($stats['in_transit']->value ?? 0) / $total * 100, 1);
        $pendingPct = round(($stats['pending']->value ?? 0) / $total * 100, 1);

        return view('dashboard', compact(
            'stats', 'regions', 'shipments', 'deliveredPct', 'transitPct', 'pendingPct'
        ));
    }

    /** Role-scoped dashboards (spec screens 8–13). */
    private function roleDashboard(string $role)
    {
        $u = auth()->user();
        $d = ['role' => $role, 'kpis' => [], 'panels' => []];

        if (in_array($role, ['PROCUREMENT_OFFICER'])) {
            $orders = \App\Modules\Catalogue\Models\ProcurementOrder::with(['supplier', 'title'])->orderByDesc('id');
            $d['title'] = 'Procurement Dashboard';
            $d['kpis'] = [
                'Open orders' => (clone $orders)->where('status', '!=', 'DELIVERED')->count(),
                'Delivered' => (clone $orders)->where('status', 'DELIVERED')->count(),
                'Order value (FCFA)' => number_format(\App\Modules\Catalogue\Models\ProcurementOrder::selectRaw('sum(quantity * unit_price_fcfa) v')->value('v') ?? 0),
                'Suppliers' => \App\Modules\Catalogue\Models\Supplier::count(),
            ];
            $d['chart'] = ['heading' => 'Order value by supplier (FCFA)',
                'data' => \App\Modules\Catalogue\Models\ProcurementOrder::with('supplier')->get()
                    ->groupBy(fn ($o) => $o->supplier->name)->map(fn ($g) => $g->sum(fn ($o) => $o->quantity * $o->unit_price_fcfa))->sortDesc()->take(6)->all()];
            $d['panels'][] = ['heading' => 'Recent orders', 'link' => route('procurement.index'),
                'rows' => $orders->limit(8)->get()->map(fn ($o) => [$o->order_no, $o->supplier->name, $o->title->ntid, number_format($o->quantity), $o->status])];
        } elseif (in_array($role, ['WAREHOUSE_MANAGER', 'STOREKEEPER', 'WAREHOUSE_OFFICER'])) {
            $whFilter = fn ($q) => $u->warehouse_id ? $q->where('warehouse_id', $u->warehouse_id) : $q;
            $d['title'] = 'Warehouse Operations Dashboard';
            $d['kpis'] = [
                'Available stock' => number_format($whFilter(\App\Modules\Custody\Models\StockRecord::where('stock_class', 'AVAILABLE'))->sum('quantity')),
                'Awaiting approval' => Shipment::where('status', 'CONFIRMED')->whereNull('approved_at')->count(),
                'Ready to dispatch' => Shipment::where('status', 'CONFIRMED')->whereNotNull('approved_at')->count(),
                'In quarantine' => number_format($whFilter(\App\Modules\Custody\Models\StockRecord::where('stock_class', 'QUARANTINE'))->sum('quantity')),
            ];
            $d['chart'] = ['heading' => 'Stock position by class',
                'data' => $whFilter(\App\Modules\Custody\Models\StockRecord::query())
                    ->selectRaw('stock_class, sum(quantity) v')->groupBy('stock_class')->pluck('v', 'stock_class')->all()];
            $d['panels'][] = ['heading' => 'Open shipments', 'link' => route('shipments.index'),
                'rows' => Shipment::whereNotIn('status', ['CLOSED', 'RECEIVED_FULL', 'CANCELLED'])->orderByDesc('id')->limit(8)->get()
                    ->map(fn ($s) => [$s->shipment_no, $s->origin_name, $s->destination_name, number_format($s->books), $s->statusLabel()])];
        } elseif ($role === 'TRANSPORT_OFFICER') {
            $d['title'] = 'Logistics Control Dashboard';
            $d['kpis'] = [
                'Trips en route' => \App\Modules\Logistics\Models\Trip::where('status', 'EN_ROUTE')->count(),
                'Incidents open' => \App\Modules\Logistics\Models\Trip::where('status', 'INCIDENT')->count(),
                'Vehicles free' => \App\Modules\Logistics\Models\Vehicle::where('status', 'AVAILABLE')->count(),
                'Drivers free' => \App\Modules\Logistics\Models\Driver::where('status', 'AVAILABLE')->count(),
            ];
            $d['chart'] = ['heading' => 'Trips by status',
                'data' => \App\Modules\Logistics\Models\Trip::selectRaw('status, count(*) v')->groupBy('status')->pluck('v', 'status')->all()];
            $d['panels'][] = ['heading' => 'Active trips', 'link' => route('logistics.index'),
                'rows' => \App\Modules\Logistics\Models\Trip::with(['shipment', 'vehicle', 'driver'])->whereIn('status', ['EN_ROUTE', 'INCIDENT'])->limit(8)->get()
                    ->map(fn ($t) => [$t->shipment->shipment_no, $t->vehicle->plate ?? '—', $t->driver->name ?? '—', $t->status, $t->departed_at?->format('d M H:i')])];
        } elseif (in_array($role, ['SCHOOL_HEAD', 'TEACHER'])) {
            $sid = $u->school_id;
            $d['title'] = 'School Delivery Dashboard';
            $d['kpis'] = [
                'Expected deliveries' => Shipment::where('destination_school_id', $sid)->whereIn('status', ['CONFIRMED', 'DISPATCHED', 'IN_TRANSIT'])->count(),
                'Books on hand' => number_format(\App\Modules\SchoolOps\Models\SchoolStock::where('school_id', $sid)->sum('quantity')),
                'Open assignments' => \App\Modules\SchoolOps\Models\Assignment::where('school_id', $sid)->where('status', 'ASSIGNED')->count(),
                'Learners' => number_format(\App\Modules\Registry\Models\Student::where('school_id', $sid)->count()),
            ];
            $d['chart'] = ['heading' => 'Books on hand by title',
                'data' => \App\Modules\SchoolOps\Models\SchoolStock::with('title')->where('school_id', $sid)->get()
                    ->groupBy(fn ($s) => $s->title?->ntid ?? '—')->map->sum('quantity')->sortDesc()->take(6)->all()];
            $d['panels'][] = ['heading' => 'Inbound shipments', 'link' => $sid ? route('schools.show', $sid) : route('shipments.index'),
                'rows' => Shipment::where('destination_school_id', $sid)->orderByDesc('id')->limit(8)->get()
                    ->map(fn ($s) => [$s->shipment_no, $s->origin_name, number_format($s->books), $s->statusLabel(), $s->shipped_on->format('d M Y')])];
        } elseif (in_array($role, ['INSPECTOR'])) {
            $d['title'] = 'Verification & Audit Dashboard';
            $d['kpis'] = [
                'Unresolved findings' => \App\Modules\SchoolOps\Models\Inspection::whereNull('resolved_at')->where('outcome', '!=', 'CONFORM')->count(),
                'Inspections total' => \App\Modules\SchoolOps\Models\Inspection::count(),
                'Open discrepancies' => Shipment::where('status', 'RECEIVED_WITH_DISCREPANCY')->whereNull('resolved_at')->count(),
                'Critical alerts' => \App\Modules\Platform\Models\Alert::where('severity', 'CRITICAL')->whereNull('read_at')->count(),
            ];
            $d['chart'] = ['heading' => 'Inspection outcomes',
                'data' => \App\Modules\SchoolOps\Models\Inspection::selectRaw('outcome, count(*) v')->groupBy('outcome')->pluck('v', 'outcome')->all()];
            $d['panels'][] = ['heading' => 'Latest inspections', 'link' => route('inspections.index'),
                'rows' => \App\Modules\SchoolOps\Models\Inspection::with('school')->orderByDesc('inspected_on')->limit(8)->get()
                    ->map(fn ($i) => [$i->inspected_on->format('d M Y'), $i->school->name_official, $i->variance(), str_replace('_', ' ', $i->outcome), $i->resolved_at ? 'Resolved' : 'Open'])];
        } else {   // DIVISION_OFFICER, SUBDIV_OFFICER, CURRICULUM_OFFICER — scoped to the officer's division region when set
            $regionId = $u->division_id ? \Illuminate\Support\Facades\DB::table('divisions')->where('id', $u->division_id)->value('region_id') : null;
            $schoolIds = $regionId ? \App\Modules\Registry\Models\School::where('region_id', $regionId)->pluck('id') : null;
            $scopeSchool = fn ($q) => $schoolIds !== null ? $q->whereIn('school_id', $schoolIds) : $q;
            $region = $regionId ? Region::find($regionId) : null;
            $d['title'] = 'Regional Oversight Dashboard'.($region ? ' — '.$region->name_en : '');
            $d['kpis'] = [
                'Pending enrolment validations' => $scopeSchool(\App\Modules\Registry\Models\Enrolment::where('validation_status', 'SUBMITTED'))->count(),
                'Redistribution proposals' => \App\Modules\Custody\Models\RedistributionProposal::where('status', 'PROPOSED')->count(),
                'Open discrepancies' => Shipment::where('status', 'RECEIVED_WITH_DISCREPANCY')->whereNull('resolved_at')
                    ->when($schoolIds !== null, fn ($q) => $q->whereIn('destination_school_id', $schoolIds))->count(),
                'Operational schools' => \App\Modules\Registry\Models\School::where('status', 'OPERATIONAL')
                    ->when($regionId, fn ($q) => $q->where('region_id', $regionId))->count(),
            ];
            $d['chart'] = ['heading' => 'Validated learners by class level'.($region ? ' — '.$region->name_en : ''),
                'data' => $scopeSchool(\App\Modules\Registry\Models\Enrolment::where('validation_status', 'VALIDATED'))
                    ->selectRaw('class_level, sum(boys + girls) v')->groupBy('class_level')->orderBy('class_level')->pluck('v', 'class_level')->all()];
            $d['panels'][] = ['heading' => 'Enrolment returns awaiting validation', 'link' => route('schools.index'),
                'rows' => $scopeSchool(\App\Modules\Registry\Models\Enrolment::with('school')->where('validation_status', 'SUBMITTED'))->limit(8)->get()
                    ->map(fn ($e) => [$e->school->name_official, $e->class_level, $e->boys + $e->girls, $e->academic_year, 'SUBMITTED'])];
        }

        return view('dashboard-role', $d);
    }
}
