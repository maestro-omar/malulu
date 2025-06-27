<?php

namespace App\Models\Relations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Relations\RoleRelationship;
use App\Models\Entities\Course;
use App\Models\Catalogs\StudentCourseEndReason;

class StudentCourse extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'student_courses';

    protected $fillable = [
        'role_relationship_id', // student
        'course_id',
        'start_date',
        'end_date',
        'end_reason_id',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'custom_fields' => 'array',
    ];

    public function roleRelationship()
    {
        return $this->belongsTo(RoleRelationship::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function endReason()
    {
        return $this->belongsTo(StudentCourseEndReason::class, 'end_reason_id');
    }
}
