<?php

namespace Database\Seeders;

use App\Models\Catalogs\Province;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Simulate upload for San Luis
        $logo1Source = database_path('seeders/images/sl-logo1.png');
        $logo2Source = database_path('seeders/images/sl-logo2.png');
        $logo1Dest = 'province-logos/sl/seeded-logo1.png';
        $logo2Dest = 'province-logos/sl/seeded-logo2.png';
        // Copy images to storage if not already present
        if (file_exists($logo1Source) && !Storage::disk('public')->exists($logo1Dest)) {
            Storage::disk('public')->put($logo1Dest, file_get_contents($logo1Source));
        }
        if (file_exists($logo2Source) && !Storage::disk('public')->exists($logo2Dest)) {
            Storage::disk('public')->put($logo2Dest, file_get_contents($logo2Source));
        }
        $provinces = [
            [
                'code' => Province::DEFAULT,
                'name' => 'San Luis',
                'logo1' => '/storage/' . $logo1Dest,
                'logo2' => '/storage/' . $logo2Dest,
                'title' => 'Ministerio de Educación',
                'subtitle' => 'Forjamos el futuro a través de la educación, cimiento sólido para el progreso de la provincia de San Luis.',
                'link' => 'https://sanluis.gov.ar/educacion/',
                'config' => json_encode([
                    "header" => [
                        "background-color" => "#f0dea1",
                        "color" => "#333"
                    ]
                ]),
            ],
            [
                'code' => 'mza',
                'name' => 'Mendoza',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
                'config' => null,
            ],
            [
                'code' => 'cba',
                'name' => 'Córdoba',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
                'config' => null,
            ],
            [
                'code' => 'sj',
                'name' => 'San Juan',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
                'config' => null,
            ],
            [
                'code' => 'lr',
                'name' => 'La Rioja',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
                'config' => null,
            ],
            [
                'code' => 'bsas',
                'name' => 'Buenos Aires',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
                'config' => null,
            ],
            [
                'code' => 'caba',
                'name' => 'Ciudad Autónoma de Buenos Aires',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
                'config' => null,
            ],
            [
                'code' => 'cat',
                'name' => 'Catamarca',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
                'config' => null,
            ],
            [
                'code' => 'cha',
                'name' => 'Chaco',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
                'config' => null,
            ],
            [
                'code' => 'chu',
                'name' => 'Chubut',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
                'config' => null,
            ],
            [
                'code' => 'cor',
                'name' => 'Corrientes',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
                'config' => null,
            ],
            [
                'code' => 'ent',
                'name' => 'Entre Ríos',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
                'config' => null,
            ],
            [
                'code' => 'for',
                'name' => 'Formosa',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
                'config' => null,
            ],
            [
                'code' => 'juj',
                'name' => 'Jujuy',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
                'config' => null,
            ],
            [
                'code' => 'lpa',
                'name' => 'La Pampa',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
                'config' => null,
            ],
            [
                'code' => 'mis',
                'name' => 'Misiones',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
                'config' => null,
            ],
            [
                'code' => 'neu',
                'name' => 'Neuquén',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
                'config' => null,
            ],
            [
                'code' => 'rng',
                'name' => 'Río Negro',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
                'config' => null,
            ],
            [
                'code' => 'sal',
                'name' => 'Salta',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
                'config' => null,
            ],
            [
                'code' => 'sct',
                'name' => 'Santa Cruz',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
                'config' => null,
            ],
            [
                'code' => 'sfe',
                'name' => 'Santa Fe',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
                'config' => null,
            ],
            [
                'code' => 'sde',
                'name' => 'Santiago del Estero',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
                'config' => null,
            ],
            [
                'code' => 'tfu',
                'name' => 'Tierra del Fuego',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
                'config' => null,
            ],
            [
                'code' => 'tuc',
                'name' => 'Tucumán',
                'logo1' => null,
                'logo2' => null,
                'title' => null,
                'subtitle' => null,
                'link' => null,
                'config' => null,
            ],
        ];

        foreach ($provinces as $i => $province) {
            $province['order'] = $i;
            Province::create($province);
        }
    }
}
