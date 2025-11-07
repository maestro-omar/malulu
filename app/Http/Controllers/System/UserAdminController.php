<?php

namespace App\Http\Controllers\System;

use App\Models\Entities\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\UserService;
use App\Services\DiagnosisService;
use App\Http\Requests\UserRequest;
use App\Models\Catalogs\Province;
use App\Models\Catalogs\Country;
use App\Models\Catalogs\Diagnosis;
use App\Models\Entities\School;
use App\Models\Relations\RoleRelationship;
use App\Models\Relations\WorkerRelationship;
use App\Models\Relations\GuardianRelationship;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\System\SystemBaseController;
use Diglactic\Breadcrumbs\Breadcrumbs;

class UserAdminController extends SystemBaseController
{
    protected $userService;
    protected $roleService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
        $this->middleware('permission:superadmin');
    }

    /**
     * Display a listing of the users.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Users/Index', [
            'users' => $this->userService->getUsers($request),
            'filters' => $request->only(['search', 'sort', 'direction']),
            'breadcrumbs' => Breadcrumbs::generate('users.index'),
        ]);
    }

    /**
     * Display a listing of the trashed users.
     */
    public function trashed(): Response
    {
        return Inertia::render('Users/Trashed', [
            'users' => $this->userService->getTrashedUsers(),
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): Response
    {
        return Inertia::render('Users/Create', [
            'roles' => Role::all(),
            'provinces' => Province::orderBy('order')->get(),
            'countries' => Country::orderBy('order')->get(),
            'genders' => User::genders(),
            'breadcrumbs' => Breadcrumbs::generate('users.create'),
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = $this->userService->createUser($request->all());
            return redirect()->route('users.show', $user)->with('success', 'Usuario creado exitosamente.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): Response
    {
        return Inertia::render('Users/Edit', [
            'user' => $user->load(['roles']),
            'roles' => Role::all(),
            'provinces' => Province::orderBy('order')->get(),
            'countries' => Country::orderBy('order')->get(),
            'genders' => User::genders(),
            'breadcrumbs' => Breadcrumbs::generate('users.edit', $user),
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        try {
            $this->userService->updateUser($user, $request->all());
            return redirect()->route('users.show', $user)->with('success', 'Usuario actualizado exitosamente.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function editDiagnoses(User $user): Response
    {
        $diagnoses = Diagnosis::getAllWithCategory();
        return Inertia::render('Users/EditDiagnoses', [
            'user' => $user,
            'genders' => User::genders(),
            'userDiagnoses' => $user->diagnoses,
            'diagnoses' => $diagnoses,
            'canEdit' => true,
            'pageTitle' => "Diagnósticos de {$user->name}",
            'headerTitle' => "Diagnósticos de {$user->name}",
            'saveUrl' => route('users.update-diagnoses', $user),
            'cancelUrl' => route('users.show', $user),
            'editablePicture' => true,
            'publicView' => false,
            'breadcrumbs' => Breadcrumbs::generate('users.edit-diagnoses', $user),
        ]);
    }

    public function updateDiagnoses(Request $request, User $user)
    {
        try {
            $diagnosisService = new DiagnosisService();
            $diagnosisService->updateUserDiagnoses($user, $request->input('diagnoses', []));
            return redirect()->route('users.show', $user)->with('success', 'Diagnósticos actualizados exitosamente.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deletion of admin users
        if ($user->hasRole('admin')) {
            return redirect()->route('users.index')
                ->with('error', 'No se puede eliminar un usuario administrador.');
        }

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'No puede eliminar su propia cuenta desde esta sección.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }

    /**
     * Restore the specified user from trash.
     */
    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('users.trashed')
            ->with('success', 'Usuario restaurado exitosamente.');
    }

    /**
     * Permanently delete the specified user.
     */
    public function forceDelete($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);

        // Prevent permanent deletion of admin users
        if ($user->hasRole('admin')) {
            return redirect()->route('users.trashed')
                ->with('error', 'No se puede eliminar permanentemente un usuario administrador.');
        }

        $user->forceDelete();

        return redirect()->route('users.trashed')
            ->with('success', 'Usuario eliminado permanentemente.');
    }

    public function show(User $user): Response
    {
        $loggedUser = auth()->user();
        $userData = $this->userService->getFullUserShowData($user);
        return Inertia::render('Users/Show', [
            'user' => $userData,
            'files' => $this->userService->getFiles($user, $loggedUser),
            'genders' => User::genders(),
            'breadcrumbs' => Breadcrumbs::generate('users.show', $user),
        ]);
    }

    /**
     * Show the form for editing user roles.
     */
    public function addRole(User $user): Response
    {
        $userData = $this->userService->getFullUserShowData($user);

        return Inertia::render('Users/AddRole', [
            'user' => $userData,
            'allSchools' => \App\Models\Entities\School::with(['schoolLevels', 'locality'])->get(),
            'assignedSchools' => $userData['schools'],
            'availableRoles' => \Spatie\Permission\Models\Role::all(),
            'roleRelationships' => $userData['roleRelationships'],
            'workerRelationships' => $userData['workerRelationships'],
            'guardianRelationships' => $userData['guardianRelationships'],
            'studentRelationships' => $userData['studentRelationships'],
            'roles' => $userData['roles'],
            // 'classSubjects', 'students', 'courses' are no longer passed as props as per previous discussions
            // Use the static methods from RoleRelationship model
            'jobStatuses' => WorkerRelationship::jobStatuses(),
            'relationshipTypes' => GuardianRelationship::relationshipTypes(),
            'breadcrumbs' => Breadcrumbs::generate('users.add.role', $user),
        ]);
    }

    /**
     * Creates the user's new role.
     */
    public function storeRole(Request $request, User $user)
    {
        $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'role_id' => ['required', 'exists:roles,id'],
            'start_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            // Specific fields based on role will be validated in the service or within this method
            'worker_details.job_status_id' => ['nullable', 'exists:job_statuses,id'],
            'worker_details.job_status_date' => ['nullable', 'date'],
            'worker_details.decree_number' => ['nullable', 'string'],
            'worker_details.degree_title' => ['nullable', 'string'],
            'worker_details.schedule' => ['nullable', 'array'],
            'worker_details.class_subject_id' => ['nullable', 'exists:class_subjects,id'],
            'worker_details.school_shift_id' => ['nullable', 'exists:school_shifts,id'],

            'guardian_details.relationship_type' => ['nullable', 'string'],
            'guardian_details.is_emergency_contact' => ['nullable', 'boolean'],
            'guardian_details.is_restricted' => ['nullable', 'boolean'],
            'guardian_details.emergency_contact_priority' => ['nullable', 'integer'],
            'guardian_details.student_id' => ['nullable', 'exists:users,id'], // Assuming students are also users

            'student_details.current_course_id' => ['nullable', 'exists:courses,id'],
        ]);

        try {
            // Pass the authenticated user as the creator
            $creator = auth()->user();
            $this->userService->assignRoleWithDetails($user, $request->school_id, $request->role_id, $request->school_level_id,  $request->all(), $creator);
            return redirect()->route('users.show', $user)
                ->with('success', 'Rol añadido correctamente.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error al asignar el nuevo rol: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Error al asignar el nuevo rol. Por favor, intente nuevamente.'])
                ->withInput();
        }
    }

    public function uploadImage(Request $request, User $user)
    {
        try {
            if ($this->userService->updateUserImage($user, $request))
                return back()->with('success', 'Imagen subida exitosamente');
            else
                return back()->with('error', 'Hubo un problema inesperado al subir la imagen');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function deleteImage(Request $request, User $user)
    {
        try {
            $request->validate([
                'type' => 'required|in:picture'
            ]);

            $type = $request->input('type');

            // Delete the image file if it exists
            if ($type === 'picture' && $user->picture) {
                $oldPath = str_replace('/storage/', '', $user->picture);
                Storage::disk('public')->delete($oldPath);
                $user->update(['picture' => null]);
            }

            return back()->with('success', 'Imagen eliminada exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
