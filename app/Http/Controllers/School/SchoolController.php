<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\School\SchoolBaseController;
use App\Models\Entities\School;
use App\Models\Catalogs\Locality;
use App\Models\Catalogs\SchoolLevel;
use App\Models\Catalogs\SchoolManagementType;
use App\Models\Catalogs\SchoolShift;
use App\Services\SchoolService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Diglactic\Breadcrumbs\Breadcrumbs;

class SchoolController extends SchoolBaseController
{
    protected $schoolService;

    public function __construct(SchoolService $schoolService)
    {
        $this->schoolService = $schoolService;
    }

    public function index(Request $request)
    {
        $filters = $request->all();
        return $this->render($request, 'Schools/Index', [
            'schools' => $this->schoolService->getSchools($request),
            'localities' => Locality::orderBy('order')->get(),
            'filters' => $filters,
            'breadcrumbs' => Breadcrumbs::generate('schools.index'),
        ]);
    }

    public function create()
    {
        return $this->render(null, 'Schools/Create', [
            'breadcrumbs' => Breadcrumbs::generate('schools.create'),
            'localities' => Locality::orderBy('order')->get(),
            'schoolLevels' => SchoolLevel::orderBy('id')->get(),
            'managementTypes' => SchoolManagementType::orderBy('id')->get(),
            'shifts' => SchoolShift::orderBy('id')->get()
        ]);
    }

    public function store(Request $request)
    {
        try {
            $school = $this->schoolService->createSchool($request->all());

            return redirect()->route('school.index')
                ->with('success', 'School created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors($e->errors() ?? ['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function edit(Request $request, School $school)
    {
        if ($school->name === School::GLOBAL) {
            return Inertia::render('Errors/403', ['message' => 'No se puede editar escuela GLOBAL'])
                ->toResponse($request)
                ->setStatusCode(403);
        }

        return $this->render($request, 'Schools/Edit', [
            'school' => $school->load(['locality', 'schoolLevels', 'managementType', 'shifts']),
            'breadcrumbs' => Breadcrumbs::generate('school.edit', $school),
            'localities' => Locality::orderBy('order')->get(),
            'schoolLevels' => SchoolLevel::orderBy('id')->get(),
            'managementTypes' => SchoolManagementType::orderBy('id')->get(),
            'shifts' => SchoolShift::orderBy('id')->get()
        ]);
    }

    public function update(Request $request, School $school)
    {
        try {
            $this->schoolService->updateSchool($school, $request->all());

            return redirect()->route('school.index')
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

            return redirect()->route('school.index')
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

        return $this->render(null, 'Schools/Show', [
            'school' => $school,
            'breadcrumbs' => Breadcrumbs::generate('school.show', $school),
        ]);
    }

    public function trashed()
    {
        $schools = $this->schoolService->getTrashedSchools();

        return $this->render(null, 'Schools/Trashed', [
            'schools' => $schools
        ]);
    }

    public function restore($id)
    {
        try {
            $this->schoolService->restoreSchool($id);

            return redirect()->route('school.trashed')
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

            return redirect()->route('school.trashed')
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
