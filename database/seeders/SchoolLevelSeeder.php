<?php

namespace Database\Seeders;

use App\Models\SchoolLevel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SchoolLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [

            [
                'key' => SchoolLevel::KINDER,
                'name' => 'Inicial',
            ],

            [
                'key' => SchoolLevel::PRIMARY,
                'name' => 'Primaria',
            ],

            [
                'key' => SchoolLevel::SECONDARY,
                'name' => 'Secundaria',
            ],
        ];

        foreach ($levels as $level) {
            SchoolLevel::create($level);
        }
    }
}
