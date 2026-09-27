<?php

namespace App\Services;

use App\Models\AccessLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AccessMonitoringService
{
    public function getData(
        ?Carbon $from = null,
        ?Carbon $to = null
    ): array {
        $from ??= now()->subHours(24);
        $to ??= now();

        return [
            'summary' => $this->summary($from, $to),
            'traffic' => $this->trafficPerHour($from, $to),
            'peakHours' => $this->peakHours($from, $to),
            'topSegments' => $this->topSegments($from, $to),
        ];
    }

    public function summary(
        Carbon $from,
        Carbon $to
    ): array {
        $base = AccessLog::query()
            ->whereBetween('logged_at', [$from, $to]);

        $total = (clone $base)->count();

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

        $uniqueClients = (clone $base)
            ->distinct()
            ->count('client_ip');

        /*
        |--------------------------------------------------------------------------
        | Response time
        |--------------------------------------------------------------------------
        |
        | CONNECT HTTPS tidak digunakan karena elapsed_ms pada TCP_TUNNEL
        | dapat merepresentasikan durasi tunnel, bukan latency jaringan.
        |
        */

        $avgResponse = (clone $base)
            ->where('method', '!=', 'CONNECT')
            ->where('elapsed_ms', '>', 0)
            ->avg('elapsed_ms');

        $totalBytes = (clone $base)
            ->sum('bytes');

        return [
            'total' => $total,
            'allowed' => $allowed,
            'blocked' => $blocked,
            'unique_clients' => $uniqueClients,
            'avg_response_ms' => $avgResponse !== null
                ? round((float) $avgResponse, 2)
                : 0,
            'total_bytes' => (int) $totalBytes,
        ];
    }

    public function trafficPerHour(
        Carbon $from,
        Carbon $to
    ): Collection {
        $rows = AccessLog::query()
            ->selectRaw("
                DATE_FORMAT(logged_at, '%Y-%m-%d %H:00:00') AS hour,
                COUNT(*) AS total,

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

        $result = collect();

        $cursor = $from->copy()->startOfHour();
        $end = $to->copy()->startOfHour();

        while ($cursor <= $end) {
            $key = $cursor->format('Y-m-d H:00:00');

            $row = $rows->get($key);

            $result->push([
                'hour' => $cursor->format('H:00'),
                'total' => $row
                    ? (int) $row->total
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

    public function peakHours(
        Carbon $from,
        Carbon $to
    ): Collection {
        return AccessLog::query()
            ->selectRaw("
                HOUR(logged_at) AS hour_number,
                COUNT(*) AS total
            ")
            ->whereBetween('logged_at', [$from, $to])
            ->groupBy('hour_number')
            ->orderBy('hour_number')
            ->get()
            ->keyBy('hour_number');
    }

    public function topSegments(
        Carbon $from,
        Carbon $to
    ): Collection {
        $clients = AccessLog::query()
            ->selectRaw("
                client_ip,
                COUNT(*) AS total
            ")
            ->whereBetween('logged_at', [$from, $to])
            ->groupBy('client_ip')
            ->get();

        $segments = [
            '172.16.4.0/23' => 0,
            '172.16.6.0/23' => 0,
            '12.12.12.0/24' => 0,
            'Lainnya' => 0,
        ];

        foreach ($clients as $client) {
            $ip = $client->client_ip;
            $total = (int) $client->total;

            if ($this->ipInCidr($ip, '172.16.4.0/23')) {
                $segments['172.16.4.0/23'] += $total;
            } elseif ($this->ipInCidr($ip, '172.16.6.0/23')) {
                $segments['172.16.6.0/23'] += $total;
            } elseif ($this->ipInCidr($ip, '12.12.12.0/24')) {
                $segments['12.12.12.0/24'] += $total;
            } else {
                $segments['Lainnya'] += $total;
            }
        }

        $grandTotal = array_sum($segments);

        return collect($segments)
            ->map(function ($total, $segment) use ($grandTotal) {
                return [
                    'segment' => $segment,
                    'total' => $total,
                    'percentage' => $grandTotal > 0
                        ? round(
                            ($total / $grandTotal) * 100,
                            1
                        )
                        : 0,
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    private function ipInCidr(
        string $ip,
        string $cidr
    ): bool {
        if (
            !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
        ) {
            return false;
        }

        [$network, $prefix] = explode('/', $cidr);

        $ipLong = ip2long($ip);
        $networkLong = ip2long($network);

        $mask = -1 << (32 - (int) $prefix);

        return (
            ($ipLong & $mask)
            ===
            ($networkLong & $mask)
        );
    }
}
