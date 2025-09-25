<?php

namespace App\Models\Relations;

use Illuminate\Database\Eloquent\Model;
use App\Models\Relations\AcademicEvent;
use App\Models\Entities\Course;

class AcademicEventCourse extends Model
{
    protected $table = 'academic_event_course';

    protected $fillable = [
        'academic_event_id',
        'course_id',
        'created_by',
        'updated_by',
    ];

    public function academicEvent()
    {
        return $this->belongsTo(AcademicEvent::class, 'academic_event_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
