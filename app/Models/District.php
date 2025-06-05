<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'long',
        'order'
    ];

    /**
     * Get the localities for the district.
     */
    public function localities()
    {
        return $this->hasMany(Locality::class);
    }
}
