<?php

namespace App\Services;

use App\Models\AccessLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DashboardQueryService
{
    public function getDashboardData(
        ?Carbon $from = null,
        ?Carbon $to = null
    ): array {
        $to ??= now();
        $from ??= $to->copy()->subHours(24);

        return [
            'period' => [
                'from' => $from,
                'to' => $to,
            ],

            'summary' => $this->summary($from, $to),
            'traffic' => $this->trafficPerHour($from, $to),
            'topDomains' => $this->topDomains($from, $to),
            'topClients' => $this->topClients($from, $to),
            'latestActivities' => $this->latestActivities($from, $to),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | SUMMARY
    |--------------------------------------------------------------------------
    |
    | Cache 2 menit.
    | Import Squid tetap 1 menit.
    |
    */
    public function summary(
        Carbon $from,
        Carbon $to
    ): array {
        return Cache::remember(
            $this->cacheKey('summary'),
            now()->addMinutes(2),
            function () use ($from, $to) {

                $totalRequest = AccessLog::query()
                    ->whereBetween('logged_at', [$from, $to])
                    ->count();

                $allowed = AccessLog::query()
                    ->whereIn('category', [
                        AccessLog::CATEGORY_ALLOWED,
                        AccessLog::CATEGORY_CACHE_HIT,
                        AccessLog::CATEGORY_CACHE_MISS,
                    ])
                    ->whereBetween('logged_at', [$from, $to])
                    ->count();

                $blocked = AccessLog::query()
                    ->where(
                        'category',
                        AccessLog::CATEGORY_BLOCKED
                    )
                    ->whereBetween('logged_at', [$from, $to])
                    ->count();

                $failed = AccessLog::query()
                    ->where(
                        'category',
                        AccessLog::CATEGORY_FAILED
                    )
                    ->whereBetween('logged_at', [$from, $to])
                    ->count();

                $cacheHit = AccessLog::query()
                    ->where(
                        'category',
                        AccessLog::CATEGORY_CACHE_HIT
                    )
                    ->whereBetween('logged_at', [$from, $to])
                    ->count();

                $cacheMiss = AccessLog::query()
                    ->where(
                        'category',
                        AccessLog::CATEGORY_CACHE_MISS
                    )
                    ->whereBetween('logged_at', [$from, $to])
                    ->count();

                $uniqueClients = AccessLog::query()
                    ->whereBetween('logged_at', [$from, $to])
                    ->whereNotNull('client_ip')
                    ->distinct()
                    ->count('client_ip');

                $cacheTotal = $cacheHit + $cacheMiss;

                $hitRatio = $cacheTotal > 0
                    ? ($cacheHit / $cacheTotal) * 100
                    : 0;

                return [
                    'total_request' => (int) $totalRequest,
                    'allowed' => (int) $allowed,
                    'blocked' => (int) $blocked,
                    'failed' => (int) $failed,
                    'unique_clients' => (int) $uniqueClients,
                    'cache_hit' => (int) $cacheHit,
                    'cache_miss' => (int) $cacheMiss,
                    'hit_ratio' => round($hitRatio, 2),
                    'total_bytes' => 0,
                ];
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TRAFFIC 24 JAM
    |--------------------------------------------------------------------------
    |
    | CACHE MENYIMPAN ARRAY, BUKAN COLLECTION.
    |
    */
    public function trafficPerHour(
        Carbon $from,
        Carbon $to
    ): Collection {
        $data = Cache::remember(
            $this->cacheKey('traffic'),
            now()->addMinutes(2),
            function () use ($from, $to) {

                $rows = AccessLog::query()
                    ->whereBetween('logged_at', [$from, $to])
                    ->selectRaw("
                        DATE_FORMAT(
                            logged_at,
                            '%Y-%m-%d %H:00:00'
                        ) AS hour,

                        COUNT(*) AS total_request,

                        SUM(
                            CASE
                                WHEN category IN (
                                    'ALLOWED',
                                    'CACHE_HIT',
                                    'CACHE_MISS'
                                )
                                THEN 1
                                ELSE 0
                            END
                        ) AS allowed,

                        SUM(
                            CASE
                                WHEN category = 'BLOCKED'
                                THEN 1
                                ELSE 0
                            END
                        ) AS blocked
                    ")
                    ->groupByRaw("
                        DATE_FORMAT(
                            logged_at,
                            '%Y-%m-%d %H:00:00'
                        )
                    ")
                    ->orderByRaw("
                        DATE_FORMAT(
                            logged_at,
                            '%Y-%m-%d %H:00:00'
                        )
                    ")
                    ->get()
                    ->keyBy('hour');

                $result = [];

                $cursor = $from->copy()->startOfHour();
                $end = $to->copy()->startOfHour();

                while ($cursor <= $end) {
                    $key = $cursor->format(
                        'Y-m-d H:00:00'
                    );

                    $row = $rows->get($key);

                    $result[] = [
                        'hour' =>
                            $cursor->format('H:00'),

                        'total_request' =>
                            $row
                                ? (int) $row->total_request
                                : 0,

                        'allowed' =>
                            $row
                                ? (int) $row->allowed
                                : 0,

                        'blocked' =>
                            $row
                                ? (int) $row->blocked
                                : 0,
                    ];

                    $cursor->addHour();
                }

                return $result;
            }
        );

        return collect($data);
    }

    /*
    |--------------------------------------------------------------------------
    | TOP DOMAIN
    |--------------------------------------------------------------------------
    */
    public function topDomains(
        Carbon $from,
        Carbon $to,
        int $limit = 10
    ): Collection {
        $data = Cache::remember(
            $this->cacheKey(
                'top-domains-'.$limit
            ),
            now()->addMinutes(3),
            function () use ($from, $to, $limit) {

                return AccessLog::query()
                    ->whereBetween(
                        'logged_at',
                        [$from, $to]
                    )
                    ->whereNotNull('domain')
                    ->where('domain', '!=', '')
                    ->selectRaw('
                        domain,
                        COUNT(*) AS total_request
                    ')
                    ->groupBy('domain')
                    ->orderByDesc('total_request')
                    ->limit($limit)
                    ->get()
                    ->map(function ($row) {
                        return [
                            'domain' =>
                                $row->domain,

                            'total_request' =>
                                (int) $row->total_request,

                            'total_bytes' => 0,
                        ];
                    })
                    ->values()
                    ->all();
            }
        );

        return collect($data);
    }

    /*
    |--------------------------------------------------------------------------
    | TOP CLIENT
    |--------------------------------------------------------------------------
    */
    public function topClients(
        Carbon $from,
        Carbon $to,
        int $limit = 10
    ): Collection {
        $data = Cache::remember(
            $this->cacheKey(
                'top-clients-'.$limit
            ),
            now()->addMinutes(3),
            function () use ($from, $to, $limit) {

                return AccessLog::query()
                    ->whereBetween(
                        'logged_at',
                        [$from, $to]
                    )
                    ->whereNotNull('client_ip')
                    ->selectRaw('
                        client_ip,
                        COUNT(*) AS total_request,
                        MAX(logged_at) AS last_seen
                    ')
                    ->groupBy('client_ip')
                    ->orderByDesc('total_request')
                    ->limit($limit)
                    ->get()
                    ->map(function ($row) {
                        return [
                            'client_ip' =>
                                $row->client_ip,

                            'total_request' =>
                                (int) $row->total_request,

                            'total_bytes' => 0,

                            'last_seen' =>
                                $row->last_seen,
                        ];
                    })
                    ->values()
                    ->all();
            }
        );

        return collect($data);
    }

    /*
    |--------------------------------------------------------------------------
    | AKTIVITAS TERBARU
    |--------------------------------------------------------------------------
    |
    | TIDAK DICACHE.
    | Query hanya 10 row terbaru.
    | Ini bagian live/realtime dashboard.
    |
    */
    public function latestActivities(
        Carbon $from,
        Carbon $to,
        int $limit = 10
    ): Collection {
        return AccessLog::query()
            ->whereBetween(
                'logged_at',
                [$from, $to]
            )
            ->orderByDesc('logged_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get([
                'id',
                'logged_at',
                'client_ip',
                'domain',
                'method',
                'protocol',
                'squid_code',
                'http_code',
                'bytes',
                'elapsed_ms',
                'category',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CACHE KEY
    |--------------------------------------------------------------------------
    |
    | Key dibuat stabil.
    | Jangan memasukkan current time ke key,
    | karena itu membuat cache tidak pernah benar-benar terpakai.
    |
    */
    private function cacheKey(
        string $name
    ): string {
        return 'dashboard:v4:' . $name;
    }
}
