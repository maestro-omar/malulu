<?php

namespace App\Models\Catalogs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventResponsibilityType extends Model
{
    use HasFactory;

    public const CODE_COORDINATION = 'coordination';

    protected $table = 'event_responsibility_types';

    protected $fillable = [
        'code',
        'name',
        'description',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function academicEventResponsibles()
    {
        return $this->hasMany(\App\Models\Relations\AcademicEventResponsible::class, 'event_responsibility_type_id');
    }

    public static function forDropdown(): array
    {
        return static::orderBy('order')->orderBy('name')
            ->get()
            ->map(fn (self $t) => ['id' => $t->id, 'label' => $t->name, 'code' => $t->code])
            ->values()
            ->toArray();
    }
}
