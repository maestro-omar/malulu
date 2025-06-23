<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\School\SchoolBaseController;
use Illuminate\Http\Request;
use App\Services\DashboardService;
use Inertia\Inertia;
use Diglactic\Breadcrumbs\Breadcrumbs;

class DashboardController extends SchoolBaseController
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function dashboard(Request $request)
    {
        $loggedUser = auth()->user();
        $data = $this->dashboardService->getData($request, $loggedUser);
        return Inertia::render(
            'Dashboard',
            $data + ['breadcrumbs' => Breadcrumbs::generate('dashboard')],
        );
    }
}
