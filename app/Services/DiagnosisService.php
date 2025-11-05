<?php

namespace App\Services;

use App\Models\Catalogs\Diagnosis;
use App\Models\Entities\User;
use App\Models\Relations\UserDiagnosis;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DiagnosisService
{

    /**
     * Get all diagnoses
     */
    public function getDiagnoses()
    {
        return Diagnosis::withCount('users')->orderBy('name', 'asc')->get();
    }

    /**
     * Validate diagnosis data
     */
    public function validateDiagnosisData(array $data, ?Diagnosis $diagnosis = null)
    {
        $rules = [
            'code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('diagnoses', 'code')->ignore($diagnosis?->id)
            ],
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'category' => [
                'required',
                'string',
                'max:255'
            ],
            'active' => [
                'boolean'
            ]
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Create a new diagnosis
     */
    public function createDiagnosis(array $data)
    {
        $validatedData = $this->validateDiagnosisData($data);
        return Diagnosis::create($validatedData);
    }

    /**
     * Update an existing diagnosis
     */
    public function updateDiagnosis(Diagnosis $diagnosis, array $data)
    {
        $validatedData = $this->validateDiagnosisData($data, $diagnosis);
        $diagnosis->update($validatedData);
        return $diagnosis;
    }

    /**
     * Delete a diagnosis
     */
    public function deleteDiagnosis(Diagnosis $diagnosis)
    {
        return $diagnosis->delete();
    }

    /**
     * Get trashed diagnoses
     */
    public function getTrashedDiagnoses()
    {
        return Diagnosis::onlyTrashed()
            ->withCount('users')
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Restore a trashed diagnosis
     */
    public function restoreDiagnosis($id)
    {
        $diagnosis = Diagnosis::onlyTrashed()->findOrFail($id);
        return $diagnosis->restore();
    }

    /**
     * Permanently delete a diagnosis
     */
    public function forceDeleteDiagnosis($id)
    {
        $diagnosis = Diagnosis::onlyTrashed()->findOrFail($id);
        return $diagnosis->forceDelete();
    }

    /**
     * Validate user diagnoses data
     */
    public function validateUserDiagnosesData(array $data)
    {
        $rules = [
            'diagnoses' => 'required|array',
            'diagnoses.*.id' => 'required|exists:diagnoses,id',
            'diagnoses.*.diagnosed_at' => 'nullable|date',
            'diagnoses.*.notes' => 'nullable|string|max:1000',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Update user diagnoses
     * Each row of $diagnosesData has: id, diagnosed_at, notes
     */
    public function updateUserDiagnoses(User $user, array $diagnosesData)
    {
        // Validate the data
        $validatedData = $this->validateUserDiagnosesData(['diagnoses' => $diagnosesData]);
        $diagnosesData = $validatedData['diagnoses'];

        //1) get current diagnoses (only active ones, not soft-deleted)
        $currentDiagnoses = UserDiagnosis::where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->pluck('diagnosis_id')
            ->toArray();
        
        //2) get new diagnoses IDs
        $newDiagnoses = array_map(function ($diagnosis) {
            return $diagnosis['id'];
        }, $diagnosesData);
        
        //3) get diagnoses to add
        $diagnosesToAdd = array_diff($newDiagnoses, $currentDiagnoses);
        
        //4) get diagnoses to remove
        $diagnosesToRemove = array_diff($currentDiagnoses, $newDiagnoses);
        
        //5) get diagnoses to update (exist in both lists)
        $diagnosesToUpdate = array_intersect($currentDiagnoses, $newDiagnoses);
        
        //6) add new diagnoses
        foreach ($diagnosesData as $diagnosis) {
            if (in_array($diagnosis['id'], $diagnosesToAdd)) {
                $user->diagnoses()->attach($diagnosis['id'], [
                    'diagnosed_at' => $diagnosis['diagnosed_at'] ?: null,
                    'notes' => $diagnosis['notes'] ?: null
                ]);
            }
        }
        
        //7) update existing diagnoses
        foreach ($diagnosesData as $diagnosis) {
            if (in_array($diagnosis['id'], $diagnosesToUpdate)) {
                UserDiagnosis::where('user_id', $user->id)
                    ->where('diagnosis_id', $diagnosis['id'])
                    ->whereNull('deleted_at')
                    ->update([
                        'diagnosed_at' => $diagnosis['diagnosed_at'] ?: null,
                        'notes' => $diagnosis['notes'] ?: null
                    ]);
            }
        }
        
        //8) soft delete removed diagnoses
        foreach ($diagnosesToRemove as $diagnosisId) {
            UserDiagnosis::where('user_id', $user->id)
                ->where('diagnosis_id', $diagnosisId)
                ->update(['deleted_at' => now()]);
        }
        
        return $user;
    }
}
