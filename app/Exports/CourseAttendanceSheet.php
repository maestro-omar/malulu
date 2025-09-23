<?php

namespace App\Exports;

use App\Services\CourseService;
use App\Models\Entities\Course;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CourseAttendanceSheet implements FromArray, WithEvents, WithColumnWidths, WithStyles, WithTitle
{
    // Color constants for styling
    const COLOR_HEADER_LABELS = '004473';      // Blue for header section labels
    const COLOR_TABLE_HEADER_TEXT = 'FFFFFF';  // White text for table headers
    const COLOR_ATTENDANCE_HEADERS = '004473'; // Blue for attendance table header backgrounds

    protected Course $course;
    protected array $exportOptions;
    protected CourseService $courseService;

    public function __construct(CourseService $courseService, Course $course, array $exportOptions)
    {
        $this->course = $course;
        $this->exportOptions = $exportOptions;
        $this->courseService = $courseService;
    }

    public function array(): array
    {
        $exportData = [];

        // Attendance section header
        $exportData[] = ['ASISTENCIA DIARIA', ''];
        $exportData[] = ['', '']; // Empty row

        // TODO: Implement daily attendance data export
        // This will be implemented based on your attendance data structure
        $exportData[] = ['Nota:', 'La exportación de asistencia diaria aún no está implementada'];
        $exportData[] = ['', 'Esta funcionalidad se implementará según la estructura de datos de asistencia'];

        return $exportData;
    }

    /**
     * Get the sheet title
     */
    public function title(): string
    {
        return 'Asistencia';
    }

    /**
     * Set column widths for better formatting
     */
    public function columnWidths(): array
    {
        return [
            'A' => 25,  // Labels and first column
            'B' => 40,  // Second column
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
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $rowCount = $sheet->getHighestRow();

                // Find and style section headers
                for ($row = 1; $row <= $rowCount; $row++) {
                    $cellValue = $sheet->getCell("A{$row}")->getValue();

                    // Style section header (ASISTENCIA DIARIA)
                    if ($cellValue === 'ASISTENCIA DIARIA') {
                        $sheet->getStyle("A{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 14,
                                'color' => ['rgb' => self::COLOR_ATTENDANCE_HEADERS],
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_LEFT,
                            ],
                        ]);
                    }
                }

                // Auto-fit columns
                foreach (range('A', $sheet->getHighestColumn()) as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(false);
                }
            },
        ];
    }
}
