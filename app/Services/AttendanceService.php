<?php

namespace App\Services;

use App\Models\Entities\Course;
use App\Models\Entities\User;
use App\Models\Relations\Attendance;
use App\Models\Catalog\AttendanceStatus;
use App\Models\Entities\AcademicYear;
use App\Services\UserService;

class AttendanceService
{
    protected $userservice;

    public function __construct(UserService $userservice)
    {
        $this->userservice = $userservice;
    }


    public function getUserAttendance(User $user, string $attendanceDate)
    {
        $date = new \DateTime($attendanceDate);
        $academicYear = AcademicYear::findByDate($date);
        $from = $academicYear->start_date;
        $to = $academicYear->end_date;
        $summary = $this->getUserAttendanceSummary($user, $from, $to);
        $forDate = $this->getUserAttendanceForDate($user, $date);
        return ['summary' => $summary, 'forDate' => $forDate];
    }

    public function getUserAttendanceSummary(User $user, \DateTime $from, \DateTime $to) {}
    public function getUserAttendanceForDate(User $user, \DateTime $date) {}
}
