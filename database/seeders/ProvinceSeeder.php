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
                'logo1' => null,
                'logo2' => null,
                'title' => 'Ministerio de Educación',
                'subtitle' => 'Forjamos el futuro a través de la educación, cimiento sólido para el progreso de la provincia de San Luis.',
                'link' => 'https://sanluis.gov.ar/educacion/',
            ],
            [
                'code' => 'mza',
                'name' => 'Mendoza',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
            ],
            [
                'code' => 'cba',
                'name' => 'Córdoba',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
            ],
            [
                'code' => 'sj',
                'name' => 'San Juan',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
            ],
            [
                'code' => 'lr',
                'name' => 'La Rioja',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
            ],
            [
                'code' => 'bsas',
                'name' => 'Buenos Aires',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
            ],
            [
                'code' => 'caba',
                'name' => 'Ciudad Autónoma de Buenos Aires',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
            ],
            [
                'code' => 'cat',
                'name' => 'Catamarca',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
            ],
            [
                'code' => 'cha',
                'name' => 'Chaco',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
            ],
            [
                'code' => 'chu',
                'name' => 'Chubut',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
            ],
            [
                'code' => 'cor',
                'name' => 'Corrientes',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
            ],
            [
                'code' => 'ent',
                'name' => 'Entre Ríos',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
            ],
            [
                'code' => 'for',
                'name' => 'Formosa',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
            ],
            [
                'code' => 'juj',
                'name' => 'Jujuy',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
            ],
            [
                'code' => 'lpa',
                'name' => 'La Pampa',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
            ],
            [
                'code' => 'mis',
                'name' => 'Misiones',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
            ],
            [
                'code' => 'neu',
                'name' => 'Neuquén',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
            ],
            [
                'code' => 'rng',
                'name' => 'Río Negro',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
            ],
            [
                'code' => 'sal',
                'name' => 'Salta',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
            ],
            [
                'code' => 'sct',
                'name' => 'Santa Cruz',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
            ],
            [
                'code' => 'sfe',
                'name' => 'Santa Fe',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
            ],
            [
                'code' => 'sde',
                'name' => 'Santiago del Estero',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
            ],
            [
                'code' => 'tfu',
                'name' => 'Tierra del Fuego',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
            ],
            [
                'code' => 'tuc',
                'name' => 'Tucumán',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
            ],
        ];

        foreach ($provinces as $i => $province) {
            $province['order'] = $i;
            Province::create($province);
        }
    }
}
