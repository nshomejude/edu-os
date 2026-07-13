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

    public function shipmentsCsv()
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['shipment_no', 'origin', 'destination', 'status', 'books', 'received', 'date']);
            foreach (\App\Modules\Custody\Models\Shipment::orderByDesc('id')->get() as $s) {
                fputcsv($out, [$s->shipment_no, $s->origin_name, $s->destination_name, $s->status, $s->books, $s->received_books, $s->shipped_on?->toDateString()]);
            }
            fclose($out);
        }, 'shipments.csv', ['Content-Type' => 'text/csv']);
    }

    public function stockCsv()
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['warehouse', 'ntid', 'class', 'quantity']);
            foreach (\App\Modules\Custody\Models\StockRecord::with(['warehouse', 'title'])->get() as $r) {
                fputcsv($out, [$r->warehouse?->name, $r->title?->ntid, $r->stock_class, $r->quantity]);
            }
            fclose($out);
        }, 'stock-position.csv', ['Content-Type' => 'text/csv']);
    }

    /** National statistics summary (open data). */
    public function stats()
    {
        return response()->json([
            'dataset' => 'EduOS Cameroon — National Statistics Summary',
            'generated_at' => now()->toIso8601String(),
            'books_printed' => (int) \App\Modules\Catalogue\Models\PrintBatch::sum('quantity'),
            'books_delivered' => (int) \App\Modules\Custody\Models\Shipment::whereNotNull('received_books')->sum('received_books'),
            'receipt_confirmation_rate_pct' => ($shipped = (int) \App\Modules\Custody\Models\Shipment::whereNotNull('received_books')->sum('books')) > 0
                ? round((int) \App\Modules\Custody\Models\Shipment::whereNotNull('received_books')->sum('received_books') / $shipped * 100, 1) : null,
            'operational_schools' => School::where('status', 'OPERATIONAL')->count(),
            'registered_learners' => \App\Modules\Registry\Models\Student::count(),
            'approved_titles' => TextbookTitle::where('status', 'APPROVED')->count(),
        ]);
    }

    /** Minimal OpenAPI 3.1 description of the public endpoints. */
    public function openapi()
    {
        return response()->json([
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'EduOS Cameroon Open Data API',
                'version' => '1.0.0',
                'description' => 'Public read-only national datasets: school directory, approved textbook catalogue, statistics summary.',
                'contact' => ['name' => 'Opesware Technologies', 'email' => 'eudos@opesware.com'],
            ],
            'paths' => [
                '/api/schools' => ['get' => ['summary' => 'National school directory (operational public schools)', 'parameters' => [['name' => 'page', 'in' => 'query', 'schema' => ['type' => 'integer']]], 'responses' => ['200' => ['description' => 'Paginated directory']]]],
                '/api/catalogue' => ['get' => ['summary' => 'Approved national textbook catalogue with current editions', 'parameters' => [['name' => 'page', 'in' => 'query', 'schema' => ['type' => 'integer']]], 'responses' => ['200' => ['description' => 'Paginated catalogue']]]],
                '/api/stats' => ['get' => ['summary' => 'National statistics summary', 'responses' => ['200' => ['description' => 'Summary object']]]],
            ],
        ]);
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
    /** Public QR verification (guest): is this a legitimate government copy? No school or learner data is exposed. */
    public function verify(\Illuminate\Http\Request $request)
    {
        $ncid = trim((string) $request->query('ncid'));
        $copy = null;
        $verdict = null;
        $chainIntact = null;
        if ($ncid !== '') {
            $copy = \App\Modules\Catalogue\Models\Copy::with('batch.title', 'batch.passportEvents')->where('ncid', $ncid)->first();
            if (! $copy) {
                $verdict = 'UNKNOWN';
            } elseif ($copy->batch->recalled_at || $copy->lifecycle_state === 'RECALLED') {
                $verdict = 'RECALLED';
            } elseif (in_array($copy->lifecycle_state, ['RETIRED', 'DISPOSED'])) {
                $verdict = 'WITHDRAWN';
            } elseif ($copy->lifecycle_state === 'LOST') {
                $verdict = 'REPORTED_LOST';
            } else {
                $verdict = 'AUTHENTIC';
                $chainIntact = $copy->batch->passportEvents->every(fn ($e) => ! $e->hash || $e->verifyChainLink());
            }
        }

        return view('verify', compact('ncid', 'copy', 'verdict', 'chainIntact'));
    }

}
