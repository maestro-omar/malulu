<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Entities\User;
use App\Models\Entities\School;
use App\Models\Catalogs\Role;

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

        $adminRole = Role::where('code', Role::SUPERADMIN)->first();
        if (!$adminRole) {
            $this->error('Admin role not found! Please run RoleAndPermissionSeeder first.');
            return 1;
        }

        // Get global school
        $globalSchoolId = School::specialGlobalId();
        if (!$globalSchoolId) {
            $this->error('Global school not found! Please run SchoolSeeder first.');
            return 1;
        }

        // Set the team ID globally
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($globalSchoolId);

        $user->assignRole($adminRole);
        $this->info("Admin role assigned to {$user->name} ({$user->email})");

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->info('Permission cache cleared');

        return 0;
    }
}