<?php

namespace Database\Seeders;

use App\Models\Entities\Course;
use App\Models\Catalogs\SchoolLevel;
use App\Models\Entities\School;
use App\Models\Catalogs\SchoolShift;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CourseSeeder extends Seeder
{
    private $coursesCreated = 0;
    private $coursesBySchool = [];
    private $SIMPLE_LUCIO_LUCERO = false;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->SIMPLE_LUCIO_LUCERO = config('malulu.one_school_cue') === School::CUE_LUCIO_LUCERO;

        $schoolLevels = SchoolLevel::all();

        if ($schoolLevels->isEmpty()) {
            $this->command->error('No school levels found. Please run SchoolLevelSeeder first.');
            return;
        }

        // Get the specific school and two other schools
        $defaultSchool = School::where('code', School::CUE_LUCIO_LUCERO)->first();
        if (!$defaultSchool) {
            $this->command->error('School with code ' . School::CUE_LUCIO_LUCERO . ' not found. Please run SchoolSeeder first.');
            return;
        }

        // Get two other schools that are not the global school and have KINDER level
        $kinder = School::where('code', '!=', School::CUE_LUCIO_LUCERO)
            ->where('code', '!=', School::GLOBAL)
            ->whereHas('schoolLevels', function ($query) {
                $query->where('code', SchoolLevel::KINDER);
            })
            ->first();

        if (!$kinder && !$this->SIMPLE_LUCIO_LUCERO) {
            $this->command->error('No other schools found. Please run SchoolSeeder first.');
            return;
        }

        $otherSchools = School::where('code', '!=', School::CUE_LUCIO_LUCERO)
            ->where('code', '!=', School::GLOBAL)
            ->where('id', '!=', $kinder ? $kinder->id : null)
            ->inRandomOrder()
            ->take(2)
            ->get();

        if ($otherSchools->isEmpty() && !$this->SIMPLE_LUCIO_LUCERO) {
            $this->command->error('No other schools found. Please run SchoolSeeder first.');
            return;
        }

        // Check if we already have courses
        $existingCourses = Course::count();
        if ($existingCourses > 0) {
            DB::table('courses')->truncate();
        }
        // Combine the schools
        $schools = collect([$defaultSchool, $kinder])->merge($otherSchools);
        $schools  = $schools->filter();

        $coursesByLevel = [
            SchoolLevel::KINDER => [
                [
                    'grade' => 2,
                    'courses_per_shift' => 2,
                    'names' => ['ositos', 'duendes'],
                ],
                [
                    'grade' => 3,
                    'courses_per_shift' => 2,
                    'names' => ['rosa', 'verde'],
                ],
                [
                    'grade' => 4,
                    'courses_per_shift' => 2,
                    'names' => ['amarilla', 'violeta'],
                ],
            ],
            SchoolLevel::PRIMARY => [
                [
                    'grade' => 1,
                    'courses_per_shift' => 3,
                ],
                [
                    'grade' => 2,
                    'courses_per_shift' => 3,
                ],
                [
                    'grade' => 3,
                    'courses_per_shift' => 3,
                ],
                [
                    'grade' => 4,
                    'courses_per_shift' => 3,
                ],
                [
                    'grade' => 5,
                    'courses_per_shift' => 3,
                ],
                [
                    'grade' => 6,
                    'courses_per_shift' => 3,
                ]
            ],
            SchoolLevel::SECONDARY => [
                [
                    'grade' => 1,
                    'courses_per_shift' => 3,
                ],
                [
                    'grade' => 2,
                    'courses_per_shift' => 3,
                ],
                [
                    'grade' => 3,
                    'courses_per_shift' => 3,
                ],
                [
                    'grade' => 4,
                    'courses_per_shift' => 3,
                ],
                [
                    'grade' => 5,
                    'courses_per_shift' => 3,
                ],
                [
                    'grade' => 6,
                    'courses_per_shift' => 3,
                ],
            ],
        ];

        $currentYear = Carbon::now()->year;
        $startingYear = $currentYear - 3;

        for ($year = $startingYear; $year <= $currentYear; $year++) {
            $startDate = Carbon::create($year, 3, 1); // March 1st
            $endDate = Carbon::create($year, 12, 15); // December 15th

            $this->createCourses($schools, $coursesByLevel, $startDate, $endDate);
        }
    }

    private function createCourses($schools, $coursesByLevel, $startDate, $endDate)
    {
        foreach ($schools as $school) {
            $this->coursesBySchool[$school->code] = 0;

            // Get shifts for this specific school
            $schoolShifts = $school->shifts;
            if ($schoolShifts->isEmpty()) {
                $this->command->error("No shifts found for school: {$school->name}. Skipping...");
                continue;
            }

            $schoolLevels = $school->schoolLevels;
            if ($schoolLevels->isEmpty()) {
                $this->command->error("No levels found for school: {$school->name}. Skipping...");
                continue;
            }

            foreach ($schoolLevels as $level) {
                if (isset($coursesByLevel[$level->code])) {
                    foreach ($coursesByLevel[$level->code] as $courseData) {
                        $coursesPerShift = $courseData['courses_per_shift'];
                        $currentLetter = 'A';
                        $nameIndex = 0;

                        foreach ($schoolShifts as $shift) {
                            // Create courses for this shift
                            for ($i = 0; $i < $coursesPerShift; $i++) {
                                try {
                                    $prevCourseId = $this->getPreviousCourseId($school, $level, $shift, $courseData['grade'], $currentLetter, $startDate);
                                    $course = Course::create([
                                        'school_id' => $school->id,
                                        'school_level_id' => $level->id,
                                        'school_shift_id' => $shift->id,
                                        'previous_course_id' => $prevCourseId,
                                        'number' => $courseData['grade'],
                                        'letter' => $currentLetter,
                                        'name' => $courseData['names'][$nameIndex] ?? null,
                                        'start_date' => $startDate,
                                        'end_date' => $endDate,
                                        'active' => true,
                                    ]);

                                    $this->coursesCreated++;
                                    $this->coursesBySchool[$school->code]++;
                                    if ($prevCourseId) $this->deactivate($prevCourseId);

                                    // Move to next letter and name index
                                    $currentLetter++;
                                    $nameIndex++;
                                } catch (\Exception $e) {
                                    $this->command->error("Error creating course: {$e->getMessage()}");
                                    $this->command->error("Details: School: {$school->name}, Level: {$level->name}, Grade: {$courseData['grade']}, Letter: {$currentLetter}, Shift: {$shift->name}");
                                    throw $e;
                                }
                            }
                        }
                    }
                } else {
                    $this->command->error("No courses defined for level: {$level->name}");
                }
                if ($school->code === School::CUE_LUCIO_LUCERO && $level->code === SchoolLevel::PRIMARY) {
                    $this->specialPromoteCoursesForLucio($school, $level);
                }
            }
        }

        // Verify final count
        $finalCount = Course::count();
        if ($finalCount !== $this->coursesCreated) {
            $this->command->error("Warning: Expected {$this->coursesCreated} courses but found {$finalCount} in database!");
        }
    }

    private function specialPromoteCoursesForLucio($school, $level)
    {
        // Update courses of this school and level, setting number = number + 1 where number > 3
        Course::where('school_id', $school->id)
            ->where('school_level_id', $level->id)
            ->where('number', '>', 3)
            ->update([
                'number' => \DB::raw('`number` + 1')
            ]);
    }

    private function getPreviousCourseId($school, $level, $shift, $grade, $letter, $startDate): ?int
    {
        if ($grade === 1) return null;
        $prevStartDateMin = \Carbon\Carbon::parse($startDate)->subYear()->toDateString();

        $previousCourse = Course::where('school_id', $school->id)
            ->where('school_level_id', $level->id)
            ->where('school_shift_id', $shift->id)
            ->where('number', $grade - 1)
            ->where('letter', $letter)
            ->where('start_date', '>=', $prevStartDateMin)
            ->where('start_date', '<', $startDate)
            ->select('id')
            ->get()
            ->first();
        // if (!$previousCourse) {
        //     $this->command->error("No previous course found for school: {$school->name}, level: {$level->name}, shift: {$shift->name}, grade: {$grade}, letter: {$letter}, start date: {$startDate}");
        //     die();
        //     return null;
        // }
        return $previousCourse ? $previousCourse->id : null;
    }

    private function deactivate($courseId)
    {
        $course = Course::find($courseId);
        $course->active = false;
        $course->save();
    }
}
