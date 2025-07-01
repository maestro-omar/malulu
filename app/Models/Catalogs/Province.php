<?php

namespace App\Models\Catalogs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseModel as Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    use HasFactory;

    const DEFAULT = 'sl';

    protected $table = 'provinces';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'order',
        'logo1',
        'logo2',
        'title',
        'subtitle',
        'link',
        'config'
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'code';
    }

    /**
     * Get the districts for the province.
     */
    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }
}
