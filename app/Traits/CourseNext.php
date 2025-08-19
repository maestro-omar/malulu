<?php

namespace App\Traits;

use Illuminate\Http\Request;
use App\Models\Entities\School;
use App\Models\Entities\Course;
use App\Models\Catalogs\SchoolLevel;
use App\Models\Entities\AcademicYear;
use App\Helpers\DateHelper;
use App\Services\AcademicYearService;
use DateTime;

trait CourseNext
{
    public function calculateNextCourses(Request $request, School $school, SchoolLevel $schoolLevel, bool $doCreate, bool $limitNumbers = true)
    {
        $minSchoolLevelNumber = $limitNumbers ? Course::where('school_id', $school->id)
            ->where('school_level_id', $schoolLevel->id)
            ->min('number')
            : null;

        $maxSchoolLevelNumber = $limitNumbers ? Course::where('school_id', $school->id)
            ->where('school_level_id', $schoolLevel->id)
            ->max('number')
            : null;

        $year = date('Y');
        $request->merge([
            'no_next' => true, //important
            'no_paginate' => true, //important
            'school_level_id' => $schoolLevel->id,
            'school_id' => $school->id,
            'year' => $year,
            'active' => true,
        ]);

        $courses = $this->getCourses($request, $school->id);

        foreach ($courses as $idx =>  $course) {
            if ($course['number'] >= $maxSchoolLevelNumber) {
                unset($courses[$idx]);
            } else {
                $nextCourse = $this->calculateNextCourse($course, $doCreate);
                $courses[$idx]['to_set'] = $doCreate
                    ? ($nextCourse['existing'] ? $nextCourse['existing'] : $this->createCourse($nextCourse['create']))
                    : $nextCourse;
                $courses[$idx]['to_duplicate'] = ($course['number'] == $minSchoolLevelNumber) ?
                    $this->calculateSameCourseNextYear($course) : null;
            }
        }

        return $courses;
    }

    private function calculateSameCourseNextYear(array $courseData)
    {
        $currentEndDate = ($courseData['end_date'] ?? '') ? $courseData['end_date'] : $this->guessEndDate(new \DateTime($courseData['start_date']));
        $aux =  $this->calculateStartDate(new \DateTime($currentEndDate));
        $newStartDate = $aux->format('Y-m-d');
        $endDateLimit = $aux;
        $endDateLimit->add(new \DateInterval('P1Y'));
        $nextNumber = $courseData['number']; //will be the same number
        $nextLetter = $courseData['letter'];

        $posibleNextCourses = Course::where('school_id', $courseData['school_id'])
            ->where('school_level_id', $courseData['school_level_id'])
            ->where('number', $nextNumber)
            ->where('letter', $nextLetter)
            // Check if the course in DB intersects with the desired date range
            ->where(function ($query) use ($newStartDate, $endDateLimit) {
                $query
                    // DB row starts within our range
                    ->whereBetween('start_date', [$newStartDate, $endDateLimit->format('Y-m-d')])
                    // OR our range starts within the DB row's range
                    ->orWhere(function ($q) use ($newStartDate) {
                        $q->where('start_date', '<=', $newStartDate)
                            ->where(function ($q2) use ($newStartDate) {
                                $q2->where('end_date', '>=', $newStartDate)
                                    ->orWhere(function ($q3) use ($newStartDate) {
                                        // If end_date is null, treat as one year from start_date
                                        $q3->whereNull('end_date')
                                            ->whereRaw("DATE_ADD(start_date, INTERVAL 1 YEAR) >= ?", $newStartDate);
                                    });
                            });
                    });
            })
            ->get();
        // if ($nextLetter == 'B' and $nextNumber === 4) {
        //     dd('debug posibleNextCourses', $posibleNextCourses, $newStartDate, $endDateLimit);
        // }

        if ($posibleNextCourses->count() > 0) {
            return null;

            // $sameShiftCourses = $posibleNextCourses->where('school_shift_id', $courseData['school_shift_id']);
            // if ($sameShiftCourses->count() > 1) {
            //     $sameShiftCourses = $sameShiftCourses->where('active', true);
            // }
            // if ($sameShiftCourses->count() === 1) {
            //     $nextCourseExists = $sameShiftCourses->first();
            //     return ['existing' => $nextCourseExists, 'create' => null];
            // }
        }

        $newData = [
            'number' => $nextNumber,
            'letter' => $nextLetter,
            'start_date' => $newStartDate,
            'end_date' => $this->guessEndDate(new \DateTime($newStartDate)),
            'active' => true,
            'school_id' => $courseData['school_id'],
            'name' => $courseData['name'],
            'school_level_id' => $courseData['school_level_id'],
            'school_shift_id' => $courseData['school_shift_id'],
            'previous_course_id' => null,
        ];
        $courseNew = new Course($newData);
        return $courseNew;
    }

    private function calculateNextCourse(array $courseData, bool $mustSearchForAvailableLetter)
    {
        $currentEndDate = ($courseData['end_date'] ?? '') ? $courseData['end_date'] : $this->guessEndDate(new \DateTime($courseData['start_date']));
        $aux =  $this->calculateStartDate(new \DateTime($currentEndDate));
        $newStartDate = $aux->format('Y-m-d');
        $endDateLimit = $aux;
        $endDateLimit->add(new \DateInterval('P1Y'));
        $nextNumber = $courseData['number'] + 1;
        $nextLetter = $courseData['letter'];

        $posibleNextCourses = Course::where('school_id', $courseData['school_id'])
            ->where('school_level_id', $courseData['school_level_id'])
            ->where('number', $nextNumber)
            ->where('letter', $nextLetter)
            // Check if the course in DB intersects with the desired date range
            ->where(function ($query) use ($newStartDate, $endDateLimit) {
                $query
                    // DB row starts within our range
                    ->whereBetween('start_date', [$newStartDate, $endDateLimit->format('Y-m-d')])
                    // OR our range starts within the DB row's range
                    ->orWhere(function ($q) use ($newStartDate) {
                        $q->where('start_date', '<=', $newStartDate)
                            ->where(function ($q2) use ($newStartDate) {
                                $q2->where('end_date', '>=', $newStartDate)
                                    ->orWhere(function ($q3) use ($newStartDate) {
                                        // If end_date is null, treat as one year from start_date
                                        $q3->whereNull('end_date')
                                            ->whereRaw("DATE_ADD(start_date, INTERVAL 1 YEAR) >= ?", $newStartDate);
                                    });
                            });
                    });
            })
            ->get();
        // if ($nextLetter == 'B' and $nextNumber === 4) {
        //     dd('debug posibleNextCourses', $posibleNextCourses, $newStartDate, $endDateLimit);
        // }

        if ($posibleNextCourses->count() > 0) {
            $sameShiftCourses = $posibleNextCourses->where('school_shift_id', $courseData['school_shift_id']);
            if ($sameShiftCourses->count() > 1) {
                $sameShiftCourses = $sameShiftCourses->where('active', true);
            }
            if ($sameShiftCourses->count() === 1) {
                $nextCourseExists = $sameShiftCourses->first();
                return ['existing' => $nextCourseExists, 'create' => null];
            }
        }

        $newData = [
            'number' => $nextNumber,
            'letter' => $nextLetter,
            'start_date' => $newStartDate,
            'end_date' => $this->guessEndDate(new \DateTime($newStartDate)),
            'active' => true,
            'school_id' => $courseData['school_id'],
            'school_level_id' => $courseData['school_level_id'],
            'school_shift_id' => $courseData['school_shift_id'],
            'previous_course_id' => $courseData['id'],
        ];
        $newData['name'] = $courseData['name'] ? $this->searchForNextCourseName($newData) : '';
        $newData['letter'] = $mustSearchForAvailableLetter ? $this->searchForAvailableLetterOnNextCourse($newData) : $nextLetter;
        $courseNew = new Course($newData);
        return ['existing' => null, 'create' => $courseNew];
    }

    private function guessEndDate(\DateTime $startDate): string
    {
        $academicYear = AcademicYear::findByDate($startDate);
        if (!$academicYear) {
            $academicYear = AcademicYearService::findOrCreateByYear($startDate->format('Y'));
        }

        $courseTimeToEnd = $academicYear->end_date->diff($startDate);
        if ($courseTimeToEnd->days > (30 * 7)) {
            return $academicYear->end_date->format('Y-m-d');
        }

        $nextAcademicYear = $academicYear->getNext();
        if (!$nextAcademicYear) {
            $nextAcademicYear = AcademicYearService::findOrCreateByYear($startDate->format('Y') + 1);
        }

        return $nextAcademicYear->end_date->format('Y-m-d');
    }

    private function calculateStartDate(\DateTime $prevEndDate): \DateTime
    {
        /*
        Search for the academic year in which I am. If there is none, I create one automatically for the current year.
        If I finish much earlier than the end date of the academic year (more than a month before), the start date of the new course will be the next day (but if it is Saturday or Sunday, I will search for the next Monday)
        If I finish close to the end date of the academic year (at most, a month before), the start date of the new course will be the start date of the next academic year. If there is no next academic year, I must create it and take its start date
        */

        $academicYear = AcademicYear::findByDate($prevEndDate);
        if (!$academicYear) $academicYear = AcademicYearService::findOrCreateByYear($prevEndDate->format('Y'));
        $courseTimeToEnd = $academicYear->end_date->diff($prevEndDate);
        if ($courseTimeToEnd->days > 30)
            return \DateTime::createFromImmutable(DateHelper::tomorrowOrMondayIfWeekend(\DateTimeImmutable::createFromMutable($prevEndDate)));

        $nextAcademicYear = $academicYear->getNext();
        if (!$nextAcademicYear) $nextAcademicYear = AcademicYearService::findOrCreateByYear($prevEndDate->format('Y') + 1);
        return new DateTime($nextAcademicYear->start_date);
    }

    private function searchForAvailableLetterOnNextCourse(array $newCourseData)
    {
        $query = Course::select('letter')->where('school_id', $newCourseData['school_id'])
            ->where('school_level_id', $newCourseData['school_level_id'])
            ->where('number', $newCourseData['number'])
            ->where('start_date', '>=', $newCourseData['start_date']);
        if (empty($newCourseData['end_date'])) {
            $query->where('end_date', '<=', $newCourseData['start_date'])
                ->orWhereNull('end_date');
        } else {
            $query->where('end_date', '<=', $newCourseData['end_date'])
                ->orWhereNull('end_date');
        }

        $existingLetters = $query->get()->pluck('letter')->toArray();

        if (empty($existingLetters)) return $newCourseData['letter'];

        $nextLetter = chr(ord($newCourseData['letter']) + 1);
        while (true) {
            if (!in_array($nextLetter, $existingLetters)) {
                return $nextLetter;
            }
            $nextLetter = chr(ord($nextLetter) + 1);
            if (ord($nextLetter) > ord('Z')) {
                throw new \Exception('Too many letters');
            }
        }
    }

    private function searchForNextCourseName(array $newCourseData)
    {
        $name = Course::select('name')
            ->where('school_id', $newCourseData['school_id'])
            ->where('school_level_id', $newCourseData['school_level_id'])
            ->where('number', $newCourseData['number'])
            ->where('letter', $newCourseData['letter'])
            ->orderBy('start_date', 'desc')
            ->first();
        return $name ? $name->name : '';
    }
}
