<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Catalogs\RoleRelationshipEndReason;
use App\Models\Catalogs\Role;

class RoleRelationshipEndReasonSeeder extends Seeder
{
    public function run()
    {
        $reasons = [
            // Academic reasons
            [
                'code' => RoleRelationshipEndReason::CODE_GRADUATED,
                'name' => 'Graduado',
                'description' => 'El estudiante ha completado sus estudios y se ha graduado',
                'applicable_roles' => [Role::STUDENT],
                'is_active' => true
            ],
            [
                'code' => RoleRelationshipEndReason::CODE_TRANSFERRED,
                'name' => 'Transferido',
                'description' => 'El estudiante se ha transferido a otra institución',
                'applicable_roles' => [Role::STUDENT],
                'is_active' => true
            ],
            [
                'code' => RoleRelationshipEndReason::CODE_WITHDRAWN,
                'name' => 'Retirado',
                'description' => 'El estudiante se ha retirado voluntariamente',
                'applicable_roles' => [Role::STUDENT],
                'is_active' => true
            ],

            // Employment reasons
            [
                'code' => RoleRelationshipEndReason::CODE_RESIGNED,
                'name' => 'Renuncia',
                'description' => 'El empleado ha presentado su renuncia',
                'applicable_roles' => [
                    Role::PROFESSOR,
                    Role::GRADE_TEACHER,
                    Role::ASSISTANT_TEACHER,
                    Role::CURRICULAR_TEACHER,
                    Role::SPECIAL_TEACHER,
                    Role::CLASS_ASSISTANT,
                    Role::LIBRARIAN,
                    Role::DIRECTOR,
                    Role::REGENT,
                    Role::SECRETARY
                ],
                'is_active' => true
            ],
            [
                'code' => RoleRelationshipEndReason::CODE_CONTRACT_ENDED,
                'name' => 'Contrato Finalizado',
                'description' => 'El contrato del empleado ha llegado a su fin',
                'applicable_roles' => [
                    Role::PROFESSOR,
                    Role::GRADE_TEACHER,
                    Role::ASSISTANT_TEACHER,
                    Role::CURRICULAR_TEACHER,
                    Role::SPECIAL_TEACHER,
                    Role::CLASS_ASSISTANT,
                    Role::LIBRARIAN,
                    Role::DIRECTOR,
                    Role::REGENT,
                    Role::SECRETARY
                ],
                'is_active' => true
            ],
            [
                'code' => RoleRelationshipEndReason::CODE_TERMINATED,
                'name' => 'Terminado',
                'description' => 'El empleado ha sido terminado por la institución',
                'applicable_roles' => [
                    Role::PROFESSOR,
                    Role::GRADE_TEACHER,
                    Role::ASSISTANT_TEACHER,
                    Role::CURRICULAR_TEACHER,
                    Role::SPECIAL_TEACHER,
                    Role::CLASS_ASSISTANT,
                    Role::LIBRARIAN,
                    Role::DIRECTOR,
                    Role::REGENT,
                    Role::SECRETARY
                ],
                'is_active' => true
            ],

            // Guardian reasons
            [
                'code' => RoleRelationshipEndReason::CODE_STUDENT_GRADUATED,
                'name' => 'Estudiante Graduado',
                'description' => 'El estudiante se ha graduado',
                'applicable_roles' => [Role::GUARDIAN],
                'is_active' => true
            ],
            [
                'code' => RoleRelationshipEndReason::CODE_STUDENT_TRANSFERRED,
                'name' => 'Estudiante Transferido',
                'description' => 'El estudiante se ha transferido a otra institución',
                'applicable_roles' => [Role::GUARDIAN],
                'is_active' => true
            ],
            [
                'code' => RoleRelationshipEndReason::CODE_STUDENT_WITHDRAWN,
                'name' => 'Estudiante Retirado',
                'description' => 'El estudiante se ha retirado de la institución',
                'applicable_roles' => [Role::GUARDIAN],
                'is_active' => true
            ],

            // Cooperative reasons
            [
                'code' => RoleRelationshipEndReason::CODE_MEMBERSHIP_ENDED,
                'name' => 'Membresía Finalizada',
                'description' => 'La membresía ha llegado a su fin',
                'applicable_roles' => [Role::COOPERATIVE],
                'is_active' => true
            ],
            [
                'code' => RoleRelationshipEndReason::CODE_VOLUNTARY_WITHDRAWAL,
                'name' => 'Retiro Voluntario',
                'description' => 'El miembro se ha retirado voluntariamente',
                'applicable_roles' => [Role::COOPERATIVE],
                'is_active' => true
            ],
            [
                'code' => RoleRelationshipEndReason::CODE_EXPELLED,
                'name' => 'Expulsado',
                'description' => 'El miembro ha sido expulsado de la cooperativa',
                'applicable_roles' => [Role::COOPERATIVE],
                'is_active' => true
            ],

            // Common reasons
            [
                'code' => RoleRelationshipEndReason::CODE_DECEASED,
                'name' => 'Fallecido',
                'description' => 'La persona ha fallecido',
                'applicable_roles' => [
                    Role::STUDENT,
                    Role::PROFESSOR,
                    Role::GRADE_TEACHER,
                    Role::ASSISTANT_TEACHER,
                    Role::CURRICULAR_TEACHER,
                    Role::SPECIAL_TEACHER,
                    Role::CLASS_ASSISTANT,
                    Role::LIBRARIAN,
                    Role::DIRECTOR,
                    Role::REGENT,
                    Role::SECRETARY,
                    Role::GUARDIAN,
                    Role::COOPERATIVE
                ],
                'is_active' => true
            ],
            [
                'code' => RoleRelationshipEndReason::CODE_OTHER,
                'name' => 'Otro',
                'description' => 'Otra razón no especificada',
                'applicable_roles' => [
                    Role::STUDENT,
                    Role::PROFESSOR,
                    Role::GRADE_TEACHER,
                    Role::ASSISTANT_TEACHER,
                    Role::CURRICULAR_TEACHER,
                    Role::SPECIAL_TEACHER,
                    Role::CLASS_ASSISTANT,
                    Role::LIBRARIAN,
                    Role::DIRECTOR,
                    Role::REGENT,
                    Role::SECRETARY,
                    Role::GUARDIAN,
                    Role::COOPERATIVE
                ],
                'is_active' => true
            ],
        ];

        foreach ($reasons as $reason) {
            RoleRelationshipEndReason::create($reason);
        }
    }
}
