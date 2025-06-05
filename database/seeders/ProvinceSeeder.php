<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provinces = [
            [
                'key' => Province::DEFAULT,
                'name' => 'San Luis',
            ],
            [
                'key' => 'mza',
                'name' => 'Mendoza',
            ],
            [
                'key' => 'cba',
                'name' => 'Córdoba',
            ],
            [
                'key' => 'sj',
                'name' => 'San Juan',
            ],
            [
                'key' => 'lr',
                'name' => 'La Rioja',
            ],
            [
                'key' => 'bsas',
                'name' => 'Buenos Aires',
            ],
            [
                'key' => 'caba',
                'name' => 'Ciudad Autónoma de Buenos Aires',
            ],
            [
                'key' => 'cat',
                'name' => 'Catamarca',
            ],
            [
                'key' => 'cha',
                'name' => 'Chaco',
            ],
            [
                'key' => 'chu',
                'name' => 'Chubut',
            ],
            [
                'key' => 'cor',
                'name' => 'Corrientes',
            ],
            [
                'key' => 'ent',
                'name' => 'Entre Ríos',
            ],
            [
                'key' => 'for',
                'name' => 'Formosa',
            ],
            [
                'key' => 'juj',
                'name' => 'Jujuy',
            ],
            [
                'key' => 'lpa',
                'name' => 'La Pampa',
            ],
            [
                'key' => 'mis',
                'name' => 'Misiones',
            ],
            [
                'key' => 'neu',
                'name' => 'Neuquén',
            ],
            [
                'key' => 'rng',
                'name' => 'Río Negro',
            ],
            [
                'key' => 'sal',
                'name' => 'Salta',
            ],
            [
                'key' => 'sct',
                'name' => 'Santa Cruz',
            ],
            [
                'key' => 'sfe',
                'name' => 'Santa Fe',
            ],
            [
                'key' => 'sde',
                'name' => 'Santiago del Estero',
            ],
            [
                'key' => 'tfu',
                'name' => 'Tierra del Fuego',
            ],
            [
                'key' => 'tuc',
                'name' => 'Tucumán',
            ],
        ];

        foreach ($provinces as $i => $province) {
            $province['order'] = $i;
            Province::create($province);
        }
    }
}
