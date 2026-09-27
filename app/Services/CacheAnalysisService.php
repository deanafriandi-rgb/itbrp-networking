<?php

namespace App\Services;

use App\Models\AccessLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CacheAnalysisService
{
    /**
     * Semua data halaman Cache.
     */
    public function getCacheData(
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

            'trend' => $this->cacheTrend($from, $to),

            'topDomains' => $this->topCacheDomains(
                $from,
                $to
            ),

            'recentActivities' => $this->recentActivities(
                $from,
                $to
            ),

            'objectComparisons' => $this->objectComparisons(
                $from,
                $to
            ),
        ];
    }

    /**
     * Statistik utama cache.
     */
    public function summary(
        Carbon $from,
        Carbon $to
    ): array {
        $cacheQuery = AccessLog::query()
            ->whereBetween('logged_at', [$from, $to])
            ->whereIn('category', [
                AccessLog::CATEGORY_CACHE_HIT,
                AccessLog::CATEGORY_CACHE_MISS,
            ]);

        $hit = (clone $cacheQuery)
            ->where(
                'category',
                AccessLog::CATEGORY_CACHE_HIT
            )
            ->count();

        $miss = (clone $cacheQuery)
            ->where(
                'category',
                AccessLog::CATEGORY_CACHE_MISS
            )
            ->count();

        $total = $hit + $miss;

        $hitRatio = $total > 0
            ? ($hit / $total) * 100
            : 0;

        $avgHit = (clone $cacheQuery)
            ->where(
                'category',
                AccessLog::CATEGORY_CACHE_HIT
            )
            ->avg('elapsed_ms');

        $avgMiss = (clone $cacheQuery)
            ->where(
                'category',
                AccessLog::CATEGORY_CACHE_MISS
            )
            ->avg('elapsed_ms');

        $avgHit = $avgHit !== null
            ? round((float) $avgHit, 2)
            : null;

        $avgMiss = $avgMiss !== null
            ? round((float) $avgMiss, 2)
            : null;

        $difference = null;
        $improvement = null;

        if (
            $avgHit !== null &&
            $avgMiss !== null
        ) {
            $difference = round(
                $avgMiss - $avgHit,
                2
            );

            if ($avgMiss > 0) {
                $improvement = round(
                    (($avgMiss - $avgHit) / $avgMiss) * 100,
                    2
                );
            }
        }

        $hitBytes = (clone $cacheQuery)
            ->where(
                'category',
                AccessLog::CATEGORY_CACHE_HIT
            )
            ->sum('bytes');

        $missBytes = (clone $cacheQuery)
            ->where(
                'category',
                AccessLog::CATEGORY_CACHE_MISS
            )
            ->sum('bytes');

        return [
            'hit' => $hit,
            'miss' => $miss,
            'total' => $total,
            'hit_ratio' => round($hitRatio, 2),
            'avg_hit_ms' => $avgHit,
            'avg_miss_ms' => $avgMiss,
            'response_difference_ms' => $difference,
            'response_improvement_percent' => $improvement,
            'hit_bytes' => (int) $hitBytes,
            'miss_bytes' => (int) $missBytes,
        ];
    }

    /**
     * Tren HIT/MISS per jam untuk 24 jam terakhir.
     */
    public function cacheTrend(
        Carbon $from,
        Carbon $to
    ): Collection {
        $rows = AccessLog::query()
            ->selectRaw("
                DATE_FORMAT(logged_at, '%Y-%m-%d %H:00:00') AS hour,

                SUM(
                    CASE
                        WHEN category = 'CACHE_HIT'
                        THEN 1
                        ELSE 0
                    END
                ) AS hit,

                SUM(
                    CASE
                        WHEN category = 'CACHE_MISS'
                        THEN 1
                        ELSE 0
                    END
                ) AS miss
            ")
            ->whereBetween('logged_at', [$from, $to])
            ->whereIn('category', [
                AccessLog::CATEGORY_CACHE_HIT,
                AccessLog::CATEGORY_CACHE_MISS,
            ])
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
                'hit' => $row
                    ? (int) $row->hit
                    : 0,
                'miss' => $row
                    ? (int) $row->miss
                    : 0,
            ]);

            $cursor->addHour();
        }

        return $result;
    }

    /**
     * Domain yang paling sering berhubungan dengan cache.
     */
    public function topCacheDomains(
        Carbon $from,
        Carbon $to,
        int $limit = 10
    ): Collection {
        return AccessLog::query()
            ->selectRaw("
                domain,

                SUM(
                    CASE
                        WHEN category = 'CACHE_HIT'
                        THEN 1
                        ELSE 0
                    END
                ) AS hit_count,

                SUM(
                    CASE
                        WHEN category = 'CACHE_MISS'
                        THEN 1
                        ELSE 0
                    END
                ) AS miss_count,

                COUNT(*) AS total_request,
                SUM(bytes) AS total_bytes
            ")
            ->whereBetween('logged_at', [$from, $to])
            ->whereIn('category', [
                AccessLog::CATEGORY_CACHE_HIT,
                AccessLog::CATEGORY_CACHE_MISS,
            ])
            ->whereNotNull('domain')
            ->where('domain', '!=', '')
            ->groupBy('domain')
            ->orderByDesc('total_request')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $total =
                    (int) $row->hit_count +
                    (int) $row->miss_count;

                $ratio = $total > 0
                    ? ((int) $row->hit_count / $total) * 100
                    : 0;

                return [
                    'domain' => $row->domain,
                    'hit' => (int) $row->hit_count,
                    'miss' => (int) $row->miss_count,
                    'total_request' => (int) $row->total_request,
                    'hit_ratio' => round($ratio, 2),
                    'total_bytes' => (int) $row->total_bytes,
                ];
            });
    }

    /**
     * Aktivitas HIT/MISS terbaru.
     */
    public function recentActivities(
        Carbon $from,
        Carbon $to,
        int $limit = 20
    ): Collection {
        return AccessLog::query()
            ->whereBetween('logged_at', [$from, $to])
            ->whereIn('category', [
                AccessLog::CATEGORY_CACHE_HIT,
                AccessLog::CATEGORY_CACHE_MISS,
            ])
            ->latest('logged_at')
            ->limit($limit)
            ->get([
                'id',
                'logged_at',
                'client_ip',
                'domain',
                'url',
                'category',
                'squid_code',
                'bytes',
                'elapsed_ms',
                'mime_type',
            ]);
    }

    /**
     * Membandingkan objek yang sama yang memiliki CACHE_HIT dan CACHE_MISS.
     */
    public function objectComparisons(
        Carbon $from,
        Carbon $to,
        int $limit = 10
    ): Collection {
        return AccessLog::query()
            ->selectRaw("
                url,
                domain,

                SUM(
                    CASE
                        WHEN category = 'CACHE_HIT'
                        THEN 1
                        ELSE 0
                    END
                ) AS hit_count,

                SUM(
                    CASE
                        WHEN category = 'CACHE_MISS'
                        THEN 1
                        ELSE 0
                    END
                ) AS miss_count,

                AVG(
                    CASE
                        WHEN category = 'CACHE_HIT'
                        THEN elapsed_ms
                    END
                ) AS avg_hit_ms,

                AVG(
                    CASE
                        WHEN category = 'CACHE_MISS'
                        THEN elapsed_ms
                    END
                ) AS avg_miss_ms
            ")
            ->whereBetween('logged_at', [$from, $to])
            ->whereIn('category', [
                AccessLog::CATEGORY_CACHE_HIT,
                AccessLog::CATEGORY_CACHE_MISS,
            ])
            ->whereNotNull('url')
            ->where('url', '!=', '')
            ->groupBy('url', 'domain')

            ->havingRaw("
                SUM(
                    CASE
                        WHEN category = 'CACHE_HIT'
                        THEN 1
                        ELSE 0
                    END
                ) > 0
            ")

            ->havingRaw("
                SUM(
                    CASE
                        WHEN category = 'CACHE_MISS'
                        THEN 1
                        ELSE 0
                    END
                ) > 0
            ")

            ->orderByRaw('(hit_count + miss_count) DESC')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $hit = round(
                    (float) $row->avg_hit_ms,
                    2
                );

                $miss = round(
                    (float) $row->avg_miss_ms,
                    2
                );

                $difference = round(
                    $miss - $hit,
                    2
                );

                $improvement = $miss > 0
                    ? round(
                        (($miss - $hit) / $miss) * 100,
                        2
                    )
                    : null;

                return [
                    'url' => $row->url,
                    'domain' => $row->domain,
                    'hit_count' => (int) $row->hit_count,
                    'miss_count' => (int) $row->miss_count,
                    'avg_hit_ms' => $hit,
                    'avg_miss_ms' => $miss,
                    'difference_ms' => $difference,
                    'improvement_percent' => $improvement,
                ];
            });
    }
}
