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
                'iso' => 'gy',
                'name' => 'Guyana',
            ],
            [
                'iso' => 'sr',
                'name' => 'Suriname',
            ],
            [
                'iso' => 'gf',
                'name' => 'French Guiana',
            ],
            // Central America
            [
                'iso' => 'mx',
                'name' => 'México',
            ],
            [
                'iso' => 'gt',
                'name' => 'Guatemala',
            ],
            [
                'iso' => 'bz',
                'name' => 'Belize',
            ],
            [
                'iso' => 'sv',
                'name' => 'El Salvador',
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
                'iso' => 'cr',
                'name' => 'Costa Rica',
            ],
            [
                'iso' => 'pa',
                'name' => 'Panamá',
            ],
            // Europe
            [
                'iso' => 'es',
                'name' => 'España',
            ],
            [
                'iso' => 'pt',
                'name' => 'Portugal',
            ],
            [
                'iso' => 'fr',
                'name' => 'Francia',
            ],
            [
                'iso' => 'de',
                'name' => 'Alemania',
            ],
            [
                'iso' => 'it',
                'name' => 'Italia',
            ],
            [
                'iso' => 'gb',
                'name' => 'Reino Unido',
            ],
            [
                'iso' => 'nl',
                'name' => 'Países Bajos',
            ],
            [
                'iso' => 'be',
                'name' => 'Bélgica',
            ],
            [
                'iso' => 'ch',
                'name' => 'Suiza',
            ],
            [
                'iso' => 'at',
                'name' => 'Austria',
            ],
            [
                'iso' => 'se',
                'name' => 'Suecia',
            ],
            [
                'iso' => 'no',
                'name' => 'Noruega',
            ],
            [
                'iso' => 'dk',
                'name' => 'Dinamarca',
            ],
            [
                'iso' => 'fi',
                'name' => 'Finlandia',
            ],
            [
                'iso' => 'pl',
                'name' => 'Polonia',
            ],
            [
                'iso' => 'gr',
                'name' => 'Grecia',
            ],
            [
                'iso' => 'ie',
                'name' => 'Irlanda',
            ],
            [
                'iso' => 'cz',
                'name' => 'República Checa',
            ],
            [
                'iso' => 'sk',
                'name' => 'Eslovaquia',
            ],
            [
                'iso' => 'hu',
                'name' => 'Hungría',
            ],
            [
                'iso' => 'ro',
                'name' => 'Rumania',
            ],
            [
                'iso' => 'bg',
                'name' => 'Bulgaria',
            ],
            [
                'iso' => 'hr',
                'name' => 'Croacia',
            ],
            [
                'iso' => 'rs',
                'name' => 'Serbia',
            ],
            [
                'iso' => 'si',
                'name' => 'Eslovenia',
            ],
            [
                'iso' => 'lt',
                'name' => 'Lituania',
            ],
            [
                'iso' => 'lv',
                'name' => 'Letonia',
            ],
            [
                'iso' => 'ee',
                'name' => 'Estonia',
            ],
            [
                'iso' => 'is',
                'name' => 'Islandia',
            ],
            [
                'iso' => 'lu',
                'name' => 'Luxemburgo',
            ],
            [
                'iso' => 'mt',
                'name' => 'Malta',
            ],
            [
                'iso' => 'cy',
                'name' => 'Chipre',
            ],
            // Asia
            [
                'iso' => 'cn',
                'name' => 'China',
            ],
            [
                'iso' => 'jp',
                'name' => 'Japón',
            ],
            [
                'iso' => 'kr',
                'name' => 'Corea del Sur',
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
                'iso' => 'my',
                'name' => 'Malasia',
            ],
            [
                'iso' => 'sg',
                'name' => 'Singapur',
            ],
            [
                'iso' => 'th',
                'name' => 'Tailandia',
            ],
            [
                'iso' => 'vn',
                'name' => 'Vietnam',
            ],
            [
                'iso' => 'ph',
                'name' => 'Filipinas',
            ],
            [
                'iso' => 'ae',
                'name' => 'Emiratos Árabes Unidos',
            ],
            [
                'iso' => 'sa',
                'name' => 'Arabia Saudita',
            ],
            [
                'iso' => 'tr',
                'name' => 'Turquía',
            ],
            [
                'iso' => 'tw',
                'name' => 'Taiwán',
            ],
            [
                'iso' => 'hk',
                'name' => 'Hong Kong',
            ],
            [
                'iso' => 'mo',
                'name' => 'Macao',
            ],
            [
                'iso' => 'pk',
                'name' => 'Pakistán',
            ],
            [
                'iso' => 'bd',
                'name' => 'Bangladesh',
            ],
            [
                'iso' => 'lk',
                'name' => 'Sri Lanka',
            ],
            [
                'iso' => 'np',
                'name' => 'Nepal',
            ],
            [
                'iso' => 'bt',
                'name' => 'Bután',
            ],
            [
                'iso' => 'mm',
                'name' => 'Myanmar',
            ],
            [
                'iso' => 'la',
                'name' => 'Laos',
            ],
            [
                'iso' => 'kh',
                'name' => 'Camboya',
            ],
            [
                'iso' => 'bn',
                'name' => 'Brunéi',
            ],
            [
                'iso' => 'qa',
                'name' => 'Catar',
            ],
            [
                'iso' => 'kw',
                'name' => 'Kuwait',
            ],
            [
                'iso' => 'bh',
                'name' => 'Baréin',
            ],
            [
                'iso' => 'om',
                'name' => 'Omán',
            ],
            [
                'iso' => 'ye',
                'name' => 'Yemen',
            ],
            [
                'iso' => 'jo',
                'name' => 'Jordania',
            ],
            [
                'iso' => 'lb',
                'name' => 'Líbano',
            ],
            [
                'iso' => 'sy',
                'name' => 'Siria',
            ],
            [
                'iso' => 'iq',
                'name' => 'Irak',
            ],
            [
                'iso' => 'ir',
                'name' => 'Irán',
            ],
            [
                'iso' => 'af',
                'name' => 'Afganistán',
            ],
            [
                'iso' => 'kz',
                'name' => 'Kazajistán',
            ],
            [
                'iso' => 'uz',
                'name' => 'Uzbekistán',
            ],
            [
                'iso' => 'tm',
                'name' => 'Turkmenistán',
            ],
            [
                'iso' => 'tj',
                'name' => 'Tayikistán',
            ],
            [
                'iso' => 'kg',
                'name' => 'Kirguistán',
            ],
            [
                'iso' => 'mn',
                'name' => 'Mongolia',
            ],
            // Africa
            [
                'iso' => 'za',
                'name' => 'Sudáfrica',
            ],
            [
                'iso' => 'eg',
                'name' => 'Egipto',
            ],
            [
                'iso' => 'ma',
                'name' => 'Marruecos',
            ],
            [
                'iso' => 'ng',
                'name' => 'Nigeria',
            ],
            [
                'iso' => 'ke',
                'name' => 'Kenia',
            ],
            [
                'iso' => 'gh',
                'name' => 'Ghana',
            ],
            [
                'iso' => 'et',
                'name' => 'Etiopía',
            ],
            [
                'iso' => 'tn',
                'name' => 'Túnez',
            ],
            [
                'iso' => 'dz',
                'name' => 'Argelia',
            ],
            [
                'iso' => 'tz',
                'name' => 'Tanzania',
            ],
            [
                'iso' => 'ug',
                'name' => 'Uganda',
            ],
            [
                'iso' => 'rw',
                'name' => 'Ruanda',
            ],
            [
                'iso' => 'bi',
                'name' => 'Burundi',
            ],
            [
                'iso' => 'cm',
                'name' => 'Camerún',
            ],
            [
                'iso' => 'sn',
                'name' => 'Senegal',
            ],
            [
                'iso' => 'ci',
                'name' => 'Costa de Marfil',
            ],
            [
                'iso' => 'mg',
                'name' => 'Madagascar',
            ],
            [
                'iso' => 'zm',
                'name' => 'Zambia',
            ],
            [
                'iso' => 'zw',
                'name' => 'Zimbabue',
            ],
            [
                'iso' => 'ao',
                'name' => 'Angola',
            ],
            [
                'iso' => 'mz',
                'name' => 'Mozambique',
            ],
            [
                'iso' => 'bw',
                'name' => 'Botsuana',
            ],
            [
                'iso' => 'na',
                'name' => 'Namibia',
            ],
            [
                'iso' => 'sz',
                'name' => 'Suazilandia',
            ],
            [
                'iso' => 'ls',
                'name' => 'Lesoto',
            ],
            [
                'iso' => 'mu',
                'name' => 'Mauricio',
            ],
            [
                'iso' => 'sc',
                'name' => 'Seychelles',
            ],
            [
                'iso' => 'cv',
                'name' => 'Cabo Verde',
            ],
            [
                'iso' => 'st',
                'name' => 'Santo Tomé y Príncipe',
            ],
            [
                'iso' => 'gq',
                'name' => 'Guinea Ecuatorial',
            ],
            [
                'iso' => 'ga',
                'name' => 'Gabón',
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
                'iso' => 'td',
                'name' => 'Chad',
            ],
            [
                'iso' => 'ne',
                'name' => 'Níger',
            ],
            [
                'iso' => 'ml',
                'name' => 'Malí',
            ],
            [
                'iso' => 'bf',
                'name' => 'Burkina Faso',
            ],
            [
                'iso' => 'mr',
                'name' => 'Mauritania',
            ],
            [
                'iso' => 'gm',
                'name' => 'Gambia',
            ],
            [
                'iso' => 'gw',
                'name' => 'Guinea-Bisáu',
            ],
            [
                'iso' => 'gn',
                'name' => 'Guinea',
            ],
            [
                'iso' => 'sl',
                'name' => 'Sierra Leona',
            ],
            [
                'iso' => 'lr',
                'name' => 'Liberia',
            ],
            [
                'iso' => 'bj',
                'name' => 'Benín',
            ],
            [
                'iso' => 'tg',
                'name' => 'Togo',
            ],
            [
                'iso' => 'so',
                'name' => 'Somalia',
            ],
            [
                'iso' => 'dj',
                'name' => 'Yibuti',
            ],
            [
                'iso' => 'er',
                'name' => 'Eritrea',
            ],
            [
                'iso' => 'ss',
                'name' => 'Sudán del Sur',
            ],
            [
                'iso' => 'sd',
                'name' => 'Sudán',
            ],
            [
                'iso' => 'ly',
                'name' => 'Libia',
            ],
            // Others
            [
                'iso' => 'us',
                'name' => 'Estados Unidos',
            ],
            [
                'iso' => 'ca',
                'name' => 'Canadá',
            ],
            [
                'iso' => 'au',
                'name' => 'Australia',
            ],
            [
                'iso' => 'nz',
                'name' => 'Nueva Zelanda',
            ],
            [
                'iso' => 'il',
                'name' => 'Israel',
            ],
            [
                'iso' => 'ru',
                'name' => 'Rusia',
            ],
            [
                'iso' => 'ua',
                'name' => 'Ucrania',
            ],
            [
                'iso' => 'by',
                'name' => 'Bielorrusia',
            ],
            [
                'iso' => 'md',
                'name' => 'Moldavia',
            ],
            [
                'iso' => 'ge',
                'name' => 'Georgia',
            ],
            [
                'iso' => 'am',
                'name' => 'Armenia',
            ],
            [
                'iso' => 'az',
                'name' => 'Azerbaiyán',
            ],
            [
                'iso' => 'fj',
                'name' => 'Fiyi',
            ],
            [
                'iso' => 'pg',
                'name' => 'Papúa Nueva Guinea',
            ],
            [
                'iso' => 'sb',
                'name' => 'Islas Salomón',
            ],
            [
                'iso' => 'vu',
                'name' => 'Vanuatu',
            ],
            [
                'iso' => 'ws',
                'name' => 'Samoa',
            ],
            [
                'iso' => 'to',
                'name' => 'Tonga',
            ],
            [
                'iso' => 'ki',
                'name' => 'Kiribati',
            ],
            [
                'iso' => 'mh',
                'name' => 'Islas Marshall',
            ],
            [
                'iso' => 'fm',
                'name' => 'Micronesia',
            ],
            [
                'iso' => 'pw',
                'name' => 'Palaos',
            ],
            [
                'iso' => 'nr',
                'name' => 'Nauru',
            ],
            [
                'iso' => 'tv',
                'name' => 'Tuvalu',
            ],
        ];

        foreach ($countries as $i => $country) {
            $country['order'] = $i;
            Country::create($country);
        }
    }
}
