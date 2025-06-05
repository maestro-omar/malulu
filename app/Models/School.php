<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

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

    /**
     * The school levels that belong to the school.
     */
    public function schoolLevels()
    {
        return $this->belongsToMany(SchoolLevel::class);
    }
}
