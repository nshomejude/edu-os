<?php

namespace App\Http\Controllers;

use App\Modules\Catalogue\Models\TextbookTitle;
use App\Modules\Registry\Models\School;

/**
 * Public read-only national APIs — FR-NTR-13 (catalogue) and FR-NSR-05 (directory).
 * Unauthenticated by design: these are the first open datasets of the platform.
 */
class PublicApiController extends Controller
{
    public function catalogue()
    {
        return response()->json([
            'dataset' => 'EduOS Cameroon — Approved National Textbook Catalogue',
            'licence' => 'Open data — Ministry of Basic and Secondary Education',
            'generated_at' => now()->toIso8601String(),
            'count' => TextbookTitle::where('status', 'APPROVED')->count(),
            'titles' => TextbookTitle::where('status', 'APPROVED')->orderBy('ntid')
                ->paginate(100)->through(fn ($t) => array_filter([
                    'ntid' => $t->ntid, 'title_en' => $t->title_en, 'title_fr' => $t->title_fr,
                    'ministry' => $t->ministry, 'subject_code' => $t->subject_code,
                    'grade_code' => $t->grade_code, 'language' => $t->language,
                    'current_edition' => \App\Modules\Catalogue\Models\Edition::where('textbook_title_id', $t->id)
                        ->where('superseded', false)->value('edition_no'),
                ], fn ($v) => $v !== null)),
        ]);
    }

    public function schools()
    {
        return response()->json([
            'dataset' => 'EduOS Cameroon — National School Directory (operational public schools)',
            'licence' => 'Open data — Ministry of Basic and Secondary Education',
            'generated_at' => now()->toIso8601String(),
            'count' => School::where('status', 'OPERATIONAL')->count(),
            'schools' => School::with('region')->where('status', 'OPERATIONAL')->orderBy('nsid')
                ->paginate(100)->through(fn ($s) => [
                    'nsid' => $s->nsid,
                    'name' => mb_convert_encoding($s->name_official, 'UTF-8', 'UTF-8'),
                    'ministry' => $s->ministry,
                    'type' => $s->school_type,
                    'region' => $s->region->name_en,
                ]),
        ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
    }

    /** RPT-COV as CSV — the FRS export requirement, openly downloadable for authorized users. */
    public function coverageCsv()
    {
        $rows = \App\Modules\Registry\Models\Region::orderByDesc('books_distributed')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['region_code', 'region_en', 'books_distributed']);
            foreach ($rows as $r) {
                fputcsv($out, [$r->code, $r->name_en, $r->books_distributed]);
            }
            fclose($out);
        }, 'rpt-cov-coverage.csv', ['Content-Type' => 'text/csv']);
    }
}
