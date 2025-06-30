<?php

namespace App\Services;

use App\Models\Entities\School;
use App\Models\Entities\User;
use App\Models\Catalogs\Role;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\Auth;
use App\Services\CourseService;
use App\Services\PaginationTrait;

class UserService
{
    use PaginationTrait;

    public function getUsers(Request $request)
    {
        $query = User::with('allRolesAcrossTeams');
        // Apply search filter if present
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        $users = $this->handlePagination($query, $request->input('per_page'));

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

    public function getTrashedUsers(Request $request = null)
    {
        $query = User::onlyTrashed()->with('roles');
        if ($request) {
            return $this->handlePagination($query, $request->input('per_page'));
        }
        return $query->paginate(10);
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
            'gender' => ['nullable', 'string', 'max:50'],
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
            'gender' => $validatedData['gender'] ?? null,
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
            $user->assignRoleForSchool($validatedData['role'], null);
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
            'gender' => $validatedData['gender'] ?? $user->gender,
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
     * Assign a single role to a user with associated details.
     */
    public function assignRoleWithDetails(User $user, int $schoolId, int $roleId, ?int $levelId, array $details, User $creator)
    {
        DB::beginTransaction();

        try {
            // Get the role to check its code
            $role = Role::find($roleId);

            // Check if role already exists for this school
            $existingRoleRelationship = $user->roleRelationships()
                ->where('role_id', $roleId)
                ->where('school_id', $schoolId)
                ->first();

            // Only prevent duplicate if role is not in allowed Mutiple On Same School
            if ($existingRoleRelationship && !in_array($role->code, Role::allowedMutipleOnSameSchool())) {
                throw new \Exception('Este rol ya está asignado a este usuario para la escuela seleccionada.');
            }

            // 1. Assign the role using Spatie
            $user->assignRoleForSchool($roleId, $schoolId);

            // 2. Create the RoleRelationship entry
            $roleRelationship = $user->roleRelationships()->create([
                'role_id' => $roleId,
                'school_id' => $schoolId,
                'school_level_id' => $levelId,
                'start_date' => $details['start_date'],
                'notes' => $details['notes'] ?? null,
                'created_by' => $creator->id, // Store the creator's ID
            ]);

            // 3. Conditionally create specific relationship details based on role
            $roleCode = $role->code; // Get the role code from the role ID

            if (Role::isWorker($roleCode)) {
                if (!empty($details['worker_details'])) {
                    $roleRelationship->workerRelationship()->create([
                        'job_status_id' => $details['worker_details']['job_status_id'] ?? null,
                        'job_status_date' => $details['worker_details']['job_status_date'] ?? null,
                        'decree_number' => $details['worker_details']['decree_number'] ?? null,
                        'degree_title' => $details['worker_details']['degree_title'] ?? null,
                        'schedule' => $details['worker_details']['schedule'] ?? null,
                        'class_subject_id' => Role::isTeacher($roleCode) ? ($details['worker_details']['class_subject_id'] ?? null) : null,
                    ]);

                    // If is also a teacher, assign courses
                    if (Role::isTeacher($roleCode) && !empty($details['worker_details']['courses']) && is_array($details['worker_details']['courses'])) {
                        $courseService = app(CourseService::class);
                        foreach ($details['worker_details']['courses'] as $courseId) {
                            $courseService->assignCourseToTeacher(
                                $roleRelationship->id,
                                $courseId,
                                [
                                    'start_date' => $details['worker_details']['start_date'] ?? now()->toDateString(),
                                    'in_charge' => $details['worker_details']['in_charge'] ?? false,
                                    'notes' => $details['worker_details']['notes'] ?? null,
                                    'created_by' => $creator->id,
                                ]
                            );
                        }
                    }
                }
            } elseif ($roleCode === Role::GUARDIAN) {
                if (!empty($details['guardian_details'])) {
                    $roleRelationship->guardianRelationship()->create([
                        'student_id' => $details['guardian_details']['student_id'] ?? null,
                        'relationship_type' => $details['guardian_details']['relationship_type'] ?? null,
                        'is_emergency_contact' => $details['guardian_details']['is_emergency_contact'] ?? false,
                        'is_restricted' => $details['guardian_details']['is_restricted'] ?? false,
                        'emergency_contact_priority' => $details['guardian_details']['emergency_contact_priority'] ?? null,
                    ]);
                }
            } elseif ($roleCode === Role::STUDENT) {
                if (!empty($details['student_details'])) {
                    $studentRelationship = $roleRelationship->studentRelationship()->create([
                        'current_course_id' => $details['student_details']['current_course_id'] ?? null,
                    ]);

                    // Enroll the student to the course if current_course_id is provided
                    if (!empty($details['student_details']['current_course_id'])) {
                        $courseService = app(CourseService::class);
                        $courseService->enrollStudentToCourse(
                            $roleRelationship->id,
                            $details['student_details']['current_course_id'],
                            [
                                'start_date' => $details['student_details']['start_date'] ?? now()->toDateString(),
                                'created_by' => $creator->id,
                                // Add other fields if needed
                            ]
                        );
                    }
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assigning role with details: ' . $e->getMessage());
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
                    'workerRelationship' => function ($query) {
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
                    },
                    'creator'
                ]);
            }
        ]);

        // Transform the data to include roles and schools
        $transformedUser = $user->toArray();
        $allRolesAcrossTeams = collect($user->allRolesAcrossTeams);

        // Get unique school IDs from roles
        $schoolIds = $allRolesAcrossTeams
            ->pluck('pivot.team_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Get schools for these IDs
        $schools = School::whereIn('id', $schoolIds)->get();

        $transformedUser['roles'] = $allRolesAcrossTeams->map(function ($role) {
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
                'start_date' => $relationship->start_date ? $relationship->start_date->toDateString() : null,
                'end_date' => $relationship->end_date,
                'end_reason_id' => $relationship->end_reason_id,
                'notes' => $relationship->notes,
                'custom_fields' => $relationship->custom_fields,
                'created_at' => $relationship->created_at,
                'updated_at' => $relationship->updated_at,
                'creator' => $relationship->creator ? [
                    'id' => $relationship->creator->id,
                    'name' => $relationship->creator->name
                ] : null
            ];
        })->values()->toArray();

        // Add teacher relationships with detailed information
        $transformedUser['workerRelationships'] = $user->roleRelationships
            ->pluck('workerRelationship')
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
                    'job_status_id' => $relationship->job_status_id,
                    'job_status_date' => $relationship->job_status_date,
                    'decree_number' => $relationship->decree_number,
                    'decree_file_id' => $relationship->decree_file_id,
                    'schedule' => $relationship->schedule,
                    'degree_title' => $relationship->degree_title,
                    'start_date' => $roleRelationship->start_date ? $roleRelationship->start_date->toDateString() : null,
                    'creator' => $roleRelationship->creator ? [
                        'id' => $roleRelationship->creator->id,
                        'name' => $roleRelationship->creator->name
                    ] : null
                ];
            })->values()->toArray();

        // Add guardian relationships with student information
        $transformedUser['guardianRelationships'] = $user->roleRelationships
            ->pluck('guardianRelationship')
            ->filter()
            ->map(function ($relationship) use ($user) {
                $roleRelationship = $user->roleRelationships->firstWhere('id', $relationship->role_relationship_id);
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
                    'emergency_contact_priority' => $relationship->emergency_contact_priority,
                    'start_date' => $roleRelationship->start_date ? $roleRelationship->start_date->toDateString() : null,
                    'creator' => $roleRelationship->creator ? [
                        'id' => $roleRelationship->creator->id,
                        'name' => $roleRelationship->creator->name
                    ] : null
                ];
            })->values()->toArray();

        // Add student relationships with course information
        $transformedUser['studentRelationships'] = $user->roleRelationships
            ->pluck('studentRelationship')
            ->filter()
            ->map(function ($relationship) use ($user) {
                $roleRelationship = $user->roleRelationships->firstWhere('id', $relationship->role_relationship_id);
                return [
                    'id' => $relationship->id,
                    'role_relationship_id' => $relationship->role_relationship_id,
                    'current_course' => $relationship->currentCourse ? [
                        'id' => $relationship->currentCourse->id,
                        'name' => $relationship->currentCourse->name,
                        'grade' => $relationship->currentCourse->grade,
                        'section' => $relationship->currentCourse->section
                    ] : null,
                    'start_date' => $roleRelationship->start_date ? $roleRelationship->start_date->toDateString() : null,
                    'creator' => $roleRelationship->creator ? [
                        'id' => $roleRelationship->creator->id,
                        'name' => $roleRelationship->creator->name
                    ] : null
                ];
            })->values()->toArray();

        return $transformedUser;
    }

    public function hasAccessToSchool(int $schoolId): bool
    {
        /** @var \App\Models\Entities\User $user */
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Global admins have access to all schools
        if ($user->hasRole('global admin')) {
            return true;
        }

        // Check if the user has any roles associated with the given school ID
        return $user->allRolesAcrossTeams()->wherePivot('team_id', $schoolId)->exists();
    }

    public function getUsersBySchool(Request $request, int $schoolId)
    {
        $query = User::with(['allRolesAcrossTeams' => function ($query) use ($schoolId) {
            $query->wherePivot('team_id', $schoolId);
        }])
            ->whereHas('allRolesAcrossTeams', function ($query) use ($schoolId) {
                $query->wherePivot('team_id', $schoolId);
            });

        // Apply filters if needed (example: search by name or email)
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $users = $query->paginate(10);

        // Transform the data to include roles in the expected format, similar to getUsers
        $transformedUsers = json_decode(json_encode($users), true);
        $transformedUsers['data'] = collect($transformedUsers['data'])->map(function ($user) use ($schoolId) {
            // Re-filter allRolesAcrossTeams to only include those for the current school
            $filteredRoles = collect($user['all_roles_across_teams'])->filter(function ($role) use ($schoolId) {
                return $role['pivot']['team_id'] == $schoolId;
            });

            // Get unique school IDs from the filtered roles
            $schoolIds = $filteredRoles
                ->pluck('pivot.team_id')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            // Get schools for these IDs
            $schools = School::whereIn('id', $schoolIds)->get();

            $user['roles'] = $filteredRoles->map(function ($role) {
                return [
                    'id' => $role['id'],
                    'name' => $role['name'],
                    'short' => $role['short'],
                    'code' => $role['code'],
                    'team_id' => $role['pivot']['team_id']
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

    public static function genders()
    {
        return User::genders();
    }
}
