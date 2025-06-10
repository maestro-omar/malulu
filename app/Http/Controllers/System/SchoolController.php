<?php

namespace App\Http\Controllers\System;

use App\Models\School;
use App\Models\SchoolLevel;
use App\Models\SchoolManagementType;
use App\Models\SchoolShift;
use App\Services\SchoolService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SchoolController extends SystemBaseController
{
    protected $schoolService;

    public function __construct(SchoolService $schoolService)
    {
        $this->schoolService = $schoolService;
        $this->middleware('permission:superadmin');
    }

    public function index(Request $request)
    {
        $schools = $this->schoolService->getSchools($request);

        return Inertia::render('Schools/Index', [
            'schools' => $schools,
            'filters' => $request->only(['search', 'locality_id']),
            'localities' => \App\Models\Locality::orderBy('order')->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Schools/Create', [
            'localities' => \App\Models\Locality::orderBy('order')->get(),
            'schoolLevels' => SchoolLevel::orderBy('name')->get(),
            'managementTypes' => SchoolManagementType::orderBy('name')->get(),
            'shifts' => SchoolShift::orderBy('name')->get()
        ]);
    }

    public function store(Request $request)
    {
        try {
            $school = $this->schoolService->createSchool($request->all());

            return redirect()->route('schools.index')
                ->with('success', 'School created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors($e->errors() ?? ['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function edit(School $school)
    {
        if ($school->name === 'GLOBAL') {
            abort(403, 'Cannot edit GLOBAL school.');
        }

        return Inertia::render('Schools/Edit', [
            'school' => $school->load(['locality', 'schoolLevels', 'managementType', 'shifts']),
            'localities' => \App\Models\Locality::orderBy('order')->get(),
            'schoolLevels' => SchoolLevel::orderBy('name')->get(),
            'managementTypes' => SchoolManagementType::orderBy('name')->get(),
            'shifts' => SchoolShift::orderBy('name')->get()
        ]);
    }

    public function update(Request $request, School $school)
    {
        try {
            // Let's add some debugging to verify the school is being passed correctly
            \Log::info('Controller updating school', [
                'school_id' => $school->id,
                'school_name' => $school->name
            ]);

            $this->schoolService->updateSchool($school, $request->all());

            return redirect()->route('schools.index')
                ->with('success', 'School updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors($e->errors() ?? ['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(School $school)
    {
        try {
            $this->schoolService->deleteSchool($school);

            return redirect()->route('schools.index')
                ->with('success', 'School deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(School $school)
    {
        $school->load(['locality', 'schoolLevels', 'managementType', 'shifts']);

        if (is_string($school->extra)) {
            $school->extra = json_decode($school->extra, true);
        }

        return Inertia::render('Schools/Show', [
            'school' => $school
        ]);
    }

    public function trashed()
    {
        $schools = $this->schoolService->getTrashedSchools();

        return Inertia::render('Schools/Trashed', [
            'schools' => $schools
        ]);
    }

    public function restore($id)
    {
        try {
            $this->schoolService->restoreSchool($id);

            return redirect()->route('schools.trashed')
                ->with('success', 'School restored successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function forceDelete($id)
    {
        try {
            $this->schoolService->forceDeleteSchool($id);

            return redirect()->route('schools.trashed')
                ->with('success', 'School permanently deleted.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}