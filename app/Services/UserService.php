<?php

namespace App\Services;

use App\Models\Entities\School;
use App\Models\Entities\User;
use Illuminate\Support\Facades\Auth;
use App\Traits\UserServiceCrud;
use App\Traits\UserServiceList;
use App\Services\FileService;

class UserService
{
    use UserServiceCrud, UserServiceList;
    private FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }
    /**
     * Get user data for show view with all relationships
     */
    public function getFullUserShowData(User $user): array
    {
        return $this->getUserShowData($user, false, true, true, true);
    }
    public function getBasicUserShowData(User $user): array
    {
        return $this->getUserShowData($user, true, false, false, false);
    }
    public function getStudentUserShowData(User $user): array
    {
        return $this->getUserShowData($user, false, true, false, false);
    }
    public function getGuardianUserShowData(User $user): array
    {
        return $this->getUserShowData($user, false, false, true, false);
    }
    public function getWorkerUserShowData(User $user): array
    {
        return $this->getUserShowData($user, false, false, false, true);
    }

    public function getUserShowData(
        User $user,
        bool $basicDataOnly,
        bool $getDataForStudent,
        bool $getDataForGuardian,
        bool $getDataForWorker,
    ): array {
        if ($basicDataOnly) {
            $getDataForStudent = false;
            $getDataForWorker = false;
            $getDataForGuardian = false;
        }
        $loadRelations = [
            'province',
            'country',
        ];
        if (!$basicDataOnly) {
            $loadRelations[] = 'allRolesAcrossTeams';
        }
        $loadRelations['roleRelationships'] = $basicDataOnly ? null : function ($query) use ($getDataForStudent, $getDataForGuardian, $getDataForWorker) {
            $width = [
                'workerRelationship' => $getDataForWorker ? function ($query) {
                    $query->with(['classSubject']);
                } : null,
                'guardianRelationship' =>  $getDataForGuardian ? function ($query) {
                    $query->with(['student' => function ($query) {
                        $query->with(['roleRelationships' => function ($query) {
                            $query->with(['studentRelationship' => function ($query) {
                                $query->with(['currentCourse.schoolLevel']);
                            }]);
                        }]);
                    }]);
                } : null,
                'studentRelationship' =>  $getDataForStudent ? function ($query) {
                    $query->with(['currentCourse.schoolLevel']);
                } : null,
                'creator'
            ];
            $width = array_filter($width);
            $query->with($width);
        };
        $loadRelations = array_filter($loadRelations);
        $user->load($loadRelations);

        // Transform the data to include roles and schools
        $basicKeys = [
            'id',
            'address',
            'birthdate',
            'country',
            'email',
            'firstname',
            'gender',
            'id_number',
            'lastname',
            'locality',
            'name',
            'nationality',
            'phone',
            'picture',
            'province',
            'created_at',
            'updated_at'
        ];
        $transformedUser = array_intersect_key($user->toArray(), array_flip($basicKeys));
        $allRolesAcrossTeams = $basicDataOnly ? null : collect($user->allRolesAcrossTeams);

        // Get unique school IDs from roles
        $schoolIds = $basicDataOnly ? null : $allRolesAcrossTeams
            ->pluck('pivot.team_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Get schools for these IDs
        $schools = $basicDataOnly ? null : School::whereIn('id', $schoolIds)->get();

        $transformedUser['roles'] = $basicDataOnly ? null : $allRolesAcrossTeams->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'short' => $role->short,
                'code' => $role->code,
                'team_id' => $role->pivot->team_id
            ];
        })->values()->toArray();

        // Add schools to user data
        $transformedUser['schools'] = $basicDataOnly ? null : $schools->map(function ($school) {
            return [
                'id' => $school->id,
                'name' => $school->name,
                'short' => $school->short,
                'slug' => $school->slug,
            ];
        })->values()->toArray();

        // Add role relationships data
        $transformedUser['roleRelationships'] = $basicDataOnly ? null : $user->roleRelationships->map(function ($relationship) {
            return [
                'id' => $relationship->id,
                'user_id' => $relationship->user_id,
                'role_id' => $relationship->role_id,
                'school_id' => $relationship->school_id,
                'start_date' => $relationship->start_date ? $relationship->start_date->toDateString() : null,
                'end_date' => $relationship->end_date,
                'end_reason_id' => $relationship->end_reason_id,
                'notes' => $relationship->notes,
                'custom_fields' => $relationship->custom_fields,
                'created_at' => $relationship->created_at,
                'updated_at' => $relationship->updated_at,
                'creator' => $relationship->creator ? [
                    'id' => $relationship->creator->id,
                    'name' => $relationship->creator->name
                ] : null
            ];
        })->values()->toArray();

        // Add teacher relationships with detailed information
        $transformedUser['workerRelationships'] = !$getDataForWorker ? null : $user->roleRelationships
            ->pluck('workerRelationship')
            ->filter()
            ->map(function ($relationship) use ($user) {
                $roleRelationship = $user->roleRelationships->firstWhere('id', $relationship->role_relationship_id);
                $role = $user->allRolesAcrossTeams->firstWhere('id', $roleRelationship->role_id);

                return [
                    'id' => $relationship->id,
                    'role_relationship_id' => $relationship->role_relationship_id,
                    'role' => $role ? [
                        'id' => $role->id,
                        'name' => $role->name,
                        'short' => $role->short,
                        'code' => $role->code
                    ] : null,
                    'class_subject' => $relationship->classSubject ? [
                        'id' => $relationship->classSubject->id,
                        'name' => $relationship->classSubject->name,
                        'short_name' => $relationship->classSubject->short_name
                    ] : null,
                    'school_shift_id' => $relationship->school_shift_id,
                    'job_status_id' => $relationship->job_status_id,
                    'job_status_date' => $relationship->job_status_date,
                    'decree_number' => $relationship->decree_number,
                    'decree_file_id' => $relationship->decree_file_id,
                    'schedule' => $relationship->schedule,
                    'degree_title' => $relationship->degree_title,
                    'start_date' => $roleRelationship->start_date ? $roleRelationship->start_date->toDateString() : null,
                    'creator' => $roleRelationship->creator ? [
                        'id' => $roleRelationship->creator->id,
                        'name' => $roleRelationship->creator->name
                    ] : null
                ];
            })->values()->toArray();

        // Add guardian relationships with student information
        $transformedUser['guardianRelationships'] = !$getDataForGuardian ? null :  $user->roleRelationships
            ->pluck('guardianRelationship')
            ->filter()
            ->map(function ($relationship) use ($user) {
                $roleRelationship = $user->roleRelationships->firstWhere('id', $relationship->role_relationship_id);
                $student = $relationship->student;
                $studentRoleRelationship = $student ? $student->roleRelationships->first() : null;
                $studentRelationship = $studentRoleRelationship ? $studentRoleRelationship->studentRelationship : null;
                $currentCourse = $studentRelationship ? $studentRelationship->currentCourse : null;

                return [
                    'id' => $relationship->id,
                    'role_relationship_id' => $relationship->role_relationship_id,
                    'student' => $student ? [
                        'id' => $student->id,
                        'name' => $student->name,
                        'current_course' => $currentCourse ? $this->parseCurrentCourse($currentCourse) : null
                    ] : null,
                    'student_id' => $relationship->student_id,
                    'relationship_type' => $relationship->relationship_type,
                    'is_emergency_contact' => $relationship->is_emergency_contact,
                    'is_restricted' => $relationship->is_restricted,
                    'emergency_contact_priority' => $relationship->emergency_contact_priority,
                    'start_date' => $roleRelationship->start_date ? $roleRelationship->start_date->toDateString() : null,
                    'creator' => $roleRelationship->creator ? [
                        'id' => $roleRelationship->creator->id,
                        'name' => $roleRelationship->creator->name
                    ] : null
                ];
            })->values()->toArray();

        // Add student relationships with the real current active course information
        $transformedUser['studentRelationships'] = !$getDataForStudent ? null : $user->roleRelationships
            ->pluck('studentRelationship')
            ->filter()
            ->map(function ($relationship) use ($user) {
                $roleRelationship = $user->roleRelationships->firstWhere('id', $relationship->role_relationship_id);
                return [
                    'id' => $relationship->id,
                    'role_relationship_id' => $relationship->role_relationship_id,
                    'current_course' => $this->parseCurrentCourse($relationship->currentCourse),
                    'start_date' => $roleRelationship->start_date ? $roleRelationship->start_date->toDateString() : null,
                    'creator' => $roleRelationship->creator ? [
                        'id' => $roleRelationship->creator->id,
                        'name' => $roleRelationship->creator->name
                    ] : null
                ];
            })->values()->toArray();

        $transformedUser['current_course'] = !$getDataForStudent ? null : $this->getRealCurrentCourse($transformedUser['studentRelationships']);
        return $transformedUser;
    }

    private function getRealCurrentCourse($studentRelationships)
    {
        return $studentRelationships[0] ?? null;
    }

    public function hasAccessToSchool(int $schoolId): bool
    {
        /** @var \App\Models\Entities\User $user */
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Global admins have access to all schools
        if ($user->isSuperadmin()) {
            return true;
        }

        // Check if the user has any roles associated with the given school ID
        return $user->allRolesAcrossTeams()->wherePivot('team_id', $schoolId)->exists();
    }

    public static function genders()
    {
        return User::genders();
    }

    private function parseCurrentCourse($currentCourse)
    {
        if (!$currentCourse) return null;
        $data = $currentCourse->toArray();
        $data['url'] = route('school.course.show', [
            'school' => $currentCourse->school->slug,
            'schoolLevel' => $currentCourse->schoolLevel->code,
            'idAndLabel' => $currentCourse->idAndLabel
        ]);
        return $data;
    }

    public function getFiles(User $user, User $loggedUser)
    {
        return $this->fileService->getUserFiles($user, $loggedUser);
    }
}
