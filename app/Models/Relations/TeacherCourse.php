<?php

namespace App\Models\Relations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseModel as Model;
use App\Models\Entities\Course;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherCourse extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'teacher_courses';

    protected $fillable = [
        'role_relationship_id', // teacher
        'course_id',
        'start_date',
        'end_date',
        'active',
        'in_charge',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'in_charge' => 'boolean',
        'active' => 'boolean',
    ];

    public function roleRelationship()
    {
        return $this->belongsTo(RoleRelationship::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
