<?php

namespace App\Models\Catalogs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseModel as Model;

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
