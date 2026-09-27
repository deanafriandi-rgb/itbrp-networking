<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use App\Services\DashboardQueryService;

class StatisticsController extends Controller
{
    /**
     * Halaman statistik monitoring (read-only).
     */
    public function index(
        DashboardQueryService $dashboard
    ) {
        $data =
            $dashboard
                ->getDashboardData();

        return view(
            'pages.monitoring.statistics',
            $data
        );
    }
}
