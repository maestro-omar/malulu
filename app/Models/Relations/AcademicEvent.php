<?php

namespace App\Models\Relations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Entities\School;
use App\Models\Entities\AcademicYear;
use App\Models\Catalogs\EventType;


class AcademicEvent extends Model
{
    use HasFactory;

    protected $table = 'academic_events';

    protected $fillable = [
        'title',
        'date',
        'is_non_working_day',
        'notes',
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

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
}
