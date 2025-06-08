<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SchoolShift extends Model
{
    protected $fillable = ['name', 'key'];

    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class, 'school_shift');
    }
} 