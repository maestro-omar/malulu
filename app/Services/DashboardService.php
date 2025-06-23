<?php

namespace App\Services;

use App\Services\UserService;
use App\Models\Role;

class DashboardService
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getData($request, $loggedUser)
    {
        $loggedUserData = $this->userService->getUserShowData($loggedUser);
        $flags = self::getFlagsForCards($loggedUser, $loggedUserData);
        return [
            'loggedUserData' => $loggedUserData,
            'rolesCardsFlags' => $flags
        ];
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
