<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicYear extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'year',
        'start_date',
        'end_date',
        'winter_break_start',
        'winter_break_end',
        'active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'year' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'winter_break_start' => 'datetime',
        'winter_break_end' => 'datetime',
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Get the courses for this academic year
     */
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    /**
     * Find an academic year by its year
     *
     * @param int $year
     * @return AcademicYear|null
     */
    public static function findByYear(int $year): ?AcademicYear
    {
        return self::where('year', $year)->first();
    }

    /**
     * Get the current academic year
     *
     * @return AcademicYear|null
     */
    public static function getCurrent(): ?AcademicYear
    {
        $now = now();
        return self::where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->first();
    }

    /**
     * Check if a date falls within this academic year
     *
     * @param \DateTime $date
     * @return bool
     */
    public function containsDate(\DateTime $date): bool
    {
        return $date >= $this->start_date && $date <= $this->end_date;
    }

    /**
     * Check if a date falls within the winter break
     *
     * @param \DateTime $date
     * @return bool
     */
    public function isWinterBreak(\DateTime $date): bool
    {
        return $date >= $this->winter_break_start && $date <= $this->winter_break_end;
    }
}