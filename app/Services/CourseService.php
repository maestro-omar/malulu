<?php

namespace App\Services;

use App\Models\Entities\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\Relations\StudentCourse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CourseService
{
    /**
     * Get courses with filters
     */
    public function getCourses(Request $request, ?int $schoolId = null)
    {
        $query = Course::query()
            ->with(['school', 'schoolLevel', 'schoolShift', 'previousCourse'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('number', 'like', "%{$search}%")
                        ->orWhere('letter', 'like', "%{$search}%")
                        ->orWhereHas('school', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('schoolLevel', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('schoolShift', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($schoolId !== null, function ($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->when($request->input('school_level_id'), function ($query, $schoolLevelId) {
                $query->where('school_level_id', $schoolLevelId);
            })
            ->when($request->input('shift'), function ($query, $schoolShiftCode) {
                $query->whereHas('schoolShift', function ($q) use ($schoolShiftCode) {
                    $q->where('code', $schoolShiftCode);
                });
            })
            ->when($request->input('active') !== null, function ($query) use ($request) {
                $query->where('active', $request->boolean('active'));
            })
            ->when($request->input('year'), function ($query, $year) {
                $query->whereYear('start_date', $year);
            });

        $courses = $query->orderBy('number')->orderBy('letter')->paginate(10);

        return $courses->appends($request->only(['search', 'school_level_id', 'school_id', 'year', 'active', 'shift']))->through(function ($course) use ($request) {
            return $course;
        })->withQueryString()->toArray();
    }

    /**
     * Validate course data
     */
    public function validateCourseData(array $data, ?Course $course = null)
    {
        $rules = [
            'school_id' => [
                'required',
                Rule::exists('schools', 'id')
            ],
            'school_level_id' => [
                'required',
                Rule::exists('school_levels', 'id')
            ],
            'school_shift_id' => [
                'required',
                Rule::exists('school_shifts', 'id')
            ],
            'previous_course_id' => [
                'nullable',
                Rule::exists('courses', 'id')
            ],
            'number' => [
                'required',
                'integer',
                'min:1',
                'max:9'
            ],
            'letter' => [
                'required',
                'string',
                'size:1',
                'regex:/^[A-Z]$/'
            ],
            'start_date' => [
                'required',
                'date'
            ],
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date'
            ],
            'active' => [
                'boolean'
            ]
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Create a new course
     */
    public function createCourse(array $data)
    {
        $validatedData = $this->validateCourseData($data);
        return Course::create($validatedData);
    }

    /**
     * Update an existing course
     */
    public function updateCourse(Course $course, array $data)
    {
        $validatedData = $this->validateCourseData($data, $course);
        $course->update($validatedData);
        return $course;
    }

    /**
     * Delete a course
     */
    public function deleteCourse(Course $course)
    {
        return $course->delete();
    }

    /**
     * Get active courses for a school
     */
    public function getCoursesForSchool(?int $schoolId, ?int $levelId, ?int $shiftId, ?bool $active)
    {
        return Course::when($schoolId !== null, function ($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        })
            ->when($levelId !== null, function ($query) use ($levelId) {
                $query->where('school_level_id', $levelId);
            })
            ->when($shiftId !== null, function ($query) use ($shiftId) {
                $query->where('school_shift_id', $shiftId);
            })
            ->when($active !== null, function ($query) use ($active) {
                $query->where('active', $active);
            })
            ->with(['school', 'schoolShift', 'schoolLevel'])
            ->orderBy('number')
            ->orderBy('letter')
            ->get();
    }

    /**
     * Enroll a student to a course.
     *
     * @param int $roleRelationshipId
     * @param int $courseId
     * @param array $data (optional: start_date, end_reason_id, notes, etc.)
     * @return StudentCourse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function enrollStudentToCourse(int $roleRelationshipId, int $courseId, array $data = [])
    {
        // Validate input
        $rules = [
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'end_reason_id' => ['nullable', 'exists:student_course_end_reasons,id'],
            'notes' => ['nullable', 'string'],
        ];

        $data = array_merge([
            'start_date' => now()->toDateString(),
        ], $data);

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Check if already enrolled (active)
        $existing = StudentCourse::where('role_relationship_id', $roleRelationshipId)
            ->where('course_id', $courseId)
            ->whereNull('end_date')
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'enrollment' => ['El estudiante ya se encontraba vinculado al curso.'],
            ]);
        }

        // Create enrollment
        return StudentCourse::create([
            'role_relationship_id' => $roleRelationshipId,
            'course_id' => $courseId,
            'start_date' => $data['start_date'],
            'end_reason_id' => $data['end_reason_id'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => $data['created_by'] ?? null,
        ]);
    }

    /**
     * Assign a course to a teacher.
     *
     * @param int $roleRelationshipId
     * @param int $courseId
     * @param array $data (optional: start_date, end_date, in_charge, notes, etc.)
     * @return TeacherCourse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function assignCourseToTeacher(int $roleRelationshipId, int $courseId, array $data = [])
    {
        // Validate input
        $rules = [
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'in_charge' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];

        $data = array_merge([
            'start_date' => now()->toDateString(),
        ], $data);

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Check if already assigned (active)
        $existing = \App\Models\Relations\TeacherCourse::where('role_relationship_id', $roleRelationshipId)
            ->where('course_id', $courseId)
            ->whereNull('end_date')
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'assignment' => ['El docente ya se encontraba asignado a este curso.'],
            ]);
        }

        // Create assignment
        return \App\Models\Relations\TeacherCourse::create([
            'role_relationship_id' => $roleRelationshipId,
            'course_id' => $courseId,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'in_charge' => $data['in_charge'] ?? false,
            'notes' => $data['notes'] ?? null,
            'created_by' => $data['created_by'] ?? null,
        ]);
    }

    public function parseTeacherCourses(Collection|array $teacherCourses)
    {
        if (!is_array($teacherCourses))
            $teacherCourses = $teacherCourses->all();
        foreach ($teacherCourses as $teacherCourse) {
            dd($teacherCourse->course);
        }
    }
}
