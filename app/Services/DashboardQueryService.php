<?php

namespace App\Services;

use App\Models\AccessLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardQueryService
{
    /**
     * Data utama Dashboard Admin.
     */
    public function getDashboardData(
        ?Carbon $from = null,
        ?Carbon $to = null
    ): array {
        $from ??= now()->subHours(24);
        $to ??= now();

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

    /**
     * Card dashboard.
     */
    public function summary(
        Carbon $from,
        Carbon $to
    ): array {
        $base = AccessLog::query()
            ->whereBetween('logged_at', [$from, $to]);

        $totalRequest = (clone $base)->count();

        /*
        |--------------------------------------------------------------------------
        | Allowed
        |--------------------------------------------------------------------------
        |
        | CACHE_HIT dan CACHE_MISS adalah request yang tetap berhasil
        | diproses, sehingga ikut dihitung sebagai allowed.
        |
        */

        $allowed = (clone $base)
            ->whereIn('category', [
                AccessLog::CATEGORY_ALLOWED,
                AccessLog::CATEGORY_CACHE_HIT,
                AccessLog::CATEGORY_CACHE_MISS,
            ])
            ->count();

        $blocked = (clone $base)
            ->where(
                'category',
                AccessLog::CATEGORY_BLOCKED
            )
            ->count();

        $failed = (clone $base)
            ->where(
                'category',
                AccessLog::CATEGORY_FAILED
            )
            ->count();

        $uniqueClients = (clone $base)
            ->distinct()
            ->count('client_ip');

        $cacheHit = (clone $base)
            ->where(
                'category',
                AccessLog::CATEGORY_CACHE_HIT
            )
            ->count();

        $cacheMiss = (clone $base)
            ->where(
                'category',
                AccessLog::CATEGORY_CACHE_MISS
            )
            ->count();

        $cacheTotal = $cacheHit + $cacheMiss;

        $hitRatio = $cacheTotal > 0
            ? ($cacheHit / $cacheTotal) * 100
            : 0;

        $totalBytes = (clone $base)
            ->sum('bytes');

        return [
            'total_request' => $totalRequest,

            'allowed' => $allowed,

            'blocked' => $blocked,

            'failed' => $failed,

            'unique_clients' => $uniqueClients,

            'cache_hit' => $cacheHit,

            'cache_miss' => $cacheMiss,

            'hit_ratio' => round($hitRatio, 2),

            'total_bytes' => (int) $totalBytes,
        ];
    }

    /**
     * Grafik request 24 jam.
     */
    public function trafficPerHour(
        Carbon $from,
        Carbon $to
    ): Collection {
        $rows = AccessLog::query()
            ->selectRaw("
                DATE_FORMAT(logged_at, '%Y-%m-%d %H:00:00') AS hour,
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
            ->whereBetween('logged_at', [$from, $to])
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        /*
        |--------------------------------------------------------------------------
        | Isi jam kosong dengan angka 0
        |--------------------------------------------------------------------------
        */

        $result = collect();

        $cursor = $from
            ->copy()
            ->startOfHour();

        $end = $to
            ->copy()
            ->startOfHour();

        while ($cursor <= $end) {
            $key = $cursor->format('Y-m-d H:00:00');

            $row = $rows->get($key);

            $result->push([
                'hour' => $cursor->format('H:00'),

                'total_request' => $row
                    ? (int) $row->total_request
                    : 0,

                'allowed' => $row
                    ? (int) $row->allowed
                    : 0,

                'blocked' => $row
                    ? (int) $row->blocked
                    : 0,
            ]);

            $cursor->addHour();
        }

        return $result;
    }

    /**
     * Top domain.
     */
    public function topDomains(
        Carbon $from,
        Carbon $to,
        int $limit = 10
    ): Collection {
        return AccessLog::query()
            ->selectRaw('
                domain,
                COUNT(*) AS total_request,
                SUM(bytes) AS total_bytes
            ')
            ->whereBetween('logged_at', [$from, $to])
            ->whereNotNull('domain')
            ->where('domain', '!=', '')
            ->groupBy('domain')
            ->orderByDesc('total_request')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                return [
                    'domain' => $row->domain,

                    'total_request' =>
                    (int) $row->total_request,

                    'total_bytes' =>
                    (int) $row->total_bytes,
                ];
            });
    }

    /**
     * Top Client berdasarkan jumlah request.
     */
    public function topClients(
        Carbon $from,
        Carbon $to,
        int $limit = 10
    ): Collection {
        return AccessLog::query()
            ->selectRaw('
                client_ip,
                COUNT(*) AS total_request,
                SUM(bytes) AS total_bytes,
                MAX(logged_at) AS last_seen
            ')
            ->whereBetween('logged_at', [$from, $to])
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

                    'total_bytes' =>
                    (int) $row->total_bytes,

                    'last_seen' =>
                    $row->last_seen,
                ];
            });
    }

    /**
     * Aktivitas terbaru.
     */
    public function latestActivities(
        Carbon $from,
        Carbon $to,
        int $limit = 10
    ): Collection {
        return AccessLog::query()
            ->whereBetween('logged_at', [$from, $to])
            ->latest('logged_at')
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
}
