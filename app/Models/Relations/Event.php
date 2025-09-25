<?php

namespace App\Models\Relations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Catalogs\EventType;
use App\Models\Catalogs\Province;
use App\Models\Entities\School;
use App\Models\Entities\AcademicYear;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';

    protected $fillable = [
        'title',
        'date',
        'is_recurring',
        'recurrence_month',
        'recurrence_week',
        'recurrence_weekday',
        'event_type_id',
        'province_id',
        'school_id',
        'academic_year_id',
        'is_non_working_day',
        'notes',
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
}
