<?php

namespace App\Traits;

use App\Models\Entities\School;
use App\Models\Entities\User;
use App\Models\Catalogs\Role;
use Illuminate\Http\Request;
use App\Services\PaginationTrait;

trait UserServiceList
{
    use PaginationTrait;

    public function getUsers(Request $request)
    {
        $query = User::with('allRolesAcrossTeams');
        $query = $this->addTextSearch($request, $query);
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
        $users = $this->handlePagination($query, $request->input('per_page'), 30);

        // Transform the data to include roles in the expected format, similar to getUsers
        $transformedUsers = json_decode(json_encode($users), true);
        // $transformedUsers['data'] = collect($transformedUsers['data'])->map(function ($user) {
        //     // Get unique school IDs from roles
        //     $schoolIds = collect($user['all_roles_across_teams'])
        //         ->pluck('pivot.team_id')
        //         ->filter()
        //         ->unique()
        //         ->values()
        //         ->toArray();

        //     // Get schools for these IDs
        //     $schools = School::whereIn('id', $schoolIds)->get();

        //     $user['roles'] = collect($user['all_roles_across_teams'])->map(function ($role) {
        //         return [
        //             'id' => $role['id'],
        //             'name' => $role['name'],
        //             'short' => $role['short'],
        //             'code' => $role['code'],
        //             'team_id' => $role['team_id']
        //         ];
        //     })->toArray();
        //     unset($user['all_roles_across_teams']);

        //     // Add schools to user data
        //     $user['schools'] = $schools->map(function ($school) {
        //         return [
        //             'id' => $school->id,
        //             'name' => $school->name,
        //             'short' => $school->short
        //         ];
        //     })->toArray();

        //     return $user;
        // })->toArray();

        return $transformedUsers;
    }

    /**
     * Get users with active role relationships for multiple role-school combinations.
     */
    public function getUsersByRoleRelationships(Request $request, array $roleSchoolPairs)
    {
        $query = User::withActiveRoleRelationships($roleSchoolPairs);
        $query = $this->addTextSearch($request, $query);
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
}
