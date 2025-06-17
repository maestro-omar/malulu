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
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            'schoolLevels' => SchoolLevel::orderBy('id')->get(),
            'managementTypes' => SchoolManagementType::orderBy('id')->get(),
            'shifts' => SchoolShift::orderBy('id')->get()
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
        if ($school->name === School::GLOBAL) {
            abort(403, 'Cannot edit GLOBAL school.');
        }

        return Inertia::render('Schools/Edit', [
            'school' => $school->load(['locality', 'schoolLevels', 'managementType', 'shifts']),
            'localities' => \App\Models\Locality::orderBy('order')->get(),
            'schoolLevels' => SchoolLevel::orderBy('id')->get(),
            'managementTypes' => SchoolManagementType::orderBy('id')->get(),
            'shifts' => SchoolShift::orderBy('id')->get()
        ]);
    }

    public function update(Request $request, School $school)
    {
        try {
            $this->schoolService->updateSchool($school, $request->all());

            return redirect()->route('schools.index')
                ->with('success', 'School updated successfully.');
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

    public function uploadImage(Request $request, School $school)
    {
        try {
            $request->validate([
                'image' => 'required|image|max:2048', // Max 2MB
                'type' => 'required|in:logo,picture'
            ]);

            $image = $request->file('image');
            $type = $request->input('type');

            // Delete old image if exists
            if ($type === 'logo' && $school->logo) {
                $oldPath = str_replace('/storage/', '', $school->logo);
                Storage::disk('public')->delete($oldPath);
            } elseif ($type === 'picture' && $school->picture) {
                $oldPath = str_replace('/storage/', '', $school->picture);
                Storage::disk('public')->delete($oldPath);
            }

            // Generate timestamp and slugged filename
            $timestamp = now()->format('YmdHis');
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            $sluggedName = Str::slug($originalName);
            $newFilename = $timestamp . '_' . $sluggedName . '.' . $extension;

            // Store new image with custom filename
            $path = $image->storeAs('schools/' . $school->id, $newFilename, 'public');
            
            // Get the full URL for the stored image using the asset helper
            $url = asset('storage/' . $path);

            // Update school with new image path
            $school->update([
                $type => $url
            ]);

            return back()->with('success', 'Image uploaded successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function deleteImage(Request $request, School $school)
    {
        try {
            $request->validate([
                'type' => 'required|in:logo,picture'
            ]);

            $type = $request->input('type');

            // Delete the image file if it exists
            if ($type === 'logo' && $school->logo) {
                $oldPath = str_replace('/storage/', '', $school->logo);
                Storage::disk('public')->delete($oldPath);
                $school->update(['logo' => null]);
            } elseif ($type === 'picture' && $school->picture) {
                $oldPath = str_replace('/storage/', '', $school->picture);
                Storage::disk('public')->delete($oldPath);
                $school->update(['picture' => null]);
            }

            return back()->with('success', 'Image deleted successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}