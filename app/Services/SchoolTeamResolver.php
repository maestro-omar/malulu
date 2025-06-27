<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Contracts\PermissionsTeamResolver;
use App\Models\Catalogs\Role;
use App\Models\Entities\User;
use App\Models\Entities\School;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SchoolTeamResolver implements PermissionsTeamResolver
{
    const SESSION_NAME = 'current_school_id';
    public function resolveTeamId(): int|string|null
    {
        // If user is admin, return global school ID
        $user = Auth::user();

        if ($user instanceof User && $user->isSuperadmin()) {
            return School::specialGlobalId();
        }

        // For other users, return the current school ID from session
        $teamId = session(self::SESSION_NAME);
        return $teamId;
    }

    public function getPermissionsTeamId(): int|string|null
    {
        return $this->resolveTeamId();
    }

    public function setPermissionsTeamId($id): void
    {
        // Store the team ID in the session
        session([self::SESSION_NAME => $id]);
    }
}
