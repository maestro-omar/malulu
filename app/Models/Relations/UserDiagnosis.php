<?php

namespace App\Models\Relations;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use \App\Models\Catalogs\Diagnosis;
use \App\Models\Entities\User;

class UserDiagnosis extends Pivot
{
    use SoftDeletes;

    public $incrementing = true;
    protected $primaryKey = 'id';
    protected $keyType = 'int';

    protected $table = 'user_diagnosis';

    protected $fillable = [
        'user_id',
        'diagnosis_id',
        'diagnosed_at',
        'notes',
    ];

    protected $dates = [
        'diagnosed_at',
        'deleted_at',
    ];

    /**
     * User associated with this diagnosis.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Diagnosis associated with this user.
     */
    public function diagnosis()
    {
        return $this->belongsTo(Diagnosis::class);
    }

    /**
     * Check if diagnosis is currently active.
     */
    public function isActive(): bool
    {
        return is_null($this->deleted_at);
    }
}
