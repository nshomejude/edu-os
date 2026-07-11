<?php

namespace App\Http\Controllers;

use App\Modules\Custody\Models\Shipment;
use App\Modules\Custody\Models\StockRecord;
use App\Modules\Registry\Models\Region;
use App\Modules\Registry\Models\School;

class ReportController extends Controller
{
    public function index()
    {
        $regions = Region::orderByDesc('books_distributed')->get();

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
            'totalShipped', 'totalReceived', 'stockByClass', 'schoolsTotal', 'schoolsServed'
        ));
    }
}
