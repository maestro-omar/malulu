<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Contracts\PermissionsTeamResolver;
use App\Models\User;
use App\Models\School;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SchoolTeamResolver implements PermissionsTeamResolver
{
    public function resolveTeamId(): int|string|null
    {
        // If user is admin, return global school ID
        $user = Auth::user();

        if ($user instanceof User) {
            // Get the admin role ID first
            $adminRoleId = DB::table('roles')
                ->where('name', 'Administrador')
                ->value('id');

            // Log::info('SchoolTeamResolver: Checking admin role', [
            //     'user_id' => $user->id,
            //     'admin_role_id' => $adminRoleId
            // ]);

            if ($adminRoleId) {
                // Check if user has admin role
                $isAdmin = DB::table('model_has_roles')
                    ->where('model_id', $user->id)
                    ->where('model_type', User::class)
                    ->where('role_id', $adminRoleId)
                    ->exists();

                // Log::info('SchoolTeamResolver: Admin check result', [
                //     'is_admin' => $isAdmin,
                //     'user_id' => $user->id,
                //     'role_id' => $adminRoleId
                // ]);

                if ($isAdmin) {
                    $globalSchool = School::where('code', 'GLOBAL')->first();
                    $teamId = $globalSchool ? $globalSchool->id : null;
                    // Log::info('SchoolTeamResolver: Admin user, returning team_id', [
                    //     'team_id' => $teamId,
                    //     'user' => $user->email,
                    //     'global_school' => $globalSchool ? $globalSchool->toArray() : null
                    // ]);
                    return $teamId;
                }
            }
        }

        // For other users, return the current school ID from session
        $teamId = session('current_school_id');
        // Log::info('SchoolTeamResolver: Non-admin user, returning team_id', [
        //     'team_id' => $teamId,
        //     'user' => $user->email,
        //     'user_id' => $user->id
        // ]);
        return $teamId;
    }

    public function getPermissionsTeamId(): int|string|null
    {
        return $this->resolveTeamId();
    }

    public function setPermissionsTeamId($id): void
    {
        // Store the team ID in the session
        session(['current_school_id' => $id]);
    }
}
