<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
// use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Entities\School;
use App\Models\Entities\User;
use App\Models\Entities\AcademicYear;

use App\Models\Catalogs\Role;
use App\Models\Catalogs\SchoolLevel;
use App\Models\Catalogs\SchoolShift;
use App\Models\Catalogs\AttendanceStatus;
use App\Models\Catalogs\JobStatus;
// use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

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
        // Handle case when database is empty (before migrations)
        try {
            $schoolGlobalId = School::specialGlobalId();
        } catch (\Exception $e) {
            // If database is empty, allow admin-tools to work
            // Check if this is an admin-tools route
            if ($request->routeIs('admin-tools.*')) {
                $schoolGlobalId = null; // Allow admin-tools to work without global school
            } else {
                throw new \Exception('Verificá la instalación de la aplicación <br>(no se encuentra la escuela "global")');
            }
        }
        $user = $request->user();
        $activeSchool = UserContextService::activeSchool();
        $activeSchoolObj = $activeSchool ? School::find($activeSchool['id']) : null;
        if ($activeSchool) {
            app(PermissionRegistrar::class)->setPermissionsTeamId($activeSchool['id']);
        }

        // Skip menu generation for admin-tools when database is empty
        $headerMenuItems = [];
        $sideMenuItems = [];
        if ($schoolGlobalId !== null) {
            $headerMenuItems = $this->headerMenuItems($user, $activeSchoolObj);
            $sideMenuItems = $this->sideMenuItems($user, $activeSchoolObj);
        }
        $flash = $this->getFlash($request);
        // Warn admins when no academic year is defined for current date (avoids null reference errors app-wide)
        $noCurrentAcademicYear = false;
        $canManageAcademicYears = false;
        if ($schoolGlobalId !== null && $user) {
            $canManageAcademicYears = $user->isSuperadmin() || $user->can('country.manage');
            $isAdmin = $canManageAcademicYears || $user->can('admin.create');
            if ($isAdmin) {
                try {
                    $currentYear = (int) date('Y');
                    $noCurrentAcademicYear = AcademicYear::findByYear($currentYear) === null;
                } catch (\Throwable $e) {
                    // Ignore (e.g. DB not ready)
                }
            }
        }
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
                    'id_number' => $user->id_number,
                    // 'gender' => $user->gender,
                    'birthdate' => $user->birthdate,
                    // 'phone' => $user->phone,
                    // 'address' => $user->address,
                    // 'locality' => $user->locality,
                    // 'province_id' => $user->province_id,
                    // 'country_id' => $user->country_id,
                    // 'birth_place' => $user->birth_place,
                    // 'nationality' => $user->nationality,
                    // 'critical_info' => $user->critical_info,
                    // 'occupation' => $user->occupation,
                    // 'diagnoses_data' => $user->diagnoses_data,
                    // 'email_verified_at' => $user->email_verified_at,
                    //omar importante
                    'permissionBySchool' => $user->permissionBySchoolDirect(),
                    'schools' => UserContextService::relatedSchools(),
                    'activeSchool' => $activeSchool,
                ] : null,
            ],
            'menu' => [
                'items' => $headerMenuItems,
                'userItems' => $sideMenuItems,
            ],
            'constants' => [
                'schoolGlobalId' => $schoolGlobalId,
                'catalogs' => $schoolGlobalId !== null ? [
                    'schoolLevels' => SchoolLevel::vueOptions(),
                    'schoolShifts' => SchoolShift::vueOptions(),
                    'attendanceStatuses' => AttendanceStatus::vueOptions(),
                    'jobStatuses' => JobStatus::vueOptions(),
                    'roles' => Role::vueOptions(),
                ] : [
                    'schoolLevels' => [],
                    'schoolShifts' => [],
                    'attendanceStatuses' => [],
                    'jobStatuses' => [],
                    'roles' => [],
                ], // Empty catalogs if database not initialized
            ],
            'debug' => [
                'guard' => Auth::getDefaultDriver(),
            ],
            'flash' => $flash,
            'noCurrentAcademicYear' => $noCurrentAcademicYear,
            'canManageAcademicYears' => $canManageAcademicYears,
        ];
    }

    /**
     * Get the menu items based on user permissions
     */
    protected function headerMenuItems(?User $user, ?School $school): array
    {
        if (!$user) {
            return [];
        }

        $items = [];
        $items[] = [
            'name' => 'Calendario',
            'route' => 'calendar.index',
            'icon' => 'calendar_month',
            'colorClass' => 'text-deep-purple',
        ];
        if ($user->isSuperadmin()) {
            // Role::SUPERADMIN
            $items[] = [
                'name' => 'Usuarios',
                'route' => 'users.index',
                'icon' => 'groups_2',
            ];
            $items[] = [
                'name' => 'Escuelas',
                'route' => 'schools.index',
                'icon' => 'holiday_village',
            ];
        } elseif ($user->can('country.manage')) {
            dd('Role::CONFIGURATOR WIP');
        } elseif ($user->can('admin.create')) {
            $items[] = [
                'name' => $school->short,
                'href' => route('school.show', $school->slug),
                'icon' => 'home',
                'colorClass' => 'text-blue',
            ];
            $items[] = [
                'name' => 'Estudiantes',
                'href' => route('school.students', $school->slug),
                'icon' => 'person',
                'colorClass' => 'text-teal',
            ];
            /* too many buttons on header
            $items[] = [
                'name' => 'Madres/padres',
                'href' => route('school.guardians', $school->slug),
                'icon' => 'family_restroom',
                'colorClass' => 'text-grey',
            ];*/
            $items[] = [
                'name' => 'Personal',
                'href' => route('school.staff', $school->slug),
                'icon' => 'co_present',
                'colorClass' => 'text-orange',
            ];
        } elseif ($school) {
            $items[] = [
                'name' => 'Mi escuela',
                'href' => route('school.show', $school->slug),
                'icon' => 'home',
                'colorClass' => 'text-blue',
            ];
        }

        return $items;
    }

    /**
     * Get the user menu items (profile, logout, etc)
     */
    protected function sideMenuItems(?User $user, ?School $school): array
    {
        if (!$user) {
            return [];
        }

        $items = [];

        if ($user->isSuperadmin() || $user->can('country.manage')) {
            $items = array_merge($items, $this->sideMenuItemsForConfig());
            $items[] = ['type' => 'separator'];
        } elseif ($user->can('admin.create')) {
            $items = array_merge($items, $this->sideMenuItemsForSchoolAdmin($school));
            $items[] = ['type' => 'separator'];
        } elseif ($user->can('academic-event.manage')) {
            $items[] = [
                'name' => 'Eventos',
                'href' => route('school.academic-events.index', $school->slug),
                'icon' => 'event',
            ];
            $items[] = ['type' => 'separator'];
        }


        $items = array_merge($items, $this->sideMenuItemsForAll());

        return $items;
    }


    private function sideMenuItemsForConfig(): array
    {
        $items = [];
        $items[] = [
            'name' => 'Ciclos lectivos',
            'route' => 'academic-years.index',
            'icon' => 'calendar',
        ];

        $items[] = [
            'name' => 'Provincias',
            'route' => 'provinces.index',
            'icon' => 'location',
        ];

        $items[] = [
            'name' => 'Tipos de Archivo',
            'route' => 'file-types.index',
            'icon' => 'document',
        ];

        $items[] = [
            'name' => 'Subtipos de Archivo',
            'route' => 'file-subtypes.index',
            'icon' => 'document-text',
        ];

        $items[] = [
            'name' => 'Diagnósticos',
            'route' => 'diagnoses.index',
            'icon' => 'medical_services',
        ];

        $items[] = [
            'name' => 'Eventos recurrentes',
            'route' => 'recurrent-events.index',
            'icon' => 'event_repeat',
        ];

        $defaultSchool = School::where('code', School::CUE_LUCIO_LUCERO)->first();

        $items[] = [
            'name' => 'Eventos',
            'href' => route('school.academic-events.index', $defaultSchool->slug),
            'icon' => 'event',
        ];


        // $items[] = [
        //     'name' => 'Materias',
        //     'route' => 'class-subject.index',
        //     'icon' => 'interests',
        // ];

        return $items;
    }

    private function sideMenuItemsForSchoolAdmin(School $school): array
    {
        if (!$school) return [];
        // Items for: students, guardians, staff and loop school levels for links to courses of level

        $items = [];

        $items[] = [
            'name' => $school->short,
            'href' => route('school.show', $school),
            'icon' => 'home',
        ];


        $items[] = [
            'name' => $school->locality->district->province->name,
            'href' => route('school.province.show', $school),
            'icon' => 'landscape',
        ];
        // If school and school_levels are available in the request or context
        $schoolLevels = $school ? $school->schoolLevels : [];

        foreach ($schoolLevels as $level) {
            $items[] = [
                'name' => 'Cursos (' . ($level->name ?? $level['name'] ?? '') . ')',
                'href' => route('school.courses', [$school, $level]),
                'params' => [
                    'school' => $school->slug ?? $school['slug'] ?? null,
                    'schoolLevel' => $level->code ?? $level['code'] ?? null,
                ],
                'icon' => 'class',
            ];
        }

        $items[] = ['type' => 'separator'];
        $items[] = [
            'name' => 'Estudiantes',
            'href' => route('school.students', $school->slug),
            'icon' => 'person',
        ];

        $items[] = [
            'name' => 'Madres/padres',
            'href' => route('school.guardians', $school->slug),
            'icon' => 'family_restroom',
        ];

        $items[] = [
            'name' => 'Personal',
            'href' => route('school.staff', $school->slug),
            'icon' => 'co_present',
        ];

        $items[] = ['type' => 'separator'];
        $items[] = [
            'name' => 'Eventos',
            'href' => route('school.academic-events.index', $school->slug),
            'icon' => 'event',
        ];


        return $items;
    }
    // private function sideMenuItemsForSchoolAdmin() {}
    private function sideMenuItemsForAll()
    {
        return [
            [
                'name' => 'Documentación',
                'route' => 'files.index',
                'icon' => 'folder',
            ],
            ['type' => 'separator'],
            [
                'name' => 'Perfil',
                'route' => 'profile.show',
                'icon' => 'account_circle',
            ],
            [
                'name' => 'Salir',
                'route' => 'logout.get',
                'icon' => 'logout',
            ]
        ];
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
