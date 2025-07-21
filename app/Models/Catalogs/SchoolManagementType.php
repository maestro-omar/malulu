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
        $map = [
            self::PUBLIC   => ['label' => 'Pública', 'color' => 'sky'],
            self::PRIVATE => ['label' => 'Privada', 'color' => 'purple'],
            self::GENERATIVE   => ['label' => 'Generativa', 'color' => 'emerald'],
            self::SELF_MANAGED   => ['label' => 'Autogestionada', 'color' => 'pink'],
        ];

        return collect(self::getFilteredConstants())
            ->mapWithKeys(fn($value) => [$value => $map[$value] ?? [
                'label' => ucfirst($value),
                'color' => 'gray',
            ]])
            ->toArray();
    }
}
