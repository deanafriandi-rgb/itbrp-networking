<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Services\DashboardQueryService;
use App\Services\SystemHealthService;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    /**
     * Beranda monitoring publik / read-only.
     */
    public function index(
        DashboardQueryService $dashboard,
        SystemHealthService $health
    ) {
        /*
        |--------------------------------------------------------------------------
        | Data monitoring 24 jam
        |--------------------------------------------------------------------------
        |
        | Reuse service dashboard yang sama dengan Admin/Statistik supaya
        | angka publik dan admin tetap konsisten.
        |
        */

        $data = $dashboard->getDashboardData();


        /*
        |--------------------------------------------------------------------------
        | Status sistem
        |--------------------------------------------------------------------------
        */

        $healthData = $health->getStatusData();

        $healthSummary =
            $healthData['summary']
            ?? [
                'total' => 0,
                'online' => 0,
                'warning' => 0,
                'offline' => 0,
                'local' => 0,
            ];


        /*
        |--------------------------------------------------------------------------
        | Label status untuk tampilan monitoring
        |--------------------------------------------------------------------------
        */

        if (
            (int) ($healthSummary['offline'] ?? 0) > 0
        ) {
            $systemStatus = [
                'label' => 'Perlu Dicek',
                'class' => 'danger',
                'description' => 'Ada komponen yang tidak dapat dijangkau.',
            ];
        } elseif (
            (int) ($healthSummary['warning'] ?? 0) > 0
        ) {
            $systemStatus = [
                'label' => 'Warning',
                'class' => 'warning',
                'description' => 'Ada komponen yang memerlukan perhatian.',
            ];
        } elseif (
            strtolower(
                (string) config(
                    'app.env',
                    'local'
                )
            ) === 'local'
        ) {
            $systemStatus = [
                'label' => 'Local',
                'class' => 'local',
                'description' => 'Dashboard berjalan pada lingkungan development lokal.',
            ];
        } else {
            $systemStatus = [
                'label' => 'Normal',
                'class' => 'success',
                'description' => 'Komponen yang diperiksa berjalan normal.',
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Perangkat terbaru
        |--------------------------------------------------------------------------
        */

        $onlineColumn =
            Schema::hasColumn(
                'devices',
                'is_online'
            )
                ? 'is_online'
                : 'online';

        $lastSeenColumn =
            Schema::hasColumn(
                'devices',
                'last_seen_at'
            )
                ? 'last_seen_at'
                : 'last_seen';


        $latestDevices =
            Device::query()
                ->orderByDesc(
                    $lastSeenColumn
                )
                ->orderByDesc('id')
                ->limit(5)
                ->get();


        /*
        |--------------------------------------------------------------------------
        | Tambahan khusus monitoring
        |--------------------------------------------------------------------------
        */

        $data['healthSummary'] =
            $healthSummary;

        $data['systemStatus'] =
            $systemStatus;

        $data['latestDevices'] =
            $latestDevices;

        $data['deviceOnlineColumn'] =
            $onlineColumn;

        $data['deviceLastSeenColumn'] =
            $lastSeenColumn;


        return view(
            'pages.monitoring.index',
            $data
        );
    }
}
