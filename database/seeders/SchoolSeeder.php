<?php

namespace Database\Seeders;

use App\Models\Entities\School;
use App\Models\Catalogs\SchoolLevel;
use App\Models\Catalogs\Locality;
use App\Models\Catalogs\SchoolManagementType;
use App\Models\Catalogs\SchoolShift;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class SchoolSeeder extends Seeder
{
    private $SIMPLE_LUCIO_LUCERO = false;
    private $SIMPLE_LUCIO_LUCERO_ONLY_PRIMARY = false;

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

        $this->SIMPLE_LUCIO_LUCERO = config('malulu.one_school_cue') === School::CUE_LUCIO_LUCERO;
        $this->SIMPLE_LUCIO_LUCERO_ONLY_PRIMARY = config('malulu.one_school_only_primary');

        // Simulate upload for school School::CUE_LUCIO_LUCERO
        $pictureSource = database_path('seeders/images/ce-n8-picture.png');
        $logoSource = database_path('seeders/images/ce-n8-logo-2025.png');
        $pictureDest = 'school-logos/' . School::CUE_LUCIO_LUCERO . '/ce-n8-picture.png';
        $logoDest = 'school-logos/' . School::CUE_LUCIO_LUCERO . '/ce-n8-logo-2025.png';
        if (file_exists($pictureSource) && !Storage::disk('public')->exists($pictureDest)) {
            Storage::disk('public')->put($pictureDest, file_get_contents($pictureSource));
        }
        if (file_exists($logoSource) && !Storage::disk('public')->exists($logoDest)) {
            Storage::disk('public')->put($logoDest, file_get_contents($logoSource));
        }

        $mainSchools = [
            [
                'code' => School::GLOBAL,
                'name' => School::GLOBAL,
                'short' => School::GLOBAL,
                'locality_id' => $this->localities['San Luis'],
                'address' => 'System',
                'zip_code' => '0000',
                'phone' => null,
                'email' => null,
                'coordinates' => null,
                'cue' => School::GLOBAL,
                'management_type_id' => $this->managementTypes[SchoolManagementType::PUBLIC],
                'social' => null,
                'extra' => null,
                'levels' => [SchoolLevel::PRIMARY],
                'shifts' => [SchoolShift::MORNING]
            ],
            [
                'code' => School::CUE_LUCIO_LUCERO,
                'name' => 'Centro Educativo n°8 Maestras Lucio Lucero',
                'short' => 'CE n°8',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'Riobamba 630',
                'zip_code' => 'D5700',
                'phone' => '2664426546',
                'email' => 'centroeducativo8@gmail.com',
                'coordinates' => '-33.2972111111111,-66.3128472222222',
                'cue' => School::CUE_LUCIO_LUCERO,
                'management_type_id' => $this->managementTypes[SchoolManagementType::PUBLIC],
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
                'levels' => $this->SIMPLE_LUCIO_LUCERO_ONLY_PRIMARY ? [SchoolLevel::PRIMARY] : [SchoolLevel::PRIMARY, SchoolLevel::SECONDARY],
                'shifts' => [SchoolShift::MORNING, SchoolShift::AFTERNOON],
                'picture' => '/storage/' . $pictureDest,
                'logo' => '/storage/' . $logoDest,
            ],
        ];

        $otherSchools = $this->SIMPLE_LUCIO_LUCERO ? [] : [
            [
                'code' => '740058100',
                'name' => 'Centro Educativo n°1 Juan Pascual Pringles',
                'short' => 'CE n°1',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'Av. Illia 445',
                'zip_code' => 'D5700',
                'phone' => '2664426546',
                'email' => '',
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058100',
                'management_type_id' => $this->managementTypes[SchoolManagementType::PUBLIC],
                'social' => null,
                'extra' => null,
                'levels' => [SchoolLevel::PRIMARY, SchoolLevel::SECONDARY],
                'shifts' => [SchoolShift::MORNING, SchoolShift::AFTERNOON]
            ],
            [
                'code' => '740058050',
                'name' => 'Escuela de Nivel Inicial Patito Feo',
                'short' => 'ENI n°6',
                'locality_id' => $this->localities['Villa Mercedes'],
                'address' => 'Paseo del Bosque S/N',
                'zip_code' => 'D5730',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058050',
                'management_type_id' => $this->managementTypes[SchoolManagementType::PRIVATE],
                'social' => null,
                'extra' => null,
                'levels' => [SchoolLevel::KINDER],
                'shifts' => [SchoolShift::MORNING]
            ],
            [
                'code' => '740058500',
                'name' => 'Centro Educativo n°5 Senador Alfredo Bertin',
                'short' => 'CE n°5',
                'locality_id' => $this->localities['Merlo'],
                'address' => 'Paseo del Bosque S/N',
                'zip_code' => 'D5881',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058500',
                'management_type_id' => $this->managementTypes[SchoolManagementType::GENERATIVE],
                'social' => null,
                'extra' => null,
                'levels' => [SchoolLevel::PRIMARY, SchoolLevel::SECONDARY],
                'shifts' => [SchoolShift::MORNING]
            ],
            [
                'code' => '740058040',
                'name' => 'Escuela de Nivel Inicial n°5 Maria Edelmira Alric de Castillo',
                'short' => 'ENI n°5',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'Paseo del Bosque S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058040',
                'management_type_id' => $this->managementTypes[SchoolManagementType::SELF_MANAGED],
                'social' => null,
                'extra' => null,
                'levels' => [SchoolLevel::KINDER],
                'shifts' => [SchoolShift::MORNING]
            ],
            [
                'code' => '740058600',
                'name' => 'Escuela Publica Digital Biling Nelson Mandela',
                'short' => 'EPD Nelson Mandela',
                'locality_id' => $this->localities['La Punta'],
                'address' => 'Paseo del Bosque S/N',
                'zip_code' => 'D5717',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058600',
                'management_type_id' => $this->managementTypes[SchoolManagementType::PUBLIC],
                'social' => null,
                'extra' => null,
                'levels' => [SchoolLevel::PRIMARY, SchoolLevel::SECONDARY],
                'shifts' => [SchoolShift::MORNING, SchoolShift::AFTERNOON]
            ],
            [
                'code' => '740058700',
                'name' => 'Centro Educativo n°27 Gobernador Santiago Besso',
                'short' => 'CE n°27',
                'locality_id' => $this->localities['Juana Koslay'],
                'address' => 'Paseo del Bosque S/N',
                'zip_code' => 'D5703',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058700',
                'management_type_id' => $this->managementTypes[SchoolManagementType::PUBLIC],
                'social' => null,
                'extra' => null,
                'levels' => [SchoolLevel::PRIMARY, SchoolLevel::SECONDARY],
                'shifts' => [SchoolShift::MORNING, SchoolShift::AFTERNOON]
            ],
        ];

        foreach (array_merge($mainSchools, $otherSchools) as $school) {
            $levels = $school['levels'];
            $shifts = $school['shifts'] ?? [];
            unset($school['levels'], $school['shifts']); // Remove levels and shifts from the school data before creating

            // Add slug field based on short field
            $school['slug'] = Str::slug($school['short']);

            $newSchool = School::create($school);

            // Attach the predefined levels (null for KINDER; grades=8 for Lucio Lucero primary)
            $attachData = [];
            foreach ($levels as $level) {
                $levelId = $this->schoolLevels[$level];
                if ($level === SchoolLevel::KINDER) {
                    $attachData[$levelId] = ['years_duration' => null, 'grades' => null];
                } else {
                    $isLucioPrimary = ($school['cue'] ?? '') === School::CUE_LUCIO_LUCERO && $level === SchoolLevel::PRIMARY;
                    $attachData[$levelId] = [
                        'years_duration' => 6,
                        'grades' => $isLucioPrimary ? 8 : 6,
                    ];
                }
            }
            $newSchool->schoolLevels()->attach($attachData);

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
