<?php

namespace Database\Seeders;

use App\Models\SchoolShift;
use Illuminate\Database\Seeder;

class SchoolShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            ['name' => 'Mañana', 'code' => 'morning'],
            ['name' => 'Tarde', 'code' => 'afternoon'],
            ['name' => 'Noche', 'code' => 'night'],
        ];

        foreach ($shifts as $shift) {
            SchoolShift::create($shift);
        }
    }
} 