<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\School\SchoolBaseController;
use App\Models\Entities\AcademicEvent;
use App\Models\Entities\RecurrentEvent;
use App\Models\Entities\School;
use App\Models\Catalogs\EventType;
use App\Models\Catalogs\Province;
use App\Models\Entities\AcademicYear;
use App\Models\Entities\Course;
use App\Models\Catalogs\SchoolLevel;
use App\Models\Catalogs\SchoolShift;
use App\Services\AcademicEventService;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AcademicEventController extends SchoolBaseController
{
    public function __construct(private AcademicEventService $academicEventService)
    {
        // No middleware needed here as it's handled by route middleware
    }

    public function index(Request $request, School $school)
    {
        // Get academic year from request or use current/default
        $academicYearId = $request->get('academic_year_id');

        if ($academicYearId) {
            $academicYear = AcademicYear::find($academicYearId);
        } else {
            $academicYear = AcademicYear::getCurrent() ?? AcademicYear::orderBy('year', 'desc')->first();
        }

        if (!$academicYear) {
            return redirect()->route('school.show', $school->slug)
                ->with('error', 'No hay ciclos lectivos disponibles.');
        }

        $academicEvents = $this->academicEventService->listBySchoolAndAcademicYear(
            $school->id,
            $academicYear->id
        )->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'date' => $event->date,
                'is_non_working_day' => $event->is_non_working_day,
                'notes' => $event->notes,
                'type' => $event->type ? [
                    'id' => $event->type->id,
                    'name' => $event->type->name,
                    'code' => $event->type->code,
                ] : null,
                'province' => $event->province ? ['name' => $event->province->name] : null,
                'school' => $event->school ? ['name' => $event->school->name, 'short' => $event->school->short] : null,
                'academic_year' => $event->academicYear ? ['year' => $event->academicYear->year] : null,
                'courses' => $event->courses->map(function ($course) {
                    return [
                        'id' => $course->id,
                        'nice_name' => $course->nice_name,
                    ];
                }),
            ];
        });

        return Inertia::render('AcademicEvents/Index', [
            'academicEvents' => $academicEvents,
            'school' => $school,
            'academicYear' => $academicYear,
            'academicYears' => AcademicYear::orderBy('year', 'desc')->get(),
            'breadcrumbs' => Breadcrumbs::generate('school.academic-events.index', $school),
        ]);
    }

    public function create(Request $request, School $school)
    {
        // Get the current academic year or the first available
        $academicYear = AcademicYear::getCurrent() ?? AcademicYear::orderBy('year', 'desc')->first();

        if (!$academicYear) {
            return redirect()->route('school.academic-events.index', $school->slug)
                ->with('error', 'No hay ciclos lectivos disponibles.');
        }

        // Load school relationships
        $school->load(['schoolLevels', 'shifts']);

        return Inertia::render('AcademicEvents/Create', $this->formOptions($school, [
            'school' => $school,
            'academicYear' => $academicYear,
            'breadcrumbs' => Breadcrumbs::generate('school.academic-events.create', $school),
        ]));
    }

    public function store(Request $request, School $school)
    {
        try {
            $data = $request->all();
            $data['school_id'] = $school->id;
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            // Handle course_ids if present
            if ($request->has('course_ids')) {
                $data['course_ids'] = $request->input('course_ids', []);
            }

            $this->academicEventService->create($data);

            return redirect()
                ->route('school.academic-events.index', $school->slug)
                ->with('success', 'Evento académico creado exitosamente.');
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

    public function show(Request $request, School $school, AcademicEvent $academicEvent)
    {
        // Ensure the event belongs to this school
        if ($academicEvent->school_id !== $school->id) {
            abort(403, 'Este evento no pertenece a esta escuela.');
        }

        $academicEvent->load(['type', 'province', 'school', 'academicYear', 'courses']);

        return Inertia::render('AcademicEvents/Show', [
            'academicEvent' => $academicEvent,
            'school' => $school,
            'breadcrumbs' => Breadcrumbs::generate('school.academic-events.show', $school, $academicEvent),
        ]);
    }

    public function edit(Request $request, School $school, AcademicEvent $academicEvent)
    {
        // Ensure the event belongs to this school
        if ($academicEvent->school_id !== $school->id) {
            abort(403, 'Este evento no pertenece a esta escuela.');
        }

        // Load school relationships
        $school->load(['schoolLevels', 'shifts']);
        $academicEvent->load(['type', 'province', 'school', 'academicYear', 'courses.schoolShift', 'recurrentEvent']);

        return Inertia::render('AcademicEvents/Edit', $this->formOptions($school, [
            'academicEvent' => $academicEvent,
            'school' => $school,
            'breadcrumbs' => Breadcrumbs::generate('school.academic-events.edit', $school, $academicEvent),
        ]));
    }

    public function update(Request $request, School $school, AcademicEvent $academicEvent)
    {
        // Ensure the event belongs to this school
        if ($academicEvent->school_id !== $school->id) {
            abort(403, 'Este evento no pertenece a esta escuela.');
        }

        try {
            $data = $request->all();
            $data['school_id'] = $school->id;
            $data['updated_by'] = auth()->id();

            // Handle course_ids if present
            if ($request->has('course_ids')) {
                $data['course_ids'] = $request->input('course_ids', []);
            }

            $this->academicEventService->update($academicEvent, $data);

            return redirect()
                ->route('school.academic-events.index', $school->slug)
                ->with('success', 'Evento académico actualizado exitosamente.');
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

    public function destroy(Request $request, School $school, AcademicEvent $academicEvent)
    {
        // Ensure the event belongs to this school
        if ($academicEvent->school_id !== $school->id) {
            abort(403, 'Este evento no pertenece a esta escuela.');
        }

        try {
            $this->academicEventService->delete($academicEvent);

            return redirect()
                ->route('school.academic-events.index', $school->slug)
                ->with('success', 'Evento académico eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Share options used by Create/Edit forms.
     */
    protected function formOptions(School $school, array $extra = []): array
    {
        // Get RecurrentEvents with NON_WORKING_FLEXIBLE type
        $recurrentEvents = RecurrentEvent::with(['type'])
            ->where('non_working_type', RecurrentEvent::NON_WORKING_FLEXIBLE)
            ->orderBy('title')
            ->get()
            ->map(function (RecurrentEvent $recurrentEvent) {
                $dateFormatted = null;
                if ($recurrentEvent->date) {
                    // Format as DD/MM (e.g., "25/12" for Christmas)
                    $dateFormatted = \Carbon\Carbon::parse($recurrentEvent->date)->format('d/m');
                }

                return [
                    'id' => $recurrentEvent->id,
                    'title' => $recurrentEvent->title,
                    'date' => $recurrentEvent->date,
                    'date_formatted' => $dateFormatted,
                    'event_type' => $recurrentEvent->type ? [
                        'id' => $recurrentEvent->type->id,
                        'name' => $recurrentEvent->type->name,
                        'code' => $recurrentEvent->type->code,
                    ] : null,
                ];
            })->values();

        // Get event types grouped by scope
        $eventTypesByScope = EventType::orderBy('scope')->orderBy('name')->get()->groupBy('scope')->map(function ($types, $scope) {
            return $types->map(function (EventType $eventType) {
                return [
                    'id' => $eventType->id,
                    'label' => $eventType->name,
                    'code' => $eventType->code,
                    'scope' => $eventType->scope,
                ];
            })->values();
        });

        // Also provide flat list for backward compatibility
        $eventTypes = EventType::forDropdown();

        $provinces = Province::orderBy('name')->get()->map(function (Province $province) {
            return [
                'id' => $province->id,
                'code' => $province->code,
                'name' => $province->name,
            ];
        })->values();

        $academicYears = AcademicYear::orderBy('year', 'desc')->get()->map(function (AcademicYear $year) {
            return [
                'id' => $year->id,
                'year' => $year->year,
                'label' => $year->year,
            ];
        })->values();

        // Get school levels and shifts for CoursePopoverSelect
        $schoolLevels = $school->schoolLevels()->orderBy('id')->get()->map(function (SchoolLevel $level) {
            return [
                'id' => $level->id,
                'code' => $level->code,
                'name' => $level->name,
                'years_duration' => $level->pivot->years_duration,
                'grades' => $level->pivot->grades,
            ];
        })->values();

        // Find primary level if config requires it
        $primaryLevel = null;
        $oneSchoolOnlyPrimary = config('malulu.one_school_only_primary', false);
        if ($oneSchoolOnlyPrimary) {
            $primaryLevel = $schoolLevels->firstWhere('code', SchoolLevel::PRIMARY);
        }

        $schoolShifts = $school->shifts()->orderBy('id')->get()->map(function (SchoolShift $shift) {
            return [
                'id' => $shift->id,
                'code' => $shift->code,
                'name' => $shift->name,
            ];
        })->values();

        // Get courses for this school (for display purposes, CoursePopoverSelect will fetch dynamically)
        $courses = Course::where('school_id', $school->id)
            ->get()
            ->map(function (Course $course) {
                return [
                    'id' => $course->id,
                    'nice_name' => $course->nice_name,
                ];
            })->values();

        $nonWorkingTypeOptions = [
            [
                'value' => false,
                'label' => 'Laborable'
            ],
            [
                'value' => true,
                'label' => 'No laborable'
            ]
        ];

        return array_merge([
            'recurrentEvents' => $recurrentEvents,
            'eventTypesByScope' => $eventTypesByScope,
            'eventTypes' => $eventTypes,
            'provinces' => $provinces,
            'academicYears' => $academicYears,
            'courses' => $courses,
            'schoolLevels' => $schoolLevels,
            'schoolShifts' => $schoolShifts,
            'oneSchoolOnlyPrimary' => $oneSchoolOnlyPrimary,
            'primaryLevel' => $primaryLevel,
            'nonWorkingTypeOptions' => $nonWorkingTypeOptions,
        ], $extra);
    }
}
