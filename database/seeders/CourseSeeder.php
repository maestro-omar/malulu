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

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schoolLevels = SchoolLevel::all();

        if ($schoolLevels->isEmpty()) {
            $this->command->error('No school levels found. Please run SchoolLevelSeeder first.');
            return;
        }

        // Get the specific school and two other schools
        $defaultSchool = School::where('code', '740058000')->first();
        if (!$defaultSchool) {
            $this->command->error('School with code 740058000 not found. Please run SchoolSeeder first.');
            return;
        }

        // Get two other schools that are not the global school
        $otherSchools = School::where('code', '!=', '740058000')
            ->where('code', '!=', School::GLOBAL)
            ->inRandomOrder()
            ->take(2)
            ->get();

        if ($otherSchools->isEmpty()) {
            $this->command->error('No other schools found. Please run SchoolSeeder first.');
            return;
        }

        // Check if we already have courses
        $existingCourses = Course::count();
        if ($existingCourses > 0) {
            DB::table('courses')->truncate();
        }
        // Combine the schools
        $schools = collect([$defaultSchool])->merge($otherSchools);

        $coursesByLevel = [
            SchoolLevel::KINDER => [
                [
                    'grade' => 2,
                    'letters' => ['A', 'B'],
                    'names' => ['ositos', 'duendes'],
                ],
                [
                    'grade' => 3,
                    'letters' => ['A', 'B'],
                    'names' => ['rosa', 'verde'],
                ],
                [
                    'grade' => 4,
                    'letters' => ['A', 'B'],
                    'names' => ['amarilla', 'violeta'],
                ],
            ],
            SchoolLevel::PRIMARY => [
                [
                    'grade' => 1,
                    'letters' => ['A', 'B', 'C'],
                ],
                [
                    'grade' => 2,
                    'letters' => ['A', 'B', 'C'],
                ],
                [
                    'grade' => 3,
                    'letters' => ['A', 'B', 'C'],
                ],
                [
                    'grade' => 4,
                    'letters' => ['A', 'B', 'C'],
                ],
                [
                    'grade' => 5,
                    'letters' => ['A', 'B', 'C'],
                ],
                [
                    'grade' => 6,
                    'letters' => ['A', 'B', 'C'],
                ]
            ],
            SchoolLevel::SECONDARY => [
                [
                    'grade' => 1,
                    'letters' => ['A', 'B', 'C'],
                ],
                [
                    'grade' => 2,
                    'letters' => ['A', 'B', 'C'],
                ],
                [
                    'grade' => 3,
                    'letters' => ['A', 'B', 'C'],
                ],
                [
                    'grade' => 4,
                    'letters' => ['A', 'B', 'C'],
                ],
                [
                    'grade' => 5,
                    'letters' => ['A', 'B', 'C'],
                ],
                [
                    'grade' => 6,
                    'letters' => ['A', 'B', 'C'],
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
                        foreach ($courseData['letters'] as $cIdx => $letter) {
                            foreach ($schoolShifts as $shift) {
                                try {
                                    $prevCourseId = $this->getPreviousCourseId($school, $level, $shift, $courseData['grade'], $letter, $startDate);
                                    $course = Course::create([
                                        'school_id' => $school->id,
                                        'school_level_id' => $level->id,
                                        'school_shift_id' => $shift->id,
                                        'previous_course_id' => $prevCourseId,
                                        'number' => $courseData['grade'],
                                        'letter' => $letter,
                                        'name' => $courseData['names'][$cIdx] ?? null,
                                        'start_date' => $startDate,
                                        'end_date' => $endDate,
                                        'active' => true,
                                    ]);

                                    $this->coursesCreated++;
                                    $this->coursesBySchool[$school->code]++;
                                    if ($prevCourseId) $this->deactivate($prevCourseId);
                                } catch (\Exception $e) {
                                    $this->command->error("Error creating course: {$e->getMessage()}");
                                    $this->command->error("Details: School: {$school->name}, Level: {$level->name}, Grade: {$courseData['grade']}, Letter: {$letter}, Shift: {$shift->name}");
                                    throw $e;
                                }
                            }
                        }
                    }
                } else {
                    $this->command->error("No courses defined for level: {$level->name}");
                }
            }
        }

        // Verify final count
        $finalCount = Course::count();
        if ($finalCount !== $this->coursesCreated) {
            $this->command->error("Warning: Expected {$this->coursesCreated} courses but found {$finalCount} in database!");
        }
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
