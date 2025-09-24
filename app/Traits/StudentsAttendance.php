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
        $date = $this->checkAttendanceDate($sDate);
        $students = $this->courseService->getStudents($course, true, $date->format('Y-m-d'), true);
        $datesNavigation = $this->getDatesNavigation($date);

        return Inertia::render('Courses/AttendanceDayEdit', [
            'course' => $course,
            'school' => $school,
            'students' => $students,
            'selectedLevel' => $schoolLevel,
            'dateYMD' => $date->format('Y-m-d'),
            'daysBefore' => $datesNavigation['daysBefore'],
            'daysAfter' => $datesNavigation['daysAfter'],
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

    private function getDatesNavigation(\DateTime $date, int $totalCount = 10)
    {
        $half = ceil($totalCount / 2);
        $currentAcademicYear = AcademicYear::findByDate($date);
        if (!$currentAcademicYear) return null;
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
                || ($checkDate >= $currentAcademicYear->winter_break_start && $checkDate <= $currentAcademicYear->winter_break_end)
            ) {
                continue;
            } else {
                $before[] = $checkDate->format('Y-m-d');
            }
        }

        $checkDate = new \DateTime($date->format('Y-m-d'));
        while (count($after) < $half && $checkDate <= $currentAcademicYear->end_date) {
            $checkDate->modify('+1 day');
            if (
                $checkDate->format('N') == 6
                || $checkDate->format('N') == 7
                || ($checkDate >= $currentAcademicYear->winter_break_start && $checkDate <= $currentAcademicYear->winter_break_end)
            ) {
                continue;
            } else {
                $after[] = $checkDate->format('Y-m-d');
            }
        }
        return ['daysBefore' => $before, 'daysAfter' => $after];
    }
}
