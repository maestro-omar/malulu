<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\School\SchoolBaseController;
use App\Models\Catalogs\Role;
use App\Models\Entities\School;
use App\Models\Entities\AcademicYear;
use App\Models\Relations\RoleRelationship;
use App\Services\AcademicEventService;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Http\Request;
use Inertia\Inertia;

class YearEventsController extends SchoolBaseController
{
    public function __construct(private AcademicEventService $academicEventService)
    {
    }

    /**
     * Show all year events (school, province, national) with responsibles for the selected academic year.
     */
    public function index(Request $request, School $school)
    {
        $academicYearId = $request->get('academic_year_id');

        if ($academicYearId) {
            $academicYear = AcademicYear::find($academicYearId);
        } else {
            $academicYear = AcademicYear::getCurrent() ?? AcademicYear::orderBy('year', 'desc')->first();
        }

        if (!$academicYear) {
            return redirect()->route('school.show', $school->slug)
                ->with('error', 'No hay ciclos lectivos disponibles.');
        }

        $data = $this->academicEventService->listYearEventsByScope($school, $academicYear->id);

        $workerRoleIds = Role::whereIn('code', Role::workersCodes())->pluck('id')->toArray();
        $workers = RoleRelationship::query()
            ->with('user')
            ->forSchool($school->id)
            ->forRoles($workerRoleIds)
            ->active()
            ->get()
            ->map(fn (RoleRelationship $rr) => [
                'id' => $rr->id,
                'user_id' => $rr->user_id,
                'short_name' => $rr->user ? $rr->user->short_name : '',
                'name' => $rr->user ? $rr->user->name : '',
                'firstname' => $rr->user ? $rr->user->firstname : '',
                'lastname' => $rr->user ? $rr->user->lastname : '',
            ])
            ->values()
            ->toArray();

        return Inertia::render('YearEvents/Index', [
            'school' => $school,
            'academicYear' => $academicYear,
            'academicYears' => AcademicYear::orderBy('year', 'desc')->get(),
            'events' => $data['events'],
            'workers' => $workers,
            'breadcrumbs' => Breadcrumbs::generate('school.year-events.index', $school),
        ]);
    }
}
