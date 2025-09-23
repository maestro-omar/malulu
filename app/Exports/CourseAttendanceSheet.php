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
use Carbon\CarbonImmutable;
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

        $statuses = AttendanceStatus::all();

        $studentsIds = array_map(function ($student) {
            return $student['id'];
        }, $this->students);
        $response = $this->attendanceService->getStudentsAttendanceInAcademicYear($studentsIds, null);
        $attendance = $response['attendances'];
        $datesWithoutAttendance = $response['dates_without_attendance'];
        $minDate = CarbonImmutable::create($response['min_date']);
        $maxDate = CarbonImmutable::create($response['max_date']);

        $headerRow = $this->buildHeader($minDate, $maxDate);
        $exportData[] = $headerRow;
        $currentRow++;

        // Students data rows
        foreach ($this->students as $student) {
            $studentAttendance = $attendance[$student['id']] ?? [];
            if (!empty($studentAttendance)) $studentAttendance = $studentAttendance->pluck('status_id', 'date')->toArray();
            $row = [
                // ['Nombre', 'Apellido', 'Género', 'Fecha de nacimiento', 'Lugar de nacimiento', 'Nacionalidad', 'DNI', 'Domicilio', 'Teléfono'];
                $student['firstname'],
                $student['lastname'],
                $student['gender'],
                $this->formatBirthdate($student['birthdate']),
                $student['birth_place'],
                $student['nationality'],
                $student['id_number'],
                $student['address'] ?? '',
                $student['phone'] ?? '',
            ];
            for ($date = Carbon::create($minDate); $date <= $maxDate; $date->modify('+1 day')) {
                $ymd = $date->format('Y-m-d');
                $statusId = $studentAttendance[$ymd] ?? null;
                $status = $statusId ? $statuses->where('id', $statusId)->first()->symbol : '';
                $row[] = $status;
            }
            $exportData[] = $row;
            $currentRow++;
        }

        // Add symbol meaning rows
        $exportData[] = ['', '']; // Empty row
        $currentRow++;

        foreach ($statuses as $status) {
            $exportData[] = [$status->symbol, $status->name];
            $currentRow++;
        }

        return $exportData;
    }

    private function buildHeader($minDate, $maxDate)
    {
        $cells = ['Nombre', 'Apellido', 'Género', 'Fecha de nacimiento', 'Lugar de nacimiento', 'Nacionalidad', 'DNI', 'Domicilio', 'Teléfono'];
        for ($date = Carbon::create($minDate); $date <= $maxDate; $date->modify('+1 day')) {
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
            'A' => 20,  // Nombre
            'B' => 20,  // Apellido
            'C' => 10,  // Género
            'D' => 18,  // Fecha de nacimiento
            'E' => 20,  // Lugar de nacimiento
            'F' => 15,  // Nacionalidad
            'G' => 15,  // DNI
            'H' => 30,  // Domicilio
            'I' => 15,  // Teléfono
            // Attendance date columns will be auto-sized based on content
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
                $columnCount = $sheet->getHighestColumn();

                // Find the actual data header row (after symbol meaning rows)
                $dataStartRow = 1;
                $symbolSectionStart = 0;
                for ($row = 1; $row <= $rowCount; $row++) {
                    $cellValue = $sheet->getCell("A{$row}")->getValue();
                    if ($cellValue === 'SÍMBOLOS DE ASISTENCIA') {
                        $symbolSectionStart = $row;
                    }
                    if ($cellValue === 'Nombre') {
                        $dataStartRow = $row;
                        break;
                    }
                }

                // Style the symbol meaning section
                if ($symbolSectionStart > 0) {
                    // Style the "SÍMBOLOS DE ASISTENCIA" header
                    $sheet->getStyle("A{$symbolSectionStart}")->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 12,
                            'color' => ['rgb' => self::COLOR_ATTENDANCE_HEADERS],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                        ],
                    ]);

                    // Style the symbol rows (symbol and name)
                    for ($row = $symbolSectionStart + 1; $row < $dataStartRow; $row++) {
                        $cellValue = $sheet->getCell("A{$row}")->getValue();
                        if (!empty($cellValue)) {
                            $sheet->getStyle("A{$row}:B{$row}")->applyFromArray([
                                'font' => [
                                    'bold' => true,
                                ],
                                'alignment' => [
                                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                                ],
                            ]);
                        }
                    }
                }

                // Style the data header row
                if ($dataStartRow > 0 && $rowCount > 0) {
                    $lastColumn = $sheet->getHighestColumn();

                    // Style the data header row with attendance colors
                    $sheet->getStyle("A{$dataStartRow}:{$lastColumn}{$dataStartRow}")->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => self::COLOR_TABLE_HEADER_TEXT],
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => self::COLOR_ATTENDANCE_HEADERS],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);

                    // Style all data rows (starting after the header)
                    for ($row = $dataStartRow + 1; $row <= $rowCount; $row++) {
                        $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => 'CCCCCC'],
                                ],
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_LEFT,
                                'vertical' => Alignment::VERTICAL_CENTER,
                            ],
                        ]);

                        // Center attendance data columns (from column J onwards)
                        $attendanceStartColumn = 'J'; // After the 9 student info columns
                        if ($lastColumn >= $attendanceStartColumn) {
                            $sheet->getStyle("{$attendanceStartColumn}{$row}:{$lastColumn}{$row}")->applyFromArray([
                                'alignment' => [
                                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                                    'vertical' => Alignment::VERTICAL_CENTER,
                                ],
                            ]);
                        }
                    }

                    // Freeze the header row
                    $sheet->freezePane('A2');

                    // Set attendance date columns to smaller width
                    $attendanceStartColumn = 'J';
                    for ($col = $attendanceStartColumn; $col <= $lastColumn; $col++) {
                        $sheet->getColumnDimension($col)->setWidth(8);
                    }
                }

                // Auto-fit columns for student info columns only
                foreach (range('A', 'I') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(false);
                }
            },
        ];
    }
}
