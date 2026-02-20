<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Entities\Course;
use App\Models\Entities\School;
use App\Models\Entities\AcademicYear;
use App\Models\Entities\User;
use App\Models\Catalogs\SchoolLevel;
use App\Models\Catalogs\SchoolShift;
use App\Services\StudentService;
use App\Services\TeacherService;
use App\Services\CourseService;
use Carbon\Carbon;

/**
 * Promotes/graduates students from 2025 courses to next courses.
 * Only processes courses whose start_date falls within the previous academic year (2025),
 * so 2026 courses with incorrect end_date are never included.
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

        $previousAyStart = $previousAy->start_date->format('Y-m-d');
        $previousAyEnd = $previousAy->end_date->format('Y-m-d');
        $firstDayOfTargetAy = $targetAy->start_date->format('Y-m-d');
        $today = Carbon::today()->toDateString();

        // Promote from any course that has ended by the end of the previous academic year and started
        // before the target year (so we don't pick the target-year courses as "from" courses).
        // This includes Lucio courses that span two calendar years (e.g. 3A Sep 2024–Jun 2025,
        // 6A Oct 2024–Jul 2025) so students in 3A/6A get promoted to 4A/7A.
        $courses = Course::where('school_id', $school->id)
            ->where('school_level_id', $primaryLevel->id)
            ->whereNotNull('end_date')
            ->where('end_date', '<=', $previousAyEnd)
            ->where('start_date', '<', $firstDayOfTargetAy)
            ->orderBy('number')
            ->orderBy('letter')
            ->get();

        $studentService = app(StudentService::class);
        $creatorId = \App\Models\Entities\User::first()?->id;

        $totalPromoted = 0;
        $totalGraduated = 0;
        $totalSkipped = 0;
        $totalStays = 0;
        $allErrors = [];

        foreach ($courses as $course) {
            $activeCount = $course->courseStudents()->whereNull('end_date')->whereNull('deleted_at')->count();
            if ($activeCount === 0) {
                continue;
            }

            $result = $studentService->promoteOrGraduateCourse($course, $creatorId);

            $totalPromoted += $result['promoted'];
            $totalGraduated += $result['graduated'];
            $totalStays += $result['stays'] ?? 0;
            $totalSkipped += $result['skipped'];
            $allErrors = array_merge($allErrors, $result['errors']);

            if ($result['promoted'] > 0 || $result['graduated'] > 0 || ($result['stays'] ?? 0) > 0) {
                $this->command->info(sprintf(
                    'Course %s: %d promoted, %d graduated, %d stays, %d skipped',
                    $course->nice_name,
                    $result['promoted'],
                    $result['graduated'],
                    $result['stays'] ?? 0,
                    $result['skipped']
                ));
            }
        }

        $this->command->info(sprintf(
            'UpdateDataTo2026Seeder: Total %d promoted, %d graduated, %d stays, %d skipped',
            $totalPromoted,
            $totalGraduated,
            $totalStays,
            $totalSkipped
        ));

        if (! empty($allErrors)) {
            foreach ($allErrors as $err) {
                $this->command->error($err);
            }
        }

        $this->updateCourseActiveStatus($school->id, $primaryLevel->id, $firstDayOfTargetAy, $previousAyEnd, $today);

        $this->enrollTeachersTo2026Courses($school, $primaryLevel, $targetAy, $firstDayOfTargetAy);
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

    /**
     * Enroll teachers to 2026 assigned courses from ce-8_initial_staff.json Agrupamiento2026
     * as "in charge" teacher. Matches staff by email and course by number+letter+shift.
     */
    private function enrollTeachersTo2026Courses(School $school, SchoolLevel $primaryLevel, AcademicYear $targetAy, string $firstDayOfTargetAy): void
    {
        $jsonPath = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'ce-8_initial_staff.json';
        if (! file_exists($jsonPath)) {
            $this->command->warn('ce-8_initial_staff.json not found. Skipping teacher 2026 enrollment.');
            return;
        }

        $jsonContent = file_get_contents($jsonPath);
        $staffData = $jsonContent !== false ? json_decode($jsonContent, true) : null;
        if (! is_array($staffData)) {
            $this->command->warn('Invalid or empty ce-8_initial_staff.json. Skipping teacher 2026 enrollment.');
            return;
        }

        $ayStart = $targetAy->start_date->format('Y-m-d');
        $ayEnd = $targetAy->end_date->format('Y-m-d');
        $courses2026 = Course::where('school_id', $school->id)
            ->where('school_level_id', $primaryLevel->id)
            ->where('start_date', '<=', $ayEnd)
            ->where(function ($q) use ($ayStart) {
                $q->where('end_date', '>=', $ayStart)->orWhereNull('end_date');
            })
            ->get();

        $shiftMorning = SchoolShift::where('code', SchoolShift::MORNING)->first();
        $shiftAfternoon = SchoolShift::where('code', SchoolShift::AFTERNOON)->first();
        $teacherService = app(TeacherService::class);
        $courseService = app(CourseService::class);
        $creatorId = User::first()?->id;

        $enrolled = 0;
        $skipped = 0;
        $errors = [];

        foreach ($staffData as $row) {
            $agrupamiento2026 = trim((string) ($row['Agrupamiento2026'] ?? ''));
            if ($agrupamiento2026 === '') {
                continue;
            }

            if (! preg_match('/^(\d+)\s*([A-Za-z])$/i', $agrupamiento2026, $m)) {
                $errors[] = "Invalid Agrupamiento2026: \"{$agrupamiento2026}\" for " . ($row['Email'] ?? 'unknown');
                continue;
            }

            $number = (int) $m[1];
            $letter = strtoupper($m[2]);
            $turno = strtolower(trim((string) ($row['Turno'] ?? 'mañana')));
            $shift = str_contains($turno, 'tarde') ? $shiftAfternoon : $shiftMorning;
            if (! $shift) {
                $errors[] = 'School shifts not found. Skipping teacher 2026 enrollment.';
                break;
            }

            $course = $courses2026->where('number', $number)->where('letter', $letter)->where('school_shift_id', $shift->id)->first();
            if (! $course) {
                $errors[] = "2026 course {$number}{$letter} (shift: {$shift->code}) not found for " . ($row['Email'] ?? 'unknown');
                continue;
            }

            $email = strtolower(trim((string) ($row['Email'] ?? '')));
            if ($email === '') {
                $errors[] = "Staff with Agrupamiento2026 {$agrupamiento2026} has no email.";
                continue;
            }

            $user = User::where('email', $email)->first();
            if (! $user) {
                $errors[] = "User not found for email: {$email} (Agrupamiento2026: {$agrupamiento2026}).";
                $skipped++;
                continue;
            }

            $roleRelationshipId = $teacherService->getRoleRelationshipIdForTeacherInSchool($user->id, $school->id);
            if (! $roleRelationshipId) {
                $errors[] = "No active worker role in school for: {$email} (Agrupamiento2026: {$agrupamiento2026}).";
                $skipped++;
                continue;
            }

            try {
                $courseService->assignCourseToTeacher($roleRelationshipId, $course->id, [
                    'start_date' => $firstDayOfTargetAy,
                    'in_charge' => true,
                    'created_by' => $creatorId,
                ]);
                $enrolled++;
                $this->command->info("Enrolled {$email} as in-charge teacher for {$course->nice_name} (2026).");
            } catch (\Throwable $e) {
                $errors[] = "Enroll {$email} → {$course->nice_name}: " . $e->getMessage();
            }
        }

        $this->command->info(sprintf(
            'UpdateDataTo2026Seeder: Teachers enrolled to 2026 courses: %d (skipped: %d).',
            $enrolled,
            $skipped
        ));
        foreach ($errors as $err) {
            $this->command->error($err);
        }
    }

    private function shouldRun(): bool
    {
        return config('malulu.one_school_cue') === School::CUE_LUCIO_LUCERO
            && filter_var(config('malulu.one_school_only_primary'), FILTER_VALIDATE_BOOLEAN) === true;
    }
}
