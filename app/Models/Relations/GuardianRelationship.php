<?php

namespace App\Models\Relations;

use App\Models\Base\BaseModel as Model;
use App\Models\Entities\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\FilterConstants;
use App\Models\Relations\RoleRelationship;

class GuardianRelationship extends Model
{
    use SoftDeletes, FilterConstants;

    protected $table = 'guardian_relationships';

    protected $fillable = [
        'role_relationship_id',
        'student_id',
        'relationship_type',
        'is_legal',
        'is_restricted',
        'emergency_contact_priority',
    ];

    protected $casts = [
        'is_legal' => 'boolean',
        'is_restricted' => 'boolean',
        'emergency_contact_priority' => 'integer',
    ];

    // Relationship type constants
    const RELATIONSHIP_PADRE = 'padre';
    const RELATIONSHIP_MADRE = 'madre';
    const RELATIONSHIP_XADRE = 'xadre';
    const RELATIONSHIP_TUTOR = 'tutor';
    const RELATIONSHIP_PARIENTE = 'pariente';
    const RELATIONSHIP_OTRO = 'otro';

    /**
     * Get the role relationship that owns this guardian relationship.
     */
    public function roleRelationship(): BelongsTo
    {
        return $this->belongsTo(RoleRelationship::class);
    }

    /**
     * Get the student associated with this guardian relationship.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get all available relationship types.
     */
    public static function relationshipTypes(): array
    {
        $map = [
            self::RELATIONSHIP_PADRE => 'Padre',
            self::RELATIONSHIP_MADRE => 'Madre',
            self::RELATIONSHIP_TUTOR => 'Tutor',
            self::RELATIONSHIP_XADRE => 'Xadre',
            self::RELATIONSHIP_PARIENTE => 'Pariente',
            self::RELATIONSHIP_OTRO => 'Otro'
        ];

        return collect(self::getFilteredConstants())
            ->mapWithKeys(fn($value) => [$value => $map[$value] ?? ucfirst($value)])
            ->toArray();
    }
}
