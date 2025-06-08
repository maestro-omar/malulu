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

    /**
     * Profile key constants
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'name',
        'short',
        'locality_id',
        'address',
        'zip_code',
        'phone',
        'email',
        'coordinates',
        'cue',
        'extra',
    ];

    protected $casts = [
        'extra' => 'array',
    ];

    /**
     * The school levels that belong to the school.
     */
    public function schoolLevels(): BelongsToMany
    {
        return $this->belongsToMany(SchoolLevel::class);
    }

    public function userProfiles()
    {
        return $this->belongsToMany(User::class, 'profile_user_school')
            ->withPivot('profile_id', 'active', 'entry_date', 'end_date', 'extra')
            ->withTimestamps();
    }

    public function profiles()
    {
        return $this->belongsToMany(Profile::class, 'profile_user_school')
            ->withPivot('user_id', 'active', 'entry_date', 'end_date', 'extra')
            ->withTimestamps();
    }

    public function locality(): BelongsTo
    {
        return $this->belongsTo(Locality::class);
    }
}
