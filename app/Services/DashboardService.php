<?php

namespace App\Services;

use App\Models\Catalogs\JobStatus;
use App\Services\UserService;
use App\Services\CourseService;
use App\Models\Catalogs\Role;
use App\Models\Relations\RoleRelationship;
use App\Models\Entities\User;
use App\Models\Catalogs\SchoolLevel;
use App\Models\Catalogs\SchoolShift;

class DashboardService
{
    private Userservice $userService;
    private CourseService $courseService;
    private User $user;
    private $userData;

    public function __construct(UserService $userService, CourseService $courseService)
    {
        $this->userService = $userService;
        $this->courseService = $courseService;
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
            $roleRelationships = $this->user->roleRelationships->all(); //OMAR ¿cambiar por ->activeRoleRelationships?
            foreach ($roleRelationships as $roleRel) {
                $count++;
                $schoolId = $roleRel->school_id;
                $roleData = $rolesAndSchools[$roleRel->role_id . '-' . $schoolId] ?? null;
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
        return ['rolesCardsFlags' => $flags, 'count' => $count];
    }

    /**
     * Get dashboard data for a school administrator.
     *
     * @param  \App\Models\Entities\User  $loggedUser
     * @param  array  $roleData
     * @return array
     */
    protected function schoolAdmin(RoleRelationship $roleRel): array
    {
        $data = ['news' => ['Novedad1', 'Novedad DOS']];
        return $data;
    }

    /**
     * Get dashboard data for a teacher in a school.
     *
     * @param  \App\Models\Entities\User  $loggedUser
     * @param  array  $roleData
     * @return array
     */
    protected function schoolTeacher(RoleRelationship $roleRel): array
    {
        $dataToPanel = [];
        // dd(SchoolShift::optionsByCode());
        // dd(SchoolShift::getNameById(2));
        // dd(SchoolShift::getNameByCode(SchoolShift::MORNING));
        // dd($roleRel);
        $dataToPanel['job_status'] = JobStatus::getNameById($roleRel->workerRelationship->job_status_id);
        $dataToPanel['job_status_date'] = $roleRel->workerRelationship->job_status_date;
        $dataToPanel['decree_number'] = $roleRel->workerRelationship->decree_number;
        $dataToPanel['schedule'] = $roleRel->workerRelationship->schedule;
        $dataToPanel['degree_title'] = $roleRel->workerRelationship->degree_title;
        $dataToPanel['class_name'] = $roleRel->workerRelationship->classSubject->name;
        $dataToPanel['school_level'] =  SchoolLevel::getNameById($roleRel->workerRelationship->classSubject->school_level_id);
        $dataToPanel['courses'] = $this->courseService->parseTeacherCourses($roleRel->teacherCourses);
        //
        dd($dataToPanel, $roleRel->workerRelationship);
        return $dataToPanel;
    }

    /**
     * Get dashboard data for a student in a school.
     *
     * @param  \App\Models\Entities\User  $loggedUser
     * @param  array  $roleData
     * @return array
     */
    protected function schoolStudent(RoleRelationship $roleRel): array
    {
        dd($roleRel->studentRelationship);
        return [];
    }

    /**
     * Get dashboard data for a guardian in a school.
     *
     * @param  \App\Models\Entities\User  $loggedUser
     * @param  array  $roleData
     * @return array
     */
    protected function schoolGuardian(RoleRelationship $roleRel): array
    {
        // dd($roleRel->guardian_relationship);
        return $roleRel->guardianRelationship;
    }

    /**
     * Get dashboard data for a former student in a school.
     *
     * @param  \App\Models\Entities\User  $loggedUser
     * @param  array  $roleData
     * @return array
     */
    protected function schoolFormerStudent(RoleRelationship $roleRel): array
    {
        return [];
    }

    /**
     * Get dashboard data for a cooperative member in a school.
     *
     * @param  \App\Models\Entities\User  $loggedUser
     * @param  array  $roleData
     * @return array
     */
    protected function schoolCooperative(RoleRelationship $roleRel): array
    {
        return [];
    }

    /**
     * Get dashboard data for other workers in a school (not teachers).
     *
     * @param  \App\Models\Entities\User  $loggedUser
     * @param  array  $roleData
     * @return array
     */
    protected function schoolWorker(RoleRelationship $roleRel): array
    {
        return [];
    }
}
