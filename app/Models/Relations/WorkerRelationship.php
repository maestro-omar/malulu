<?php

namespace App\Models\Relations;

use App\Models\Base\BaseModel as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\FilterConstants;
use App\Models\Relations\RoleRelationship;
use App\Models\Catalogs\ClassSubject;
use App\Models\Catalogs\JobStatus;
use App\Models\Catalogs\SchoolShift;
use App\Models\Entities\File;

class WorkerRelationship extends Model
{
    use FilterConstants, SoftDeletes;

    protected $table = 'worker_relationships';

    protected $fillable = [
        'role_relationship_id',
        'class_subject_id',
        'school_shift_id',
        'job_status_id',
        'job_status_date',
        'decree_number',
        'decree_file_id',
        'schedule',
        'degree_title',
    ];

    protected $casts = [
        'job_status_date' => 'date',
        'schedule' => 'array',
    ];

    /**
     * Get the role relationship that owns this teacher relationship.
     */
    public function roleRelationship(): BelongsTo
    {
        return $this->belongsTo(RoleRelationship::class);
    }

    /**
     * Get the class subject that this teacher teaches.
     */
    public function classSubject(): BelongsTo
    {
        return $this->belongsTo(ClassSubject::class);
    }

    /**
     * Get the school shift that this teacher teaches.
     */
    public function schoolShift(): BelongsTo
    {
        return $this->belongsTo(SchoolShift::class);
    }

    /**
     * Get the class subject that this teacher teaches.
     */
    public function jobStatus(): BelongsTo
    {
        return $this->belongsTo(JobStatus::class);
    }

    /**
     * Get the decree file associated with this teacher relationship.
     */
    public function decreeFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'decree_file_id');
    }

    public static function jobStatuses()
    {
        return JobStatus::all()->pluck('name', 'id')->toArray();
    }
}
