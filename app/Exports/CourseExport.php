<?php

namespace App\Exports;

use App\Services\CourseService;
use App\Services\AttendanceService;
use App\Models\Entities\Course;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CourseExport implements FromArray, WithEvents, WithColumnWidths, WithStyles, WithMultipleSheets
{
    protected Course $course;
    protected array $exportOptions;
    protected CourseService $courseService;
    protected AttendanceService $attendanceService;

    public function __construct(CourseService $courseService, AttendanceService $attendanceService, Course $course, array $exportOptions)
    {
        $this->course = $course;
        $this->exportOptions = $exportOptions;
        $this->exportOptions['attendance'] = true;
        $this->courseService = $courseService;
        $this->attendanceService = $attendanceService;
    }

    public function array(): array
    {
        // This method is no longer used since we're using multiple sheets
        return [];
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

    /**
     * Set column widths for better formatting
     */
    public function columnWidths(): array
    {
        return [
            'A' => 25,  // Labels and first column (Nombre)
            'B' => 25,  // Lastname column (Apellido)
            'C' => 10,  // Gender column
            'D' => 40,  // Email column
            'E' => 18,  // Birthdate column
            'F' => 15,  // ID Number column
            'G' => 20,  // Role column (teachers only)
            'H' => 20,  // Subject column (teachers only)
            'I' => 10,  // In charge column (teachers only)
        ];
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Header row styles will be applied in registerEvents
        ];
    }

    public function registerEvents(): array
    {
        return [
            // Events are handled by individual sheet classes
        ];
    }
}
