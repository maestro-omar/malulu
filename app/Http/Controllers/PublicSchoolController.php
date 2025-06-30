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
use Illuminate\Support\Str;
use Diglactic\Breadcrumbs\Breadcrumbs;
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
        return $this->getFilteredSchools($request);
    }

    public function byProvince(Request $request, string $provinceCode)
    {
        return $this->getFilteredSchools($request, $provinceCode);
    }

    /**
     * Get filtered schools based on location parameters
     *
     * @param Request $request
     * @param string|null $provinceCode
     * @param int|null $districtId
     * @param int|null $localityId
     * @return \Inertia\Response
     */
    private function getFilteredSchools(Request $request, ?string $provinceCode = null, ?int $districtId = null, ?int $localityId = null)
    {
        // Set the filter parameters in the request for filtering
        if ($provinceCode) {
            $request->merge(['province_code' => $provinceCode]);
        }
        if ($districtId) {
            $request->merge(['district_id' => $districtId]);
        }
        if ($localityId) {
            $request->merge(['locality_id' => $localityId]);
        }

        // Use advanced filters when district filtering is needed, regular filters for others
        $schools = ($districtId || $request->input('district_id'))
            ? $this->schoolService->getSchoolsWithAdvancedFilters($request)
            : $this->schoolService->getSchools($request);

        // Generate title suffix based on filter parameters
        $titleSuffix = $this->generateTitleSuffix($provinceCode, $districtId, $localityId);

        // Conditionally load location data based on filter parameters
        $locationData = $this->getLocationData($provinceCode, $districtId, $localityId);

        $breadcrumbs = [];
        if ($localityId ?? $request->locality_id) {
            $breadcrumbs = Breadcrumbs::generate('public-schools.byLocality', $localityId ?? $request->locality_id)->toArray();
        } elseif ($districtId ?? $request->district_id) {
            $breadcrumbs = Breadcrumbs::generate('public-schools.byDistrict', $districtId ?? $request->district_id)->toArray();
        } elseif ($provinceCode ?? $request->province_code) {
            $breadcrumbs = Breadcrumbs::generate('public-schools.byProvince', $provinceCode ?? $request->province_code)->toArray();
        } else {
            $breadcrumbs = Breadcrumbs::generate('public-schools.index')->toArray();
        }

        return Inertia::render('Schools/Public/Index', [
            'schools' => $schools,
            'filters' => [
                'search' => $request->search,
                'province_code' => $provinceCode ?? $request->province_code,
                'district_id' => $districtId ?? $request->district_id,
                'locality_id' => $localityId ?? $request->locality_id,
            ],
            'selectedProvince' => $provinceCode ?? $request->province_code,
            'selectedDistrict' => $districtId ?? $request->district_id,
            'selectedLocality' => $localityId ?? $request->locality_id,
            'titleSuffix' => $titleSuffix,
            ...$locationData,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Get location data based on filter parameters
     *
     * @param string|null $provinceCode
     * @param int|null $districtId
     * @param int|null $localityId
     * @return array
     */
    private function getLocationData(?string $provinceCode = null, ?int $districtId = null, ?int $localityId = null): array
    {
        // Get the actual district_id from the request
        $requestDistrictId = request()->input('district_id');
        $actualDistrictId = $districtId ?? $requestDistrictId;

        // If localityId is provided, don't load any location data
        if ($localityId) {
            return [
                'localities' => [],
                'districts' => [],
                'provinces' => [],
            ];
        }

        // If districtId is provided (either as parameter or in request), load only localities for that district
        if ($actualDistrictId) {
            // Get the selected district to include in the districts array
            $selectedDistrict = District::find($actualDistrictId);

            return [
                'localities' => Locality::where('district_id', $actualDistrictId)->orderBy('order')->get(),
                'districts' => $selectedDistrict ? [$selectedDistrict] : [],
                'provinces' => [],
            ];
        }

        // If provinceCode is provided, load districts and localities for that province
        if ($provinceCode) {
            $province = Province::where('code', $provinceCode)->first();
            if ($province) {
                return [
                    'localities' => Locality::whereHas('district', function ($query) use ($province) {
                        $query->where('province_id', $province->id);
                    })->orderBy('order')->get(),
                    'districts' => District::where('province_id', $province->id)->orderBy('order')->get(),
                    'provinces' => [],
                ];
            }
        }

        // Default: load all location data
        return [
            'localities' => Locality::with('district.province')->orderBy('order')->get(),
            'districts' => District::with('province')->orderBy('order')->get(),
            'provinces' => Province::orderBy('order')->get(),
        ];
    }

    /**
     * Generate title suffix based on location filter parameters
     *
     * @param string|null $provinceCode
     * @param int|null $districtId
     * @param int|null $localityId
     * @return string|null
     */
    private function generateTitleSuffix(?string $provinceCode = null, ?int $districtId = null, ?int $localityId = null): ?string
    {
        // Get the actual district_id from the request
        $requestDistrictId = request()->input('district_id');
        $requestLocalityId = request()->input('locality_id');
        $actualDistrictId = $districtId ?? $requestDistrictId;
        $actualLocalityId = $localityId ?? $requestLocalityId;

        if ($actualLocalityId) {
            $locality = Locality::with('district.province')->find($actualLocalityId);
            if ($locality) {
                return "de la localidad de {$locality->name}";
            }
        }

        if ($actualDistrictId) {
            $district = District::with('province')->find($actualDistrictId);
            if ($district) {
                return "del departamento de {$district->long}";
            }
        }

        if ($provinceCode) {
            $province = Province::where('code', $provinceCode)->first();
            if ($province) {
                return "de la provincia {$province->name}";
            }
        }

        return null;
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
