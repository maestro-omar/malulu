<?php

namespace Database\Seeders;

use App\Models\Catalogs\Province;
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
                'code' => Province::DEFAULT,
                'name' => 'San Luis',
            ],
            [
                'code' => 'mza',
                'name' => 'Mendoza',
            ],
            [
                'code' => 'cba',
                'name' => 'Córdoba',
            ],
            [
                'code' => 'sj',
                'name' => 'San Juan',
            ],
            [
                'code' => 'lr',
                'name' => 'La Rioja',
            ],
            [
                'code' => 'bsas',
                'name' => 'Buenos Aires',
            ],
            [
                'code' => 'caba',
                'name' => 'Ciudad Autónoma de Buenos Aires',
            ],
            [
                'code' => 'cat',
                'name' => 'Catamarca',
            ],
            [
                'code' => 'cha',
                'name' => 'Chaco',
            ],
            [
                'code' => 'chu',
                'name' => 'Chubut',
            ],
            [
                'code' => 'cor',
                'name' => 'Corrientes',
            ],
            [
                'code' => 'ent',
                'name' => 'Entre Ríos',
            ],
            [
                'code' => 'for',
                'name' => 'Formosa',
            ],
            [
                'code' => 'juj',
                'name' => 'Jujuy',
            ],
            [
                'code' => 'lpa',
                'name' => 'La Pampa',
            ],
            [
                'code' => 'mis',
                'name' => 'Misiones',
            ],
            [
                'code' => 'neu',
                'name' => 'Neuquén',
            ],
            [
                'code' => 'rng',
                'name' => 'Río Negro',
            ],
            [
                'code' => 'sal',
                'name' => 'Salta',
            ],
            [
                'code' => 'sct',
                'name' => 'Santa Cruz',
            ],
            [
                'code' => 'sfe',
                'name' => 'Santa Fe',
            ],
            [
                'code' => 'sde',
                'name' => 'Santiago del Estero',
            ],
            [
                'code' => 'tfu',
                'name' => 'Tierra del Fuego',
            ],
            [
                'code' => 'tuc',
                'name' => 'Tucumán',
            ],
        ];

        foreach ($provinces as $i => $province) {
            $province['order'] = $i;
            Province::create($province);
        }
    }
}
