<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Traits\InitialUsersTrait;

/**
 * Initial Students Seeder
 *
 * Seeds students and their guardians from ce-8_initial_students_with_guardian.json (2025).
 */
class InitialStudentsSeeder extends Seeder
{
    use InitialUsersTrait;

    private string $json2025StudentsFileName = 'ce-8_initial_students_with_guardian.json';

    public function run(): void
    {
        $this->initStudentsSeeder(2025);
        $this->createInitialStudentsFromFile($this->json2025StudentsFileName);
    }
}
