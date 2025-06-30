<?php

namespace App\Http\Controllers;

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
// use Diglactic\Breadcrumbs\Breadcrumbs;
use App\Http\Controllers\Controller;

class PublicSchoolController extends Controller
{
    protected $schoolService;

    public function __construct(SchoolService $schoolService)
    {
        $this->schoolService = $schoolService;
    }

    public function index(Request $request)
    {
        return Inertia::render('Schools/Public/Index', [
            'schools' => $this->schoolService->getSchools($request),
            'localities' => Locality::orderBy('order')->get(),
            'search' => $request->search,
        ]);
    }

    public function byProvince(Request $request)
    {
        return Inertia::render('Schools/Public/Index', [
            'schools' => $this->schoolService->getSchools($request),
            'localities' => Locality::orderBy('order')->get(),
            'search' => $request->search,
        ]);
    }

    public function show(School $school)
    {
        // $school->load(['locality', 'schoolLevels', 'managementType', 'shifts']);

        // if (is_string($school->extra)) {
        //     $school->extra = json_decode($school->extra, true);
        // }

        return Inertia::render('Schools/Public/Show', [
            'school' => $school,
        ]);
    }
}
