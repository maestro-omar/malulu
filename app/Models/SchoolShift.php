<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SchoolShift extends Model
{
    const MORNING = 'manana';
    const AFTERNOON = 'tarde';
    const NIGHT = 'noche';
    protected $fillable = ['name', 'code'];

    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class, 'school_shift');
    }

    public static function vueOptions(): array
    {
        $constants = (new \ReflectionClass(static::class))->getConstants();

        $map = [
            SchoolShift::MORNING   => ['label' => 'Mañana', 'color' => 'green'],
            SchoolShift::AFTERNOON => ['label' => 'Tarde', 'color' => 'orange'],
            SchoolShift::NIGHT   => ['label' => 'Noche', 'color' => 'indigo'],
        ];

        return collect($constants)
            ->mapWithKeys(fn($value) => [$value => $map[$value] ?? [
                'label' => ucfirst($value),
                'color' => 'gray',
            ]])
            ->toArray();
    }
}