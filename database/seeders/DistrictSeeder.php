<?php

namespace Database\Seeders;

use App\Models\Catalogs\District;
use App\Models\Catalogs\Province;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get San Luis province ID
        $sanLuisProvince = Province::where('code', Province::DEFAULT)->firstOrFail();

        $districts = [
            [
                'name' => 'Pueyrredón',
                'long' => 'Juan Martín de Pueyrredón',
            ],
            [
                'name' => 'Pedernera',
            ],
            [
                'name' => 'Junin',
            ],
            [
                'name' => 'Pringles',
            ],
            [
                'name' => 'Ayacucho',
            ],
            [
                'name' => 'Chacabuco',
            ],
            [
                'name' => 'Belgrano',
            ],
            [
                'name' => 'San Martín',
            ],
            [
                'name' => 'Dupuy',
            ],
        ];

        foreach ($districts as $i => $district) {
            $district['order'] = $i;
            $district['long'] = ($district['long'] ?? '') ?: $district['name'];
            $district['province_id'] = $sanLuisProvince->id;
            District::create($district);
        }
    }
}
