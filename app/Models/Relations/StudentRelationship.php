<?php

namespace App\Models\Relations;

use App\Models\Base\BaseModel as Model;
use App\Models\Entities\Course;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentRelationship extends Model
{
    use SoftDeletes;

    protected $table = 'student_relationships';

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
