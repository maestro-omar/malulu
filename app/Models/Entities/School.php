<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Catalogs\SchoolLevel;
use App\Models\Catalogs\SchoolShift;
use App\Models\Catalogs\SchoolManagementType;
use App\Models\Catalogs\Locality;

class School extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'schools';

    const GLOBAL = 'GLOBAL'; //code for special "global" school, for admin uses

    /**
     * Profile code constants
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
        'slug',
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
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

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

    /**
     * Get the pages that belong to the school.
     */
    public function pages(): HasMany
    {
        return $this->hasMany(SchoolPage::class);
    }

    /**
     * Get the ID of the special school GLOBAL (used for superadmin user)
     */
    public static function specialGlobalId(): int
    {
        return self::where('code', self::GLOBAL)->select('id')->firstOrFail()->id;
    }
}
