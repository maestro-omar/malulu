<?php

namespace App\Services;

use App\Models\Entities\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Helpers\DateHelper;

class AcademicYearService
{
    /**
     * Get all academic years
     */
    public function getAcademicYears()
    {
        return AcademicYear::orderBy('year', 'desc')->get();
    }

    /**
     * Validate academic year data
     */
    public function validateAcademicYearData(array $data, ?AcademicYear $academicYear = null)
    {
        $rules = [
            'year' => [
                'required',
                'integer',
                Rule::unique('academic_years', 'year')->ignore($academicYear?->id)
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'winter_break_start' => 'required|date|after_or_equal:start_date|before:end_date',
            'winter_break_end' => 'required|date|after:winter_break_start|before:end_date',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Create a new academic year
     */
    public function createAcademicYear(array $data)
    {
        $validatedData = $this->validateAcademicYearData($data);
        return AcademicYear::create($validatedData);
    }

    /**
     * Update an existing academic year
     */
    public function updateAcademicYear(AcademicYear $academicYear, array $data)
    {
        $validatedData = $this->validateAcademicYearData($data, $academicYear);
        $academicYear->update($validatedData);
        return $academicYear;
    }

    /**
     * Delete an academic year
     */
    public function deleteAcademicYear(AcademicYear $academicYear)
    {
        return $academicYear->delete();
    }

    /**
     * Get trashed academic years
     */
    public function getTrashedAcademicYears()
    {
        return AcademicYear::onlyTrashed()
            ->orderBy('year', 'desc')
            ->get();
    }

    /**
     * Restore a trashed academic year
     */
    public function restoreAcademicYear($id)
    {
        $academicYear = AcademicYear::onlyTrashed()->findOrFail($id);
        return $academicYear->restore();
    }

    /**
     * Permanently delete an academic year
     */
    public function forceDeleteAcademicYear($id)
    {
        $academicYear = AcademicYear::onlyTrashed()->findOrFail($id);
        return $academicYear->forceDelete();
    }

    public static function isSummerOnDecember(): bool
    {
        return config('malulu.summer_on_december');
    }

    public static function findOrCreateByYear(int $year): AcademicYear
    {
        $exists = AcademicYear::findByYear($year);
        if ($exists) return $exists;
        if (self::isSummerOnDecember()) {
            $startDate = DateHelper::findMonday(new \DateTimeImmutable($year . '-02-27'));
            $endDate = DateHelper::findFriday(new \DateTimeImmutable($year . '-12-15'));
            $winterBreakStartDate = DateHelper::findMonday(new \DateTimeImmutable($year . '-07-05'));
            $winterBreakEndDate =  $winterBreakStartDate->add(new \DateInterval('P14D'));
        } else {
            $startDate = DateHelper::findMonday(new \DateTimeImmutable($year . '-08-27'));
            $endDate = DateHelper::findFriday(new \DateTimeImmutable(($year + 1) . '-06-15'));
            $winterBreakStartDate = DateHelper::findMonday(new \DateTimeImmutable($year . '-12-20'));
            $winterBreakEndDate =  $winterBreakStartDate->add(new \DateInterval('P14D'));
        }
        $data = [
            'active' => true,
            'year' => $year,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'winter_break_start' => $winterBreakStartDate,
            'winter_break_end' => $winterBreakEndDate,
        ];
        return AcademicYear::create($data);
    }
}
