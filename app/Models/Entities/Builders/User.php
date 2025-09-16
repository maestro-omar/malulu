<?php

declare(strict_types=1);

namespace App\Models\Entities\Builders;

use App\Models\Entities\Builders\BuilderAbstract;

class User extends BuilderAbstract
{

    /**
     * @param int $schoolId
     *
     * @return self
     */
    public function bySchoolId(int $schoolId): self
    {
        return $this->where('active', true) // usuario activo
            ->whereHas('roleRelationships', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId)
                    ->where('active', true) // relación activa
                    ->whereHas('school', fn($schoolQuery) => $schoolQuery->where('active', true));
            });
    }

    /**
     * Scope to filter users who have active role relationships with specific role and school.
     */
    public function withActiveRoleRelationship($roleId, $schoolId)
    {
        return $this->whereHas('roleRelationships', function ($q) use ($roleId, $schoolId) {
            $q->forRole($roleId)
                ->forSchool($schoolId)
                ->active();
        });
    }

    /**
     * Scope to filter users who have active role relationships with specific role and school.
     */
    public function withActiveRoleRelationships($roleIds, $schoolId)
    {
        return $this->whereHas('roleRelationships', function ($q) use ($roleIds, $schoolId) {
            $q->forRole($roleIds)
                ->forSchool($schoolId)
                ->active();
        });
    }
    /**
     * Scope to filter users who have active role relationships with any of the provided role and school combinations.
    public function scopeWithActiveRoleRelationships($query, array $roleSchoolPairs)
    {
        return $query->whereHas('roleRelationships', function ($q) use ($roleSchoolPairs) {
            $q->where(function ($subQuery) use ($roleSchoolPairs) {
                foreach ($roleSchoolPairs as $pair) {
                    $roleId = $pair['role_id'] ?? $pair[0];
                    $schoolId = $pair['school_id'] ?? $pair[1];

                    $subQuery->orWhere(function ($orQuery) use ($roleId, $schoolId) {
                        $orQuery->forRole($roleId)
                            ->forSchool($schoolId)
                            ->active();
                    });
                }
            });
        });
    }
     */
}
