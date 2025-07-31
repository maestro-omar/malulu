<?php

namespace App\Services;

use App\Models\Entities\School;
use App\Models\Entities\User;
use App\Models\Catalogs\Role;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\Auth;
use App\Services\CourseService;
use App\Traits\UserServiceCrud;
use App\Traits\UserServiceList;

class UserService
{
    use UserServiceCrud, UserServiceList;
    /**
     * Get user data for show view with all relationships
     */
    public function getUserShowData(User $user): array
    {
        $user->load([
            'province',
            'country',
            'allRolesAcrossTeams',
            'roleRelationships' => function ($query) {
                $query->with([
                    'workerRelationship' => function ($query) {
                        $query->with(['classSubject']);
                    },
                    'guardianRelationship' => function ($query) {
                        $query->with(['student' => function ($query) {
                            $query->with(['roleRelationships' => function ($query) {
                                $query->with(['studentRelationship' => function ($query) {
                                    $query->with(['currentCourse']);
                                }]);
                            }]);
                        }]);
                    },
                    'studentRelationships' => function ($query) {
                        $query->with(['currentCourse']);
                    },
                    'creator'
                ]);
            }
        ]);

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
        $allRolesAcrossTeams = collect($user->allRolesAcrossTeams);

        // Get unique school IDs from roles
        $schoolIds = $allRolesAcrossTeams
            ->pluck('pivot.team_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Get schools for these IDs
        $schools = School::whereIn('id', $schoolIds)->get();

        $transformedUser['roles'] = $allRolesAcrossTeams->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'short' => $role->short,
                'code' => $role->code,
                'team_id' => $role->pivot->team_id
            ];
        })->values()->toArray();

        // Add schools to user data
        $transformedUser['schools'] = $schools->map(function ($school) {
            return [
                'id' => $school->id,
                'name' => $school->name,
                'short' => $school->short,
                'slug' => $school->slug,
            ];
        })->values()->toArray();

        // Add role relationships data
        $transformedUser['roleRelationships'] = $user->roleRelationships->map(function ($relationship) {
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
        $transformedUser['workerRelationships'] = $user->roleRelationships
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
        $transformedUser['guardianRelationships'] = $user->roleRelationships
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
                        'current_course' => $currentCourse ? [
                            'id' => $currentCourse->id,
                            'name' => $currentCourse->name,
                            'grade' => $currentCourse->grade,
                            'section' => $currentCourse->section
                        ] : null
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

        // Add student relationships with course information
        $transformedUser['studentRelationships'] = $user->roleRelationships
            ->pluck('studentRelationship')
            ->filter()
            ->map(function ($relationship) use ($user) {
                $roleRelationship = $user->roleRelationships->firstWhere('id', $relationship->role_relationship_id);
                return [
                    'id' => $relationship->id,
                    'role_relationship_id' => $relationship->role_relationship_id,
                    'current_course' => $relationship->currentCourse ? [
                        'id' => $relationship->currentCourse->id,
                        'name' => $relationship->currentCourse->name,
                        'grade' => $relationship->currentCourse->grade,
                        'section' => $relationship->currentCourse->section
                    ] : null,
                    'start_date' => $roleRelationship->start_date ? $roleRelationship->start_date->toDateString() : null,
                    'creator' => $roleRelationship->creator ? [
                        'id' => $roleRelationship->creator->id,
                        'name' => $roleRelationship->creator->name
                    ] : null
                ];
            })->values()->toArray();

        return $transformedUser;
    }

    public function hasAccessToSchool(int $schoolId): bool
    {
        /** @var \App\Models\Entities\User $user */
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Global admins have access to all schools
        if ($user->hasRole('global admin')) {
            return true;
        }

        // Check if the user has any roles associated with the given school ID
        return $user->allRolesAcrossTeams()->wherePivot('team_id', $schoolId)->exists();
    }

    public static function genders()
    {
        return User::genders();
    }
}
