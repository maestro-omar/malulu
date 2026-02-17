<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Entities\Course;
use App\Models\Entities\School;
use App\Models\Entities\AcademicYear;
use App\Models\Catalogs\SchoolLevel;
use App\Models\Catalogs\SchoolShift;
use Carbon\Carbon;

/**
 * Fixes Lucio Lucero primary courses to match the 8-grade structure.
 *
 * Lucio Lucero (CUE 740058000, MALULU_ONE_SCHOOL_ONLY_PRIMARY=true) has 6 physical primary years
 * represented as 8 "grades" with mid-year transitions:
 *
 * - Grade 1: Full academic year
 * - Grade 2 → 3: 2nd until first Monday Sep, then 3rd until first Monday Jun (next year)
 * - Grade 4: First Mon Jun until end of that year
 * - Grade 5 → 6: 5th from start of next year until first Monday Oct, then 6th until end
 * - Grade 7: After winter break (next year) until first day of May
 * - Grade 8: First day of May until scholarship ends
 */
class LucioCoursesFixSeeder extends Seeder
{
    /** @var array<int> IDs of courses we created or updated (to avoid deactivating them) */
    private array $touchedCourseIds = [];
    public function run(): void
    {
        if (! $this->shouldRun()) {
            return;
        }

        $otherSchoolsExist = School::where('code', '!=', School::CUE_LUCIO_LUCERO)->where('code', '!=', School::GLOBAL)->exists();
        if (Course::count() === 0 && $otherSchoolsExist) {
            $this->command->warn('No courses found. CourseSeeder may have failed. Skipping LucioCoursesFixSeeder.');
            return;
        }

        $school = School::where('code', School::CUE_LUCIO_LUCERO)->first();
        if (! $school) {
            $this->command->warn('Lucio Lucero school not found. Skipping LucioCoursesFixSeeder.');
            return;
        }

        $primaryLevel = $school->schoolLevels()->where('code', SchoolLevel::PRIMARY)->first();
        if (! $primaryLevel) {
            $this->command->warn('Lucio Lucero has no primary level. Skipping LucioCoursesFixSeeder.');
            return;
        }

        $shifts = $school->shifts;
        if ($shifts->isEmpty()) {
            $this->command->warn('Lucio Lucero has no shifts. Skipping LucioCoursesFixSeeder.');
            return;
        }

        $academicYears = AcademicYear::orderBy('year')->get()->keyBy('year');
        if ($academicYears->isEmpty()) {
            $this->command->warn('No academic years found. Run AcademicYearSeeder first.');
            return;
        }

        // Mañana: A, B, C; Tarde: D, E, F (3 courses per shift for PRIMARY)
        $years = $academicYears->keys()->toArray();
        $minYear = min($years);
        $maxYear = max($years);

        foreach ($years as $year) {
            $ay = $academicYears->get($year);
            if (! $ay) {
                continue;
            }

            foreach ($shifts as $shift) {
                $letters = $this->lettersForShift($shift);
                foreach ($letters as $letter) {
                    $this->ensureLucioGradesForCohort(
                        $school,
                        $primaryLevel,
                        $shift,
                        $letter,
                        $year,
                        $academicYears,
                        $minYear,
                        $maxYear
                    );
                }
            }
        }

        $this->deactivateObsoleteLucioCourses($school, $primaryLevel);
    }

    private function shouldRun(): bool
    {
        return config('malulu.one_school_cue') === School::CUE_LUCIO_LUCERO
            && filter_var(config('malulu.one_school_only_primary'), FILTER_VALIDATE_BOOLEAN) === true;
    }

    /** Letters per shift: Mañana A,B,C; Tarde D,E,F; others fallback to A,B,C. */
    private function lettersForShift($shift): array
    {
        $code = $shift->code ?? $shift->name ?? '';
        if ($code === SchoolShift::MORNING) {
            return ['A', 'B', 'C'];
        }
        if ($code === SchoolShift::AFTERNOON) {
            return ['D', 'E', 'F'];
        }
        return ['A', 'B', 'C'];
    }

    private function ensureLucioGradesForCohort(
        School $school,
        $primaryLevel,
        $shift,
        string $letter,
        int $cohortYear,
        $academicYears,
        int $minYear,
        int $maxYear
    ): void {
        $ay = $academicYears->get($cohortYear);
        if (! $ay) {
            return;
        }

        $ayNext1 = $academicYears->get($cohortYear + 1);
        $ayNext2 = $academicYears->get($cohortYear + 2);
        $ayNext3 = $academicYears->get($cohortYear + 3);
        $ayNext4 = $academicYears->get($cohortYear + 4);

        $startY = Carbon::parse($ay->start_date);
        $endY = Carbon::parse($ay->end_date);

        // Grade 1: full academic year (no previous - first year of primary)
        $this->upsertCourse($school, $primaryLevel, $shift, $letter, 1, $cohortYear, null, $startY, $endY);

        // Grade 2: start until first Monday September (previous = Grade 1 of cohort Y-1)
        $firstMonSep = $this->firstMondayOf($cohortYear, 9);
        $grade2End = $firstMonSep->copy()->subDay();
        $grade1Prev = $cohortYear > $minYear
            ? $this->findCourseByGradeAndYear($school->id, $primaryLevel->id, $shift->id, $letter, 1, $cohortYear - 1)
            : null;
        $this->upsertCourse($school, $primaryLevel, $shift, $letter, 2, $cohortYear, $grade1Prev?->id, $startY, $grade2End);

        // Grade 3: first Monday Sep until first Monday June (next year) (previous = Grade 2 same cohort)
        $firstMonJunNext = $cohortYear + 1 <= $maxYear ? $this->firstMondayOf($cohortYear + 1, 6) : null;
        $grade3Prev = $this->findCourse($school->id, $primaryLevel->id, $shift->id, $letter, 2, $startY, $grade2End);
        $grade3End = ($firstMonJunNext && $firstMonJunNext->lte($endY->copy()->addYear()))
            ? $firstMonJunNext->copy()->subDay()
            : $endY;
        $this->upsertCourse($school, $primaryLevel, $shift, $letter, 3, $cohortYear, $grade3Prev?->id, $firstMonSep, $grade3End);

        // Grade 4: first Monday June (next year) until end of that year
        if ($ayNext1 && $cohortYear + 1 <= $maxYear) {
            $firstMonJun = $this->firstMondayOf($cohortYear + 1, 6);
            $endY1 = Carbon::parse($ayNext1->end_date);
            $grade4Prev = $this->findCourse($school->id, $primaryLevel->id, $shift->id, $letter, 3, $firstMonSep, $grade3End);
            $grade4Prev = $grade4Prev ?? $this->findCourseByGradeAndYear($school->id, $primaryLevel->id, $shift->id, $letter, 3, $cohortYear);
            $this->upsertCourse($school, $primaryLevel, $shift, $letter, 4, $cohortYear + 1, $grade4Prev?->id, $firstMonJun, $endY1);
        }

        // Grade 5: start of AY+2 until first Monday October
        if ($ayNext2 && $cohortYear + 2 <= $maxYear) {
            $startY2 = Carbon::parse($ayNext2->start_date);
            $firstMonOct2 = $this->firstMondayOf($cohortYear + 2, 10);
            $grade5Prev = $this->findCourseByGradeAndYear($school->id, $primaryLevel->id, $shift->id, $letter, 4, $cohortYear + 1);
            $this->upsertCourse($school, $primaryLevel, $shift, $letter, 5, $cohortYear + 2, $grade5Prev?->id, $startY2, $firstMonOct2->copy()->subDay());
        }

        // Grade 6: first Monday October until end of AY+2
        if ($ayNext2 && $cohortYear + 2 <= $maxYear) {
            $firstMonOct2 = $this->firstMondayOf($cohortYear + 2, 10);
            $endY2 = Carbon::parse($ayNext2->end_date);
            $grade6Prev = $this->findCourseByGradeAndYear($school->id, $primaryLevel->id, $shift->id, $letter, 5, $cohortYear + 2);
            $this->upsertCourse($school, $primaryLevel, $shift, $letter, 6, $cohortYear + 2, $grade6Prev?->id, $firstMonOct2, $endY2);
        }

        // Grade 7: day after winter break AY+3 until May 1 (next year)
        if ($ayNext3 && $cohortYear + 3 <= $maxYear) {
            $winterEnd = Carbon::parse($ayNext3->winter_break_end)->addDay();
            $may1Next = Carbon::create($cohortYear + 4, 5, 1);
            $grade7Prev = $this->findCourseByGradeAndYear($school->id, $primaryLevel->id, $shift->id, $letter, 6, $cohortYear + 2);
            $grade7End = $may1Next->copy()->subDay();
            $this->upsertCourse($school, $primaryLevel, $shift, $letter, 7, $cohortYear + 3, $grade7Prev?->id, $winterEnd, $grade7End);
        }

        // Grade 8: May 1 until end of AY+4
        if ($ayNext4 && $cohortYear + 4 <= $maxYear) {
            $may1 = Carbon::create($cohortYear + 4, 5, 1);
            $endY4 = Carbon::parse($ayNext4->end_date);
            $grade8Prev = $this->findCourseByGradeAndYear($school->id, $primaryLevel->id, $shift->id, $letter, 7, $cohortYear + 3);
            $this->upsertCourse($school, $primaryLevel, $shift, $letter, 8, $cohortYear + 4, $grade8Prev?->id, $may1, $endY4);
        }
    }

    private function firstMondayOf(int $year, int $month): Carbon
    {
        $date = Carbon::create($year, $month, 1);
        if ($date->dayOfWeek !== Carbon::MONDAY) {
            $date->next(Carbon::MONDAY);
        }
        return $date;
    }

    private function findCourse(int $schoolId, int $levelId, int $shiftId, string $letter, int $grade, ?Carbon $startDate, ?Carbon $endDate): ?Course
    {
        $q = Course::where('school_id', $schoolId)
            ->where('school_level_id', $levelId)
            ->where('school_shift_id', $shiftId)
            ->where('letter', $letter)
            ->where('number', $grade);

        if ($startDate) {
            $q->where('start_date', $startDate->toDateString());
        }
        if ($endDate) {
            $q->where('end_date', $endDate->toDateString());
        }

        return $q->first();
    }

    private function findCourseByGradeAndYear(int $schoolId, int $levelId, int $shiftId, string $letter, int $grade, int $year): ?Course
    {
        $ay = AcademicYear::findByYear($year);
        if (! $ay) {
            return null;
        }
        return Course::where('school_id', $schoolId)
            ->where('school_level_id', $levelId)
            ->where('school_shift_id', $shiftId)
            ->where('letter', $letter)
            ->where('number', $grade)
            ->where('start_date', '>=', $ay->start_date)
            ->where('start_date', '<=', $ay->end_date)
            ->first();
    }

    /**
     * Find existing Lucio course to repurpose (CourseSeeder creates one per grade/shift/letter/year).
     * Matches by school, level, shift, letter, number and start_date year.
     */
    private function findExistingLucioCourse(int $schoolId, int $levelId, int $shiftId, string $letter, int $grade, int $startYear): ?Course
    {
        return Course::where('school_id', $schoolId)
            ->where('school_level_id', $levelId)
            ->where('school_shift_id', $shiftId)
            ->where('letter', $letter)
            ->where('number', $grade)
            ->whereYear('start_date', $startYear)
            ->first();
    }

    /**
     * Update existing course or create new one. Uses canonicalStartYear to find CourseSeeder-created
     * courses to repurpose (e.g. Grade 3 was created with start Mar 1, we update it to Sep-Jun).
     */
    private function upsertCourse(
        School $school,
        $primaryLevel,
        $shift,
        string $letter,
        int $grade,
        int $canonicalStartYear,
        ?int $previousCourseId,
        Carbon $startDate,
        Carbon $endDate
    ): void {
        // Try exact match first, then by canonical year (to repurpose CourseSeeder courses)
        $existing = Course::where('school_id', $school->id)
            ->where('school_level_id', $primaryLevel->id)
            ->where('school_shift_id', $shift->id)
            ->where('letter', $letter)
            ->where('number', $grade)
            ->where(function ($q) use ($startDate, $canonicalStartYear) {
                $q->where('start_date', $startDate->toDateString())
                    ->orWhereYear('start_date', $canonicalStartYear);
            })
            ->first();

        if ($previousCourseId) {
            Course::where('id', $previousCourseId)->update([
                'active' => false,
            ]);
        }

        if ($existing) {
            $existing->update([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'previous_course_id' => $previousCourseId,
                'active' => true,
            ]);
            $this->touchedCourseIds[] = $existing->id;
            return;
        }

        $course = Course::create([
            'school_id' => $school->id,
            'school_level_id' => $primaryLevel->id,
            'school_shift_id' => $shift->id,
            'previous_course_id' => $previousCourseId,
            'number' => $grade,
            'letter' => $letter,
            'name' => null,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'active' => true,
        ]);
        $this->touchedCourseIds[] = $course->id;
    }

    private function deactivateObsoleteLucioCourses(School $school, $primaryLevel): void
    {
        // Deactivate Lucio primary courses that we didn't touch - these are obsolete CourseSeeder
        // leftovers that couldn't be repurposed (e.g. duplicate grade entries from edge cases).
        $touchedIds = array_unique($this->touchedCourseIds);
        if (empty($touchedIds)) {
            return;
        }

        Course::where('school_id', $school->id)
            ->where('school_level_id', $primaryLevel->id)
            ->whereNotIn('id', $touchedIds)
            ->delete();
    }
}
