<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function getUsers(Request $request)
    {
        $users = User::with('allRolesAcrossTeams')->paginate(10);

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
            $schools = \App\Models\School::whereIn('id', $schoolIds)->get();

            $user['roles'] = collect($user['all_roles_across_teams'])->map(function ($role) {
                return [
                    'id' => $role['id'],
                    'name' => $role['name'],
                    'short' => $this->getRoleShortName($role['name']),
                    'key' => $role['key'],
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

    public function getTrashedUsers()
    {
        return User::onlyTrashed()->with('roles')->paginate(10);
    }

    public function createUser(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if (!empty($data['role'])) {
            $user->assignRole($data['role']);
        }

        return $user;
    }

    public function updateUser(User $user, array $data)
    {
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if (!empty($data['password'])) {
            $user->update([
                'password' => Hash::make($data['password']),
            ]);
        }

        return $user;
    }

    public function deleteUser(User $user)
    {
        if ($user->hasRole('admin')) {
            throw new \Exception('No se puede eliminar un usuario administrador.');
        }

        if ($user->id === auth()->id()) {
            throw new \Exception('No puede eliminar su propia cuenta desde esta sección.');
        }

        return $user->delete();
    }

    public function restoreUser($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        return $user->restore();
    }

    public function forceDeleteUser($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);

        if ($user->hasRole('admin')) {
            throw new \Exception('No se puede eliminar permanentemente un usuario administrador.');
        }

        return $user->forceDelete();
    }

    private function getRoleShortName($roleName)
    {
        $shortNames = [
            'Administrador' => 'Admin',
            'Director' => 'Dir',
            'Regente' => 'Reg',
            'Secretario' => 'Sec',
            'Maestra/o de Grado' => 'Grado',
            'Responsable' => 'Resp'
        ];

        return $shortNames[$roleName] ?? $roleName;
    }
}