<?php

namespace App\Services;

use App\Models\Catalogs\Diagnosis;
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
}
