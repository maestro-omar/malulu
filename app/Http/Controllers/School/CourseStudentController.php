<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\School\SchoolBaseController;
use App\Models\Entities\Course;
use App\Models\Entities\School;
use App\Models\Catalogs\SchoolLevel;
use App\Models\Catalogs\StudentCourseEndReason;
use App\Services\CourseService;
use App\Services\StudentService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Diglactic\Breadcrumbs\Breadcrumbs;

class CourseStudentController extends SchoolBaseController
{
    public function __construct(
        protected CourseService $courseService,
        protected StudentService $studentService,
        protected UserService $userService
    ) {}

    /**
     * Show the enrollment page: search existing students and option to create new student.
     */
    public function create(School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel): Response
    {
        $course = $this->getCourseFromUrlParameter($courseIdAndLabel);
        $course->load(['school', 'schoolLevel', 'schoolShift', 'previousCourse', 'nextCourses']);

        $endReasons = StudentCourseEndReason::active()->orderBy('name')->get(['id', 'code', 'name']);

        return Inertia::render('Courses/EnrollStudent', [
            'course' => $course,
            'school' => $school,
            'selectedLevel' => $schoolLevel,
            'endReasons' => $endReasons,
            'createStudentUrl' => route('school.students.create', ['school' => $school->slug]) . '?course=' . urlencode($course->idAndLabel),
            'breadcrumbs' => Breadcrumbs::generate('school.course.student.enroll', $school, $schoolLevel, $course),
        ]);
    }

    /**
     * Search students of the school for enrollment (exclude already in this course).
     * Uses the same UserService::getStudentsBySchool as the school students list.
     * Returns: id, firstname, lastname, birthdate, age, current_course (if any).
     */
    public function searchStudents(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel)
    {
        $course = $this->getCourseFromUrlParameter($courseIdAndLabel);

        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $request->merge(['per_page' => 50]);
        $paginated = $this->userService->getStudentsBySchool($request, $school->id);
        $data = $paginated['data'] ?? [];

        $courseId = (int) $course->id;
        $students = [];
        foreach ($data as $user) {
            $currentCourse = $user['course'] ?? null;
            if ($currentCourse && (int) ($currentCourse['id'] ?? 0) === $courseId) {
                continue; // already in this course
            }
            $birthdate = $user['birthdate'] ?? null;
            $birthdateStr = null;
            $age = null;
            if ($birthdate) {
                $birthdateStr = is_string($birthdate) ? $birthdate : (\Carbon\Carbon::parse($birthdate)->format('Y-m-d'));
                $age = \Carbon\Carbon::parse($birthdate)->diffInYears(now());
            }
            $students[] = [
                'id' => $user['id'],
                'name' => $user['name'] ?? '',
                'firstname' => $user['firstname'] ?? '',
                'lastname' => $user['lastname'] ?? '',
                'id_number' => $user['id_number'] ?? null,
                'birthdate' => $birthdateStr,
                'age' => $age,
                'gender' => $user['gender'] ?? null,
                'picture' => $user['picture'] ?? null,
                'current_course' => $currentCourse ? [
                    'id' => $currentCourse['id'],
                    'nice_name' => $currentCourse['nice_name'] ?? $currentCourse['id_and_label'] ?? '',
                ] : null,
            ];
        }

        return response()->json(['students' => $students]);
    }

    /**
     * Enroll a student in the course.
     * If already in this course: return error.
     * If in another course: require end_reason_id, end previous and create new.
     * If not in any course: create new enrollment.
     */
    public function store(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel)
    {
        $course = $this->getCourseFromUrlParameter($courseIdAndLabel);

        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'end_reason_id' => ['nullable', 'integer', 'exists:student_course_end_reasons,id'],
            'enrollment_reason' => ['nullable', 'string'],
        ]);

        $result = $this->studentService->enrollStudentInCourse(
            (int) $request->user_id,
            $school->id,
            $course->id,
            $request->user()->id,
            $request->input('end_reason_id') ? (int) $request->end_reason_id : null,
            $request->input('enrollment_reason')
        );

        if (isset($result['error'])) {
            return back()->withErrors(['enrollment' => $result['error']]);
        }

        return redirect()
            ->route('school.course.show', [
                'school' => $school->slug,
                'schoolLevel' => $schoolLevel->code,
                'idAndLabel' => $course->idAndLabel,
            ])
            ->with('success', $result['message']);
    }
}
