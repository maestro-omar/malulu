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
                'key' => '740058050',
                'name' => 'JARDÍN DE INFANTES N° 1 MARÍA MONTESSORI',
                'short' => 'JI N° 1',
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
                'key' => '740058100',
                'name' => 'ESCUELA NORMAL JUAN PASCUAL PRINGLES',
                'short' => 'ENJP',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'AV. ILLIA 445',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058100',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana/Tarde',
                ]),
                'levels' => [SchoolLevel::PRIMARY, SchoolLevel::SECONDARY]
            ],
            [
                'key' => '740058200',
                'name' => 'ESCUELA N° 1 DOMINGO FAUSTINO SARMIENTO',
                'short' => 'EP N° 1',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058200',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY, SchoolLevel::SECONDARY]
            ],
            [
                'key' => '740058300',
                'name' => 'ESCUELA TÉCNICA N° 26 ING. JUAN CICARELLI',
                'short' => 'ET N° 26',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'AV. ILLIA 445',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058300',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana/Tarde',
                ]),
                'levels' => [SchoolLevel::SECONDARY]
            ],
            [
                'key' => '740058400',
                'name' => 'ESCUELA N° 2 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 2',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058400',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740058500',
                'name' => 'ESCUELA N° 3 JUAN CRISÓSTOMO LAFINUR',
                'short' => 'EP N° 3',
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
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740058600',
                'name' => 'ESCUELA N° 4 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 4',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058600',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740058700',
                'name' => 'ESCUELA N° 5 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 5',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058700',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740058800',
                'name' => 'ESCUELA N° 6 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 6',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058800',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740058900',
                'name' => 'ESCUELA N° 7 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 7',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740058900',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740059000',
                'name' => 'ESCUELA N° 8 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 8',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740059000',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740059100',
                'name' => 'ESCUELA N° 9 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 9',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740059100',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740059200',
                'name' => 'ESCUELA N° 10 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 10',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740059200',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740059300',
                'name' => 'ESCUELA N° 11 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 11',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740059300',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740059400',
                'name' => 'ESCUELA N° 12 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 12',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740059400',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740059500',
                'name' => 'ESCUELA N° 13 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 13',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740059500',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740059600',
                'name' => 'ESCUELA N° 14 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 14',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740059600',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740059700',
                'name' => 'ESCUELA N° 15 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 15',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740059700',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740059800',
                'name' => 'ESCUELA N° 16 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 16',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740059800',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740059900',
                'name' => 'ESCUELA N° 17 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 17',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740059900',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740060000',
                'name' => 'ESCUELA N° 18 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 18',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740060000',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740060100',
                'name' => 'ESCUELA N° 19 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 19',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740060100',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740060200',
                'name' => 'ESCUELA N° 20 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 20',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740060200',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740060300',
                'name' => 'ESCUELA N° 21 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 21',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740060300',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740060400',
                'name' => 'ESCUELA N° 22 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 22',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740060400',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740060500',
                'name' => 'ESCUELA N° 23 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 23',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740060500',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740060600',
                'name' => 'ESCUELA N° 24 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 24',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740060600',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740060700',
                'name' => 'ESCUELA N° 25 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 25',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740060700',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740060800',
                'name' => 'ESCUELA N° 26 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 26',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740060800',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740060900',
                'name' => 'ESCUELA N° 27 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 27',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740060900',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740061000',
                'name' => 'ESCUELA N° 28 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 28',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740061000',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740061100',
                'name' => 'ESCUELA N° 29 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 29',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740061100',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740061200',
                'name' => 'ESCUELA N° 30 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 30',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740061200',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740061300',
                'name' => 'ESCUELA N° 31 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 31',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740061300',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740061400',
                'name' => 'ESCUELA N° 32 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 32',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740061400',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740061500',
                'name' => 'ESCUELA N° 33 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 33',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740061500',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740061600',
                'name' => 'ESCUELA N° 34 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 34',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740061600',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740061700',
                'name' => 'ESCUELA N° 35 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 35',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740061700',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740061800',
                'name' => 'ESCUELA N° 36 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 36',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740061800',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740061900',
                'name' => 'ESCUELA N° 37 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 37',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740061900',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740062000',
                'name' => 'ESCUELA N° 38 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 38',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740062000',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740062100',
                'name' => 'ESCUELA N° 39 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 39',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740062100',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740062200',
                'name' => 'ESCUELA N° 40 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 40',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740062200',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740062300',
                'name' => 'ESCUELA N° 41 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 41',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740062300',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740062400',
                'name' => 'ESCUELA N° 42 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 42',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740062400',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740062500',
                'name' => 'ESCUELA N° 43 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 43',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740062500',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740062600',
                'name' => 'ESCUELA N° 44 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 44',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740062600',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740062700',
                'name' => 'ESCUELA N° 45 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 45',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740062700',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740062800',
                'name' => 'ESCUELA N° 46 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 46',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740062800',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740062900',
                'name' => 'ESCUELA N° 47 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 47',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740062900',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740063000',
                'name' => 'ESCUELA N° 48 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 48',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740063000',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740063100',
                'name' => 'ESCUELA N° 49 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 49',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740063100',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740063200',
                'name' => 'ESCUELA N° 50 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 50',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740063200',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740063300',
                'name' => 'ESCUELA N° 51 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 51',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740063300',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740063400',
                'name' => 'ESCUELA N° 52 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 52',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740063400',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740063500',
                'name' => 'ESCUELA N° 53 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 53',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740063500',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740063600',
                'name' => 'ESCUELA N° 54 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 54',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740063600',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740063700',
                'name' => 'ESCUELA N° 55 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 55',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740063700',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740063800',
                'name' => 'ESCUELA N° 56 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 56',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740063800',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
            ],
            [
                'key' => '740063900',
                'name' => 'ESCUELA N° 57 JUAN BAUTISTA ALBERDI',
                'short' => 'EP N° 57',
                'locality_id' => $this->localities['San Luis'],
                'address' => 'PASEO DEL BOSQUE S/N',
                'zip_code' => 'D5700',
                'phone' => null,
                'email' => null,
                'coordinates' => '-33.3017,-66.3378',
                'cue' => '740063900',
                'extra' => json_encode([
                    'management_type' => 'Estatal',
                    'shift' => 'Mañana',
                ]),
                'levels' => [SchoolLevel::PRIMARY]
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
