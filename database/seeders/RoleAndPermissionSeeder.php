<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
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

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
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
                'guard_name' => 'admin'
            ]);
        }

        // Create roles and assign permissions
        $roles = [
            'admin' => [
                'name' => 'Administrador',
                'description' => 'Administrador del sistema con acceso total',
                'short' => 'Admin',
                'guard' => 'admin',
                'permissions' => $permissions, // Admin has all permissions
            ],
            'director' => [
                'name' => 'Director',
                'description' => 'Director de la institución',
                'short' => 'Dir',
                'guard' => 'director',
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
                'description' => 'Regente de la institución',
                'short' => 'Reg',
                'guard' => 'regent',
                'permissions' => [
                    'view profile',
                    'edit profile',
                    'view academic',
                ],
            ],
            'secretary' => [
                'name' => 'Secretario',
                'description' => 'Secretario de la institución',
                'short' => 'Sec',
                'guard' => 'secretary',
                'permissions' => [
                    'view profile',
                    'edit profile',
                    'view academic',
                ],
            ],
            'professor' => [
                'name' => 'Profesor',
                'description' => 'Profesor de la institución',
                'short' => 'Prof',
                'guard' => 'professor',
                'permissions' => [
                    'view profile',
                    'edit profile',
                    'view academic',
                ],
            ],
            'class_assistant' => [
                'name' => 'Preceptor',
                'description' => 'Preceptor de nivel secundario',
                'short' => 'Prec',
                'guard' => 'class_assistant',
                'permissions' => [
                    'view profile',
                    'edit profile',
                    'view academic',
                ],
            ],
            'grade_teacher' => [
                'name' => 'Maestra/o de Grado',
                'description' => 'Maestra/o de grado',
                'short' => 'Maes',
                'guard' => 'grade_teacher',
                'permissions' => [
                    'view profile',
                    'edit profile',
                    'view academic',
                ],
            ],
            'assistant_teacher' => [
                'name' => 'Maestra/o Auxiliar',
                'description' => 'Maestra/o auxiliar',
                'short' => 'Auxi',
                'guard' => 'assistant_teacher',
                'permissions' => [
                    'view profile',
                    'edit profile',
                    'view academic',
                ],
            ],
            'curricular_teacher' => [
                'name' => 'Profesor Curricular',
                'description' => 'Profesor de materias curriculares',
                'short' => 'Curr',
                'guard' => 'curricular_teacher',
                'permissions' => [
                    'view profile',
                    'edit profile',
                    'view academic',
                ],
            ],
            'special_teacher' => [
                'name' => 'Profesor/a Especial',
                'description' => 'Profesor/a de educación especial',
                'short' => 'Esp',
                'guard' => 'special_teacher',
                'permissions' => [
                    'view profile',
                    'edit profile',
                    'view academic',
                ],
            ],
            'librarian' => [
                'name' => 'Bibliotecario/a',
                'description' => 'Responsable de la biblioteca',
                'short' => 'Biblio',
                'guard' => 'librarian',
                'permissions' => [
                    'view profile',
                    'edit profile',
                ],
            ],
            'guardian' => [
                'name' => 'Responsable',
                'description' => 'Adulto responsable legal del estudiante',
                'short' => 'Tut',
                'guard' => 'guardian',
                'permissions' => [
                    'view profile',
                    'edit profile',
                ],
            ],
            'student' => [
                'name' => 'Estudiante',
                'description' => 'Estudiante/alumno/a de la institución',
                'short' => 'Est',
                'guard' => 'student',
                'permissions' => [
                    'view profile',
                    'edit profile',
                ],
            ],
            'cooperative' => [
                'name' => 'Cooperativa',
                'description' => 'Miembro de la cooperativa',
                'short' => 'Coop',
                'guard' => 'cooperative',
                'permissions' => [
                    'view profile',
                    'edit profile',
                ],
            ],
            'former_student' => [
                'name' => 'Ex-Estudiante',
                'description' => 'Ex-estudiante de la institución',
                'short' => 'Exalum',
                'guard' => 'former_student',
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
                'guard_name' => $roleData['guard'],
                'team_id' => $globalSchool->id
            ], [
                'description' => $roleData['description'],
                'short' => $roleData['short']
            ]);
            
            $role->syncPermissions($roleData['permissions']);
            
            if ($roleKey === 'admin') {
                $adminRole = $role;
            }
        }

        // Assign admin role to the first user (if exists)
        $user = User::first();
        if ($user && $adminRole) {
            $user->assignRole($adminRole, ['team_id' => $globalSchool->id]);
        }
    }
} 