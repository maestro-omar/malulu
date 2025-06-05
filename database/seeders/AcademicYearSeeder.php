<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear;

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

    ];

    public function run()
    {
        $yearsToSeed = $this->academicYears;
        // $thisYear = (int) date('Y');
        // $lastYear = $thisYear - 1;

        // // Filter only current and last year
        // $yearsToSeed = array_filter($yearsToSeed, function ($year) use ($thisYear, $lastYear) {
        //     return in_array($year['name'], [$thisYear, $lastYear]);
        // });


        foreach ($yearsToSeed as $year) {
            AcademicYear::create($year);
        }
    }
}
