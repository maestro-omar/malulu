<?php

namespace App\Traits;

use App\Models\Entities\School;
use App\Models\Entities\User;
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
        $workersIds = Role::select('id')->whereIn('code', Role::workersCodes())->pluck('id')->toArray();

        $query = User::withActiveRoleRelationships($workersIds, $schoolId);
        $query = $this->addTextSearch($request, $query);
        $query = $this->addSorting($request, $query);
        $users = $this->handlePagination($query, $request->input('per_page'), 30);

        // Transform the data to include roles in the expected format, similar to getUsers
        $transformedUsers = json_decode(json_encode($users), true);
        $transformedUsers['data'] = $users->map(function ($user) {
            $workerRelationships = $user->workerRelationships;
            if ($workerRelationships) dd($workerRelationships, 'tetetingings');
            $workerRelationships = $workerRelationships ? $workerRelationships->whereNull('deleted_at') : null;
            $user['workerRelationships'] = $workerRelationships;
            return $user;
        })->toArray();

        $transformedUsers['data'] = array_filter($transformedUsers['data']);
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
            // Add student relationships to the user data
            $user['course'] = $currentCourse;
            return $user;
        })->toArray();

        $transformedUsers['data'] = array_filter($transformedUsers['data']);
        return $transformedUsers;
    }

    /**
     * Get users with active role relationships for multiple role-school combinations.
     */
    public function getUsersByRoleRelationships(Request $request, array $roleSchoolPairs)
    {
        $query = User::withActiveRoleRelationships($roleSchoolPairs);
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
}
