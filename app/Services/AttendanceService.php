<?php
//TODO make it by school (so should search course_id is null or course is related with given school)
namespace App\Services;

use App\Models\Entities\Course;
use App\Models\Entities\User;
use App\Models\Relations\Attendance;
use App\Models\Catalogs\AttendanceStatus;
use App\Models\Entities\AcademicYear;
use Carbon\Carbon;

class AttendanceService
{
    public function __construct()
    {
        // No dependencies needed
    }

    public function getStudentAttendance(User $student, string $attendanceDate)
    {
        $date = new \DateTime($attendanceDate);
        // $academicYear = AcademicYear::findByDate($date);
        // $from = $academicYear->start_date;
        // $to = $academicYear->end_date;
        // $summary = $this->getStudentAttendanceSummary($student, $from, $to);
        $ret = $this->getStudentAttendanceForDate($student, $date);
        return $ret;
    }

    public function getStudentAttendanceInPeriod(User $student, \DateTime $from, \DateTime $to)
    {
        $attendances = Attendance::with('status')
            ->where('user_id', $student->id)
            ->whereBetween('date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->orderBy('date')
            ->get();

        return $attendances;
    }

    public function getStudentsAttendanceInAcademicYear(array $studentsIds, ?AcademicYear $academicYear)
    {
        if (empty($academicYear)) {
            $academicYear = AcademicYear::getCurrent();
        }
        return $this->getStudentsAttendanceInPeriod($studentsIds, $academicYear->start_date, $academicYear->end_date);
    }

    public function getStudentsAttendanceInPeriod(array $studentsIds, \DateTime $from, \DateTime $to)
    {
        $attendances = Attendance::select('user_id', 'date', 'status_id', 'file_id', 'course_id')
            ->with('file')
            ->whereIn('user_id', $studentsIds)
            ->whereBetween('date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->get();

        $minDate = $attendances->min('date');
        $maxDate = $attendances->max('date');
        $datesWithoutAttendance = $this->getDatesWithoutAttendance($attendances);
        //Group by student
        $attendances = $attendances->groupBy('user_id');
        return ['attendances' => $attendances, 'min_date' => $minDate, 'max_date' => $maxDate, 'dates_without_attendance' => $datesWithoutAttendance];
    }

    private function getDatesWithoutAttendance(\Illuminate\Database\Eloquent\Collection $attendances)
    {
        $minDate = Carbon::create($attendances->min('date'));
        $maxDate = Carbon::create($attendances->max('date'));
        // dd($minDate, $maxDate,$attendances->min('date'));
        $attendances = $attendances->sortBy('date');
        $allDates = $attendances->pluck('date')->unique()->sort()->toArray();
        $datesWithoutAttendance = [];
        for ($date = $minDate; $date <= $maxDate; $date->modify('+1 day')) {
            if (!in_array($date->format('Y-m-d'), $allDates)) {
                $datesWithoutAttendance[] = $date->format('Y-m-d');
            }
        }
        return $datesWithoutAttendance;
    }


    /*
    Return Structure
    [
    'first_date' => '2024-01-15',           // First attendance date found
    'last_date' => '2024-03-20',            // Last attendance date found
    'status_counts' => [                    // Count for each status type
        'presente' => 25,
        'tarde' => 3,
        'ausente_injustificado' => 2
    ],
    'total_presents' => 28,                 // Total present records (is_absent = false)
    'total_absences' => 2,                  // Total absent records (is_absent = true)
    'total_records' => 30                   // Total attendance records
]
    */
    public function getStudentAttendanceSummary(User $student, \DateTime $from, \DateTime $to)
    {
        // Get all attendance records for the student within the date range
        $attendances = Attendance::with('status')
            ->where('user_id', $student->id)
            ->whereBetween('date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->orderBy('date')
            ->get();

        // If no attendance records found, return empty summary
        if ($attendances->isEmpty()) {
            return [
                'first_date' => null,
                'last_date' => null,
                'status_counts' => [],
                'total_presents' => 0,
                'total_absences' => 0,
                'total_records' => 0
            ];
        }

        // Get first and last dates
        $firstDate = $attendances->first()->date;
        $lastDate = $attendances->last()->date;

        // Count by status
        $statusCounts = [];
        $totalPresents = 0;
        $totalAbsences = 0;

        foreach ($attendances as $attendance) {
            $statusCode = $attendance->status->code;

            // Initialize status count if not exists
            if (!isset($statusCounts[$statusCode])) {
                $statusCounts[$statusCode] = 0;
            }

            $statusCounts[$statusCode]++;

            // Count presents and absences
            if ($attendance->status->is_absent) {
                $totalAbsences++;
            } else {
                $totalPresents++;
            }
        }

        return [
            'first_date' => $firstDate->format('Y-m-d'),
            'last_date' => $lastDate->format('Y-m-d'),
            'status_counts' => $statusCounts,
            'total_presents' => $totalPresents,
            'total_absences' => $totalAbsences,
            'total_records' => $attendances->count()
        ];
    }

    public function getStudentAttendanceForDate(User $student, \DateTime $date): ?Attendance
    {
        $attendance = Attendance::where('user_id', $student->id)
            ->where('date', $date->format('Y-m-d'))
            ->first();
        return $attendance;
    }

    public function getStudentsAttendanceForDate(array $studentsIds, \DateTime $date)
    {
        $attendance = Attendance::whereIn('user_id', $studentsIds)
            ->where('date', $date->format('Y-m-d'));
        return $attendance;
    }

    /**
     * Get minimal attendance summary for multiple students
     * Returns total presents and absences for each student in the given period
     * 
     * @param array $studentsIds Array of student user IDs
     * @param \DateTime $from Start date
     * @param \DateTime $to End date
     * @return array Array with student_id as key and attendance counts
     */
    public function getStudentsAttendanceMinimal(array $studentsIds, ?\DateTime $from, ?\DateTime $to)
    {
        if (empty($from) || empty($to)) {
            $date = new \DateTime();
            $academicYear = AcademicYear::findByDate($date);
            $from = $academicYear->start_date;
            $to = $academicYear->end_date;
        }

        // Get aggregated attendance data grouped by user and absence status
        $query = Attendance::join('attendance_statuses', 'attendance.status_id', '=', 'attendance_statuses.id')
            ->whereIn('attendance.user_id', $studentsIds)
            ->whereBetween('attendance.date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->selectRaw('
                attendance.user_id,
                attendance_statuses.is_absent,
                COUNT(*) as count
            ')
            ->groupBy('attendance.user_id', 'attendance_statuses.is_absent');
        $attendanceData = $query->get();

        // $sql = vsprintf(
        //     str_replace('?', "'%s'", $query->toSql()),
        //     $query->getBindings()
        // );

        // echo $sql;
        // dd($attendanceData, $from->format('Y-m-d'), $to->format('Y-m-d'), $studentsIds);

        // Initialize result array for all students
        $result = [];
        foreach ($studentsIds as $studentId) {
            $result[$studentId] = [
                'total_presents' => 0,
                'total_absences' => 0,
                'total_records' => 0
            ];
        }

        // Process the aggregated data
        foreach ($attendanceData as $data) {
            $studentId = $data->user_id;
            $count = $data->count;

            if ($data->is_absent) {
                $result[$studentId]['total_absences'] = $count;
            } else {
                $result[$studentId]['total_presents'] = $count;
            }

            $result[$studentId]['total_records'] += $count;
        }

        return $result;
    }

    /**
     * Update or create attendance record for a single student
     * 
     * @param int $studentId Student user ID
     * @param int $courseId Course ID
     * @param string $date Attendance date (Y-m-d format)
     * @param string|null $statusCode Attendance status code (null to remove attendance)
     * @param int $createdBy User ID who created/updated the record
     * @return Attendance|null The attendance record or null if removed
     */
    public function updateStudentAttendance(int $studentId, int $courseId, string $date, ?string $statusCode, int $createdBy): ?Attendance
    {
        // Find existing attendance record
        $attendance = Attendance::where('user_id', $studentId)
            ->where('course_id', $courseId)
            ->where('date', $date)
            ->first();

        // If status is null, remove the attendance record
        if ($statusCode === null) {
            if ($attendance) {
                $attendance->delete();
            }
            return null;
        }

        // Get the status ID
        $status = AttendanceStatus::where('code', $statusCode)->first();
        if (!$status) {
            throw new \Exception("Invalid attendance status: {$statusCode}");
        }

        // Create or update attendance record
        if ($attendance) {
            // Update existing record
            $attendance->update([
                'status_id' => $status->id,
                'updated_by' => $createdBy,
            ]);
        } else {
            // Create new record
            $attendance = Attendance::create([
                'user_id' => $studentId,
                'course_id' => $courseId,
                'date' => $date,
                'status_id' => $status->id,
                'created_by' => $createdBy,
                'updated_by' => $createdBy,
            ]);
        }

        return $attendance->fresh(['status']);
    }

    /**
     * Update attendance for multiple students (bulk operation)
     * 
     * @param array $studentIds Array of student user IDs
     * @param int $courseId Course ID
     * @param string $date Attendance date (Y-m-d format)
     * @param string $statusCode Attendance status code
     * @param int $createdBy User ID who created/updated the record
     * @return array Array of created/updated attendance records
     */
    public function updateBulkAttendance(array $studentIds, int $courseId, string $date, string $statusCode, int $createdBy): array
    {
        $results = [];

        foreach ($studentIds as $studentId) {
            $attendance = $this->updateStudentAttendance($studentId, $courseId, $date, $statusCode, $createdBy);
            if ($attendance) {
                $results[] = $attendance;
            }
        }

        return $results;
    }

    /**
     * Get current attendance status for students on a specific date
     * 
     * @param array $studentIds Array of student user IDs
     * @param int $courseId Course ID
     * @param string $date Attendance date (Y-m-d format)
     * @return array Array with student_id as key and status_code as value
     */
    public function getStudentsAttendanceStatusForDate(array $studentIds, int $courseId, string $date): array
    {
        $attendances = Attendance::with('status')
            ->whereIn('user_id', $studentIds)
            ->where('course_id', $courseId)
            ->where('date', $date)
            ->get();

        $result = [];
        foreach ($attendances as $attendance) {
            $result[$attendance->user_id] = $attendance->status->code;
        }

        return $result;
    }

    /**
     * Get students attendance for current date and N days before
     * Returns array grouped by user with N days of status codes
     * 
     * @param array $studentIds Array of student user IDs
     * @param int $courseId Course ID
     * @param string $currentDate Current date in Y-m-d format
     * @param int $days Number of days to retrieve (default: 5)
     * @return array Array with user_id as key and array of N status codes as value
     */
    public function getStudentsAttendanceLastNDays(array $studentIds, int $courseId, string $currentDate, int $days = 5): array
    {
        // Calculate the N days before current date
        $dates = [];
        $current = new \DateTime($currentDate);

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = clone $current;
            $date->modify("-{$i} days");
            $dates[] = $date->format('Y-m-d');
        }

        // Get all attendances for the N days
        $attendances = Attendance::with('status')
            ->whereIn('user_id', $studentIds)
            ->where('course_id', $courseId)
            ->whereIn('date', $dates)
            ->get();

        // Group by user_id and date
        $grouped = [];
        foreach ($attendances as $attendance) {
            $grouped[$attendance->user_id][$attendance->date] = $attendance->status->code;
        }

        // Build result array with N days for each user
        $result = [];
        foreach ($studentIds as $studentId) {
            $result[$studentId] = [];
            foreach ($dates as $date) {
                $result[$studentId][$date] = $grouped[$studentId][$date] ?? null;
            }
        }

        return $result;
    }
}
