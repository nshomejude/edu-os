<?php

namespace App\Http\Controllers;

use App\Modules\Catalogue\Models\TextbookTitle;
use App\Modules\Custody\Models\StockRecord;
use App\Modules\Registry\Models\Enrolment;
use App\Modules\SchoolOps\Models\SchoolStock;

/**
 * Demand forecasting (Problems 1–7): validated enrolment × 1-book-per-learner
 * target vs stock on hand, per title and per school. The forecast is computed
 * live from the registries — no stale cascade reporting.
 */
class ForecastController extends Controller
{
    public function index()
    {
        $titles = TextbookTitle::where('status', 'APPROVED')->get();
        $rows = [];

        foreach ($titles as $title) {
            // learners in the title's grade, validated returns only (FR-NSR-03)
            $learners = Enrolment::where('class_level', $title->grade_code)
                ->where('validation_status', 'VALIDATED')
                ->selectRaw('school_id, sum(boys + girls) as n')->groupBy('school_id')->pluck('n', 'school_id');
            $need = $learners->sum();
            if ($need === 0) {
                continue;
            }
            $atSchools = SchoolStock::where('textbook_title_id', $title->id)->sum('quantity');
            $warehouse = StockRecord::where('textbook_title_id', $title->id)
                ->where('stock_class', 'AVAILABLE')->sum('quantity');
            $gap = $need - $atSchools;

            // Shortage schools: enrolled learners but no stock of this title
            $stocked = SchoolStock::where('textbook_title_id', $title->id)->pluck('school_id');
            $shortSchools = $learners->keys()->diff($stocked)->count();

            $rows[] = [
                'title' => $title,
                'need' => $need,
                'at_schools' => $atSchools,
                'gap' => $gap,
                'warehouse' => $warehouse,
                'procure' => max(0, $gap - $warehouse),
                'short_schools' => $shortSchools,
                'coverage' => $need > 0 ? round(min(100, $atSchools / $need * 100)) : 100,
            ];
        }
        usort($rows, fn ($a, $b) => $b['procure'] <=> $a['procure']);

        return view('forecast.index', ['rows' => $rows]);
    }
}
