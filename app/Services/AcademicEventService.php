<?php

namespace App\Services;

use App\Models\Entities\AcademicEvent;
use App\Models\Entities\RecurrentEvent;
use App\Models\Catalogs\EventType;
use App\Models\Entities\User;
use App\Models\Entities\AcademicYear;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use App\Models\Entities\Course;
use Carbon\Carbon;

class AcademicEventService
{
    //TODO mejorar omar
    //quizas debería consturir el calendario dentro de cada panel del dashboard que ya tengo la información de rol, escuela
    //por ahora, buscamos generales asumiendo una escuela y una provincia

    public function getDashboardCalendar(User $user): array
    {
        $provinceId = $user->province_id;
        $today = Carbon::now()->startOfDay();
        // Set $from to the previous Sunday (or today if today is Sunday)
        $from = $today->copy()->startOfWeek(Carbon::SUNDAY);
        // Set $to to 27 days after $from (to cover 4 full weeks)
        $to = $from->copy()->addDays(27);
        return $this->listAround($provinceId, null, $from, $to);
    }
    /**
     * List all academic events for a given school and academic year.
     */
    public function listBySchoolAndAcademicYear(int $schoolId, int $academicYearId): Collection
    {
        return AcademicEvent::with(['type', 'province', 'school', 'academicYear', 'courses'])
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $academicYearId)
            ->orderBy('date')
            ->get();
    }

    /**
     * List academic events for the next N days and the past M days.
     */
    public function listAround(?int $provinceId, ?int $schoolId, $from, $to): array
    {
        $academicEvents = $this->getAcademicEventsByDateRange($provinceId, $schoolId, $from, $to);
        $recurrentEvents = $this->getRecurrentEventsByDateRange($provinceId, $schoolId, $from, $to);

        // Combine both types of events
        $allEvents = collect()->concat($academicEvents)->concat($recurrentEvents);

        // Sort by date
        $allEvents = $allEvents->sortBy(function ($event) use ($from, $to) {
            if ($event instanceof AcademicEvent) {
                return $event->date;
            } else {
                // For RecurrentEvent, use the calculated_date if available, otherwise the fixed date
                return $event->calculateDate($from, $to);
            }
        });
        // DEBUG dd($allEvents->map(function ($event) {
        //     return $event->title;
        // })->toArray(),'rrrrrrrr');
        $events = $this->parseForView($allEvents);
        return ['from' => $from, 'to' => $to, 'events' => $events];
    }

    private function getAcademicEventsByDateRange(?int $provinceId, ?int $schoolId, \DateTime $from, \DateTime $to): Collection
    {
        $query = AcademicEvent::with(['type', 'province', 'school', 'academicYear', 'courses'])->whereBetween('date', [
            $from,
            $to,
        ]);
        if ($provinceId)
            $query = $query->where(function ($q) use ($provinceId) {
                $q->where('province_id', $provinceId)
                    ->orWhereNull('province_id');
            });
        else
            $query = $query->whereNull('province_id');
        if ($schoolId)
            $query = $query->where(function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId)
                    ->orWhereNull('school_id');
            });
        else
            $query = $query->whereNull('school_id');

        $query = $query->orderBy('date');
        return $query->get();
    }

    private function getRecurrentEventsByDateRange(?int $provinceId, ?int $schoolId, \DateTime $from, \DateTime $to): Collection
    {
        // Get all recurrent events and filter in PHP since we need to calculate dates
        $query = RecurrentEvent::with(['type', 'province', 'school']);

        // Apply province and school filters
        if ($provinceId) {
            $query = $query->where(function ($q) use ($provinceId) {
                $q->where('province_id', $provinceId)
                    ->orWhereNull('province_id');
            });
        } else {
            $query = $query->whereNull('province_id');
        }

        if ($schoolId) {
            $query = $query->where(function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId)
                    ->orWhereNull('school_id');
            });
        } else {
            $query = $query->whereNull('school_id');
        }

        $recurrentEvents = $query->get();

        // Filter events that have actual occurrences within the date range
        $filteredEvents = collect();

        foreach ($recurrentEvents as $event) {
            // Use the model method to get all occurrences in the range
            $occurrences = $event->getOccurrencesInRange($from, $to);

            if ($occurrences->isEmpty()) {
                continue;
            }

            // For fixed date events (no recurrence rules), set calculated_date to first occurrence
            if ($event->date && !$event->recurrence_month && !$event->recurrence_week && $event->recurrence_weekday === null) {
                $eventWithDate = clone $event;
                $eventWithDate->calculated_date = $occurrences->first();
                $filteredEvents->push($eventWithDate);
            } else {
                // For events with recurrence rules, create virtual events for each occurrence
                foreach ($occurrences as $occurrenceDate) {
                    $virtualEvent = clone $event;
                    $virtualEvent->calculated_date = $occurrenceDate;
                    $filteredEvents->push($virtualEvent);
                }
            }
        }

        return $filteredEvents;
    }

    /**
     * Parse academic events for a view.
     */
    public function parseForView(Collection $events): array
    {
        return $events->map(function ($event) {
            // Determine the effective date for the event
            $effectiveDate = null;
            if ($event instanceof AcademicEvent) {
                $effectiveDate = $event->date;
            } elseif ($event instanceof RecurrentEvent) {
                $effectiveDate = $event->calculated_date ?? $event->date;
            }

            $isNonWorkingDay = $event->is_non_working_day ?? false;

            return [
                'id' => $event->id,
                'title' => $event->title,
                'date' => $effectiveDate, //? $effectiveDate->format('d/m/Y') : null,
                'is_non_working_day' => $isNonWorkingDay,
                'notes' => $event->notes,
                'event_type' => [
                    'id' => $event->type?->id,
                    'code' => $event->type?->code,
                    'name' => $event->type?->name,
                    'scope' => $event->type?->scope,
                ],
                'province' => $event->province?->name,
                'school' => $event->school?->name,
                'academic_year' => $event->academicYear?->year,
                'courses' => $event instanceof AcademicEvent && $event->courses->isNotEmpty()
                    ? $event->courses->map(function (Course $course) {
                        return [
                            'id' => $course->id,
                            'nice_name' => $course->nice_name,
                        ];
                    })
                    : [],
                'is_recurrent' => $event instanceof RecurrentEvent,
                'recurrence_info' => $event instanceof RecurrentEvent && empty($event->date) ? [
                    'month' => $event->recurrence_month,
                    'week' => $event->recurrence_week,
                    'weekday' => $event->recurrence_weekday,
                ] : null,
            ];
        })->toArray();
    }

    /**
     * Create a new academic event.
     */
    public function create(array $data): AcademicEvent
    {
        $validatedData = $this->validateEventData($data);

        // Extract course_ids if present
        $courseIds = $validatedData['course_ids'] ?? [];
        unset($validatedData['course_ids']);

        $event = AcademicEvent::create($validatedData);

        // Attach courses if provided
        if (!empty($courseIds)) {
            $event->courses()->attach($courseIds, [
                'created_by' => $validatedData['created_by'] ?? auth()->id(),
                'updated_by' => $validatedData['updated_by'] ?? auth()->id(),
            ]);
        }

        return $event->load('courses');
    }

    /**
     * Update an academic event.
     */
    public function update(AcademicEvent $event, array $data): AcademicEvent
    {
        $validatedData = $this->validateEventData($data, $event);

        // Extract course_ids if present
        $courseIds = $validatedData['course_ids'] ?? null;
        unset($validatedData['course_ids']);

        $event->update($validatedData);

        // Sync courses if provided
        if ($courseIds !== null) {
            $event->courses()->sync($courseIds);

            // Update timestamps in pivot table
            if (!empty($courseIds)) {
                $event->courses()->updateExistingPivot($courseIds, [
                    'updated_by' => $validatedData['updated_by'] ?? auth()->id(),
                ]);
            }
        }

        return $event->load('courses');
    }

    /**
     * Delete an academic event.
     */
    public function delete(AcademicEvent $event): bool
    {
        return $event->delete();
    }

    /**
     * Initialize common academic events for a new academic year.
     * Example defaults: start/end of year, exam periods, report delivery.
     */
    public function initiateAcademicYearEvents(int $schoolId, int $academicYearId): void
    {
        $defaults = [
            [
                'title' => 'Inicio del ciclo lectivo',
                'date' => now()->setDate($academicYearId, 3, 3), // adjust logic per rules
                'is_non_working_day' => false,
                'notes' => 'Primer día de clases',
                'event_type_id' => EventType::CODE_INICIO_ESCOLAR,
            ],
            [
                'title' => 'Fin del ciclo lectivo',
                'date' => now()->setDate($academicYearId, 12, 15),
                'is_non_working_day' => false,
                'notes' => 'Último día de clases',
                'event_type_id' => EventType::CODE_FIN_ESCOLAR,
            ],
            [
                'title' => 'Inicio de las vacaciones de invierno',
                'date' => now()->setDate($academicYearId, 12, 15),
                'is_non_working_day' => false,
                'notes' => 'Inicio de las vacaciones de invierno',
                'event_type_id' => EventType::CODE_INICIO_INVIERNO
            ],
            [
                'title' => 'Fin de las vacaciones de invierno',
                'date' => now()->setDate($academicYearId, 12, 31),
                'is_non_working_day' => false,
                'notes' => 'Fin de las vacaciones de invierno',
                'event_type_id' => EventType::CODE_FIN_INVIERNO
            ],
        ];

        foreach ($defaults as $event) {
            AcademicEvent::create(array_merge($event, [
                'school_id' => $schoolId,
                'academic_year_id' => $academicYearId,
            ]));
        }
    }

    /**
     * Validate academic event data
     */
    public function validateEventData(array $data, ?AcademicEvent $event = null): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'is_non_working_day' => ['required', 'bool'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'event_type_id' => ['required', 'exists:event_types,id'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'school_id' => ['nullable', 'exists:schools,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'course_ids' => ['nullable', 'array'],
            'course_ids.*' => ['exists:courses,id'],
        ];

        $messages = [
            'title.required' => 'El título es obligatorio',
            'title.max' => 'El título no puede superar los 255 caracteres',
            'date.required' => 'La fecha es obligatoria',
            'date.date' => 'La fecha debe tener un formato válido',
            'non_working_type.required' => 'Debe indicar si es un día laborable o no laborable',
            'non_working_type.integer' => 'El tipo de día no laborable debe ser un número válido',
            'non_working_type.in' => 'El tipo de día no laborable seleccionado no es válido',
            'notes.max' => 'Las notas no pueden superar los 1000 caracteres',
            'event_type_id.required' => 'El tipo de evento es obligatorio',
            'event_type_id.exists' => 'El tipo de evento seleccionado no existe',
            'province_id.exists' => 'La provincia seleccionada no existe',
            'school_id.exists' => 'La escuela seleccionada no existe',
            'academic_year_id.required' => 'El ciclo lectivo es obligatorio',
            'academic_year_id.exists' => 'El ciclo lectivo seleccionado no existe',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();

        return $validated;
    }
}
