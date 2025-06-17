<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\SchoolLevel;
use App\Models\Locality;
use App\Models\SchoolManagementType;
use App\Models\SchoolShift;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    private array $schoolLevels;
    private array $localities;
    private array $managementTypes;
    private array $shifts;

    public function run(): void
    {
        $this->initSchoolLevels();
        $this->initLocalities();
        $this->initManagementTypes();
        $this->initShifts();

        $schools = [
            [
                'code' => School::GLOBAL,
                'name' => 'GLOBAL',
                'short' => 'GLOBAL',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'System',
                'zip_code' => '0000',
                'phone' => null,
                'email' => null,
                'coordinates' => null,
                'cue' => 'GLOBAL',
                'management_type_id' => $this->managementTypes['state'],
                'social' => null,
                'extra' => null,
                'levels' => [SchoolLevel::PRIMARY, SchoolLevel::SECONDARY],
                'shifts' => ['morning']
            ],
            [
                'code' => '740058000',
                'name' => 'CENTRO EDUCATIVO N° 8 MAESTRAS LUCIO LUCERO',
                'short' => 'CE N° 8',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'RIOBAMBA 630',
                'zip_code' => 'D5700',
                'phone' => '2664426546',
                'email' => 'centroeducativo8@gmail.com',
                'coordinates' => '-33.2972111111111,-66.3128472222222',
                'cue' => '740058000',
                'management_type_id' => $this->managementTypes['state'],
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
                ],
                'extra' => null,
                'levels' => [SchoolLevel::PRIMARY, SchoolLevel::SECONDARY],
                'shifts' => ['morning', 'afternoon']
            ],
            [
                'code' => '740058100',
                'name' => 'CENTRO EDUCATIVO N°1 JUAN PASCUAL PRINGLES',
                'short' => 'CE N°1',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'AV. ILLIA 445',
                'zip_code' => 'D5700',
                'phone' => '2664426546',
                'email' => '',
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058100',
                'management_type_id' => $this->managementTypes['state'],
                'social' => null,
                'extra' => null,
                'levels' => [SchoolLevel::PRIMARY, SchoolLevel::SECONDARY],
                'shifts' => ['morning', 'afternoon']
            ],
            [
                'code' => '740058050',
                'name' => 'ESCUELA DE NIVEL INICIAL N°6 CRECER JUGANDO',
                'short' => 'ENI N°6',
                'locality_id' => $this->localities['Villa Mercedes'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5730',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058050',
                'management_type_id' => $this->managementTypes['state'],
                'social' => null,
                'extra' => null,
                'levels' => [SchoolLevel::KINDER],
                'shifts' => ['morning']
            ],
            [
                'code' => '740058500',
                'name' => 'CENTRO EDUCATIVO N°5 SENADOR ALFREDO BERTIN',
                'short' => 'CE N°5',
                'locality_id' => $this->localities['Merlo'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5881',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058500',
                'management_type_id' => $this->managementTypes['state'],
                'social' => null,
                'extra' => null,
                'levels' => [SchoolLevel::PRIMARY, SchoolLevel::SECONDARY],
                'shifts' => ['morning']
            ],
            [
                'code' => '740058040',
                'name' => 'ESCUELA DE NIVEL INICIAL N°5 MARIA EDELMIRA ALRIC DE CASTILLO',
                'short' => 'ENI N°5',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058040',
                'management_type_id' => $this->managementTypes['state'],
                'social' => null,
                'extra' => null,
                'levels' => [SchoolLevel::KINDER],
                'shifts' => ['morning']
            ],
            [
                'code' => '740058600',
                'name' => 'ESCUELA PUBLICA DIGITAL BILING NELSON MANDELA',
                'short' => 'EPD NELSON MANDELA',
                'locality_id' => $this->localities['La Punta'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5717',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058600',
                'management_type_id' => $this->managementTypes['state'],
                'social' => null,
                'extra' => null,
                'levels' => [SchoolLevel::PRIMARY, SchoolLevel::SECONDARY],
                'shifts' => ['morning', 'afternoon']
            ],
            [
                'code' => '740058700',
                'name' => 'CENTRO EDUCATIVO N°27 GOBERNADOR SANTIAGO BESSO',
                'short' => 'CE N°27',
                'locality_id' => $this->localities['Juana Koslay'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5703',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058700',
                'management_type_id' => $this->managementTypes['state'],
                'social' => null,
                'extra' => null,
                'levels' => [SchoolLevel::PRIMARY, SchoolLevel::SECONDARY],
                'shifts' => ['morning', 'afternoon']
            ],
        ];

        foreach ($schools as $school) {
            $levels = $school['levels'];
            $shifts = $school['shifts'] ?? [];
            unset($school['levels'], $school['shifts']); // Remove levels and shifts from the school data before creating

            $newSchool = School::create($school);

            // Attach the predefined levels
            $levelIds = array_map(function ($level) {
                return $this->schoolLevels[$level];
            }, $levels);
            $newSchool->schoolLevels()->attach($levelIds);

            // Attach the predefined shifts if any
            if (!empty($shifts)) {
                $shiftIds = array_map(function ($shift) {
                    return $this->shifts[$shift];
                }, $shifts);
                $newSchool->shifts()->attach($shiftIds);
            }
        }
    }

    private function initSchoolLevels()
    {
        $this->schoolLevels = SchoolLevel::all()->pluck('id', 'code')->toArray();
    }

    private function initLocalities()
    {
        $this->localities = Locality::all()->pluck('id', 'name')->toArray();
    }

    private function initManagementTypes()
    {
        $this->managementTypes = SchoolManagementType::all()->pluck('id', 'code')->toArray();
    }

    private function initShifts()
    {
        $this->shifts = SchoolShift::all()->pluck('id', 'code')->toArray();
    }
}
