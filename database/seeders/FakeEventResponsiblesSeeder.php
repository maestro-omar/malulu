<?php

namespace Database\Seeders;

use App\Models\Catalogs\EventType;
use App\Models\Catalogs\Role;
use App\Models\Entities\AcademicEvent;
use App\Models\Entities\AcademicYear;
use App\Models\Relations\AcademicEventResponsible;
use App\Models\Relations\RoleRelationship;
use App\Models\Catalogs\EventResponsibilityType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Assigns each worker (active RoleRelationship with worker role) to at least 2
 * academic events for the 2026 academic year, with different responsibility types.
 * Events must belong to the same school as the worker's role_relationship.
 * If a school has no 2026 events, creates two minimal school events for that school.
 */
class FakeEventResponsiblesSeeder extends Seeder
{
    private int $firstUserId;

    public function run(): void
    {
        $this->firstUserId = (int) (DB::table('users')->value('id') ?? 1);

        $academicYear = AcademicYear::findByYear(2026);
        if (!$academicYear) {
            $this->command->warn('Academic year 2026 not found. Run AcademicYearSeeder first.');
            return;
        }

        $types = EventResponsibilityType::orderBy('order')->get();
        if ($types->count() < 2) {
            $this->command->warn('Need at least 2 event responsibility types. Run EventResponsibilityTypeSeeder first.');
            return;
        }

        $workerRoleIds = Role::whereIn('code', Role::workersCodes())->pluck('id')->toArray();
        $workers = RoleRelationship::query()
            ->whereIn('role_id', $workerRoleIds)
            ->active()
            ->get();

        if ($workers->isEmpty()) {
            $this->command->warn('No active workers found.');
            return;
        }

        $schoolIds = $workers->pluck('school_id')->filter()->unique()->values()->toArray();
        $this->ensureSchoolEventsFor2026($academicYear->id, $schoolIds);

        $eventsBySchool = AcademicEvent::query()
            ->where('academic_year_id', $academicYear->id)
            ->whereNotNull('school_id')
            ->get()
            ->groupBy('school_id');

        $created = 0;
        foreach ($workers as $rr) {
            $schoolId = $rr->school_id;
            if (!$schoolId) {
                continue;
            }
            $schoolEvents = $eventsBySchool->get($schoolId, collect());
            if ($schoolEvents->isEmpty()) {
                continue;
            }

            $typeIds = $types->pluck('id')->toArray();
            $eventIds = $schoolEvents->pluck('id')->toArray();

            $assignments = $this->pickAtLeastTwoAssignments($eventIds, $typeIds);
            foreach ($assignments as [$eventId, $typeId]) {
                $exists = AcademicEventResponsible::where('academic_event_id', $eventId)
                    ->where('role_relationship_id', $rr->id)
                    ->where('event_responsibility_type_id', $typeId)
                    ->exists();
                if (!$exists) {
                    AcademicEventResponsible::create([
                        'academic_event_id' => $eventId,
                        'role_relationship_id' => $rr->id,
                        'event_responsibility_type_id' => $typeId,
                        'notes' => null,
                    ]);
                    $created++;
                }
            }
        }

        $this->command->info("FakeEventResponsiblesSeeder: created {$created} event responsible assignments for 2026.");
    }

    /**
     * Ensure each school has at least 2 academic events for 2026. Creates minimal events if missing.
     */
    private function ensureSchoolEventsFor2026(int $academicYearId, array $schoolIds): void
    {
        $eventType = EventType::where('code', EventType::CODE_ACADEMICO_ESCOLAR)->first();
        if (!$eventType) {
            $eventType = EventType::orderBy('id')->first();
        }
        if (!$eventType) {
            return;
        }

        foreach ($schoolIds as $schoolId) {
            $count = AcademicEvent::where('academic_year_id', $academicYearId)
                ->where('school_id', $schoolId)
                ->count();
            if ($count >= 2) {
                continue;
            }
            $toCreate = 2 - $count;
            $dates = ['2026-05-25', '2026-07-09', '2026-08-17'];
            $titles = ['Acto escolar (faker)', 'Reunión institucional (faker)', 'Jornada de reflexión (faker)'];
            for ($i = 0; $i < $toCreate; $i++) {
                AcademicEvent::create([
                    'title' => $titles[$i] ?? 'Evento escolar 2026 (faker)',
                    'date' => $dates[$i] ?? '2026-06-15',
                    'is_non_working_day' => false,
                    'notes' => 'Creado por FakeEventResponsiblesSeeder',
                    'school_id' => $schoolId,
                    'academic_year_id' => $academicYearId,
                    'event_type_id' => $eventType->id,
                    'active' => true,
                    'created_by' => $this->firstUserId,
                ]);
            }
        }
    }

    /**
     * Return at least 2 pairs of [event_id, type_id] with different types.
     * Uses different events when possible, or same event with 2 types.
     */
    private function pickAtLeastTwoAssignments(array $eventIds, array $typeIds): array
    {
        $pairs = [];
        if (count($eventIds) >= 2 && count($typeIds) >= 2) {
            $pairs[] = [$eventIds[0], $typeIds[0]];
            $pairs[] = [$eventIds[1], $typeIds[1]];
        } elseif (count($eventIds) >= 1 && count($typeIds) >= 2) {
            $pairs[] = [$eventIds[0], $typeIds[0]];
            $pairs[] = [$eventIds[0], $typeIds[1]];
        } elseif (count($eventIds) >= 1 && count($typeIds) >= 1) {
            $pairs[] = [$eventIds[0], $typeIds[0]];
        }
        return $pairs;
    }
}
