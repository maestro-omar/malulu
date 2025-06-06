<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Log;

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

        // Debug information
        Log::info('User:', [
            'id' => $user?->id,
            'name' => $user?->name,
            'email' => $user?->email,
            'roles' => $user?->getRoleNames(),
            'permissions' => $user?->getAllPermissions()->pluck('name'),
        ]);

        Log::info('Menu Items:', $menuItems);

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
                    ],
                ] : null,
            ],
            'menu' => [
                'items' => $menuItems,
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

        return $items;
    }
}
