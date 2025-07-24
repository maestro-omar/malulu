<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\School\SchoolBaseController;
use Illuminate\Http\Request;
use App\Services\DashboardService;
use Diglactic\Breadcrumbs\Breadcrumbs;

class DashboardController extends SchoolBaseController
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
        parent::__construct();
    }

    public function dashboard(Request $request)
    {
        $data = $this->dashboardService->getData($request, $this->loggedUser());
        return $this->render($request, 'Dashboard', $data + ['breadcrumbs' => Breadcrumbs::generate('dashboard')]);
    }
}
