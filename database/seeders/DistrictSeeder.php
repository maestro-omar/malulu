<?php

namespace Database\Seeders;

use App\Models\District;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
            District::create($district);
        }
    }
}
