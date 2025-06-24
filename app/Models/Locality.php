<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseModel as Model;

class Locality extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'order',
        'coordinates',
        'district_id'
    ];

    /**
     * Get the district that owns the locality.
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }
}
