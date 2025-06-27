<?php

namespace App\Models;

use App\Models\BaseCatalogModel as Model;
use App\Traits\FilterConstants;

class JobStatus extends Model
{
    use FilterConstants;

    const SUBSTITUTE = 'suplente';
    const INTERIM = 'interino';
    const PERMANENT = 'titular';

    protected $table = 'job_statuses';

    protected $fillable = ['name', 'code'];

    /**
     * Returns an array of job status options formatted for Vue components.
     *
     * @return array
     */
    public static function vueOptions(): array
    {
        $map = [
            self::SUBSTITUTE => ['label' => 'Suplente', 'color' => 'red'],
            self::INTERIM    => ['label' => 'Interino', 'color' => 'blue'],
            self::PERMANENT  => ['label' => 'Permanente', 'color' => 'green'],
        ];

        return collect(self::getFilteredConstants())
            ->mapWithKeys(fn($value) => [$value => $map[$value] ?? [
                'label' => ucfirst($value),
                'color' => 'gray',
            ]])
            ->toArray();
    }
}
