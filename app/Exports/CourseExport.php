<?php

namespace App\Exports;

use App\Services\CourseService;
use App\Services\AttendanceService;
use App\Models\Entities\Course;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CourseExport implements WithMultipleSheets
{
    protected Course $course;
    protected array $exportOptions;
    protected CourseService $courseService;
    protected AttendanceService $attendanceService;

    public function __construct(CourseService $courseService, AttendanceService $attendanceService, Course $course, array $exportOptions)
    {
        $this->course = $course;
        $this->exportOptions = $this->checkOptions($exportOptions);
        $this->exportOptions['attendance'] = true;
        $this->courseService = $courseService;
        $this->attendanceService = $attendanceService;
    }

    /**
     * Define the sheets for the export
     */
    public function sheets(): array
    {
        $students = (($this->exportOptions['students'] ?? false) || ($this->exportOptions['attendance'] ?? false))
            ? $this->courseService->getStudents($this->course, true, null, false)
            : null;

        $sheets = [
            new CourseMainSheet($this->courseService, $this->course, $this->exportOptions, $students)
        ];

        // Add attendance sheet if attendance option is selected
        if ($this->exportOptions['attendance'] ?? false) {
            $sheets[] = new CourseAttendanceSheet($this->courseService, $this->attendanceService, $this->course, $this->exportOptions, $students);
        }

        return $sheets;
    }

    private function checkOptions(array $exportOptions): array
    {
        $exportOptions['attendance'] = $exportOptions['attendance'] ?? false;
        $exportOptions['students'] = ($exportOptions['students'] ?? false) || $exportOptions['attendance'];
        $exportOptions['basicData'] = ($exportOptions['basicData'] ?? false) || $exportOptions['attendance'];
        return $exportOptions;
    }
}
