<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use App\Services\CacheAnalysisService;
use App\Services\SystemHealthService;

class SystemStatusController extends Controller
{
    /**
     * Halaman monitoring status sistem (read-only).
     */
    public function index(
        SystemHealthService $health,
        CacheAnalysisService $cache
    ) {
        $data =
            $health
            ->getStatusData();

        $cacheData =
            $cache
            ->getCacheData();

        $data['cacheSummary'] =
            $cacheData['summary']
            ?? [];

        $data['cachePeriod'] =
            $cacheData['period']
            ?? [];

        return view(
            'pages.monitoring.status',
            $data
        );
    }
}
