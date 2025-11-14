<?php

namespace App\Services;

use App\Models\Entities\RecurrentEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class RecurrentEventService
{
    /**
     * Get all recurrent events ordered by title and date.
     */
    public function getRecurrentEvents()
    {
        return RecurrentEvent::with(['type', 'province', 'school', 'academicYear'])
            ->orderBy('title')
            ->orderBy('date')
            ->get();
    }

    /**
     * Validate recurrent event data.
     *
     * @throws ValidationException
     */
    protected function validateRecurrentEventData(array $data, ?RecurrentEvent $recurrentEvent = null): array
    {
        $normalized = $this->normalizeInput($data);

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
            'recurrence_month' => ['nullable', 'integer', 'between:1,12'],
            'recurrence_week' => ['nullable', 'integer', 'between:-5,5', Rule::notIn([0])],
            'recurrence_weekday' => ['nullable', 'integer', 'between:0,6'],
            'event_type_id' => ['required', 'exists:event_types,id'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'school_id' => ['nullable', 'exists:schools,id'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'non_working_type' => ['required', 'integer', Rule::in([
                RecurrentEvent::WORKING_DAY,
                RecurrentEvent::NON_WORKING_FIXED,
                RecurrentEvent::NON_WORKING_FLEXIBLE,
            ])],
            'notes' => ['nullable', 'string'],
        ];

        $validator = Validator::make($normalized, $rules);

        $validator->after(function ($validator) use ($normalized) {
            $date = $normalized['date'] ?? null;
            $month = $normalized['recurrence_month'] ?? null;
            $week = $normalized['recurrence_week'] ?? null;
            $weekday = $normalized['recurrence_weekday'] ?? null;

            $hasRecurrenceData = $month !== null || $week !== null || $weekday !== null;
            $recurrenceComplete = $month !== null && $week !== null && $weekday !== null;

            if (!$date && !$hasRecurrenceData) {
                $validator->errors()->add('date', 'Debe especificar una fecha fija o una recurrencia.');
            }

            if ($hasRecurrenceData && !$recurrenceComplete) {
                $validator->errors()->add('recurrence_month', 'Debe completar mes, semana y día para la recurrencia.');
            }
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();

        if (!array_key_exists('non_working_type', $validated)) {
            $validated['non_working_type'] = RecurrentEvent::WORKING_DAY;
        }

        return $validated;
    }

    /**
     * Create a recurrent event.
     */
    public function createRecurrentEvent(array $data): RecurrentEvent
    {
        $validated = $this->validateRecurrentEventData($data);

        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        return RecurrentEvent::create($validated);
    }

    /**
     * Update a recurrent event.
     */
    public function updateRecurrentEvent(RecurrentEvent $recurrentEvent, array $data): RecurrentEvent
    {
        $validated = $this->validateRecurrentEventData($data, $recurrentEvent);
        $validated['updated_by'] = Auth::id();

        $recurrentEvent->update($validated);

        return $recurrentEvent->fresh(['type', 'province', 'school', 'academicYear']);
    }

    /**
     * Delete a recurrent event.
     */
    public function deleteRecurrentEvent(RecurrentEvent $recurrentEvent): bool
    {
        return (bool) $recurrentEvent->delete();
    }

    /**
     * Normalize nullable inputs.
     */
    protected function normalizeInput(array $data): array
    {
        $normalizeNullable = static function ($value) {
            return ($value === '' || $value === null) ? null : $value;
        };

        $normalized = $data;

        $normalized['date'] = $normalizeNullable($normalized['date'] ?? null);
        $normalized['recurrence_month'] = $normalizeNullable($normalized['recurrence_month'] ?? null);
        $normalized['recurrence_week'] = $normalizeNullable($normalized['recurrence_week'] ?? null);
        $normalized['recurrence_weekday'] = $normalizeNullable($normalized['recurrence_weekday'] ?? null);
        $normalized['province_id'] = $normalizeNullable($normalized['province_id'] ?? null);
        $normalized['school_id'] = $normalizeNullable($normalized['school_id'] ?? null);
        $normalized['academic_year_id'] = $normalizeNullable($normalized['academic_year_id'] ?? null);
        $normalized['notes'] = $normalizeNullable($normalized['notes'] ?? null);

        $normalized['non_working_type'] = $normalized['non_working_type'] ?? RecurrentEvent::WORKING_DAY;
        $normalized['non_working_type'] = (int) $normalized['non_working_type'];

        if ($normalized['recurrence_month'] !== null) {
            $normalized['recurrence_month'] = (int) $normalized['recurrence_month'];
        }
        if ($normalized['recurrence_week'] !== null) {
            $normalized['recurrence_week'] = (int) $normalized['recurrence_week'];
        }
        if ($normalized['recurrence_weekday'] !== null) {
            $normalized['recurrence_weekday'] = (int) $normalized['recurrence_weekday'];
        }

        return $normalized;
    }
}


