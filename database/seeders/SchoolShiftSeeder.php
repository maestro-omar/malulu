<?php

namespace Database\Seeders;

use App\Models\Catalogs\SchoolShift;
use Illuminate\Database\Seeder;

class SchoolShiftSeeder extends Seeder
{
    public function run(): void
    {
        $options = SchoolShift::vueOptions();
        foreach ($options as $code => $option) {
            SchoolShift::create(['code' => $code, 'name' => $option['label']]);
        }
    }
}
