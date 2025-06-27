<?php

namespace App\Models\Catalogs;

use App\Models\Base\BaseCatalogModel as Model;
use App\Traits\FilterConstants;
use App\Models\Relations\RoleRelationship;

class RoleRelationshipEndReason extends Model
{
    use FilterConstants;

    // Academic reasons
    const CODE_GRADUATED = 'graduado';
    const CODE_TRANSFERRED = 'transferido';
    const CODE_WITHDRAWN = 'retirado';

    // Employment reasons
    const CODE_RESIGNED = 'renuncia';
    const CODE_CONTRACT_ENDED = 'contrato_finalizado';
    const CODE_TERMINATED = 'despedido';

    // Guardian reasons
    const CODE_STUDENT_GRADUATED = 'estudiante_graduado';
    const CODE_STUDENT_TRANSFERRED = 'estudiante_transferido';
    const CODE_STUDENT_WITHDRAWN = 'estudiante_retirado';

    // Cooperative reasons
    const CODE_MEMBERSHIP_ENDED = 'periodo_finalizado_coope';
    const CODE_VOLUNTARY_WITHDRAWAL = 'renuncia_coope';
    const CODE_EXPELLED = 'expulsado_coope';

    // Common reasons
    const CODE_DECEASED = 'fallecido';
    const CODE_OTHER = 'otro';

    protected $table = 'role_relationship_end_reasons';

    protected $fillable = [
        'code',
        'name',
        'description',
        'applicable_roles',
        'is_active',
    ];

    protected $casts = [
        'applicable_roles' => 'array',
        'is_active' => 'boolean',
    ];


    // Relationships
    public function roleRelationships()
    {
        return $this->hasMany(RoleRelationship::class, 'end_reason_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForRole($query, $role)
    {
        return $query->whereJsonContains('applicable_roles', $role);
    }

    // Helper methods
    public function isApplicableToRole($role)
    {
        return in_array($role, $this->applicable_roles ?? []);
    }
}
