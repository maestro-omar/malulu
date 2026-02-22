<?php

namespace App\Services;

use App\Models\Entities\AcademicEvent;
use App\Models\Entities\AcademicYear;
use App\Models\Entities\User;
use App\Models\Relations\AcademicEventResponsible;
use App\Models\Relations\RoleRelationship;
use App\Models\Catalogs\Role;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class AcademicEventResponsibleService
{
    public function listForEvent(AcademicEvent $event): Collection
    {
        return AcademicEventResponsible::query()
            ->where('academic_event_id', $event->id)
            ->with(['roleRelationship.user', 'responsibilityType'])
            ->orderBy('id')
            ->get();
    }

    public function syncForEvent(AcademicEvent $event, array $items): void
    {
        $validated = $this->validateSyncData($items, $event);

        $existingIds = [];
        foreach ($validated as $item) {
            $responsible = AcademicEventResponsible::updateOrCreate(
                [
                    'academic_event_id' => $event->id,
                    'role_relationship_id' => $item['role_relationship_id'],
                    'event_responsibility_type_id' => $item['event_responsibility_type_id'],
                ],
                ['notes' => $item['notes'] ?? null]
            );
            $existingIds[] = $responsible->id;
        }

        AcademicEventResponsible::where('academic_event_id', $event->id)
            ->whereNotIn('id', $existingIds)
            ->delete();
    }

    public function store(AcademicEvent $event, array $data): AcademicEventResponsible
    {
        $validated = $this->validateSingle($data, $event);
        return AcademicEventResponsible::create([
            'academic_event_id' => $event->id,
            'role_relationship_id' => $validated['role_relationship_id'],
            'event_responsibility_type_id' => $validated['event_responsibility_type_id'],
            'notes' => $validated['notes'] ?? null,
        ]);
    }

    public function destroy(AcademicEventResponsible $responsible): bool
    {
        return $responsible->delete();
    }

    public function validateSyncData(array $items, AcademicEvent $event): array
    {
        $schoolId = $event->school_id;
        $workerRoleIds = Role::whereIn('code', Role::workersCodes())->pluck('id')->toArray();

        $rules = [
            'items' => ['required', 'array'],
            'items.*.role_relationship_id' => [
                'required',
                'exists:role_relationships,id',
                function ($attribute, $value, $fail) use ($schoolId, $workerRoleIds) {
                    $rr = RoleRelationship::find($value);
                    if (!$rr || $rr->school_id != $schoolId) {
                        $fail(__('El responsable debe pertenecer a la misma escuela del evento.'));
                    }
                    if (!in_array($rr->role_id, $workerRoleIds)) {
                        $fail(__('El responsable debe tener un rol de trabajador.'));
                    }
                },
            ],
            'items.*.event_responsibility_type_id' => ['required', 'exists:event_responsibility_types,id'],
            'items.*.notes' => ['nullable', 'string', 'max:1000'],
        ];

        $validator = Validator::make(['items' => $items], $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        return $validator->validated()['items'];
    }

    public function validateSingle(array $data, AcademicEvent $event): array
    {
        $schoolId = $event->school_id;
        $workerRoleIds = Role::whereIn('code', Role::workersCodes())->pluck('id')->toArray();

        $rules = [
            'role_relationship_id' => [
                'required',
                'exists:role_relationships,id',
                function ($attribute, $value, $fail) use ($schoolId, $workerRoleIds) {
                    $rr = RoleRelationship::find($value);
                    if (!$rr || $rr->school_id != $schoolId) {
                        $fail(__('El responsable debe pertenecer a la misma escuela del evento.'));
                    }
                    if (!in_array($rr->role_id, $workerRoleIds)) {
                        $fail(__('El responsable debe tener un rol de trabajador.'));
                    }
                },
            ],
            'event_responsibility_type_id' => ['required', 'exists:event_responsibility_types,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        return $validator->validated();
    }

    public function getEventIdsWhereUserIsResponsible(User $user): array
    {
        $roleRelationshipIds = RoleRelationship::where('user_id', $user->id)->active()->pluck('id');
        return AcademicEventResponsible::whereIn('role_relationship_id', $roleRelationshipIds)
            ->pluck('academic_event_id')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get all event responsibilities assigned to the user for the current academic year,
     * ordered by event date. Returns items with event info and is_past flag for grouping.
     */
    public function getMyEventResponsibilitiesForCurrentYear(User $user): array
    {
        $academicYear = AcademicYear::getCurrent();
        if (!$academicYear) {
            return [];
        }

        $roleRelationshipIds = RoleRelationship::where('user_id', $user->id)->active()->pluck('id');
        if ($roleRelationshipIds->isEmpty()) {
            return [];
        }

        $today = now()->startOfDay();
        $rows = AcademicEventResponsible::query()
            ->whereIn('role_relationship_id', $roleRelationshipIds)
            ->with(['academicEvent.school', 'responsibilityType'])
            ->get()
            ->filter(function (AcademicEventResponsible $r) use ($academicYear) {
                return $r->academicEvent && (int) $r->academicEvent->academic_year_id === (int) $academicYear->id;
            })
            ->sortBy(function (AcademicEventResponsible $r) {
                $date = $r->academicEvent->date;
                if (!$date) {
                    return '';
                }
                return $date instanceof \DateTimeInterface
                    ? $date->format('Y-m-d')
                    : \Carbon\Carbon::parse($date)->format('Y-m-d');
            })
            ->values();

        $result = [];
        foreach ($rows as $r) {
            $event = $r->academicEvent;
            $eventDate = $event->date ? \Carbon\Carbon::parse($event->date) : null;
            $result[] = [
                'id' => $r->id,
                'academic_event_id' => $event->id,
                'event_title' => $event->title,
                'event_date' => $eventDate ? $eventDate->format('Y-m-d') : null,
                'event_date_formatted' => $eventDate ? $eventDate->locale('es')->isoFormat('D MMM YYYY') : null,
                'school_id' => $event->school_id,
                'school_slug' => $event->school ? $event->school->slug : null,
                'school_name' => $event->school ? $event->school->short : null,
                'responsibility_type' => $r->responsibilityType ? [
                    'id' => $r->responsibilityType->id,
                    'code' => $r->responsibilityType->code,
                    'name' => $r->responsibilityType->name,
                ] : null,
                'notes' => $r->notes,
                'is_past' => $eventDate ? $eventDate->lt($today) : false,
            ];
        }
        return $result;
    }
}
