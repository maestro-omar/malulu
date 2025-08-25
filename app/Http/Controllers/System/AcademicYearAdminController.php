<?php

namespace App\Http\Controllers\System;

use App\Models\Entities\AcademicYear;
use App\Services\AcademicYearService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\System\SystemBaseController;
use Diglactic\Breadcrumbs\Breadcrumbs;

class AcademicYearAdminController extends SystemBaseController
{
    protected $academicYearService;

    public function __construct(AcademicYearService $academicYearService)
    {
        $this->academicYearService = $academicYearService;
        $this->middleware('permission:superadmin');
    }

    public function index()
    {
        $academicYears = $this->academicYearService->getAcademicYears();
        return Inertia::render('AcademicYears/Index', [
            'academicYears' => $academicYears,
            'breadcrumbs' => Breadcrumbs::generate('academic-years.index'),
        ]);
    }

    public function create()
    {
        return Inertia::render('AcademicYears/Create', [
            'breadcrumbs' => Breadcrumbs::generate('academic-years.create'),
        ]);
    }

    public function store(Request $request)
    {
        try {
            $this->academicYearService->createAcademicYear($request->all());
            return redirect()->route('academic-years.index')
                ->with('success', 'Año académico creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors($e->errors() ?? ['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function edit(AcademicYear $academicYear)
    {
        return Inertia::render('AcademicYears/Edit', [
            'academicYear' => $academicYear,
            'breadcrumbs' => Breadcrumbs::generate('academic-years.edit', $academicYear),
        ]);
    }

    public function show(AcademicYear $academicYear)
    {
        return Inertia::render('AcademicYears/Show', [
            'academicYear' => $academicYear,
            'breadcrumbs' => Breadcrumbs::generate('academic-years.show', $academicYear),
        ]);
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        try {
            $this->academicYearService->updateAcademicYear($academicYear, $request->all());
            return redirect()->route('academic-years.index')
                ->with('success', 'Año académico actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors($e->errors() ?? ['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(AcademicYear $academicYear)
    {
        try {
            $this->academicYearService->deleteAcademicYear($academicYear);
            return redirect()->route('academic-years.index')
                ->with('success', 'Año académico eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function trashed()
    {
        $academicYears = $this->academicYearService->getTrashedAcademicYears();
        return Inertia::render('AcademicYears/Trashed', [
            'academicYears' => $academicYears
        ]);
    }

    public function restore($id)
    {
        try {
            $this->academicYearService->restoreAcademicYear($id);
            return redirect()->route('academic-years.trashed')
                ->with('success', 'Año académico restaurado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function forceDelete($id)
    {
        try {
            $this->academicYearService->forceDeleteAcademicYear($id);
            return redirect()->route('academic-years.trashed')
                ->with('success', 'Año académico eliminado permanentemente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
