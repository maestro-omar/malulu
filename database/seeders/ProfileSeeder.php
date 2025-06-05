<?php

namespace Database\Seeders;

use App\Models\Profile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profiles = [
            [
                'key' => Profile::ADMIN,
                'name' => 'Administrador',
                'description' => 'Administrador del sistema con acceso total',
                'short' => 'Admin',
            ],
            [
                'key' => Profile::DIRECTOR,
                'name' => 'Director',
                'description' => 'Director de la institución',
                'short' => 'Dir',
            ],
            [
                'key' => Profile::REGENT,
                'name' => 'Regente',
                'description' => 'Regente de la institución',
                'short' => 'Reg',
            ],
            [
                'key' => Profile::SECRETARY,
                'name' => 'Secretario',
                'description' => 'Secretario de la institución',
                'short' => 'Sec',
            ],
            [
                'key' => Profile::PROFESSOR,
                'name' => 'Profesor',
                'description' => 'Profesor de la institución',
                'short' => 'Prof',
            ],
            [
                'key' => Profile::CLASS_ASSISTANT,
                'name' => 'Preceptor',
                'description' => 'Preceptor de nivel secundario',
                'short' => 'Prec',
            ],
            [
                'key' => Profile::GRADE_TEACHER,
                'name' => 'Maestra/o de Grado',
                'description' => 'Maestra/o de grado',
                'short' => 'Maes',
            ],
            [
                'key' => Profile::ASSISTANT_TEACHER,
                'name' => 'Maestra/o Auxiliar',
                'description' => 'Maestra/o auxiliar',
                'short' => 'Auxi',
            ],
            [
                'key' => Profile::CURRICULAR_TEACHER,
                'name' => 'Profesor Curricular',
                'description' => 'Profesor de materias curriculares',
                'short' => 'Curr',
            ],
            [
                'key' => Profile::SPECIAL_TEACHER,
                'name' => 'Profesor/a Especial',
                'description' => 'Profesor/a de educación especial',
                'short' => 'Esp',
            ],
            [
                'key' => Profile::LIBRARIAN,
                'name' => 'Bibliotecario/a',
                'description' => 'Responsable de la biblioteca',
                'short' => 'Biblio',
            ],
            [
                'key' => Profile::GUARDIAN,
                'name' => 'Responsable',
                'description' => 'Adulto responsable legal del estudiante',
                'short' => 'Tut',
            ],
            [
                'key' => Profile::STUDENT,
                'name' => 'Estudiante',
                'description' => 'Estudiante/alumno/a de la institución',
                'short' => 'Est',
            ],
            [
                'key' => Profile::COOPERATIVE,
                'name' => 'Cooperativa',
                'description' => 'Miembro de la cooperativa',
                'short' => 'Coop',
            ],
            [
                'key' => Profile::FORMER_STUDENT,
                'name' => 'Ex-Estudiante',
                'description' => 'Ex-estudiante de la institución',
                'short' => 'Exalum',
            ],
        ];

        foreach ($profiles as $profile) {
            Profile::create($profile);
        }
    }
}
