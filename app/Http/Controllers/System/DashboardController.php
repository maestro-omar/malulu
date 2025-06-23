<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\System\SystemBaseController;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Models\Role;
use Inertia\Inertia;
use Diglactic\Breadcrumbs\Breadcrumbs;

class DashboardController extends SystemBaseController
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function dashboard(Request $request)
    {
        $loggedUser = auth()->user();
        $loggedUserData = $this->userService->getUserShowData($loggedUser);
        // dd($loggedUserData);
        return Inertia::render('Dashboard', [
            'loggedUserData' => $loggedUserData,
            'rolesCardsFlags' => self::getFlagsForCards($loggedUser, $loggedUserData),
            'breadcrumbs' => Breadcrumbs::generate('dashboard'),
        ]);
    }

    private static function getFlagsForCards($loggedUser, $loggedUserData)
    {

        $flags = [
            'isGlobalAdmin' => $loggedUser->isSuperadmin(),
            'isSchoolAdmin' => false,
            'isTeacher'  => false,
            'isParent'  => false,
            'isFormerStudent'  => false,
            'isOtherWorker'  => false,
            'isStudent'  => false,
            'isFormerStudent'  => false,
            'isCooperative'  => false,
        ];
        if (!$loggedUser->isSuperadmin()) {
            foreach ($loggedUserData['all_roles_across_teams'] as $roleData) {
                $roleCode = $roleData['code'];

                if ($roleCode === Role::ADMIN) {
                    $flags['isSchoolAdmin'] = true;
                }

                if (Role::isTeacher($roleCode)) {
                    $flags['isTeacher'] = true;
                }

                if ($roleCode === Role::STUDENT) {
                    $flags['isStudent'] = true;
                }

                if ($roleCode === Role::GUARDIAN) {
                    $flags['isParent'] = true;
                }

                if ($roleCode === Role::FORMER_STUDENT) {
                    $flags['isFormerStudent'] = true;
                }

                if ($roleCode === Role::COOPERATIVE) {
                    $flags['isCooperative'] = true;
                }

                if (in_array($roleCode, [Role::CLASS_ASSISTANT, Role::LIBRARIAN])) {
                    $flags['isOtherWorker'] = true;
                }
            }
        }
        return $flags;
    }
}
