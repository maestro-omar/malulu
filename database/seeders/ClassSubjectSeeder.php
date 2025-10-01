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
                    'code' => 'sala',
                    'is_curricular' => false,
                ],
                [
                    'name' => 'Música',
                    'short_name' => 'MUS',
                    'code' => 'musica',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Educación Física',
                    'short_name' => 'EDF',
                    'code' => 'ed_fisica',
                    'is_curricular' => true,
                ],
            ],
            SchoolLevel::PRIMARY => [
                [
                    'name' => 'Lengua',
                    'short_name' => 'LEN',
                    'code' => 'lengua',
                    'is_curricular' => false,
                ],
                [
                    'name' => 'Matemática',
                    'short_name' => 'MAT',
                    'code' => 'matematica',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Ciencias Sociales',
                    'short_name' => 'SOC',
                    'code' => 'ciencias_sociales',
                    'is_curricular' => false,
                ],
                [
                    'name' => 'Ciencias Naturales',
                    'short_name' => 'NAT',
                    'code' => 'ciencias_naturales',
                    'is_curricular' => false,
                ],
                [
                    'name' => 'Ética',
                    'short_name' => 'ETI',
                    'code' => 'etica',
                    'is_curricular' => false,
                ],
                [
                    'name' => 'ESI',
                    'short_name' => 'ESI',
                    'code' => 'esi',
                    'is_curricular' => false,
                ],
                [
                    'name' => 'Tecnología',
                    'short_name' => 'TEC',
                    'code' => 'tecnologia',
                    'is_curricular' => false,
                ],
                [
                    'name' => 'Música',
                    'short_name' => 'MUS',
                    'code' => 'musica',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Plástica',
                    'short_name' => 'PLA',
                    'code' => 'plastica',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Inglés',
                    'short_name' => 'ING',
                    'code' => 'ingles',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Educación Física',
                    'short_name' => 'EDF',
                    'code' => 'ed_fisica',
                    'is_curricular' => true,
                ],
            ],
            SchoolLevel::SECONDARY => [
                [
                    'name' => 'Lengua',
                    'short_name' => 'LEN',
                    'code' => 'lengua',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Literatura',
                    'short_name' => 'LIT',
                    'code' => 'literatura',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Matemática',
                    'short_name' => 'MAT',
                    'code' => 'matematica',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Historia',
                    'short_name' => 'HIS',
                    'code' => 'historia',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Física',
                    'short_name' => 'FIS',
                    'code' => 'fisica',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Química',
                    'short_name' => 'QUI',
                    'code' => 'quimica',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Educación Física',
                    'short_name' => 'EDF',
                    'code' => 'ed_fisica',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Contabilidad',
                    'short_name' => 'CON',
                    'code' => 'contabilidad',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Biología',
                    'short_name' => 'BIO',
                    'code' => 'biologia',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Filosofía',
                    'short_name' => 'FIL',
                    'code' => 'filosofia',
                    'is_curricular' => true,
                ],
                [
                    'name' => 'Geografía',
                    'short_name' => 'GEO',
                    'code' => 'geografia',
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
                        'code' => $subject['code'],
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
