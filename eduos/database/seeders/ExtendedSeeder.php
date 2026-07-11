<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Catalogue\Models\Supplier;
use App\Modules\Custody\Models\StorageLocation;
use App\Modules\Custody\Models\Warehouse;
use App\Modules\Registry\Models\Division;
use App\Modules\Registry\Models\Region;
use App\Modules\Registry\Models\School;
use App\Modules\Registry\Models\Subdivision;
use Illuminate\Database\Seeder;

/** Backfill: admin hierarchy, school GPS/infrastructure, suppliers, warehouse zones. */
class ExtendedSeeder extends Seeder
{
    public function run(): void
    {
        // Two representative divisions per region, two subdivisions each
        $divisionNames = [
            'CE' => ['Mfoundi', 'Nyong-et-So\'o'], 'LT' => ['Wouri', 'Moungo'],
            'OU' => ['Mifi', 'Menoua'], 'NO' => ['Bénoué', 'Mayo-Louti'],
            'SU' => ['Mvila', 'Océan'], 'EN' => ['Diamaré', 'Mayo-Sava'],
            'NW' => ['Mezam', 'Bui'], 'SW' => ['Fako', 'Meme'],
            'AD' => ['Vina', 'Mbéré'], 'ES' => ['Lom-et-Djérem', 'Kadey'],
        ];
        foreach (Region::all() as $region) {
            foreach ($divisionNames[$region->code] ?? [] as $i => $name) {
                $division = Division::firstOrCreate(
                    ['region_id' => $region->id, 'code' => sprintf('%s%02d', $region->code, $i + 1)],
                    ['name' => $name]
                );
                foreach ([1, 2] as $j) {
                    Subdivision::firstOrCreate(
                        ['division_id' => $division->id, 'code' => sprintf('%s%02d', $division->code, $j)],
                        ['name' => "{$name} ".($j === 1 ? 'Centre' : 'Rural')]
                    );
                }
            }
        }

        // Approximate town coordinates for GPS backfill
        $coords = [
            'Yaoundé' => [3.848, 11.502], 'Mbalmayo' => [3.517, 11.500], 'Obala' => [4.169, 11.533],
            'Douala' => [4.051, 9.768], 'Nkongsamba' => [4.955, 9.940], 'Edéa' => [3.800, 10.133],
            'Bafoussam' => [5.478, 10.418], 'Dschang' => [5.443, 10.053], 'Mbouda' => [5.626, 10.254],
            'Garoua' => [9.301, 13.398], 'Guider' => [9.933, 13.946], 'Poli' => [8.480, 13.246],
            'Ebolowa' => [2.900, 11.150], 'Kribi' => [2.940, 9.910], 'Sangmélima' => [2.933, 11.983],
            'Maroua' => [10.591, 14.316], 'Kousséri' => [12.078, 15.031], 'Mokolo' => [10.740, 13.802],
            'Bamenda' => [5.963, 10.159], 'Kumbo' => [6.204, 10.678], 'Wum' => [6.383, 10.067],
            'Buea' => [4.155, 9.231], 'Kumba' => [4.636, 9.446], 'Limbe' => [4.023, 9.206],
            'Ngaoundéré' => [7.327, 13.584], 'Meiganga' => [6.517, 14.300], 'Tibati' => [6.467, 12.633],
            'Bertoua' => [4.577, 13.684], 'Batouri' => [4.433, 14.367], 'Abong-Mbang' => [3.983, 13.183],
        ];
        foreach (School::whereNull('subdivision_id')->get() as $school) {
            $sub = Subdivision::whereHas('division', fn ($q) => $q->where('region_id', $school->region_id))
                ->inRandomOrder()->first();
            $town = collect($coords)->first(fn ($c, $t) => str_contains($school->name_official, $t));
            $school->update([
                'subdivision_id' => $sub?->id,
                'gps_lat' => $town[0] ?? null, 'gps_lon' => $town[1] ?? null,
                'gps_verified' => (bool) $town,
                'classrooms_total' => 8 + $school->id % 22,
                'storage_secure' => $school->id % 3 !== 0,
                'grid_power' => ['GRID', 'GRID', 'SOLAR', 'NONE'][$school->id % 4],
                'connectivity' => ['4G', '3G', '3G', '2G', 'NONE'][$school->id % 5],
            ]);
        }

        foreach ([
            ['Imprimerie Nationale', 'PRINTER', 'Yaoundé'],
            ['MACACOS Printing Douala', 'PRINTER', 'Douala'],
            ['Éditions CLE', 'PUBLISHER', 'Yaoundé'],
            ['Camrail Express Logistics', 'LOGISTICS', 'Douala'],
        ] as [$name, $type, $contact]) {
            Supplier::firstOrCreate(['name' => $name], ['type' => $type, 'contact' => $contact]);
        }

        foreach (Warehouse::all() as $wh) {
            foreach (['A1', 'A2', 'B1'] as $zone) {
                StorageLocation::firstOrCreate(['warehouse_id' => $wh->id, 'zone' => $zone], ['capacity' => 15000]);
            }
        }

        // Attach the school-head demo user to a school for row-level scoping
        User::where('email', 'school@minesec.cm')->update(['school_id' => School::where('ministry', 'MINESEC')->first()?->id]);
    }
}
