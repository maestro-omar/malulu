<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $query = School::query()
            ->with(['locality', 'schoolLevels'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('short', 'like', "%{$search}%")
                        ->orWhere('key', 'like', "%{$search}%")
                        ->orWhereHas('locality', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('schoolLevels', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->input('locality_id'), function ($query, $localityId) {
                Log::info('Filtering by locality_id: ' . $localityId);
                $query->where('locality_id', $localityId);
            })
            ->where('name', '!=', 'GLOBAL');

        $schools = $query->orderBy('name')->paginate(10);

        Log::info('SQL Query: ' . $query->toSql());
        Log::info('Query Bindings: ' . json_encode($query->getBindings()));

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
            'schoolLevels' => \App\Models\SchoolLevel::orderBy('name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:schools',
            'short' => 'required|string|max:50',
            'cue' => 'required|string|max:50|unique:schools',
            'locality_id' => 'required|exists:localities,id',
            'school_levels' => 'required|array',
            'school_levels.*' => 'exists:school_levels,id'
        ]);

        $school = School::create($validated);
        $school->schoolLevels()->sync($request->school_levels);

        return redirect()->route('schools.index')
            ->with('success', 'School created successfully.');
    }

    public function edit(School $school)
    {
        if ($school->name === 'GLOBAL') {
            abort(403, 'Cannot edit GLOBAL school.');
        }

        return Inertia::render('Schools/Edit', [
            'school' => $school->load(['locality', 'schoolLevels']),
            'localities' => \App\Models\Locality::orderBy('order')->get(),
            'schoolLevels' => \App\Models\SchoolLevel::orderBy('name')->get()
        ]);
    }

    public function update(Request $request, School $school)
    {
        if ($school->name === 'GLOBAL') {
            abort(403, 'Cannot update GLOBAL school.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:schools,name,' . $school->id,
            'short' => 'required|string|max:50',
            'cue' => 'required|string|max:50|unique:schools,cue,' . $school->id,
            'locality_id' => 'required|exists:localities,id',
            'school_levels' => 'required|array',
            'school_levels.*' => 'exists:school_levels,id'
        ]);

        $school->update($validated);
        $school->schoolLevels()->sync($request->school_levels);

        return redirect()->route('schools.index')
            ->with('success', 'School updated successfully.');
    }

    public function destroy(School $school)
    {
        if ($school->name === 'GLOBAL') {
            abort(403, 'Cannot delete GLOBAL school.');
        }

        $school->delete();

        return redirect()->route('schools.index')
            ->with('success', 'School deleted successfully.');
    }
} 