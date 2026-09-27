<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use App\Services\DashboardQueryService;

class AccessController extends Controller
{
    /**
     * Halaman monitoring akses jaringan (read-only).
     */
    public function index(
        DashboardQueryService $dashboard
    ) {
        $data =
            $dashboard
            ->getDashboardData();

        return view(
            'pages.monitoring.access',
            $data
        );
    }
}
