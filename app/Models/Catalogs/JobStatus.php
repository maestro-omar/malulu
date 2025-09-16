<?php

namespace App\Models\Catalogs;

use App\Models\Base\BaseCatalogModel as Model;
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
    public static function alll()
    {
        return self::getFilteredConstants();
    }

    /**
     * Returns an array of job status options formatted for Vue components.
     *
     * @return array
     */
    public static function vueOptions(): array
    {
        $map = [
            self::SUBSTITUTE => ['label' => 'Suplente'],
            self::INTERIM    => ['label' => 'Interino'],
            self::PERMANENT  => ['label' => 'Titular'],
        ];

        return collect(self::getFilteredConstants())
            ->mapWithKeys(fn($value) => [$value => $map[$value] ?? [
                'label' => ucfirst($value),
            ]])
            ->toArray();
    }
}
