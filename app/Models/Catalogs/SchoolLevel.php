<?php

namespace App\Models;

use App\Models\BaseCatalogModel as Model;
use App\Traits\FilterConstants;

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
        return $this->belongsToMany(School::class, 'school_level');
    }

    public function roleRelationships()
    {
        return $this->hasMany(RoleRelationship::class);
    }

    public static function vueOptions(): array
    {
        $map = [
            self::KINDER => ['label' => 'Inicial', 'color' => 'rose'],
            self::PRIMARY   => ['label' => 'Primaria', 'color' => 'amber'],
            self::SECONDARY => ['label' => 'Secundaria', 'color' => 'violet'],
        ];

        return collect(self::getFilteredConstants())
            ->mapWithKeys(fn($value) => [$value => $map[$value] ?? [
                'label' => ucfirst($value),
                'color' => 'gray',
            ]])
            ->toArray();
    }
}
