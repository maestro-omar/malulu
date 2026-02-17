<?php

namespace Database\Seeders;

use App\Helpers\DateHelper;
use App\Models\Entities\AcademicYear;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use DateTimeImmutable;

class AcademicYearSeeder extends Seeder
{
    protected array $academicYears = [
        '2019' => [
            'year' => 2019,
            'start_date' => '2019-02-28',
            'end_date' => '2019-12-20',
            'winter_break_start' => '2019-07-15',
            'winter_break_end' => '2019-07-26',
        ],
        '2020' => [
            'year' => 2020,
            'start_date' => '2020-03-02',
            'end_date' => '2020-12-11',
            'winter_break_start' => '2020-07-13',
            'winter_break_end' => '2020-07-24',
        ],
        '2021' => [
            'year' => 2021,
            'start_date' => '2021-02-22',
            'end_date' => '2021-12-22',
            'winter_break_start' => '2021-07-12',
            'winter_break_end' => '2021-07-23',
        ],
        '2022' => [
            'year' => 2022,
            'start_date' => '2022-03-02',
            'end_date' => '2022-12-16',
            'winter_break_start' => '2022-07-11',
            'winter_break_end' => '2022-07-22',
        ],
        '2023' => [
            'year' => 2023,
            'start_date' => '2023-02-27',
            'end_date' => '2023-12-15',
            'winter_break_start' => '2023-07-10',
            'winter_break_end' => '2023-07-21',
        ],
        '2024' => [
            'year' => 2024,
            'start_date' => '2024-02-26',
            'end_date' => '2024-12-13',
            'winter_break_start' => '2024-07-08',
            'winter_break_end' => '2024-07-19',
        ],
        '2025' => [
            'year' => 2025,
            'start_date' => '2025-02-24',
            'end_date' => '2025-12-19',
            'winter_break_start' => '2025-07-07',
            'winter_break_end' => '2025-07-18',
        ],
        '2026' => [
            'year' => 2026,
            'start_date' => '2026-02-18',
            'end_date' => '2026-12-18',
            'winter_break_start' => '2026-07-06',
            'winter_break_end' => '2026-07-17',
        ],

    ];

    public function run()
    {
        $yearsToSeed = $this->academicYears;
        $max = 0;
        // $thisYear = (int) date('Y');
        // $lastYear = $thisYear - 1;

        // // Filter only current and last year
        // $yearsToSeed = array_filter($yearsToSeed, function ($year) use ($thisYear, $lastYear) {
        //     return in_array($year['name'], [$thisYear, $lastYear]);
        // });


        foreach ($yearsToSeed as $year) {
            if ($year['year'] > $max) {
                $max = $year['year'];
            }
            AcademicYear::create($year);
        }
        $currentYear = (int) Carbon::now()->year;
        $year = $max + 1;
        while ($year <= $currentYear) {
            // Start: first Monday on or after Feb 15 that is not Carnival Monday (Easter - 47)
            $feb15 = DateTimeImmutable::createFromMutable(Carbon::create($year, 2, 15)->startOfDay());
            $firstMonday = DateHelper::findMonday($feb15, true);
            $firstMondayCarbon = Carbon::parse($firstMonday->format('Y-m-d'));
            $carnivalMonday = DateHelper::easterOffsetDate($year, -47);
            if ($firstMondayCarbon->isSameDay($carnivalMonday)) {
                $firstMondayCarbon->addDays(7);
            }
            $firstMondayNotCarnivalAfterFeb15 = $firstMondayCarbon->format('Y-m-d');

            // End: first Friday on or after Dec 10
            $dec10 = DateTimeImmutable::createFromMutable(Carbon::create($year, 12, 10)->startOfDay());
            $fridayAfterDec10 = DateHelper::findFriday($dec10, true)->format('Y-m-d');

            // Winter break: first Monday on or after July 12 (7 days after July 5)
            $jul12 = DateTimeImmutable::createFromMutable(Carbon::create($year, 7, 12)->startOfDay());
            $winterBreakStartFirstMondAfter5Jul = DateHelper::findMonday($jul12, true)->format('Y-m-d');

            // Winter break end: 11 days after start (Monday + 11 = Friday)
            $winterBreakEndFridayAfter11DaysFromStart = Carbon::parse($winterBreakStartFirstMondAfter5Jul)->addDays(11)->format('Y-m-d');

            AcademicYear::create([
                'year' => $year,
                'start_date' => $firstMondayNotCarnivalAfterFeb15,
                'end_date' => $fridayAfterDec10,
                'winter_break_start' => $winterBreakStartFirstMondAfter5Jul,
                'winter_break_end' => $winterBreakEndFridayAfter11DaysFromStart,
            ]);
            $year++;
        }
    }
}
