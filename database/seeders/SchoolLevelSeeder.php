<?php

namespace Database\Seeders;

use App\Models\Catalogs\SchoolLevel;
use App\Models\Entities\School;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SchoolLevelSeeder extends Seeder
{
    private $SIMPLE_LUCIO_LUCERO = false;
    private $SIMPLE_LUCIO_LUCERO_ONLY_PRIMARY = false;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->SIMPLE_LUCIO_LUCERO = config('malulu.one_school_cue') === School::CUE_LUCIO_LUCERO;
        $this->SIMPLE_LUCIO_LUCERO_ONLY_PRIMARY = config('malulu.one_school_only_primary');

        $levels = $this->SIMPLE_LUCIO_LUCERO_ONLY_PRIMARY ? [
            [
                'code' => SchoolLevel::PRIMARY,
                'name' => 'Primaria',
            ],
        ] : [

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
