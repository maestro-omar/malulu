<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolManagementType extends Model
{
    const PUBLIC = 'publica';
    const PRIVATE = 'privada';
    const SELF_MANAGED = 'autogestionada';
    const GENERATIVE = 'generativa';


    protected $fillable = ['name', 'code'];

    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }

    public static function vueOptions(): array
    {
        $constants = (new \ReflectionClass(static::class))->getConstants();

        $map = [
            SchoolManagementType::PUBLIC   => ['label' => 'Pública', 'color' => 'sky'],
            SchoolManagementType::PRIVATE => ['label' => 'Privada', 'color' => 'purple'],
            SchoolManagementType::GENERATIVE   => ['label' => 'Generativa', 'color' => 'emerald'],
            SchoolManagementType::SELF_MANAGED   => ['label' => 'Autogestionada', 'color' => 'pink'],
        ];

        return collect($constants)
            ->mapWithKeys(fn($value) => [$value => $map[$value] ?? [
                'label' => ucfirst($value),
                'color' => 'gray',
            ]])
            ->toArray();
    }
}
