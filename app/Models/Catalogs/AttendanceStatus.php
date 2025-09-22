<?php

namespace App\Models\Catalogs;

use Illuminate\Database\Eloquent\Model;
use App\Models\Relations\Attendance;

class AttendanceStatus extends Model
{
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
