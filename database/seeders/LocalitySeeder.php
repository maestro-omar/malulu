<?php

namespace Database\Seeders;

use App\Models\Locality;
use App\Models\District;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocalitySeeder extends Seeder
{
    private array $districts;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->initDistricts();
        $localities = [
            // Pueyrredón District
            [
                'name' => 'San Luis',
                'district_id' => $this->districts['Pueyrredón'],
                'coordinates' => '-33.3017,-66.3378'
            ],
            [
                'name' => 'La Punta',
                'district_id' => $this->districts['Pueyrredón'],
                'coordinates' => '-33.1833,-66.3167'
            ],
            [
                'name' => 'Juana Koslay',
                'district_id' => $this->districts['Pueyrredón'],
                'coordinates' => '-33.2833,-66.2333'
            ],
            [
                'name' => 'Potrero de los Funes',
                'district_id' => $this->districts['Pueyrredón'],
                'coordinates' => '-33.2167,-66.2333'
            ],

            // Pedernera District
            [
                'name' => 'Villa Mercedes',
                'district_id' => $this->districts['Pedernera'],
                'coordinates' => '-33.6833,-65.4667'
            ],
            [
                'name' => 'Justo Daract',
                'district_id' => $this->districts['Pedernera'],
                'coordinates' => '-33.8667,-65.1833'
            ],
            [
                'name' => 'Villa Reynolds',
                'district_id' => $this->districts['Pedernera'],
                'coordinates' => '-33.7167,-65.3833'
            ],

            // Junín District
            [
                'name' => 'Santa Rosa del Conlara',
                'district_id' => $this->districts['Junin'],
                'coordinates' => '-32.3333,-65.2167'
            ],
            [
                'name' => 'Merlo',
                'district_id' => $this->districts['Junin'],
                'coordinates' => '-32.3500,-65.0167'
            ],
            [
                'name' => 'Carpintería',
                'district_id' => $this->districts['Junin'],
                'coordinates' => '-32.4000,-65.0167'
            ],

            // Pringles District
            [
                'name' => 'La Toma',
                'district_id' => $this->districts['Pringles'],
                'coordinates' => '-33.0500,-65.6167'
            ],
            [
                'name' => 'Nueva Galia',
                'district_id' => $this->districts['Pringles'],
                'coordinates' => '-34.6000,-65.2500'
            ],

            // Ayacucho District
            [
                'name' => 'San Francisco del Monte de Oro',
                'district_id' => $this->districts['Ayacucho'],
                'coordinates' => '-32.6000,-66.1333'
            ],
            [
                'name' => 'Luján',
                'district_id' => $this->districts['Ayacucho'],
                'coordinates' => '-32.3667,-65.9333'
            ],

            // Chacabuco District
            [
                'name' => 'Concarán',
                'district_id' => $this->districts['Chacabuco'],
                'coordinates' => '-32.5667,-65.2500'
            ],
            [
                'name' => 'Naschel',
                'district_id' => $this->districts['Chacabuco'],
                'coordinates' => '-32.9167,-65.3667'
            ],

            // Belgrano District
            [
                'name' => 'Villa General Roca',
                'district_id' => $this->districts['Belgrano'],
                'coordinates' => '-32.6667,-65.3833'
            ],
            [
                'name' => 'Fraga',
                'district_id' => $this->districts['Belgrano'],
                'coordinates' => '-32.9333,-65.3833'
            ],

            // San Martín District
            [
                'name' => 'San Martín',
                'district_id' => $this->districts['San Martín'],
                'coordinates' => '-33.0833,-68.4667'
            ],
            [
                'name' => 'Las Chacras',
                'district_id' => $this->districts['San Martín'],
                'coordinates' => '-33.3167,-68.4667'
            ],

            // Dupuy District
            [
                'name' => 'Buena Esperanza',
                'district_id' => $this->districts['Dupuy'],
                'coordinates' => '-34.7500,-65.2500'
            ],
            [
                'name' => 'Unión',
                'district_id' => $this->districts['Dupuy'],
                'coordinates' => '-35.1500,-65.9500'
            ]
        ];

        foreach ($localities as $i => $locality) {
            $locality['order'] = $i;
            Locality::create($locality);
        }
    }
    private function initDistricts()
    {
        $this->districts = District::all()->pluck('id', 'name')->toArray();
    }
}
