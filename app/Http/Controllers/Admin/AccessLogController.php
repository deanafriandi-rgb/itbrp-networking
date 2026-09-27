<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use App\Services\AccessMonitoringService;
use Illuminate\Http\Request;

class AccessLogController extends Controller
{
    public function index(
        Request $request,
        AccessMonitoringService $monitoring
    ) {
        $monitoringData = $monitoring->getData();

        $query = AccessLog::query();

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'client_ip',
                    'like',
                    "%{$search}%"
                )
                    ->orWhere(
                        'domain',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'url',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        if ($request->filled('category')) {
            $query->where(
                'category',
                $request->category
            );
        }

        if ($request->filled('protocol')) {
            $query->where(
                'protocol',
                strtoupper($request->protocol)
            );
        }

        $logs = $query
            ->latest('logged_at')
            ->paginate(25)
            ->withQueryString();

        return view(
            'pages.admin.access',
            array_merge(
                $monitoringData,
                compact('logs')
            )
        );
    }
}
