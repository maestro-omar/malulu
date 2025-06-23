<?php

namespace App\Http\Controllers\System;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Services\RoleService;

class RoleAdminController extends SystemBaseController
{
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
        $this->middleware('permission:superadmin');
    }

    /**
     * Display a listing of the roles.
     */
    public function index(): Response
    {
        return Inertia::render('Roles/Index', [
            'roles' => $this->roleService->getRoles(),
            'permissions' => $this->roleService->getPermissions(),
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create(): Response
    {
        return Inertia::render('Roles/Create', [
            'permissions' => $this->roleService->getPermissions(),
        ]);
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        try {
            $this->roleService->createRole($request->all());
            return redirect()->route('roles.index')
                ->with('success', 'Rol creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role): Response
    {
        return Inertia::render('Roles/Edit', [
            'role' => $role->load('permissions'),
            'permissions' => $this->roleService->getPermissions(),
        ]);
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
    {
        try {
            $this->roleService->updateRole($role, $request->all());
            return redirect()->route('roles.index')
                ->with('success', 'Rol actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        try {
            $this->roleService->deleteRole($role);
            return redirect()->route('roles.index')
                ->with('success', 'Rol eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('roles.index')
                ->with('error', $e->getMessage());
        }
    }
}