<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
                    'short' => $role['short'],
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

    /**
     * Validate user data
     */
    public function validateUserData(array $data, ?User $user = null)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'firstname' => ['nullable', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'id_number' => ['nullable', 'string', 'max:50'],
            'birthdate' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'locality' => ['nullable', 'string', 'max:255'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id)
            ],
            'password' => $user ? ['nullable', 'string', 'min:8', 'confirmed'] : ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['nullable', 'string', 'exists:roles,name'],
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'El correo electrónico debe ser válido',
            'email.unique' => 'Este correo electrónico ya está registrado',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'La confirmación de la contraseña no coincide',
            'province_id.exists' => 'La provincia seleccionada no existe',
            'country_id.exists' => 'El país seleccionado no existe',
            'role.exists' => 'El rol seleccionado no existe',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function createUser(array $data)
    {
        $validatedData = $this->validateUserData($data);

        $user = User::create([
            'name' => $validatedData['name'],
            'firstname' => $validatedData['firstname'] ?? null,
            'lastname' => $validatedData['lastname'] ?? null,
            'id_number' => $validatedData['id_number'] ?? null,
            'birthdate' => $validatedData['birthdate'] ?? null,
            'phone' => $validatedData['phone'] ?? null,
            'address' => $validatedData['address'] ?? null,
            'locality' => $validatedData['locality'] ?? null,
            'province_id' => $validatedData['province_id'] ?? null,
            'country_id' => $validatedData['country_id'] ?? null,
            'nationality' => $validatedData['nationality'] ?? null,
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        if (!empty($validatedData['role'])) {
            $user->assignRole($validatedData['role']);
        }

        return $user;
    }

    public function updateUser(User $user, array $data)
    {
        $validatedData = $this->validateUserData($data, $user);

        $user->update([
            'name' => $validatedData['name'],
            'firstname' => $validatedData['firstname'] ?? $user->firstname,
            'lastname' => $validatedData['lastname'] ?? $user->lastname,
            'id_number' => $validatedData['id_number'] ?? $user->id_number,
            'birthdate' => $validatedData['birthdate'] ?? $user->birthdate,
            'phone' => $validatedData['phone'] ?? $user->phone,
            'address' => $validatedData['address'] ?? $user->address,
            'locality' => $validatedData['locality'] ?? $user->locality,
            'province_id' => $validatedData['province_id'] ?? $user->province_id,
            'country_id' => $validatedData['country_id'] ?? $user->country_id,
            'nationality' => $validatedData['nationality'] ?? $user->nationality,
            'email' => $validatedData['email'],
        ]);

        if (!empty($validatedData['password'])) {
            $user->update([
                'password' => Hash::make($validatedData['password']),
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
}