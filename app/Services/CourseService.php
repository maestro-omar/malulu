<?php

namespace App\Services;

use App\Models\Entities\Course;
use App\Models\Entities\User;
use App\Models\Relations\TeacherCourse;
use App\Models\Catalogs\ClassSubject;
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
            ->withCount([
                'activeEnrollments as active_enrolled_count',
                'courseStudents as once_enrolled_count',
            ])
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
            ->when(($activeFilter = $this->resolveActiveFilter($request)) !== null, function ($query) use ($activeFilter) {
                $query->where('active', $activeFilter);
            })
            ->when($request->input('year'), function ($query, $year) {
                $yearStart = "{$year}-01-01";
                $yearEnd = "{$year}-12-31";
                $query->where('start_date', '<=', $yearEnd)
                    ->whereRaw('COALESCE(end_date, DATE_ADD(start_date, INTERVAL 1 YEAR)) >= ?', [$yearStart]);
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
            $perPage = $request->input('per_page', 40);
            $courses = $this->handlePagination($query, $perPage, 40);
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
     * Resolve active filter from request (handles scalar or array active[label]/active[value] from query string).
     */
    private function resolveActiveFilter(Request $request): ?bool
    {
        $active = $request->input('active');
        if ($active === null) {
            return null;
        }
        if (is_array($active) && array_key_exists('value', $active)) {
            $active = $active['value'];
        }
        if ($active === '' || $active === null || (is_string($active) && strtolower($active) === 'null')) {
            return null;
        }
        return filter_var($active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? null;
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
            'enrollment_reason' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'custom_fields' => ['nullable', 'array'],
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
            'enrollment_reason' => $data['enrollment_reason'] ?? null,
            'notes' => $data['notes'] ?? null,
            'custom_fields' => $data['custom_fields'] ?? null,
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
        $existingActive = TeacherCourse::where('role_relationship_id', $roleRelationshipId)
            ->where('course_id', $courseId)
            ->whereNull('end_date')
            ->first();

        if ($existingActive) {
            throw ValidationException::withMessages([
                'assignment' => ['El docente ya está asignado a este curso.'],
            ]);
        }

        // If there is a past assignment (ended), reopen it instead of creating a duplicate
        $pastAssignment = TeacherCourse::where('role_relationship_id', $roleRelationshipId)
            ->where('course_id', $courseId)
            ->whereNotNull('end_date')
            ->first();

        if ($pastAssignment) {
            $pastAssignment->end_date = null;
            $pastAssignment->start_date = \Carbon\Carbon::parse($data['start_date']);
            $pastAssignment->in_charge = $data['in_charge'] ?? false;
            $pastAssignment->notes = $data['notes'] ?? null;
            $pastAssignment->save();
            return $pastAssignment;
        }

        // Create new assignment
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


    /** Enrollment scope: 'active' = end_date IS NULL, 'past' = end_date IS NOT NULL, 'all' = no filter */
    public const STUDENTS_SCOPE_ACTIVE = 'active';
    public const STUDENTS_SCOPE_PAST = 'past';
    public const STUDENTS_SCOPE_ALL = 'all';

    /** Teacher assignment scope: same as students */
    public const TEACHERS_SCOPE_ACTIVE = 'active';
    public const TEACHERS_SCOPE_PAST = 'past';
    public const TEACHERS_SCOPE_ALL = 'all';

    /**
     * @param Course $course
     * @param bool $withGuardians
     * @param string|null $attendanceDate
     * @param bool $withAttendanceSummary
     * @param string $enrollmentScope 'active' (default), 'past', or 'all'
     * @return array
     */
    public function getStudents(Course $course, bool $withGuardians, ?string $attendanceDate, bool $withAttendanceSummary, string $enrollmentScope = self::STUDENTS_SCOPE_ACTIVE): array
    {
        $query = $course->courseStudents();
        if ($enrollmentScope === self::STUDENTS_SCOPE_ACTIVE) {
            $query->whereNull('end_date');
        } elseif ($enrollmentScope === self::STUDENTS_SCOPE_PAST) {
            $query->whereNotNull('end_date');
        }
        // 'all' = no extra constraint
        $students = $query->get()->load([
            'roleRelationship.user',
            'roleRelationship.studentRelationship.currentCourse.school',
            'roleRelationship.studentRelationship.currentCourse.schoolLevel',
            'endReason',
        ]);
        $studentsIds = $students->pluck('roleRelationship.user.id')->toArray();
        $attendanceMinimalSummary = $withAttendanceSummary ? $this->attendanceService->getStudentsAttendanceMinimal($studentsIds, null, null) : null;
        $parsedStudents = $students->map(function ($oneRel) use ($course, $withGuardians, $attendanceDate, $attendanceMinimalSummary) {
            return $this->parseRelatedStudent($course, $oneRel, $withGuardians, $attendanceDate, $attendanceMinimalSummary);
        }, $students);
        $parsedStudents = $parsedStudents->sortBy([['rel_end_date'], ['lastname'], ['firstname']]);
        return $parsedStudents->values()->all();
    }

    /**
     * @param string $scope TEACHERS_SCOPE_ACTIVE (default), TEACHERS_SCOPE_PAST, or TEACHERS_SCOPE_ALL
     */
    public function getTeachers(Course $course, string $scope = self::TEACHERS_SCOPE_ACTIVE): array
    {
        $query = $course->courseTeachers();
        if ($scope === self::TEACHERS_SCOPE_ACTIVE) {
            $query->whereNull('end_date');
        } elseif ($scope === self::TEACHERS_SCOPE_PAST) {
            $query->whereNotNull('end_date');
        }
        $teachers = $query->get()->load(['roleRelationship.user', 'roleRelationship.role', 'roleRelationship.workerRelationship.classSubject']);
        $parsedTeachers = $teachers->map(function ($oneRel) {
            return $this->parseRelatedTeacher($oneRel);
        });
        if ($scope === self::TEACHERS_SCOPE_PAST) {
            $parsedTeachers = $parsedTeachers->sortBy([['rel_end_date'], ['lastname'], ['firstname']]);
        } else {
            $parsedTeachers = $parsedTeachers->sortBy([['rel_in_charge', true], ['lastname'], ['firstname']]);
        }
        return $parsedTeachers->values()->all();
    }

    private function parseRelatedStudent(Course $course, object $studentRel, bool $withGuardians, ?string $attendanceDate, ?array $entireCourseAttendanceSummary)
    {
        // dd($course);
        $user = $studentRel->roleRelationship->user->load(['province']);
        $student = [
            "rel_id" => $studentRel->id,
            "id" => $user->id,
            "name" => $user->name,
            "firstname" => $user->firstname,
            "lastname" => $user->lastname,
            "email" => $user->email,
            "id_number" => $user->id_number,
            "gender" => User::getGenderName($user->gender, true),
            "birthdate" => $user->birthdate ? $user->birthdate->format('Y-m-d') : null,
            "age" => $user->birthdate ? $user->birthdate->diffInYears(now()) : null,
            "phone" => $user->phone,
            "address" => $user->address,
            "locality" => $user->locality,
            "province" => $user->province->name,
            "birth_place" => $user->birth_place,
            "nationality" => $user->nationality,
            "picture" => $user->picture,
            "critical_info" => $user->critical_info,
            "occupation" => $user->occupation,
            "diagnoses_data" => $user->diagnoses_data,
            "rel_start_date" => $studentRel->start_date ? $studentRel->start_date->format('Y-m-d') : null,
            "rel_end_date" => $studentRel->end_date ? $studentRel->end_date->format('Y-m-d') : null,
            "rel_end_reason" => $studentRel->end_reason_id ? $studentRel->endReason->name : null,
            "rel_notes" => $studentRel->notes,
            "rel_custom_fields" => $studentRel->custom_fields,
        ];
        $studentRelationship = $studentRel->roleRelationship->studentRelationship ?? null;
        $currentCourse = $studentRelationship?->currentCourse;
        if ($currentCourse) {
            $student['current_course'] = [
                'id' => $currentCourse->id,
                'nice_name' => $currentCourse->nice_name,
                'url' => route('school.course.show', [
                    'school' => $currentCourse->school->slug,
                    'schoolLevel' => $currentCourse->schoolLevel->code,
                    'idAndLabel' => $currentCourse->id_and_label,
                ]),
            ];
        } else {
            $student['current_course'] = null;
        }
        if ($withGuardians) {
            $student["guardians"] = $this->userservice->getStudentParents($user);
            if (empty($student["phone"]) && !empty($student["guardians"])) {
                $student["phone"] = $this->getStudentMainPhone($student["guardians"]);
            }
        }
        if (!empty($attendanceDate)) {
            $student["attendanceForDate"] = $this->attendanceService->getStudentAttendance($user, $attendanceDate);
        }
        if (!empty($entireCourseAttendanceSummary)) {
            $student["attendanceSummary"] = $entireCourseAttendanceSummary[$user->id] ?? null;
        }
        return $student;
    }

    private function getStudentMainPhone(array $guardians)
    {
        $selected = null;
        foreach ($guardians as $guardian) {
            $checked = !empty($guardian["phone"]) && !($guardian["is_restricted"] ?? false);
            if ((empty($selected) && $checked)
                || (!empty($selected) && $checked && !($selected['is_emergency_contact'] ?? false) && ($guardian['is_emergency_contact'] ?? false))
                || (!empty($selected) && $checked && ($selected['is_emergency_contact'] ?? false) == ($guardian['is_emergency_contact'] ?? false)  && ($selected['emergency_contact_priority'] ?? 0) > ($guardian['emergency_contact_priority'] ?? 0))
            )
                $selected = $guardian;
        }
        return $selected ? $selected["phone"] . ' (' . $selected['firstname'] . ' - ' . $selected['relationship_type'] . ')' : '';
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
            "birth_place" => $user->birth_place,
            "nationality" => $user->nationality,
            "picture" => $user->picture,
            "critical_info" => $user->critical_info,
            "occupation" => $user->occupation,
            "rel_start_date" => $teacherRel->start_date->format('Y-m-d'),
            "rel_end_date" => $teacherRel->end_date ? $teacherRel->end_date->format('Y-m-d') : null,
            "rel_in_charge" => $teacherRel->in_charge,
            "rel_notes" => $teacherRel->notes,
            "rel_subject" => $subject ? ['name' => $subject->name, 'code' => $subject->code] : null,
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
            'active_enrolled_count',
            'once_enrolled_count',
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
            new CourseExport($this, $this->attendanceService, $course, $exportOptions),
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

    public function getSchedule(Course $course)
    {
        $timeSlotsConfig = $course->schoolShift->timeSlots;
        $timeSlots = $timeSlotsConfig['timeSlots'] ?? $timeSlotsConfig;
        $days = $timeSlotsConfig['days'] ?? array_keys($timeSlotsConfig['schedule'] ?? [1, 2, 3, 4, 5]);

        // If course has a stored schedule, return it with current shift's timeSlots structure
        if (! empty($course->schedule) && is_array($course->schedule)) {
            return [
                'timeSlots' => $timeSlots,
                'schedule' => $course->schedule,
            ];
        }

        // No stored schedule: return structure with empty schedule (no random data)
        return [
            'timeSlots' => $timeSlots,
            'schedule' => null,
        ];
    }

    /**
     * Get subject options for schedule editing (curricular + regular for the course level).
     * Same pool as getSchedule uses for generation.
     */
    public function getScheduleSubjects(Course $course): array
    {
        $classSubjects = ClassSubject::where('school_level_id', $course->school_level_id)->get();
        $curricular = $classSubjects->where('is_curricular', true)->map(fn ($s) => ['code' => $s->code, 'name' => $s->name])->values()->all();
        $regular = $classSubjects->where('is_curricular', false)
            ->whereIn('code', ['lengua', 'matematica', 'ciencias_sociales', 'ciencias_naturales'])
            ->map(fn ($s) => ['code' => $s->code, 'name' => $s->name])
            ->values()
            ->all();
        return array_merge($curricular, $regular);
    }

    /**
     * Get teachers for schedule dropdowns. Course teachers first (already related).
     * When $subjectCode is set, teachers with that subject are listed first (same logic as getRandomTeacher / parseRelatedTeacher).
     */
    public function getTeachersForSchedule(Course $course, ?string $subjectCode = null): array
    {
        $teachers = $course->courseTeachers()
            ->with(['roleRelationship.user', 'roleRelationship.workerRelationship.classSubject', 'roleRelationship.role'])
            ->whereNull('teacher_courses.end_date')
            ->get();
        $parsed = $teachers->map(fn ($rel) => $this->parseRelatedTeacher($rel))->values()->all();
        if ($subjectCode !== null && $subjectCode !== '') {
            usort($parsed, function ($a, $b) use ($subjectCode) {
                $aMatch = isset($a['rel_subject']['code']) && $a['rel_subject']['code'] === $subjectCode ? 1 : 0;
                $bMatch = isset($b['rel_subject']['code']) && $b['rel_subject']['code'] === $subjectCode ? 1 : 0;
                return $bMatch - $aMatch;
            });
        }
        return $parsed;
    }

    /**
     * Update the course stored schedule. Expects grid: [ day => [ slotId => { subject: {code, name}, teacher: {...} } ] ].
     */
    public function updateCourseSchedule(Course $course, array $scheduleGrid): Course
    {
        $course->schedule = $scheduleGrid;
        $course->save();
        return $course;
    }

    private function getRandomTeacher($course, $subject)
    {
        static $teachers = null;
        if (empty($teachers))
            $teachers = $course->courseTeachers()->get();
        if (empty($teachers) || $teachers->count() === 0) return null;
        $teacher = $teachers->count() > 1 ? $teachers->random() : $teachers->first();
        $teacher = $this->parseRelatedTeacher($teacher);
        // return ['name' => $teacher['name'], 'subject' => $teacher['subject']];
        return $teacher;
    }
}
