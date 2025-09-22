<?php

namespace App\Models\Relations;

use App\Models\Base\BaseModel as Model;
use App\Models\Entities\User;
use App\Models\Catalogs\AttendanceStatus;

class Attendance extends Model
{
    protected $table = 'attendance';

    protected $fillable = [
        'user_id',
        'date',
        'status_id',
        'file_id',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['date'];

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function status()
    {
        return $this->belongsTo(AttendanceStatus::class, 'status_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
