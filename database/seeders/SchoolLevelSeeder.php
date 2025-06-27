<?php

namespace Database\Seeders;

use App\Models\Catalogs\SchoolLevel;
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
                'code' => SchoolLevel::KINDER,
                'name' => 'Inicial',
            ],

            [
                'code' => SchoolLevel::PRIMARY,
                'name' => 'Primaria',
            ],

            [
                'code' => SchoolLevel::SECONDARY,
                'name' => 'Secundaria',
            ],
        ];

        foreach ($levels as $level) {
            SchoolLevel::create($level);
        }
    }
}
