<?php

namespace App\Models\Catalogs;

use App\Models\Base\BaseCatalogModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\FilterConstants;

class SchoolShift extends Model
{
    use FilterConstants;
    const MORNING = 'manana';
    const AFTERNOON = 'tarde';
    const NIGHT = 'noche';

    protected $table = 'school_shifts';

    protected $fillable = ['name', 'code'];


    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class, 'school_shift');
    }

    public static function vueOptions(): array
    {
        $map = [
            self::MORNING   => ['label' => 'Mañana', 'color' => 'green'],
            self::AFTERNOON => ['label' => 'Tarde', 'color' => 'orange'],
            self::NIGHT   => ['label' => 'Noche', 'color' => 'indigo'],
        ];

        return collect(self::getFilteredConstants())
            ->mapWithKeys(fn($value) => [$value => $map[$value] ?? [
                'label' => ucfirst($value),
                'color' => 'gray',
            ]])
            ->toArray();
    }
}
