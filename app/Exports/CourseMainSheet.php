<?php

namespace App\Exports;

use App\Services\CourseService;
use App\Models\Entities\Course;
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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class CourseMainSheet implements FromArray, WithEvents, WithColumnWidths, WithStyles, WithTitle
{
  use ExportsTrait;
  // Color constants for styling
  const COLOR_HEADER_LABELS = '004473';    // Blue for header section labels (Escuela, Nivel, etc.)
  const COLOR_TABLE_HEADER_TEXT = 'FFFFFF';  // White text for table headers

  // Separate colors for teachers and students tables
  const COLOR_TEACHERS_HEADERS = 'F5B54E';   // Orange for teachers table header backgrounds
  const COLOR_STUDENTS_HEADERS = '059669';   // Green for students table header backgrounds
  const COLOR_SCHEDULE_HEADERS = '7C3AED';   // Purple for schedule table header backgrounds

  protected Course $course;
  protected array $exportOptions;
  protected CourseService $courseService;
  protected ?array $students;

  protected array $teachersHeaderRow;
  protected array $studentsHeaderRow;

  public function __construct(CourseService $courseService, Course $course, array $exportOptions, ?array $students)
  {
    $this->course = $course;
    $this->exportOptions = $exportOptions;
    $this->courseService = $courseService;
    $this->students = $students;
  }

  public function array(): array
  {
    // Load necessary relationships
    $this->course->load(['school', 'schoolLevel', 'schoolShift']);

    $exportData = [];

    // Build each section based on export options
    if ($this->exportOptions['basicData'] ?? false) {
      $exportData = array_merge($exportData, $this->buildBasicDataSection());
    }

    if ($this->exportOptions['schedule'] ?? false) {
      $exportData = array_merge($exportData, $this->buildScheduleSection());
    }

    if ($this->exportOptions['teachers'] ?? false) {
      $exportData = array_merge($exportData, $this->buildTeachersSection());
    }

    if ($this->exportOptions['students'] ?? false) {
      $exportData = array_merge($exportData, $this->buildStudentsSection());
    }

    return $exportData;
  }

  /**
   * Build the basic course data section
   */
  private function buildBasicDataSection(): array
  {
    return [
      ['Escuela:', $this->course->school->name],
      ['Nivel:', $this->course->schoolLevel->name],
      ['Turno:', $this->course->schoolShift->name],
      ['Curso:', $this->course->nice_name],
    ];
  }

  /**
   * Build the schedule section
   */
  private function buildScheduleSection(): array
  {
    $exportData = [];

    // Schedule section header
    $exportData[] = [''];
    $exportData[] = ['HORARIOS'];

    $courseSchedule = $this->courseService->getSchedule($this->course);

    if ($courseSchedule && isset($courseSchedule['timeSlots']) && isset($courseSchedule['schedule'])) {
      // Generate day headers
      $dayNames = [1 => 'LU', 2 => 'MA', 3 => 'MI', 4 => 'JU', 5 => 'VI', 6 => 'SA', 7 => 'DO'];
      $dayHeaders = ['#', 'Horario']; // Start with period and time columns

      // Get available days from the schedule
      $availableDays = array_keys($courseSchedule['schedule']);

      foreach ($availableDays as $day) {
        $dayHeaders[] = $dayNames[$day] ?? '';
      }

      // Add header row with day names
      $exportData[] = $dayHeaders;

      // Get all time slots and sort them by start time
      $timeSlots = [];
      foreach ($courseSchedule['timeSlots'] as $key => $times) {
        if (is_array($times) && count($times) >= 2) {
          $timeSlots[] = [
            'id' => $key,
            'start' => $times[0],
            'end' => $times[1]
          ];
        }
      }

      // Sort by start time
      usort($timeSlots, function ($a, $b) {
        $timeA = explode(':', $a['start']);
        $timeB = explode(':', $b['start']);
        return ($timeA[0] * 60 + $timeA[1]) - ($timeB[0] * 60 + $timeB[1]);
      });

      // Generate schedule rows for each time slot
      foreach ($timeSlots as $slot) {
        $periodLabel = $this->formatPeriodLabel($slot['id']);
        $timeString = $slot['start'] . ' - ' . $slot['end'];

        // Create schedule row
        $scheduleRow = [$periodLabel, $timeString];

        // Add class data for each day
        foreach ($availableDays as $day) {
          if (isset($courseSchedule['schedule'][$day][$slot['id']])) {
            $classData = $courseSchedule['schedule'][$day][$slot['id']];

            if ($classData !== null && isset($classData['subject'])) {
              $subjectName = $classData['subject']['name'] ?? '';
              $teacherName = '';

              if (isset($classData['teacher'])) {
                $teacher = $classData['teacher'];
                if (is_array($teacher)) {
                  $teacherName = ($teacher['firstname'] ?? '') . ' ' . ($teacher['lastname'] ?? '');
                  $teacherName = trim($teacherName) ?: ($teacher['name'] ?? '');
                }
              }

              $cellContent = $subjectName;
              if ($teacherName) {
                $cellContent .= " (" . $teacherName . ")";
              }

              $scheduleRow[] = $cellContent;
            } else {
              $scheduleRow[] = '';
            }
          } else {
            $scheduleRow[] = '';
          }
        }

        $exportData[] = $scheduleRow;
      }
    }

    return $exportData;
  }

  /**
   * Build the teachers section
   */
  private function buildTeachersSection(): array
  {
    $exportData = [];
    $teachers = $this->courseService->getTeachers($this->course);

    // Teachers section header
    $exportData[] = [''];
    $exportData[] = ['DOCENTES', ''];

    // Teachers header
    $this->teachersHeaderRow = ['#', 'Nombre', 'Apellido', 'Género', 'DNI', 'Fecha Nacimiento', 'Email', 'Rol', 'Materia', 'A Cargo'];
    $exportData[] = $this->teachersHeaderRow;

    // Teachers data rows
    $teacherNumber = 1;
    foreach ($teachers as $teacher) {
      $exportData[] = [
        $teacherNumber,
        $teacher['firstname'],
        $teacher['lastname'],
        $teacher['gender'],
        $teacher['id_number'],
        $this->formatBirthdate($teacher['birthdate']),
        $teacher['email'],
        $teacher['rel_role']->name,
        is_array($teacher['rel_subject']) ? ($teacher['rel_subject']['name'] ?? '') : ($teacher['rel_subject'] ?? ''),
        $teacher['rel_in_charge'] ? 'Sí' : 'No',
      ];
      $teacherNumber++;
    }

    return $exportData;
  }

  /**
   * Build the students section
   */
  private function buildStudentsSection(): array
  {
    $exportData = [];

    // Students section header
    $exportData[] = [''];
    $exportData[] = ['ALUMNOS/AS', ''];

    // Students header (all columns from attendance sheet)
    $this->studentsHeaderRow = ['#', 'Nombre', 'Apellido', 'Género', 'DNI', 'Fecha de nacimiento', 'Lugar de nacimiento', 'Nacionalidad', 'Domicilio', 'Teléfono', 'Información Crítica'];
    $exportData[] = $this->studentsHeaderRow;

    // Students data rows
    $studentNumber = 1;
    foreach ($this->students as $student) {
      $address = $student['address'] ?? '';
      if ($student['locality']) {
        $address .= ($address ?  ', ' : '') . $student['locality'];
      }
      $criticalInfo = $student['critical_info'] ?? '';
      if ($student['diagnoses_data'] ?? ''){
        $criticalInfo .= ($criticalInfo ? ' - Diagnósticos: ' : '') . $student['diagnoses_data'];
      }
      // if ($student['province']) {
      //   $address .= ', ' . $student['province'];
      // }
      $exportData[] = [
        $studentNumber,
        $student['firstname'],
        $student['lastname'],
        $student['gender'],
        $student['id_number'],
        $this->formatBirthdate($student['birthdate']),
        $student['birth_place'],
        $student['nationality'],
        $address,
        $student['phone'] ?? '',
        $criticalInfo,
      ];
      $studentNumber++;
    }

    return $exportData;
  }

  /**
   * Format period label for schedule
   */
  private function formatPeriodLabel(string $periodKey): string
  {
    if (strpos($periodKey, 'break') !== false) {
      return '(recreo)';
    } elseif (strpos($periodKey, 'lunch') !== false) {
      return '(almuerzo)';
    }

    return $periodKey;
  }

  /**
   * Calculate the last row of the schedule section
   */
  private function calculateScheduleLastRow(int $headerRow, array $courseSchedule): int
  {
    if (!isset($courseSchedule['schedule'])) {
      return $headerRow;
    }

    // Count the number of schedule periods
    $schedulePeriodsCount = count($courseSchedule['schedule']);
    return $headerRow + $schedulePeriodsCount;
  }

  /**
   * Get the sheet title
   */
  public function title(): string
  {
    return 'Datos';
  }


  /**
   * Set column widths for better formatting
   */
  public function columnWidths(): array
  {
    return [
      'A' => 5,   // Sequential number column
      'B' => 25,  // Labels and first column (Nombre)
      'C' => 25,  // Lastname column (Apellido)
      'D' => 10,  // Gender column
      'E' => 18,  // Birthdate column
      'F' => 20,  // Birth place column
      'G' => 15,  // Nationality column
      'H' => 15,  // ID Number column
      'I' => 30,  // Address column
      'J' => 15,  // Phone column
      'K' => 40,  // Email column (teachers only)
      'L' => 20,  // Role column (teachers only)
      'M' => 20,  // Subject column (teachers only)
      'N' => 10,  // In charge column (teachers only)
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
          } elseif ($cellValue === 'ALUMNOS/AS') {
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

          // Style section headers (DOCENTES, ALUMNOS/AS, HORARIOS)
          if (in_array($cellValue, ['DOCENTES', 'ALUMNOS/AS', 'HORARIOS'])) {
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
            $courseSchedule = $this->courseService->getSchedule($this->course);
            $dayCount = isset($courseSchedule['days']) ? count($courseSchedule['days']) : 5;
            $lastColumn = Coordinate::stringFromColumnIndex(3 + $dayCount - 1); // 3 = 'C'

            // Calculate the last row of the schedule section
            $scheduleLastRow = $this->calculateScheduleLastRow($row, $courseSchedule);

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
              $nextRow <= $scheduleLastRow &&
              ($sheet->getCell("A{$nextRow}")->getValue() !== '' || $sheet->getCell("B{$nextRow}")->getValue() !== '') &&
              !in_array($sheet->getCell("A{$nextRow}")->getValue(), ['DOCENTES', 'ALUMNOS/AS', 'HORARIOS'])
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

          // Check if this is a regular table header row (Nombre or #)
          if ($cellValue === 'Nombre' || $cellValue === '#') {
            // Determine the number of columns based on the current section
            $headerColumns = $currentSection === 'teachers' ? $this->teachersHeaderRow : $this->studentsHeaderRow;
            $lastColumn = Coordinate::stringFromColumnIndex(count($headerColumns));

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

            // Add borders and alternating colors to all data cells in this table
            $nextRow = $row + 1;
            $rowCounter = 0;
            while ($nextRow <= $rowCount && $sheet->getCell("A{$nextRow}")->getValue() !== '' && !in_array($sheet->getCell("A{$nextRow}")->getValue(), ['DOCENTES', 'ALUMNOS/AS', 'HORARIOS'])) {
              $isEvenRow = $rowCounter % 2 === 0;
              $backgroundColor = $isEvenRow ? 'FFFFFF' : 'F8F9FA'; // White and light gray

              $sheet->getStyle("A{$nextRow}:{$lastColumn}{$nextRow}")->applyFromArray([
                'fill' => [
                  'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                  'startColor' => ['rgb' => $backgroundColor],
                ],
                'borders' => [
                  'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                  ],
                  'inside' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E0E0E0'],
                  ],
                ],
                'alignment' => [
                  'horizontal' => Alignment::HORIZONTAL_LEFT,
                ],
              ]);
              $nextRow++;
              $rowCounter++;
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
