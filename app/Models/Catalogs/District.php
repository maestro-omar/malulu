<?php

namespace App\Models\Catalogs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Catalogs\Locality;
use App\Models\Catalogs\Province;

class District extends Model
{
    use HasFactory;

    protected $table = 'districts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'province_id',
        'name',
        'long',
        'order'
    ];

    /**
     * Get the province that owns the district.
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Get the localities for the district.
     */
    public function localities()
    {
        return $this->hasMany(Locality::class);
    }
}
