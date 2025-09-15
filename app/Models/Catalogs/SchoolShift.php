<?php

namespace App\Models\Catalogs;

use App\Models\Base\BaseCatalogModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\FilterConstants;
use App\Models\Entities\School;

class SchoolShift extends Model
{
    use FilterConstants;
    const MORNING = 'manana';
    const AFTERNOON = 'tarde';
    const NIGHT = 'noche';
    const BOTH = 'ambos';

    protected $table = 'school_shifts';

    protected $fillable = ['name', 'code'];


    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Entities\School::class, 'school_shift');
    }

    public static function vueOptions(): array
    {
        // Get all records from database
        $records = static::all()->keyBy('code');

        $map = [
            self::MORNING   => ['label' => 'Mañana'],
            self::AFTERNOON => ['label' => 'Tarde'],
            self::NIGHT   => ['label' => 'Noche'],
            self::BOTH   => ['label' => 'Ambos'],
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
