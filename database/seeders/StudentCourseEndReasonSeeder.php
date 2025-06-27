<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Catalogs\StudentCourseEndReason;

class StudentCourseEndReasonSeeder extends Seeder
{
    public function run()
    {
        $reasons = [
            [
                'code' => StudentCourseEndReason::CODE_TRANSFER_SAME_SHIFT,
                'name' => 'Cambio de curso en el mismo turno',
                'description' => 'El estudiante cambió de curso dentro del mismo turno.',
                'is_active' => true
            ],
            [
                'code' => StudentCourseEndReason::CODE_TRANSFER_OTHER_SHIFT,
                'name' => 'Cambio de curso a otro turno',
                'description' => 'El estudiante cambió de curso a un turno diferente.',
                'is_active' => true
            ],
            [
                'code' => StudentCourseEndReason::CODE_TRANSFER_OTHER_SCHOOL,
                'name' => 'Cambio de escuela',
                'description' => 'El estudiante se transfirió a otra escuela.',
                'is_active' => true
            ],
            [
                'code' => StudentCourseEndReason::CODE_PROMOTED,
                'name' => 'Promocionado',
                'description' => 'El estudiante fue promocionado al siguiente nivel.',
                'is_active' => true
            ],
            [
                'code' => StudentCourseEndReason::CODE_GRADUATED,
                'name' => 'Graduado',
                'description' => 'El estudiante completó el curso y se graduó.',
                'is_active' => true
            ],
            [
                'code' => StudentCourseEndReason::CODE_WITHDRAWN,
                'name' => 'Abandono',
                'description' => 'El estudiante abandonó el curso.',
                'is_active' => true
            ],
            [
                'code' => StudentCourseEndReason::CODE_DECEASED,
                'name' => 'Fallecido',
                'description' => 'El estudiante falleció.',
                'is_active' => true
            ],
            [
                'code' => StudentCourseEndReason::CODE_OTHER,
                'name' => 'Otro',
                'description' => 'Otra razón no especificada.',
                'is_active' => true
            ],
        ];

        foreach ($reasons as $reason) {
            StudentCourseEndReason::updateOrCreate(['code' => $reason['code']], $reason);
        }
    }
}
