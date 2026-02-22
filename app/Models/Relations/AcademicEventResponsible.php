<?php

namespace App\Models\Relations;

use App\Models\Base\BaseModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Entities\AcademicEvent;
use App\Models\Catalogs\EventResponsibilityType;

class AcademicEventResponsible extends Model
{
    protected $table = 'academic_event_responsibles';

    protected $fillable = [
        'academic_event_id',
        'role_relationship_id',
        'event_responsibility_type_id',
        'notes',
    ];

    public function academicEvent(): BelongsTo
    {
        return $this->belongsTo(AcademicEvent::class);
    }

    public function roleRelationship(): BelongsTo
    {
        return $this->belongsTo(RoleRelationship::class);
    }

    public function responsibilityType(): BelongsTo
    {
        return $this->belongsTo(EventResponsibilityType::class, 'event_responsibility_type_id');
    }
}
