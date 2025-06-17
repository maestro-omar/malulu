<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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

        // More detailed debug information
        // Log::info('Debug Menu Items:', [
        //     'user_id' => $user?->id,
        //     'user_name' => $user?->name,
        //     'user_email' => $user?->email,
        //     'user_roles' => $user?->getRoleNames()->toArray(),
        //     'user_permissions' => $user?->getAllPermissions()->pluck('name')->toArray(),
        //     'can_view_schools' => $user?->can('view schools'),
        //     'menu_items' => $menuItems,
        //     'guard' => Auth::getDefaultDriver(),
        // ]);

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'can' => [
                        'view users' => $user->can('view users'),
                        'create users' => $user->can('create users'),
                        'edit users' => $user->can('edit users'),
                        'delete users' => $user->can('delete users'),
                        'view schools' => $user->can('view schools'),
                        'create schools' => $user->can('create schools'),
                        'edit schools' => $user->can('edit schools'),
                        'delete schools' => $user->can('delete schools'),
                        'superadmin' => $user->can('superadmin'),
                    ],
                ] : null,
            ],
            'menu' => [
                'items' => $menuItems,
                'userItems' => $userMenuItems,
            ],
            'debug' => [
                'can_view_schools' => $user?->can('view schools'),
                'user_roles' => $user?->getRoleNames()->toArray(),
                'user_permissions' => $user?->getAllPermissions()->pluck('name')->toArray(),
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
        if ($user->can('view users')) {
            $items[] = [
                'name' => 'Usuarios',
                'route' => 'users.index',
                'icon' => 'users',
            ];
        }

        if ($user->can('view schools')) {
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
                'name' => 'Ciclos escolares',
                'route' => 'academic-years.index',
                'icon' => 'calendar',
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
