<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\System\SystemBaseController;
use App\Services\UserService;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class CalendarAdminController extends SystemBaseController
{
    public function __construct(private UserService $userService)
    {
        // No specific permission required - all authenticated users can view calendar
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get month and year from request, default to current month/year
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);
        
        // Validate month and year
        $month = max(1, min(12, (int)$month));
        $year = max(2000, min(2100, (int)$year));
        
        // Get calendar data for the specified month/year
        $calendarData = $this->userService->getCalendarDataForMonth($user, $month, $year);
        
        return Inertia::render('Calendar/Index', [
            'calendarData' => $calendarData,
            'currentMonth' => $month,
            'currentYear' => $year,
            'breadcrumbs' => Breadcrumbs::generate('calendar.index'),
        ]);
    }
}

