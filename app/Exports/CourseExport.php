<?php

namespace App\Exports;

use App\Services\CourseService;
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
    // Color constants for styling
    const COLOR_HEADER_LABELS = '004473';      // Blue for header section labels (Escuela, Nivel, etc.)
    const COLOR_TABLE_HEADER_TEXT = 'FFFFFF';  // White text for table headers

    // Separate colors for teachers and students tables
    const COLOR_TEACHERS_HEADERS = 'F5B54E';   // Orange for teachers table header backgrounds
    const COLOR_STUDENTS_HEADERS = '059669';   // Green for students table header backgrounds
    const COLOR_SCHEDULE_HEADERS = '7C3AED';   // Purple for schedule table header backgrounds
    const COLOR_ATTENDANCE_HEADERS = '004473'; // Blue for attendance table header backgrounds

    protected Course $course;
    protected array $exportOptions;
    protected CourseService $courseService;

    public function __construct(CourseService $courseService, Course $course, array $exportOptions)
    {
        $this->course = $course;
        $this->exportOptions = $exportOptions;
        $this->exportOptions['attendance'] = true;
        $this->courseService = $courseService;
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
        $sheets = [
            new CourseMainSheet($this->courseService, $this->course, $this->exportOptions)
        ];

        // Add attendance sheet if attendance option is selected
        if ($this->exportOptions['attendance'] ?? false) {
            $sheets[] = new CourseAttendanceSheet($this->courseService, $this->course, $this->exportOptions);
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
            'B' => 10,  // Gender column
            'C' => 40,  // Email column
            'D' => 18,  // Birthdate column
            'E' => 15,  // ID Number column
            'F' => 20,  // Role column (teachers only)
            'G' => 20,  // Subject column (teachers only)
            'H' => 10,  // In charge column (teachers only)
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
