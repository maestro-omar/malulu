<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Catalogs\AttendanceStatus;
use App\Models\Entities\User;
use App\Models\Entities\AcademicYear;
use App\Models\Relations\Attendance;
use App\Models\Relations\StudentCourse;
use App\Models\Catalogs\Role;
use Carbon\Carbon;

class FakeAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1) Get all attendance statuses
        $attendanceStatuses = AttendanceStatus::all();
        if ($attendanceStatuses->isEmpty()) {
            $this->command->warn('No attendance statuses found. Please run AttendanceStatusesTableSeeder first.');
            return;
        }

        // 2) Get all students
        $studentRoleId = Role::where('code', Role::STUDENT)->first()?->id;
        if (!$studentRoleId) {
            $this->command->warn('Student role not found. Please run RoleAndPermissionSeeder first.');
            return;
        }

        $students = User::whereHas('roleRelationships', function ($query) use ($studentRoleId) {
            $query->where('role_id', $studentRoleId)
                  ->whereNull('end_date')
                  ->whereNull('end_reason_id');
        })->whereDoesntHave('attendances')->get();

        if ($students->isEmpty()) {
            $this->command->warn('No active students found, whitout attendance records.');
            return;
        }

        // 3) Get current academic year
        $academicYear = AcademicYear::getCurrent();
        if (!$academicYear) {
            $this->command->warn('No current academic year found.');
            return;
        }

        $this->command->info("Generating attendance for {$students->count()} students from {$academicYear->start_date->format('Y-m-d')} to " . now()->format('Y-m-d'));

        $attendanceRecords = [];
        $processedCount = 0;

        foreach ($students as $student) {
            // 4) Get current course for each student through role relationship
            $studentRoleRelationship = $student->roleRelationships()
                ->where('role_id', $studentRoleId)
                ->whereNull('end_date')
                ->whereNull('end_reason_id')
                ->first();

            if (!$studentRoleRelationship) {
                $this->command->warn("No active role relationship found for student {$student->name} (ID: {$student->id})");
                continue;
            }

            $currentCourse = StudentCourse::where('role_relationship_id', $studentRoleRelationship->id)
                ->whereNull('end_date')
                ->whereNull('deleted_by')
                ->with('course')
                ->first();

            if (!$currentCourse || !$currentCourse->course) {
                $this->command->warn("No active course found for student {$student->name} (ID: {$student->id})");
                continue;
            }

            $course = $currentCourse->course;
            $this->command->info("Processing student {$student->name} in course {$course->nice_name}");

            // 5) Generate attendance for each weekday from academic year start to today
            $currentDate = Carbon::parse($academicYear->start_date);
            $today = Carbon::now();

            while ($currentDate->lte($today)) {
                // Skip weekends (Saturday = 6, Sunday = 0)
                if ($currentDate->dayOfWeek !== Carbon::SATURDAY && $currentDate->dayOfWeek !== Carbon::SUNDAY) {
                    // Skip winter break
                    if (!$academicYear->isWinterBreak($currentDate->toDateTime())) {
                        // Check if attendance record already exists
                        $existingAttendance = Attendance::where('user_id', $student->id)
                            ->where('date', $currentDate->format('Y-m-d'))
                            ->exists();

                        if (!$existingAttendance) {
                            // Generate realistic attendance status
                            $status = $this->generateAttendanceStatus($attendanceStatuses, $currentDate);
                            
                            $attendanceRecords[] = [
                                'user_id' => $student->id,
                                'date' => $currentDate->format('Y-m-d'),
                                'status_id' => $status->id,
                                'course_id' => $course->id,
                                'created_by' => 1, // Assuming user ID 1 exists (admin)
                                'updated_by' => null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];

                            $processedCount++;
                        }
                    }
                }

                $currentDate->addDay();
            }
        }

        // Insert all attendance records in batches
        if (!empty($attendanceRecords)) {
            $this->command->info("Inserting {$processedCount} attendance records...");
            
            // Insert in chunks to avoid memory issues
            $chunks = array_chunk($attendanceRecords, 1000);
            foreach ($chunks as $chunk) {
                Attendance::insert($chunk);
            }
            
            $this->command->info("Successfully created {$processedCount} attendance records!");
        } else {
            $this->command->info("No new attendance records to create.");
        }
    }

    /**
     * Generate realistic attendance status based on date and probability
     */
    private function generateAttendanceStatus($attendanceStatuses, Carbon $date): AttendanceStatus
    {
        // Get statuses by type
        $presentStatuses = $attendanceStatuses->where('is_absent', false);
        $absentStatuses = $attendanceStatuses->where('is_absent', true);

        // Realistic probabilities
        $presentProbability = 0.85; // 85% present
        $lateProbability = 0.10;    // 10% late
        // $absentProbability = 0.05;   5% absent

        $random = mt_rand(1, 100);

        if ($random <= $presentProbability * 100) {
            // Present - choose between "presente" and "presente_sin_clases"
            $presentOptions = $presentStatuses->where('code', '!=', 'tarde');
            return $presentOptions->random();
        } elseif ($random <= ($presentProbability + $lateProbability) * 100) {
            // Late
            $lateStatus = $presentStatuses->where('code', 'tarde')->first();
            return $lateStatus ?: $presentStatuses->random();
        } else {
            // Absent - choose between justified and unjustified
            $absentOptions = $absentStatuses->where('code', '!=', 'ausente_sin_clases');
            return $absentOptions->random();
        }
    }
}
