<?php

namespace App\Traits;

use App\Models\Entities\School;
use App\Models\Entities\User;
use App\Models\Entities\AcademicYear;
use App\Models\Catalogs\Role;
use Illuminate\Http\Request;
use App\Services\PaginationTrait;
use Illuminate\Support\Facades\Log;

trait UserServiceList
{
    use PaginationTrait;

    public function getUsers(Request $request)
    {
        $expectedFilters = ['search', 'sort', 'direction', 'per_page'];

        $query = User::with('allRolesAcrossTeams');
        $query = $this->addTextSearch($request, $query);
        $query = $this->addSorting($request, $query);

        // Handle pagination
        $perPage = $request->input('per_page', 30);
        $users = $this->handlePagination($query, $perPage, 30);

        // Transform the data to include roles in the expected format
        $transformedUsers = json_decode(json_encode($users), true);
        $transformedUsers['data'] = collect($transformedUsers['data'])->map(function ($user) {
            // Get unique school IDs from roles
            $schoolIds = collect($user['all_roles_across_teams'])
                ->pluck('pivot.team_id')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            // Get schools for these IDs
            $schools = School::whereIn('id', $schoolIds)->get();

            $user['roles'] = collect($user['all_roles_across_teams'])->map(function ($role) {
                return [
                    'id' => $role['id'],
                    'name' => $role['name'],
                    'short' => $role['short'],
                    'code' => $role['code'],
                    'team_id' => $role['team_id']
                ];
            })->toArray();
            unset($user['all_roles_across_teams']);

            // Add schools to user data
            $user['schools'] = $schools->map(function ($school) {
                return [
                    'id' => $school->id,
                    'name' => $school->name,
                    'short' => $school->short
                ];
            })->toArray();


            return $user;
        })->toArray();

        // Add query string parameters to pagination links
        $paginationData = $users->appends($request->only($expectedFilters))->withQueryString()->toArray();

        // Merge pagination metadata with transformed data
        $transformedUsers = array_merge($transformedUsers, [
            'current_page' => $paginationData['current_page'],
            'from' => $paginationData['from'],
            'last_page' => $paginationData['last_page'],
            'per_page' => $paginationData['per_page'],
            'to' => $paginationData['to'],
            'total' => $paginationData['total'],
            'links' => $paginationData['links'],
            'path' => $paginationData['path'],
            'first_page_url' => $paginationData['first_page_url'],
            'last_page_url' => $paginationData['last_page_url'],
            'next_page_url' => $paginationData['next_page_url'],
            'prev_page_url' => $paginationData['prev_page_url']
        ]);

        return $transformedUsers;
    }

    public function getStaffBySchool(Request $request, int $schoolId)
    {
        $expectedFilters = ['search', 'sort', 'direction', 'per_page', 'shift', 'roles'];

        $workersIds = Role::select('id')->whereIn('code', Role::workersCodes())->pluck('id')->toArray();

        $query = User::withActiveRoleRelationships($workersIds, $schoolId)
            ->with([
                'roleRelationships.role',
                'roleRelationships.teacherCourses.course.schoolLevel',
                'roleRelationships.teacherCourses.course.schoolShift',
                'roleRelationships.teacherCourses.course.school',
                'roleRelationships.workerRelationship',
                'roleRelationships.workerRelationship.classSubject'
            ]);

        // Apply filters
        $query = $this->addTextSearch($request, $query);
        $query = $this->addShiftFilter($request, $query);
        $query = $this->addRolesFilter($request, $query);
        $query = $this->addSorting($request, $query);

        // Handle pagination
        $perPage = $request->input('per_page', 30);
        $users = $this->handlePagination($query, $perPage, 30);

        // Transform the data to include roles and courses in the expected format, similar to getUsers
        $transformedUsers = json_decode(json_encode($users), true);
        $transformedUsers['data'] = $users->map(function ($user) use ($schoolId) {
            // Add roles data similar to lines 42-50, but only worker roles for this school
            $user['roles'] = $user->roleRelationships
                ->filter(function ($roleRelationship) use ($schoolId) {
                    // Only include roles for this specific school and that are worker roles
                    return $roleRelationship->school_id == $schoolId &&
                        Role::isWorker($roleRelationship->role->code);
                })
                ->map(function ($roleRelationship) {
                    return [
                        'id' => $roleRelationship->role->id,
                        'name' => $roleRelationship->role->name,
                        'short' => $roleRelationship->role->short,
                        'code' => $roleRelationship->role->code,
                        'team_id' => $roleRelationship->school_id
                    ];
                })->toArray();

            // Add courses data through TeacherCourse relation
            $courses = collect();
            foreach ($user->roleRelationships as $roleRelationship) {
                foreach ($roleRelationship->teacherCourses as $teacherCourse) {
                    if ($teacherCourse->course) {
                        $courses->push([
                            'id' => $teacherCourse->course->id,
                            'nice_name' => $teacherCourse->course->nice_name,
                            'url' => route('school.course.show', [
                                'school' => $teacherCourse->course->school->slug,
                                'schoolLevel' => $teacherCourse->course->schoolLevel->code,
                                'idAndLabel' => $teacherCourse->course->idAndLabel
                            ]),
                            'school_level' => $teacherCourse->course->schoolLevel,
                            'school_shift' => $teacherCourse->course->schoolShift
                        ]);
                    }
                }
            }
            $user['courses'] = $courses->toArray();

            // Get worker relationships through role relationships
            $workerRelationships = $user->roleRelationships
                ->pluck('workerRelationship')
                ->filter()
                ->whereNull('deleted_at');
            $user['workerRelationships'] = $workerRelationships;
            return $user;
        })->toArray();

        $transformedUsers['data'] = array_filter($transformedUsers['data']);

        // Add query string parameters to pagination links
        $paginationData = $users->appends($request->only($expectedFilters))->withQueryString()->toArray();

        // Merge pagination metadata with transformed data
        $transformedUsers = array_merge($transformedUsers, [
            'current_page' => $paginationData['current_page'],
            'from' => $paginationData['from'],
            'last_page' => $paginationData['last_page'],
            'per_page' => $paginationData['per_page'],
            'to' => $paginationData['to'],
            'total' => $paginationData['total'],
            'links' => $paginationData['links'],
            'path' => $paginationData['path'],
            'first_page_url' => $paginationData['first_page_url'],
            'last_page_url' => $paginationData['last_page_url'],
            'next_page_url' => $paginationData['next_page_url'],
            'prev_page_url' => $paginationData['prev_page_url']
        ]);

        return $transformedUsers;
    }

    public function getStudentsBySchool(Request $request, int $schoolId)
    {
        $studentRoleId = Role::where('code', Role::STUDENT)->firstOrFail()->id;
        $query = User::withActiveRoleRelationship($studentRoleId, $schoolId);
        $query = $this->addTextSearch($request, $query);
        $query = $this->addSorting($request, $query);
        $users = $this->handlePagination($query, $request->input('per_page'), 30);

        // Transform the data to include roles in the expected format, similar to getUsers
        $transformedUsers = json_decode(json_encode($users), true);
        $transformedUsers['data'] = $users->map(function ($user) {
            $studentRelationship = $user->studentRelationships->whereNull('deleted_at')->first();
            $currentCourse = empty($studentRelationship) ? null : $studentRelationship->currentCourse;
            if (!empty($currentCourse)) {
                $currentCourse->load(['schoolLevel', 'schoolShift']);
                // dd($currentCourse);
            }
            $currentCourse->url = route('school.course.show', [
                'school' => $currentCourse->school->slug,
                'schoolLevel' => $currentCourse->schoolLevel->code,
                'idAndLabel' => $currentCourse->idAndLabel
            ]);
            // Add student relationships to the user data
            $user['course'] = $currentCourse;
            return $user;
        })->toArray();

        $transformedUsers['data'] = array_filter($transformedUsers['data']);
        return $transformedUsers;
    }

    /**
     * Get users with active role relationships for multiple role-school combinations.

    public function getUsersByRoleRelationships(Request $request, array $roleSchoolPairs)
    {
        $query = User::withActiveRoleRelationships(make parameters);
        $query = $this->addTextSearch($request, $query);
        $query = $this->addSorting($request, $query);
        $users = $this->handlePagination($query, $request->input('per_page'), 30);

        // Transform the data to include roles in the expected format
        $transformedUsers = json_decode(json_encode($users), true);
        $transformedUsers['data'] = collect($transformedUsers['data'])->map(function ($user) {
            // Get unique school IDs from roles
            $schoolIds = collect($user['all_roles_across_teams'])
                ->pluck('pivot.team_id')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            // Get schools for these IDs
            $schools = School::whereIn('id', $schoolIds)->get();

            $user['roles'] = collect($user['all_roles_across_teams'])->map(function ($role) {
                return [
                    'id' => $role['id'],
                    'name' => $role['name'],
                    'short' => $role['short'],
                    'code' => $role['code'],
                    'team_id' => $role['team_id']
                ];
            })->toArray();
            unset($user['all_roles_across_teams']);

            // Add schools to user data
            $user['schools'] = $schools->map(function ($school) {
                return [
                    'id' => $school->id,
                    'name' => $school->name,
                    'short' => $school->short
                ];
            })->toArray();

            return $user;
        })->toArray();

        return $transformedUsers;
    }
     */
    private function addTextSearch(Request $request, $query)
    {
        // Apply search filter if present
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('firstname', 'like', "%{$search}%")
                    ->orWhere('lastname', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('id_number', 'like', "%{$search}%");
            });
        }
        return $query;
    }

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
                // Direct user table field sorting
                $query->orderBy("users.{$sort}", $direction);
            }
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
            'name',
            'firstname',
            'lastname',
            'id_number',
            'gender',
            'birthdate',
            'phone',
            'address',
            'locality',
            'nationality',
            'email',
            'created_at',
            'updated_at'
        ];

        $validRelationshipFields = [
            'level',    // from course->schoolLevel
            'shift',    // from course->schoolShift
            'course'    // from course number+letter
        ];

        return in_array($field, $validDirectFields) || in_array($field, $validRelationshipFields);
    }

    /**
     * Check if field requires relationship joins
     */
    private function isRelationshipSortField(string $field): bool
    {
        return in_array($field, ['level', 'shift', 'course']);
    }

    /**
     * Add sorting for relationship-based fields
     */
    private function addRelationshipSorting($query, string $field, string $direction)
    {
        // Join student relationships, courses, and related tables
        $query->leftJoin('role_relationships', function ($join) {
            $join->on('users.id', '=', 'role_relationships.user_id')
                ->whereNull('role_relationships.deleted_at')
                ->whereNull('role_relationships.end_date');
        })
            ->leftJoin('student_relationships', function ($join) {
                $join->on('role_relationships.id', '=', 'student_relationships.role_relationship_id')
                    ->whereNull('student_relationships.deleted_at');
            })
            ->leftJoin('courses', 'student_relationships.current_course_id', '=', 'courses.id');

        switch ($field) {
            case 'level':
                $query->leftJoin('school_levels', 'courses.school_level_id', '=', 'school_levels.id')
                    ->select('users.*', 'school_levels.code as level_code')
                    ->orderBy('school_levels.code', $direction)
                    ->distinct();
                break;

            case 'shift':
                $query->leftJoin('school_shifts', 'courses.school_shift_id', '=', 'school_shifts.id')
                    ->select('users.*', 'school_shifts.code as shift_code')
                    ->orderBy('school_shifts.code', $direction)
                    ->distinct();
                break;

            case 'course':
                $query->select('users.*', 'courses.number as course_number', 'courses.letter as course_letter')
                    ->orderBy('courses.number', $direction)
                    ->orderBy('courses.letter', $direction)
                    ->distinct();
                break;
        }
    }

    public function getStudentParents(User $student)
    {
        $parents = $student->myParents();
        if (empty($parents)) return null;
        $return  = [];
        foreach ($parents as $parent) {
            $r = $parent->roleRelationships->first();
            $g = $r ? $r->guardianRelationship : null;
            $parent->setRelations([]);
            $return[] = [
                "id" => $parent->id,
                "firstname" => $parent->firstname,
                "lastname" => $parent->lastname,
                "id_number" => $parent->id_number,
                "gender" => $parent->gender,
                "birthdate" => $parent->birthdate->format('Y-m-d'),
                "phone" => $parent->phone,
                "address" => $parent->address,
                "locality" => $parent->locality,
                "province" => $parent->province->name,
                "country" => $parent->country->name,
                "nationality" => $parent->nationality,
                "picture" =>  $parent->picture,
                "email" => $parent->email,
                "relationship_type" => $g ? $g->relationship_type : null,
                "is_emergency_contact" => $g ? $g->is_emergency_contact : null,
                "is_restricted" => $g ? $g->is_restricted : null,
                "emergency_contact_priority" => $g ? $g->emergency_contact_priority : null,
            ];
        }
        $return = collect($return)->sort(function ($a, $b) {
            // 1. is_restricted: true first, false second, null last
            $a_restricted = $a['is_restricted'];
            $b_restricted = $b['is_restricted'];
            if ($a_restricted !== $b_restricted) {
                // true (1) < false (0) < null
                if ($a_restricted === true) return -1;
                if ($b_restricted === true) return 1;
                if ($a_restricted === false) return -1;
                if ($b_restricted === false) return 1;
                // nulls go last
            }

            // 2. emergency_contact_priority: number order, null last
            $a_priority = $a['emergency_contact_priority'];
            $b_priority = $b['emergency_contact_priority'];
            if ($a_priority !== $b_priority) {
                if ($a_priority === null) return 1;
                if ($b_priority === null) return -1;
                return $a_priority <=> $b_priority;
            }

            // 3. lastname firstname
            $lastname_cmp = strcmp($a['lastname'], $b['lastname']);
            if ($lastname_cmp !== 0) {
                return $lastname_cmp;
            }
            return strcmp($a['firstname'], $b['firstname']);
        })->values()->all();
        return $return;
    }

    /**
     * Add shift filter to the query
     */
    protected function addShiftFilter(Request $request, $query)
    {
        $shiftId = $request->input('shift');

        if ($shiftId && is_numeric($shiftId)) {
            // Filter users who have courses in the selected shift
            $query->whereHas('roleRelationships.teacherCourses.course', function ($q) use ($shiftId) {
                $q->where('school_shift_id', $shiftId);
            });
        }

        return $query;
    }

    /**
     * Add roles filter to the query
     */
    protected function addRolesFilter(Request $request, $query)
    {
        $roleIds = $request->input('roles', []);

        if (!empty($roleIds) && is_array($roleIds)) {
            // Filter out any non-numeric values
            $roleIds = array_filter($roleIds, 'is_numeric');

            if (!empty($roleIds)) {
                $query->whereHas('roleRelationships', function ($q) use ($roleIds) {
                    $q->whereIn('role_id', $roleIds);
                });
            }
        }

        return $query;
    }

    public function getLoggedUserRelevantBirthdays($user, $from, $to)
    {
        /** @var User $loggedUser */
        $loggedUser = auth()->user();
        if ($loggedUser->id !== $user->id) {
            throw new \Exception('User should be logged one. If necesary, improve this method to stop using context to optimize performance');
        }

        // If user is superadmin, return all users within birthdate range
        if ($loggedUser->isSuperadmin()) {
            $return = User::whereRaw("DATE_FORMAT(birthdate, '%m-%d') >= ?", [$from->format('m-d')])
                ->whereRaw("DATE_FORMAT(birthdate, '%m-%d') <= ?", [$to->format('m-d')])
                ->get()
                ->map(function ($user) {
                    $user->context = ['superadmin_view'];
                    return $user;
                });

            return $return->map(function ($user) {
                return $this->parseUserForBirthdayCalendar($user);
            })->toArray();
        }

        // Get user context from UserContextService
        $userContext = \App\Services\UserContextService::get();
        $activeRoleRelationships = $userContext['activeRoleRelationships'] ?? [];

        if (empty($activeRoleRelationships)) {
            return [];
        }

        $relatedUsers = collect();
        dd($activeRoleRelationships);
        foreach ($activeRoleRelationships as $roleRelationship) {
            $roleCode = $roleRelationship['role_code'] ?? null;
            $schoolId = $roleRelationship['school_id'] ?? null;

            if (!$roleCode || !$schoolId) {
                continue;
            }

            // Handle different role types
            if (Role::isWorker($roleCode)) {
                // If worker, get co-workers of same school
                $relatedUsers = $relatedUsers->merge($this->getCoworkers($schoolId, $from, $to));

                // If worker is related to class, get students
                $relatedUsers = $relatedUsers->merge($this->getStudentsForWorkerFromContext($userContext, $schoolId, $from, $to));
            } elseif ($roleCode === Role::STUDENT) {
                // If student, get classmates and teachers
                $relatedUsers = $relatedUsers->merge($this->getClassmatesAndTeachersFromContext($userContext, $schoolId, $from, $to));
            } elseif ($roleCode === Role::GUARDIAN) {
                // If guardian, get related students and their classmates/teachers
                $relatedUsers = $relatedUsers->merge($this->getRelatedStudentsForGuardianFromContext($userContext, $from, $to));
            }
        }

        // Remove duplicates and merge contexts, then filter by birthdate
        $uniqueUsers = collect();

        foreach ($relatedUsers as $user) {
            $userId = $user->id;

            if ($uniqueUsers->has($userId)) {
                // User already exists, merge contexts
                $existingUser = $uniqueUsers->get($userId);
                $existingContexts = is_array($existingUser->context) ? $existingUser->context : [$existingUser->context];
                $newContext = is_array($user->context) ? $user->context : [$user->context];
                $mergedContexts = array_unique(array_merge($existingContexts, $newContext));
                $existingUser->context = $mergedContexts;
            } else {
                // New user, ensure context is an array
                $user->context = is_array($user->context) ? $user->context : [$user->context];
                $uniqueUsers->put($userId, $user);
            }
        }

        $return = $uniqueUsers
            ->values()
            ->filter(function ($user) use ($from, $to) {
                if (!$user->birthdate) {
                    return false;
                }

                $userBirthday = $user->birthdate->format('m-d');
                $fromDate = $from->format('m-d');
                $toDate = $to->format('m-d');

                return $userBirthday >= $fromDate && $userBirthday <= $toDate;
            });

        return $return->map(function ($user) {
            return $this->parseUserForBirthdayCalendar($user);
        })->toArray();
    }

    private function parseUserForBirthdayCalendar($user): array
    {
        return [
            'id' => $user->id,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'shortname' => $user->shortName,
            'birthdate' => $user->birthdate->format('Y-m-d'),
            'context' => $user->context,
        ];
    }

    /**
     * Get co-workers from the same school
     */
    private function getCoworkers($schoolId, $from, $to)
    {
        $workerRoleIds = Role::select('id')->whereIn('code', Role::workersCodes())->pluck('id')->toArray();

        return User::withActiveRoleRelationships($workerRoleIds, $schoolId)
            ->whereRaw("DATE_FORMAT(birthdate, '%m-%d') >= ?", [$from->format('m-d')])
            ->whereRaw("DATE_FORMAT(birthdate, '%m-%d') <= ?", [$to->format('m-d')])
            ->get()
            ->map(function ($user) {
                $user->context = 'coworker';
                return $user;
            });
    }

    /**
     * Get students for a worker (if worker is related to classes) - using context
     */
    private function getStudentsForWorkerFromContext($userContext, $schoolId, $from, $to)
    {
        // Get the logged user from context
        $loggedUserId = auth()->id();
        if (!$loggedUserId) {
            return collect();
        }

        // Get courses where the worker is a teacher
        $courseIds = User::find($loggedUserId)
            ->roleRelationships()
            ->where('school_id', $schoolId)
            ->whereHas('teacherCourses')
            ->with('teacherCourses.course')
            ->get()
            ->pluck('teacherCourses')
            ->flatten()
            ->pluck('course_id')
            ->unique();

        if ($courseIds->isEmpty()) {
            return collect();
        }

        // Get students in those courses
        $studentRoleId = Role::where('code', Role::STUDENT)->first()->id;

        return User::withActiveRoleRelationship($studentRoleId, $schoolId)
            ->whereHas('studentRelationships', function ($query) use ($courseIds) {
                $query->whereIn('current_course_id', $courseIds);
            })
            ->whereRaw("DATE_FORMAT(birthdate, '%m-%d') >= ?", [$from->format('m-d')])
            ->whereRaw("DATE_FORMAT(birthdate, '%m-%d') <= ?", [$to->format('m-d')])
            ->get()
            ->map(function ($user) {
                $user->context = 'student';
                return $user;
            });
    }

    /**
     * Get classmates and teachers for a student - using context
     */
    private function getClassmatesAndTeachersFromContext($userContext, $schoolId, $from, $to)
    {
        $relatedUsers = collect();

        // Get the logged user from context
        $loggedUserId = auth()->id();
        if (!$loggedUserId) {
            return $relatedUsers;
        }

        $student = User::find($loggedUserId);
        if (!$student) {
            return $relatedUsers;
        }

        // Get student's current course
        $studentRelationship = $student->studentRelationships()
            ->whereNull('deleted_at')
            ->first();

        if (!$studentRelationship || !$studentRelationship->currentCourse) {
            return $relatedUsers;
        }

        $currentCourseId = $studentRelationship->current_course_id;

        // Get classmates (other students in the same course)
        $studentRoleId = Role::where('code', Role::STUDENT)->first()->id;
        $classmates = User::withActiveRoleRelationship($studentRoleId, $schoolId)
            ->whereHas('studentRelationships', function ($query) use ($currentCourseId) {
                $query->where('current_course_id', $currentCourseId);
            })
            ->where('id', '!=', $student->id) // Exclude the student themselves
            ->whereRaw("DATE_FORMAT(birthdate, '%m-%d') >= ?", [$from->format('m-d')])
            ->whereRaw("DATE_FORMAT(birthdate, '%m-%d') <= ?", [$to->format('m-d')])
            ->get()
            ->map(function ($user) {
                $user->context = 'classmate';
                return $user;
            });

        $relatedUsers = $relatedUsers->merge($classmates);

        // Get teachers for this course
        $teacherRoleIds = Role::select('id')->whereIn('code', Role::teacherCodes())->pluck('id')->toArray();
        $teachers = User::withActiveRoleRelationships($teacherRoleIds, $schoolId)
            ->whereHas('roleRelationships.teacherCourses', function ($query) use ($currentCourseId) {
                $query->where('course_id', $currentCourseId);
            })
            ->whereRaw("DATE_FORMAT(birthdate, '%m-%d') >= ?", [$from->format('m-d')])
            ->whereRaw("DATE_FORMAT(birthdate, '%m-%d') <= ?", [$to->format('m-d')])
            ->get()
            ->map(function ($user) {
                $user->context = 'teacher';
                return $user;
            });

        $relatedUsers = $relatedUsers->merge($teachers);

        return $relatedUsers;
    }

    /**
     * Get related students and their classmates/teachers for a guardian - using context
     */
    private function getRelatedStudentsForGuardianFromContext($userContext, $from, $to)
    {
        $relatedUsers = collect();

        // Get the logged user from context
        $loggedUserId = auth()->id();
        if (!$loggedUserId) {
            return $relatedUsers;
        }

        $guardian = User::find($loggedUserId);
        if (!$guardian) {
            return $relatedUsers;
        }

        // Get students that this guardian is responsible for
        $relatedStudents = $guardian->myChildren();

        foreach ($relatedStudents as $student) {
            // Add the student themselves
            if ($student->birthdate && $student->birthdate >= $from && $student->birthdate <= $to) {
                $student->context = 'my_child';
                $relatedUsers->push($student);
            }

            // Get classmates and teachers for each related student
            $studentActiveRelationships = $student->activeRoleRelationships();
            foreach ($studentActiveRelationships as $roleRelationship) {
                if ($roleRelationship->role->code === Role::STUDENT) {
                    $classmatesAndTeachers = $this->getClassmatesAndTeachersFromContext($userContext, $roleRelationship->school_id, $from, $to);
                    $relatedUsers = $relatedUsers->merge($classmatesAndTeachers);
                }
            }
        }

        return $relatedUsers;
    }
}
