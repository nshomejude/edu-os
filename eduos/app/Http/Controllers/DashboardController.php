<?php

namespace App\Http\Controllers;

use App\Modules\Custody\Models\NationalStat;
use App\Modules\Custody\Models\Shipment;
use App\Modules\Registry\Models\Region;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = NationalStat::all()->keyBy('key');
        $regions = Region::orderByDesc('books_distributed')->limit(9)->get();
        $shipments = Shipment::orderByDesc('shipment_no')->limit(5)->get();

        $total = max(1, (int) ($stats['total_textbooks']->value ?? 0));
        $deliveredPct = round(($stats['delivered']->value ?? 0) / $total * 100, 1);
        $transitPct = round(($stats['in_transit']->value ?? 0) / $total * 100, 1);
        $pendingPct = round(($stats['pending']->value ?? 0) / $total * 100, 1);

        return view('dashboard', compact(
            'stats', 'regions', 'shipments', 'deliveredPct', 'transitPct', 'pendingPct'
        ));
    }
}
