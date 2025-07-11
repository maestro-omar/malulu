<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Relations\TeacherCourse;
use App\Models\Relations\StudentCourse;
use App\Models\Entities\School;
use App\Models\Catalogs\SchoolLevel;
use App\Models\Catalogs\SchoolShift;

class Course extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'school_id',
        'school_level_id',
        'school_shift_id',
        'previous_course_id',
        'number',
        'letter',
        'start_date',
        'end_date',
        'active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'active' => 'boolean',
    ];

    /**
     * Get the school that owns the course.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the school level that owns the course.
     */
    public function schoolLevel(): BelongsTo
    {
        return $this->belongsTo(SchoolLevel::class);
    }

    /**
     * Get the school shift that owns the course.
     */
    public function schoolShift(): BelongsTo
    {
        return $this->belongsTo(SchoolShift::class);
    }

    /**
     * Get the previous course that owns this course.
     */
    public function previousCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'previous_course_id');
    }

    /**
     * Get the next courses for this course.
     */
    public function nextCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'previous_course_id');
    }

    /**
     * Get the student enrollments for this course.
     */
    public function courseStudents(): HasMany
    {
        return $this->hasMany(StudentCourse::class);
    }

    /**
     * Get the teacher courses (assignments) for this course.
     */
    public function teacherCourses(): HasMany
    {
        return $this->hasMany(TeacherCourse::class);
    }

    /**
     * Get all students (users) enrolled in this course (active, not deleted).
     */
    public function activeStudents()
    {
        return $this->courseStudents()
            ->whereNull('deleted_by')
            ->whereNull('end_date')
            ->with(['roleRelationship.user', 'roleRelationship.studentRelationship'])
            ->get();
    }
}