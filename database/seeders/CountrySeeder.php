<?php

namespace Database\Seeders;

use App\Models\Catalogs\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            // South America
            [
                'iso' => Country::DEFAULT,
                'name' => 'Argentina',
            ],
            [
                'iso' => 'cl',
                'name' => 'Chile',
            ],
            [
                'iso' => 'bo',
                'name' => 'Bolivia',
            ],
            [
                'iso' => 'py',
                'name' => 'Paraguay',
            ],
            [
                'iso' => 'uy',
                'name' => 'Uruguay',
            ],
            [
                'iso' => 'pe',
                'name' => 'Perú',
            ],
            [
                'iso' => 'br',
                'name' => 'Brasil',
            ],
            [
                'iso' => 've',
                'name' => 'Venezuela',
            ],
            [
                'iso' => 'co',
                'name' => 'Colombia',
            ],
            [
                'iso' => 'ec',
                'name' => 'Ecuador',
            ],
            [
                'iso' => 'mx',
                'name' => 'México',
            ],
            //

            [
                'iso' => 'bz',
                'name' => 'Belize',
            ],
            [
                'iso' => 'cr',
                'name' => 'Costa Rica',
            ],
            [
                'iso' => 'sv',
                'name' => 'El Salvador',
            ],
            [
                'iso' => 'gf',
                'name' => 'French Guiana',
            ],
            [
                'iso' => 'gt',
                'name' => 'Guatemala',
            ],
            [
                'iso' => 'gy',
                'name' => 'Guyana',
            ],
            [
                'iso' => 'hn',
                'name' => 'Honduras',
            ],
            [
                'iso' => 'ni',
                'name' => 'Nicaragua',
            ],
            [
                'iso' => 'pa',
                'name' => 'Panamá',
            ],
            [
                'iso' => 'sr',
                'name' => 'Suriname',
            ],
            [
                'iso' => 'af',
                'name' => 'Afganistán',
            ],
            [
                'iso' => 'at',
                'name' => 'Austria',
            ],
            [
                'iso' => 'au',
                'name' => 'Australia',
            ],
            [
                'iso' => 'az',
                'name' => 'Azerbaiyán',
            ],
            [
                'iso' => 'ae',
                'name' => 'Emiratos Árabes Unidos',
            ],
            [
                'iso' => 'am',
                'name' => 'Armenia',
            ],
            [
                'iso' => 'ao',
                'name' => 'Angola',
            ],
            [
                'iso' => 'be',
                'name' => 'Bélgica',
            ],
            [
                'iso' => 'bd',
                'name' => 'Bangladesh',
            ],
            [
                'iso' => 'bg',
                'name' => 'Bulgaria',
            ],
            [
                'iso' => 'bh',
                'name' => 'Baréin',
            ],
            [
                'iso' => 'bj',
                'name' => 'Benín',
            ],
            [
                'iso' => 'by',
                'name' => 'Bielorrusia',
            ],
            [
                'iso' => 'bi',
                'name' => 'Burundi',
            ],
            [
                'iso' => 'bf',
                'name' => 'Burkina Faso',
            ],
            [
                'iso' => 'bw',
                'name' => 'Botsuana',
            ],
            [
                'iso' => 'bt',
                'name' => 'Bután',
            ],
            [
                'iso' => 'bn',
                'name' => 'Brunéi',
            ],
            [
                'iso' => 'ca',
                'name' => 'Canadá',
            ],
            [
                'iso' => 'cn',
                'name' => 'China',
            ],
            [
                'iso' => 'cm',
                'name' => 'Camerún',
            ],
            [
                'iso' => 'cv',
                'name' => 'Cabo Verde',
            ],
            [
                'iso' => 'cy',
                'name' => 'Chipre',
            ],
            [
                'iso' => 'ci',
                'name' => 'Costa de Marfil',
            ],
            [
                'iso' => 'kh',
                'name' => 'Camboya',
            ],
            [
                'iso' => 'cg',
                'name' => 'Congo',
            ],
            [
                'iso' => 'cd',
                'name' => 'República Democrática del Congo',
            ],
            [
                'iso' => 'cf',
                'name' => 'República Centroafricana',
            ],
            [
                'iso' => 'kr',
                'name' => 'Corea del Sur',
            ],
            [
                'iso' => 'hr',
                'name' => 'Croacia',
            ],
            [
                'iso' => 'cz',
                'name' => 'República Checa',
            ],
            [
                'iso' => 'dk',
                'name' => 'Dinamarca',
            ],
            [
                'iso' => 'dj',
                'name' => 'Yibuti',
            ],
            [
                'iso' => 'eg',
                'name' => 'Egipto',
            ],
            [
                'iso' => 'es',
                'name' => 'España',
            ],
            [
                'iso' => 'ee',
                'name' => 'Estonia',
            ],
            [
                'iso' => 'et',
                'name' => 'Etiopía',
            ],
            [
                'iso' => 'er',
                'name' => 'Eritrea',
            ],
            [
                'iso' => 'us',
                'name' => 'Estados Unidos',
            ],
            [
                'iso' => 'fj',
                'name' => 'Fiyi',
            ],
            [
                'iso' => 'fi',
                'name' => 'Finlandia',
            ],
            [
                'iso' => 'fr',
                'name' => 'Francia',
            ],
            [
                'iso' => 'ga',
                'name' => 'Gabón',
            ],
            [
                'iso' => 'gm',
                'name' => 'Gambia',
            ],
            [
                'iso' => 'ge',
                'name' => 'Georgia',
            ],
            [
                'iso' => 'gh',
                'name' => 'Ghana',
            ],
            [
                'iso' => 'gn',
                'name' => 'Guinea',
            ],
            [
                'iso' => 'gq',
                'name' => 'Guinea Ecuatorial',
            ],
            [
                'iso' => 'gw',
                'name' => 'Guinea-Bisáu',
            ],
            [
                'iso' => 'gr',
                'name' => 'Grecia',
            ],
            [
                'iso' => 'hk',
                'name' => 'Hong Kong',
            ],
            [
                'iso' => 'hu',
                'name' => 'Hungría',
            ],
            [
                'iso' => 'in',
                'name' => 'India',
            ],
            [
                'iso' => 'id',
                'name' => 'Indonesia',
            ],
            [
                'iso' => 'ie',
                'name' => 'Irlanda',
            ],
            [
                'iso' => 'ir',
                'name' => 'Irán',
            ],
            [
                'iso' => 'iq',
                'name' => 'Irak',
            ],
            [
                'iso' => 'is',
                'name' => 'Islandia',
            ],
            [
                'iso' => 'mh',
                'name' => 'Islas Marshall',
            ],
            [
                'iso' => 'sb',
                'name' => 'Islas Salomón',
            ],
            [
                'iso' => 'il',
                'name' => 'Israel',
            ],
            [
                'iso' => 'it',
                'name' => 'Italia',
            ],
            [
                'iso' => 'jp',
                'name' => 'Japón',
            ],
            [
                'iso' => 'jo',
                'name' => 'Jordania',
            ],
            [
                'iso' => 'kz',
                'name' => 'Kazajistán',
            ],
            [
                'iso' => 'ke',
                'name' => 'Kenia',
            ],
            [
                'iso' => 'ki',
                'name' => 'Kiribati',
            ],
            [
                'iso' => 'kg',
                'name' => 'Kirguistán',
            ],
            [
                'iso' => 'kw',
                'name' => 'Kuwait',
            ],
            [
                'iso' => 'la',
                'name' => 'Laos',
            ],
            [
                'iso' => 'ls',
                'name' => 'Lesoto',
            ],
            [
                'iso' => 'lv',
                'name' => 'Letonia',
            ],
            [
                'iso' => 'lb',
                'name' => 'Líbano',
            ],
            [
                'iso' => 'lr',
                'name' => 'Liberia',
            ],
            [
                'iso' => 'ly',
                'name' => 'Libia',
            ],
            [
                'iso' => 'lt',
                'name' => 'Lituania',
            ],
            [
                'iso' => 'lu',
                'name' => 'Luxemburgo',
            ],
            [
                'iso' => 'lk',
                'name' => 'Sri Lanka',
            ],
            [
                'iso' => 'mo',
                'name' => 'Macao',
            ],
            [
                'iso' => 'mg',
                'name' => 'Madagascar',
            ],
            [
                'iso' => 'my',
                'name' => 'Malasia',
            ],
            [
                'iso' => 'ml',
                'name' => 'Malí',
            ],
            [
                'iso' => 'mt',
                'name' => 'Malta',
            ],
            [
                'iso' => 'ma',
                'name' => 'Marruecos',
            ],
            [
                'iso' => 'mr',
                'name' => 'Mauritania',
            ],
            [
                'iso' => 'mu',
                'name' => 'Mauricio',
            ],
            [
                'iso' => 'md',
                'name' => 'Moldavia',
            ],
            [
                'iso' => 'mn',
                'name' => 'Mongolia',
            ],
            [
                'iso' => 'mz',
                'name' => 'Mozambique',
            ],
            [
                'iso' => 'mm',
                'name' => 'Myanmar',
            ],
            [
                'iso' => 'na',
                'name' => 'Namibia',
            ],
            [
                'iso' => 'np',
                'name' => 'Nepal',
            ],
            [
                'iso' => 'ne',
                'name' => 'Níger',
            ],
            [
                'iso' => 'ng',
                'name' => 'Nigeria',
            ],
            [
                'iso' => 'no',
                'name' => 'Noruega',
            ],
            [
                'iso' => 'nz',
                'name' => 'Nueva Zelanda',
            ],
            [
                'iso' => 'nr',
                'name' => 'Nauru',
            ],
            [
                'iso' => 'om',
                'name' => 'Omán',
            ],
            [
                'iso' => 'pk',
                'name' => 'Pakistán',
            ],
            [
                'iso' => 'pg',
                'name' => 'Papúa Nueva Guinea',
            ],
            [
                'iso' => 'pw',
                'name' => 'Palaos',
            ],
            [
                'iso' => 'nl',
                'name' => 'Países Bajos',
            ],
            [
                'iso' => 'ph',
                'name' => 'Filipinas',
            ],
            [
                'iso' => 'pl',
                'name' => 'Polonia',
            ],
            [
                'iso' => 'pt',
                'name' => 'Portugal',
            ],
            [
                'iso' => 'qa',
                'name' => 'Catar',
            ],
            [
                'iso' => 'ru',
                'name' => 'Rusia',
            ],
            [
                'iso' => 'ro',
                'name' => 'Rumania',
            ],
            [
                'iso' => 'rw',
                'name' => 'Ruanda',
            ],
            [
                'iso' => 'rs',
                'name' => 'Serbia',
            ],
            [
                'iso' => 'sc',
                'name' => 'Seychelles',
            ],
            [
                'iso' => 'sg',
                'name' => 'Singapur',
            ],
            [
                'iso' => 'si',
                'name' => 'Eslovenia',
            ],
            [
                'iso' => 'sk',
                'name' => 'Eslovaquia',
            ],
            [
                'iso' => 'so',
                'name' => 'Somalia',
            ],
            [
                'iso' => 'st',
                'name' => 'Santo Tomé y Príncipe',
            ],
            [
                'iso' => 'sz',
                'name' => 'Suazilandia',
            ],
            [
                'iso' => 'ch',
                'name' => 'Suiza',
            ],
            [
                'iso' => 'se',
                'name' => 'Suecia',
            ],
            [
                'iso' => 'sd',
                'name' => 'Sudán',
            ],
            [
                'iso' => 'ss',
                'name' => 'Sudán del Sur',
            ],
            [
                'iso' => 'za',
                'name' => 'Sudáfrica',
            ],
            [
                'iso' => 'sy',
                'name' => 'Siria',
            ],
            [
                'iso' => 'th',
                'name' => 'Tailandia',
            ],
            [
                'iso' => 'tw',
                'name' => 'Taiwán',
            ],
            [
                'iso' => 'tj',
                'name' => 'Tayikistán',
            ],
            [
                'iso' => 'tz',
                'name' => 'Tanzania',
            ],
            [
                'iso' => 'td',
                'name' => 'Chad',
            ],
            [
                'iso' => 'tg',
                'name' => 'Togo',
            ],
            [
                'iso' => 'to',
                'name' => 'Tonga',
            ],
            [
                'iso' => 'tr',
                'name' => 'Turquía',
            ],
            [
                'iso' => 'tm',
                'name' => 'Turkmenistán',
            ],
            [
                'iso' => 'tv',
                'name' => 'Tuvalu',
            ],
            [
                'iso' => 'ua',
                'name' => 'Ucrania',
            ],
            [
                'iso' => 'ug',
                'name' => 'Uganda',
            ],
            [
                'iso' => 'uz',
                'name' => 'Uzbekistán',
            ],
            [
                'iso' => 'vu',
                'name' => 'Vanuatu',
            ],
            [
                'iso' => 'vn',
                'name' => 'Vietnam',
            ],
            [
                'iso' => 'ye',
                'name' => 'Yemen',
            ],
            [
                'iso' => 'zm',
                'name' => 'Zambia',
            ],
            [
                'iso' => 'zw',
                'name' => 'Zimbabue',
            ],

        ];

        foreach ($countries as $i => $country) {
            $country['order'] = $i;
            Country::create($country);
        }
    }
}
