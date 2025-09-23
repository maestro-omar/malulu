<?php

namespace App\Services;

use App\Models\Entities\Course;
use App\Models\Entities\User;
use App\Models\Relations\TeacherCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\Relations\StudentCourse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use App\Services\FileService;
use App\Services\AttendanceService;
use App\Services\PaginationTrait;
use App\Traits\CourseNext;
use App\Exports\CourseExport;
use Maatwebsite\Excel\Facades\Excel;

class CourseService
{
    use CourseNext, PaginationTrait;

    protected $userservice, $fileService, $attendanceService;

    public function __construct(UserService $userservice, FileService $fileService, AttendanceService $attendanceService)
    {
        $this->userservice = $userservice;
        $this->fileService = $fileService;
        $this->attendanceService = $attendanceService;
    }

    /**
     * Get courses with filters
     */
    public function getCourses(Request $request, ?int $schoolId = null)
    {
        $expectedFilters = ['search', 'school_level_id', 'school_id', 'year', 'active', 'shift', 'school_shift_id', 'no_next', 'per_page', 'sort', 'direction'];

        $query = Course::query()
            ->with(['school', 'schoolLevel', 'schoolShift', 'previousCourse', 'nextCourses'])
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
            ->when($request->input('school_shift_id'), function ($query, $shiftId) {
                $query->where('school_shift_id', $shiftId);
            })
            ->when($request->input('shift'), function ($query, $shift) {
                $query->whereHas('schoolShift', function ($q) use ($shift) {
                    // If shift is numeric (ID), search by ID, otherwise search by code
                    if (is_numeric($shift)) {
                        $q->where('id', (int) $shift);
                    } else {
                        $q->where('code', $shift);
                    }
                });
            })
            ->when($request->input('active') !== null, function ($query) use ($request) {
                $query->where('active', $request->boolean('active'));
            })
            ->when($request->input('year'), function ($query, $year) {
                $query->whereYear('start_date', $year);
            })
            ->when($request->input('no_next'), function ($query) {
                $query->whereDoesntHave('nextCourses');
            });

        // Add sorting
        $query = $this->addSorting($request, $query);

        // Handle pagination
        if ($request->input('no_paginate')) {
            $result = $query->get()->toArray();
        } else {
            $perPage = $request->input('per_page', 10);
            $courses = $this->handlePagination($query, $perPage, 10);
            $result = $courses->appends($request->only($expectedFilters))->withQueryString()->toArray();
        }

        Log::debug('getCourses input', [
            'filters' => $request->only($expectedFilters),
            'per_page' => $request->input('per_page'),
            'result_count' => count($result['data'] ?? [])
        ]);

        return $result;
    }

    /**
     * Validate course data
     */
    public function validateCourseData(array $data, ?Course $course = null)
    {
        $data['letter'] = strtoupper($data['letter'] ?? '');
        $lastDayOfNextTwoYears = now()->addYears(2)->endOfYear()->toDateString();
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
            'name' => [
                'nullable',
                'string',
            ],
            'letter' => [
                'required',
                'string',
                'size:1',
                'regex:/^[A-Z]$/'
            ],
            'start_date' => [
                'required',
                'date',
                'before_or_equal:' . $lastDayOfNextTwoYears,
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
        $existing = TeacherCourse::where('role_relationship_id', $roleRelationshipId)
            ->where('course_id', $courseId)
            ->whereNull('end_date')
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'assignment' => ['El docente ya se encontraba asignado a este curso.'],
            ]);
        }

        // Create assignment
        return TeacherCourse::create([
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
        $ret = [];
        foreach ($teacherCourses as $teacherCourse) {
            $ret[] = $this->parseTeacherCourse($teacherCourse);
        }
        $ret = array_filter($ret);
        return $ret;
    }

    public function parseTeacherCourse(?TeacherCourse $teacherCourse)
    {
        if (empty($teacherCourse) || empty($teacherCourse->course)) return null;
        $course = $teacherCourse->course;
        $data = $course->toArray();
        $data['url'] = route('school.course.show', [
            'school' => $course->school->slug,
            'schoolLevel' => $course->schoolLevel->code,
            'idAndLabel' => $course->idAndLabel
        ]);
        $data['attendance_url'] = route('school.course.attendance.edit', [
            'school' => $course->school->slug,
            'schoolLevel' => $course->schoolLevel->code,
            'idAndLabel' => $course->idAndLabel
        ]);
        return $data;
    }


    public function getStudents(Course $course, bool $withGuardians, ?string $attendanceDate, bool $withAttendanceSu): array
    {
        $students = $course->courseStudents->load(['roleRelationship.user', 'endReason']);
        $studentsIds = $students->pluck('roleRelationship.user.id')->toArray();
        $attendanceMinimalSummary = $this->attendanceService->getStudentsAttendanceMinimal($studentsIds, null, null);
        // student_relationships OMAR PREGUNTA ¿esta relacion es redundante? ¿estuvo hecha para facilitar búsquedas?
        $parsedStudents = $students->map(function ($oneRel) use ($course, $withGuardians, $attendanceDate, $attendanceMinimalSummary) {
            return $this->parseRelatedStudent($course, $oneRel, $withGuardians, $attendanceDate, $attendanceMinimalSummary);
        }, $students);
        $parsedStudents = $parsedStudents->sortBy([['rel.end_date'], ['user.lastname'], ['user.firstname']]);
        return $parsedStudents->values()->all();
    }

    public function getTeachers(Course $course): array
    {
        $teachers = $course->courseTeachers->load(['roleRelationship.user',]);
        $parsedTeachers =  $teachers->map(function ($oneRel) {
            return $this->parseRelatedTeacher($oneRel);
        });
        $parsedTeachers = $parsedTeachers->sortBy([['rel.in_charge'], ['user.lastname'], ['user.firstname']]);
        return $parsedTeachers->values()->all();
    }

    private function parseRelatedStudent(Course $course, object $studentRel, bool $withGuardians, ?string $attendanceDate, ?array $entireCourseAttendanceSummary)
    {
        $user = $studentRel->roleRelationship->user->load(['province']);
        $student = [
            "rel_id" => $studentRel->id,
            "id" => $user->id,
            "name" => $user->name,
            "email" => $user->email,
            "firstname" => $user->firstname,
            "lastname" => $user->lastname,
            "id_number" => $user->id_number,
            "gender" => User::getGenderName($user->gender, true),
            "birthdate" => $user->birthdate->format('Y-m-d'),
            "age" => $user->birthdate->diffInYears(now()),
            "phone" => $user->phone,
            "address" => $user->address,
            "locality" => $user->locality,
            "province" => $user->province->name,
            "nationality" => $user->nationality,
            "picture" => $user->picture,
            "rel_start_date" => $studentRel->start_date->format('Y-m-d'),
            "rel_end_date" => $studentRel->end_date ? $studentRel->end_date->format('Y-m-d') : null,
            "rel_end_reason" => $studentRel->end_reason_id ? $studentRel->endReason->name : null,
            "rel_notes" => $studentRel->notes,
            "rel_custom_fields" => $studentRel->custom_fields,
        ];
        if ($withGuardians) {
            $student["guardians"] = $this->userservice->getStudentParents($user);
        }
        if (!empty($attendanceDate)) {
            $student["attendance"] = $this->attendanceService->getStudentAttendance($user, $course, $attendanceDate);
        }
        if (!empty($entireCourseAttendanceSummary)) {
            $student["attendanceSummary"] = $entireCourseAttendanceSummary[$user->id] ?? null;
        }
        return $student;
    }

    private function parseRelatedTeacher(object $teacherRel)
    {
        $roleRel = $teacherRel->roleRelationship;
        $workerRel = $roleRel->workerRelationship; // is it ok if it is null? how should i handle it?
        $subject = $workerRel ? $workerRel->classSubject : null; //can be null
        $role = $roleRel->role;
        $user = $roleRel->user->load(['province']);
        $teacher = [
            "rel_id" => $teacherRel->id,
            "id" => $user->id,
            "name" => $user->name,
            "firstname" => $user->firstname,
            "lastname" => $user->lastname,
            "email" => $user->email,
            "id_number" => $user->id_number,
            "gender" => User::getGenderName($user->gender, true),
            "birthdate" => $user->birthdate->format('Y-m-d'),
            "age" => $user->birthdate->diffInYears(now()),
            "phone" => $user->phone,
            "address" => $user->address,
            "locality" => $user->locality,
            "province" => $user->province->name,
            "nationality" => $user->nationality,
            "picture" => $user->picture,
            "rel_start_date" => $teacherRel->start_date->format('Y-m-d'),
            "rel_end_date" => $teacherRel->end_date ? $teacherRel->end_date->format('Y-m-d') : null,
            "rel_in_charge" => $teacherRel->in_charge,
            "rel_notes" => $teacherRel->notes,
            "rel_subject" => $subject ? $subject->name : null,
            'rel_role' => $role,
        ];
        return $teacher;
    }

    public function getFiles(Course $course, User $loggedUser)
    {
        return $this->fileService->getCourseFiles($course, $loggedUser);
    }

    /**
     * Add sorting to the query
     */
    private function addSorting(Request $request, $query)
    {
        if ($request->filled('sort')) {
            $sort = $request->input('sort');
            $direction = $request->input('direction', 'asc');

            // Validate sort field and direction
            if (!$this->isValidSortField($sort) || !in_array($direction, ['asc', 'desc'])) {
                return $query;
            }

            // Handle relationship-based sorting
            if ($this->isRelationshipSortField($sort)) {
                $this->addRelationshipSorting($query, $sort, $direction);
            } else {
                // Direct course table field sorting
                $query->orderBy("courses.{$sort}", $direction);
            }
        } else {
            // Default sorting
            $query->orderBy('courses.number')->orderBy('courses.letter');
        }
        return $query;
    }

    /**
     * Validate if the sort field is allowed
     */
    private function isValidSortField(string $field): bool
    {
        $validDirectFields = [
            'id',
            'number',
            'letter',
            'name',
            'start_date',
            'end_date',
            'active',
            'created_at',
            'updated_at'
        ];

        $validRelationshipFields = [
            'shift',    // from schoolShift
            'level'     // from schoolLevel
        ];

        return in_array($field, $validDirectFields) || in_array($field, $validRelationshipFields);
    }

    /**
     * Check if field requires relationship joins
     */
    private function isRelationshipSortField(string $field): bool
    {
        return in_array($field, ['shift', 'level']);
    }

    /**
     * Add sorting for relationship-based fields
     */
    private function addRelationshipSorting($query, string $field, string $direction)
    {
        switch ($field) {
            case 'shift':
                $query->leftJoin('school_shifts', 'courses.school_shift_id', '=', 'school_shifts.id')
                    ->select('courses.*', 'school_shifts.name as shift_name')
                    ->orderBy('school_shifts.name', $direction)
                    ->distinct();
                break;

            case 'level':
                $query->leftJoin('school_levels', 'courses.school_level_id', '=', 'school_levels.id')
                    ->select('courses.*', 'school_levels.name as level_name')
                    ->orderBy('school_levels.name', $direction)
                    ->distinct();
                break;
        }
    }

    /**
     * Export course data based on selected options
     */
    public function exportCourse(Course $course, array $exportOptions)
    {
        $filename = $course->nice_name . '-' . now()->toISOString() . '.xlsx';
        $return = Excel::download(
            new CourseExport($this, $course, $exportOptions),
            $filename
        );
        return $return;

        // For now, return JSON response
        // Later you can implement actual file generation (PDF, Excel, etc.)
        // return response()->json([
        //     'success' => true,
        //     'message' => 'Course data exported successfully',
        //     'course_name' => $course->nice_name,
        //     'exported_at' => now()->toISOString(),
        //     'file' => $return
        // ]);
    }
}
