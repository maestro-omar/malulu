<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Role;

class FileType extends Model
{
    const PROVINCIAL = 'provincial';
    const INSTITUTIONAL = 'institutional';
    const COURSE = 'course';
    const TEACHER = 'teacher';
    const STUDENT = 'student';
    const USER = 'user';

    // Add these new constants for relate_with options
    const RELATE_WITH_SCHOOL = 'school';
    const RELATE_WITH_COURSE = 'course';
    const RELATE_WITH_TEACHER = 'teacher';
    const RELATE_WITH_STUDENT = 'student';
    const RELATE_WITH_USER = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'relate_with',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    /**
     * Get all file type keys
     *
     * @return array
     */
    public static function allKeys(): array
    {
        return [
            self::PROVINCIAL,
            self::INSTITUTIONAL,
            self::COURSE,
            self::TEACHER,
            self::STUDENT,
            self::USER
        ];
    }

    /**
     * Get user-related file type keys
     *
     * @return array
     */
    public static function userRelatedKeys(): array
    {
        return [
            self::TEACHER,
            self::STUDENT,
            self::USER
        ];
    }

    /**
     * Get file types for specific profiles
     *
     * @param array $profilesKeys
     * @return array
     */
    public static function getForProfiles(array $profilesKeys): array
    {
        $r = [self::USER];

        if (!empty(array_intersect(
            Role::teacherKeys(),
            $profilesKeys
        ))) {
            $r[] = self::TEACHER;
        }

        if (!empty(array_intersect([
            Role::STUDENT,
            Role::FORMER_STUDENT,
        ], $profilesKeys))) {
            $r[] = self::STUDENT;
        }

        return $r;
    }

    /**
     * Get the subtypes for this file type
     */
    public function subtypes(): HasMany
    {
        return $this->hasMany(FileSubtype::class);
    }

    /**
     * Get available options for relate_with field
     *
     * @return array
     */
    public static function relateWithOptions(): array
    {
        return [
            self::RELATE_WITH_SCHOOL => 'Escuela',
            self::RELATE_WITH_COURSE => 'Grupo',
            self::RELATE_WITH_TEACHER => 'Docente',
            self::RELATE_WITH_STUDENT => 'Alumna/o',
            self::RELATE_WITH_USER => 'Usuario',
        ];
    }

    public function fileSubtypes()
    {
        return $this->hasMany(FileSubtype::class);
    }
}
