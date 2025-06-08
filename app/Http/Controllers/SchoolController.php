<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolLevel;
use App\Models\SchoolManagementType;
use App\Models\SchoolShift;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $query = School::query()
            ->with(['locality', 'schoolLevels', 'managementType', 'shifts'])
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
            'schoolLevels' => SchoolLevel::orderBy('name')->get(),
            'managementTypes' => SchoolManagementType::orderBy('name')->get(),
            'shifts' => SchoolShift::orderBy('name')->get()
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('schools', 'name')
                ],
                'short' => 'required|string|max:50',
                'cue' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('schools', 'cue')
                ],
                'locality_id' => [
                    'required',
                    Rule::exists('localities', 'id')
                ],
                'management_type_id' => 'required|exists:school_management_types,id',
                'school_levels' => 'required|array',
                'school_levels.*' => 'exists:school_levels,id',
                'shifts' => 'nullable|array',
                'shifts.*' => 'exists:school_shifts,id',
                'address' => 'nullable|string|max:255',
                'zip_code' => 'nullable|string|max:20',
                'phone' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:255',
                'coordinates' => 'nullable|string|max:100',
                'social' => 'nullable|array'
            ]);

            Log::info('Store validation passed:', $validated);

            // Create the school first
            $school = School::create($validated);

            // Then sync the relationships
            if ($request->has('school_levels')) {
                $school->schoolLevels()->sync($request->school_levels);
            }
            
            if ($request->has('shifts')) {
                $school->shifts()->sync($request->shifts);
            }

            return redirect()->route('schools.index')
                ->with('success', 'School created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Store validation failed:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
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
        if ($school->name === 'GLOBAL') {
            abort(403, 'Cannot update GLOBAL school.');
        }

        // Ensure school is loaded
        $school = School::findOrFail($school->id);

        // Debug the incoming request
        Log::info('Update request data:', $request->all());
        Log::info('Current school:', [
            'id' => $school->id,
            'name' => $school->name,
            'cue' => $school->cue
        ]);

        try {
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('schools', 'name')->where(function ($query) use ($school) {
                        return $query->where('id', '!=', $school->id);
                    })
                ],
                'short' => 'required|string|max:50',
                'cue' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('schools', 'cue')->where(function ($query) use ($school) {
                        return $query->where('id', '!=', $school->id);
                    })
                ],
                'locality_id' => [
                    'required',
                    Rule::exists('localities', 'id')
                ],
                'management_type_id' => 'required|exists:school_management_types,id',
                'school_levels' => 'required|array',
                'school_levels.*' => 'exists:school_levels,id',
                'shifts' => 'nullable|array',
                'shifts.*' => 'exists:school_shifts,id',
                'address' => 'nullable|string|max:255',
                'zip_code' => 'nullable|string|max:20',
                'phone' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:255',
                'coordinates' => 'nullable|string|max:100',
                'social' => 'nullable|array'
            ]);

            Log::info('Validation passed:', $validated);

            // Update the school first
            $school->update($validated);

            // Then sync the relationships
            if ($request->has('school_levels')) {
                $school->schoolLevels()->sync($request->school_levels);
            }
            
            if ($request->has('shifts')) {
                $school->shifts()->sync($request->shifts);
            } else {
                $school->shifts()->detach();
            }

            return redirect()->route('schools.index')
                ->with('success', 'School updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed:', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
                'school_id' => $school->id,
                'school_cue' => $school->cue
            ]);
            throw $e;
        }
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

    public function show(School $school)
    {
        $school->load(['locality', 'schoolLevels', 'managementType', 'shifts']);
        
        // Ensure extra is properly decoded if it's a string
        if (is_string($school->extra)) {
            $school->extra = json_decode($school->extra, true);
        }

        return Inertia::render('Schools/Show', [
            'school' => $school
        ]);
    }
} 