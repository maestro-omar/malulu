<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Entities\School;
use App\Models\Entities\Course;
use App\Models\Entities\AcademicYear;
use App\Models\Catalogs\EventType;
use App\Models\Catalogs\Province;


class AcademicEvent extends Model
{
    use HasFactory;

    protected $table = 'academic_events';

    protected $fillable = [
        'title',
        'date',
        'is_non_working_day',
        'notes',
        'province_id',
        'school_id',
        'academic_year_id',
        'event_type_id',
        'active',
        'created_by',
        'updated_by',
    ];

    public function type()
    {
        return $this->belongsTo(EventType::class, 'event_type_id');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'academic_event_course');
    }
}
