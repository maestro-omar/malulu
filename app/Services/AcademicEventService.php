<?php

namespace App\Services;

use App\Models\Entities\AcademicEvent;
use App\Models\Catalogs\EventType;
use App\Models\Entities\User;
use App\Models\Entities\AcademicYear;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\Entities\Course;

class AcademicEventService
{

    public function getDashboardCalendar(User $user): Collection
    {
        $currentAcademicYear = AcademicYear::getCurrent();
        $query = AcademicEvent::with(['type', 'school', 'academicYear', 'courses'])->where('academic_year_id', $currentAcademicYear->id);
        if ($user->isSuperadmin()) {
            $query = $query->whereNull('school_id');
        } else {
            $query = $query->where(function ($q) use ($user) {
                $q->where('school_id', $user->school_id)
                    ->orWhereNull('school_id');
            });
        }
        $ret = $query
            ->orderBy('date')
            ->toSql();
        dd($ret);
        return $ret;
    }
    /**
     * List all academic events for a given school and academic year.
     */
    public function listBySchoolAndAcademicYear(int $schoolId, int $academicYearId): Collection
    {
        return AcademicEvent::with(['type', 'school', 'academicYear', 'courses'])
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $academicYearId)
            ->orderBy('date')
            ->get();
    }

    /**
     * List academic events for the next N days and the past M days.
     */
    public function listAroundBySchoolAndAcademicYear(int $schoolId, int $academicYearId, int $nextDays = 25, int $pastDays = 5): Collection
    {
        $today = now()->startOfDay();

        return AcademicEvent::with(['type', 'school', 'academicYear', 'courses'])
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $academicYearId)
            ->whereBetween('date', [
                $today->copy()->subDays($pastDays),
                $today->copy()->addDays($nextDays),
            ])
            ->orderBy('date')
            ->get();
    }

    /**
     * Parse academic events for a view.
     */
    public function parseForView(Collection $events): array
    {
        return $events->map(function (AcademicEvent $event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'date' => $event->date ? $event->date->format('d/m/Y') : null,
                'is_non_working_day' => $event->is_non_working_day,
                'notes' => $event->notes,
                'event_type' => [
                    'id' => $event->type?->id,
                    'code' => $event->type?->code,
                    'name' => $event->type?->name,
                    'scope' => $event->type?->scope,
                ],
                'school' => $event->school?->name,
                'academic_year' => $event->academicYear?->year,
                'courses' => $event->courses->map(function (Course $course) {
                    return [
                        'id' => $course->id,
                        'namnice_namee' => $course->nice_name,
                    ];
                }),
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
