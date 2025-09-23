<?php

namespace App\Exports;

use App\Services\CourseService;
use App\Services\AttendanceService;
use App\Models\Entities\Course;
use App\Models\Catalogs\AttendanceStatus;
use App\Traits\ExportsTrait;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class CourseAttendanceSheet implements FromArray, WithEvents, WithColumnWidths, WithStyles, WithTitle
{
    use ExportsTrait;
    // Color constants for styling
    const COLOR_HEADER_LABELS = '004473';      // Blue for header section labels
    const COLOR_TABLE_HEADER_TEXT = 'FFFFFF';  // White text for table headers
    const COLOR_ATTENDANCE_HEADERS = '004473'; // Blue for attendance table header backgrounds

    protected Course $course;
    protected array $exportOptions;
    protected CourseService $courseService;
    protected AttendanceService $attendanceService;
    protected array $students;

    public function __construct(CourseService $courseService, AttendanceService $attendanceService, Course $course, array $exportOptions, array $students)
    {
        $this->course = $course;
        $this->exportOptions = $exportOptions;
        $this->courseService = $courseService;
        $this->attendanceService = $attendanceService;
        $this->students = $students;
    }

    public function array(): array
    {
        $exportData = [];

        // Attendance section header
        $currentRow = 1;
        $exportData[] = ['ASISTENCIA DIARIA', ''];
        $currentRow++;
        $exportData[] = ['', '']; // Empty row
        $currentRow++;

        $statuses = AttendanceStatus::all();

        $studentsIds = array_map(function ($student) {
            return $student['id'];
        }, $this->students);
        $response = $this->attendanceService->getStudentsAttendanceInAcademicYear($studentsIds, null);

        $attendance = $response['attendances'];
        $datesWithoutAttendance = $response['dates_without_attendance'];
        $minDate = Carbon::create($response['min_date']);
        $maxDate = Carbon::create($response['max_date']);

        $headerRow = $this->buildHeader($minDate, $maxDate);
        $exportData[] = $headerRow;
        $currentRow++;

        // Students data rows
        foreach ($this->students as $student) {
            $row = [
                // ['Nombre', 'Apellido', 'Género', 'Fecha de nacimiento', 'Lugar de nacimiento', 'Nacionalidad', 'DNI', 'Domicilio', 'Teléfono'];
                $student['firstname'],
                $student['lastname'],
                $student['gender'],
                $this->formatBirthdate($student['birthdate']),
                $student['birth_place'],
                $student['nationality'],
                $student['id_number'],
                $student['address'],
                $student['phone'],
            ];

            $currentRow++;
        }
        return $exportData;
    }

    private function buildHeader($minDate, $maxDate)
    {
        $cells = ['Nombre', 'Apellido', 'Género', 'Fecha de nacimiento', 'Lugar de nacimiento', 'Nacionalidad', 'DNI', 'Domicilio', 'Teléfono'];
        for ($date = $minDate; $date <= $maxDate; $date->modify('+1 day')) {
            $cells[] = $date->format('d/m');
        }
        return $cells;
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
