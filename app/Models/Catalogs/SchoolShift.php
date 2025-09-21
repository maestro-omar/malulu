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

    public function getScheduleAttribute()
    {
        if ($this->code == self::MORNING) {
            return [
                'schedule' => [
                    '1' => ['8:00', '8:45'],
                    '2' => ['8:45', '9:30'],
                    'break-1' => ['9:30', '9:45'],
                    '3' => ['9:45', '10:30'],
                    '4' => ['10:30', '11:05'],
                    'break-2' => ['11:05', '11:20'],
                    '5' => ['11:20', '12:00'],
                ],
                'days' => [1, 2, 3, 4, 5], //monday to friday
            ];
        } elseif ($this->code == self::AFTERNOON) {
            return [
                'schedule' => [
                    '1' => ['13:30', '14:15'],
                    '2' => ['14:15', '15:00'],
                    'break-1' => ['15:00', '15:15'],
                    '3' => ['15:15', '16:00'],
                    '4' => ['16:00', '16:35'],
                    'break-2' => ['16:35', '16:50'],
                    '5' => ['16:50', '17:30'],
                ],
                'days' => [1, 2, 3, 4, 5], //monday to friday
            ];
        } elseif ($this->code == self::NIGHT) {
            return [
                'schedule' => [

                    '1' => ['18:00', '18:45'],
                    '2' => ['18:45', '19:30'],
                    'break-1' => ['19:30', '19:45'],
                    '3' => ['19:45', '20:30'],
                    '4' => ['20:30', '21:05'],
                    'break-2' => ['21:05', '21:20'],
                    '5' => ['21:20', '22:00'],
                ],
                'days' => [1, 2, 3, 4, 5], //monday to friday
            ];
        } elseif ($this->code == self::BOTH) {
            return [
                'schedule' => [
                    '1' => ['8:00', '8:45'],
                    '2' => ['8:45', '9:30'],
                    'break-1' => ['9:30', '9:45'],
                    '3' => ['9:45', '10:30'],
                    '4' => ['10:30', '11:05'],
                    'break-2' => ['11:05', '11:20'],
                    '5' => ['11:20', '12:05'],
                    '6' => ['12:05', '12:50'],
                    'lunch' => ['12:50', '14:00'],
                    '7' => ['14:00', '14:45'],
                    '8' => ['14:45', '15:30'],
                ],
                'days' => [1, 2, 3, 4, 5], //monday to friday
            ];
        }
    }
}
