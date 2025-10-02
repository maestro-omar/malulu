<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Catalogs\Diagnosis;

class DiagnosesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $diagnoses = [
            // Trastornos del aprendizaje
            ['code' => null, 'name' => 'Dislexia', 'category' => Diagnosis::CATEGORY_LEARN],
            ['code' => null, 'name' => 'TDAH', 'category' => Diagnosis::CATEGORY_LEARN],

            // Discapacidades
            ['code' => null, 'name' => 'Discapacidad motriz', 'category' => Diagnosis::CATEGORY_DISABILITY_MOTOR],
            ['code' => null, 'name' => 'Sordera', 'category' => Diagnosis::CATEGORY_DISABILITY_AUDITIVE],
            ['code' => null, 'name' => 'Hipoacusia', 'category' => Diagnosis::CATEGORY_DISABILITY_AUDITIVE],
            ['code' => null, 'name' => 'Ceguera', 'category' => Diagnosis::CATEGORY_DISABILITY_VISUAL],
            ['code' => null, 'name' => 'Disminución visual', 'category' => Diagnosis::CATEGORY_DISABILITY_VISUAL],
            ['code' => null, 'name' => 'Discapacidad intelectual', 'category' => Diagnosis::CATEGORY_DISABILITY_INTELLECTUAL],
            ['code' => null, 'name' => 'Discapacidad psicosocial', 'category' => Diagnosis::CATEGORY_DISABILITY_PSYCHOSOCIAL],
            ['code' => null, 'name' => 'Trastorno del espectro autista (TEA)', 'category' => Diagnosis::CATEGORY_DISABILITY_DEVELOPMENT],

            // Condiciones crónicas
            ['code' => null, 'name' => 'Diabetes tipo 1', 'category' => Diagnosis::CATEGORY_CHRONIC],
            ['code' => null, 'name' => 'Diabetes tipo 2', 'category' => Diagnosis::CATEGORY_CHRONIC],
            ['code' => null, 'name' => 'Epilepsia', 'category' => Diagnosis::CATEGORY_CHRONIC],
            ['code' => null, 'name' => 'Asma', 'category' => Diagnosis::CATEGORY_CHRONIC],
            ['code' => null, 'name' => 'Celiaquía', 'category' => Diagnosis::CATEGORY_CHRONIC],

            // Alergias
            ['code' => null, 'name' => 'Alergia', 'category' => Diagnosis::CATEGORY_ALERGY],

            // Síndromes frecuentes en contexto escolar
            ['code' => null, 'name' => 'Síndrome de Down', 'category' => Diagnosis::CATEGORY_SYNDROME],
            ['code' => null, 'name' => 'Síndrome de X Frágil', 'category' => Diagnosis::CATEGORY_SYNDROME],
        ];
        foreach ($diagnoses as  $diagnosis) {
            Diagnosis::create($diagnosis);
        }
    }
}
