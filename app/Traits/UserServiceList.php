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
        $query = User::with('allRolesAcrossTeams');
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
}
