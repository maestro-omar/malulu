<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\System\SystemBaseController;
use App\Models\Entities\RecurrentEvent;
use App\Models\Catalogs\EventType;
use App\Models\Catalogs\Province;
use App\Models\Entities\School;
use App\Models\Entities\AcademicYear;
use App\Services\RecurrentEventService;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class RecurrentEventAdminController extends SystemBaseController
{
    public function __construct(private RecurrentEventService $recurrentEventService)
    {
        $this->middleware('permission:recurrent-event.manage');
    }

    public function index()
    {
        return Inertia::render('RecurrentEvents/Index', [
            'recurrentEvents' => $this->recurrentEventService->getRecurrentEvents(),
            'breadcrumbs' => Breadcrumbs::generate('recurrent-events.index'),
        ]);
    }

    public function create()
    {
        return Inertia::render('RecurrentEvents/Create', $this->formOptions([
            'breadcrumbs' => Breadcrumbs::generate('recurrent-events.create'),
        ]));
    }

    public function store(Request $request)
    {
        try {
            $this->recurrentEventService->createRecurrentEvent($request->all());
            return redirect()
                ->route('recurrent-events.index')
                ->with('success', 'Evento recurrente creado exitosamente.');
        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function show(RecurrentEvent $recurrentEvent)
    {
        $recurrentEvent->load(['type', 'province', 'school', 'academicYear']);

        return Inertia::render('RecurrentEvents/Show', [
            'recurrentEvent' => $recurrentEvent,
            'breadcrumbs' => Breadcrumbs::generate('recurrent-events.show', $recurrentEvent),
        ]);
    }

    public function edit(RecurrentEvent $recurrentEvent)
    {
        $recurrentEvent->load(['type', 'province', 'school', 'academicYear']);

        return Inertia::render('RecurrentEvents/Edit', $this->formOptions([
            'recurrentEvent' => $recurrentEvent,
            'breadcrumbs' => Breadcrumbs::generate('recurrent-events.edit', $recurrentEvent),
        ]));
    }

    public function update(Request $request, RecurrentEvent $recurrentEvent)
    {
        try {
            $this->recurrentEventService->updateRecurrentEvent($recurrentEvent, $request->all());
            return redirect()
                ->route('recurrent-events.index')
                ->with('success', 'Evento recurrente actualizado exitosamente.');
        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(RecurrentEvent $recurrentEvent)
    {
        try {
            $this->recurrentEventService->deleteRecurrentEvent($recurrentEvent);
            return redirect()
                ->route('recurrent-events.index')
                ->with('success', 'Evento recurrente eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Share options used by Create/Edit forms.
     */
    protected function formOptions(array $extra = []): array
    {
        $eventTypes = EventType::orderBy('name')->get()->map(function (EventType $type) {
            return [
                'id' => $type->id,
                'name' => $type->name,
                'code' => $type->code,
            ];
        })->values();

        $provinces = Province::orderBy('name')->get()->map(function (Province $province) {
            return [
                'id' => $province->id,
                'name' => $province->name,
            ];
        })->values();

        $schools = School::orderBy('name')->get()->map(function (School $school) {
            return [
                'id' => $school->id,
                'name' => $school->name,
            ];
        })->values();

        $academicYears = AcademicYear::orderBy('year', 'desc')->get()->map(function (AcademicYear $academicYear) {
            return [
                'id' => $academicYear->id,
                'year' => $academicYear->year,
            ];
        })->values();

        return array_merge([
            'eventTypes' => $eventTypes,
            'provinces' => $provinces,
            'schools' => $schools,
            'academicYears' => $academicYears,
        ], $extra);
    }
}


