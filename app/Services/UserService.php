<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

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

    /**
     * Get user roles data for editing
     */
    public function getUserRolesData(User $user)
    {
        // Load the roles relationship with team_id
        $user->load(['allRolesAcrossTeams']);

        // Get current roles grouped by team_id
        $currentRoles = $user->allRolesAcrossTeams->groupBy('pivot.team_id')
            ->map(function ($roles) {
                return $roles->pluck('id')->toArray();
            })
            ->toArray();

        return [
            'user' => $user,
            'schools' => \App\Models\School::all(),
            'availableRoles' => \Spatie\Permission\Models\Role::all(),
            'currentRoles' => $currentRoles,
        ];
    }

    /**
     * Update user roles
     */
    public function updateUserRoles(User $user, array $roles)
    {
        try {
            DB::beginTransaction();

            // Remove all existing roles
            $user->roles()->detach();

            // Add new roles
            foreach ($roles as $schoolId => $roleIds) {
                foreach ($roleIds as $roleId) {
                    $user->roles()->attach($roleId, ['team_id' => $schoolId]);
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating user roles: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get user data for show view with all relationships
     */
    public function getUserShowData(User $user): array
    {
        $user->load([
            'province',
            'country',
            'allRolesAcrossTeams',
            'roleRelationships' => function ($query) {
                $query->with([
                    'teacherRelationship' => function ($query) {
                        $query->with(['classSubject']);
                    },
                    'guardianRelationship' => function ($query) {
                        $query->with(['student' => function ($query) {
                            $query->with(['roleRelationships' => function ($query) {
                                $query->with(['studentRelationship' => function ($query) {
                                    $query->with(['currentCourse']);
                                }]);
                            }]);
                        }]);
                    },
                    'studentRelationship' => function ($query) {
                        $query->with(['currentCourse']);
                    }
                ]);
            }
        ]);

        // Transform the data to include roles and schools
        $transformedUser = $user->toArray();

        // Get unique school IDs from roles
        $schoolIds = collect($user->allRolesAcrossTeams)
            ->pluck('pivot.team_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Get schools for these IDs
        $schools = \App\Models\School::whereIn('id', $schoolIds)->get();

        $transformedUser['roles'] = collect($user->allRolesAcrossTeams)->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'short' => $role->short,
                'code' => $role->code,
                'team_id' => $role->pivot->team_id
            ];
        })->values()->toArray();

        // Add schools to user data
        $transformedUser['schools'] = $schools->map(function ($school) {
            return [
                'id' => $school->id,
                'name' => $school->name,
                'short' => $school->short
            ];
        })->values()->toArray();

        // Add role relationships data
        $transformedUser['roleRelationships'] = $user->roleRelationships->map(function ($relationship) {
            return [
                'id' => $relationship->id,
                'user_id' => $relationship->user_id,
                'role_id' => $relationship->role_id,
                'school_id' => $relationship->school_id,
                'start_date' => $relationship->start_date,
                'end_date' => $relationship->end_date,
                'end_reason_id' => $relationship->end_reason_id,
                'notes' => $relationship->notes,
                'custom_fields' => $relationship->custom_fields,
                'created_at' => $relationship->created_at,
                'updated_at' => $relationship->updated_at
            ];
        })->values()->toArray();

        // Add teacher relationships with detailed information
        $transformedUser['teacherRelationships'] = $user->roleRelationships
            ->pluck('teacherRelationship')
            ->filter()
            ->map(function ($relationship) use ($user) {
                $roleRelationship = $user->roleRelationships->firstWhere('id', $relationship->role_relationship_id);
                $role = $user->allRolesAcrossTeams->firstWhere('id', $roleRelationship->role_id);

                return [
                    'id' => $relationship->id,
                    'role_relationship_id' => $relationship->role_relationship_id,
                    'role' => $role ? [
                        'id' => $role->id,
                        'name' => $role->name,
                        'short' => $role->short,
                        'code' => $role->code
                    ] : null,
                    'class_subject' => $relationship->classSubject ? [
                        'id' => $relationship->classSubject->id,
                        'name' => $relationship->classSubject->name,
                        'short_name' => $relationship->classSubject->short_name
                    ] : null,
                    'job_status' => $relationship->job_status,
                    'job_status_date' => $relationship->job_status_date,
                    'decree_number' => $relationship->decree_number,
                    'decree_file_id' => $relationship->decree_file_id,
                    'schedule' => $relationship->schedule,
                    'degree_title' => $relationship->degree_title
                ];
            })->values()->toArray();

        // Add guardian relationships with student information
        $transformedUser['guardianRelationships'] = $user->roleRelationships
            ->pluck('guardianRelationship')
            ->filter()
            ->map(function ($relationship) {
                $student = $relationship->student;
                $studentRoleRelationship = $student ? $student->roleRelationships->first() : null;
                $studentRelationship = $studentRoleRelationship ? $studentRoleRelationship->studentRelationship : null;
                $currentCourse = $studentRelationship ? $studentRelationship->currentCourse : null;

                return [
                    'id' => $relationship->id,
                    'role_relationship_id' => $relationship->role_relationship_id,
                    'student' => $student ? [
                        'id' => $student->id,
                        'name' => $student->name,
                        'current_course' => $currentCourse ? [
                            'id' => $currentCourse->id,
                            'name' => $currentCourse->name,
                            'grade' => $currentCourse->grade,
                            'section' => $currentCourse->section
                        ] : null
                    ] : null,
                    'student_id' => $relationship->student_id,
                    'relationship_type' => $relationship->relationship_type,
                    'is_emergency_contact' => $relationship->is_emergency_contact,
                    'is_restricted' => $relationship->is_restricted,
                    'emergency_contact_priority' => $relationship->emergency_contact_priority
                ];
            })->values()->toArray();

        // Add student relationships with course information
        $transformedUser['studentRelationships'] = $user->roleRelationships
            ->pluck('studentRelationship')
            ->filter()
            ->map(function ($relationship) {
                return [
                    'id' => $relationship->id,
                    'role_relationship_id' => $relationship->role_relationship_id,
                    'current_course' => $relationship->currentCourse ? [
                        'id' => $relationship->currentCourse->id,
                        'name' => $relationship->currentCourse->name,
                        'grade' => $relationship->currentCourse->grade,
                        'section' => $relationship->currentCourse->section
                    ] : null
                ];
            })->values()->toArray();

        return $transformedUser;
    }
}
