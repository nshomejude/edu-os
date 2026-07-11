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

        // RPT-RPL — replacement planning from per-copy condition & lifecycle data (FR-NTR-16)
        $replacement = \App\Modules\Catalogue\Models\Copy::query()
            ->join('print_batches', 'copies.print_batch_id', '=', 'print_batches.id')
            ->join('textbook_titles', 'print_batches.textbook_title_id', '=', 'textbook_titles.id')
            ->selectRaw("textbook_titles.ntid,
                sum(case when copies.lifecycle_state = 'UNDER_REPAIR' then 1 else 0 end) as under_repair,
                sum(case when copies.lifecycle_state in ('RETIRED','DISPOSED') then 1 else 0 end) as retired,
                sum(case when copies.lifecycle_state = 'LOST' then 1 else 0 end) as lost,
                sum(case when copies.condition in ('POOR','UNUSABLE') and copies.lifecycle_state not in ('RETIRED','DISPOSED') then 1 else 0 end) as poor,
                count(*) as total")
            ->groupBy('textbook_titles.ntid')->get()
            ->map(function ($r) {
                $r->replace_now = $r->retired + $r->lost + $r->poor;
                return $r;
            });

        return view('forecast.index', ['rows' => $rows, 'replacement' => $replacement]);
    }
}
