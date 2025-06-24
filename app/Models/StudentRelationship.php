<?php

namespace App\Models;

use App\Models\BaseModel as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentRelationship extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'role_relationship_id',
        'current_course_id',
    ];

    /**
     * Get the role relationship that owns this student relationship.
     */
    public function roleRelationship(): BelongsTo
    {
        return $this->belongsTo(RoleRelationship::class);
    }

    /**
     * Get the current course for this student.
     */
    public function currentCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'current_course_id');
    }
}