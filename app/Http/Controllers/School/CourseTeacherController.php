<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\School\SchoolBaseController;
use App\Models\Entities\School;
use App\Models\Catalogs\SchoolLevel;
use App\Services\TeacherService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Diglactic\Breadcrumbs\Breadcrumbs;

class CourseTeacherController extends SchoolBaseController
{
    public function __construct(
        protected TeacherService $teacherService
    ) {}

    /**
     * Show the assign-teacher page: search staff and assign to course.
     */
    public function create(School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel): Response
    {
        $course = $this->getCourseFromUrlParameter($courseIdAndLabel);
        $course->load(['school', 'schoolLevel', 'schoolShift']);

        return Inertia::render('Courses/EnrollTeacher', [
            'course' => $course,
            'school' => $school,
            'selectedLevel' => $schoolLevel,
            'createStaffUrl' => route('school.staff.create', ['school' => $school->slug]),
            'breadcrumbs' => Breadcrumbs::generate('school.course.teacher.enroll', $school, $schoolLevel, $course),
        ]);
    }

    /**
     * Search staff of the school for course assignment (exclude already in this course).
     */
    public function searchTeachers(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel)
    {
        $course = $this->getCourseFromUrlParameter($courseIdAndLabel);

        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $search = $request->query('search') ?? $request->input('search');
        $teachers = $this->teacherService->searchTeachersForCourse($school->id, $course->id, $search);

        return response()->json(['teachers' => $teachers]);
    }

    /**
     * Assign a teacher to the course.
     */
    public function store(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel)
    {
        $course = $this->getCourseFromUrlParameter($courseIdAndLabel);

        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'in_charge' => ['nullable', 'boolean'],
        ]);

        $result = $this->teacherService->assignTeacherToCourse(
            (int) $request->user_id,
            $school->id,
            $course->id,
            $request->user()->id,
            $request->boolean('in_charge')
        );

        if (isset($result['error'])) {
            return back()->withErrors(['assignment' => $result['error']]);
        }

        return redirect()
            ->route('school.course.show', [
                'school' => $school->slug,
                'schoolLevel' => $schoolLevel->code,
                'idAndLabel' => $course->id_and_label,
            ])
            ->with('success', $result['message']);
    }
}
