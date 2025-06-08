<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;
use App\Models\School;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Get global school ID
        $globalSchool = School::where('key', 'GLOBAL')->first();
        if (!$globalSchool) {
            throw new \Exception('Global school not found. Please run SchoolSeeder first.');
        }

        // Set the team ID globally
        app(PermissionRegistrar::class)->setPermissionsTeamId($globalSchool->id);

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions with default guard
        $permissions = [
            // User permissions
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Role permissions
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            
            // Profile permissions
            'view profile',
            'edit profile',
            
            // School permissions
            'view schools',
            'create schools',
            'edit schools',
            'delete schools',
            
            // Academic permissions
            'view academic',
            'create academic',
            'edit academic',
            'delete academic',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web' // Default guard for permissions
            ]);
        }

        // Create roles and assign permissions
        $roles = [
            'admin' => [
                'name' => 'Administrador',
                'key' => 'admin',
                'description' => 'Administrador del sistema con acceso total',
                'short' => 'Admin',
                'permissions' => $permissions, // Admin has all permissions
            ],
            'director' => [
                'name' => 'Director',
                'key' => 'director',
                'description' => 'Director de la institución',
                'short' => 'Dir',
                'permissions' => [
                    'view users',
                    'view profile',
                    'edit profile',
                    'view schools',
                    'view academic',
                ],
            ],
            'regent' => [
                'name' => 'Regente',
                'key' => 'regent',
                'description' => 'Regente de la institución',
                'short' => 'Reg',
                'permissions' => [
                    'view profile',
                    'edit profile',
                    'view academic',
                ],
            ],
            'secretary' => [
                'name' => 'Secretario',
                'key' => 'secretary',
                'description' => 'Secretario de la institución',
                'short' => 'Sec',
                'permissions' => [
                    'view profile',
                    'edit profile',
                    'view academic',
                ],
            ],
            'professor' => [
                'name' => 'Profesor',
                'key' => 'professor',
                'description' => 'Profesor de la institución',
                'short' => 'Prof',
                'permissions' => [
                    'view profile',
                    'edit profile',
                    'view academic',
                ],
            ],
            'class_assistant' => [
                'name' => 'Preceptor',
                'key' => 'class_assistant',
                'description' => 'Preceptor de nivel secundario',
                'short' => 'Prec',
                'permissions' => [
                    'view profile',
                    'edit profile',
                    'view academic',
                ],
            ],
            'grade_teacher' => [
                'name' => 'Maestra/o de Grado',
                'key' => 'grade_teacher',
                'description' => 'Maestra/o de grado',
                'short' => 'Maes',
                'permissions' => [
                    'view profile',
                    'edit profile',
                    'view academic',
                ],
            ],
            'assistant_teacher' => [
                'name' => 'Maestra/o Auxiliar',
                'key' => 'assistant_teacher',
                'description' => 'Maestra/o auxiliar',
                'short' => 'Auxi',
                'permissions' => [
                    'view profile',
                    'edit profile',
                    'view academic',
                ],
            ],
            'curricular_teacher' => [
                'name' => 'Profesor Curricular',
                'key' => 'curricular_teacher',
                'description' => 'Profesor de materias curriculares',
                'short' => 'Curr',
                'permissions' => [
                    'view profile',
                    'edit profile',
                    'view academic',
                ],
            ],
            'special_teacher' => [
                'name' => 'Profesor/a Especial',
                'key' => 'special_teacher',
                'description' => 'Profesor/a de educación especial',
                'short' => 'Esp',
                'permissions' => [
                    'view profile',
                    'edit profile',
                    'view academic',
                ],
            ],
            'librarian' => [
                'name' => 'Bibliotecario/a',
                'key' => 'librarian',
                'description' => 'Responsable de la biblioteca',
                'short' => 'Biblio',
                'permissions' => [
                    'view profile',
                    'edit profile',
                ],
            ],
            'guardian' => [
                'name' => 'Responsable',
                'key' => 'guardian',
                'description' => 'Adulto responsable legal del estudiante',
                'short' => 'Tut',
                'permissions' => [
                    'view profile',
                    'edit profile',
                ],
            ],
            'student' => [
                'name' => 'Estudiante',
                'key' => 'student',
                'description' => 'Estudiante/alumno/a de la institución',
                'short' => 'Est',
                'permissions' => [
                    'view profile',
                    'edit profile',
                ],
            ],
            'cooperative' => [
                'name' => 'Cooperativa',
                'key' => 'cooperative',
                'description' => 'Miembro de la cooperativa',
                'short' => 'Coop',
                'permissions' => [
                    'view profile',
                    'edit profile',
                ],
            ],
            'former_student' => [
                'name' => 'Ex-Estudiante',
                'key' => 'former_student',
                'description' => 'Ex-estudiante de la institución',
                'short' => 'Exalum',
                'permissions' => [
                    'view profile',
                    'edit profile',
                ],
            ],
        ];

        $adminRole = null;
        foreach ($roles as $roleKey => $roleData) {
            $role = Role::firstOrCreate([
                'name' => $roleData['name'],
                'key' => $roleData['key'],
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
            
            if ($roleKey === 'admin') {
                $adminRole = $role;
            }
        }

        // Assign admin role to the first user (if exists)
        $user = User::first();
        if ($user && $adminRole) {
            $user->assignRole($adminRole);
        }
    }
} 