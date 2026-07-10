<?php

namespace Database\Seeders;

use App\Modules\Catalogue\Models\TextbookTitle;
use App\Modules\Custody\Models\NationalStat;
use App\Modules\Custody\Models\Shipment;
use App\Modules\Registry\Models\Region;
use App\Modules\Registry\Models\School;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Demo dataset mirroring the EduOS Heritage dashboard mockup.
     * Aggregates become event-stream projections once custody events land.
     */
    public function run(): void
    {
        $regions = [
            ['code' => 'CE', 'name_en' => 'Centre',     'name_fr' => 'Centre',         'books_distributed' => 198450],
            ['code' => 'LT', 'name_en' => 'Littoral',   'name_fr' => 'Littoral',       'books_distributed' => 176230],
            ['code' => 'OU', 'name_en' => 'West',       'name_fr' => 'Ouest',          'books_distributed' => 154600],
            ['code' => 'NO', 'name_en' => 'North',      'name_fr' => 'Nord',           'books_distributed' => 142380],
            ['code' => 'SU', 'name_en' => 'South',      'name_fr' => 'Sud',            'books_distributed' => 123760],
            ['code' => 'EN', 'name_en' => 'Far North',  'name_fr' => 'Extrême-Nord',   'books_distributed' => 110760],
            ['code' => 'NW', 'name_en' => 'North West', 'name_fr' => 'Nord-Ouest',     'books_distributed' => 98520],
            ['code' => 'SW', 'name_en' => 'South West', 'name_fr' => 'Sud-Ouest',      'books_distributed' => 78300],
            ['code' => 'AD', 'name_en' => 'Adamawa',    'name_fr' => 'Adamaoua',       'books_distributed' => 42000],
            ['code' => 'ES', 'name_en' => 'East',       'name_fr' => 'Est',            'books_distributed' => 38000],
        ];
        foreach ($regions as $r) {
            Region::create($r);
        }

        School::create([
            'nsid' => 'CM-SCH-CE-0101-BP-00001',
            'name_official' => 'École Publique de Bastos',
            'ministry' => 'MINEDUB', 'school_type' => 'PRIMARY',
            'region_id' => Region::where('code', 'CE')->first()->id,
        ]);
        School::create([
            'nsid' => 'CM-SCH-NW-0703-SG-00412',
            'name_official' => 'Government Bilingual High School Bamenda',
            'ministry' => 'MINESEC', 'school_type' => 'GEN_SEC',
            'region_id' => Region::where('code', 'NW')->first()->id,
            'accessibility_class' => 'RURAL_ROAD',
        ]);

        TextbookTitle::create([
            'ntid' => 'CM-TB-S-MAT-F1-EN-0007-03',
            'title_en' => 'Mathematics for Form 1', 'ministry' => 'MINESEC',
            'subject_code' => 'MAT', 'grade_code' => 'F1', 'language' => 'EN',
            'status' => 'APPROVED',
        ]);
        TextbookTitle::create([
            'ntid' => 'CM-TB-B-FRE-P3-FR-0002-05',
            'title_fr' => 'Français au CE1', 'ministry' => 'MINEDUB',
            'subject_code' => 'FRE', 'grade_code' => 'P3', 'language' => 'FR',
            'status' => 'APPROVED',
        ]);

        $shipments = [
            ['shipment_no' => 'SHP-2025-000125', 'origin_name' => 'National Warehouse',    'destination_name' => 'Yaoundé Regional Depot', 'status' => 'RECEIVED_FULL', 'books' => 12450, 'shipped_on' => '2025-05-08'],
            ['shipment_no' => 'SHP-2025-000124', 'origin_name' => 'Douala Regional Depot', 'destination_name' => 'Bafoussam',              'status' => 'IN_TRANSIT',    'books' => 18750, 'shipped_on' => '2025-05-08'],
            ['shipment_no' => 'SHP-2025-000123', 'origin_name' => 'Bamenda Depot',         'destination_name' => 'Kumbo',                  'status' => 'RECEIVED_FULL', 'books' => 9860,  'shipped_on' => '2025-05-07'],
            ['shipment_no' => 'SHP-2025-000122', 'origin_name' => 'Garoua Depot',          'destination_name' => 'Maroua',                 'status' => 'IN_TRANSIT',    'books' => 11230, 'shipped_on' => '2025-05-07'],
            ['shipment_no' => 'SHP-2025-000121', 'origin_name' => 'Ebolowa Depot',         'destination_name' => 'Kribi',                  'status' => 'CONFIRMED',     'books' => 8500,  'shipped_on' => '2025-05-06'],
        ];
        foreach ($shipments as $s) {
            Shipment::create($s);
        }

        NationalStat::create(['key' => 'total_textbooks', 'value' => 1245780, 'delta_pct' => null]);
        NationalStat::create(['key' => 'in_transit',      'value' => 235560,  'delta_pct' => 18.9]);
        NationalStat::create(['key' => 'delivered',       'value' => 876420,  'delta_pct' => 70.3]);
        NationalStat::create(['key' => 'pending',         'value' => 133800,  'delta_pct' => 10.8]);
    }
}
