<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /** @var DashboardService */
    private DashboardService $dashboardService;

    /**
     * @param  DashboardService $dashboardService the service handling dashboard data assembly
     */
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display the dashboard with financial summary and charts.
     *
     * @param  Request $request the incoming request
     * @return Response
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Dashboard',
            $this->dashboardService->getDashboardData(
                $request->user(),
                Carbon::now()
            )
        );
    }
}