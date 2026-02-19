<?php

namespace App\Models\Catalogs;

use App\Models\Base\BaseCatalogModel as Model;
use App\Models\Relations\StudentCourse;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\FilterConstants;

class StudentCourseEndReason extends Model
{
    use FilterConstants;

    const CODE_TRANSFER_SAME_SHIFT     = 'cambio_mismo_turno';
    const CODE_TRANSFER_OTHER_SHIFT    = 'cambio_otro_turno';
    const CODE_TRANSFER_OTHER_SCHOOL   = 'cambio_escuela';
    const CODE_PROMOTED                = 'promocionado';
    const CODE_GRADUATED               = 'graduado';
    const CODE_WITHDRAWN               = 'abandono';
    const CODE_STAYS                   = 'permanece';
    const CODE_DECEASED                = 'fallecido';
    const CODE_OTHER                   = 'otro ';

    protected $table = 'student_course_end_reasons';

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function studentCourses(): HasMany
    {
        return $this->hasMany(StudentCourse::class);
    }
}
