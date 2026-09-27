<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardQueryService;

class StatisticsController extends Controller
{
    public function index(
        DashboardQueryService $dashboard
    ) {
        $data = $dashboard->getDashboardData();

        return view(
            'pages.admin.statistics',
            $data
        );
    }
}
