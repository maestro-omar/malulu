<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\School\SchoolBaseController;
use App\Models\Entities\Course;
use App\Services\CourseService;
use App\Services\SchoolService;
use App\Services\SchoolLevelService;
use App\Services\SchoolShiftService;
use App\Services\FileService;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;
use App\Models\Entities\School;
use App\Models\Entities\User;
use App\Models\Catalogs\SchoolLevel;
use Diglactic\Breadcrumbs\Breadcrumbs;

use Illuminate\Support\Facades\Log;

class CourseController extends SchoolBaseController
{
    protected $courseService;
    protected $schoolService;
    protected $schoolLevelService;
    protected $schoolShiftService;
    protected $fileService;
    protected $attendanceService;

    public function __construct(
        CourseService $courseService,
        SchoolService $schoolService,
        SchoolLevelService $schoolLevelService,
        SchoolShiftService $schoolShiftService,
        FileService $fileService,
        AttendanceService $attendanceService
    ) {
        $this->courseService = $courseService;
        $this->schoolService = $schoolService;
        $this->schoolLevelService = $schoolLevelService;
        $this->schoolShiftService = $schoolShiftService;
        $this->fileService = $fileService;
        $this->attendanceService = $attendanceService;
    }

    public function index(Request $request, School $school, SchoolLevel $schoolLevel)
    {
        $request->merge([
            'school_level_id' => $schoolLevel->id,
            'school_id' => $school->id,
            'year' => $request->input('year', date('Y')),
            // 'active' => $request->input('active'),
        ]);

        $courses = $this->courseService->getCourses($request, $school->id);
        $schoolShifts = $school->shifts;

        return Inertia::render('Courses/Index', [
            'courses' => $courses,
            'year' => $request->year,
            'active' => $request->active,
            'shift' => $request->shift,
            'filters' => $request->only(['search', 'sort', 'direction']),
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
            'breadcrumbs' => Breadcrumbs::generate('school.course.create-next', $school, $schoolLevel),
            'school' => $school,
            'selectedLevel' => $schoolLevel,
            'schoolShifts' => $schoolShifts,
        ]);
    }

    public function storeNext(Request $request, School $school, SchoolLevel $schoolLevel)
    {
        $doCreate = true;
        $result = $this->courseService->calculateNextCourses($request, $school, $schoolLevel, $doCreate);

        Log::debug('🔍 storeNext', [
            'result' => $result
        ]);

        return redirect()->route('school.courses', [
            'school' => $school->slug,
            'schoolLevel' => $schoolLevel->code
        ])->with('success', 'Cursos creados correctamente');
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
        /** @var User $user */
        $user = auth()->user();
        if ($user->isSuperadmin() || $user->hasPermissionToSchool('course.manage', $school->id)) {
            $view = 'Courses/Show';
        } else {
            $view = 'Courses/ShowForStudent'; //or guardian
        }
        $course = $this->getCourseFromUrlParameter($courseIdAndLabel);
        $course->load(['school', 'schoolLevel', 'schoolShift', 'previousCourse', 'nextCourses']);


        $students = $this->courseService->getStudents($course, true, null, true);
        $teachers = $this->courseService->getTeachers($course);
        $files = $this->courseService->getFiles($course, $user);
        return Inertia::render($view, [
            'course' => $course,
            'school' => $school,
            'files' => $files,
            'students' => $students,
            'teachers' => $teachers,
            'selectedLevel' => $schoolLevel,
            'breadcrumbs' => Breadcrumbs::generate('school.course.show', $school, $schoolLevel, $course),
        ]);
    }

    public function export(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel)
    {
        $course = $this->getCourseFromUrlParameter($courseIdAndLabel);

        // Default export options (all true for testing purposes)
        $defaultExportOptions = [
            'basicData' => true,
            'schedule' => true,
            'teachers' => true,
            'students' => true,
            'attendance' => false
        ];

        // Get export options from request or use defaults
        if ($request->has('export_options')) {
            // Validate export options if provided
            $request->validate([
                'export_options' => 'required|array',
                'export_options.basicData' => 'boolean',
                'export_options.schedule' => 'boolean',
                'export_options.teachers' => 'boolean',
                'export_options.students' => 'boolean',
                'export_options.attendance' => 'boolean',
            ]);
            $exportOptions = $request->input('export_options');
        } else {
            // Use default options (all true) for testing
            $exportOptions = $defaultExportOptions;
        }

        // Call the exportCourse method from CourseService
        return $this->courseService->exportCourse($course, $exportOptions);
    }

    private function goBackToIndex(Request $request, string $message)
    {
        $school = School::find($request->school_id);
        $schoolLevel = SchoolLevel::find($request->school_level_id);

        return redirect()->route('school.courses', ['school' => $school->slug, 'schoolLevel' => $schoolLevel->code])->with('success', $message);
    }

    public function attendanceEdit(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel)
    {
        $course = $this->getCourseFromUrlParameter($courseIdAndLabel);
        $date = $request->input('date', date('Y-m-d'));
        $students = $this->courseService->getStudents($course, true, $date, false);

        // TODO: Implement attendance edit view
        return response()->json([
            'message' => 'Attendance edit functionality is work in progress',
            'students' => $students,
            'date' => $date
        ]);
    }
}
