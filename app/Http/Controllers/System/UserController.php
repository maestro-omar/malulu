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
use Illuminate\Validation\ValidationException;

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
        $user->load(['allRolesAcrossTeams']);

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
                'key' => $role->key,
                'team_id' => $role->pivot->team_id
            ];
        })->toArray();

        // Add schools to user data
        $transformedUser['schools'] = $schools->map(function ($school) {
            return [
                'id' => $school->id,
                'name' => $school->name,
                'short' => $school->short
            ];
        })->toArray();

        return Inertia::render('Users/Show', [
            'user' => $transformedUser
        ]);
    }
}