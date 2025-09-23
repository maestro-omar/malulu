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
        $academicYear = AcademicYear::findByDate($date);
        $from = $academicYear->start_date;
        $to = $academicYear->end_date;
        $summary = $this->getStudentAttendanceSummary($student, $from, $to);
        $forDate = $this->getStudentAttendanceForDate($student, $date);
        return ['summary' => $summary, 'forDate' => $forDate];
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
}
