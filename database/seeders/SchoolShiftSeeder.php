<?php

namespace Database\Seeders;

use App\Models\SchoolShift;
use Illuminate\Database\Seeder;

class SchoolShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            ['name' => 'Mañana', 'key' => 'morning'],
            ['name' => 'Tarde', 'key' => 'afternoon'],
            ['name' => 'Noche', 'key' => 'night'],
        ];

        foreach ($shifts as $shift) {
            SchoolShift::create($shift);
        }
    }
} 