<?php

namespace App\Services;

use App\Models\Catalogs\Role;
use App\Models\Entities\User;
use App\Models\Relations\TeacherCourse;
use Illuminate\Http\Request;

class TeacherService
{
    public function __construct(
        protected UserService $userService,
        protected CourseService $courseService
    ) {}

    /**
     * Get the first active worker role_relationship id for a user in a school.
     */
    public function getRoleRelationshipIdForTeacherInSchool(int $userId, int $schoolId): ?int
    {
        $workerRoleIds = Role::whereIn('code', Role::workersCodes())->pluck('id')->toArray();
        if (empty($workerRoleIds)) {
            return null;
        }

        $rr = \App\Models\Relations\RoleRelationship::query()
            ->where('user_id', $userId)
            ->where('school_id', $schoolId)
            ->whereIn('role_id', $workerRoleIds)
            ->whereNull('end_date')
            ->whereNull('deleted_at')
            ->first();

        return $rr?->id;
    }

    /**
     * Search staff/teachers of the school for course assignment (exclude already actively assigned to this course).
     * Uses UserService::getStaffBySchool. Builds a GET-style request so search is in the query (reliable for addTextSearch).
     */
    public function searchTeachersForCourse(int $schoolId, int $courseId, ?string $search): array
    {
        $assignedUserIds = TeacherCourse::query()
            ->where('teacher_courses.course_id', $courseId)
            ->whereNull('teacher_courses.end_date')
            ->join('role_relationships', 'teacher_courses.role_relationship_id', '=', 'role_relationships.id')
            ->whereNull('role_relationships.deleted_at')
            ->pluck('role_relationships.user_id')
            ->unique()
            ->values()
            ->all();

        $request = Request::create('/', 'GET', [
            'search' => $search ?? '',
            'per_page' => 50,
        ]);
        $paginated = $this->userService->getStaffBySchool($request, $schoolId);
        $data = $paginated['data'] ?? [];

        $result = [];
        foreach ($data as $user) {
            if (in_array($user['id'], $assignedUserIds, true)) {
                continue;
            }
            $courses = $user['courses'] ?? [];
            $result[] = [
                'id' => $user['id'],
                'firstname' => $user['firstname'] ?? '',
                'lastname' => $user['lastname'] ?? '',
                'name' => $user['name'] ?? '',
                'email' => $user['email'] ?? null,
                'picture' => $user['picture'] ?? null,
                'courses' => $courses,
            ];
        }

        return $result;
    }

    /**
     * Assign a teacher to a course.
     *
     * @return array{message: string}|array{error: string}
     */
    public function assignTeacherToCourse(int $userId, int $schoolId, int $courseId, ?int $createdBy = null, bool $inCharge = false): array
    {
        $roleRelationshipId = $this->getRoleRelationshipIdForTeacherInSchool($userId, $schoolId);
        if (! $roleRelationshipId) {
            return ['error' => 'El usuario no es personal de esta escuela o la relación no está activa.'];
        }

        try {
            $this->courseService->assignCourseToTeacher($roleRelationshipId, $courseId, [
                'created_by' => $createdBy,
                'in_charge' => $inCharge,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $msg = $e->validator->errors()->first('assignment') ?? $e->getMessage();
            return ['error' => $msg];
        }

        return ['message' => 'Docente asignado al curso correctamente.'];
    }
}
