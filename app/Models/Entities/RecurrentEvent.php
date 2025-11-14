<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Catalogs\EventType;
use App\Models\Catalogs\Province;
use App\Models\Entities\School;
use App\Models\Entities\AcademicYear;
use Carbon\Carbon;

class RecurrentEvent extends Model
{
    use HasFactory;

    const WORKING_DAY = 'laborable';
    const NON_WORKING_FIXED = 'feriado_fijo';
    const NON_WORKING_FLEXIBLE = 'feriado_variable';


    protected $table = 'recurrent_events';

    protected $fillable = [
        'title',
        'date',
        'recurrence_month',
        'recurrence_week',
        'recurrence_weekday',
        'event_type_id',
        'province_id',
        'school_id',
        'academic_year_id',
        'non_working_type',
        'notes',
        'created_by',
        'updated_by',
    ];


    public function type()
    {
        return $this->belongsTo(EventType::class, 'event_type_id');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function calculateDate(\DateTime $from, \DateTime $to)
    {
        // For example, if I'm getting this event from a range of 10 days, and today is december 23
        // new year must use next year, but christmas this year
        // if there is a recursive event for last thursday of the month, must use this year

        $fromCarbon = Carbon::parse($from);
        $toCarbon = Carbon::parse($to);
        $today = Carbon::now();

        // Case 1: Event has a fixed date (not recurring)
        if ($this->date && !$this->recurrence_month && !$this->recurrence_week && $this->recurrence_weekday === null) {
            $eventDate = Carbon::parse($this->date);
            if ($eventDate->between($fromCarbon, $toCarbon)) {
                return $eventDate;
            }
            return null;
        }

        // Case 2: Event has a day/month pattern (like Christmas on 25/12 every year)
        if ($this->date && ($this->recurrence_month || $this->recurrence_week || $this->recurrence_weekday !== null)) {
            $eventDate = Carbon::parse($this->date);
            $day = $eventDate->day;
            $month = $eventDate->month;

            // Check current year first
            $currentYearDate = $this->createDateForYear($today->year, $month, $day);
            if ($currentYearDate && $currentYearDate->between($fromCarbon, $toCarbon)) {
                return $currentYearDate;
            }

            // If current year doesn't work, check next year
            $nextYearDate = $this->createDateForYear($today->year + 1, $month, $day);
            if ($nextYearDate && $nextYearDate->between($fromCarbon, $toCarbon)) {
                return $nextYearDate;
            }

            // Check previous year if we're at the beginning of the year
            $prevYearDate = $this->createDateForYear($today->year - 1, $month, $day);
            if ($prevYearDate && $prevYearDate->between($fromCarbon, $toCarbon)) {
                return $prevYearDate;
            }

            return null;
        }

        // Case 3: Pure recurring event based on recurrence rules only
        if ($this->recurrence_month && $this->recurrence_week && $this->recurrence_weekday !== null) {
            // Check current year first
            $currentYearDate = $this->calculateOccurrenceForYear(
                $today->year,
                $this->recurrence_month,
                $this->recurrence_week,
                $this->recurrence_weekday
            );
            if ($currentYearDate && $currentYearDate->between($fromCarbon, $toCarbon)) {
                return $currentYearDate;
            }

            // If current year doesn't work, check next year
            $nextYearDate = $this->calculateOccurrenceForYear(
                $today->year + 1,
                $this->recurrence_month,
                $this->recurrence_week,
                $this->recurrence_weekday
            );
            if ($nextYearDate && $nextYearDate->between($fromCarbon, $toCarbon)) {
                return $nextYearDate;
            }

            // Check previous year if we're at the beginning of the year
            $prevYearDate = $this->calculateOccurrenceForYear(
                $today->year - 1,
                $this->recurrence_month,
                $this->recurrence_week,
                $this->recurrence_weekday
            );
            if ($prevYearDate && $prevYearDate->between($fromCarbon, $toCarbon)) {
                return $prevYearDate;
            }

            return null;
        }

        return null;
    }

    /**
     * Create a date for a specific year, handling invalid dates gracefully
     * Feb 29 in non-leap years is adjusted to March 1st
     */
    private function createDateForYear(int $year, int $month, int $day): ?Carbon
    {
        try {
            return Carbon::create($year, $month, $day);
        } catch (\Exception $e) {
            // Handle Feb 29 in non-leap years by adjusting to March 1st
            if ($month === 2 && $day === 29) {
                return Carbon::create($year, 3, 1);
            }
            // For other invalid dates, return null
            return null;
        }
    }

    /**
     * Calculate the occurrence date for a specific year based on recurrence rules
     */
    private function calculateOccurrenceForYear(int $year, int $month, int $week, int $weekday): ?Carbon
    {
        // Get the first day of the month
        $firstDay = Carbon::create($year, $month, 1);

        if ($week > 0) {
            // Positive week: find the Nth occurrence
            $targetDay = $firstDay->copy()->startOfWeek()->addDays($weekday);

            // Find the first occurrence of the weekday in the month
            while ($targetDay->month !== $month) {
                $targetDay->addWeek();
            }

            // Add the required number of weeks
            $targetDay->addWeeks($week - 1);

            // Check if we're still in the same month
            if ($targetDay->month === $month) {
                return $targetDay;
            }
        } else {
            // Negative week: find the Nth occurrence from the end
            $lastDay = $firstDay->copy()->endOfMonth();
            $targetDay = $lastDay->copy()->startOfWeek()->addDays($weekday);

            // Find the last occurrence of the weekday in the month
            while ($targetDay->month !== $month) {
                $targetDay->subWeek();
            }

            // Subtract the required number of weeks (week is negative)
            $targetDay->addWeeks($week + 1);

            // Check if we're still in the same month
            if ($targetDay->month === $month) {
                return $targetDay;
            }
        }

        return null;
    }
}
