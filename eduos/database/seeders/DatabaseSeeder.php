<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Catalogue\Models\PassportEvent;
use App\Modules\Catalogue\Models\PrintBatch;
use App\Modules\Catalogue\Models\TextbookTitle;
use App\Modules\Custody\Models\CustodyEvent;
use App\Modules\Custody\Models\NationalStat;
use App\Modules\Custody\Models\Shipment;
use App\Modules\Custody\Models\StockRecord;
use App\Modules\Custody\Models\Warehouse;
use App\Modules\Platform\Models\Alert;
use App\Modules\Registry\Models\Enrolment;
use App\Modules\Registry\Models\Region;
use App\Modules\Registry\Models\School;
use App\Modules\SchoolOps\Models\SchoolStock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /** Demo dataset for Phase-I flows; aggregates become event projections later. */
    public function run(): void
    {
        // ---- Users -------------------------------------------------------
        User::create(['name' => 'Admin', 'email' => 'admin@minedub.cm', 'password' => Hash::make('password'), 'role' => 'ADMIN', 'ministry' => 'MINEDUB']);
        User::create(['name' => 'Paul Mbarga', 'email' => 'warehouse@minedub.cm', 'password' => Hash::make('password'), 'role' => 'WAREHOUSE_OFFICER', 'ministry' => 'MINEDUB']);
        User::create(['name' => 'Grace Nfor', 'email' => 'school@minesec.cm', 'password' => Hash::make('password'), 'role' => 'SCHOOL_HEAD', 'ministry' => 'MINESEC']);

        // ---- Regions ------------------------------------------------------
        $regions = [
            ['code' => 'CE', 'name_en' => 'Centre',     'name_fr' => 'Centre',       'books_distributed' => 198450],
            ['code' => 'LT', 'name_en' => 'Littoral',   'name_fr' => 'Littoral',     'books_distributed' => 176230],
            ['code' => 'OU', 'name_en' => 'West',       'name_fr' => 'Ouest',        'books_distributed' => 154600],
            ['code' => 'NO', 'name_en' => 'North',      'name_fr' => 'Nord',         'books_distributed' => 142380],
            ['code' => 'SU', 'name_en' => 'South',      'name_fr' => 'Sud',          'books_distributed' => 123760],
            ['code' => 'EN', 'name_en' => 'Far North',  'name_fr' => 'Extrême-Nord', 'books_distributed' => 110760],
            ['code' => 'NW', 'name_en' => 'North West', 'name_fr' => 'Nord-Ouest',   'books_distributed' => 98520],
            ['code' => 'SW', 'name_en' => 'South West', 'name_fr' => 'Sud-Ouest',    'books_distributed' => 78300],
            ['code' => 'AD', 'name_en' => 'Adamawa',    'name_fr' => 'Adamaoua',     'books_distributed' => 42000],
            ['code' => 'ES', 'name_en' => 'East',       'name_fr' => 'Est',          'books_distributed' => 38000],
        ];
        $regionModels = [];
        foreach ($regions as $r) {
            $regionModels[$r['code']] = Region::create($r);
        }

        // ---- Schools (3 per region) ---------------------------------------
        $townByRegion = [
            'CE' => ['Yaoundé', 'Mbalmayo', 'Obala'], 'LT' => ['Douala', 'Nkongsamba', 'Edéa'],
            'OU' => ['Bafoussam', 'Dschang', 'Mbouda'], 'NO' => ['Garoua', 'Guider', 'Poli'],
            'SU' => ['Ebolowa', 'Kribi', 'Sangmélima'], 'EN' => ['Maroua', 'Kousséri', 'Mokolo'],
            'NW' => ['Bamenda', 'Kumbo', 'Wum'], 'SW' => ['Buea', 'Kumba', 'Limbe'],
            'AD' => ['Ngaoundéré', 'Meiganga', 'Tibati'], 'ES' => ['Bertoua', 'Batouri', 'Abong-Mbang'],
        ];
        $accessCycle = ['URBAN', 'RURAL_ROAD', 'RURAL_SEASONAL', 'REMOTE'];
        $seq = 1;
        $schools = [];
        foreach ($townByRegion as $code => $towns) {
            foreach ($towns as $i => $town) {
                $isPrimary = ($i % 2 === 0);
                $schools[] = School::create([
                    'nsid' => sprintf('CM-SCH-%s-%02d%02d-%s-%05d', $code, $i + 1, $i + 2, $isPrimary ? 'BP' : 'SG', $seq++),
                    'name_official' => $isPrimary ? "École Publique de {$town}" : "Government Bilingual High School {$town}",
                    'ministry' => $isPrimary ? 'MINEDUB' : 'MINESEC',
                    'school_type' => $isPrimary ? 'PRIMARY' : 'GEN_SEC',
                    'region_id' => $regionModels[$code]->id,
                    'accessibility_class' => $accessCycle[($i + array_search($code, array_keys($townByRegion))) % 4],
                ]);
            }
        }

        // ---- Titles (12 approved) ------------------------------------------
        $titleDefs = [
            ['MAT', 'F1', 'EN', 'S', 'Mathematics for Form 1', null],
            ['ENG', 'F2', 'EN', 'S', 'English Language Form 2', null],
            ['PHY', 'F4', 'EN', 'S', 'Physics for Form 4', null],
            ['FRE', 'P3', 'FR', 'B', null, 'Français au CE1'],
            ['MAT', 'P4', 'FR', 'B', null, 'Mathématiques au CM1'],
            ['SCI', 'P5', 'BI', 'B', 'Integrated Science P5', 'Sciences intégrées CM1'],
            ['HIS', 'F3', 'EN', 'S', 'History of Cameroon Form 3', null],
            ['GEO', 'F2', 'FR', 'S', null, 'Géographie 5e'],
            ['ENG', 'P6', 'EN', 'B', 'English Primary 6', null],
            ['CSC', 'F5', 'EN', 'S', 'Computer Science Form 5', null],
            ['BIO', 'F3', 'FR', 'S', null, 'Biologie 4e'],
            ['CIV', 'P2', 'BI', 'B', 'Citizenship P2', 'Éducation civique CP'],
        ];
        $titles = [];
        foreach ($titleDefs as $i => [$subj, $grade, $lang, $min, $en, $fr]) {
            $titles[] = TextbookTitle::create([
                'ntid' => sprintf('CM-TB-%s-%s-%s-%s-%04d-0%d', $min, $subj, $grade, $lang, $i + 1, ($i % 3) + 1),
                'title_en' => $en, 'title_fr' => $fr,
                'ministry' => $min === 'S' ? 'MINESEC' : 'MINEDUB',
                'subject_code' => $subj, 'grade_code' => $grade, 'language' => $lang,
                'status' => 'APPROVED',
            ]);
        }

        // ---- Warehouses -----------------------------------------------------
        $whDefs = [
            ['CM-WH-CE-001', 'National Warehouse Yaoundé', 'NATIONAL', 'CE'],
            ['CM-WH-LT-001', 'Douala Regional Depot', 'REGIONAL', 'LT'],
            ['CM-WH-NW-001', 'Bamenda Depot', 'REGIONAL', 'NW'],
            ['CM-WH-NO-001', 'Garoua Depot', 'REGIONAL', 'NO'],
            ['CM-WH-SU-001', 'Ebolowa Depot', 'REGIONAL', 'SU'],
            ['CM-WH-OU-001', 'Bafoussam Depot', 'REGIONAL', 'OU'],
            ['CM-WH-EN-001', 'Maroua Depot', 'REGIONAL', 'EN'],
            ['CM-WH-ES-001', 'Bertoua Depot', 'DIVISIONAL', 'ES'],
        ];
        $warehouses = [];
        foreach ($whDefs as [$id, $name, $tier, $code]) {
            $warehouses[] = Warehouse::create(['wh_id' => $id, 'name' => $name, 'tier' => $tier, 'region_id' => $regionModels[$code]->id]);
        }

        // ---- Print batches + passport events + national stock ---------------
        foreach ($titles as $i => $title) {
            $qty = 20000 + $i * 3500;
            $batch = PrintBatch::create([
                'batch_no' => sprintf('BAT-2025-%05d', $i + 1),
                'textbook_title_id' => $title->id,
                'printer' => $i % 2 ? 'Imprimerie Nationale' : 'MACACOS Printing Douala',
                'quantity' => $qty, 'qa_status' => 'PASSED', 'received_qty' => $qty,
            ]);
            $when = now()->subDays(120 - $i * 5);
            foreach ([
                ['PRINTED', $batch->printer], ['QA_PASSED', $batch->printer],
                ['WAREHOUSE_RECEIPT', 'National Warehouse Yaoundé'],
            ] as $j => [$type, $loc]) {
                PassportEvent::create([
                    'print_batch_id' => $batch->id, 'event_type' => $type,
                    'location' => $loc, 'actor' => 'Paul Mbarga',
                    'occurred_at' => $when->copy()->addDays($j * 3),
                ]);
            }
            StockRecord::post($warehouses[0]->id, $title->id, 'AVAILABLE', (int) ($qty * 0.35));
            StockRecord::post($warehouses[($i % 7) + 1]->id, $title->id, 'AVAILABLE', (int) ($qty * 0.25));
        }

        // ---- Shipments: 5 mockup rows + 35 generated, with custody chains ----
        $mockupRows = [
            ['SHP-2025-000125', 0, null, 'RECEIVED_FULL', 12450, '2025-05-08', 'Yaoundé Regional Depot'],
            ['SHP-2025-000124', 1, null, 'IN_TRANSIT', 18750, '2025-05-08', 'Bafoussam'],
            ['SHP-2025-000123', 2, null, 'RECEIVED_FULL', 9860, '2025-05-07', 'Kumbo'],
            ['SHP-2025-000122', 3, null, 'IN_TRANSIT', 11230, '2025-05-07', 'Maroua'],
            ['SHP-2025-000121', 4, null, 'CONFIRMED', 8500, '2025-05-06', 'Kribi'],
        ];
        foreach ($mockupRows as [$no, $whIdx, $schoolId, $status, $books, $date, $dest]) {
            $s = Shipment::create([
                'shipment_no' => $no,
                'origin_name' => $warehouses[$whIdx]->name, 'origin_warehouse_id' => $warehouses[$whIdx]->id,
                'destination_name' => $dest,
                'textbook_title_id' => $titles[$whIdx % 12]->id,
                'status' => $status, 'books' => $books, 'shipped_on' => $date,
                'received_books' => $status === 'RECEIVED_FULL' ? $books : null,
            ]);
            $this->custodyChain($s);
        }

        $statuses = ['RECEIVED_FULL', 'RECEIVED_FULL', 'RECEIVED_FULL', 'IN_TRANSIT', 'RECEIVED_WITH_DISCREPANCY', 'CONFIRMED', 'DISPATCHED'];
        for ($i = 1; $i <= 35; $i++) {
            $wh = $warehouses[$i % 8];
            $school = $schools[$i % count($schools)];
            $status = $statuses[$i % count($statuses)];
            $books = 1500 + ($i * 317) % 9000;
            $received = match ($status) {
                'RECEIVED_FULL' => $books,
                'RECEIVED_WITH_DISCREPANCY' => $books - (50 + $i * 7 % 200),
                default => null,
            };
            $s = Shipment::create([
                'shipment_no' => sprintf('SHP-2025-%06d', 120 - $i),
                'origin_name' => $wh->name, 'origin_warehouse_id' => $wh->id,
                'destination_name' => $school->name_official, 'destination_school_id' => $school->id,
                'textbook_title_id' => $titles[$i % 12]->id,
                'status' => $status, 'books' => $books, 'received_books' => $received,
                'shipped_on' => now()->subDays(10 + $i * 4)->toDateString(),
            ]);
            $this->custodyChain($s);
            if ($status === 'RECEIVED_WITH_DISCREPANCY') {
                Alert::create([
                    'severity' => 'CRITICAL',
                    'title' => "Discrepancy on {$s->shipment_no}",
                    'message' => "Received {$received} of {$books} books at {$s->destination_name}. Variance frozen in QUARANTINE pending reconciliation (FR-NWD-SM-02).",
                    'link' => "/shipments/{$s->id}",
                ]);
            }
            if (in_array($status, ['RECEIVED_FULL'])) {
                SchoolStock::create([
                    'school_id' => $school->id, 'textbook_title_id' => $titles[$i % 12]->id,
                    'quantity' => $received, 'condition' => 'GOOD',
                ]);
            }
        }

        // ---- Enrolments -------------------------------------------------------
        foreach ($schools as $i => $school) {
            $levels = $school->school_type === 'PRIMARY' ? ['P1', 'P2', 'P3', 'P4', 'P5', 'P6'] : ['F1', 'F2', 'F3', 'F4', 'F5'];
            foreach ($levels as $lvl) {
                Enrolment::create([
                    'school_id' => $school->id, 'academic_year' => '2025/2026', 'class_level' => $lvl,
                    'boys' => 28 + ($i * 7 + ord($lvl[1])) % 60, 'girls' => 26 + ($i * 5 + ord($lvl[1])) % 55,
                ]);
            }
        }

        // ---- Alerts (top-up to at least 3 unread) ------------------------------
        Alert::create(['severity' => 'WARNING', 'title' => 'Verification campaign closing', 'message' => 'Annual stock verification window closes in 14 days; 62 schools have not yet submitted scans.', 'link' => '/reports']);
        Alert::create(['severity' => 'INFO', 'title' => 'New edition approved', 'message' => 'CM-TB-S-MAT-F1-EN-0001-01 edition 2 approved by ministerial decision N°2025/0341.', 'link' => '/textbooks']);

        // ---- National KPI stats -------------------------------------------------
        NationalStat::create(['key' => 'total_textbooks', 'value' => 1245780, 'delta_pct' => null]);
        NationalStat::create(['key' => 'in_transit', 'value' => 235560, 'delta_pct' => 18.9]);
        NationalStat::create(['key' => 'delivered', 'value' => 876420, 'delta_pct' => 70.3]);
        NationalStat::create(['key' => 'pending', 'value' => 133800, 'delta_pct' => 10.8]);
    }

    private function custodyChain(Shipment $s): void
    {
        $steps = match ($s->status) {
            'CONFIRMED' => ['CONFIRMED'],
            'DISPATCHED' => ['CONFIRMED', 'LOADED', 'DISPATCHED'],
            'IN_TRANSIT' => ['CONFIRMED', 'LOADED', 'DISPATCHED'],
            'RECEIVED_FULL' => ['CONFIRMED', 'LOADED', 'DISPATCHED', 'ARRIVED', 'RECEIVED'],
            'RECEIVED_WITH_DISCREPANCY' => ['CONFIRMED', 'LOADED', 'DISPATCHED', 'ARRIVED', 'RECEIVED', 'DISCREPANCY_OPENED'],
            default => [],
        };
        $base = $s->shipped_on->copy()->subDays(2);
        foreach ($steps as $i => $step) {
            CustodyEvent::create([
                'shipment_id' => $s->id, 'event_type' => $step,
                'actor' => $step === 'RECEIVED' || $step === 'DISCREPANCY_OPENED' ? 'Grace Nfor (School Head)' : 'Paul Mbarga (Warehouse)',
                'notes' => $step === 'DISCREPANCY_OPENED' ? 'Variance vs waybill; case opened automatically.' : null,
                'occurred_at' => $base->copy()->addDays($i),
            ]);
        }
    }
}
