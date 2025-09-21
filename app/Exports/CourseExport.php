<?php

namespace App\Exports;

use App\Services\CourseService;
use App\Models\Entities\Course;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CourseExport implements FromArray, WithEvents, WithColumnWidths, WithStyles
{
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

        // Add 2 empty rows
        $exportData[] = ['', ''];
        $exportData[] = ['', ''];
        $currentRow += 2;

        // Teachers data
        if ($this->exportOptions['teachers'] ?? false) {
            $teachers = $this->courseService->getTeachers($this->course);

            // Teachers header
            $exportData[] = ['Nombre', 'Email', 'Teléfono', 'Fecha Nacimiento', 'DNI', 'Rol', 'Materia', 'A Cargo'];
            $currentRow++;

            // Teachers data rows
            foreach ($teachers as $teacher) {
                $exportData[] = [
                    $teacher['name'],
                    $teacher['email'],
                    $teacher['phone'],
                    $this->formatBirthdate($teacher['birthdate']),
                    $teacher['id_number'],
                    $teacher['rel_role']->name,
                    $teacher['rel_in_charge'] ?: 'N/A',
                    $teacher['rel_in_charge'] ? 'Sí' : 'No',
                ];
                $currentRow++;
            }
        }

        // Add 2 empty rows
        $exportData[] = ['', ''];
        $exportData[] = ['', ''];
        $currentRow += 2;

        // Students data
        if ($this->exportOptions['students'] ?? false) {
            $students = $this->courseService->getStudents($this->course, true);

            // Students header
            $exportData[] = ['Nombre', 'Género', 'Email', 'Teléfono', 'Fecha Nacimiento', 'DNI'];
            $currentRow++;

            // Students data rows
            foreach ($students as $student) {
                $exportData[] = [
                    $student['name'],
                    $student['gender'],
                    $student['email'],
                    $student['phone'],
                    $this->formatBirthdate($student['birthdate']),
                    $student['id_number'],
                ];
                $currentRow++;
            }
        }

        return $exportData;
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
            'A' => 20,  // Labels and first column
            'B' => 35,  // Values and data
            'C' => 20,  // Additional columns
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 20,
            'H' => 10,
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
                'color' => ['rgb' => '2F5597'],
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

                // Find and style table headers
                for ($row = 1; $row <= $rowCount; $row++) {
                    $cellValue = $sheet->getCell("A{$row}")->getValue();

                    // Check if this is a table header row
                    if ($cellValue === 'Nombre') {
                        $lastColumn = $sheet->getHighestColumn();

                        // Style the header row
                        $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => 'FFFFFF'],
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '2F5597'],
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
                        while ($nextRow <= $rowCount && $sheet->getCell("A{$nextRow}")->getValue() !== '') {
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
