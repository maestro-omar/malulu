<?php

namespace App\Models\Entities;

use App\Models\Base\BaseModel as Model;

class AcademicYear extends Model
{
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
        'extra',
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
        'extra' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->validateDates();
        });
    }

    /**
     * Validate that dates are in the correct order
     *
     * @throws \InvalidArgumentException
     */
    protected function validateDates(): void
    {
        if ($this->start_date >= $this->winter_break_start) {
            throw new \InvalidArgumentException('El inicio de las vacaciones de invierno debe ser posterior al inicio del año académico');
        }

        if ($this->winter_break_start >= $this->winter_break_end) {
            throw new \InvalidArgumentException('El fin de las vacaciones de invierno debe ser posterior al inicio de las vacaciones');
        }

        if ($this->winter_break_end >= $this->end_date) {
            throw new \InvalidArgumentException('El fin del año académico debe ser posterior al fin de las vacaciones de invierno');
        }
    }

    /**
     * Get the courses for this academic year
     */
    public function courses()
    {
        return Course::where(function ($query) {
            $query->where('start_date', '>=', $this->start_date)
                ->where('start_date', '<=', $this->end_date);
        })->orWhere(function ($query) {
            $query->whereNotNull('end_date')
                ->where('end_date', '>=', $this->start_date)
                ->where('end_date', '<=', $this->end_date);
        })->get();
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


    /**
     * Find the academic year that contains the given date.
     * First tries exact date range; if not found, falls back to the year field (calendar year of $date).
     *
     * @param \DateTime $date
     * @return AcademicYear|null
     */
    public static function findByDate(\DateTime $date): ?AcademicYear
    {
        $byRange = self::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        if ($byRange !== null) {
            return $byRange;
        }

        $year = (int) $date->format('Y');
        return self::findByYear($year);
    }

    public function getNext()
    {
        return self::findByYear($this->year + 1);
    }
}
