<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\SchoolLevel;
use App\Models\School;
use App\Models\SchoolShift;
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
        // Check if we already have courses
        $existingCourses = Course::count();
        if ($existingCourses > 0) {
            DB::table('courses')->truncate();
        }

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

        // Combine the schools
        $schools = collect([$defaultSchool])->merge($otherSchools);

        $coursesByLevel = [
            SchoolLevel::KINDER => [
                [
                    'grade' => 1,
                    'letters' => ['A', 'B'],
                ],
                [
                    'grade' => 2,
                    'letters' => ['A', 'B'],
                ],
                [
                    'grade' => 3,
                    'letters' => ['A', 'B'],
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
                ],
                [
                    'grade' => 7,
                    'letters' => ['A', 'B', 'C'],
                ],
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
        $startDate = Carbon::create($currentYear, 3, 1); // March 1st
        $endDate = Carbon::create($currentYear, 12, 15); // December 15th

        foreach ($schools as $school) {
            $this->coursesBySchool[$school->code] = 0;

            // Get shifts for this specific school
            $schoolShifts = $school->shifts;
            if ($schoolShifts->isEmpty()) {
                $this->command->error("No shifts found for school: {$school->name}. Skipping...");
                continue;
            }
            
            foreach ($schoolLevels as $level) {
                if (isset($coursesByLevel[$level->code])) {
                    $previousCourse = null;

                    foreach ($coursesByLevel[$level->code] as $courseData) {
                        foreach ($courseData['letters'] as $letter) {
                            foreach ($schoolShifts as $shift) {
                                try {
                                    $course = Course::create([
                                        'school_id' => $school->id,
                                        'school_level_id' => $level->id,
                                        'school_shift_id' => $shift->id,
                                        'previous_course_id' => $previousCourse ? $previousCourse->id : null,
                                        'number' => $courseData['grade'],
                                        'letter' => $letter,
                                        'start_date' => $startDate,
                                        'end_date' => $endDate,
                                        'active' => true,
                                    ]);

                                    $this->coursesCreated++;
                                    $this->coursesBySchool[$school->code]++;

                                    // Store the first course of each grade as previous course for the next grade
                                    if ($letter === 'A') {
                                        $previousCourse = $course;
                                    }
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
}
