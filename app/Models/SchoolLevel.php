<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolLevel extends Model
{
    use HasFactory;

    /**
     * SchoolLevel code constants
     */
    const KINDER = 'inicial';
    const PRIMARY = 'primaria';
    const SECONDARY = 'secundaria';

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
        $constants = (new \ReflectionClass(static::class))->getConstants();

        $map = [
            'inicial' => ['label' => 'Inicial', 'color' => 'rose'],
            'primaria'   => ['label' => 'Primaria', 'color' => 'amber'],
            'secundaria' => ['label' => 'Secundaria', 'color' => 'violet'],
        ];

        return collect($constants)
            ->mapWithKeys(fn($value) => [$value => $map[$value] ?? [
                'label' => ucfirst($value),
                'color' => 'gray',
            ]])
            ->toArray();
    }
}
