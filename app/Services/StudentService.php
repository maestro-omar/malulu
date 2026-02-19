<?php

namespace App\Services;

use App\Models\Entities\AcademicYear;
use App\Models\Entities\Course;
use App\Models\Entities\User;
use App\Models\Relations\StudentCourse;
use App\Models\Relations\StudentRelationship;
use App\Models\Catalogs\Role;
use App\Models\Catalogs\StudentCourseEndReason;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentService
{
    /**
     * Promote or graduate all students in a course who haven't been promoted yet.
     */
    public function promoteOrGraduateCourse(Course $course, ?int $createdBy = null): array
    {
        $studentCourses = $course->courseStudents()
            ->whereNull('end_date')
            ->whereNull('deleted_at')
            ->with(['roleRelationship.studentRelationship'])
            ->get();

        return $this->promoteOrGraduateStudents($studentCourses, $course, $createdBy);
    }

    /**
     * Promote or graduate a list of student enrollments.
     *
     * @param  iterable<StudentCourse>  $studentCourses  Active enrollments (end_date null)
     * @return array{promoted: int, graduated: int, skipped: int, errors: array}
     */
    public function promoteOrGraduateStudents(iterable $studentCourses, Course $fromCourse, ?int $createdBy = null): array
    {
        $result = ['promoted' => 0, 'graduated' => 0, 'stays' => 0, 'skipped' => 0, 'errors' => []];

        $isLastGrade = $this->isLastGradeForSchoolLevel($fromCourse);
        $nextCourse = $isLastGrade ? null : $fromCourse->nextCourses()->first();

        foreach ($studentCourses as $studentCourse) {
            if (! $studentCourse instanceof StudentCourse) {
                continue;
            }

            if ($studentCourse->end_date !== null) {
                $result['skipped']++;
                continue;
            }

            if ($studentCourse->course_id !== $fromCourse->id) {
                $result['skipped']++;
                continue;
            }

            $customFields = $studentCourse->custom_fields ?? [];
            $permaneceAgrupamiento = $customFields['permanece_2026'] ?? null;
            if ($permaneceAgrupamiento !== null && $permaneceAgrupamiento !== '') {
                try {
                    $targetCourse = $this->findCourseForPermanece($fromCourse, (string) $permaneceAgrupamiento, 2026);
                    if (! $targetCourse) {
                        $result['errors'][] = "Permanece target course not found for agrupamiento '{$permaneceAgrupamiento}' (from course {$fromCourse->nice_name}). Student enrollment ID: {$studentCourse->id}";
                        continue;
                    }
                    $this->endEnrollmentAndCreateNew(
                        $studentCourse,
                        $targetCourse,
                        StudentCourseEndReason::CODE_STAYS,
                        $createdBy
                    );
                    $result['stays']++;
                } catch (\Throwable $e) {
                    $result['errors'][] = "Student enrollment {$studentCourse->id} (permanece): " . $e->getMessage();
                }
                continue;
            }

            try {
                if ($isLastGrade) {
                    $this->endEnrollmentAndCreateNew(
                        $studentCourse,
                        null,
                        StudentCourseEndReason::CODE_GRADUATED,
                        $createdBy
                    );
                    $result['graduated']++;
                } else {
                    if (! $nextCourse) {
                        $result['errors'][] = "No next course found for {$fromCourse->nice_name}. Student enrollment ID: {$studentCourse->id}";
                        continue;
                    }
                    $this->endEnrollmentAndCreateNew(
                        $studentCourse,
                        $nextCourse,
                        StudentCourseEndReason::CODE_PROMOTED,
                        $createdBy
                    );
                    $result['promoted']++;
                }
            } catch (\Throwable $e) {
                $result['errors'][] = "Student enrollment {$studentCourse->id}: " . $e->getMessage();
            }
        }

        return $result;
    }

    /**
     * Find the course for a "permanece" (stays) target: same school, level, shift as $fromCourse,
     * with number/letter from $agrupamiento (e.g. "1A") and start_date on the first day of $targetYear academic year.
     */
    public function findCourseForPermanece(Course $fromCourse, string $agrupamiento, int $targetYear): ?Course
    {
        if (! preg_match('/^(\d+)([A-Za-z])$/', trim($agrupamiento), $m)) {
            return null;
        }
        $number = (int) $m[1];
        $letter = strtoupper($m[2]);

        $targetAy = AcademicYear::findByYear($targetYear);
        if (! $targetAy) {
            return null;
        }
        $firstDay = $targetAy->start_date->format('Y-m-d');

        return Course::where('school_id', $fromCourse->school_id)
            ->where('school_level_id', $fromCourse->school_level_id)
            ->where('school_shift_id', $fromCourse->school_shift_id)
            ->where('number', $number)
            ->where('letter', $letter)
            ->where('start_date', $firstDay)
            ->first();
    }

    /**
     * End the current enrollment and optionally create a new one (promotion).
     * For graduation, $newCourse is null.
     */
    public function endEnrollmentAndCreateNew(
        StudentCourse $studentCourse,
        ?Course $newCourse,
        string $endReasonCode,
        ?int $createdBy = null,
        ?string $enrollmentReason = null
    ): void {
        $endReason = StudentCourseEndReason::where('code', $endReasonCode)->first();
        if (! $endReason) {
            throw ValidationException::withMessages([
                'end_reason' => ["End reason '{$endReasonCode}' not found."],
            ]);
        }

        $currentCourse = $studentCourse->course;
        $endDate = $currentCourse->end_date
            ? Carbon::parse($currentCourse->end_date)->toDateString()
            : now()->toDateString();

        DB::transaction(function () use ($studentCourse, $newCourse, $endReason, $endDate, $createdBy, $enrollmentReason) {
            $studentCourse->update([
                'end_date' => $endDate,
                'end_reason_id' => $endReason->id,
                'updated_by' => $createdBy,
            ]);

            if ($newCourse) {
                $newStartDate = Carbon::parse($endDate)->addDay()->toDateString();
                StudentCourse::create([
                    'role_relationship_id' => $studentCourse->role_relationship_id,
                    'course_id' => $newCourse->id,
                    'start_date' => $newStartDate,
                    'end_date' => null,
                    'end_reason_id' => null,
                    'enrollment_reason' => $enrollmentReason,
                    'created_by' => $createdBy,
                ]);

                $this->updateStudentCurrentCourse($studentCourse->role_relationship_id, $newCourse->id);
            } else {
                $this->updateStudentCurrentCourse($studentCourse->role_relationship_id, null);
            }
        });
    }

    /**
     * Transfer a student from one course to another (e.g. 2A to 2B).
     */
    public function transferStudentToCourse(StudentCourse $studentCourse, Course $targetCourse, ?int $createdBy = null, ?string $enrollmentReason = null): void
    {
        $currentCourse = $studentCourse->course;

        if ($studentCourse->course_id === $targetCourse->id) {
            throw ValidationException::withMessages([
                'course' => ['El estudiante ya está en ese curso.'],
            ]);
        }

        $endReasonCode = $currentCourse->school_shift_id === $targetCourse->school_shift_id
            ? StudentCourseEndReason::CODE_TRANSFER_SAME_SHIFT
            : StudentCourseEndReason::CODE_TRANSFER_OTHER_SHIFT;

        $endReason = StudentCourseEndReason::where('code', $endReasonCode)->first();
        if (! $endReason) {
            throw ValidationException::withMessages([
                'end_reason' => ["End reason '{$endReasonCode}' not found."],
            ]);
        }

        $endDate = now()->toDateString();
        $newStartDate = $endDate;

        DB::transaction(function () use ($studentCourse, $targetCourse, $endReason, $endDate, $newStartDate, $createdBy, $enrollmentReason) {
            $studentCourse->update([
                'end_date' => $endDate,
                'end_reason_id' => $endReason->id,
                'updated_by' => $createdBy,
            ]);

            StudentCourse::create([
                'role_relationship_id' => $studentCourse->role_relationship_id,
                'course_id' => $targetCourse->id,
                'start_date' => $newStartDate,
                'end_date' => null,
                'end_reason_id' => null,
                'enrollment_reason' => $enrollmentReason,
                'created_by' => $createdBy,
            ]);

            $this->updateStudentCurrentCourse($studentCourse->role_relationship_id, $targetCourse->id);
        });
    }

    protected function updateStudentCurrentCourse(int $roleRelationshipId, ?int $courseId): void
    {
        StudentRelationship::where('role_relationship_id', $roleRelationshipId)
            ->update(['current_course_id' => $courseId]);
    }

    /**
     * Check if the course is the last grade for this school+level.
     * Uses school_level pivot 'grades' when not null; otherwise falls back to max course number.
     */
    public function isLastGradeForSchoolLevel(Course $course): bool
    {
        $pivot = DB::table('school_level')
            ->where('school_id', $course->school_id)
            ->where('school_level_id', $course->school_level_id)
            ->first();

        $lastGrade = $pivot?->grades;

        if ($lastGrade !== null) {
            return (int) $course->number >= (int) $lastGrade;
        }

        $maxNumber = Course::where('school_id', $course->school_id)
            ->where('school_level_id', $course->school_level_id)
            ->max('number');

        return $maxNumber !== null && (int) $course->number >= (int) $maxNumber;
    }

    /**
     * Get the role_relationship id for a user as student in a school (active relationship).
     */
    public function getRoleRelationshipIdForStudentInSchool(int $userId, int $schoolId): ?int
    {
        $roleId = Role::where('code', Role::STUDENT)->first()?->id;
        if (! $roleId) {
            return null;
        }

        $rr = \App\Models\Relations\RoleRelationship::query()
            ->where('user_id', $userId)
            ->where('school_id', $schoolId)
            ->where('role_id', $roleId)
            ->whereNull('end_date')
            ->whereNull('deleted_at')
            ->first();

        return $rr?->id;
    }

    /**
     * Search students of the school for enrollment (exclude already in this course).
     * Returns array of: id, firstname, lastname, birthdate, age, current_course (null or { id, nice_name }).
     */
    public function searchStudentsForEnrollment(int $schoolId, int $courseId, ?string $search = null): array
    {
        $studentRoleId = Role::where('code', Role::STUDENT)->firstOrFail()->id;
        $query = User::query()->withActiveRoleRelationship($studentRoleId, $schoolId);

        if ($search !== null && trim($search) !== '') {
            $term = '%' . trim($search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('firstname', 'like', $term)
                    ->orWhere('lastname', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('id_number', 'like', $term);
            });
        }

        $users = $query->with([
            'studentRelationships' => fn ($q) => $q->whereNull('deleted_at'),
            'studentRelationships.roleRelationship',
            'studentRelationships.currentCourse.schoolLevel',
            'studentRelationships.currentCourse.schoolShift',
        ])->orderBy('lastname')->orderBy('firstname')->limit(50)->get();

        $result = [];
        foreach ($users as $user) {
            $studentRel = $user->studentRelationships->first(
                fn ($sr) => $sr->roleRelationship && (int) $sr->roleRelationship->school_id === $schoolId
            );
            $currentCourse = $studentRel?->currentCourse;
            if ($currentCourse && (int) $currentCourse->id === $courseId) {
                continue; // already in this course, exclude from list
            }

            $result[] = [
                'id' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'birthdate' => $user->birthdate?->format('Y-m-d'),
                'age' => $user->birthdate?->diffInYears(now()),
                'current_course' => $currentCourse ? [
                    'id' => $currentCourse->id,
                    'nice_name' => $currentCourse->nice_name,
                ] : null,
            ];
        }

        return $result;
    }

    /**
     * Enroll a student in a course. Handles: already in course, in other course (with end reason), or new enrollment.
     *
     * @return array{message: string}|array{error: string}
     */
    public function enrollStudentInCourse(
        int $userId,
        int $schoolId,
        int $courseId,
        ?int $createdBy = null,
        ?int $endReasonId = null,
        ?string $enrollmentReason = null
    ): array {
        $roleRelationshipId = $this->getRoleRelationshipIdForStudentInSchool($userId, $schoolId);
        if (! $roleRelationshipId) {
            return ['error' => 'El usuario no es estudiante de esta escuela o la relación no está activa.'];
        }

        $targetCourse = Course::find($courseId);
        if (! $targetCourse) {
            return ['error' => 'Curso no encontrado.'];
        }

        $activeEnrollment = StudentCourse::where('role_relationship_id', $roleRelationshipId)
            ->whereNull('end_date')
            ->whereNull('deleted_at')
            ->with('course')
            ->first();

        if ($activeEnrollment) {
            if ((int) $activeEnrollment->course_id === $courseId) {
                return ['error' => 'El estudiante ya está inscripto en este curso.'];
            }

            if (! $endReasonId) {
                return ['error' => 'El estudiante tiene otro curso asignado. Debe indicar el motivo de finalización del curso anterior.'];
            }

            $endReason = StudentCourseEndReason::find($endReasonId);
            if (! $endReason || ! $endReason->is_active) {
                return ['error' => 'Motivo de finalización no válido.'];
            }

            $this->endEnrollmentAndCreateNew(
                $activeEnrollment,
                $targetCourse,
                $endReason->code,
                $createdBy,
                $enrollmentReason
            );

            return ['message' => 'Estudiante inscripto correctamente (curso anterior finalizado).'];
        }

        // No current course: create new enrollment via CourseService
        $courseService = app(CourseService::class);
        $courseService->enrollStudentToCourse($roleRelationshipId, $courseId, [
            'created_by' => $createdBy,
            'enrollment_reason' => $enrollmentReason,
        ]);

        StudentRelationship::updateOrCreate(
            ['role_relationship_id' => $roleRelationshipId],
            ['current_course_id' => $courseId]
        );

        return ['message' => 'Estudiante inscripto correctamente.'];
    }
}
