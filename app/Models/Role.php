<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
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
    const ADMIN = 'admin';
    const DIRECTOR = 'director';
    const REGENT = 'regent';
    const SECRETARY = 'secretary';
    const GRADE_TEACHER = 'grade_teacher';
    const ASSISTANT_TEACHER = 'assistant_teacher'; //auxiliar docente, primaria/inicial
    const CURRICULAR_TEACHER = 'curricular_teacher';
    const SPECIAL_TEACHER = 'special_teacher';
    const PROFESSOR = 'professor';
    const CLASS_ASSISTANT = 'class_assistant'; //preceptor
    const LIBRARIAN = 'librarian';
    const GUARDIAN = 'guardian';
    const STUDENT = 'student';
    const COOPERATIVE = 'cooperative';
    const FORMER_STUDENT = 'former_student';

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
}