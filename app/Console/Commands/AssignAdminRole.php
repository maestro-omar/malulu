<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\School;
use App\Models\Role;

class AssignAdminRole extends Command
{
    protected $signature = 'user:assign-admin {email}';
    protected $description = 'Assign admin role to a user by email';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found!");
            return 1;
        }

        $adminRole = Role::where('key', Role::ADMIN)->first();
        if (!$adminRole) {
            $this->error('Admin role not found! Please run RoleAndPermissionSeeder first.');
            return 1;
        }

        // Get global school
        $globalSchool = School::where('key', 'GLOBAL')->first();
        if (!$globalSchool) {
            $this->error('Global school not found! Please run SchoolSeeder first.');
            return 1;
        }

        // Set the team ID globally
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($globalSchool->id);

        $user->assignRole($adminRole);
        $this->info("Admin role assigned to {$user->name} ({$user->email})");

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->info('Permission cache cleared');

        return 0;
    }
} 