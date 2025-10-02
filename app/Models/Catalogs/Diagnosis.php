<?php

namespace App\Models\Catalogs;

use Illuminate\Database\Eloquent\Model;
use App\Models\Entities\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Diagnosis extends Model
{
    use HasFactory;

    public const CATEGORY_LEARN = 'aprendizaje';
    public const CATEGORY_DISABILITY_MOTOR = 'discapacidad_motriz';
    public const CATEGORY_DISABILITY_AUDITIVE = 'discapacidad_auditiva';
    public const CATEGORY_DISABILITY_VISUAL = 'discapacidad_visual';
    public const CATEGORY_DISABILITY_INTELLECTUAL = 'discapacidad_intelectual';
    public const CATEGORY_DISABILITY_PSYCHOSOCIAL = 'discapacidad_psicosocial';
    public const CATEGORY_DISABILITY_DEVELOPMENT = 'discapacidad_desarrollo';
    public const CATEGORY_CHRONIC = 'salud_cronica';
    public const CATEGORY_ALERGY = 'salud_alergia';
    public const CATEGORY_SYNDROME = 'sindrome';
    public const CATEGORY_OTHER = 'otro';

    protected $fillable = [
        'code',
        'name',
        'category',
        'active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Users with this diagnosis
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_diagnosis')
            ->withPivot(['diagnosed_at', 'notes', 'deleted_at'])
            ->withTimestamps();
    }

    public static function categories(): array{
        return [
            self::CATEGORY_LEARN => 'Trastornos del aprendizaje',
            self::CATEGORY_DISABILITY_MOTOR => 'Discapacidad motriz',
            self::CATEGORY_DISABILITY_AUDITIVE => 'Discapacidad auditiva',
            self::CATEGORY_DISABILITY_VISUAL => 'Discapacidad visual',
            self::CATEGORY_DISABILITY_INTELLECTUAL => 'Discapacidad intelectual',
            self::CATEGORY_DISABILITY_PSYCHOSOCIAL => 'Discapacidad psicosocial',
            self::CATEGORY_DISABILITY_DEVELOPMENT => 'Discapacidad en el desarrollo',
            self::CATEGORY_CHRONIC => 'Condiciones crónicas',
            self::CATEGORY_ALERGY => 'Alergias',
            self::CATEGORY_SYNDROME => 'Síndromes frecuentes en contexto escolar',
            self::CATEGORY_OTHER => 'Otro',
        ];
    }

    public static function getCategoryName($category): string{
        return self::categories()[$category] ?? $category;
    }
}
