<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\Entities\School;

class UserContextService
{
    public static function load(): void
    {
        /** @var \App\Models\Entities\User $user */
        $user = Auth::user();
        if (!$user) return;

        $activeRoleRelationships = $user->activeRoleRelationships()->map(function ($rel) {
            return [
                'role_id'        => $rel->role_id,
                'role_code'      => $rel->role->code,
                'school_id'      => $rel->school_id,
                'school_level_id' => $rel->school_level_id,
                'custom_fields'  => $rel->custom_fields,
                'start_date'     => $rel->start_date,
            ];
        });

        $schoolIds = empty($activeRoleRelationships) ? [] : $activeRoleRelationships->pluck('school_id')->unique()->toArray();
        $userSchools = empty($schoolIds) ? [] : School::select('cue', 'id', 'name', 'short', 'slug', 'logo', 'extra')->find($schoolIds)->keyBy('id')->toArray();
        $first = empty($schoolIds) ? null : $activeRoleRelationships->first();

        //omar importante
        $context = [
            'schools' => $userSchools,
            'activeRoleRelationships' => $activeRoleRelationships->toArray(),
            'activeSchoolId' => empty($first) ? null : $first['school_id'],
            'activeRoleRel' => $first,
        ];

        Session::put('user_context', $context);
    }

    public static function get(): array
    {
        return Session::get('user_context', []);
    }

    public static function forget(): void
    {
        Session::forget('user_context');
    }

    public static function activeSchool(): ?array
    {
        return self::relatedSchools()[self::activeSchoolId()] ?? null;
    }

    public static function relatedSchools(): ?array
    {
        return Session::get('user_context.schools');
    }

    public static function activeSchoolId(): ?int
    {
        return Session::get('user_context.activeSchoolId');
    }
    public static function all()
    {
        return Session::get('user_context');
    }

    public static function setActiveSchool(int $schoolId): void
    {
        Session::put('user_context.activeSchoolId', $schoolId);
    }
}
