<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\School\SchoolBaseController;
use App\Models\Entities\SchoolPage;
use App\Models\Entities\School;
use App\Services\SchoolPageService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Diglactic\Breadcrumbs\Breadcrumbs;

class SchoolPageController extends SchoolBaseController
{
    protected $schoolPageService;

    public function __construct(SchoolPageService $schoolPageService)
    {
        $this->schoolPageService = $schoolPageService;
    }

    public function index(Request $request, School $school)
    {
        $schoolPages = $this->schoolPageService->getSchoolPages($request, $school->id);

        return Inertia::render('SchoolPages/Index', [
            'schoolPages' => $schoolPages,
            'school' => $school,
            'search' => $request->search,
            'active' => $request->active,
            'breadcrumbs' => Breadcrumbs::generate('school-pages.index', $school),
        ]);
    }

    public function create(School $school)
    {
        return Inertia::render('SchoolPages/Create', [
            'school' => $school,
            'breadcrumbs' => Breadcrumbs::generate('school-pages.create', $school),
        ]);
    }

    public function store(Request $request, School $school)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
                'html_content' => 'required|string',
                'active' => 'boolean',
            ]);

            // If slug is not provided, generate it from title
            if (empty($data['slug'])) {
                $data['slug'] = $this->schoolPageService->generateUniqueSlug($data['title'], $school->id);
            }

            $data['school_id'] = $school->id;

            $this->schoolPageService->createSchoolPage($data, Auth::id());

            return redirect()->route('school-pages.index', $school)
                ->with('success', 'School page created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function show(School $school, SchoolPage $schoolPage)
    {
        // Ensure the page belongs to the school
        if ($schoolPage->school_id !== $school->id) {
            abort(404);
        }

        $schoolPage->load(['school', 'creator']);

        return Inertia::render('SchoolPages/Show', [
            'schoolPage' => $schoolPage,
            'school' => $school,
            'breadcrumbs' => Breadcrumbs::generate('school-pages.show', $school, $schoolPage),
        ]);
    }

    public function edit(School $school, SchoolPage $schoolPage)
    {
        // Ensure the page belongs to the school
        if ($schoolPage->school_id !== $school->id) {
            abort(404);
        }

        return Inertia::render('SchoolPages/Edit', [
            'schoolPage' => $schoolPage,
            'school' => $school,
            'breadcrumbs' => Breadcrumbs::generate('school-pages.edit', $school, $schoolPage),
        ]);
    }

    public function update(Request $request, School $school, SchoolPage $schoolPage)
    {
        // Ensure the page belongs to the school
        if ($schoolPage->school_id !== $school->id) {
            abort(404);
        }

        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
                'html_content' => 'required|string',
                'active' => 'boolean',
            ]);

            // If slug is not provided, generate it from title
            if (empty($data['slug'])) {
                $data['slug'] = $this->schoolPageService->generateUniqueSlug($data['title'], $school->id, $schoolPage);
            }

            $data['school_id'] = $school->id;

            $this->schoolPageService->updateSchoolPage($schoolPage, $data);

            return redirect()->route('school-pages.index', $school)
                ->with('success', 'School page updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(School $school, SchoolPage $schoolPage)
    {
        // Ensure the page belongs to the school
        if ($schoolPage->school_id !== $school->id) {
            abort(404);
        }

        try {
            $this->schoolPageService->deleteSchoolPage($schoolPage);

            return redirect()->route('school-pages.index', $school)
                ->with('success', 'School page deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
} 