<?php

namespace Database\Seeders;

use App\Models\SchoolShift;
use Illuminate\Database\Seeder;

class SchoolShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            ['name' => 'Mañana', 'code' => SchoolShift::MORNING],
            ['name' => 'Tarde', 'code' => SchoolShift::AFTERNOON],
            ['name' => 'Noche', 'code' => SchoolShift::NIGHT],
        ];

        foreach ($shifts as $shift) {
            SchoolShift::create($shift);
        }
    }
}