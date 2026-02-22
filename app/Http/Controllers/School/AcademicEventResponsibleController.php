<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\School\SchoolBaseController;
use App\Models\Entities\AcademicEvent;
use App\Models\Entities\School;
use App\Models\Relations\AcademicEventResponsible;
use App\Models\Relations\RoleRelationship;
use App\Models\Catalogs\Role;
use App\Models\Catalogs\EventResponsibilityType;
use App\Services\AcademicEventResponsibleService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AcademicEventResponsibleController extends SchoolBaseController
{
    public function __construct(private AcademicEventResponsibleService $responsibleService)
    {
    }

    private function ensureEventBelongsToSchool(AcademicEvent $academicEvent, School $school): void
    {
        if ($academicEvent->school_id !== $school->id) {
            abort(403, 'Este evento no pertenece a esta escuela.');
        }
    }

    public function index(School $school, AcademicEvent $academicEvent)
    {
        $this->ensureEventBelongsToSchool($academicEvent, $school);
        $responsibles = $this->responsibleService->listForEvent($academicEvent);
        $data = $responsibles->map(fn ($r) => [
            'id' => $r->id,
            'role_relationship_id' => $r->role_relationship_id,
            'event_responsibility_type_id' => $r->event_responsibility_type_id,
            'notes' => $r->notes,
            'user' => $r->roleRelationship && $r->roleRelationship->user ? [
                'id' => $r->roleRelationship->user->id,
                'short_name' => $r->roleRelationship->user->short_name,
                'name' => $r->roleRelationship->user->name,
            ] : null,
            'responsibility_type' => $r->responsibilityType ? [
                'id' => $r->responsibilityType->id,
                'code' => $r->responsibilityType->code,
                'name' => $r->responsibilityType->name,
            ] : null,
        ]);
        return response()->json($data->toArray());
    }

    public function store(Request $request, School $school, AcademicEvent $academicEvent)
    {
        $this->ensureEventBelongsToSchool($academicEvent, $school);
        try {
            $data = $request->validate([
                'role_relationship_id' => ['required', 'integer'],
                'event_responsibility_type_id' => ['required', 'integer'],
                'notes' => ['nullable', 'string', 'max:1000'],
            ]);
            $responsible = $this->responsibleService->store($academicEvent, $data);
            $responsible->load(['roleRelationship.user', 'responsibilityType']);
            return response()->json([
                'id' => $responsible->id,
                'role_relationship_id' => $responsible->role_relationship_id,
                'event_responsibility_type_id' => $responsible->event_responsibility_type_id,
                'notes' => $responsible->notes,
                'user' => $responsible->roleRelationship && $responsible->roleRelationship->user ? [
                    'id' => $responsible->roleRelationship->user->id,
                    'short_name' => $responsible->roleRelationship->user->short_name,
                    'name' => $responsible->roleRelationship->user->name,
                ] : null,
                'responsibility_type' => $responsible->responsibilityType ? [
                    'id' => $responsible->responsibilityType->id,
                    'code' => $responsible->responsibilityType->code,
                    'name' => $responsible->responsibilityType->name,
                ] : null,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function sync(Request $request, School $school, AcademicEvent $academicEvent)
    {
        $this->ensureEventBelongsToSchool($academicEvent, $school);
        try {
            $request->validate(['items' => ['required', 'array']]);
            $this->responsibleService->syncForEvent($academicEvent, $request->input('items', []));
            $responsibles = $this->responsibleService->listForEvent($academicEvent);
            $data = $responsibles->map(fn ($r) => [
                'id' => $r->id,
                'role_relationship_id' => $r->role_relationship_id,
                'event_responsibility_type_id' => $r->event_responsibility_type_id,
                'notes' => $r->notes,
                'user' => $r->roleRelationship && $r->roleRelationship->user ? [
                    'id' => $r->roleRelationship->user->id,
                    'short_name' => $r->roleRelationship->user->short_name,
                    'name' => $r->roleRelationship->user->name,
                ] : null,
                'responsibility_type' => $r->responsibilityType ? [
                    'id' => $r->responsibilityType->id,
                    'code' => $r->responsibilityType->code,
                    'name' => $r->responsibilityType->name,
                ] : null,
            ]);
            return response()->json($data->toArray());
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function destroy(School $school, AcademicEvent $academicEvent, AcademicEventResponsible $responsible)
    {
        $this->ensureEventBelongsToSchool($academicEvent, $school);
        if ($responsible->academic_event_id !== $academicEvent->id) {
            abort(404);
        }
        $this->responsibleService->destroy($responsible);
        return response()->json(null, 204);
    }

    /**
     * Return workers (role relationships) for the event's school for dropdowns.
     */
    public function workers(School $school, AcademicEvent $academicEvent): array
    {
        $this->ensureEventBelongsToSchool($academicEvent, $school);
        $workerRoleIds = Role::whereIn('code', Role::workersCodes())->pluck('id')->toArray();
        $list = RoleRelationship::query()
            ->with('user')
            ->forSchool($school->id)
            ->forRoles($workerRoleIds)
            ->active()
            ->get()
            ->map(fn (RoleRelationship $rr) => [
                'id' => $rr->id,
                'user_id' => $rr->user_id,
                'short_name' => $rr->user ? $rr->user->short_name : '',
                'name' => $rr->user ? $rr->user->name : '',
            ])
            ->values();
        return $list->toArray();
    }

    /**
     * Return responsibility types for dropdowns.
     */
    public function types(): array
    {
        return EventResponsibilityType::forDropdown();
    }
}
