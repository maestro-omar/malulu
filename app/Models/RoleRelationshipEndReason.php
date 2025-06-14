<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoleRelationshipEndReason extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'applicable_roles',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'applicable_roles' => 'array',
        'is_active' => 'boolean',
    ];

    // Academic reasons
    const CODE_GRADUATED = 'graduated';
    const CODE_TRANSFERRED = 'transferred';
    const CODE_WITHDRAWN = 'withdrawn';

    // Employment reasons
    const CODE_RESIGNED = 'resigned';
    const CODE_CONTRACT_ENDED = 'contract_ended';
    const CODE_TERMINATED = 'terminated';

    // Guardian reasons
    const CODE_STUDENT_GRADUATED = 'student_graduated';
    const CODE_STUDENT_TRANSFERRED = 'student_transferred';
    const CODE_STUDENT_WITHDRAWN = 'student_withdrawn';

    // Cooperative reasons
    const CODE_MEMBERSHIP_ENDED = 'membership_ended';
    const CODE_VOLUNTARY_WITHDRAWAL = 'voluntary_withdrawal';
    const CODE_EXPELLED = 'expelled';

    // Common reasons
    const CODE_DECEASED = 'deceased';
    const CODE_OTHER = 'other';

    // Relationships
    public function roleRelationships()
    {
        return $this->hasMany(RoleRelationship::class, 'end_reason_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForRole($query, $role)
    {
        return $query->whereJsonContains('applicable_roles', $role);
    }

    // Helper methods
    public function isApplicableToRole($role)
    {
        return in_array($role, $this->applicable_roles ?? []);
    }
} 