<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Catalogs\EventType;
use App\Models\Catalogs\Province;
use App\Models\Entities\School;
use App\Models\Entities\AcademicYear;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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
        'easter_offset',
    ];

    protected $appends = ['non_working_type_label'];

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

        // NEW: Case 0: Easter-based events
        if ($this->easter_offset !== null) {
            // Check current year first
            $currentYearEaster = $this->calculateEasterDate($today->year);
            $currentYearDate = $this->calculateEasterBasedDate($currentYearEaster, $this->easter_offset);
            if ($currentYearDate && $currentYearDate->between($fromCarbon, $toCarbon)) {
                return $currentYearDate;
            }

            // Check next year
            $nextYearEaster = $this->calculateEasterDate($today->year + 1);
            $nextYearDate = $this->calculateEasterBasedDate($nextYearEaster, $this->easter_offset);
            if ($nextYearDate && $nextYearDate->between($fromCarbon, $toCarbon)) {
                return $nextYearDate;
            }

            // Check previous year
            $prevYearEaster = $this->calculateEasterDate($today->year - 1);
            $prevYearDate = $this->calculateEasterBasedDate($prevYearEaster, $this->easter_offset);
            if ($prevYearDate && $prevYearDate->between($fromCarbon, $toCarbon)) {
                return $prevYearDate;
            }

            return null;
        }

        // Case 1: Event has a fixed date (not recurring)
        // Extract month/day from the event date (ignoring the historical year)
        if ($this->date && !$this->recurrence_month && !$this->recurrence_week && $this->recurrence_weekday === null) {
            $eventDate = Carbon::parse($this->date);
            $day = $eventDate->day;
            $month = $eventDate->month;
            
            // Check all years covered by the date range
            $startYear = $fromCarbon->year;
            $endYear = $toCarbon->year;
            
            for ($year = $startYear; $year <= $endYear; $year++) {
                try {
                    $occurrenceDate = Carbon::create($year, $month, $day);
                    if ($occurrenceDate->between($fromCarbon, $toCarbon)) {
                        return $occurrenceDate;
                    }
                } catch (\Exception $e) {
                    // Skip invalid dates (like Feb 29 in non-leap years)
                    continue;
                }
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
     * Get all occurrences of this event within the given date range.
     * Returns a collection of Carbon dates.
     *
     * @param \DateTime|string $from Start date of the range
     * @param \DateTime|string $to End date of the range
     * @return Collection Collection of Carbon dates representing occurrences
     */
    public function getOccurrencesInRange($from, $to): Collection
    {
        $occurrences = collect();
        $fromCarbon = Carbon::parse($from);
        $toCarbon = Carbon::parse($to);
        $startYear = $fromCarbon->year;
        $endYear = $toCarbon->year;

        // NEW: Case 0: Easter-based events
        if ($this->easter_offset !== null) {
            for ($year = $startYear; $year <= $endYear; $year++) {
                $easterDate = $this->calculateEasterDate($year);
                $occurrenceDate = $this->calculateEasterBasedDate($easterDate, $this->easter_offset);
                
                if ($occurrenceDate && $occurrenceDate->between($fromCarbon, $toCarbon)) {
                    $occurrences->push($occurrenceDate);
                }
            }
            return $occurrences;
        }

        // Case 1: Event has a fixed date (not recurring)
        // Extract month/day from the event date (ignoring the historical year)
        if ($this->date && !$this->recurrence_month && !$this->recurrence_week && $this->recurrence_weekday === null) {
            $eventDate = Carbon::parse($this->date);
            $day = $eventDate->day;
            $month = $eventDate->month;

            // dd($this,$fromCarbon, $toCarbon);
            // Check all years covered by the date range
            for ($year = $startYear; $year <= $endYear; $year++) {
                try {
                    $occurrenceDate = Carbon::create($year, $month, $day);
                    if ($occurrenceDate->between($fromCarbon, $toCarbon)) {
                        $occurrences->push($occurrenceDate);
                    }
                } catch (\Exception $e) {
                    // Skip invalid dates (like Feb 29 in non-leap years)
                    continue;
                }
            }
        }
        // Case 2: Event has a day/month pattern (like Christmas on 25/12 every year)
        elseif ($this->date && ($this->recurrence_month || $this->recurrence_week || $this->recurrence_weekday !== null)) {
            $eventDate = Carbon::parse($this->date);
            $day = $eventDate->day;
            $month = $eventDate->month;

            // Check all years covered by the date range
            for ($year = $startYear; $year <= $endYear; $year++) {
                $occurrenceDate = $this->createDateForYear($year, $month, $day);
                if ($occurrenceDate && $occurrenceDate->between($fromCarbon, $toCarbon)) {
                    $occurrences->push($occurrenceDate);
                }
            }
        }
        // Case 3: Pure recurring event based on recurrence rules only
        elseif ($this->recurrence_month && $this->recurrence_week && $this->recurrence_weekday !== null) {
            // Check all years covered by the date range
            for ($year = $startYear; $year <= $endYear; $year++) {
                $occurrenceDate = $this->calculateOccurrenceForYear(
                    $year,
                    $this->recurrence_month,
                    $this->recurrence_week,
                    $this->recurrence_weekday
                );
                if ($occurrenceDate && $occurrenceDate->between($fromCarbon, $toCarbon)) {
                    $occurrences->push($occurrenceDate);
                }
            }
        }

        return $occurrences;
    }

    /**
     * Check if this event has any occurrences within the given date range.
     *
     * @param \DateTime|string $from Start date of the range
     * @param \DateTime|string $to End date of the range
     * @return bool True if the event has occurrences in the range, false otherwise
     */
    public function matchesDateRange($from, $to): bool
    {
        return $this->getOccurrencesInRange($from, $to)->isNotEmpty();
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

    /**
     * Calculate the Easter date for a specific year.
     * This method uses Carbon's built-in Easter calculation.
     */
    private function calculateEasterDate(int $year): Carbon
    {
        // Carbon has built-in Easter calculation
        // return Carbon::create($year, 1, 1)->easterDate();
        return Carbon::createFromTimestamp(easter_date($year));
    }

    /**
     * Calculate a date based on Easter offset, adjusting for weekday if specified.
     * This ensures Carnival dates fall on the correct weekday (Monday/Tuesday).
     *
     * @param Carbon $easterDate The Easter date for the year
     * @param int $offset Days offset from Easter (e.g., -47 for Carnival Monday)
     * @return Carbon|null The calculated date adjusted to match recurrence_weekday if specified
     */
    private function calculateEasterBasedDate(Carbon $easterDate, int $offset): ?Carbon
    {
        // Start with the approximate date based on offset
        $baseDate = $easterDate->copy()->addDays($offset);
        
        // If recurrence_weekday is specified, adjust to match the required weekday
        if ($this->recurrence_weekday !== null) {
            $targetWeekday = $this->recurrence_weekday; // 0=Sunday, 1=Monday, ..., 6=Saturday
            $currentWeekday = $baseDate->dayOfWeek; // Carbon: 0=Sunday, 1=Monday, ..., 6=Saturday
            
            // Calculate the difference
            $dayDifference = $targetWeekday - $currentWeekday;
            
            // Handle wrap-around (e.g., if we need Monday but got Wednesday, go back 2 days)
            if ($dayDifference > 3) {
                $dayDifference -= 7; // Go back a week
            } elseif ($dayDifference < -3) {
                $dayDifference += 7; // Go forward a week
            }
            
            // Adjust the date to match the required weekday
            $baseDate->addDays($dayDifference);
        }
        
        return $baseDate;
    }

    public function getNonWorkingTypeLabelAttribute()
    {
        $map = [
            self::WORKING_DAY => 'Laborable',
            self::NON_WORKING_FIXED => 'No laborable',
            self::NON_WORKING_FLEXIBLE => 'No laborable (flexible)',
        ];
        return $map[$this->non_working_type] ?? 'Laborable';
    }

    public static function nonWorkingTypeOptions(): array
    {
        return [
            [
                'value' => self::WORKING_DAY,
                'label' => 'Laborable'
            ],
            [
                'value' => self::NON_WORKING_FIXED,
                'label' =>  'No laborable'
            ],
            [
                'value' => self::NON_WORKING_FLEXIBLE,
                'label' => 'No laborable (flexible)'
            ]
        ];
    }
}
