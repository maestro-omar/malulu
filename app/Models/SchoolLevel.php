<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolLevel extends Model
{
    use HasFactory;

    /**
     * SchoolLevel key constants
     */
    const KINDER = 'kinder';
    const PRIMARY = 'primary';
    const SECONDARY = 'secondary';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'order',
        'active'
    ];

    /**
     * The schools that belong to the school level.
     */
    public function schools()
    {
        return $this->belongsToMany(School::class, 'school_level');
    }
}
