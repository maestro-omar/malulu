<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'schools';

    /**
     * Profile key constants
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'short',
        'cue',
        'locality_id',
        'management_type_id',
        'address',
        'zip_code',
        'phone',
        'email',
        'coordinates',
        'social',
        'extra',
        'logo',
        'picture'
    ];

    protected $casts = [
        'social' => 'array',
        'extra' => 'array'
    ];

    /**
     * The school levels that belong to the school.
     */
    public function schoolLevels(): BelongsToMany
    {
        return $this->belongsToMany(SchoolLevel::class, 'school_level');
    }

    /**
     * The shifts that belong to the school.
     */
    public function shifts(): BelongsToMany
    {
        return $this->belongsToMany(SchoolShift::class, 'school_shift');
    }

    /**
     * Get the management type that owns the school.
     */
    public function managementType(): BelongsTo
    {
        return $this->belongsTo(SchoolManagementType::class);
    }

    /**
     * Get the locality that owns the school.
     */
    public function locality(): BelongsTo
    {
        return $this->belongsTo(Locality::class);
    }
}
