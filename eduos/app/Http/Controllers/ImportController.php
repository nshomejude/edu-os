<?php

namespace App\Http\Controllers;

use App\Modules\Catalogue\Models\PrintBatch;
use App\Modules\Catalogue\Models\TextbookTitle;
use App\Modules\Custody\Models\StockRecord;
use App\Modules\Custody\Models\Warehouse;
use App\Modules\Registry\Models\Region;
use App\Modules\Registry\Models\School;
use App\Modules\SchoolOps\Models\SchoolStock;
use Illuminate\Http\Request;

/**
 * Migration tooling (FR-NSR-07, FR-NTR-MIG-01/02): staged CSV imports with a
 * row-level defect report; valid rows commit, defective rows are reported and
 * skipped; re-imports are idempotent.
 */
class ImportController extends Controller
{
    public function index()
    {
        return view('imports.index', ['report' => session('import_report')]);
    }

    private function rows(Request $request): array
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:10240']);
        $h = fopen($request->file('file')->getRealPath(), 'r');
        $header = array_map(fn ($c) => strtolower(trim($c)), fgetcsv($h) ?: []);
        $rows = [];
        while (($line = fgetcsv($h)) !== false) {
            if (count(array_filter($line, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }
            $rows[] = array_combine($header, array_pad(array_map('trim', $line), count($header), ''));
        }
        fclose($h);

        return $rows;
    }

    /** Carte scolaire import: name_official, ministry, school_type, region_code[, accessibility_class]. */
    public function schools(Request $request)
    {
        $regions = Region::pluck('id', 'code');
        $created = 0;
        $skipped = 0;
        $defects = [];
        foreach ($this->rows($request) as $i => $r) {
            $row = $i + 2;
            if (empty($r['name_official']) || strlen($r['name_official']) < 3) {
                $defects[] = "Row {$row}: name_official missing or too short.";
                continue;
            }
            if (! in_array($r['ministry'] ?? '', ['MINEDUB', 'MINESEC'])) {
                $defects[] = "Row {$row}: ministry must be MINEDUB or MINESEC.";
                continue;
            }
            if (! in_array($r['school_type'] ?? '', ['NURSERY', 'PRIMARY', 'GEN_SEC', 'TECH_SEC', 'COMBINED'])) {
                $defects[] = "Row {$row}: invalid school_type.";
                continue;
            }
            if (! isset($regions[$r['region_code'] ?? ''])) {
                $defects[] = "Row {$row}: unknown region_code '{$r['region_code']}'.";
                continue;
            }
            // idempotency: same official name in the same region is the same school
            if (School::where('region_id', $regions[$r['region_code']])->where('name_official', $r['name_official'])->exists()) {
                $skipped++;
                continue;
            }
            $minCode = $r['ministry'] === 'MINEDUB' ? 'B' : 'S';
            $typeCode = match ($r['school_type']) {
                'NURSERY' => 'N', 'PRIMARY' => 'P', 'GEN_SEC' => 'G', 'TECH_SEC' => 'T', default => 'C',
            };
            School::create([
                'nsid' => sprintf('CM-SCH-%s-0101-%s%s-%05d', $r['region_code'], $minCode, $typeCode, School::count() + 1),
                'name_official' => $r['name_official'], 'ministry' => $r['ministry'],
                'school_type' => $r['school_type'], 'region_id' => $regions[$r['region_code']],
                'accessibility_class' => in_array($r['accessibility_class'] ?? '', ['URBAN', 'RURAL_ROAD', 'RURAL_SEASONAL', 'REMOTE'])
                    ? $r['accessibility_class'] : 'URBAN',
            ]);
            $created++;
        }

        return back()->with('import_report', [
            'kind' => 'Schools (carte scolaire)', 'created' => $created, 'skipped' => $skipped, 'defects' => $defects,
        ]);
    }

    /** Title catalogue import: title_en|title_fr, ministry, subject_code, grade_code, language. */
    public function titles(Request $request)
    {
        $created = 0;
        $skipped = 0;
        $defects = [];
        foreach ($this->rows($request) as $i => $r) {
            $row = $i + 2;
            $name = $r['title_en'] ?: ($r['title_fr'] ?? '');
            if ($name === '') {
                $defects[] = "Row {$row}: title_en or title_fr required.";
                continue;
            }
            if (! in_array($r['ministry'] ?? '', ['MINEDUB', 'MINESEC']) || ! in_array($r['language'] ?? '', ['EN', 'FR', 'BI'])
                || strlen($r['subject_code'] ?? '') !== 3 || empty($r['grade_code'])) {
                $defects[] = "Row {$row}: ministry/subject_code(3)/grade_code/language invalid.";
                continue;
            }
            $exists = TextbookTitle::where('subject_code', strtoupper($r['subject_code']))
                ->where('grade_code', strtoupper($r['grade_code']))->where('language', $r['language'])
                ->where(fn ($q) => $q->where('title_en', $name)->orWhere('title_fr', $name))->exists();
            if ($exists) {
                $skipped++;
                continue;
            }
            $min = $r['ministry'] === 'MINEDUB' ? 'B' : 'S';
            $seq = TextbookTitle::where(['ministry' => $r['ministry'], 'subject_code' => strtoupper($r['subject_code']),
                'grade_code' => strtoupper($r['grade_code']), 'language' => $r['language']])->count() + 1;
            TextbookTitle::create([
                'ntid' => sprintf('CM-TB-%s-%s-%s-%s-%04d-01', $min, strtoupper($r['subject_code']), strtoupper($r['grade_code']), $r['language'], $seq),
                'title_en' => $r['title_en'] ?: null, 'title_fr' => $r['title_fr'] ?: null,
                'ministry' => $r['ministry'], 'subject_code' => strtoupper($r['subject_code']),
                'grade_code' => strtoupper($r['grade_code']), 'language' => $r['language'],
                'status' => 'APPROVED',   // migrated catalogue is the active approved catalogue
            ]);
            $created++;
        }

        return back()->with('import_report', [
            'kind' => 'Textbook titles', 'created' => $created, 'skipped' => $skipped, 'defects' => $defects,
        ]);
    }

    /** Brownfield stock (FR-NTR-MIG-02): target_type, target_id, ntid, quantity[, condition]. */
    public function stock(Request $request)
    {
        $created = 0;
        $defects = [];
        foreach ($this->rows($request) as $i => $r) {
            $row = $i + 2;
            $qty = (int) ($r['quantity'] ?? 0);
            $title = TextbookTitle::where('ntid', $r['ntid'] ?? '')->first();
            if (! $title) {
                $defects[] = "Row {$row}: unknown ntid '{$r['ntid']}'.";
                continue;
            }
            if ($qty < 1 || $qty > 500000) {
                $defects[] = "Row {$row}: quantity out of range.";
                continue;
            }
            $type = strtoupper($r['target_type'] ?? '');
            if ($type === 'WAREHOUSE') {
                $wh = Warehouse::where('wh_id', $r['target_id'] ?? '')->first();
                if (! $wh) {
                    $defects[] = "Row {$row}: unknown warehouse '{$r['target_id']}'.";
                    continue;
                }
                StockRecord::post($wh->id, $title->id, 'AVAILABLE', $qty);
            } elseif ($type === 'SCHOOL') {
                $school = School::where('nsid', $r['target_id'] ?? '')->first();
                if (! $school) {
                    $defects[] = "Row {$row}: unknown school NSID '{$r['target_id']}'.";
                    continue;
                }
                SchoolStock::create([
                    'school_id' => $school->id, 'textbook_title_id' => $title->id,
                    'quantity' => $qty,
                    'condition' => in_array($r['condition'] ?? '', ['GOOD', 'FAIR', 'POOR']) ? $r['condition'] : 'GOOD',
                ]);
            } else {
                $defects[] = "Row {$row}: target_type must be WAREHOUSE or SCHOOL.";
                continue;
            }
            // brownfield batch registration keeps passport lineage (upgradeable to per-copy later)
            PrintBatch::create([
                'batch_no' => sprintf('BAT-MIG-%05d', PrintBatch::count() + 1),
                'textbook_title_id' => $title->id, 'printer' => 'BROWNFIELD (pre-system stock)',
                'quantity' => $qty, 'qa_status' => 'PASSED', 'received_qty' => $qty,
            ]);
            $created++;
        }

        return back()->with('import_report', [
            'kind' => 'Brownfield stock', 'created' => $created, 'skipped' => 0, 'defects' => $defects,
        ]);
    }
}
