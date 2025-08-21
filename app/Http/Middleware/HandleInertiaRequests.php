<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
// use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Entities\School;
// use App\Models\Catalogs\Role;
// use Spatie\Permission\Models\Permission;
use App\Services\UserContextService;


class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        try {
            $schoolGlobalId = School::specialGlobalId();
        } catch (\Exception $e) {
            throw new \Exception('Verificá la instalación de la aplicación <br>(no se encuentra la escuela "global")');
        }
        $user = $request->user();
        $menuItems = $this->getMenuItems($user);
        $userMenuItems = $this->getUserMenuItems($user);
        $flash = $this->getFlash($request);
        // dd($flash);
        return [
            ...parent::share($request),
            //
            'appName' => config('app.name', 'Malulu'),
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
                'schoolGlobalId' => $schoolGlobalId,
            ],
            'debug' => [
                'guard' => Auth::getDefaultDriver(),
            ],
            'flash' => $flash,
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

        $items = [];
        // $items[] =[
        //         'name' => 'Inicio',
        //         'route' => 'dashboard',
        //         'icon' => 'home',
        //     ];

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
    private function getFlash(Request $request)
    {
        return [
            'success' => $request->session()->get('success'),
            'error' => $request->session()->get('error'),
            'status' => $request->session()->get('status'),
        ];
    }
}
