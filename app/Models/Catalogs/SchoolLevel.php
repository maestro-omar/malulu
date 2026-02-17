<?php

namespace App\Models\Catalogs;

use App\Models\Base\BaseCatalogModel as Model;
use App\Traits\FilterConstants;
use App\Models\Entities\School;
use App\Models\Relations\RoleRelationship;

class SchoolLevel extends Model
{
    use FilterConstants;

    /**
     * SchoolLevel code constants
     */
    const KINDER = 'inicial';
    const PRIMARY = 'primaria';
    const SECONDARY = 'secundaria';

    protected $table = 'school_levels';

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
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'code';
    }

    /**
     * The schools that belong to the school level.
     */
    public function schools()
    {
        return $this->belongsToMany(School::class, 'school_level')
            ->withPivot('years_duration', 'grades');
    }

    public function roleRelationships()
    {
        return $this->hasMany(RoleRelationship::class);
    }

    public static function vueOptions(): array
    {
        // Get all records from database
        $records = static::all()->keyBy('code');

        $map = [
            self::KINDER => ['label' => 'Inicial'],
            self::PRIMARY   => ['label' => 'Primaria'],
            self::SECONDARY => ['label' => 'Secundaria'],
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
