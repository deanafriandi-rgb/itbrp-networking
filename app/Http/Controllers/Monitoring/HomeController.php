<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use App\Models\BlacklistDomain;
use App\Models\Device;
use App\Services\CacheAnalysisService;
use App\Services\DashboardQueryService;
use App\Services\SystemHealthService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function index(
        Request $request,
        DashboardQueryService $dashboard,
        CacheAnalysisService $cache,
        SystemHealthService $health
    ) {
        $data = $dashboard->getDashboardData();
        $cacheData = $cache->getCacheData();
        $healthData = $health->getStatusData();

        $healthSummary = $healthData['summary'] ?? [
            'total' => 0,
            'online' => 0,
            'warning' => 0,
            'offline' => 0,
            'local' => 0,
        ];

        if ((int) ($healthSummary['offline'] ?? 0) > 0) {
            $systemStatus = [
                'label' => 'Perlu Dicek',
                'class' => 'danger',
                'description' => 'Ada komponen yang tidak dapat dijangkau.',
            ];
        } elseif ((int) ($healthSummary['warning'] ?? 0) > 0) {
            $systemStatus = [
                'label' => 'Warning',
                'class' => 'warning',
                'description' => 'Ada komponen yang memerlukan perhatian.',
            ];
        } else {
            $systemStatus = [
                'label' => 'Normal',
                'class' => 'success',
                'description' => 'Komponen yang diperiksa berjalan normal.',
            ];
        }

        $onlineColumn = Schema::hasColumn('devices', 'is_online')
            ? 'is_online'
            : 'online';

        $lastSeenColumn = Schema::hasColumn('devices', 'last_seen_at')
            ? 'last_seen_at'
            : 'last_seen';

        /*
        |--------------------------------------------------------------------------
        | DEVICE TABLE + FILTER
        |--------------------------------------------------------------------------
        */

        $deviceQuery = Device::query();

        if ($request->filled('device_search')) {
            $search = trim((string) $request->device_search);

            $deviceQuery->where(function ($q) use ($search) {
                $q->where('device_name', 'like', "%{$search}%")
                    ->orWhere('hostname', 'like', "%{$search}%")
                    ->orWhere('owner_name', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('mac_address', 'like', "%{$search}%")
                    ->orWhere('interface', 'like', "%{$search}%")
                    ->orWhere('segment', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->device_status === 'online') {
            $deviceQuery->where($onlineColumn, true);
        } elseif ($request->device_status === 'offline') {
            $deviceQuery->where($onlineColumn, false);
        }

        if ($request->filled('device_segment')) {
            $deviceQuery->where('segment', $request->device_segment);
        }

        if ($request->filled('device_interface')) {
            $deviceQuery->where('interface', $request->device_interface);
        }

        $devices = $deviceQuery
            ->orderByDesc($onlineColumn)
            ->orderByDesc($lastSeenColumn)
            ->paginate(20, ['*'], 'device_page')
            ->withQueryString();

        $deviceSummary = [
            'total' => Device::count(),
            'online' => Device::where($onlineColumn, true)->count(),
            'offline' => Device::where($onlineColumn, false)->count(),
            'staff' => Device::whereIn('owner_type', [
                'dosen',
                'staff',
                'dosen_staff',
            ])->count(),
            'student' => Device::where('owner_type', 'mahasiswa')->count(),
            'guest' => Device::where('owner_type', 'tamu')->count(),
        ];

        $segments = Device::query()
            ->whereNotNull('segment')
            ->where('segment', '!=', '')
            ->distinct()
            ->orderBy('segment')
            ->pluck('segment');

        $interfaces = Device::query()
            ->whereNotNull('interface')
            ->where('interface', '!=', '')
            ->distinct()
            ->orderBy('interface')
            ->pluck('interface');

        $displayedIps = $devices
            ->getCollection()
            ->pluck('ip_address')
            ->filter()
            ->unique()
            ->values();

        $deviceUsageFrom = Carbon::now()->subHours(24);

        $usageByIp = $displayedIps->isEmpty()
            ? collect()
            : AccessLog::query()
                ->selectRaw('
                    client_ip,
                    COALESCE(SUM(bytes), 0) AS total_bytes,
                    COUNT(*) AS total_request
                ')
                ->where('logged_at', '>=', $deviceUsageFrom)
                ->whereIn('client_ip', $displayedIps)
                ->groupBy('client_ip')
                ->get()
                ->keyBy('client_ip');

        $latestDevices = Device::query()
            ->orderByDesc($onlineColumn)
            ->orderByDesc($lastSeenColumn)
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | BLACKLIST TABLE + FILTER
        |--------------------------------------------------------------------------
        */

        $blacklistQuery = BlacklistDomain::query();

        if ($request->filled('blacklist_search')) {
            $search = trim((string) $request->blacklist_search);

            $blacklistQuery->where(function ($q) use ($search) {
                $q->where('domain', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('source', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($request->filled('blacklist_category')) {
            $blacklistQuery->where(
                'category',
                $request->blacklist_category
            );
        }

        if ($request->blacklist_status === 'active') {
            $blacklistQuery->where('is_active', true);
        } elseif ($request->blacklist_status === 'inactive') {
            $blacklistQuery->where('is_active', false);
        }

        if ($request->filled('blacklist_sync_status')) {
            $blacklistQuery->where(
                'sync_status',
                $request->blacklist_sync_status
            );
        }

        $domains = $blacklistQuery
            ->orderByDesc('is_active')
            ->orderBy('domain')
            ->paginate(20, ['*'], 'blacklist_page')
            ->withQueryString();

        $blacklistSummary = [
            'total' => BlacklistDomain::count(),
            'active' => BlacklistDomain::where('is_active', true)->count(),
            'inactive' => BlacklistDomain::where('is_active', false)->count(),
            'categories' => BlacklistDomain::query()
                ->where('is_active', true)
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->distinct()
                ->count('category'),
            'synced' => BlacklistDomain::where('sync_status', 'synced')->count(),
            'pending' => BlacklistDomain::where('sync_status', 'pending')->count(),
            'error' => BlacklistDomain::where('sync_status', 'error')->count(),
            'include_subdomains' => BlacklistDomain::where(
                'include_subdomains',
                true
            )->count(),
        ];

        $blacklistCategories = BlacklistDomain::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        /*
        |--------------------------------------------------------------------------
        | BLOCKED TREND 24 JAM
        |--------------------------------------------------------------------------
        */

        $blockedTo = Carbon::now();
        $blockedFrom = $blockedTo->copy()->subHours(24);

        $blockedRows = AccessLog::query()
            ->selectRaw("
                DATE_FORMAT(logged_at, '%Y-%m-%d %H:00:00') AS hour,
                COUNT(*) AS blocked
            ")
            ->whereBetween('logged_at', [$blockedFrom, $blockedTo])
            ->where('category', 'BLOCKED')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $blockedTrend = collect();

        $cursor = $blockedFrom->copy()->startOfHour();
        $end = $blockedTo->copy()->startOfHour();

        while ($cursor <= $end) {
            $key = $cursor->format('Y-m-d H:00:00');
            $row = $blockedRows->get($key);

            $blockedTrend->push([
                'hour' => $cursor->format('H:00'),
                'blocked' => $row ? (int) $row->blocked : 0,
            ]);

            $cursor->addHour();
        }

        $policyInfo = [
            'squid_mode' => strtoupper(
                (string) config('squid.mode', 'local')
            ),
            'https_port' => (int) config(
                'squid.ports.https_intercept',
                config('squid.https_intercept_port', 3130)
            ),
            'https_method' => 'SNI / ssl::server_name',
            'full_tls_decrypt' => false,
        ];

        /*
        |--------------------------------------------------------------------------
        | SATU VIEW INDEX
        |--------------------------------------------------------------------------
        */

        $data['cacheData'] = $cacheData;
        $data['healthData'] = $healthData;
        $data['healthSummary'] = $healthSummary;
        $data['systemStatus'] = $systemStatus;

        $data['devices'] = $devices;
        $data['deviceSummary'] = $deviceSummary;
        $data['segments'] = $segments;
        $data['interfaces'] = $interfaces;
        $data['usageByIp'] = $usageByIp;
        $data['latestDevices'] = $latestDevices;
        $data['onlineColumn'] = $onlineColumn;
        $data['lastSeenColumn'] = $lastSeenColumn;

        $data['domains'] = $domains;
        $data['blacklistSummary'] = $blacklistSummary;
        $data['blacklistCategories'] = $blacklistCategories;
        $data['blockedTrend'] = $blockedTrend;
        $data['policyInfo'] = $policyInfo;

        $data['nowMonitoring'] = now('Asia/Jakarta');

        return view(
            'pages.monitoring.index',
            $data
        );
    }
}
