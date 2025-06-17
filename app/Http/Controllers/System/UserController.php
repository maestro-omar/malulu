<?php

namespace App\Http\Controllers\System;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\UserService;
use App\Http\Requests\UserRequest;
use App\Models\Province;
use App\Models\Country;
use App\Models\School;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends SystemBaseController
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
        $this->middleware('permission:superadmin');
    }

    /**
     * Display a listing of the users.
     */
    public function index(): Response
    {
        return Inertia::render('Users/Index', [
            'users' => $this->userService->getUsers(request()),
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
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
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
        return Inertia::render('Users/Show', [
            'user' => $this->userService->getUserShowData($user)
        ]);
    }

    /**
     * Show the form for editing user roles.
     */
    public function addRole(User $user): Response
    {
        $userData = $this->userService->getUserShowData($user);

        return Inertia::render('Users/AddRole', [
            'user' => $userData,
            'allSchools' => \App\Models\School::all(),
            'assignedSchools' => $userData['schools'],
            'availableRoles' => \Spatie\Permission\Models\Role::all(),
            'roleRelationships' => $userData['roleRelationships'],
            'workerRelationships' => $userData['workerRelationships'],
            'guardianRelationships' => $userData['guardianRelationships'],
            'studentRelationships' => $userData['studentRelationships'],
            'roles' => $userData['roles'],
            // 'classSubjects', 'students', 'courses' are no longer passed as props as per previous discussions
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
            'worker_details.job_status' => ['nullable', 'string'],
            'worker_details.job_status_date' => ['nullable', 'date'],
            'worker_details.decree_number' => ['nullable', 'string'],
            'worker_details.degree_title' => ['nullable', 'string'],
            'worker_details.schedule' => ['nullable', 'array'],
            'worker_details.class_subject_id' => ['nullable', 'exists:class_subjects,id'],

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
            $this->userService->assignRoleWithDetails($user, $request->school_id, $request->role_id, $request->all(), $creator);
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
            $user->update([
                $type => $url
            ]);

            return back()->with('success', 'Imagen subida exitosamente');
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