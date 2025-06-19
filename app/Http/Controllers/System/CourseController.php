<?php

namespace App\Http\Controllers\System;

use App\Models\Course;
use App\Services\CourseService;
use App\Services\SchoolService;
use App\Services\SchoolLevelService;
use App\Services\SchoolShiftService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;
use App\Models\School;
use App\Models\SchoolLevel;

class CourseController extends SystemBaseController
{
    protected $courseService;
    protected $schoolService;
    protected $schoolLevelService;
    protected $schoolShiftService;

    public function __construct(
        CourseService $courseService,
        SchoolService $schoolService,
        SchoolLevelService $schoolLevelService,
        SchoolShiftService $schoolShiftService
    )
    {
        $this->courseService = $courseService;
        $this->schoolService = $schoolService;
        $this->schoolLevelService = $schoolLevelService;
        $this->schoolShiftService = $schoolShiftService;
        $this->middleware('permission:superadmin');
    }

    public function index(Request $request, School $school, SchoolLevel $schoolLevel)
    {
        $courses = $this->courseService->getCourses($request, $school->id, $schoolLevel->id);
        $schoolLevels = $school->schoolLevels()->orderBy('name')->get();

        return Inertia::render('Courses/Index', [
            'courses' => $courses,
            'school' => $school,
            'schoolLevels' => $schoolLevels,
            'selectedLevel' => $schoolLevel,
        ]);
    }

    public function create(School $school, SchoolLevel $schoolLevel)
    {
        $schools = $this->schoolService->getSchools(new Request(), true);
        $schoolLevels = $this->schoolLevelService->getSchoolLevels(new Request(), true);
        $schoolShifts = $this->schoolShiftService->getSchoolShifts(new Request(), true);
        $courses = $this->courseService->getCoursesForSchool($school->id, $schoolLevel->id, null, true);

        return Inertia::render('Courses/Create', [
            'schools' => $schools,
            'schoolLevels' => $schoolLevels,
            'schoolShifts' => $schoolShifts,
            'courses' => $courses,
            'school' => $school,
            'selectedLevel' => $schoolLevel,
        ]);
    }

    public function store(Request $request, School $school, SchoolLevel $schoolLevel)
    {
        try {
            $data = $request->all();
            $data['school_id'] = $school->id;
            $data['school_level_id'] = $schoolLevel->id;
            $this->courseService->createCourse($data);
            return redirect()->route('courses.index', ['school' => $school->cue, 'schoolLevel' => $schoolLevel->code])
                ->with('success', 'Curso creado exitosamente.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function edit(School $school, SchoolLevel $schoolLevel, Course $course)
    {
        $schools = $this->schoolService->getSchools(new Request(), true);
        $schoolLevels = $this->schoolLevelService->getSchoolLevels(new Request(), true);
        $schoolShifts = $this->schoolShiftService->getSchoolShifts(new Request(), true);
        $courses = $this->courseService->getCoursesForSchool($school->id, $schoolLevel->id, null, true);

        return Inertia::render('Courses/Edit', [
            'course' => $course,
            'schools' => $schools,
            'schoolLevels' => $schoolLevels,
            'schoolShifts' => $schoolShifts,
            'courses' => $courses,
            'school' => $school,
            'selectedLevel' => $schoolLevel,
        ]);
    }

    public function update(Request $request, School $school, SchoolLevel $schoolLevel, Course $course)
    {
        try {
            $data = $request->all();
            $data['school_id'] = $school->id;
            $data['school_level_id'] = $schoolLevel->id;
            $this->courseService->updateCourse($course, $data);
            return redirect()->route('courses.index', ['school' => $school->cue, 'schoolLevel' => $schoolLevel->code])
                ->with('success', 'Curso actualizado exitosamente.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(School $school, SchoolLevel $schoolLevel, Course $course)
    {
        try {
            $this->courseService->deleteCourse($course);
            return redirect()->route('courses.index', ['school' => $school->cue, 'schoolLevel' => $schoolLevel->code])
                ->with('success', 'Curso eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
} 