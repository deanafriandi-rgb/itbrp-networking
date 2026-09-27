<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use App\Services\CacheAnalysisService;

class CacheController extends Controller
{
    /**
     * Halaman monitoring cache Squid (read-only).
     */
    public function index(
        CacheAnalysisService $cache
    ) {
        $data =
            $cache
            ->getCacheData();

        return view(
            'pages.monitoring.cache',
            $data
        );
    }
}
