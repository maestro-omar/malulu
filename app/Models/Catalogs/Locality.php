<?php

namespace App\Models\Catalogs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Catalogs\District;
use App\Models\Catalogs\Province;

class Locality extends Model
{
    use HasFactory;

    protected $table = 'localities';

    protected $fillable = [
        'name',
        'order',
        'coordinates',
        'district_id'
    ];

    /**
     * Get the district that owns the locality.
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
