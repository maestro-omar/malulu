<?php

namespace App\Http\Controllers\System;

use App\Models\Catalogs\Diagnosis;
use App\Services\DiagnosisService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\System\SystemBaseController;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Validation\ValidationException;

class DiagnosisAdminController extends SystemBaseController
{
    protected $diagnosisService;

    public function __construct(DiagnosisService $diagnosisService)
    {
        $this->diagnosisService = $diagnosisService;
        $this->middleware('permission:superadmin');
    }

    public function index()
    {
        $diagnoses = $this->diagnosisService->getDiagnoses();
        return Inertia::render('Diagnoses/Index', [
            'diagnoses' => $diagnoses,
            'breadcrumbs' => Breadcrumbs::generate('diagnoses.index'),
            'categories' => Diagnosis::categories(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Diagnoses/Create', [
            'breadcrumbs' => Breadcrumbs::generate('diagnoses.create'),
            'categories' => Diagnosis::categories(),
        ]);
    }

    public function store(Request $request)
    {
        try {
            $this->diagnosisService->createDiagnosis($request->all());
            return redirect()->route('diagnoses.index')
                ->with('success', 'Diagnóstico creado exitosamente.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function edit(Diagnosis $diagnosis)
    {
        return Inertia::render('Diagnoses/Edit', [
            'diagnosis' => $diagnosis,
            'breadcrumbs' => Breadcrumbs::generate('diagnoses.edit', $diagnosis),
            'categories' => Diagnosis::categories(),
        ]);
    }

    public function show(Diagnosis $diagnosis)
    {
        $diagnosis->load(['users' => function ($query) {
            $query->whereNull('user_diagnosis.deleted_at')
                  ->withPivot(['diagnosed_at', 'notes']);
        }]);
        
        return Inertia::render('Diagnoses/Show', [
            'diagnosis' => $diagnosis,
            'breadcrumbs' => Breadcrumbs::generate('diagnoses.show', $diagnosis),
            'categories' => Diagnosis::categories(),
        ]);
    }

    public function update(Request $request, Diagnosis $diagnosis)
    {
        try {
            $this->diagnosisService->updateDiagnosis($diagnosis, $request->all());
            return redirect()->route('diagnoses.index')
                ->with('success', 'Diagnóstico actualizado exitosamente.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(Diagnosis $diagnosis)
    {
        try {
            $this->diagnosisService->deleteDiagnosis($diagnosis);
            return redirect()->route('diagnoses.index')
                ->with('success', 'Diagnóstico eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function trashed()
    {
        $diagnoses = $this->diagnosisService->getTrashedDiagnoses();
        return Inertia::render('Diagnoses/Trashed', [
            'diagnoses' => $diagnoses,
            'categories' => Diagnosis::categories(),
        ]);
    }

    public function restore($id)
    {
        try {
            $this->diagnosisService->restoreDiagnosis($id);
            return redirect()->route('diagnoses.trashed')
                ->with('success', 'Diagnóstico restaurado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function forceDelete($id)
    {
        try {
            $this->diagnosisService->forceDeleteDiagnosis($id);
            return redirect()->route('diagnoses.trashed')
                ->with('success', 'Diagnóstico eliminado permanentemente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}