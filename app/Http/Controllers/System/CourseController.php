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
use App\Http\Controllers\System\SystemBaseController;
use Diglactic\Breadcrumbs\Breadcrumbs;

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
    ) {
        $this->courseService = $courseService;
        $this->schoolService = $schoolService;
        $this->schoolLevelService = $schoolLevelService;
        $this->schoolShiftService = $schoolShiftService;
        $this->middleware('permission:superadmin');
    }

    public function index(Request $request, School $school, SchoolLevel $schoolLevel)
    {
        $request->merge([
            'school_level_id' => $schoolLevel->id,
            'school_id' => $school->id,
            'year' => $request->input('year', date('Y')),
            'active' => $request->has('active') ? $request->boolean('active') : null,
            'shift' => $request->input('shift'),
        ]);

        $courses = $this->courseService->getCourses($request, $school->id);

        return Inertia::render('Courses/Index', [
            'courses' => $courses,
            'search' => $request->search,
            'year' => $request->year,
            'active' => $request->active,
            'shift' => $request->shift,
            'breadcrumbs' => Breadcrumbs::generate('courses.index', $school, $schoolLevel),
            'school' => $school,
            'selectedLevel' => $schoolLevel,
        ]);
    }

    public function create(School $school, SchoolLevel $schoolLevel)
    {
        $schools = $this->schoolService->getSchools(new Request(), true);
        $schoolLevels = $this->schoolLevelService->getSchoolLevels(new Request());
        $schoolShifts = $this->schoolShiftService->getSchoolShifts(new Request(), true);
        $courses = $this->courseService->getCoursesForSchool($school->id, $schoolLevel->id, null, true);

        return Inertia::render('Courses/Create', [
            'schools' => $schools,
            'schoolLevels' => $schoolLevels,
            'schoolShifts' => $schoolShifts,
            'courses' => $courses,
            'school' => $school,
            'selectedLevel' => $schoolLevel,
            'breadcrumbs' => Breadcrumbs::generate('courses.create', $school, $schoolLevel),
        ]);
    }

    public function store(Request $request)
    {
        $this->courseService->createCourse($request->validated());

        return redirect()->route('courses.index')->with('success', 'Course created successfully!');
    }

    public function edit(School $school, SchoolLevel $schoolLevel, Course $course)
    {
        $schools = $this->schoolService->getSchools(new Request(), true);
        $schoolLevels = $this->schoolLevelService->getSchoolLevels(new Request());
        $schoolShifts = $this->schoolShiftService->getSchoolShifts(new Request());
        $courses = $this->courseService->getCoursesForSchool($school->id, $schoolLevel->id, null, true);

        return Inertia::render('Courses/Edit', [
            'course' => $course,
            'schools' => $schools,
            'schoolLevels' => $schoolLevels,
            'schoolShifts' => $schoolShifts,
            'courses' => $courses,
            'school' => $school,
            'selectedLevel' => $schoolLevel,
            'breadcrumbs' => Breadcrumbs::generate('courses.edit', $school, $schoolLevel, $course),
        ]);
    }

    public function update(Request $request, Course $course)
    {
        $this->courseService->updateCourse($course, $request->validated());

        return redirect()->route('courses.index')->with('success', 'Course updated successfully!');
    }

    public function destroy(Course $course)
    {
        $this->courseService->deleteCourse($course);

        return redirect()->route('courses.index')->with('success', 'Course deleted successfully!');
    }

    public function show(School $school, SchoolLevel $schoolLevel, Course $course)
    {
        $course->load(['school', 'schoolLevel', 'schoolShift', 'previousCourse']);

        return Inertia::render('Courses/Show', [
            'course' => $course,
            'school' => $school,
            'selectedLevel' => $schoolLevel,
            'breadcrumbs' => Breadcrumbs::generate('courses.show', $school, $schoolLevel, $course),
        ]);
    }
}
