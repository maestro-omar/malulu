<?php

namespace App\Models\Relations;

use App\Models\Base\BaseModel as Model;
use App\Models\Entities\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\FilterConstants;
use App\Models\Relations\TeacherCourse;
use App\Models\Catalogs\SchoolLevel;
use App\Models\Catalogs\Role;
use App\Models\Entities\School;
use App\Models\Catalogs\RoleRelationshipEndReason;
use App\Models\Relations\WorkerRelationship;
use App\Models\Relations\GuardianRelationship;
use App\Models\Relations\StudentRelationship;

class RoleRelationship extends Model
{
    use SoftDeletes;
    use FilterConstants;

    protected $table = 'role_relationships';

    protected $fillable = [
        'user_id',
        'role_id',
        'school_id',
        'school_level_id',
        'start_date',
        'end_date',
        'end_reason_id',
        'notes',
        'custom_fields',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'custom_fields' => 'array',
    ];

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
    public function scopeForRole($query, int $roleId)
    {
        return $query->where('role_id', $roleId);
    }
    
    /**
     * Scope a query to only include relationships for a specific role.
     */
    public function scopeForRoles($query, array $roleIds)
    {
        return $query->whereIn('role_id', $roleIds);
    }

    /**
     * Scope a query to only include relationships for a specific school.
     */
    public function scopeForSchool($query, int $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    // Role-specific relationships
    public function workerRelationship()
    {
        return $this->hasOne(WorkerRelationship::class);
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
        return match ($this->role_id) {
            1 => $this->workerRelationship,
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

    // Add the relationship to SchoolLevel
    public function schoolLevel()
    {
        return $this->belongsTo(SchoolLevel::class);
    }

    public function teacherCourses()
    {
        return $this->hasMany(TeacherCourse::class);
    }
}
