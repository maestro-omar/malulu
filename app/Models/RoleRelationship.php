<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleRelationship extends Model
{
    use SoftDeletes;

    protected $table = 'role_relationships';

    protected $fillable = [
        'user_id',
        'role_id',
        'school_id',
        'start_date',
        'end_date',
        'end_reason_id',
        'notes',
        'custom_fields',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'custom_fields' => 'array',
    ];

    // Job status constants
    const JOB_STATUS_SUPLENTE = 'suplente';
    const JOB_STATUS_INTERINO = 'interino';
    const JOB_STATUS_TITULAR = 'titular';

    // Relationship type constants
    const RELATIONSHIP_PADRE = 'padre';
    const RELATIONSHIP_MADRE = 'madre';
    const RELATIONSHIP_TUTOR = 'tutor';
    const RELATIONSHIP_OTRO = 'otro';

    /**
     * Get the user that owns the relationship.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the role that owns the relationship.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the school that owns the relationship.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the end reason for this relationship.
     */
    public function endReason(): BelongsTo
    {
        return $this->belongsTo(RoleRelationshipEndReason::class);
    }

    /**
     * Get the user who created the relationship.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the relationship.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the relationship.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Scope a query to only include active relationships.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('end_date')
            ->whereNull('end_reason_id');
    }

    /**
     * Scope a query to only include relationships for a specific role.
     */
    public function scopeForRole($query, $roleId)
    {
        return $query->where('role_id', $roleId);
    }

    /**
     * Scope a query to only include relationships for a specific school.
     */
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Get all job statuses.
     */
    public static function jobStatuses(): array
    {
        return [
            self::JOB_STATUS_SUPLENTE => 'Suplente',
            self::JOB_STATUS_INTERINO => 'Interino',
            self::JOB_STATUS_TITULAR => 'Titular'
        ];
    }

    /**
     * Get all relationship types.
     */
    public static function relationshipTypes(): array
    {
        return [
            self::RELATIONSHIP_PADRE => 'Padre',
            self::RELATIONSHIP_MADRE => 'Madre',
            self::RELATIONSHIP_TUTOR => 'Tutor',
            self::RELATIONSHIP_OTRO => 'Otro'
        ];
    }

    // Role-specific relationships
    public function teacherRelationship()
    {
        return $this->hasOne(TeacherRelationship::class);
    }

    public function guardianRelationship()
    {
        return $this->hasOne(GuardianRelationship::class);
    }

    public function studentRelationship()
    {
        return $this->hasOne(StudentRelationship::class);
    }

    // Helper method to get the specific relationship based on role
    public function getSpecificRelationship()
    {
        return match($this->role_id) {
            1 => $this->teacherRelationship,
            2 => $this->guardianRelationship,
            3 => $this->studentRelationship,
            default => null,
        };
    }

    /**
     * Check if the relationship is active.
     */
    public function isActive(): bool
    {
        return is_null($this->end_date) && is_null($this->end_reason_id);
    }
} 