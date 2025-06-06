<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
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
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $roles = [
            'admin' => $permissions, // Admin has all permissions
            'director' => [
                'view users',
                'view profile',
                'edit profile',
                'view schools',
                'view academic',
            ],
            'teacher' => [
                'view profile',
                'edit profile',
                'view academic',
            ],
            'student' => [
                'view profile',
                'edit profile',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }
    }
} 