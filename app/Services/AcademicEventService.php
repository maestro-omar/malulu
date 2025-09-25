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
        return $this->listAround($provinceId, null, 25, 5);
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
    public function listAround(?int $provinceId, ?int $schoolId, int $nextDays = 25, int $pastDays = 5): array
    {
        $today = Carbon::now()->startOfDay();
        $from = $today->copy()->subDays($pastDays);
        $to = $today->copy()->addDays($nextDays);

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

        $events = $this->parseForView($allEvents);
        /*
2 => array:12 [▼
    "id" => 19
    "title" => "Día del Estudiante / Día de la Primavera"
    "date" => "2025-09-21"
    "is_non_working_day" => false
    "notes" => "Conmemoración nacional"
    "event_type" => array:4 [▼
      "id" => 2
      "code" => "conmemoracion_nacional"
      "name" => "Conmemoración"
      "scope" => "nacional"
    ]
    "province" => null
    "school" => null
    "academic_year" => null
    "courses" => []
    "is_recurrent" => true
    "recurrence_info" => null
  ]
        */
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
            if ($event->date && !$event->recurrence_month && !$event->recurrence_week && $event->recurrence_weekday === null) {
                // This is a fixed date event (not a recurring one) - check if it's in range
                $eventDate = Carbon::parse($event->date);
                if ($eventDate->between($from, $to)) {
                    $filteredEvents->push($event);
                }
            } elseif ($event->date && ($event->recurrence_month || $event->recurrence_week || $event->recurrence_weekday !== null)) {
                // This has a date field but also recurrence rules - the date represents day/month for every year
                $occurrences = $this->calculateDayMonthOccurrences($event, $from, $to);
                if ($occurrences->isNotEmpty()) {
                    foreach ($occurrences as $occurrenceDate) {
                        $virtualEvent = clone $event;
                        $virtualEvent->calculated_date = $occurrenceDate;
                        $filteredEvents->push($virtualEvent);
                    }
                }
            } else {
                // Pure recurring event based on recurrence rules only
                $occurrences = $this->calculateRecurrenceOccurrences($event, $from, $to);
                if ($occurrences->isNotEmpty()) {
                    // Create virtual events for each occurrence
                    foreach ($occurrences as $occurrenceDate) {
                        $virtualEvent = clone $event;
                        $virtualEvent->calculated_date = $occurrenceDate;
                        $filteredEvents->push($virtualEvent);
                    }
                }
            }
        }

        return $filteredEvents;
    }

    /**
     * Calculate occurrences for events with day/month pattern (like Christmas on 25/12 every year)
     */
    private function calculateDayMonthOccurrences(RecurrentEvent $event, \DateTime $from, \DateTime $to): Collection
    {
        $occurrences = collect();
        $fromCarbon = Carbon::parse($from);
        $toCarbon = Carbon::parse($to);

        // Parse the day/month from the event date
        $eventDate = Carbon::parse($event->date);
        $day = $eventDate->day;
        $month = $eventDate->month;

        // Get the year range to check
        $startYear = $fromCarbon->year;
        $endYear = $toCarbon->year;

        for ($year = $startYear; $year <= $endYear; $year++) {
            try {
                $occurrenceDate = Carbon::create($year, $month, $day);

                // Check if this date falls within our range
                if ($occurrenceDate->between($fromCarbon, $toCarbon)) {
                    $occurrences->push($occurrenceDate);
                }
            } catch (\Exception $e) {
                // Skip invalid dates (like Feb 29 in non-leap years)
                continue;
            }
        }

        return $occurrences;
    }

    /**
     * Calculate all occurrences of a recurring event within a date range
     */
    private function calculateRecurrenceOccurrences(RecurrentEvent $event, \DateTime $from, \DateTime $to): Collection
    {
        $occurrences = collect();
        $fromCarbon = Carbon::parse($from);
        $toCarbon = Carbon::parse($to);

        // Get the year range to check
        $startYear = $fromCarbon->year;
        $endYear = $toCarbon->year;

        for ($year = $startYear; $year <= $endYear; $year++) {
            if ($event->recurrence_month && $event->recurrence_week && $event->recurrence_weekday !== null) {
                $occurrenceDate = $this->calculateOccurrenceForYear(
                    $year,
                    $event->recurrence_month,
                    $event->recurrence_week,
                    $event->recurrence_weekday
                );

                if ($occurrenceDate && $occurrenceDate->between($fromCarbon, $toCarbon)) {
                    $occurrences->push($occurrenceDate);
                }
            }
        }

        return $occurrences;
    }

    /**
     * Calculate the occurrence date for a specific year
     */
    private function calculateOccurrenceForYear(int $year, int $month, int $week, int $weekday): ?Carbon
    {
        // Get the first day of the month
        $firstDay = Carbon::create($year, $month, 1);

        if ($week > 0) {
            // Positive week: find the Nth occurrence
            $targetDay = $firstDay->copy()->startOfWeek()->addDays($weekday);

            // Find the first occurrence of the weekday in the month
            while ($targetDay->month !== $month) {
                $targetDay->addWeek();
            }

            // Add the required number of weeks
            $targetDay->addWeeks($week - 1);

            // Check if we're still in the same month
            if ($targetDay->month === $month) {
                return $targetDay;
            }
        } else {
            // Negative week: find the Nth occurrence from the end
            $lastDay = $firstDay->copy()->endOfMonth();
            $targetDay = $lastDay->copy()->startOfWeek()->addDays($weekday);

            // Find the last occurrence of the weekday in the month
            while ($targetDay->month !== $month) {
                $targetDay->subWeek();
            }

            // Subtract the required number of weeks (week is negative)
            $targetDay->addWeeks($week + 1);

            // Check if we're still in the same month
            if ($targetDay->month === $month) {
                return $targetDay;
            }
        }

        return null;
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

            return [
                'id' => $event->id,
                'title' => $event->title,
                'date' => $effectiveDate, //? $effectiveDate->format('d/m/Y') : null,
                'is_non_working_day' => true && $event->is_non_working_day,
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
        return AcademicEvent::create($validatedData);
    }

    /**
     * Update an academic event.
     */
    public function update(AcademicEvent $event, array $data): AcademicEvent
    {
        $validatedData = $this->validateEventData($data, $event);
        $event->update($validatedData);
        return $event;
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
            'is_non_working_day' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'event_type_id' => ['required', 'exists:event_types,id'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'school_id' => ['nullable', 'exists:schools,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'created_by' => ['required', 'exists:users,id'],
            'updated_by' => ['nullable', 'exists:users,id'],
        ];

        $messages = [
            'title.required' => 'El título es obligatorio',
            'title.max' => 'El título no puede superar los 255 caracteres',
            'date.required' => 'La fecha es obligatoria',
            'date.date' => 'La fecha debe tener un formato válido',
            'is_non_working_day.boolean' => 'El campo "día no laborable" debe ser verdadero o falso',
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

        return $validator->validated();
    }
}
