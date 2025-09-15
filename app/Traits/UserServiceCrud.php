<?php

namespace App\Traits;

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
use App\Services\CourseService;
use App\Services\PaginationTrait;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait UserServiceCrud
{
    use PaginationTrait;

    public function getTrashedUsers(?Request $request = null)
    {
        $query = User::onlyTrashed()->with('roles');
        if ($request) {
            return $this->handlePagination($query, $request->input('per_page'), 30);
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
            'lastname' => ['required', 'string', 'max:255'],
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

    public function updateUserImage(User $user, Request $request) {
        $request->validate([
            'image' => 'required|image|max:2048', // Max 2MB
            'type' => 'required|in:picture'
        ]);

        $image = $request->file('image');
        $type = $request->input('type');

        // Delete old image if exists
        if ($type === 'picture' && $user->picture) {
            $oldPath = str_replace('/storage/', '', $user->picture);
            Storage::disk('public')->delete($oldPath);
        }

        // Generate timestamp and slugged filename
        $timestamp = now()->format('YmdHis');
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $image->getClientOriginalExtension();
        $sluggedName = Str::slug($originalName);
        $newFilename = $timestamp . '_' . $sluggedName . '.' . $extension;

        // Store new image with custom filename
        $path = $image->storeAs('users/' . $user->id, $newFilename, 'public');

        // Get the full URL for the stored image using the asset helper
        $url = asset('storage/' . $path);

        // Update user with new image path
        return $user->update([
            $type => $url
        ]);
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
                        'school_shift_id' => Role::isTeacher($roleCode) ? ($details['worker_details']['school_shift_id'] ?? null) : null,
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
}
