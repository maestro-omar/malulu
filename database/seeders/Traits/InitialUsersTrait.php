<?php

namespace Database\Seeders\Traits;

use App\Models\Catalogs\SchoolShift;
use App\Models\Catalogs\ClassSubject;
use App\Models\Entities\User;
use Database\Seeders\Faker\UserFaker;

/**
 * Trait with shared methods for Initial Staff and Students seeders
 */
trait InitialUsersTrait
{
    protected $faker;
    protected $jsonFolder;
    protected $defaultSchool;
    protected $primaryLevel;
    protected $courses;
    protected $province;
    protected $country;
    protected $countries;
    protected $academicYears;
    protected $allRoles;
    protected $userService;
    protected $creator;
    protected $jsonForYear = 2025;

    /**
     * Initialize the faker and JSON folder path
     */
    protected function initializeFaker(): void
    {
        if (!$this->faker) {
            // Use the seeders directory (parent of Traits directory)
            $this->jsonFolder = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
            $this->faker = \Faker\Factory::create('es_ES');
            $this->faker->addProvider(new UserFaker($this->faker));
        }
    }

    /**
     * Map gender string to User gender constant
     */
    protected function mapGender($gender)
    {
        return match (strtolower(trim($gender))) {
            'masculino' => User::GENDER_MALE,
            'm' => User::GENDER_MALE,
            'masc' => User::GENDER_MALE,
            'femenino' => User::GENDER_FEMALE,
            'femenina' => User::GENDER_FEMALE,
            'f' => User::GENDER_FEMALE,
            'fem' => User::GENDER_FEMALE,
            'no binario' => User::GENDER_NOBINARY,
            'no-binario' => User::GENDER_NOBINARY,
            'no-bin' => User::GENDER_NOBINARY,
            'fluido' => User::GENDER_FLUID,
            'fluído' => User::GENDER_FLUID,
            'trans' => User::GENDER_TRANS,
            'transgénero' => User::GENDER_TRANS,
            'otro' => User::GENDER_OTHER,
            default => User::GENDER_FEMALE, // Default fallback
        };
    }

    /**
     * Map shift string to SchoolShift model
     */
    protected function mapShift(string $turno): SchoolShift
    {
        $turno = strtolower(trim($turno));
        $code = match (true) {
            str_contains($turno, 'mañana') => SchoolShift::MORNING,
            str_contains($turno, 'tarde') => SchoolShift::AFTERNOON,
            str_contains($turno, 'ambos') => SchoolShift::BOTH,
            default => 'Mañana', // Default fallback
        };
        return SchoolShift::where('code', $code)->first();
    }

    /**
     * Parse date string in various formats
     */
    protected function parseDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }

        // Handle different date formats
        $formats = [
            'd/m/Y',
            'd-m-Y',
            'Y-m-d',
            'd/m/y',
            'd-m-y',
        ];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date !== false) {
                $age = $this->faker->calculateAge($date);
                if ($age < 90 && $age > 0) {
                    return $date->format('Y-m-d');
                }
            }
        }

        // Try to parse as timestamp or year only
        if (is_numeric($dateString)) {
            if (strlen($dateString) === 4) {
                // Year only
                return $dateString . '-01-01';
            } elseif (strlen($dateString) >= 8) {
                // Try as timestamp
                $date = new \DateTime();
                $date->setTimestamp($dateString);
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    /**
     * Format phone number (remove non-numeric characters)
     */
    protected function formatPhone($phone)
    {
        if (empty($phone)) {
            return null;
        }

        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        return $phone;
    }

    /**
     * Get nationality based on birth place
     */
    protected function nacionalityBasedOnBirthPlace($nacionality, $birth_place)
    {
        if (!empty($nacionality)) {
            return $nacionality;
        }
        if ($this->countries->where('name', $birth_place)->first()) {
            return $birth_place;
        }
        return 'Argentina';
    }

    /**
     * Normalize course strings to course arrays.
     * Optional $onDate (Y-m-d): when resolving number+letter, pick the course that contains this date (for correct cohort).
     */
    protected function normalizeCourses($shift, $agrupamiento, bool $specialAddToNumber = false, $onDate = null)
    {
        if (empty($agrupamiento)) {
            return [];
        }

        $agrupamiento = str_replace(' ', '', trim($agrupamiento));

        // Handle special cases first
        if (str_contains(strtolower($agrupamiento), 'segundo ciclo')) {
            return $this->getCoursesByNumbers($shift, [4, 5, 6, 7, 8], false);
        }

        if (str_contains(strtolower($agrupamiento), 'primer y segundo ciclo')) {
            return $this->getCoursesByNumbers($shift, [1, 2, 3, 4, 5, 6, 7, 8], false);
        }

        if (str_contains(strtolower($agrupamiento), 'primer ciclo')) {
            return $this->getCoursesByNumbers($shift, [1, 2, 3], false);
        }

        // 7/8: take only grade 7 (Lucio's grade 7 becomes 8 mid-year; assigning both would reassign same cohort)
        if (str_contains(strtolower($agrupamiento), '7/8') || str_contains(strtolower($agrupamiento), '7mos / 8vos')) {
            return $this->getCoursesByNumbers($shift, [7], false);
        }

        if (str_contains(strtolower($agrupamiento), '7mos') || str_contains(strtolower($agrupamiento), '7mo')) {
            return $this->getCoursesByNumbers($shift, [7], false);
        }

        if (str_contains(strtolower($agrupamiento), '8vos') || str_contains(strtolower($agrupamiento), '8vo')) {
            return $this->getCoursesByNumbers($shift, [8], false);
        }

        if (str_contains(strtolower($agrupamiento), 'administración')) {
            return []; // Administration doesn't correspond to specific courses
        }

        // Handle numeric values (like just "2")
        if (is_numeric($agrupamiento)) {
            return $this->getCoursesByNumbers($shift, [(int)$agrupamiento], $specialAddToNumber);
        }

        // Handle specific course patterns like "1A", "3c", "6to F", etc.
        $courses = [];

        // Split by common separators
        $parts = preg_split('/[,;]/', $agrupamiento);

        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part)) continue;

            $parsed = $this->parseCourseString($shift, $part, $specialAddToNumber, $onDate);
            if ($parsed) {
                $courses[] = $parsed;
            }
        }

        return $courses;
    }

    /**
     * Parse a course string into course data.
     * @param string|null $onDate Y-m-d to resolve cohort (course that contains this date)
     */
    protected function parseCourseString($shift, $courseString, ?bool $specialAddToNumber = false, $onDate = null)
    {
        $courseString = trim($courseString);

        // Handle patterns like "1A", "3c", "6to F", "1°A", "3ero D", etc.
        if (preg_match('/(\d+)(?:°|to|ro|er|mo|vo|vo)?\s*([A-Za-z])/i', $courseString, $matches)) {
            $number = (int)$matches[1];
            $letter = strtoupper($matches[2]);

            return $this->getCourseByNumberAndLetter($number + ($specialAddToNumber && $number > 3 ? 1 : 0), $letter, $shift, $onDate);
        }

        // Handle patterns like "1 agrupamiento b", "1er agrupamiento C"
        if (preg_match('/(\d+)(?:er|ro|mo|vo)?\s+agrupamiento\s+([A-Za-z])/i', $courseString, $matches)) {
            $number = (int)$matches[1];
            $letter = strtoupper($matches[2]);

            return $this->getCourseByNumberAndLetter($number + ($specialAddToNumber && $number > 3 ? 1 : 0), $letter, $shift, $onDate);
        }

        // Handle patterns like "1 agrupamiento division C"
        if (preg_match('/(\d+)\s+agrupamiento\s+division\s+([A-Za-z])/i', $courseString, $matches)) {
            $number = (int)$matches[1];
            $letter = strtoupper($matches[2]);

            return $this->getCourseByNumberAndLetter($number + ($specialAddToNumber && $number > 3 ? 1 : 0), $letter, $shift, $onDate);
        }

        // Handle patterns like "3er agrupamiento"
        if (preg_match('/(\d+)(?:er|ro|mo|vo)?\s+agrupamiento/i', $courseString, $matches)) {
            $number = (int)$matches[1];
            // For this case, we need to get all letters for that number
            return $this->getCoursesByNumbers($shift, [$number + ($specialAddToNumber && $number > 3 ? 1 : 0)], false);
        }

        return null;
    }

    /**
     * Get course by number and letter, optionally for a specific shift (so 6C Mañana vs 6C Tarde is correct).
     * When $onDate (Y-m-d) is provided, returns the course whose [start_date, end_date] contains $onDate (correct cohort).
     */
    protected function getCourseByNumberAndLetter($number, $letter, $shift = null, $onDate = null)
    {
        $filtered = $this->courses->where('number', $number)->where('letter', $letter);
        if ($shift !== null && is_object($shift) && isset($shift->id)) {
            $filtered = $filtered->where('school_shift_id', $shift->id);
        }
        if ($onDate !== null && $onDate !== '') {
            $on = \Carbon\Carbon::parse($onDate)->startOfDay();
            $filtered = $filtered->filter(function ($c) use ($on) {
                $start = \Carbon\Carbon::parse($c->start_date)->startOfDay();
                $end = $c->end_date ? \Carbon\Carbon::parse($c->end_date)->startOfDay() : null;
                return $start->lte($on) && ($end === null || $end->gte($on));
            });
        }
        $course = $filtered->first();
        return $course ? ['id' => $course->id, 'number' => $course->number, 'letter' => $course->letter] : null;
    }

    /**
     * Get courses by numbers
     */
    protected function getCoursesByNumbers(SchoolShift $shift, $numbers, ?bool $DUMMYspecialAddToNumber = false, ?bool $nextIfInactiveAndExists = true)
    {
        $courses = [];

        foreach ($numbers as $number) {
            $numberCourses = $this->courses->where('number', $number)->where('school_shift_id', $shift->id);
            foreach ($numberCourses as $course) {
                $addCourse = [
                    'id' => $course->id,
                    'number' => $course->number,
                    'letter' => $course->letter
                ];
                if ($nextIfInactiveAndExists && !$course->active) {
                    $nextCourse = $course->nextCourses()->first();
                    if ($nextCourse) {
                        $addCourse = [
                            'id' => $nextCourse->id,
                            'number' => $nextCourse->number,
                            'letter' => $nextCourse->letter
                        ];
                    }
                }
                $courses[] = $addCourse;
            }
        }
        return $courses;
    }

    /**
     * Get student start date based on course number
     */
    protected function getStudentStartDate($courseNumber)
    {
        $startDate = now()->subYears($courseNumber - 1);
        $found = $this->academicYears->where('year', $startDate->format('Y'))->first();
        if ($found) {
            return $found->start_date;
        }
        // 1 of march of startDateYear
        return $startDate->format('Y-03-01');
    }

    /**
     * Start date of the academic year for jsonForYear (for initial import: enrollment in that year).
     */
    protected function getAcademicYearStartForJsonYear()
    {
        $ay = $this->academicYears->where('year', $this->jsonForYear)->first();
        return $ay ? $ay->start_date : now()->toDateString();
    }
}
