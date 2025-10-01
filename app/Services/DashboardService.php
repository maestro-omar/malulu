<?php

namespace App\Services;

use App\Models\Catalogs\JobStatus;
use App\Services\UserService;
use App\Services\CourseService;
use App\Services\AcademicEventService;
use App\Models\Catalogs\Role;
use App\Models\Relations\RoleRelationship;
use App\Models\Entities\User;
use App\Models\Catalogs\SchoolLevel;

class DashboardService
{
    private Userservice $userService;
    private CourseService $courseService;
    private AcademicEventService $academicEventService;
    private User $user;

    public function __construct(UserService $userService, CourseService $courseService, AcademicEventService $academicEventService)
    {
        $this->userService = $userService;
        $this->courseService = $courseService;
        $this->academicEventService = $academicEventService;
    }

    public function getData($request)
    {
        $this->user = auth()->user();
        $data = $this->getFlagsForCards();

        $eventsData = $this->userService->getCalendarData($this->user);

        return $data + [
            'eventsData' => $eventsData,
            'loggedUserData' => $this->userService->getBasicUserShowData($this->user)
        ];
    }

    private function getFlagsForCards()
    {
        $isGlobalAdmin = $this->user->isSuperadmin();
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

                if ($roleCode === Role::SCHOOL_ADMIN) {
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
        /*
        dd($flags);
        array:8 [▼ // app\Services\DashboardService.php:82
  "isGlobalAdmin" => false
  "isSchoolAdmin" => []
  "isTeacher" => array:1 [▼
    2 => array:8 [▼
      "job_status" => "Permanente"
      "job_status_date" => 
Illuminate\Support
\
Carbon @1646179200
 {#1869 ▶}
      "decree_number" => "747339/43"
      "schedule" => array:5 [▶]
      "degree_title" => "Profesor de nivel primario egb 1 y 2"
      "subject_name" => null
      "school_level" => "Primaria"
      "courses" => null
    ]
  ]
  "isGuardian" => []
  "isFormerStudent" => []
  "isOtherWorker" => []
  "isStudent" => []
  "isCooperative" => []
]
        */
        return ['rolesCardsFlags' => $flags, 'combinationCount' => $count];
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
        $dataToPanel['subject_name'] = $roleRel->workerRelationship->classSubject ? $roleRel->workerRelationship->classSubject->name : null;
        $dataToPanel['school_level'] = SchoolLevel::getNameById($roleRel->workerRelationship->classSubject ? $roleRel->workerRelationship->classSubject->school_level_id : $roleRel->school_level_id);
        $activeCoursesRels = $roleRel->teacherCourses->filter(function ($teacherCourseRel) {
            return $teacherCourseRel->course->active;
        });
        $dataToPanel['courses'] = $this->courseService->parseTeacherCourses($activeCoursesRels);
        /* dd($dataToPanel);

"job_status" => "Permanente"
"job_status_date" => Illuminate\Support\Carbon @1646179200 {#1869 ▶}
"decree_number" => "747339/43"
"schedule" => array:5 [▶]
"degree_title" => "Profesor de nivel primario egb 1 y 2"
"subject_name" => null
"school_level" => "Primaria"
"courses" => null

        */
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
        // dd('schoolStudentschoolStudent', $roleRel->studentRelationship);
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
        // dd('guardian_relationshipguardian_relationship', $roleRel->guardian_relationship);
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
