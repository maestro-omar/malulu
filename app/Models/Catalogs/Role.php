<?php

namespace App\Models\Catalogs;

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
    const CONFIGURATOR = 'configurador';
    const SCHOOL_ADMIN = 'administrador';
    const DIRECTOR = 'director';
    const REGENT = 'regente';
    const SECRETARY = 'secretaria';
    const ADMINISTRATIVE = 'administrativo';
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
    const MAINTENANCE = 'mantenimiento';
    const EXTERNAL = 'externo';
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
            self::CONFIGURATOR,
            self::SCHOOL_ADMIN,
            self::DIRECTOR,
            self::REGENT,
            self::SECRETARY,
            self::ADMINISTRATIVE,
            self::PROFESSOR,
            self::GRADE_TEACHER,
            self::CLASS_ASSISTANT,
            self::ASSISTANT_TEACHER,
            self::CURRICULAR_TEACHER,
            self::SPECIAL_TEACHER,
            self::LIBRARIAN,
            self::COOPERATIVE,
            self::MAINTENANCE,
            self::EXTERNAL,
            self::STUDENT,
            self::GUARDIAN,
            self::FORMER_STUDENT,
        ];
    }

    /**
     * Get roles that don't require extra data
     *
     * @return array
    public static function rolesWithoutExtraData(): array
    {
        return [
            self::SUPERADMIN,
            self::CONFIGURATOR,
            self::SCHOOL_ADMIN,
            self::DIRECTOR,
            self::REGENT,
            self::SECRETARY,
            self::ADMINISTRATIVE,
        ];
    }
     */

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
            self::ADMINISTRATIVE,
            self::CLASS_ASSISTANT,
            self::PROFESSOR,
            self::GRADE_TEACHER,
            self::ASSISTANT_TEACHER,
            self::CURRICULAR_TEACHER,
            self::SPECIAL_TEACHER,
            self::LIBRARIAN,
            self::MAINTENANCE,
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
            self::MAINTENANCE,
            self::EXTERNAL,
        ];
    }

    public static function vueOptions(): array
    {
        // Get all records from database
        $records = static::all()->keyBy('code');

        $map = [
            self::SUPERADMIN => ['label' => 'Superadmin'],
            self::CONFIGURATOR => ['label' => 'Configurador'],
            self::SCHOOL_ADMIN => ['label' => 'Administrador'],
            self::DIRECTOR => ['label' => 'Director/a'],
            self::REGENT => ['label' => 'Regente'],
            self::SECRETARY => ['label' => 'Secretario/a'],
            self::ADMINISTRATIVE => ['label' => 'Administrativo/a'],
            self::PROFESSOR => ['label' => 'Profesor/a'],
            self::GRADE_TEACHER => ['label' => 'Maestro/a de Grado'],
            self::ASSISTANT_TEACHER => ['label' => 'Auxiliar Docente'],
            self::CURRICULAR_TEACHER => ['label' => 'Maestro/a Curricular'],
            self::SPECIAL_TEACHER => ['label' => 'Maestro/a Especial'],
            self::CLASS_ASSISTANT => ['label' => 'Preceptor/a'],
            self::LIBRARIAN => ['label' => 'Bibliotecario/a'],
            self::GUARDIAN => ['label' => 'Tutor/a'],
            self::STUDENT => ['label' => 'Alumno/a'],
            self::COOPERATIVE => ['label' => 'Cooperadora'],
            self::MAINTENANCE => ['label' => 'Mantenimiento'],
            self::EXTERNAL => ['label' => 'Externo'],
            self::FORMER_STUDENT => ['label' => 'Ex-alumno/a'],
        ];

        return collect(self::getFilteredConstants())
            ->mapWithKeys(function ($value, $constant) use ($records, $map) {
                $record = $records->get($value);
                $label = $map[$value]['label'] ?? ($record ? $record->name : ucfirst(str_replace('_', ' ', $value)));
                return [$value => [
                    'id' => $record ? $record->id : null,
                    'label' => $label,
                    'code' => $value,
                    'short' => $record->short ?? mb_substr($label, 0, 4),
                ]];
            })
            ->toArray();
    }
}
