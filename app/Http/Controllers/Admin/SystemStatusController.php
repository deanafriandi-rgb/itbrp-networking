<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemHealthService;

class SystemStatusController extends Controller
{
    /**
     * Halaman status sistem.
     */
    public function index(
        SystemHealthService $health
    ) {
        $data =
            $health
                ->getStatusData();


        return view(
            'pages.admin.status',
            $data
        );
    }
}