<?php

namespace App\Models\Catalogs;

use App\Models\Base\BaseCatalogModel as Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\FilterConstants;
use App\Models\Relations\Attendance;

class AttendanceStatus extends Model
{
    use FilterConstants;
    const PRESENT = 'presente';
    const LATE = 'tarde';
    const ABSENT_JUSTIFIED = 'ausente_justificado';
    const ABSENT_UNJUSTIFIED = 'ausente_injustificado';
    const ABSENT_NO_CLASSES = 'ausente_sin_clases';
    const PRESENT_NO_CLASSES = 'presente_sin_clases';

    protected $table = 'attendance_statuses';

    protected $fillable = [
        'name',
        'code',
        'symbol',
        'is_absent',
        'is_justified',
        'is_massive',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'status_id');
    }
    public static function vueOptions(): array
    {
        // Get all records from database
        $records = static::all()->keyBy('code');

        $map = [
            self::PRESENT => ['label' => 'Presente'],
            self::LATE => ['label' => 'Tarde'],
            self::ABSENT_JUSTIFIED => ['label' => 'Ausente (Just)'],
            self::ABSENT_UNJUSTIFIED => ['label' => 'Ausente (Injus)'],
            self::ABSENT_NO_CLASSES => ['label' => 'Ausente (Sin Clases)'],
            self::PRESENT_NO_CLASSES => ['label' => 'Presente (Sin Clases)'],
        ];

        return collect(self::getFilteredConstants())
            ->mapWithKeys(function ($value, $constant) use ($records, $map) {
                $record = $records->get($value);
                $label = $map[$value]['label'] ?? ($record ? $record->name : ucfirst($value));

                return [$value => [
                    'id' => $record ? $record->id : null,
                    'label' => $label,
                    'code' => $value,
                    'symbol' => $record ? $record->symbol : mb_substr($label, 0, 1),
                    'is_massive' => $record ? $record->is_massive : false,
                    'is_absent' => $record ? $record->is_absent : false,
                    'is_justified' => $record ? $record->is_justified : false,
                ]];
            })
            ->toArray();
    }
}
