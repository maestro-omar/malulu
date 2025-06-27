<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use App\Traits\FilterConstants;
use App\Traits\HasCatalogAttributes;

class Role extends SpatieRole
{
    use FilterConstants, HasCatalogAttributes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'short',
        'guard_name',
        'team_id'
    ];

    /**
     * Role code constants
     */
    const SUPERADMIN = 'superadmin';
    const ADMIN = 'administrador';
    const DIRECTOR = 'director';
    const REGENT = 'regente';
    const SECRETARY = 'secretaria';
    const GRADE_TEACHER = 'maestra';
    const ASSISTANT_TEACHER = 'auxiliar';
    const CURRICULAR_TEACHER = 'docente_curricular';
    const SPECIAL_TEACHER = 'docente_especial';
    const PROFESSOR = 'profesor';
    const CLASS_ASSISTANT = 'preceptor';
    const LIBRARIAN = 'bibliotecario';
    const GUARDIAN = 'tutor';
    const STUDENT = 'estudiante';
    const COOPERATIVE = 'cooperador';
    const FORMER_STUDENT = 'ex_alumno';

    /**
     * Get role pairs for select
     *
     * @param array $roleCodes
     * @return array
     */
    public static function pairs(array $roleCodes): array
    {
        return self::whereIn('name', $roleCodes)
            ->pluck('name', 'name')
            ->toArray();
    }

    /**
     * Get all role codes
     *
     * @return array
     */
    public static function allCodes(): array
    {
        return [
            self::SUPERADMIN,
            self::ADMIN,
            self::DIRECTOR,
            self::REGENT,
            self::SECRETARY,
            self::PROFESSOR,
            self::GRADE_TEACHER,
            self::CLASS_ASSISTANT,
            self::ASSISTANT_TEACHER,
            self::CURRICULAR_TEACHER,
            self::SPECIAL_TEACHER,
            self::LIBRARIAN,
            self::COOPERATIVE,
            self::STUDENT,
            self::GUARDIAN,
            self::FORMER_STUDENT,
        ];
    }

    /**
     * Get roles that don't require extra data
     *
     * @return array
     */
    public static function rolesWithoutExtraData(): array
    {
        return [
            self::SUPERADMIN,
            self::ADMIN,
            self::DIRECTOR,
            self::REGENT,
            self::SECRETARY,
        ];
    }

    /**
     * Get worker role codes
     *
     * @return array
     */
    public static function workersCodes(): array
    {
        return [
            self::DIRECTOR,
            self::REGENT,
            self::SECRETARY,
            self::CLASS_ASSISTANT,
            self::PROFESSOR,
            self::GRADE_TEACHER,
            self::ASSISTANT_TEACHER,
            self::CURRICULAR_TEACHER,
            self::SPECIAL_TEACHER,
            self::LIBRARIAN,
        ];
    }

    /**
     * Get teacher role codes
     *
     * @return array
     */
    public static function teacherCodes(): array
    {
        return [
            self::PROFESSOR,
            self::GRADE_TEACHER,
            self::ASSISTANT_TEACHER,
            self::CURRICULAR_TEACHER,
            self::SPECIAL_TEACHER,
        ];
    }

    /**
     * Get family role codes
     *
     * @return array
     */
    public static function familyCodes(): array
    {
        return [
            self::STUDENT,
            self::FORMER_STUDENT,
            self::GUARDIAN,
        ];
    }

    /**
     * Check if role is a family member
     *
     * @param string $code
     * @return bool
     */
    public static function isFamily(string $code): bool
    {
        return in_array($code, self::familyCodes());
    }

    /**
     * Check if role is a teacher
     *
     * @param array|string $code
     * @return bool
     */
    public static function isTeacher(array|string $code): bool
    {
        return (is_array($code) && !empty(array_intersect($code, self::teacherCodes())))
            || (!is_array($code) && in_array($code, self::teacherCodes()));
    }

    /**
     * Check if role is a worker
     *
     * @param array|string $code
     * @return bool
     */
    public static function isWorker(array|string $code): bool
    {
        return (is_array($code) && !empty(array_intersect($code, self::workersCodes())))
            || (!is_array($code) && in_array($code, self::workersCodes()));
    }

    public static function allowedMutipleOnSameSchool(): array
    {
        return [
            self::PROFESSOR,
            self::GRADE_TEACHER,
            self::CLASS_ASSISTANT,
            // self::ASSISTANT_TEACHER,
            self::CURRICULAR_TEACHER,
            self::SPECIAL_TEACHER,
            self::LIBRARIAN,
            self::GUARDIAN,
            self::FORMER_STUDENT,
        ];
    }

    public static function vueOptions(): array
    {
        $map = [
            self::SUPERADMIN => ['label' => 'Superadmin', 'color' => 'black'],
            self::ADMIN => ['label' => 'Administrador', 'color' => 'purple'],
            self::DIRECTOR => ['label' => 'Director/a', 'color' => 'blue'],
            self::REGENT => ['label' => 'Regente', 'color' => 'green'],
            self::SECRETARY => ['label' => 'Secretario/a', 'color' => 'yellow'],
            self::PROFESSOR => ['label' => 'Profesor/a', 'color' => 'indigo'],
            self::GRADE_TEACHER => ['label' => 'Maestro/a de Grado', 'color' => 'pink'],
            self::ASSISTANT_TEACHER => ['label' => 'Auxiliar Docente', 'color' => 'orange'],
            self::CURRICULAR_TEACHER => ['label' => 'Maestro/a Curricular', 'color' => 'teal'],
            self::SPECIAL_TEACHER => ['label' => 'Maestro/a Especial', 'color' => 'cyan'],
            self::CLASS_ASSISTANT => ['label' => 'Preceptor/a', 'color' => 'emerald'],
            self::LIBRARIAN => ['label' => 'Bibliotecario/a', 'color' => 'violet'],
            self::GUARDIAN => ['label' => 'Tutor/a', 'color' => 'rose'],
            self::STUDENT => ['label' => 'Alumno/a', 'color' => 'sky'],
            self::COOPERATIVE => ['label' => 'Cooperadora', 'color' => 'amber'],
            self::FORMER_STUDENT => ['label' => 'Ex-alumno/a', 'color' => 'slate'],
        ];

        return collect(self::getFilteredConstants())
            ->mapWithKeys(fn($value) => [$value => $map[$value] ?? [
                'label' => ucfirst(str_replace('_', ' ', $value)),
                'color' => 'gray',
            ]])
            ->toArray();
    }
}
