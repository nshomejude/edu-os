<?php

namespace Database\Seeders;

use App\Modules\Registry\Models\Enrolment;
use App\Modules\Registry\Models\Student;
use Illuminate\Database\Seeder;

/** Generate named learners matching each validated enrolment return. */
class StudentSeeder extends Seeder
{
    public function run(): void
    {
        if (Student::count() > 0) {
            return;
        }
        $first = ['Achille', 'Bernadette', 'Clovis', 'Divine', 'Emmanuel', 'Florence', 'Gaston', 'Hawa', 'Ibrahim', 'Jacqueline', 'Kilian', 'Larissa', 'Moussa', 'Ngozi', 'Olivier', 'Patience', 'Quentin', 'Rahmatou', 'Samuel', 'Thérèse'];
        $last = ['Abanda', 'Biya', 'Chedjou', 'Djoum', 'Ekambi', 'Fotso', 'Gwet', 'Haman', 'Issa', 'Kamga', 'Lobe', 'Mbarga', 'Ndip', 'Onana', 'Priso', 'Sadi', 'Tchoupo', 'Um', 'Wanko', 'Yaya'];
        $seq = 1;
        $rows = [];
        foreach (Enrolment::where('validation_status', 'VALIDATED')->where('academic_year', '2025/2026')->get() as $e) {
            $total = min(40, $e->boys + $e->girls);   // cap per class for demo size
            for ($i = 0; $i < $total; $i++) {
                $rows[] = [
                    'lsid' => sprintf('CM-STU-%07d', $seq),
                    'name' => $first[($seq * 7) % 20].' '.$last[($seq * 11) % 20],
                    'sex' => $i < $e->boys ? 'M' : 'F',
                    'class_level' => $e->class_level,
                    'school_id' => $e->school_id,
                    'academic_year' => '2025/2026',
                    'created_at' => now(), 'updated_at' => now(),
                ];
                $seq++;
            }
        }
        foreach (array_chunk($rows, 500) as $chunk) {
            Student::insert($chunk);
        }
    }
}
