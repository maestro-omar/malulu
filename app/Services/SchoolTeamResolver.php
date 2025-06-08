<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Contracts\PermissionsTeamResolver;
use App\Models\User;
use App\Models\School;

class SchoolTeamResolver implements PermissionsTeamResolver
{
    public function resolveTeamId(): int|string|null
    {
        // If user is admin, return global school ID
        $user = Auth::user();
        if ($user instanceof User && $user->roles()->where('name', 'admin')->exists()) {
            $globalSchool = School::where('key', 'GLOBAL')->first();
            return $globalSchool ? $globalSchool->id : null;
        }

        // For other users, return the current school ID from session
        return session('current_school_id');
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