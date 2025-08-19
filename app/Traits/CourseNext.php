<?php

namespace App\Traits;

use Illuminate\Http\Request;
use App\Models\Entities\School;
use App\Models\Entities\Course;
use App\Models\Catalogs\SchoolLevel;
use App\Models\Entities\AcademicYear;
use App\Helpers\DateHelper;

trait CourseNext
{
    public function calculateNextCourses(Request $request, School $school, SchoolLevel $schoolLevel, bool $doCreate)
    {
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

        $nextCourses = [];
        foreach ($courses as $course) {
            $nextCourse = $this->calculateNextCourse($course, $doCreate);
            if ($doCreate) {
                $nextCourses[$course['id']] = $nextCourse['existing'] ? $nextCourse['existing'] : $this->createCourse($nextCourse['create']);
            } else {
                $nextCourses[$course['id']] = $nextCourse;
            }
        }

        return $nextCourses;
    }

    private function calculateNextCourse(Course $course, bool $mustSearchForAvailableLetter)
    {
        // throw new \Exception('Not implemented - Developing');

        $currentEndDate = $this->getOrGuessEndDate($course);
        $newStartDate =  $this->calculateStartDate($currentEndDate);
        $endDateLimit = $newStartDate->add(new \DateInterval('P1Y'));
        $nextNumber = $course->number + 1;
        $nextLetter = $course->letter;

        $posibleNextCourses = Course::where('school_id', $course->school_id)
            ->where('school_level_id', $course->school_level_id)
            ->where('number', $nextNumber)
            ->where('letter', $nextLetter)
            ->where('start_date', '>=', $newStartDate)
            ->where(function ($query) use ($endDateLimit) {
                $query->where('end_date', '<', $endDateLimit)
                    ->orWhereNull('end_date');
            })
            ->get();

        if ($posibleNextCourses->count() > 0) {
            $sameShiftCourses = $posibleNextCourses->where('school_shift_id', $course->school_shift_id);
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
            'end_date' => $endDateLimit,
            'active' => true,
            'school_id' => $course->school_id,
            'school_level_id' => $course->school_level_id,
            'school_shift_id' => $course->school_shift_id,
            'previous_course_id' => $course->id,
        ];
        $newData['letter'] = $mustSearchForAvailableLetter ? $this->searchForAvailableLetterOnNextCourse($newData) : $nextLetter;

        return ['existing' => null, 'create' => $newData];
    }

    private function getOrGuessEndDate(Course $course): \DateTime
    {
        if ($course->end_date) {
            return $course->end_date;
        }
        $academicYear = AcademicYear::findByDate(new \DateTime($course->start_date));
        if (!$academicYear) {
            $academicYear = AcademicYear::findOrCreateByYear($course->start_date->format('Y'));
        }

        $courseTimeToEnd = $academicYear->end_date->diff(new \DateTime($course->start_date));
        if ($courseTimeToEnd->days > (30 * 7)) {
            return $academicYear->end_date;
        }

        $nextAcademicYear = $academicYear->getNext();
        if (!$nextAcademicYear) {
            $nextAcademicYear = AcademicYear::findOrCreateByYear($course->start_date->format('Y') + 1);
        }

        return $nextAcademicYear->end_date;
    }

    private function calculateStartDate(\DateTime $prevEndDate)
    {
        /* 
        Busco el ciclo lectivo en el que estoy. ¿No hay? Creo uno automáticamente para el año en curso.
        Si termino mucho antes de la fecha de fin de ciclo lectivo (más de un mes antes), el inicio del nuevo curso será el dia siguiente (pero si es sábado o domingo, buscaré el lunes inmediato)
        Si termino cerca de la fecha de fin de ciclo lectivo (como máximo, un mes antes), el inicio del nuevo curso será el inicio del próximo ciclo lectivo. ¿Y si no hay aún un próximo ciclo lectivo? Entonces debo crear el nuevo ciclo lectivo y coger su fecha de inicio 
    */

        $academicYear = AcademicYear::findByDate($prevEndDate);
        if (!$academicYear) $academicYear = AcademicYear::findOrCreateByYear($prevEndDate->format('Y'));

        $courseTimeToEnd = $academicYear->end_date->diff($prevEndDate);
        if ($courseTimeToEnd->days > 30)
            return DateHelper::tomorrowOrMondayIfWeekend(\DateTimeImmutable::createFromMutable($prevEndDate));

        $nextAcademicYear = $academicYear->getNext();
        if (!$nextAcademicYear) $nextAcademicYear = AcademicYear::findOrCreateByYear($prevEndDate->format('Y') + 1);
        return $nextAcademicYear->start_date;
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
}
