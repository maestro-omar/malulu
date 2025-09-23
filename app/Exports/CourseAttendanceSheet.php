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

    protected array $monthHeaderRow = ['num' => 0, 'content' => []];
    protected array $mainHeaderRow = ['num' => 0, 'content' => []];
    protected array $mainContentRows = ['starts' => 0, 'ends' => 0];
    protected array $countByMonth = [];
    
    // Attendance data properties
    protected $attendanceData = [];
    protected array $datesWithoutAttendance = [];
    protected CarbonImmutable $minDate;
    protected CarbonImmutable $maxDate;
    protected $statuses;
    protected array $studentAttendanceMap = [];
    
    // Row tracking
    protected int $currentRow = 1;
    protected int $referencesTitleRow = 0;

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
        $this->currentRow++;

        // Build main header
        $exportData = $this->buildMainHeaderSection($exportData);
        $this->currentRow++;

        // Build student data rows
        $exportData = $this->buildStudentDataSection($exportData);
        $this->currentRow = $this->mainContentRows['ends'] + 1;

        // Build symbol legend
        $exportData = $this->buildSymbolLegendSection($exportData);

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
        $row = [
            $studentNumber,
            $student['firstname'],
            $student['lastname'],
        ];
        
        $studentAttendance = $this->studentAttendanceMap[$student['id']] ?? [];
        
        for ($date = Carbon::create($this->minDate); $date <= $this->maxDate; $date->modify('+1 day')) {
            $ymd = $date->format('Y-m-d');
            if (in_array($ymd, $this->datesWithoutAttendance)) {
                $row[] = '';
            } else {
                $statusId = $studentAttendance[$ymd] ?? null;
                $status = $statusId ? $this->statuses->where('id', $statusId)->first()->symbol : '-';
                $row[] = $status;
            }
        }
        
        return $row;
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

        $monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        for ($date = Carbon::create($minDate); $date <= $maxDate; $date->modify('+1 day')) {
            $monthName = $monthNames[$date->month - 1];

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
            },
        ];
    }

    public function setSheetStyles(Worksheet $sheet)
    {
        $this->applyMonthHeaderStyles($sheet);
        $this->applyMainHeaderStyles($sheet);
        $this->applyContentRowStyles($sheet);
        $this->applyRefsTitleRowStyles($sheet);
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

    /**
     * Get the attendance start column (column D)
     */
    private function getAttendanceStartColumn(): string
    {
        return Coordinate::stringFromColumnIndex(count($this->dataHeader) + 1);
    }

    private function styleMonthHeader($sheet, $rowNum, $lastColumn, $attendanceStartColumn)
    {
        $oddBkgColor='9cb4e5';
        $evenBkgColor='e59c9c';
        // Style the month header row
        $sheet->getStyle("A{$rowNum}:{$lastColumn}{$rowNum}")->applyFromArray([
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
