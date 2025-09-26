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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
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

    protected $dataHeader = ['#', 'Nombre', 'Apellido'];
    protected array $monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

    protected array $monthHeaderRow = ['num' => 0, 'content' => []];
    protected array $mainHeaderRow = ['num' => 0, 'content' => []];
    protected array $mainContentRows = ['starts' => 0, 'ends' => 0];
    protected array $countByDay = [];
    protected array $countByMonth = [];
    // protected array $countStudentByMonth = [];

    // Attendance data properties
    protected $attendanceData = [];
    protected array $datesWithoutAttendance = [];
    protected CarbonImmutable $minDate;
    protected CarbonImmutable $maxDate;
    protected $statuses;
    protected array $studentAttendanceMap = [];

    // Row tracking
    protected int $currentRow = 1; //should be $exportData count-1
    protected int $referencesTitleRow = 0;
    protected int $monthlySummaryStartRow = 0;
    protected int $monthlySummaryEndRow = 0;

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
        $this->currentRow = 1;

        // Load attendance data
        $this->loadAttendanceData();

        // Build month header
        $exportData = $this->buildMonthHeaderSection($exportData);

        // Build main header
        $exportData = $this->buildMainHeaderSection($exportData);

        // Build student data rows
        $exportData = $this->buildStudentDataSection($exportData);
        $exportData = $this->buildDailySummarySection($exportData);
        $exportData[] = ['', '']; // Empty row
        $this->currentRow++;

        // Build symbol legend
        $exportData = $this->buildSymbolLegendSection($exportData);

        $exportData = $this->buildMonthlySummarySection($exportData);

        return $exportData;
    }

    /**
     * Load attendance data and prepare for export
     */
    private function loadAttendanceData(): void
    {
        $this->statuses = AttendanceStatus::all();

        $studentsIds = array_map(function ($student) {
            return $student['id'];
        }, $this->students);

        $response = $this->attendanceService->getStudentsAttendanceInAcademicYear($studentsIds, null);
        $this->attendanceData = $response['attendances'];
        $this->datesWithoutAttendance = $response['dates_without_attendance'];
        $this->minDate = CarbonImmutable::create($response['min_date']);
        $this->maxDate = CarbonImmutable::create($response['max_date']);

        // Pre-process student attendance data
        $this->prepareStudentAttendanceMap();
    }

    /**
     * Prepare student attendance map for efficient lookup
     */
    private function prepareStudentAttendanceMap(): void
    {
        foreach ($this->students as $student) {
            $studentAttendance = $this->attendanceData[$student['id']] ?? [];
            if (!empty($studentAttendance)) {
                $this->studentAttendanceMap[$student['id']] = $studentAttendance->pluck('status_id', 'date')->toArray();
            } else {
                $this->studentAttendanceMap[$student['id']] = [];
            }
        }
    }

    /**
     * Build month header section
     */
    private function buildMonthHeaderSection(array $exportData): array
    {
        $this->monthHeaderRow['num'] = $this->currentRow;
        $this->monthHeaderRow['content'] = $this->buildMonthHeader($this->minDate, $this->maxDate);
        $exportData[] = $this->monthHeaderRow['content'];
        $this->currentRow++;
        return $exportData;
    }

    /**
     * Build main header section
     */
    private function buildMainHeaderSection(array $exportData): array
    {
        $this->mainHeaderRow['num'] = $this->currentRow;
        $this->mainHeaderRow['content'] = $this->buildHeader($this->minDate, $this->maxDate);
        $exportData[] = $this->mainHeaderRow['content'];
        $this->currentRow++;
        return $exportData;
    }

    /**
     * Build student data section
     */
    private function buildStudentDataSection(array $exportData): array
    {
        $this->mainContentRows['starts'] = $this->currentRow;

        $studentNumber = 1;
        foreach ($this->students as $student) {
            $exportData[] = $this->buildStudentRow($student, $studentNumber);
            $studentNumber++;
            $this->currentRow++;
        }
        $this->mainContentRows['ends'] = $this->currentRow - 1;
        return $exportData;
    }

    /**
     * Build individual student row
     */
    private function buildStudentRow(array $student, int $studentNumber): array
    {
        $isFirstStudent = $studentNumber === 1;
        $row = [
            $studentNumber,
            $student['firstname'],
            $student['lastname'],
        ];

        $studentAttendance = $this->studentAttendanceMap[$student['id']] ?? [];

        for ($date = Carbon::create($this->minDate); $date <= $this->maxDate; $date->modify('+1 day')) {
            $ymd = $date->format('Y-m-d');
            if (!isset($this->countByDay[$ymd])) {
                $this->countByDay[$ymd] = [];
            }
            $monthNum = $date->month;
            if (in_array($ymd, $this->datesWithoutAttendance)) {
                $row[] = '';
            } else {
                if ($isFirstStudent)
                    $this->countByMonth[$monthNum]['days'] = ($this->countByMonth[$monthNum]['days'] ?? 0) + 1;
                $statusId = $studentAttendance[$ymd] ?? null;
                $status = $statusId ? $this->statuses->where('id', $statusId)->first() : null;
                $row[] = $status ? $status->symbol : '-';
                if (!empty($status)) {
                    $this->countByDay[$ymd][$statusId] = ($this->countByDay[$ymd][$statusId] ?? 0) + 1;
                    if ($status->is_absent) {
                        $this->countByDay[$ymd]['A'] = ($this->countByDay[$ymd]['A'] ?? 0) + 1;
                    } else {
                        $this->countByDay[$ymd]['P'] = ($this->countByDay[$ymd]['P'] ?? 0) + 1;
                    }

                    if (!isset($this->countByMonth[$monthNum])) {
                        $this->countByMonth[$monthNum] = ['A'];
                    }
                    $this->countByMonth[$monthNum][$statusId] = ($this->countByMonth[$monthNum][$statusId] ?? 0) + 1;
                    if ($status->is_absent) {
                        $this->countByMonth[$monthNum]['A'] = ($this->countByMonth[$monthNum]['A'] ?? 0) + 1;
                    } else {
                        $this->countByMonth[$monthNum]['P'] = ($this->countByMonth[$monthNum]['P'] ?? 0) + 1;
                    }


                    // if (!isset($this->countStudentByMonth[$student['id']][$monthNum])) {
                    //     $this->countStudentByMonth[$student['id']][$monthNum] = [];
                    // }
                    // $this->countStudentByMonth[$student['id']][$monthNum][$statusId] = ($this->countStudentByMonth[$student['id']][$monthNum][$statusId] ?? 0) + 1;
                    // if ($status->is_absent) {
                    //     $this->countStudentByMonth[$student['id']][$monthNum]['A'] = ($this->countStudentByMonth[$student['id']][$monthNum]['A'] ?? 0) + 1;
                    // } else {
                    //     $this->countStudentByMonth[$student['id']][$monthNum]['P'] = ($this->countStudentByMonth[$student['id']][$monthNum]['P'] ?? 0) + 1;
                    // }
                }
            }
        }

        return $row;
    }

    private function buildDailySummarySection(array $exportData): array
    {
        // Add empty row before summary
        $emptyRow = array_fill(0, count($this->dataHeader), '');
        $exportData[] = $emptyRow;
        $this->currentRow++;

        // Build "Presentes" row
        $presentesRow = array_fill(0, count($this->dataHeader) - 1, ''); // Leave space for student data columns
        $presentesRow[] = 'Presentes'; // Title in the last student data column
        for ($date = Carbon::create($this->minDate); $date <= $this->maxDate; $date->modify('+1 day')) {
            $ymd = $date->format('Y-m-d');
            if (in_array($ymd, $this->datesWithoutAttendance)) {
                $presentesRow[] = '';
            } else {
                $presentesRow[] = $this->countByDay[$ymd]['P'] ?? '-';
            }
        }
        $exportData[] = $presentesRow;
        $this->currentRow++;

        // Build "Ausentes" row
        $ausentesRow = array_fill(0, count($this->dataHeader) - 1, ''); // Leave space for student data columns
        $ausentesRow[] = 'Ausentes'; // Title in the last student data column
        for ($date = Carbon::create($this->minDate); $date <= $this->maxDate; $date->modify('+1 day')) {
            $ymd = $date->format('Y-m-d');
            if (in_array($ymd, $this->datesWithoutAttendance)) {
                $ausentesRow[] = '';
            } else {
                $ausentesRow[] = $this->countByDay[$ymd]['A'] ?? '-';
            }
        }
        $exportData[] = $ausentesRow;
        $this->currentRow++;

        // Build "Inscriptos" row (sum of presentes + ausentes)
        $inscriptosRow = array_fill(0, count($this->dataHeader) - 1, ''); // Leave space for student data columns
        $inscriptosRow[] = 'Inscriptos'; // Title in the last student data column
        for ($date = Carbon::create($this->minDate); $date <= $this->maxDate; $date->modify('+1 day')) {
            $ymd = $date->format('Y-m-d');
            if (in_array($ymd, $this->datesWithoutAttendance)) {
                $inscriptosRow[] = '';
            } else {
                $presentes = $this->countByDay[$ymd]['P'] ?? 0;
                $ausentes = $this->countByDay[$ymd]['A'] ?? 0;
                $inscriptosRow[] = $presentes + $ausentes;
            }
        }
        $exportData[] = $inscriptosRow;
        $this->currentRow++;

        return $exportData;
    }

    private function buildMonthlySummarySection(array $exportData): array
    {
        $exportData[] = ['', '']; // Empty row
        $this->currentRow++;
        $exportData[] = ['Mes', 'Presentes', 'Ausentes', 'Total', '% Asistencia', 'Días', 'Prom diario']; // Header row
        $this->monthlySummaryStartRow =  $this->currentRow;
        $this->currentRow++;

        foreach ($this->countByMonth as $monthNum => $statuses) {
            $statuses['A'] = $statuses['A'] ?? 0;
            $statuses['P'] = $statuses['P'] ?? 0;
            $monthName = $this->monthNames[$monthNum - 1];
            $sum = $statuses['P'] + $statuses['A'];
            $pctRow = round(($statuses['P'] / $sum) * 100, 2) . '%';
            $presentAvg = round($statuses['P'] / $statuses['days'], 2);
            $exportData[] = [
                $monthName,
                $statuses['P'],
                $statuses['A'],
                $sum,
                $pctRow,
                $statuses['days'],
                $presentAvg
            ];
            $this->currentRow++;
        }

        // Store summary section info for styling
        $this->monthlySummaryEndRow = $this->currentRow - 1;

        return $exportData;
    }

    /**
     * Build symbol legend section
     */
    private function buildSymbolLegendSection(array $exportData): array
    {
        $exportData[] = ['', '']; // Empty row
        $this->currentRow++;

        // Store the title row for styling
        $this->referencesTitleRow = $this->currentRow;
        $exportData[] = ['Referencias', '']; // Title
        $this->currentRow++;

        foreach ($this->statuses as $status) {
            $exportData[] = [$status->symbol, $status->name];
            $this->currentRow++;
        }

        return $exportData;
    }

    private function buildHeader($minDate, $maxDate)
    {
        $cells = $this->dataHeader;
        for ($date = Carbon::create($minDate); $date <= $maxDate; $date->modify('+1 day')) {
            $cells[] = $date->format('d/m');
        }
        return $cells;
    }

    private function buildMonthHeader($minDate, $maxDate)
    {
        $cells = ['', '', '']; // Empty cells for student info columns (#, Nombre, Apellido)

        $currentMonth = null;
        $monthStartColumn = 4; // Column D (after 3 student info columns)
        $monthSpan = 0;

        for ($date = Carbon::create($minDate); $date <= $maxDate; $date->modify('+1 day')) {
            $monthName = $this->monthNames[$date->month - 1];

            if ($currentMonth === null) {
                $currentMonth = $monthName;
                $monthSpan = 1;
            } elseif ($currentMonth === $monthName) {
                $monthSpan++;
            } else {
                // Add the previous month
                $cells[] = $currentMonth;
                // Add empty cells for the span
                for ($i = 1; $i < $monthSpan; $i++) {
                    $cells[] = '';
                }

                // Start new month
                $currentMonth = $monthName;
                $monthSpan = 1;
            }
        }

        // Add the last month
        if ($currentMonth !== null) {
            $cells[] = $currentMonth;
            // Add empty cells for the remaining span
            for ($i = 1; $i < $monthSpan; $i++) {
                $cells[] = '';
            }
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
            'A' => 5,   // Sequential number
            'B' => 20,  // Nombre
            'C' => 20,  // Apellido
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
                $this->setSheetStyles($sheet);
                $this->forceFormulaCalculation($sheet);
            },
        ];
    }

    public function setSheetStyles(Worksheet $sheet)
    {
        $this->applyMonthHeaderStyles($sheet);
        $this->applyMainHeaderStyles($sheet);
        $this->applyContentRowStyles($sheet);
        $this->applyDailySummaryStyles($sheet);
        $this->applyRefsTitleRowStyles($sheet);
        $this->applyMonthlySummaryStyles($sheet);
        $this->setColumnWidths($sheet);
    }

    /**
     * Apply month header styles and merging
     */
    private function applyMonthHeaderStyles(Worksheet $sheet): void
    {
        if ($this->monthHeaderRow['num'] <= 0) {
            return;
        }

        $lastColumn = $sheet->getHighestColumn();
        $attendanceStartColumn = $this->getAttendanceStartColumn();

        $this->styleMonthHeader($sheet, $this->monthHeaderRow['num'], $lastColumn, $attendanceStartColumn);
    }

    /**
     * Apply main header styles
     */
    private function applyMainHeaderStyles(Worksheet $sheet): void
    {
        if ($this->mainHeaderRow['num'] <= 0) {
            return;
        }

        $lastColumn = $sheet->getHighestColumn();
        $this->styleMainHeader($sheet, $this->mainHeaderRow['num'], $lastColumn);
    }

    /**
     * Apply content row styles with alternating colors
     */
    private function applyContentRowStyles(Worksheet $sheet): void
    {
        if ($this->mainContentRows['starts'] <= 0 || $this->mainContentRows['ends'] <= 0) {
            return;
        }

        $lastColumn = $sheet->getHighestColumn();
        $attendanceStartColumn = $this->getAttendanceStartColumn();

        $this->styleContentRows(
            $sheet,
            $this->mainContentRows['starts'],
            $this->mainContentRows['ends'],
            $lastColumn,
            $attendanceStartColumn
        );
    }

    /**
     * Apply daily summary section styles
     */
    private function applyDailySummaryStyles(Worksheet $sheet): void
    {
        if ($this->mainContentRows['ends'] <= 0) {
            return;
        }

        $lastColumn = $sheet->getHighestColumn();
        $attendanceStartColumn = $this->getAttendanceStartColumn();

        // Calculate summary rows (after student data + 1 empty row)
        $summaryStartRow = $this->mainContentRows['ends'] + 2; // +1 for empty row, +1 for first summary row
        $presentesRow = $summaryStartRow;
        $ausentesRow = $summaryStartRow + 1;
        $inscriptosRow = $summaryStartRow + 2;

        // Style "Presentes" row - light green background
        $this->styleSummaryRow($sheet, $presentesRow, $lastColumn, $attendanceStartColumn, '90EE90'); // Light green

        // Style "Ausentes" row - light red background
        $this->styleSummaryRow($sheet, $ausentesRow, $lastColumn, $attendanceStartColumn, 'FFB6C1'); // Light red

        // Style "Inscriptos" row - light blue background
        $this->styleSummaryRow($sheet, $inscriptosRow, $lastColumn, $attendanceStartColumn, 'ADD8E6'); // Light blue
    }

    /**
     * Style individual summary row
     */
    private function styleSummaryRow(Worksheet $sheet, int $rowNum, string $lastColumn, string $attendanceStartColumn, string $backgroundColor): void
    {
        // Style the title column (right aligned)
        $titleColumn = Coordinate::stringFromColumnIndex(count($this->dataHeader));
        $sheet->getStyle("{$titleColumn}{$rowNum}")->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
            ],
        ]);

        // Style the entire row with background color
        $sheet->getStyle("C{$rowNum}:{$lastColumn}{$rowNum}")->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => $backgroundColor],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Center align the attendance data columns
        $sheet->getStyle("{$attendanceStartColumn}{$rowNum}:{$lastColumn}{$rowNum}")->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
    }

    /**
     * Apply title row styles and merging
     */
    private function applyRefsTitleRowStyles(Worksheet $sheet): void
    {
        if ($this->referencesTitleRow <= 0) {
            return;
        }

        $lastColumn = "B";

        // Merge cells A to the last column for the title
        $sheet->mergeCells("A{$this->referencesTitleRow}:{$lastColumn}{$this->referencesTitleRow}");

        // Apply styling to the title row
        $sheet->getStyle("A{$this->referencesTitleRow}:{$lastColumn}{$this->referencesTitleRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => self::COLOR_TABLE_HEADER_TEXT],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::COLOR_ATTENDANCE_HEADERS],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
    }

    private function applyMonthlySummaryStyles(Worksheet $sheet): void
    {
        if ($this->monthlySummaryStartRow <= 0 || $this->monthlySummaryEndRow <= 0) {
            return;
        }

        // Style the header row (Mes, Presentes, Ausentes, Total, % Asistencia)
        $headerRow =  $this->monthlySummaryStartRow;
        $sheet->getStyle("A{$headerRow}:G{$headerRow}")->applyFromArray([
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
                'inside' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Style the data rows (month summaries)
        for ($row =  $this->monthlySummaryStartRow + 1; $row <=  $this->monthlySummaryEndRow; $row++) {
            $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'inside' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);
        }
    }

    /**
     * Force Excel to recalculate formulas
     */
    private function forceFormulaCalculation(Worksheet $sheet): void
    {
        // Force calculation of formulas
        $sheet->getParent()->getActiveSheet()->getParent()->getCalculationEngine()->clearCalculationCache();

        // Recalculate all formulas in the sheet
        foreach ($sheet->getRowIterator() as $row) {
            foreach ($row->getCellIterator() as $cell) {
                if ($cell->getDataType() === \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA) {
                    $cell->getCalculatedValue();
                }
            }
        }
    }

    /**
     * Get the attendance start column (column D)
     */
    private function getAttendanceStartColumn(): string
    {
        return Coordinate::stringFromColumnIndex(count($this->dataHeader) + 1);
    }


    private function styleMonthHeader($sheet, $rowNum, $lastColumn, $attendanceStartColumn)
    {
        $oddBkgColor = '9cb4e5';
        $evenBkgColor = 'e59c9c';
        // Style the month header row
        $sheet->getStyle("D{$rowNum}:{$lastColumn}{$rowNum}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '9cb4e5'], // Light blue background
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

        // Merge cells for each month using the content array
        $this->mergeMonthCells($sheet, $this->monthHeaderRow['content'], $rowNum);
    }

    private function styleMainHeader($sheet, $rowNum, $lastColumn)
    {
        $sheet->getStyle("A{$rowNum}:{$lastColumn}{$rowNum}")->applyFromArray([
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
    }

    private function styleContentRows($sheet, $startRow, $endRow, $lastColumn, $attendanceStartColumn)
    {
        for ($row = $startRow; $row <= $endRow; $row++) {
            $isEvenRow = ($row - $startRow) % 2 === 0;
            $backgroundColor = $isEvenRow ? 'FFFFFF' : 'F8F9FA'; // White and light gray

            $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
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
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Center attendance data columns
            $sheet->getStyle("{$attendanceStartColumn}{$row}:{$lastColumn}{$row}")->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        // Freeze the header row
        $sheet->freezePane("A" . $startRow);
    }

    private function setColumnWidths(Worksheet $sheet): void
    {
        $lastColumn = $sheet->getHighestColumn();
        $attendanceStartColumn = $this->getAttendanceStartColumn();

        // Set attendance date columns to smaller width
        for ($col = $attendanceStartColumn; $col <= $lastColumn; $col++) {
            $sheet->getColumnDimension($col)->setWidth(8);
        }

        // Auto-fit columns for student info columns only
        $studentInfoEndColumn = Coordinate::stringFromColumnIndex(count($this->dataHeader));
        foreach (range('A', $studentInfoEndColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(false);
        }
    }

    private function mergeMonthCells($sheet, $monthContent, $rowNum)
    {
        $currentMonth = null;
        $monthStartCol = null;
        $attendanceStartIndex = count($this->dataHeader); // Skip the first 3 columns (#, Nombre, Apellido)
        $monthIndex = 0; // Track month index for alternating colors

        $oddBkgColor = '9cb4e5';
        $evenBkgColor = 'e59c9c';

        foreach ($monthContent as $index => $cellValue) {
            if ($index < $attendanceStartIndex) {
                continue; // Skip student info columns
            }

            $colLetter = Coordinate::stringFromColumnIndex($index + 1);

            if (!empty($cellValue)) {
                if ($currentMonth !== null && $currentMonth !== $cellValue) {
                    // Merge and style previous month
                    if ($monthStartCol !== null) {
                        $endCol = Coordinate::stringFromColumnIndex($index);
                        $sheet->mergeCells("{$monthStartCol}{$rowNum}:{$endCol}{$rowNum}");

                        // Apply alternating background color
                        $bgColor = ($monthIndex % 2 === 0) ? $oddBkgColor : $evenBkgColor;
                        $sheet->getStyle("{$monthStartCol}{$rowNum}:{$endCol}{$rowNum}")->applyFromArray([
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $bgColor],
                            ],
                        ]);
                    }
                }

                if ($currentMonth !== $cellValue) {
                    // Start new month
                    $currentMonth = $cellValue;
                    $monthStartCol = $colLetter;
                    $monthIndex++;
                }
            }
        }

        // Merge and style the last month
        if ($currentMonth !== null && $monthStartCol !== null) {
            $lastCol = $sheet->getHighestColumn();
            $sheet->mergeCells("{$monthStartCol}{$rowNum}:{$lastCol}{$rowNum}");

            // Apply alternating background color for last month
            $bgColor = ($monthIndex % 2 === 0) ? $oddBkgColor : $evenBkgColor;
            $sheet->getStyle("{$monthStartCol}{$rowNum}:{$lastCol}{$rowNum}")->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $bgColor],
                ],
            ]);
        }
    }
}
