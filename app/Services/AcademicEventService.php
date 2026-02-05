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

    public function getDashboardCalendar(User $user, ?int $schoolId): array
    {
        $provinceId = $user->province_id;
        $today = Carbon::now()->startOfDay();
        // Set $from to the previous Sunday (or today if today is Sunday)
        $from = $today->copy()->startOfWeek(Carbon::SUNDAY);
        // Set $to to 27 days after $from (to cover 4 full weeks)
        $to = $from->copy()->addDays(27);
        $events = $this->listAround($provinceId, $schoolId, $from, $to);
        return ['from' => $from, 'to' => $to, 'events' => $events];
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
        
        // Get academic events that are associated with recurrent events to filter them out
        $academicEventsWithRecurrent = $academicEvents->filter(function ($event) {
            return $event->recurrent_event_id !== null;
        });
        
        // Get the recurrent event IDs that have associated academic events
        $recurrentEventIdsWithAcademicEvents = $academicEventsWithRecurrent
            ->pluck('recurrent_event_id')
            ->unique()
            ->toArray();
        
        $recurrentEvents = $this->getRecurrentEventsByDateRange($provinceId, $schoolId, $from, $to, $recurrentEventIdsWithAcademicEvents);
        $academicYearEvents = $this->getAcademicYearEventsByDateRange($from, $to);

        // Combine all types of events
        $allEvents = collect()->concat($academicEvents)->concat($recurrentEvents)->concat($academicYearEvents);

        // Sort by date
        $allEvents = $allEvents->sortBy(function ($event) use ($from, $to) {
            if ($event instanceof AcademicEvent) {
                return $event->date;
            } elseif ($event instanceof RecurrentEvent) {
                // For RecurrentEvent, use the calculated_date if available, otherwise the fixed date
                return $event->calculateDate($from, $to);
            } else {
                // For stdClass objects (like AcademicYear events), use the date property directly
                return $event->date ?? null;
            }
        });
        // DEBUG dd($allEvents->map(function ($event) {
        //     return $event->title;
        // })->toArray(),'rrrrrrrr');
        $events = $this->parseForView($allEvents);
        return $events;
    }

    private function getAcademicEventsByDateRange(?int $provinceId, ?int $schoolId, \DateTime $from, \DateTime $to): Collection
    {
        $query = AcademicEvent::with(['type', 'province', 'school', 'academicYear', 'courses', 'recurrentEvent'])->whereBetween('date', [
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

    private function getRecurrentEventsByDateRange(?int $provinceId, ?int $schoolId, \DateTime $from, \DateTime $to, array $recurrentEventIdsWithAcademicEvents = []): Collection
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

            // Check if this recurrent event has any associated academic events
            // If it does, it should NOT be marked as feriado (the academic event handles that)
            $hasAssociatedAcademicEvent = in_array($event->id, $recurrentEventIdsWithAcademicEvents);

            // For fixed date events (no recurrence rules), set calculated_date to first occurrence
            if ($event->date && !$event->recurrence_month && !$event->recurrence_week && $event->recurrence_weekday === null) {
                $occurrenceDate = $occurrences->first();

                $eventWithDate = clone $event;
                $eventWithDate->calculated_date = $occurrenceDate;
                // If there's an associated academic event, mark as working day (not feriado)
                if ($hasAssociatedAcademicEvent) {
                    $eventWithDate->non_working_type = RecurrentEvent::WORKING_DAY;
                }
                $filteredEvents->push($eventWithDate);
            } else {
                // For events with recurrence rules, create virtual events for each occurrence
                foreach ($occurrences as $occurrenceDate) {
                    $virtualEvent = clone $event;
                    $virtualEvent->calculated_date = $occurrenceDate;
                    // If there's an associated academic event, mark as working day (not feriado)
                    if ($hasAssociatedAcademicEvent) {
                        $virtualEvent->non_working_type = RecurrentEvent::WORKING_DAY;
                    }
                    $filteredEvents->push($virtualEvent);
                }
            }
        }

        return $filteredEvents;
    }

    /**
     * Get academic year date events (start_date, end_date, winter breaks) within the date range.
     */
    private function getAcademicYearEventsByDateRange(\DateTime $from, \DateTime $to): Collection
    {
        $fromCarbon = Carbon::parse($from);
        $toCarbon = Carbon::parse($to);

        // Get event types once (optimization)
        $startEventType = EventType::where('code', EventType::CODE_INICIO_CLASES)->first();
        $endEventType = EventType::where('code', EventType::CODE_FIN_CLASES)->first();
        $winterStartEventType = EventType::where('code', EventType::CODE_INICIO_INVIERNO)->first();
        $winterEndEventType = EventType::where('code', EventType::CODE_FIN_INVIERNO)->first();
        $suspensionEventType = EventType::where('code', EventType::CODE_SUSPENCION_PROVINCIAL)->first();

        // Query academic years where any of their dates fall within the range
        // or where the winter break period overlaps with the range
        $academicYears = AcademicYear::where(function ($query) use ($fromCarbon, $toCarbon) {
            $query->whereBetween('start_date', [$fromCarbon, $toCarbon])
                ->orWhereBetween('end_date', [$fromCarbon, $toCarbon])
                ->orWhereBetween('winter_break_start', [$fromCarbon, $toCarbon])
                ->orWhereBetween('winter_break_end', [$fromCarbon, $toCarbon])
                // Also include cases where the range is completely within the winter break period
                ->orWhere(function ($q) use ($fromCarbon, $toCarbon) {
                    $q->where('winter_break_start', '<=', $fromCarbon)
                        ->where('winter_break_end', '>=', $toCarbon);
                })
                // Or where the winter break period overlaps with the range
                ->orWhere(function ($q) use ($fromCarbon, $toCarbon) {
                    $q->where('winter_break_start', '<=', $toCarbon)
                        ->where('winter_break_end', '>=', $fromCarbon);
                });
        })->get();

        $events = collect();

        foreach ($academicYears as $academicYear) {

            // Check start_date
            if ($academicYear->start_date && $academicYear->start_date->between($fromCarbon, $toCarbon)) {
                $event = (object) [
                    'id' => 'academic_year_' . $academicYear->id . '_start',
                    'title' => 'Inicio del ciclo lectivo ' . $academicYear->year,
                    'date' => $academicYear->start_date,
                    'is_non_working_day' => false,
                    'notes' => 'Primer día de clases del ciclo lectivo ' . $academicYear->year,
                    'type' => $startEventType,
                    'province' => null,
                    'school' => null,
                    'academicYear' => $academicYear,
                    'courses' => collect(),
                    'is_recurrent' => false,
                ];
                $events->push($event);
            }

            // Check end_date
            if ($academicYear->end_date && $academicYear->end_date->between($fromCarbon, $toCarbon)) {
                $event = (object) [
                    'id' => 'academic_year_' . $academicYear->id . '_end',
                    'title' => 'Fin del ciclo lectivo ' . $academicYear->year,
                    'date' => $academicYear->end_date,
                    'is_non_working_day' => false,
                    'notes' => 'Último día de clases del ciclo lectivo ' . $academicYear->year,
                    'type' => $endEventType,
                    'province' => null,
                    'school' => null,
                    'academicYear' => $academicYear,
                    'courses' => collect(),
                    'is_recurrent' => false,
                ];
                $events->push($event);
            }

            // Check winter_break_start
            if ($academicYear->winter_break_start && $academicYear->winter_break_start->between($fromCarbon, $toCarbon)) {
                $event = (object) [
                    'id' => 'academic_year_' . $academicYear->id . '_winter_start',
                    'title' => 'Inicio de las vacaciones de invierno',
                    'date' => $academicYear->winter_break_start,
                    'is_non_working_day' => true,
                    'type' => $winterStartEventType,
                    'province' => null,
                    'school' => null,
                    'academicYear' => $academicYear,
                    'courses' => collect(),
                    'is_recurrent' => false,
                ];
                $events->push($event);
            }

            // Check winter_break_end
            if ($academicYear->winter_break_end && $academicYear->winter_break_end->between($fromCarbon, $toCarbon)) {
                $event = (object) [
                    'id' => 'academic_year_' . $academicYear->id . '_winter_end',
                    'title' => 'Fin de las vacaciones de invierno ' . $academicYear->year,
                    'date' => $academicYear->winter_break_end,
                    'is_non_working_day' => true,
                    'notes' => 'Fin de las vacaciones de invierno del ciclo lectivo ' . $academicYear->year,
                    'type' => $winterEndEventType,
                    'province' => null,
                    'school' => null,
                    'academicYear' => $academicYear,
                    'courses' => collect(),
                    'is_recurrent' => false,
                ];
                $events->push($event);
            }

            // Add events for all dates between winter_break_start and winter_break_end
            if ($academicYear->winter_break_start && $academicYear->winter_break_end) {
                $winterBreakStart = Carbon::parse($academicYear->winter_break_start);
                $winterBreakEnd = Carbon::parse($academicYear->winter_break_end);

                // Only process dates that fall within the requested range
                $currentDate = $winterBreakStart->copy();
                while ($currentDate <= $winterBreakEnd) {
                    // Skip weekends (Saturday = 6, Sunday = 0)
                    $dayOfWeek = $currentDate->dayOfWeek;
                    $isWeekend = $dayOfWeek === Carbon::SATURDAY || $dayOfWeek === Carbon::SUNDAY;

                    // Only add if this date is within the requested range and not a weekend
                    if ($currentDate->between($fromCarbon, $toCarbon) && !$isWeekend) {
                        $event = (object) [
                            'id' => 'academic_year_' . $academicYear->id . '_winter_' . $currentDate->format('Y-m-d'),
                            'title' => 'receso escolar',
                            'date' => $currentDate->copy(),
                            'is_non_working_day' => true,
                            'type' => $suspensionEventType,
                            'province' => null,
                            'school' => null,
                            'academicYear' => $academicYear,
                            'courses' => collect(),
                            'is_recurrent' => false,
                        ];
                        $events->push($event);
                    }
                    $currentDate->addDay();
                }
            }
        }

        return $events;
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
            } else {
                // Handle stdClass objects (like AcademicYear events)
                $effectiveDate = $event->date ?? null;
            }

            // For RecurrentEvent, determine is_non_working_day based on non_working_type
            // If the event was modified to WORKING_DAY (because it has an associated AcademicEvent),
            // it should not be marked as non-working
            if ($event instanceof RecurrentEvent) {
                $nonWorkingType = $event->non_working_type ?? RecurrentEvent::WORKING_DAY;
                $isNonWorkingDay = in_array($nonWorkingType, [
                    RecurrentEvent::NON_WORKING_FIXED,
                    RecurrentEvent::NON_WORKING_FLEXIBLE
                ]);
            } else {
                // For AcademicEvent and other event types, use is_non_working_day directly
                $isNonWorkingDay = $event->is_non_working_day ?? false;
            }

            // Get courses - handle both AcademicEvent instances and stdClass objects
            $courses = [];
            if ($event instanceof AcademicEvent && $event->courses->isNotEmpty()) {
                $courses = $event->courses->map(function (Course $course) {
                    return [
                        'id' => $course->id,
                        'nice_name' => $course->nice_name,
                    ];
                })->toArray();
            } elseif (isset($event->courses) && $event->courses instanceof Collection && $event->courses->isNotEmpty()) {
                $courses = $event->courses->map(function ($course) {
                    return [
                        'id' => $course->id ?? null,
                        'nice_name' => $course->nice_name ?? null,
                    ];
                })->toArray();
            }

            // Build notes - add "Corresponde a: (evento recurrente)" for AcademicEvents with recurrent_event_id
            $notes = $event->notes ?? null;
            if ($event instanceof AcademicEvent && $event->recurrent_event_id) {
                $recurrentEvent = $event->recurrentEvent;
                if ($recurrentEvent) {
                    $correspondeText = "Corresponde a: {$recurrentEvent->title}";
                    $notes = $notes ? "{$notes}\n{$correspondeText}" : $correspondeText;
                }
            }

            return [
                'id' => $event->id,
                'title' => $event->title,
                'date' => $effectiveDate, //? $effectiveDate->format('d/m/Y') : null,
                'is_non_working_day' => $isNonWorkingDay,
                'notes' => $notes,
                'event_type' => [
                    'id' => $event->type?->id ?? null,
                    'code' => $event->type?->code ?? null,
                    'name' => $event->type?->name ?? null,
                    'scope' => $event->type?->scope ?? null,
                ],
                'province' => $event->province?->name ?? null,
                'school' => $event->school?->name ?? null,
                'academic_year' => $event->academicYear?->year ?? null,
                'courses' => $courses,
                'is_recurrent' => $event instanceof RecurrentEvent || ($event->is_recurrent ?? false),
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

        // If recurrent_event_id is provided, populate data from RecurrentEvent
        if (!empty($validatedData['recurrent_event_id'])) {
            $recurrentEvent = RecurrentEvent::with(['type'])->findOrFail($validatedData['recurrent_event_id']);

            // Use RecurrentEvent's title and event_type_id
            $validatedData['title'] = $recurrentEvent->title;
            $validatedData['event_type_id'] = $recurrentEvent->event_type_id;

            // Calculate date for the current academic year
            $academicYear = AcademicYear::findOrFail($validatedData['academic_year_id']);
            $yearStart = Carbon::create($academicYear->year, 1, 1)->startOfYear();
            $yearEnd = Carbon::create($academicYear->year, 12, 31)->endOfYear();

            $calculatedDate = $recurrentEvent->calculateDate($yearStart, $yearEnd);
            if ($calculatedDate) {
                $validatedData['date'] = $calculatedDate->format('Y-m-d');
            } else {
                // Fallback: use the RecurrentEvent's date if calculation fails
                $validatedData['date'] = $recurrentEvent->date;
            }

            // Set is_non_working_day based on RecurrentEvent's non_working_type
            $validatedData['is_non_working_day'] = in_array($recurrentEvent->non_working_type, [
                RecurrentEvent::NON_WORKING_FIXED,
                RecurrentEvent::NON_WORKING_FLEXIBLE
            ]);
        }

        // Extract course_ids if present
        $courseIds = $validatedData['course_ids'] ?? [];
        unset($validatedData['course_ids']);

        // dd($validatedData);
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
                'event_type_id' => EventType::CODE_INICIO_CLASES,
            ],
            [
                'title' => 'Fin del ciclo lectivo',
                'date' => now()->setDate($academicYearId, 12, 15),
                'is_non_working_day' => false,
                'notes' => 'Último día de clases',
                'event_type_id' => EventType::CODE_FIN_CLASES,
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
        // If recurrent_event_id is provided, event_type_id and title/date are optional (will be set from RecurrentEvent)
        $hasRecurrentEvent = !empty($data['recurrent_event_id']);

        $rules = [
            'title' => $hasRecurrentEvent ? ['nullable', 'string', 'max:255'] : ['required', 'string', 'max:255'],
            'date' => $hasRecurrentEvent ? ['nullable', 'date'] : ['required', 'date'],
            'is_non_working_day' => ['required', 'bool'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'event_type_id' => $hasRecurrentEvent ? ['nullable', 'exists:event_types,id'] : ['required', 'exists:event_types,id'],
            'recurrent_event_id' => ['nullable', 'exists:recurrent_events,id'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'school_id' => ['nullable', 'exists:schools,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'course_ids' => ['nullable', 'array'],
            'course_ids.*' => ['exists:courses,id'],
            'created_by' => ['nullable', 'exists:users,id'],
            'updated_by' => ['nullable', 'exists:users,id'],
            'created_at' => ['nullable', 'date'],
            'updated_at' => ['nullable', 'date'],
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
