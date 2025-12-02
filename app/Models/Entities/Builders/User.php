<?php

declare(strict_types=1);

namespace App\Models\Entities\Builders;

use App\Models\Entities\Builders\BuilderAbstract;
use Carbon\Carbon;

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
    public function withActiveRoleRelationship(int $roleId, int $schoolId)
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
            $q->forRoles($roleIds)
                ->forSchool($schoolId)
                ->active();
        });
    }

    /**
     * Filter users by birthdate month-day range, handling year boundaries correctly.
     * 
     * @param Carbon $from Start date
     * @param Carbon $to End date
     * @return self
     */
    public function birthdateInRange(Carbon $from, Carbon $to): self
    {
        $fromYear = $from->year;
        $toYear = $to->year;
        $yearDiff = $toYear - $fromYear;

        // If range spans more than one year, return all dates (01-01 to 12-31)
        if ($yearDiff > 1) {
            return $this->whereRaw("DATE_FORMAT(birthdate, '%m-%d') >= ?", ['01-01'])
                ->whereRaw("DATE_FORMAT(birthdate, '%m-%d') <= ?", ['12-31']);
        }

        // If same year, simple range query
        if ($yearDiff === 0) {
            return $this->whereRaw("DATE_FORMAT(birthdate, '%m-%d') >= ?", [$from->format('m-d')])
                ->whereRaw("DATE_FORMAT(birthdate, '%m-%d') <= ?", [$to->format('m-d')]);
        }

        // If crosses one year boundary (yearDiff === 1), split into two periods
        // Period 1: from $from date to end of year (12-31)
        // Period 2: from start of year (01-01) to $to date
        return $this->where(function ($q) use ($from, $to) {
            $q->where(function ($subQ) use ($from) {
                // Period 1: from date to end of year
                $subQ->whereRaw("DATE_FORMAT(birthdate, '%m-%d') >= ?", [$from->format('m-d')])
                    ->whereRaw("DATE_FORMAT(birthdate, '%m-%d') <= ?", ['12-31']);
            })->orWhere(function ($subQ) use ($to) {
                // Period 2: start of year to to date
                $subQ->whereRaw("DATE_FORMAT(birthdate, '%m-%d') >= ?", ['01-01'])
                    ->whereRaw("DATE_FORMAT(birthdate, '%m-%d') <= ?", [$to->format('m-d')]);
            });
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
