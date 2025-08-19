<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\School\SchoolBaseController;
use App\Models\Entities\Course;
use App\Services\CourseService;
use App\Services\SchoolService;
use App\Services\SchoolLevelService;
use App\Services\SchoolShiftService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;
use App\Models\Entities\School;
use App\Models\Catalogs\SchoolLevel;
use Diglactic\Breadcrumbs\Breadcrumbs;

use Illuminate\Support\Facades\Log;

class CourseController extends SchoolBaseController
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
    }

    public function index(Request $request, School $school, SchoolLevel $schoolLevel)
    {
        $request->merge([
            'school_level_id' => $schoolLevel->id,
            'school_id' => $school->id,
            'year' => $request->input('year', date('Y')),
            'active' => $request->input('active'),
        ]);

        $courses = $this->courseService->getCourses($request, $school->id);
        $schoolShifts = $school->shifts;

        return Inertia::render('Courses/Index', [
            'courses' => $courses,
            'search' => $request->search,
            'year' => $request->year,
            'active' => $request->active,
            'shift' => $request->shift,
            'breadcrumbs' => Breadcrumbs::generate('school.courses', $school, $schoolLevel),
            'school' => $school,
            'selectedLevel' => $schoolLevel,
            'schoolShifts' => $schoolShifts,
        ]);
    }

    public function create(School $school, SchoolLevel $schoolLevel)
    {
        $schoolShifts = $school->shifts;

        return Inertia::render('Courses/Create', [
            'schoolShifts' => $schoolShifts,
            'school' => $school,
            'selectedLevel' => $schoolLevel,
            'breadcrumbs' => Breadcrumbs::generate('school.course.create', $school, $schoolLevel),
        ]);
    }

    public function createNext(Request $request, School $school, SchoolLevel $schoolLevel)
    {
        $doCreate = false;
        $courses = $this->courseService->calculateNextCourses($request, $school, $schoolLevel, $doCreate);
        $schoolShifts = $school->shifts;

        return Inertia::render('Courses/CreateNext', [
            'courses' => $courses,
            'breadcrumbs' => Breadcrumbs::generate('school.courses.create-next', $school, $schoolLevel),
            'school' => $school,
            'selectedLevel' => $schoolLevel,
            'schoolShifts' => $schoolShifts,
        ]);
    }

    public function store(Request $request)
    {
        $newCourse = $this->courseService->createCourse($request->all());
        return redirect()->route('school.course.show', ['school' => $newCourse->school->slug, 'schoolLevel' => $newCourse->schoolLevel->code, 'idAndLabel' => $newCourse->idAndLabel])->with('success', 'Curso creado correctamente');
    }

    public function edit(School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel)
    {
        $course = $this->getCourseFromUrlParameter($courseIdAndLabel);
        // $course->load(['school', 'schoolLevel', 'schoolShift', 'previousCourse']);
        $course->load(['previousCourse']);

        // $schools = $this->schoolService->getSchools(new Request(), true);
        $schoolLevels = $school->schoolLevels;
        $schoolShifts = $school->shifts;
        $courses = $this->courseService->getCoursesForSchool($school->id, $schoolLevel->id, null, true);

        return Inertia::render('Courses/Edit', [
            'course' => $course,
            'previousCourse' => $course->previousCourse,
            // 'schools' => $schools,
            'schoolLevels' => $schoolLevels,
            'schoolShifts' => $schoolShifts,
            'courses' => $courses,
            'school' => $school,
            'selectedLevel' => $schoolLevel,
            'breadcrumbs' => Breadcrumbs::generate('school.course.edit', $school, $schoolLevel, $course),
        ]);
    }

    public function update(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel)
    {
        $course = $this->getCourseFromUrlParameter($courseIdAndLabel);
        $this->courseService->updateCourse($course, $request->all());
        $school = $course->school;
        $schoolLevel = $course->schoolLevel;
        return redirect()->route('school.course.show', ['school' => $school->slug, 'schoolLevel' => $schoolLevel->code, 'idAndLabel' => $course->idAndLabel])->with('success', 'Curso actualizado correctamente');
    }

    public function destroy(Request $request, Course $course)
    {
        $this->courseService->deleteCourse($course);

        return $this->goBackToIndex($request, 'Curso eliminado correctamente');
    }

    public function search(Request $request, School $school, SchoolLevel $schoolLevel)
    {
        $request->merge([
            'school_level_id' => $schoolLevel->id,
            'school_id' => $school->id,
            'year' => $request->input('year', date('Y')),
            'active' => $request->input('active'),
        ]);

        $courses = $this->courseService->getCourses($request, $school->id);

        // Log::debug('🔍 search', [
        //     'courses_count' => count($courses),
        //     'request_data' => $request->all()
        // ]);

        // Return simple JSON response
        return response()->json([
            'courses' => $courses
        ]);
    }

    public function show(School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel)
    {
        $user = auth()->user();
        if ($user->isSuperadmin() || $user->hasPermissionToSchool('course.manage', $school->id)) {
            $view = 'Courses/Show';
        } else {
            $view = 'Courses/ShowForStudent'; //or guardian
        }
        $course = $this->getCourseFromUrlParameter($courseIdAndLabel);
        $course->load(['school', 'schoolLevel', 'schoolShift', 'previousCourse']);

        return Inertia::render($view, [
            'course' => $course,
            'school' => $school,
            'selectedLevel' => $schoolLevel,
            'breadcrumbs' => Breadcrumbs::generate('school.course.show', $school, $schoolLevel, $course),
        ]);
    }

    private function goBackToIndex(Request $request, string $message)
    {
        $school = School::find($request->school_id);
        $schoolLevel = SchoolLevel::find($request->school_level_id);

        return redirect()->route('school.courses', ['school' => $school->slug, 'schoolLevel' => $schoolLevel->code])->with('success', $message);
    }


    private function getCourseFromUrlParameter(string $idAndLabel)
    {
        $id = (int) explode('-', $idAndLabel)[0];
        $course = $id ? Course::where('id', $id)->first() : null;
        if (!$course) {
            // if (!$course) throw new \Exception('Curso no encontrado');
            abort(404, 'Curso no encontrado');
        }
        return $course;
    }
}
