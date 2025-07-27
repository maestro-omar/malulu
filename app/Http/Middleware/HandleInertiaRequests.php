<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Entities\School;
use App\Models\Catalogs\Role;
use Spatie\Permission\Models\Permission;
use App\Services\UserContextService;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $menuItems = $this->getMenuItems($user);
        $userMenuItems = $this->getUserMenuItems($user);
        // dd($user->permissionBySchoolDirect());
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'picture' => $user->picture,
                    //omar importante
                    'permissionBySchool' => $user->permissionBySchoolDirect(),
                    'schools' => UserContextService::relatedSchools(),
                    'activeSchool' => UserContextService::activeSchool(),
                ] : null,
            ],
            'menu' => [
                'items' => $menuItems,
                'userItems' => $userMenuItems,
            ],
            'constants' => [
                'schoolGlobalId' => School::specialGlobalId(),
            ],
            'debug' => [
                'guard' => Auth::getDefaultDriver(),
            ],
        ];
    }

    /**
     * Get the menu items based on user permissions
     */
    protected function getMenuItems($user): array
    {
        if (!$user) {
            return [];
        }

        $items = [
            [
                'name' => 'Inicio',
                'route' => 'dashboard',
                'icon' => 'home',
            ],
        ];

        // Add more menu items based on user permissions
        if ($user->can('user.manage')) {
            $items[] = [
                'name' => 'Usuarios',
                'route' => 'users.index',
                'icon' => 'users',
            ];
        }

        if ($user->can('school.view')) {
            $items[] = [
                'name' => 'Escuelas',
                'route' => 'schools.index',
                'icon' => 'academic-cap',
            ];
        }


        return $items;
    }

    /**
     * Get the user menu items (profile, logout, etc)
     */
    protected function getUserMenuItems($user): array
    {
        if (!$user) {
            return [];
        }

        $items = [];

        if ($user->can('superadmin')) {
            $items[] = [
                'name' => 'Ciclos lectivos',
                'route' => 'academic-years.index',
                'icon' => 'calendar',
            ];

            // Add Provinces menu item for superadmin
            $items[] = [
                'name' => 'Provincias',
                'route' => 'provinces.index',
                'icon' => 'location',
            ];

            // Add File Types menu item for superadmin
            $items[] = [
                'name' => 'Tipos de Archivo',
                'route' => 'file-types.index',
                'icon' => 'document',
            ];

            // Add File Subtypes menu item for superadmin
            $items[] = [
                'name' => 'Subtipos de Archivo',
                'route' => 'file-subtypes.index',
                'icon' => 'document-text',
            ];
        }

        // Add separator if we have any items before
        if (!empty($items)) {
            $items[] = [
                'type' => 'separator'
            ];
        }

        $items[] = [
            'name' => 'Perfil',
            'route' => 'profile.edit',
            'icon' => 'user',
        ];
        $items[] = [
            'name' => 'Salir',
            'route' => 'logout.get',
            'icon' => 'logout',
        ];
        return $items;
    }
}
