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
                ->get(['ntid', 'title_en', 'title_fr', 'ministry', 'subject_code', 'grade_code', 'language'])
                ->map(fn ($t) => array_filter($t->toArray(), fn ($v) => $v !== null)),
        ]);
    }

    public function schools()
    {
        return response()->json([
            'dataset' => 'EduOS Cameroon — National School Directory (operational public schools)',
            'licence' => 'Open data — Ministry of Basic and Secondary Education',
            'generated_at' => now()->toIso8601String(),
            'count' => School::where('status', 'OPERATIONAL')->count(),
            'schools' => School::with('region')->where('status', 'OPERATIONAL')->orderBy('nsid')->get()
                ->map(fn ($s) => [
                    'nsid' => $s->nsid,
                    'name' => mb_convert_encoding($s->name_official, 'UTF-8', 'UTF-8'),
                    'ministry' => $s->ministry,
                    'type' => $s->school_type,
                    'region' => $s->region->name_en,
                ]),
        ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
    }
}
