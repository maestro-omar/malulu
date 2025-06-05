<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    /**
     * Profile key constants
     */
    const ADMIN = 'admin';
    const DIRECTOR = 'director';
    const REGENT = 'regent';
    const SECRETARY = 'secretary';
    const GRADE_TEACHER = 'grade_teacher';
    const ASSISTANT_TEACHER = 'assistant_teacher';
    const CURRICULAR_TEACHER = 'curricular_teacher';
    const SPECIAL_TEACHER = 'special_teacher';
    const PROFESSOR = 'professor';
    const CLASS_ASSISTANT = 'class_assistant';
    const LIBRARIAN = 'librarian';
    const GUARDIAN = 'guardian';
    const STUDENT = 'student';
    const COOPERATIVE = 'cooperative';
    const FORMER_STUDENT = 'former_student';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'name',
        'description',
        'short'
    ];

    /**
     * Get profile pairs for select
     *
     * @param array $profileKeys
     * @return array
     */
    public static function pairs(array $profileKeys): array
    {
        return self::whereIn('key', $profileKeys)
            ->pluck('name', 'key')
            ->toArray();
    }

    /**
     * Get all profile keys
     *
     * @return array
     */
    public static function allKeys(): array
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
     * Get profiles that don't require extra data
     *
     * @return array
     */
    public static function profilesWithoutExtraData(): array
    {
        return [
            self::ADMIN,
            self::DIRECTOR,
            self::REGENT,
            self::SECRETARY,
        ];
    }

    /**
     * Get worker profile keys
     *
     * @return array
     */
    public static function workersKeys(): array
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
     * Get teacher profile keys
     *
     * @return array
     */
    public static function teacherKeys(): array
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
     * Get family profile keys
     *
     * @return array
     */
    public static function familyKeys(): array
    {
        return [
            self::STUDENT,
            self::FORMER_STUDENT,
            self::GUARDIAN,
        ];
    }

    /**
     * Check if profile is a family member
     *
     * @param string $key
     * @return bool
     */
    public static function isFamily(string $key): bool
    {
        return in_array($key, self::familyKeys());
    }

    /**
     * Check if profile is a teacher
     *
     * @param array|string $key
     * @return bool
     */
    public static function isTeacher(array|string $key): bool
    {
        return (is_array($key) && !empty(array_intersect($key, self::teacherKeys())))
            || (!is_array($key) && in_array($key, self::teacherKeys()));
    }
}