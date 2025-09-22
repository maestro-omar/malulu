<?php

namespace App\Models\Catalogs;

use App\Models\Base\BaseCatalogModel as Model;
use App\Models\Relations\Attendance;

class AttendanceStatus extends Model
{
    protected $table = 'attendance_statuses';

    protected $fillable = [
        'code',
        'name',
        'is_absent',
        'is_justified',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'status_id');
    }
}
