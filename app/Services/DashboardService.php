<?php

namespace App\Services;

use App\Models\JobStatus;
use App\Services\UserService;
use App\Models\Role;
use App\Models\User;
use App\Models\School;
use App\Models\SchoolLevel;
use App\Models\SchoolShift;

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

        $schoolIds = [];
        $count = $isGlobalAdmin ? 1 : 0;

        if (!$isGlobalAdmin) {
            $rolesAndSchools = $this->user->allRolesAcrossTeamsParsed();
            $roleRelationships = $this->user->roleRelationships->toArray();
            foreach ($roleRelationships as $roleRel) {
                $count++;
                $schoolId = $roleRel['school_id'];
                $roleData = $rolesAndSchools[$roleRel['role_id'] . '-' . $schoolId] ?? null;
                if (empty($roleData))
                    dd('ERROR INESPERADO: role no encontrado', $roleRel, $rolesAndSchools);
                $roleCode = $roleData['role_code'];

                if ($roleCode === Role::ADMIN) {
                    $flags['isSchoolAdmin'][$schoolId] = $this->schoolAdmin($roleRel);
                } elseif (Role::isTeacher($roleCode)) {
                    $flags['isTeacher'][$schoolId] = $this->schoolTeacher($roleRel);
                } elseif ($roleCode === Role::STUDENT) {
                    $flags['isStudent'][$schoolId] = $this->schoolStudent($roleRel);
                } elseif ($roleCode === Role::GUARDIAN) {
                    $flags['isGuardian'][$schoolId] = $this->schoolGuardian($roleRel);
                } elseif ($roleCode === Role::FORMER_STUDENT) {
                    $flags['isFormerStudent'][$schoolId] = $this->schoolFormerStudent($roleRel);
                } elseif ($roleCode === Role::COOPERATIVE) {
                    $flags['isCooperative'][$schoolId] = $this->schoolCooperative($roleRel);
                } elseif (Role::isWorker($roleCode) && !Role::isTeacher($roleCode)) {
                    $flags['isOtherWorker'][$schoolId] = $this->schoolWorker($roleRel);
                }
                if (!in_array($schoolId, $schoolIds))
                    $schoolIds[] = $schoolId;
            }
        }
        $schools = School::select('cue', 'id', 'name', 'short')->find($schoolIds)->keyBy('id')->toArray();
        return ['rolesCardsFlags' => $flags, 'schools' => $schools, 'count' => $count];
    }

    /**
     * Get dashboard data for a school administrator.
     *
     * @param  \App\Models\User  $loggedUser
     * @param  array  $roleData
     * @return array
     */
    protected function schoolAdmin(array $roleRel): array
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
    protected function schoolTeacher(array $roleRel): array
    {

        $dataToPanel = [];
        // dd(SchoolShift::optionsByCode());
        // dd(SchoolShift::getNameById(2));
        // dd(SchoolShift::getNameByCode(SchoolShift::MORNING));
        $dataToPanel['job_status'] = JobStatus::getNameById($roleRel['worker_relationship']['job_status_id']);
        $dataToPanel['job_status_date'] = $roleRel['worker_relationship']['job_status_date'];
        $dataToPanel['decree_number'] = $roleRel['worker_relationship']['decree_number'];
        $dataToPanel['schedule'] = $roleRel['worker_relationship']['schedule'];
        $dataToPanel['degree_title'] = $roleRel['worker_relationship']['degree_title'];
        $dataToPanel['class_name'] = $roleRel['worker_relationship']['class_subject']['name'];
        $dataToPanel['school_level'] =  SchoolLevel::getNameById($roleRel['worker_relationship']['class_subject']['school_level_id']);
        //
        dd($dataToPanel, $roleRel['worker_relationship']);
        return $dataToPanel;
    }

    /**
     * Get dashboard data for a student in a school.
     *
     * @param  \App\Models\User  $loggedUser
     * @param  array  $roleData
     * @return array
     */
    protected function schoolStudent(array $roleRel): array
    {
        dd($roleRel['student_relationship']);
        return [];
    }

    /**
     * Get dashboard data for a guardian in a school.
     *
     * @param  \App\Models\User  $loggedUser
     * @param  array  $roleData
     * @return array
     */
    protected function schoolGuardian(array $roleRel): array
    {
        // dd($roleRel['guardian_relationship']);
        return $roleRel['guardian_relationship'];
    }

    /**
     * Get dashboard data for a former student in a school.
     *
     * @param  \App\Models\User  $loggedUser
     * @param  array  $roleData
     * @return array
     */
    protected function schoolFormerStudent(array $roleRel): array
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
    protected function schoolCooperative(array $roleRel): array
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
    protected function schoolWorker(array $roleRel): array
    {
        return [];
    }
}
