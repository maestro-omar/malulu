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
        // Create permissions with default guard
        $allPermissions = [
            // Superadmin permission
            'superadmin',
            'admin',

            // User permissions
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view own profile',
            'edit own profile',
            'view other profiles',
            'edit other profiles',

            // Role permissions
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',

            // School permissions
            'view schools',
            'create schools',
            'edit schools',
            'delete schools',
            'view assigned schools',
            'edit assigned schools',

            // Academic permissions
            'view academic',
            'create academic',
            'edit academic',
            'delete academic',
            'view assigned academic',
            'edit assigned academic',

            // Student specific permissions
            'view students',
            'create students',
            'edit students',
            'delete students',
            'view own student data',
            'edit own student data',
            'view children data',
            'edit children data',

            // Guardian specific permissions
            'view guardians',
            'create guardians',
            'edit guardians',
            'delete guardians',
            'view own guardian data',
            'edit own guardian data',

            // Teacher specific permissions
            'view teachers',
            'create teachers',
            'edit teachers',
            'delete teachers',
            'view own teacher data',
            'edit own teacher data',

            // Cooperative specific permissions
            'view cooperative',
            'create cooperative',
            'edit cooperative',
            'delete cooperative',
            'view own cooperative data',
            'edit own cooperative data',

            // Library specific permissions
            'view library',
            'create library',
            'edit library',
            'delete library',
            'view own library data',
            'edit own library data',

            // Historical data permissions
            'view historical data',
            'create historical data',
            'edit historical data',
            'delete historical data',
            'view own historical data',
            'edit own historical data',
        ];

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web' // Default guard for permissions
            ]);
        }

        $authorityPermissions = array_diff($allPermissions, ['superadmin', 'admin']);
        $teacherPermissions = [
            'view own profile',
            'edit own profile',
            'view other profiles',
            'view assigned schools',
            'view academic',
            'view assigned academic',
            'edit assigned academic',
            'view students',
            'edit students',
            'view guardians',
            'edit guardians',
            'view own teacher data',
            'edit own teacher data',
            'view library',
        ];
        $librarianPermissions = [
            'view own profile',
            'edit own profile',
            'view assigned schools',
            'view students',
            'view own library data',
            'edit own library data',
            'view library',
            'create library',
            'edit library',
            'delete library',
        ];
        $coopPermissions = [
            'view own profile',
            'edit own profile',
            'view own cooperative data',
            'edit own cooperative data',
        ];
        $guardianPermissions = [
            'view own profile',
            'edit own profile',
            'view own guardian data',
            'edit own guardian data',
            'view children data',
            'edit children data',
        ];
        $studentPermissions = [
            'view own profile',
            'edit own profile',
            'view own student data',
            'edit own student data',
        ];

        $formerStudentPermissions = [
            'view own profile',
            'edit own profile',
            'view own historical data',
            'edit own historical data',
        ];
        // Create roles and assign permissions
        $roles = [
            Role::SUPERADMIN => [
                'name' => 'Super administrador',
                'code' => Role::SUPERADMIN,
                'description' => 'Administrador general del sistema',
                'short' => 'Super',
                'permissions' => $allPermissions
            ],
            Role::ADMIN => [
                'name' => 'Administrador',
                'code' => Role::ADMIN,
                'description' => 'Administrador de la escuela',
                'short' => 'Admin',
                'permissions' => array_diff($allPermissions, ['superadmin'])
            ],
            Role::DIRECTOR => [
                'name' => 'Director',
                'code' => Role::DIRECTOR,
                'description' => 'Director de la institución',
                'short' => 'Dir',
                'permissions' => $authorityPermissions
            ],
            Role::REGENT => [
                'name' => 'Regente',
                'code' => Role::REGENT,
                'description' => 'Regente de la institución',
                'short' => 'Reg',
                'permissions' => $authorityPermissions
            ],
            Role::SECRETARY => [
                'name' => 'Secretario',
                'code' => Role::SECRETARY,
                'description' => 'Secretario de la institución',
                'short' => 'Sec',
                'permissions' => $authorityPermissions
            ],
            Role::PROFESSOR => [
                'name' => 'Profesor',
                'code' => Role::PROFESSOR,
                'description' => 'Profesor de la institución',
                'short' => 'Prof',
                'permissions' => $teacherPermissions,
            ],
            Role::CLASS_ASSISTANT => [
                'name' => 'Preceptor',
                'code' => Role::CLASS_ASSISTANT,
                'description' => 'Preceptor de nivel secundario',
                'short' => 'Prec',
                'permissions' => $teacherPermissions,
            ],
            Role::GRADE_TEACHER => [
                'name' => 'Maestra/o de Grado',
                'code' => Role::GRADE_TEACHER,
                'description' => 'Maestra/o de grado',
                'short' => 'Maes',
                'permissions' => $teacherPermissions,
            ],
            Role::ASSISTANT_TEACHER => [
                'name' => 'Maestra/o Auxiliar',
                'code' => Role::ASSISTANT_TEACHER,
                'description' => 'Maestra/o auxiliar',
                'short' => 'Auxi',
                'permissions' => $teacherPermissions,
            ],
            Role::CURRICULAR_TEACHER => [
                'name' => 'Profesor Curricular',
                'code' => Role::CURRICULAR_TEACHER,
                'description' => 'Profesor de materias curriculares',
                'short' => 'Curr',
                'permissions' => $teacherPermissions,
            ],
            Role::SPECIAL_TEACHER => [
                'name' => 'Profesor/a Especial',
                'code' => Role::SPECIAL_TEACHER,
                'description' => 'Profesor/a de educación especial',
                'short' => 'Esp',
                'permissions' => $teacherPermissions,
            ],
            Role::LIBRARIAN => [
                'name' => 'Bibliotecario/a',
                'code' => Role::LIBRARIAN,
                'description' => 'Responsable de la biblioteca',
                'short' => 'Biblio',
                'permissions' => $librarianPermissions
            ],
            Role::GUARDIAN => [
                'name' => 'Responsable',
                'code' => Role::GUARDIAN,
                'description' => 'Adulto responsable legal del estudiante',
                'short' => 'Tut',
                'permissions' => $guardianPermissions
            ],
            Role::STUDENT => [
                'name' => 'Estudiante',
                'code' => Role::STUDENT,
                'description' => 'Estudiante/alumno/a de la institución',
                'short' => 'Est',
                'permissions' => $studentPermissions
            ],
            Role::COOPERATIVE => [
                'name' => 'Cooperadora',
                'code' => Role::COOPERATIVE,
                'description' => 'Miembro de la cooperadora',
                'short' => 'Coop',
                'permissions' => $coopPermissions
            ],
            Role::FORMER_STUDENT => [
                'name' => 'Ex-Estudiante',
                'code' => Role::FORMER_STUDENT,
                'description' => 'Ex-estudiante de la institución',
                'short' => 'Exalum',
                'permissions' => $formerStudentPermissions
            ],
        ];

        $superadminRole = null;
        foreach ($roles as $roleCode => $roleData) {
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
}
