<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\SchoolLevel;
use App\Models\Locality;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    private array $schoolLevels;
    private array $localities;

    public function run(): void
    {
        $this->initSchoolLevels();
        $this->initLocalities();

        $schools = [
            [
                'key' => 'GLOBAL',
                'name' => 'Global School',
                'short' => 'GLOBAL',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'System',
                'zip_code' => '0000',
                'phone' => null,
                'email' => null,
                'coordinates' => null,
                'cue' => 'GLOBAL',
                'extra' => json_encode([
                    'management_type' => 'System',
                    'shift' => 'System',
                ]),
                'levels' => [SchoolLevel::PRIMARY, SchoolLevel::SECONDARY]
            ],
            [
                'key' => '740058000',
                'name' => 'CENTRO EDUCATIVO N° 8 MAESTRAS LUCIO LUCERO',
                'short' => 'CE N° 8',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'RIOBAMBA 630',
                'zip_code' => 'D5700',
                'phone' => '2664426546',
                'email' => '',
                'coordinates' => '-33.2972111111111,-66.3128472222222',
                'cue' => '740058000',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana/Tarde',
                    'social' => [
                        [
                            'type' => 'facebook',
                            'label' => 'Facebook (nivel primario)',
                            'link' => 'https://www.facebook.com/profile.php?id=100063535634735'
                        ],
                        [
                            'type' => 'instagram',
                            'label' => 'Instagram (nivel secundario):',
                            'link' => 'https://www.instagram.com/luciolucerosecundaria/?hl=es'
                        ]
                    ]
                ]),
                'levels' => [SchoolLevel::PRIMARY, SchoolLevel::SECONDARY]
            ],
            [
                'key' => '740058100',
                'name' => 'CENTRO EDUCATIVO N°1 JUAN PASCUAL PRINGLES',
                'short' => 'CE N°1',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'AV. ILLIA 445',
                'zip_code' => 'D5700',
                'phone' => '2664426546',
                'email' => '',
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058100',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana/Tarde',
                ]),
                'levels' => [SchoolLevel::PRIMARY, SchoolLevel::SECONDARY]
            ],
            [
                'key' => '740058050',
                'name' => 'ESCUELA DE NIVEL INICIAL N°6 CRECER JUGANDO',
                'short' => 'ENI N°6',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058050',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::KINDER]
            ],
            [
                'key' => '740058500',
                'name' => 'CENTRO EDUCATIVO N°5 SENADOR ALFREDO BERTIN',
                'short' => 'CE N°5',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058500',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY, SchoolLevel::SECONDARY]
            ],
            [
                'key' => '740058040',
                'name' => 'ESCUELA DE NIVEL INICIAL N°5 MARIA EDELMIRA ALRIC DE CASTILLO',
                'short' => 'ENI N°5',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058040',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::KINDER]
            ],
            [
                'key' => '740058600',
                'name' => 'ESCUELA PUBLICA DIGITAL BILING NELSON MANDELA',
                'short' => 'EPD NELSON MANDELA',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058600',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana/Tarde',
                ]),
                'levels' => [SchoolLevel::PRIMARY, SchoolLevel::SECONDARY]
            ],
            [
                'key' => '740058700',
                'name' => 'CENTRO EDUCATIVO N°27 GOBERNADOR SANTIAGO BESSO',
                'short' => 'CE N°27',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058700',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana/Tarde',
                ]),
                'levels' => [SchoolLevel::PRIMARY, SchoolLevel::SECONDARY]
            ],
            // Add more schools here...
        ];

        foreach ($schools as $school) {
            $levels = $school['levels'];
            unset($school['levels']); // Remove levels from the school data before creating

            $newSchool = School::create($school);

            // Attach the predefined levels
            $levelIds = array_map(function ($level) {
                return $this->schoolLevels[$level];
            }, $levels);

            $newSchool->schoolLevels()->attach($levelIds);
        }
    }

    private function initSchoolLevels()
    {
        $this->schoolLevels = SchoolLevel::all()->pluck('id', 'key')->toArray();
    }

    private function initLocalities()
    {
        $this->localities = Locality::all()->pluck('id', 'name')->toArray();
    }
}
