<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Entities\Course;
use App\Models\Entities\School;
use App\Models\Entities\AcademicYear;
use App\Models\Catalogs\SchoolLevel;
use App\Services\StudentService;
use Carbon\Carbon;

/**
 * Promotes/graduates students from 2025 courses to next courses.
 * Updates course active status: activate new AY courses and next courses in progress; deactivate grade 8 (graduated).
 */
class UpdateDataTo2026Seeder extends Seeder
{
    private const PREVIOUS_YEAR = 2025;
    private const TARGET_YEAR = 2026;

    public function run(): void
    {
        if (! $this->shouldRun()) {
            return;
        }

        $school = School::where('code', School::CUE_LUCIO_LUCERO)->first();
        if (! $school) {
            $this->command->warn('Lucio Lucero school not found. Skipping UpdateDataTo2026Seeder.');
            return;
        }

        $primaryLevel = $school->schoolLevels()->where('code', SchoolLevel::PRIMARY)->first();
        if (! $primaryLevel) {
            $this->command->warn('Lucio Lucero has no primary level. Skipping UpdateDataTo2026Seeder.');
            return;
        }

        $previousAy = AcademicYear::findByYear(self::PREVIOUS_YEAR);
        if (! $previousAy) {
            $this->command->warn('Academic year ' . self::PREVIOUS_YEAR . ' not found. Skipping UpdateDataTo2026Seeder.');
            return;
        }

        $targetAy = AcademicYear::findByYear(self::TARGET_YEAR);
        if (! $targetAy) {
            $this->command->warn('Academic year ' . self::TARGET_YEAR . ' not found. Skipping UpdateDataTo2026Seeder.');
            return;
        }

        $previousAyEnd = $previousAy->end_date->format('Y-m-d');
        $firstDayOfTargetAy = $targetAy->start_date->format('Y-m-d');
        $today = Carbon::today()->toDateString();

        $courses = Course::where('school_id', $school->id)
            ->where('school_level_id', $primaryLevel->id)
            ->whereNotNull('end_date')
            ->where('end_date', '<=', $previousAyEnd)
            ->orderBy('number')
            ->orderBy('letter')
            ->get();

        $studentService = app(StudentService::class);
        $creatorId = \App\Models\Entities\User::first()?->id;

        $totalPromoted = 0;
        $totalGraduated = 0;
        $totalSkipped = 0;
        $allErrors = [];

        foreach ($courses as $course) {
            $activeCount = $course->courseStudents()->whereNull('end_date')->whereNull('deleted_at')->count();
            if ($activeCount === 0) {
                continue;
            }

            $result = $studentService->promoteOrGraduateCourse($course, $creatorId);

            $totalPromoted += $result['promoted'];
            $totalGraduated += $result['graduated'];
            $totalSkipped += $result['skipped'];
            $allErrors = array_merge($allErrors, $result['errors']);

            if ($result['promoted'] > 0 || $result['graduated'] > 0) {
                $this->command->info(sprintf(
                    'Course %s: %d promoted, %d graduated, %d skipped',
                    $course->nice_name,
                    $result['promoted'],
                    $result['graduated'],
                    $result['skipped']
                ));
            }
        }

        $this->command->info(sprintf(
            'UpdateDataTo2026Seeder: Total %d promoted, %d graduated, %d skipped',
            $totalPromoted,
            $totalGraduated,
            $totalSkipped
        ));

        if (! empty($allErrors)) {
            foreach ($allErrors as $err) {
                $this->command->error($err);
            }
        }

        $this->updateCourseActiveStatus($school->id, $primaryLevel->id, $firstDayOfTargetAy, $previousAyEnd, $today);
    }

    /**
     * Update course active status:
     * - Deactivate grade 8 courses from previous year (students graduated).
     * - Activate courses that start on first day of target academic year.
     * - Activate next courses that are in progress (start_date passed, end_date in future).
     */
    private function updateCourseActiveStatus(
        int $schoolId,
        int $primaryLevelId,
        string $firstDayOfTargetAy,
        string $previousAyEnd,
        string $today
    ): void {
        $baseQuery = fn () => Course::where('school_id', $schoolId)->where('school_level_id', $primaryLevelId);

        // Deactivate grade 8 courses from previous year (students graduated)
        $deactivated = $baseQuery()
            ->where('number', 8)
            ->whereNotNull('end_date')
            ->where('end_date', '<=', $previousAyEnd)
            ->update(['active' => false]);
        if ($deactivated > 0) {
            $this->command->info("Deactivated {$deactivated} grade 8 courses from previous year (graduated).");
        }

        // Activate courses that start on first day of target academic year
        $activatedFirstDay = $baseQuery()
            ->where('start_date', $firstDayOfTargetAy)
            ->update(['active' => true]);
        if ($activatedFirstDay > 0) {
            $this->command->info("Activated {$activatedFirstDay} courses starting on first day of AY ({$firstDayOfTargetAy}).");
        }

        // Activate next courses that are in progress (start_date passed, end_date in future)
        $inProgress = $baseQuery()
            ->whereNotNull('previous_course_id')
            ->where('start_date', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->where('end_date', '>=', $today)->orWhereNull('end_date');
            })
            ->update(['active' => true]);
        if ($inProgress > 0) {
            $this->command->info("Activated {$inProgress} next courses in progress.");
        }
    }

    private function shouldRun(): bool
    {
        return config('malulu.one_school_cue') === School::CUE_LUCIO_LUCERO
            && filter_var(config('malulu.one_school_only_primary'), FILTER_VALIDATE_BOOLEAN) === true;
    }
}
