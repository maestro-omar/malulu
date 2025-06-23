<?php

namespace App\Services;

use App\Services\UserService;
use App\Models\Role;
use App\Models\User;

class DashboardService
{
    private Userservice $userService;
    private User $user;
    private $userData;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getData($request, User $loggedUser)
    {
        $this->user = $loggedUser;
        $this->userData = $this->userService->getUserShowData($loggedUser);
        $data = $this->getFlagsForCards();
        return $data + [
            'loggedUserData' => $this->userData
        ];
    }
    private function getFlagsForCards()
    {

        $isGlobalAdmin =  $this->user->isSuperadmin();
        $flags = [
            'isGlobalAdmin' => $isGlobalAdmin,
            'isSchoolAdmin' => [],
            'isTeacher'  => [],
            'isGuardian'  => [],
            'isFormerStudent'  => [],
            'isOtherWorker'  => [],
            'isStudent'  => [],
            'isFormerStudent'  => [],
            'isCooperative'  => [],
        ];
        $schools = [];
        if (!$isGlobalAdmin) {
            $rolesAndSchools = $this->userData['all_roles_across_teams'];
            foreach ($rolesAndSchools as $roleData) {
                $roleCode = $roleData['code'];
                $schoolId = $roleData['pivot']['team_id'];

                if ($roleCode === Role::ADMIN) {
                    $flags['isSchoolAdmin'][$schoolId] = $this->schoolAdmin($roleData);
                } elseif (Role::isTeacher($roleCode)) {
                    $flags['isTeacher'][$schoolId] = $this->schoolTeacher($roleData);
                } elseif ($roleCode === Role::STUDENT) {
                    $flags['isStudent'][$schoolId] = $this->schoolStudent($roleData);
                } elseif ($roleCode === Role::GUARDIAN) {
                    $flags['isGuardian'][$schoolId] = $this->schoolGuardian($roleData);
                } elseif ($roleCode === Role::FORMER_STUDENT) {
                    $flags['isFormerStudent'][$schoolId] = $this->schoolFormerStudent($roleData);
                } elseif ($roleCode === Role::COOPERATIVE) {
                    $flags['isCooperative'][$schoolId] = $this->schoolCooperative($roleData);
                } elseif (Role::isWorker($roleCode) && !Role::isTeacher($roleCode)) {
                    $flags['isOtherWorker'][$schoolId] = $this->schoolWorker($roleData);
                }
            }
        }
        return ['rolesCardsFlags' => $flags, 'schools' => $schools];
    }

    /**
     * Get dashboard data for a school administrator.
     *
     * @param  \App\Models\User  $loggedUser
     * @param  array  $roleData
     * @return array
     */
    protected function schoolAdmin(array $roleData): array
    {
        $data = ['news' => ['Novedad1', 'Novedad DOS']];
        return $data;
    }

    /**
     * Get dashboard data for a teacher in a school.
     *
     * @param  \App\Models\User  $loggedUser
     * @param  array  $roleData
     * @return array
     */
    protected function schoolTeacher(array $roleData): array
    {
        dd($roleData);
        return [];
    }

    /**
     * Get dashboard data for a student in a school.
     *
     * @param  \App\Models\User  $loggedUser
     * @param  array  $roleData
     * @return array
     */
    protected function schoolStudent(array $roleData): array
    {
        return [];
    }

    /**
     * Get dashboard data for a guardian in a school.
     *
     * @param  \App\Models\User  $loggedUser
     * @param  array  $roleData
     * @return array
     */
    protected function schoolGuardian(array $roleData): array
    {
        return [];
    }

    /**
     * Get dashboard data for a former student in a school.
     *
     * @param  \App\Models\User  $loggedUser
     * @param  array  $roleData
     * @return array
     */
    protected function schoolFormerStudent(array $roleData): array
    {
        return [];
    }

    /**
     * Get dashboard data for a cooperative member in a school.
     *
     * @param  \App\Models\User  $loggedUser
     * @param  array  $roleData
     * @return array
     */
    protected function schoolCooperative(array $roleData): array
    {
        return [];
    }

    /**
     * Get dashboard data for other workers in a school (not teachers).
     *
     * @param  \App\Models\User  $loggedUser
     * @param  array  $roleData
     * @return array
     */
    protected function schoolWorker(array $roleData): array
    {
        return [];
    }
}
