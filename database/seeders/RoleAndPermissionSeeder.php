<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\Entities\User;
use App\Models\Entities\School;
use App\Models\Catalogs\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Get global school ID
        $globalSchoolId = School::specialGlobalId();
        if (!$globalSchoolId) {
            throw new \Exception('Global school not found. Please run SchoolSeeder first.');
        }

        // Set the team ID globally
        app(PermissionRegistrar::class)->setPermissionsTeamId($globalSchoolId);

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();


        $allPermissions = self::getPermissionsByRole('MAGIC_ALL');

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web' // Default guard for permissions
            ]);
        }

        // Create roles and assign permissions
        $roles = [
            Role::SUPERADMIN => [
                'name' => 'Super administrador',
                'code' => Role::SUPERADMIN,
                'description' => 'Administrador general del sistema',
                'short' => 'Super',
            ],
            Role::CONFIGURATOR => [
                'name' => 'Configurador',
                'code' => Role::CONFIGURATOR,
                'description' => 'Administrador parcial del sistema',
                'short' => 'Config',
            ],
            Role::SCHOOL_ADMIN => [
                'name' => 'Administrador',
                'code' => Role::SCHOOL_ADMIN,
                'description' => 'Administrador de la escuela',
                'short' => 'Admin',
            ],
            Role::DIRECTOR => [
                'name' => 'Director',
                'code' => Role::DIRECTOR,
                'description' => 'Director de la institución',
                'short' => 'Dir',
            ],
            Role::REGENT => [
                'name' => 'Regente',
                'code' => Role::REGENT,
                'description' => 'Regente de la institución',
                'short' => 'Reg',
            ],
            Role::SECRETARY => [
                'name' => 'Secretario',
                'code' => Role::SECRETARY,
                'description' => 'Secretario de la institución',
                'short' => 'Sec',
            ],
            Role::PROFESSOR => [
                'name' => 'Profesor',
                'code' => Role::PROFESSOR,
                'description' => 'Profesor de la institución',
                'short' => 'Prof',
            ],
            Role::CLASS_ASSISTANT => [
                'name' => 'Preceptor',
                'code' => Role::CLASS_ASSISTANT,
                'description' => 'Preceptor de nivel secundario',
                'short' => 'Prec',
            ],
            Role::GRADE_TEACHER => [
                'name' => 'Maestra/o de Grado',
                'code' => Role::GRADE_TEACHER,
                'description' => 'Maestra/o de grado',
                'short' => 'Maes',
            ],
            Role::ASSISTANT_TEACHER => [
                'name' => 'Maestra/o Auxiliar',
                'code' => Role::ASSISTANT_TEACHER,
                'description' => 'Maestra/o auxiliar',
                'short' => 'Auxi',
            ],
            Role::CURRICULAR_TEACHER => [
                'name' => 'Profesor Curricular',
                'code' => Role::CURRICULAR_TEACHER,
                'description' => 'Profesor de materias curriculares',
                'short' => 'Curr',
            ],
            Role::SPECIAL_TEACHER => [
                'name' => 'Profesor/a Especial',
                'code' => Role::SPECIAL_TEACHER,
                'description' => 'Profesor/a de educación especial',
                'short' => 'Esp',
            ],
            Role::LIBRARIAN => [
                'name' => 'Bibliotecario/a',
                'code' => Role::LIBRARIAN,
                'description' => 'Responsable de la biblioteca',
                'short' => 'Biblio',
            ],
            Role::GUARDIAN => [
                'name' => 'Responsable',
                'code' => Role::GUARDIAN,
                'description' => 'Adulto responsable legal del estudiante',
                'short' => 'Tut',
            ],
            Role::STUDENT => [
                'name' => 'Estudiante',
                'code' => Role::STUDENT,
                'description' => 'Estudiante/alumno/a de la institución',
                'short' => 'Est',
            ],
            Role::COOPERATIVE => [
                'name' => 'Cooperadora',
                'code' => Role::COOPERATIVE,
                'description' => 'Miembro de la cooperadora',
                'short' => 'Coop',
            ],
            Role::FORMER_STUDENT => [
                'name' => 'Ex-Estudiante',
                'code' => Role::FORMER_STUDENT,
                'description' => 'Ex-estudiante de la institución',
                'short' => 'Exalum',
            ],
        ];

        foreach ($roles as $roleCode => $roleData) {
            $roleData['permissions'] = self::getPermissionsByRole($roleCode);
            $role = Role::firstOrCreate([
                'name' => $roleData['name'],
                'code' => $roleData['code'],
                'guard_name' => 'web', // All roles use web guard
                // ROLES are the same for every team/school
            ], [
                'description' => $roleData['description'],
                'short' => $roleData['short']
            ]);

            // Get permissions by name and guard
            $rolePermissions = Permission::whereIn('name', $roleData['permissions'])
                ->where('guard_name', 'web')
                ->get();
            $role->syncPermissions($rolePermissions);

            if ($roleCode === Role::SUPERADMIN) {
                $superadminRole = $role;
            }
        }

        // Assign admin role to the first user (if exists)
        $user = User::first();
        if ($user && $superadminRole) {
            $user->assignRoleForSchool($superadminRole, $globalSchoolId);
        }
    }

    private function getPermissionsByRole(string $roleCode): array
    {
        /*
        -¿ver listado y datos de cualquier escuela? ¿es algo público general, sin acceso?

        ex-estudiante:
        -ver datos de escuelas en las que estuvo
        -ver propios datos
        -cambiar propios teléfono/email?
        -ver propios historial académico
        -ver propios mensajes
        -¿enviar mensajes a autoridades? (para pedir certificados??)

        estudiante (¿cambia por nivel o por edad?  ¿puede gestionar permisos sus tutores, por ejemplo, para cambiar o no su email, su foto? ¿o  autoridades/admin/docente  pueden habilitar ciertos permisos?)
        -ver datos de escuelas en las que está (¿incluye documentos... escuela+nivel, escuela, distrito, ministerio... que sean "públicos")
        -ver propios datos
        -editar propios algunos datos???
        -ver propios historial académico
        -ver propios mensajes
        -enviar mensajes (¿a quiénes?)
        -(gestión libros prestados a futuro)

        tutores
        -ver datos de escuelas en las que está hijes (incluye documentos... escuela+nivel, escuela, distrito, ministerio... que sean "públicos")
        -ver propios datos
        -editar propios algunos datos???
        -editar hijes algunos datos
        -subir hijes documentacion
        -ver hijes historial académico
        -ver propios mensajes
        -enviar mensajes (¿a quiénes?)
        -ver hijes info cursos ¿ver listado compas? ¿info tutores compas? ¿info docentes?
        -(gestión libros prestados hijes a futuro)
        
        coope
        -ver datos de escuelas en las que está hijes
        -ver propios datos
        -editar propios algunos datos???
        -ver info coope
        -publicar info coope

        bibliotecarie
        -ver datos de escuelas en las que trabaja
        -ver propios datos (personales y de trabajo)
        -editar propios datos (personales y de trabajo)
        -ver docu adminis/pedago de instituciòn
        -subir documentos escuela?
        -(gestión libros a futuro)

        precept
        -ver datos de escuelas en las que trabaja
        -ver propios datos (personales y de trabajo)
        -editar propios datos (personales y de trabajo)
        -ver docu adminis/pedago de instituciòn
        -subir documentos escuela? (limitado nivel)
        -ver editar crear alumnes? (limitado nivel, pero cualquier curso para ser flexibles con realidad escolar)
        -asistencia? (limitado nivel, pero cualquier curso para ser flexibles con realidad escolar)
        -cargar notas? (limitado nivel, pero cualquier curso para ser flexibles con realidad escolar)
        -subir docu alumnes? (limitado nivel, pero cualquier curso para ser flexibles con realidad escolar)
        -gestion cursos: promover curso completo, por alumno, editar status alumno (bajas, cambio de grupo...)
        -reportes (asistencia, sintesis por alumno....)
        
        profes ¿hay diferencia entre la variedad de cargos)
        -idem precept (sin limitaciones para habilitar flexibilidad)
        -¿se justifica limitar por ejempo a Especiales NO cargan asistencia ni NOTAS y curriculares solo notas propias y de grado NO notas curriculares? creo mejor NO limitar por ahora para facilitar situaciones excepcionales, flexibilidad

        autoridades (dire, rege, secre)
        -idem profes++
        -admin de profes
        -editar propia escuela
        -escalas calificaciones
        -modelos de libretas
        -CRUD docus escuela y superiores
        -importacion "masiva" de datos de alumnos

        admin
        -idem autoridades
        +crear admin

        superadmin
        -todo lo anterior++
        -CRUDs catalogos todos (permisos, motivos baja, niveles, turnos, paises, provincias, calendario escolar...)
        -cruds escuelas
        -
        */

        // Define permission groups (sorted from most powerful to least)
        $superAdminPermissions = [
            'superadmin',
            'user.manage',
            'role.manage',
            'permission.manage',
        ];

        $configPermissions = [
            'academic-year.manage',
            'file-type.manage',
            'file-subtype.manage',
            'country.manage',
            'district.manage',
            'province.manage',
            'locality.manage',
            'jobstatus.manage',
            'rolerelationshipendreason.manage',
            'schoollevel.manage',
            'schoolmanagementtype.manage',
            'schoolshift.manage',
            'studentcourseendreason.manage',
        ];


        $adminPermissions = [
            'admin.create',
        ];

        $schoolAdminPermissions = [
            'school.create',
            'school.delete',
        ];

        $schoolEditPermissions = [
            'school.edit',
            'school.files.manage',
        ];

        $academicAdminPermissions = [
            'subject.manage',
            'grading-scale.manage',
            'report-template.manage',
            'student.bulk-import',
            'school.files.view',
        ];

        $teacherPermissions = [
            'teacher.manage',
            'partner.view',
            'partner.view',
        ];

        $studentsGuardiansPermissions = [
            'guardian.view',
            'guardian.create',
            'guardian.edit',
            'guardian.delete',
        ];

        $studentPermissions = [
            'student.view',
            'student.create',
            'student.edit',
            'student.delete',
        ];

        $academicPermissions = [
            'attendance.view',
            'attendance.manage',
            'grade.view',
            'grade.manage',
            'course.manage',
            'course.files.manage',
            'report.generate',
        ];

        $libraryPermissions = [
            'library.book.create',
            'library.book.edit',
            'library.book.delete',
            'library.borrow.manage',
            'library.return.manage',
            'library.catalog.manage',
        ];

        $documentPermissions = [
            'document.view',
            'document.upload',
        ];

        $cooperativePermissions = [
            'cooperative.view',
            'cooperative.publish',
        ];

        $childPermissions = [
            'child.edit',
            'child.document.upload',
            'child.academic-history.view',
            'child.course.view',
        ];

        $libraryBasicPermissions = [
            'library.book.view',
            'library.borrow.create',
            'library.return.create',
        ];

        $academicHistoryPermissions = [
            'academic-history.view',
            'course.files.view',
        ];

        $roleStudentPermissions = [
            'student.me',
        ];

        $universalPermissions = [
            'profile.view',
            'profile.edit',
            'school.view',
            'message.view',
            'message.send',
        ];

        switch ($roleCode):
            case Role::SUPERADMIN:
            case 'MAGIC_ALL':
                return array_merge(
                    $superAdminPermissions,
                    $configPermissions,
                    $adminPermissions,
                    $schoolAdminPermissions,
                    $schoolEditPermissions,
                    $academicAdminPermissions,
                    $teacherPermissions,
                    $studentsGuardiansPermissions,
                    $studentPermissions,
                    $academicPermissions,
                    $libraryPermissions,
                    $documentPermissions,
                    $cooperativePermissions,
                    $childPermissions,
                    $libraryBasicPermissions,
                    $academicHistoryPermissions,
                    $universalPermissions
                );
            case Role::CONFIGURATOR:
                return array_merge(
                    $configPermissions,
                    $universalPermissions
                );
            case Role::SCHOOL_ADMIN:
                return array_merge(
                    $adminPermissions,
                    $schoolEditPermissions,
                    $academicAdminPermissions,
                    $teacherPermissions,
                    $studentsGuardiansPermissions,
                    $studentPermissions,
                    $academicPermissions,
                    $libraryPermissions,
                    $documentPermissions,
                    $cooperativePermissions,
                    $childPermissions,
                    $libraryBasicPermissions,
                    $academicHistoryPermissions,
                    $universalPermissions
                );
            case Role::DIRECTOR:
            case Role::REGENT:
            case Role::SECRETARY:
                return array_merge(
                    $schoolEditPermissions,
                    $academicAdminPermissions,
                    $teacherPermissions,
                    $studentsGuardiansPermissions,
                    $studentPermissions,
                    $academicPermissions,
                    $libraryPermissions,
                    $documentPermissions,
                    $cooperativePermissions,
                    $childPermissions,
                    $libraryBasicPermissions,
                    $academicHistoryPermissions,
                    $universalPermissions
                );
            case Role::LIBRARIAN:
                return array_merge(
                    $libraryPermissions,
                    $documentPermissions,
                    $academicHistoryPermissions,
                    $universalPermissions
                );
            case Role::PROFESSOR:
            case Role::CLASS_ASSISTANT:
            case Role::GRADE_TEACHER:
            case Role::ASSISTANT_TEACHER:
            case Role::CURRICULAR_TEACHER:
            case Role::SPECIAL_TEACHER:
                return array_merge(
                    $studentsGuardiansPermissions,
                    $studentPermissions,
                    $academicPermissions,
                    $documentPermissions,
                    $libraryBasicPermissions,
                    $academicHistoryPermissions,
                    $universalPermissions
                );
            case Role::GUARDIAN:
                return array_merge(
                    $childPermissions,
                    $libraryBasicPermissions,
                    $academicHistoryPermissions,
                    $universalPermissions
                );
            case Role::COOPERATIVE:
                return array_merge(
                    $cooperativePermissions,
                    $universalPermissions
                );
            case Role::STUDENT:
                return array_merge(
                    $roleStudentPermissions,
                    $libraryBasicPermissions,
                    $academicHistoryPermissions,
                    $universalPermissions
                );
            case Role::FORMER_STUDENT:
                return array_merge(
                    $academicHistoryPermissions,
                    ['profile.view']
                );
            case Role::MAINTENANCE:
                return array_merge(
                    $universalPermissions
                );
            case Role::EXTERNAL:
                return array_merge(
                    $universalPermissions
                );
            default:
                return [];
        endswitch;
    }
}
