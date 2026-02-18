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
     * Search staff/teachers of the school for course assignment.
     * Includes everyone: already assigned get already_assigned: true and rel_id (TeacherCourse id) so frontend can show "Quitar".
     */
    public function searchTeachersForCourse(int $schoolId, int $courseId, ?string $search): array
    {
        $assignedMap = TeacherCourse::query()
            ->where('teacher_courses.course_id', $courseId)
            ->whereNull('teacher_courses.end_date')
            ->join('role_relationships', 'teacher_courses.role_relationship_id', '=', 'role_relationships.id')
            ->whereNull('role_relationships.deleted_at')
            ->select('teacher_courses.id as rel_id', 'role_relationships.user_id')
            ->get()
            ->keyBy('user_id');

        $request = Request::create('/', 'GET', [
            'search' => $search ?? '',
            'per_page' => 50,
        ]);
        $paginated = $this->userService->getStaffBySchool($request, $schoolId);
        $data = $paginated['data'] ?? [];

        $result = [];
        foreach ($data as $user) {
            $assignment = $assignedMap->get($user['id']);
            $courses = $user['courses'] ?? [];
            $role = isset($user['roles'][0]) ? $user['roles'][0] : null;
            $subject = $this->extractSubjectFromStaffUser($user);
            $shifts = $this->extractShiftsFromStaffUser($user);
            $result[] = [
                'id' => $user['id'],
                'firstname' => $user['firstname'] ?? '',
                'lastname' => $user['lastname'] ?? '',
                'name' => $user['name'] ?? '',
                'email' => $user['email'] ?? null,
                'picture' => $user['picture'] ?? null,
                'courses' => $courses,
                'role' => $role,
                'subject' => $subject,
                'shifts' => $shifts,
                'already_assigned' => $assignment !== null,
                'rel_id' => $assignment?->rel_id,
            ];
        }

        return $result;
    }

    private function extractSubjectFromStaffUser(array $user): ?string
    {
        $wr = $user['workerRelationships'] ?? null;
        if ($wr instanceof \Illuminate\Support\Collection && $wr->isNotEmpty()) {
            $first = $wr->first();
            return $first->classSubject->name ?? null;
        }
        if (is_array($wr) && ! empty($wr)) {
            $first = $wr[0];
            return $first['class_subject']['name'] ?? $first['classSubject']['name'] ?? null;
        }
        return null;
    }

    /**
     * @return array<int, array{id: int, name: string, code: string}>
     */
    private function extractShiftsFromStaffUser(array $user): array
    {
        $courses = $user['courses'] ?? [];
        $shiftById = [];
        foreach (collect($courses)->pluck('school_shift')->filter() as $s) {
            $id = \is_array($s) ? ($s['id'] ?? null) : $s->id;
            $name = \is_array($s) ? ($s['name'] ?? null) : $s->name;
            $code = \is_array($s) ? ($s['code'] ?? null) : $s->code;
            if ($id !== null) {
                $shiftById[$id] = ['id' => $id, 'name' => $name ?? '', 'code' => $code ?? ''];
            }
        }
        return array_values($shiftById);
    }

    /**
     * End a teacher's assignment to a course (set end_date on TeacherCourse).
     *
     * @return array{message: string}|array{error: string}
     */
    public function unassignTeacherFromCourse(int $teacherCourseId, int $courseId): array
    {
        $teacherCourse = TeacherCourse::where('id', $teacherCourseId)
            ->where('course_id', $courseId)
            ->whereNull('end_date')
            ->first();

        if (! $teacherCourse) {
            return ['error' => 'Asignación no encontrada o ya finalizada.'];
        }

        $teacherCourse->end_date = now();
        $teacherCourse->save();

        return ['message' => 'Docente quitado del curso correctamente.'];
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
