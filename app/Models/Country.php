<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseModel as Model;

class Country extends Model
{
    use HasFactory;

    protected $table = 'countries';

    /**
     * Country code constants
     */
    const DEFAULT = 'ar';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'iso',
        'name',
        'order'
    ];
}
