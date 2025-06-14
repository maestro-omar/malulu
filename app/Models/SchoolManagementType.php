<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolManagementType extends Model
{
    protected $fillable = ['name', 'code'];

    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }
} 