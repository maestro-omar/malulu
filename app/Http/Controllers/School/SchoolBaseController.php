<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\Entities\User;
use App\Models\Entities\School;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\UserContextService;

class SchoolBaseController extends Controller
{

    public ?User $loggedUser = null;
    public ?array $userSchools = null;
    public $activeSchool = null;

    public function __construct() {}

    public function render(?Request $request, string $view, array $data) //: \Inertia\Response
    {
        // dd(UserContextService::activeSchool(),'asqfwqwr');

        $basicData = $this->basicData();
        return Inertia::render(
            $view,
            $basicData + $data
        );
    }

    public function loggedUser(): ?User
    {
        if (empty($this->loggedUser))
            $this->loggedUser = auth()->user();
        return $this->loggedUser;
    }

    public function basicData(): array
    {
        return [
            'userExtraData' =>
            [
                'roleRelationships' => $this->userActiveRoleRelationships()->toArray(),
                'relatedSchools' => $this->userActiveSchools()
            ]
        ];
    }

    public function userActiveRoleRelationships()
    {
        static $value;
        if (empty($value)) {
            $value = $this->loggedUser()->activeRoleRelationships()->map(function ($rel) {
                return [
                    'role_id'        => $rel->role_id,
                    'school_id'      => $rel->school_id,
                    'school_level_id' => $rel->school_level_id,
                    'custom_fields'  => $rel->custom_fields,
                    'start_date'     => $rel->start_date,
                ];
            });
        }
        return $value;
    }
    private function userActiveSchools(): array
    {
        if (is_null($this->userSchools)) {
            $rolesRel = $this->userActiveRoleRelationships();
            $schoolIds = empty($rolesRel) ? [] : $rolesRel->pluck('school_id')->unique()->toArray();
            $this->userSchools = empty($schoolIds) ? [] : School::select('cue', 'id', 'name', 'short', 'slug', 'logo', 'extra')->find($schoolIds)->keyBy('id')->toArray();
        }

        return $this->userSchools;
    }
}
