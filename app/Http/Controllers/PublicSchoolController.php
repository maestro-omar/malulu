<?php

namespace App\Http\Controllers;

use App\Models\Entities\School;
use App\Models\Catalogs\Locality;
use App\Models\Catalogs\District;
use App\Models\Catalogs\Province;
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
            'localities' => Locality::with('district.province')->orderBy('order')->get(),
            'districts' => District::with('province')->orderBy('order')->get(),
            'provinces' => Province::orderBy('order')->get(),
            'search' => $request->search,
            'selectedProvince' => $request->province_code,
            'selectedDistrict' => $request->district_id,
            'selectedLocality' => $request->locality_id,
        ]);
    }

    public function byProvince(Request $request, string $provinceCode)
    {
        // Set the province code in the request for filtering
        $request->merge(['province_code' => $provinceCode]);
        
        return Inertia::render('Schools/Public/Index', [
            'schools' => $this->schoolService->getSchools($request),
            'localities' => Locality::with('district.province')->orderBy('order')->get(),
            'districts' => District::with('province')->orderBy('order')->get(),
            'provinces' => Province::orderBy('order')->get(),
            'search' => $request->search,
            'selectedProvince' => $provinceCode,
            'selectedDistrict' => $request->district_id,
            'selectedLocality' => $request->locality_id,
        ]);
    }

    public function byDistrict(Request $request, int $districtId)
    {
        // Set the district ID in the request for filtering
        $request->merge(['district_id' => $districtId]);
        
        return Inertia::render('Schools/Public/Index', [
            'schools' => $this->schoolService->getSchoolsWithAdvancedFilters($request),
            'localities' => Locality::with('district.province')->orderBy('order')->get(),
            'districts' => District::with('province')->orderBy('order')->get(),
            'provinces' => Province::orderBy('order')->get(),
            'search' => $request->search,
            'selectedProvince' => $request->province_code,
            'selectedDistrict' => $districtId,
            'selectedLocality' => $request->locality_id,
        ]);
    }

    public function show(School $school)
    {
        $school->load(['locality', 'locality.district', 'locality.district.province', 'schoolLevels', 'managementType', 'shifts']);

        if (is_string($school->extra)) {
            $school->extra = json_decode($school->extra, true);
        }

        return Inertia::render('Schools/Public/Show', [
            'school' => $school,
        ]);
    }
}
