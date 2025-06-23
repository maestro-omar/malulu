<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;
use App\Models\School;
use App\Models\Role;

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

        // Create permissions with default guard
        $permissions = [
            // Superadmin permission
            'superadmin',

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

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web' // Default guard for permissions
            ]);
        }

        // Create roles and assign permissions
        $roles = [
            Role::ADMIN => [
                'name' => 'Administrador',
                'code' => Role::ADMIN,
                'description' => 'Administrador del sistema con acceso total',
                'short' => 'Admin',
                'permissions' => [
                    'superadmin',
                    'view users',
                    'create users',
                    'edit users',
                    'delete users',
                    'view own profile',
                    'edit own profile',
                    'view other profiles',
                    'edit other profiles',
                    'view roles',
                    'view schools',
                    'edit schools',
                    'view assigned schools',
                    'edit assigned schools',
                    'view academic',
                    'create academic',
                    'edit academic',
                    'delete academic',
                    'view assigned academic',
                    'edit assigned academic',
                    'view students',
                    'create students',
                    'edit students',
                    'delete students',
                    'view guardians',
                    'create guardians',
                    'edit guardians',
                    'delete guardians',
                    'view teachers',
                    'create teachers',
                    'edit teachers',
                    'delete teachers',
                    'view cooperative',
                    'create cooperative',
                    'edit cooperative',
                    'delete cooperative',
                    'view library',
                    'create library',
                    'edit library',
                    'delete library',
                    'view historical data',
                    'create historical data',
                    'edit historical data',
                    'delete historical data',
                ],
            ],
            Role::DIRECTOR => [
                'name' => 'Director',
                'code' => Role::DIRECTOR,
                'description' => 'Director de la institución',
                'short' => 'Dir',
                'permissions' => [
                    'view users',
                    'create users',
                    'edit users',
                    'delete users',
                    'view own profile',
                    'edit own profile',
                    'view other profiles',
                    'edit other profiles',
                    'view roles',
                    'view schools',
                    'edit schools',
                    'view assigned schools',
                    'edit assigned schools',
                    'view academic',
                    'create academic',
                    'edit academic',
                    'delete academic',
                    'view assigned academic',
                    'edit assigned academic',
                    'view students',
                    'create students',
                    'edit students',
                    'delete students',
                    'view guardians',
                    'create guardians',
                    'edit guardians',
                    'delete guardians',
                    'view teachers',
                    'create teachers',
                    'edit teachers',
                    'delete teachers',
                    'view cooperative',
                    'create cooperative',
                    'edit cooperative',
                    'delete cooperative',
                    'view library',
                    'create library',
                    'edit library',
                    'delete library',
                    'view historical data',
                    'create historical data',
                    'edit historical data',
                    'delete historical data',
                ],
            ],
            Role::REGENT => [
                'name' => 'Regente',
                'code' => Role::REGENT,
                'description' => 'Regente de la institución',
                'short' => 'Reg',
                'permissions' => [
                    'view users',
                    'create users',
                    'edit users',
                    'view own profile',
                    'edit own profile',
                    'view other profiles',
                    'edit other profiles',
                    'view roles',
                    'view schools',
                    'view assigned schools',
                    'edit assigned schools',
                    'view academic',
                    'create academic',
                    'edit academic',
                    'view assigned academic',
                    'edit assigned academic',
                    'view students',
                    'create students',
                    'edit students',
                    'view guardians',
                    'create guardians',
                    'edit guardians',
                    'view teachers',
                    'create teachers',
                    'edit teachers',
                    'view cooperative',
                    'view library',
                    'view historical data',
                ],
            ],
            Role::SECRETARY => [
                'name' => 'Secretario',
                'code' => Role::SECRETARY,
                'description' => 'Secretario de la institución',
                'short' => 'Sec',
                'permissions' => [
                    'view users',
                    'create users',
                    'edit users',
                    'view own profile',
                    'edit own profile',
                    'view other profiles',
                    'edit other profiles',
                    'view roles',
                    'view schools',
                    'view assigned schools',
                    'edit assigned schools',
                    'view academic',
                    'create academic',
                    'edit academic',
                    'view assigned academic',
                    'edit assigned academic',
                    'view students',
                    'create students',
                    'edit students',
                    'view guardians',
                    'create guardians',
                    'edit guardians',
                    'view teachers',
                    'create teachers',
                    'edit teachers',
                    'view cooperative',
                    'view library',
                    'view historical data',
                ],
            ],
            Role::PROFESSOR => [
                'name' => 'Profesor',
                'code' => Role::PROFESSOR,
                'description' => 'Profesor de la institución',
                'short' => 'Prof',
                'permissions' => [
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
                ],
            ],
            Role::CLASS_ASSISTANT => [
                'name' => 'Preceptor',
                'code' => Role::CLASS_ASSISTANT,
                'description' => 'Preceptor de nivel secundario',
                'short' => 'Prec',
                'permissions' => [
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
                ],
            ],
            Role::GRADE_TEACHER => [
                'name' => 'Maestra/o de Grado',
                'code' => Role::GRADE_TEACHER,
                'description' => 'Maestra/o de grado',
                'short' => 'Maes',
                'permissions' => [
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
                ],
            ],
            Role::ASSISTANT_TEACHER => [
                'name' => 'Maestra/o Auxiliar',
                'code' => Role::ASSISTANT_TEACHER,
                'description' => 'Maestra/o auxiliar',
                'short' => 'Auxi',
                'permissions' => [
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
                ],
            ],
            Role::CURRICULAR_TEACHER => [
                'name' => 'Profesor Curricular',
                'code' => Role::CURRICULAR_TEACHER,
                'description' => 'Profesor de materias curriculares',
                'short' => 'Curr',
                'permissions' => [
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
                ],
            ],
            Role::SPECIAL_TEACHER => [
                'name' => 'Profesor/a Especial',
                'code' => Role::SPECIAL_TEACHER,
                'description' => 'Profesor/a de educación especial',
                'short' => 'Esp',
                'permissions' => [
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
                ],
            ],
            Role::LIBRARIAN => [
                'name' => 'Bibliotecario/a',
                'code' => Role::LIBRARIAN,
                'description' => 'Responsable de la biblioteca',
                'short' => 'Biblio',
                'permissions' => [
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
                ],
            ],
            Role::GUARDIAN => [
                'name' => 'Responsable',
                'code' => Role::GUARDIAN,
                'description' => 'Adulto responsable legal del estudiante',
                'short' => 'Tut',
                'permissions' => [
                    'view own profile',
                    'edit own profile',
                    'view own guardian data',
                    'edit own guardian data',
                    'view children data',
                    'edit children data',
                ],
            ],
            Role::STUDENT => [
                'name' => 'Estudiante',
                'code' => Role::STUDENT,
                'description' => 'Estudiante/alumno/a de la institución',
                'short' => 'Est',
                'permissions' => [
                    'view own profile',
                    'edit own profile',
                    'view own student data',
                    'edit own student data',
                ],
            ],
            Role::COOPERATIVE => [
                'name' => 'Cooperadora',
                'code' => Role::COOPERATIVE,
                'description' => 'Miembro de la cooperadora',
                'short' => 'Coop',
                'permissions' => [
                    'view own profile',
                    'edit own profile',
                    'view own cooperative data',
                    'edit own cooperative data',
                ],
            ],
            Role::FORMER_STUDENT => [
                'name' => 'Ex-Estudiante',
                'code' => Role::FORMER_STUDENT,
                'description' => 'Ex-estudiante de la institución',
                'short' => 'Exalum',
                'permissions' => [
                    'view own profile',
                    'edit own profile',
                    'view own historical data',
                    'edit own historical data',
                ],
            ],
        ];

        $adminRole = null;
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

            if ($roleCode === 'admin') {
                $adminRole = $role;
            }
        }

        // Assign admin role to the first user (if exists)
        $user = User::first();
        if ($user && $adminRole) {
            $user->assignRoleForSchool($adminRole, $globalSchoolId);
        }
    }
}
