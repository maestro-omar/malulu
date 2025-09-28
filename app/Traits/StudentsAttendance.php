<?php

namespace App\Traits;

use App\Http\Controllers\School\SchoolBaseController;
use App\Models\Entities\Course;
use App\Models\Entities\AcademicYear;
use App\Services\CourseService;
use App\Services\SchoolService;
use App\Services\SchoolLevelService;
use App\Services\SchoolShiftService;
use App\Services\FileService;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;
use App\Models\Entities\School;
use App\Models\Entities\User;
use App\Models\Catalogs\SchoolLevel;
use Diglactic\Breadcrumbs\Breadcrumbs;

use Illuminate\Support\Facades\Log;

trait StudentsAttendance
{
    protected $attendanceService;
    protected $courseService;

    public function __construct(AttendanceService $attendanceService, CourseService $courseService)
    {
        $this->attendanceService = $attendanceService;
        $this->courseService = $courseService;
    }

    public function attendanceDayEdit(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel)
    {
        $course = $this->getCourseFromUrlParameter($courseIdAndLabel);
        $sDate = $request->input('fecha', '');
        $invalidDateMsg = '';
        try {
            $date = $this->checkAttendanceDate($sDate);
        } catch (\Exception $e) {
            // $invalidDateMsg = $e->getMessage();
            $invalidDateMsg = 'La fecha para tomar asistencia no es válida';
            $date = null;
        }

        $students = $invalidDateMsg ? null : $this->courseService->getStudents($course, true, $date->format('Y-m-d'), true);
        $studentIds = $invalidDateMsg ? null : array_column($students, 'id');

        $datesNavigation = $invalidDateMsg ? null : $this->getDatesNavigation($date, 8, false);
        // Get current attendance status for all students on this date
        $currentAttendanceStatuses = $invalidDateMsg ? null :
            $this->attendanceService->getStudentsAttendanceLastNDays(
                $studentIds,
                $course->id,
                $date->format('Y-m-d'),
                5
            );

        if (!$invalidDateMsg)
            // Add current attendance status to each student
            foreach ($students as &$student) {
                $student['currentAttendanceStatus'] = $currentAttendanceStatuses[$student['id']] ?? null;
            }

        return Inertia::render('Courses/AttendanceDayEdit', [
            'course' => $course,
            'school' => $school,
            'selectedLevel' => $schoolLevel,
            'students' => $students,
            'dateYMD' => $date ? $date->format('Y-m-d') : '',
            'daysBefore' => $invalidDateMsg ? null : $datesNavigation['daysBefore'],
            'daysAfter' => $invalidDateMsg ? null : $datesNavigation['daysAfter'],
            'invalidDateMsg' => $invalidDateMsg,
            'breadcrumbs' => Breadcrumbs::generate('school.course.attendanceDayEdit', $school, $schoolLevel, $course, $date),
        ]);
    }

    private function checkAttendanceDate(string $sDate)
    {
        $today = new \DateTime();
        if (!$sDate) return $today;

        $date = new \DateTime($sDate);
        if ($date <= $today)
            return $date;
        else
            throw new \Exception('La fecha de asistencia no puede ser mayor a la fecha actual');
    }

    private function getDatesNavigation(\DateTime $date, int $totalCount = 10, bool $futureDates = false)
    {
        $today = new \DateTime();
        if (!$futureDates && $date > $today) return null;
        $currentAcademicYear = AcademicYear::findByDate($date);
        if (!$currentAcademicYear) return null;

        $half = ceil($totalCount / 2);
        $currentAcademicYear->winter_break_start;
        $currentAcademicYear->winter_break_end;
        $after = [];
        $before = [];
        $checkDate = new \DateTime($date->format('Y-m-d'));
        while (count($before) < $half && $checkDate >= $currentAcademicYear->start_date) {
            $checkDate->modify('-1 day');
            if (
                $checkDate->format('N') == 6
                || $checkDate->format('N') == 7
                || ($checkDate >= $currentAcademicYear->winter_break_start
                    && $checkDate <= $currentAcademicYear->winter_break_end)
            ) {
                continue;
            } else {
                $before[] = $checkDate->format('Y-m-d');
            }
        }
        $before = array_reverse($before);

        $checkDate = new \DateTime($date->format('Y-m-d'));
        while (count($after) < $half && $checkDate <= $currentAcademicYear->end_date && ($futureDates || $checkDate <= $today)) {
            $checkDate->modify('+1 day');
            if (
                (!$futureDates && $checkDate > $today)
                || $checkDate->format('N') == 6
                || $checkDate->format('N') == 7
                || ($checkDate >= $currentAcademicYear->winter_break_start
                    && $checkDate <= $currentAcademicYear->winter_break_end)
            ) {
                continue;
            } else {
                $after[] = $checkDate->format('Y-m-d');
            }
        }
        return ['daysBefore' => $before, 'daysAfter' => $after];
    }

    /**
     * Update attendance for a single student
     * 
     * @param Request $request
     * @param School $school
     * @param SchoolLevel $schoolLevel
     * @param string $courseIdAndLabel
     * @return \Illuminate\Http\JsonResponse
     */
    public function attendanceDayUpdate(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel)
    {
        try {
            $course = $this->getCourseFromUrlParameter($courseIdAndLabel);
            $user = auth()->user();

            // Check if this is a bulk update (multiple students) or individual update
            if ($request->has('student_ids')) {
                // Bulk update for multiple students
                $request->validate([
                    'student_ids' => 'required|array|min:1',
                    'student_ids.*' => 'integer|exists:users,id',
                    'status' => 'nullable|string|exists:attendance_statuses,code',
                    'date' => 'required|date_format:Y-m-d',
                ]);

                $attendances = [];
                foreach ($request->student_ids as $studentId) {
                    $attendance = $this->attendanceService->updateStudentAttendance(
                        $studentId,
                        $course->id,
                        $request->date,
                        $request->status,
                        $user->id
                    );
                    $attendances[] = $attendance;
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Asistencias actualizadas correctamente',
                    'attendances' => $attendances,
                ]);
            } else {
                // Individual update for single student
                $request->validate([
                    'student_id' => 'required|integer|exists:users,id',
                    'status' => 'nullable|string|exists:attendance_statuses,code',
                    'date' => 'required|date_format:Y-m-d',
                ]);

                $attendance = $this->attendanceService->updateStudentAttendance(
                    $request->student_id,
                    $course->id,
                    $request->date,
                    $request->status,
                    $user->id
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Asistencia actualizada correctamente',
                    'attendance' => $attendance,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la asistencia: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update attendance for multiple students (bulk operation)
     * 
     * @param Request $request
     * @param School $school
     * @param SchoolLevel $schoolLevel
     * @param string $courseIdAndLabel
     * @return \Illuminate\Http\JsonResponse
     */
    public function attendanceBulkUpdate(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel)
    {
        try {
            // Validate request
            $request->validate([
                'student_ids' => 'required|array|min:1',
                'student_ids.*' => 'integer|exists:users,id',
                'status' => 'required|string|exists:attendance_statuses,code',
                'date' => 'required|date_format:Y-m-d',
            ]);

            $course = $this->getCourseFromUrlParameter($courseIdAndLabel);
            $user = auth()->user();

            // Update bulk attendance using the service
            $attendances = $this->attendanceService->updateBulkAttendance(
                $request->student_ids,
                $course->id,
                $request->date,
                $request->status,
                $user->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Asistencias actualizadas correctamente',
                'count' => count($attendances),
                'attendances' => $attendances,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar las asistencias: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current attendance status for students on a specific date
     * 
     * @param Request $request
     * @param School $school
     * @param SchoolLevel $schoolLevel
     * @param string $courseIdAndLabel
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAttendanceStatus(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel)
    {
        try {
            // Validate request
            $request->validate([
                'student_ids' => 'required|array|min:1',
                'student_ids.*' => 'integer|exists:users,id',
                'date' => 'required|date_format:Y-m-d',
            ]);

            $course = $this->getCourseFromUrlParameter($courseIdAndLabel);

            // Get current attendance status
            $statuses = $this->attendanceService->getStudentsAttendanceStatusForDate(
                $request->student_ids,
                $course->id,
                $request->date
            );

            return response()->json([
                'success' => true,
                'statuses' => $statuses,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el estado de asistencia: ' . $e->getMessage(),
            ], 500);
        }
    }
}
