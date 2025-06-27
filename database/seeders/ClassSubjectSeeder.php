<?php

namespace Database\Seeders;

use App\Models\Catalogs\ClassSubject;
use App\Models\Catalogs\SchoolLevel;
use Illuminate\Database\Seeder;

class ClassSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schoolLevels = SchoolLevel::all();

        if ($schoolLevels->isEmpty()) {
            $this->command->error('No school levels found. Please run SchoolLevelSeeder first.');
            return;
        }

        $subjectsByLevel = [
            SchoolLevel::KINDER => [
                [
                    'name' => 'Sala',
                    'short_name' => 'SALA',
                    'is_curricular' => false,
                ],
                [
                    'name' => 'Música',
                    'short_name' => 'MUS',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Educación Física',
                    'short_name' => 'EDF',
                    'is_curricular' => true,
                ],
            ],
            SchoolLevel::PRIMARY => [
                [
                    'name' => 'Lengua',
                    'short_name' => 'LEN',
                    'is_curricular' => false,
                ],
                [
                    'name' => 'Matemática',
                    'short_name' => 'MAT',
                    'is_curricular' => false,
                ],
                [
                    'name' => 'Ciencias Sociales',
                    'short_name' => 'SOC',
                    'is_curricular' => false,
                ],
                [
                    'name' => 'Ciencias Naturales',
                    'short_name' => 'NAT',
                    'is_curricular' => false,
                ],
                [
                    'name' => 'Ética',
                    'short_name' => 'ETI',
                    'is_curricular' => false,
                ],
                [
                    'name' => 'ESI',
                    'short_name' => 'ESI',
                    'is_curricular' => false,
                ],
                [
                    'name' => 'Tecnología',
                    'short_name' => 'TEC',
                    'is_curricular' => false,
                ],
                [
                    'name' => 'Música',
                    'short_name' => 'MUS',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Plástica',
                    'short_name' => 'PLA',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Inglés',
                    'short_name' => 'ING',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Educación Física',
                    'short_name' => 'EDF',
                    'is_curricular' => true,
                ],
            ],
            SchoolLevel::SECONDARY => [
                [
                    'name' => 'Lengua',
                    'short_name' => 'LEN',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Literatura',
                    'short_name' => 'LIT',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Matemática',
                    'short_name' => 'MAT',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Historia',
                    'short_name' => 'HIS',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Física',
                    'short_name' => 'FIS',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Química',
                    'short_name' => 'QUI',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Educación Física',
                    'short_name' => 'EDF',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Contabilidad',
                    'short_name' => 'CON',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Biología',
                    'short_name' => 'BIO',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Filosofía',
                    'short_name' => 'FIL',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Geografía',
                    'short_name' => 'GEO',
                    'is_curricular' => true,
                ],
            ],
        ];

        $any = false;
        foreach ($schoolLevels as $level) {
            if (isset($subjectsByLevel[$level->code])) {
                foreach ($subjectsByLevel[$level->code] as $subject) {
                    ClassSubject::create([
                        'name' => $subject['name'],
                        'short_name' => $subject['short_name'],
                        'school_level_id' => $level->id,
                        'is_curricular' => $subject['is_curricular'],
                    ]);
                    $any = true;
                }
            } else {
                $this->command->error('No subjects for level!' . $level->name);
                dd($level);
            }
        }

        if (!$any) {
            $this->command->error('No subject created! why?');
            return;
        }
    }
}
