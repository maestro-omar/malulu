<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FileType extends Model
{
    const PROVINCIAL = 'provincial';
    const INSTITUTIONAL = 'institutional';
    const COURSE = 'course';
    const TEACHER = 'teacher';
    const STUDENT = 'student';
    const USER = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
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
            Profile::teacherKeys(),
            $profilesKeys
        ))) {
            $r[] = self::TEACHER;
        }

        if (!empty(array_intersect([
            Profile::STUDENT,
            Profile::FORMER_STUDENT,
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
}
