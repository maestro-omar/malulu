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

class CourseMainSheet implements FromArray, WithEvents, WithColumnWidths, WithStyles, WithTitle
{
    // Color constants for styling
    const COLOR_HEADER_LABELS = '004473';      // Blue for header section labels (Escuela, Nivel, etc.)
    const COLOR_TABLE_HEADER_TEXT = 'FFFFFF';  // White text for table headers

    // Separate colors for teachers and students tables
    const COLOR_TEACHERS_HEADERS = 'F5B54E';   // Orange for teachers table header backgrounds
    const COLOR_STUDENTS_HEADERS = '059669';   // Green for students table header backgrounds
    const COLOR_SCHEDULE_HEADERS = '7C3AED';   // Purple for schedule table header backgrounds

    protected Course $course;
    protected array $exportOptions;
    protected CourseService $courseService;

    protected array $teachersHeaderRow;
    protected array $studentsHeaderRow;

    public function __construct(CourseService $courseService, Course $course, array $exportOptions)
    {
        $this->course = $course;
        $this->exportOptions = $exportOptions;
        $this->courseService = $courseService;
    }

    public function array(): array
    {
        // Load necessary relationships
        $this->course->load(['school', 'schoolLevel', 'schoolShift']);

        $exportData = [];
        $currentRow = 1;

        // Basic course data header section
        if ($this->exportOptions['basicData'] ?? false) {
            $exportData[] = ['Escuela:', $this->course->school->name];
            $exportData[] = ['Nivel:', $this->course->schoolLevel->name];
            $exportData[] = ['Turno:', $this->course->schoolShift->name];
            $exportData[] = ['Curso:', $this->course->nice_name];
            $currentRow += 4;
        }

        // Schedule data
        if ($this->exportOptions['schedule'] ?? false) {
            // Schedule section header
            $exportData[] = ['HORARIOS', ''];
            $currentRow++;

            $courseSchedule = $this->course->schedule;

            if ($courseSchedule && isset($courseSchedule['schedule'])) {
                // Generate day headers based on the days array (1 = Monday)
                $dayNames = [1 => 'LU', 2 => 'MA', 3 => 'MI', 4 => 'JU', 5 => 'VI', 6 => 'SA', 7 => 'DO'];
                $dayHeaders = ['', '']; // Start with empty cells for period and time columns

                if (isset($courseSchedule['days'])) {
                    foreach ($courseSchedule['days'] as $dayNumber) {
                        $dayHeaders[] = $dayNames[$dayNumber] ?? '';
                    }
                } else {
                    // Fallback to default weekdays if days array is not provided
                    $dayHeaders = ['', '', 'LU', 'MA', 'MI', 'JU', 'VI'];
                }

                // Add header row with day names
                $exportData[] = $dayHeaders;
                $currentRow++;

                // Generate schedule rows based on the schedule data
                foreach ($courseSchedule['schedule'] as $periodKey => $timeRange) {
                    $periodLabel = $periodKey;

                    // Handle special period labels
                    if (strpos($periodKey, 'break') !== false) {
                        $periodLabel = '(recreo)';
                    } elseif (strpos($periodKey, 'lunch') !== false) {
                        $periodLabel = '(almuerzo)';
                    }

                    // Create time range string
                    $timeString = $timeRange[0] . ' - ' . $timeRange[1];

                    // Create schedule row with empty cells for each day
                    $scheduleRow = [$periodLabel, $timeString];

                    // Add empty cells for each day in the schedule
                    $dayCount = isset($courseSchedule['days']) ? count($courseSchedule['days']) : 5;
                    for ($i = 0; $i < $dayCount; $i++) {
                        $scheduleRow[] = '';
                    }

                    $exportData[] = $scheduleRow;
                    $currentRow++;
                }
            }
        }

        // Teachers data
        if ($this->exportOptions['teachers'] ?? false) {
            $teachers = $this->courseService->getTeachers($this->course);

            // Teachers section header
            $exportData[] = [''];
            $currentRow++;
            $exportData[] = ['DOCENTES', ''];
            $currentRow++;

            // Teachers header
            $this->teachersHeaderRow = ['Nombre', 'Género', 'Email', 'Fecha Nacimiento', 'DNI', 'Rol', 'Materia', 'A Cargo'];
            $exportData[] = $this->teachersHeaderRow;
            $currentRow++;

            // Teachers data rows
            foreach ($teachers as $teacher) {
                $exportData[] = [
                    $teacher['name'],
                    $teacher['gender'] ?? 'N/A',
                    $teacher['email'],
                    $this->formatBirthdate($teacher['birthdate']),
                    $teacher['id_number'],
                    $teacher['rel_role']->name,
                    $teacher['rel_in_charge'] ?: 'N/A',
                    $teacher['rel_in_charge'] ? 'Sí' : 'No',
                ];
                $currentRow++;
            }
        }

        // Students data
        if ($this->exportOptions['students'] ?? false) {
            $students = $this->courseService->getStudents($this->course, true, null, false);

            // Students section header
            $exportData[] = [''];
            $currentRow++;
            $exportData[] = ['ALUMNOS/AS', ''];
            $currentRow++;

            // Students header (only 6 columns to match the data)
            $this->studentsHeaderRow = ['Nombre', 'Género', 'Email', 'Fecha Nacimiento', 'DNI'];
            $exportData[] = $this->studentsHeaderRow;
            $currentRow++;

            // Students data rows
            foreach ($students as $student) {
                $exportData[] = [
                    $student['name'],
                    $student['gender'],
                    $student['email'],
                    $this->formatBirthdate($student['birthdate']),
                    $student['id_number'],
                ];
                $currentRow++;
            }
        }

        return $exportData;
    }

    /**
     * Get the sheet title
     */
    public function title(): string
    {
        return 'Datos';
    }

    /**
     * Format birthdate from Y-m-d to d/m/Y format
     *
     * @param string|null $birthdate
     * @return string|null
     */
    private function formatBirthdate(?string $birthdate): ?string
    {
        if (empty($birthdate)) {
            return null;
        }

        try {
            $date = \DateTime::createFromFormat('Y-m-d', $birthdate);
            if ($date === false) {
                // If the format doesn't match, return the original value
                return $birthdate;
            }
            return $date->format('d/m/Y');
        } catch (\Exception $e) {
            // If any error occurs, return the original value
            return $birthdate;
        }
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
        // Style the header section (first 4 rows)
        $sheet->getStyle('A1:B4')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);

        // Style column A (labels) in header section
        $sheet->getStyle('A1:A4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => self::COLOR_HEADER_LABELS],
            ],
        ]);

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

                // Find and style section headers and table headers
                $currentSection = null;
                for ($row = 1; $row <= $rowCount; $row++) {
                    $cellValue = $sheet->getCell("A{$row}")->getValue();

                    // Track current section
                    if ($cellValue === 'DOCENTES') {
                        $currentSection = 'teachers';
                    } elseif ($cellValue === 'ESTUDIANTES') {
                        $currentSection = 'students';
                    } elseif ($cellValue === 'HORARIOS') {
                        $currentSection = 'schedule';
                    }

                    // Determine the appropriate header color based on current section
                    $headerColor = self::COLOR_TEACHERS_HEADERS; // Default to teachers
                    if ($currentSection === 'students') {
                        $headerColor = self::COLOR_STUDENTS_HEADERS;
                    } elseif ($currentSection === 'schedule') {
                        $headerColor = self::COLOR_SCHEDULE_HEADERS;
                    }

                    // Style section headers (DOCENTES, ESTUDIANTES, HORARIOS)
                    if (in_array($cellValue, ['DOCENTES', 'ESTUDIANTES', 'HORARIOS'])) {
                        $sheet->getStyle("A{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 14,
                                'color' => ['rgb' => $headerColor],
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_LEFT,
                            ],
                        ]);
                    }

                    // Check if this is a schedule table header row (starts with empty A, B cells and has day headers)
                    $cellB = $sheet->getCell("B{$row}")->getValue();
                    $cellC = $sheet->getCell("C{$row}")->getValue();
                    if ($currentSection === 'schedule' && empty($cellValue) && empty($cellB) && $cellC === 'LU') {
                        // This is the schedule header row with day names
                        // Calculate the last column dynamically based on the number of days
                        $courseSchedule = $this->course->schedule;
                        $dayCount = isset($courseSchedule['days']) ? count($courseSchedule['days']) : 5;
                        $lastColumn = chr(67 + $dayCount - 1); // Start from C (67), add day count - 1

                        // Style the header row
                        $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => self::COLOR_TABLE_HEADER_TEXT],
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $headerColor],
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                ],
                            ],
                        ]);

                        // Add borders to all schedule data cells
                        $nextRow = $row + 1;
                        while (
                            $nextRow <= $rowCount &&
                            ($sheet->getCell("A{$nextRow}")->getValue() !== '' || $sheet->getCell("B{$nextRow}")->getValue() !== '') &&
                            !in_array($sheet->getCell("A{$nextRow}")->getValue(), ['DOCENTES', 'ESTUDIANTES', 'HORARIOS'])
                        ) {
                            $sheet->getStyle("A{$nextRow}:{$lastColumn}{$nextRow}")->applyFromArray([
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                    ],
                                ],
                                'alignment' => [
                                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                                ],
                            ]);
                            $nextRow++;
                        }
                    }

                    // Check if this is a regular table header row (Nombre)
                    if ($cellValue === 'Nombre') {
                        $lastColumn = $sheet->getHighestColumn();

                        // Style the header row
                        $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => self::COLOR_TABLE_HEADER_TEXT],
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $headerColor],
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                ],
                            ],
                        ]);

                        // Add borders to all data cells in this table
                        $nextRow = $row + 1;
                        while ($nextRow <= $rowCount && $sheet->getCell("A{$nextRow}")->getValue() !== '' && !in_array($sheet->getCell("A{$nextRow}")->getValue(), ['DOCENTES', 'ESTUDIANTES', 'HORARIOS'])) {
                            $sheet->getStyle("A{$nextRow}:{$lastColumn}{$nextRow}")->applyFromArray([
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                    ],
                                ],
                                'alignment' => [
                                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                                ],
                            ]);
                            $nextRow++;
                        }
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
