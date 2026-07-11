<?php

namespace App\Http\Controllers;

use App\Modules\Custody\Models\NationalStat;
use App\Modules\Custody\Models\Shipment;
use App\Modules\Registry\Models\Region;

class DashboardController extends Controller
{
    public function index()
    {
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
}
