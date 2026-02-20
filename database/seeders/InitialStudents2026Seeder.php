<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Traits\InitialUsersTrait;

/**
 * Initial Students 2026 Seeder
 *
 * Seeds first-grade students and guardians for 2026 from
 * ce-8_initial_students_with_guardian_2026_first_grade.json.
 * Must run after UpdateDataTo2026Seeder so "Agrupamiento" resolves to 2026 courses.
 */
class InitialStudents2026Seeder extends Seeder
{
    use InitialUsersTrait;

    private string $json2026StudentsFileName = 'ce-8_initial_students_with_guardian_2026_first_grade.json';

    public function run(): void
    {
        $this->initStudentsSeeder(2026);
        $this->createInitialStudentsFromFile($this->json2026StudentsFileName);
    }
}
