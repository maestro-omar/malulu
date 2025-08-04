<?php

namespace App\Models\Catalogs;

use App\Models\Base\BaseCatalogModel as Model;
use App\Models\Entities\School;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\FilterConstants;

class SchoolManagementType extends Model
{
    use FilterConstants;

    const PUBLIC = 'publica';
    const PRIVATE = 'privada';
    const SELF_MANAGED = 'autogestionada';
    const GENERATIVE = 'generativa';

    protected $table = 'school_management_types';

    protected $fillable = ['name', 'code'];

    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }

    public static function vueOptions(): array
    {
        // Get all records from database
        $records = static::all()->keyBy('code');

        $map = [
            self::PUBLIC   => ['label' => 'Pública'],
            self::PRIVATE => ['label' => 'Privada'],
            self::GENERATIVE   => ['label' => 'Generativa'],
            self::SELF_MANAGED   => ['label' => 'Autogestionada'],
        ];

        return collect(self::getFilteredConstants())
            ->mapWithKeys(function ($value, $constant) use ($records, $map) {
                $record = $records->get($value);

                return [$value => [
                    'id' => $record ? $record->id : null,
                    'label' => $map[$value]['label'] ?? ucfirst($value),
                    'code' => $value,
                ]];
            })
            ->toArray();
    }
}
