<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CacheAnalysisService;

class CacheController extends Controller
{
    public function index(
        CacheAnalysisService $cache
    ) {
        $data = $cache->getCacheData();

        return view(
            'pages.admin.cache',
            $data
        );
    }
}
