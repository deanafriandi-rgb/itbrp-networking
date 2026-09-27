@extends('layouts.monitoring')

@section('title', 'Cache')

@section('content')

@php
/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

$hit =
(int) ($summary['hit'] ?? 0);

$miss =
(int) ($summary['miss'] ?? 0);

$totalCache =
(int) ($summary['total'] ?? 0);

$hitRatio =
(float) ($summary['hit_ratio'] ?? 0);

$avgHit =
$summary['avg_hit_ms'] ?? null;

$avgMiss =
$summary['avg_miss_ms'] ?? null;

$responseDifference =
$summary['response_difference_ms'] ?? null;

$responseImprovement =
$summary['response_improvement_percent'] ?? null;

$hitBytes =
(int) ($summary['hit_bytes'] ?? 0);

$missBytes =
(int) ($summary['miss_bytes'] ?? 0);


/*
|--------------------------------------------------------------------------
| Time
|--------------------------------------------------------------------------
*/

$now =
now('Asia/Jakarta');


/*
|--------------------------------------------------------------------------
| Trend Chart
|--------------------------------------------------------------------------
*/

$trendCollection =
collect(
$trend ?? []
)->values();


$maxTrendValue =
max(
1,
(int) (
$trendCollection
->max(
function ($item) {
return max(
(int) ($item['hit'] ?? 0),
(int) ($item['miss'] ?? 0)
);
}
)
?? 0
)
);


$trendCount =
$trendCollection->count();


$trendStep =
$trendCount > 1
? 800 / ($trendCount - 1)
: 800;


$hitPointList = [];
$missPointList = [];


foreach (
$trendCollection
as $index => $item
) {
$x =
$index * $trendStep;

$hitValue =
(int) ($item['hit'] ?? 0);

$missValue =
(int) ($item['miss'] ?? 0);

$hitY =
190
-
(
($hitValue / $maxTrendValue)
* 170
);

$missY =
190
-
(
($missValue / $maxTrendValue)
* 170
);


$hitPointList[] =
number_format(
$x,
1,
'.',
''
)
.
','
.
number_format(
$hitY,
1,
'.',
''
);


$missPointList[] =
number_format(
$x,
1,
'.',
''
)
.
','
.
number_format(
$missY,
1,
'.',
''
);
}


$hitPoints =
implode(
' ',
$hitPointList
);


$missPoints =
implode(
' ',
$missPointList
);


/*
|--------------------------------------------------------------------------
| Top Domain
|--------------------------------------------------------------------------
*/

$topDomainsCollection =
collect(
$topDomains ?? []
)
->take(6)
->values();


$maxDomainRequest =
max(
1,
(int) (
$topDomainsCollection
->max('total_request')
?? 0
)
);


/*
|--------------------------------------------------------------------------
| Object Comparisons
|--------------------------------------------------------------------------
*/

$objectComparisonCollection =
collect(
$objectComparisons ?? []
)
->take(6)
->values();


/*
|--------------------------------------------------------------------------
| Recent Activities
|--------------------------------------------------------------------------
*/

$recentCollection =
collect(
$recentActivities ?? []
)
->take(12)
->values();


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

$formatBytes =
function ($bytes) {
$bytes =
(int) $bytes;

if ($bytes >= 1073741824) {
return number_format(
$bytes / 1073741824,
2,
',',
'.'
)
.
' GB';
}

if ($bytes >= 1048576) {
return number_format(
$bytes / 1048576,
2,
',',
'.'
)
.
' MB';
}

if ($bytes >= 1024) {
return number_format(
$bytes / 1024,
2,
',',
'.'
)
.
' KB';
}

return number_format(
$bytes,
0,
',',
'.'
)
.
' B';
};


$shortObjectName =
function ($url, $domain = null) {

if (!$url) {
return $domain ?: '-';
}

try {
$path =
parse_url(
$url,
PHP_URL_PATH
);

$basename =
$path
? basename($path)
: null;

if (
$basename
&&
$basename !== '/'
) {
return $basename;
}
} catch (\Throwable $e) {
//
}

return $domain
?: \Illuminate\Support\Str::limit(
(string) $url,
38
);
};
@endphp


<style>
    .monitoring-cache {
        --mc-border: #dce7f2;
        --mc-text: #17385f;
        --mc-muted: #78879b;
    }

    .monitoring-cache * {
        box-sizing: border-box;
    }

    .mc-grid-three {
        display: grid;
        grid-template-columns:
            1.45fr 1fr .85fr;
        gap: 12px;
    }

    .mc-empty {
        padding: 34px 18px;
        text-align: center;
        color: #8190a3;
        font-size: 11px;
        line-height: 1.7;
    }

    .mc-object-list {
        display: grid;
        gap: 11px;
        margin-top: 14px;
    }

    .mc-object-row {
        display: grid;
        gap: 6px;
    }

    .mc-object-top {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        color: #4f6783;
        font-size: 10px;
    }

    .mc-object-top span {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mc-object-top b {
        color: #173b66;
        white-space: nowrap;
    }

    .mc-track {
        height: 7px;
        overflow: hidden;
        border-radius: 999px;
        background: #edf2f7;
    }

    .mc-track i {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: #12b76a;
    }

    .mc-donut-wrap {
        position: relative;
        width: 178px;
        height: 178px;
        margin: 18px auto 0;
    }

    .mc-donut {
        width: 178px;
        height: 178px;
        border-radius: 50%;

        background: conic-gradient(#12b76a 0 {
                    {
                    min(100, max(0, $hitRatio))
                }
            }

            %,
            #eef3f7 {
                    {
                    min(100, max(0, $hitRatio))
                }
            }

            % 100%);
        position: relative;
    }

    .mc-donut::after {
        content: "";
        position: absolute;
        inset: 26px;
        border-radius: 50%;
        background: #fff;
        box-shadow:
            inset 0 0 0 1px #edf1f6;
    }

    .mc-donut-center {
        position: absolute;
        inset: 0;
        z-index: 1;
        display: grid;
        place-content: center;
        text-align: center;
    }

    .mc-donut-center b {
        color: #123865;
        font-size: 28px;
        line-height: 1;
    }

    .mc-donut-center small {
        margin-top: 7px;
        color: #7d8a9c;
        font-size: 10px;
    }

    .mc-note {
        margin-top: 14px;
        padding: 12px 13px;
        border: 1px solid #e2ebf5;
        border-radius: 12px;
        background: #f7faff;
        color: #647891;
        font-size: 10px;
        line-height: 1.7;
    }

    .mc-domain {
        max-width: 260px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mc-url {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    @media (max-width: 1050px) {
        .mc-grid-three {
            grid-template-columns: 1fr;
        }
    }
</style>


<div class="monitoring-cache">

    {{-- ===================================================== --}}
    {{-- HERO --}}
    {{-- ===================================================== --}}

    <section class="hero">

        <div class="hero-bg"></div>

        <div class="hero-copy">

            <div class="eyebrow">
                ITBRP NETWORK MONITORING
            </div>

            <h1>
                Cache Jaringan
                <br>

                <span class="accent">
                    untuk Akses yang Lebih Efisien
                </span>
            </h1>

            <p>
                Pantau efektivitas cache Squid berdasarkan transaksi
                CACHE_HIT dan CACHE_MISS, response time, serta data
                aktivitas cache selama 24 jam terakhir.
            </p>

            <div class="hero-chips">

                <span class="hero-chip">
                    <span class="chip-icon green">▤</span>
                    CACHE HIT
                </span>

                <span class="hero-chip">
                    <span class="chip-icon">◆</span>
                    CACHE MISS
                </span>

                <span class="hero-chip">
                    <span class="chip-icon purple">◴</span>
                    Response Time
                </span>

            </div>

        </div>


        <div class="hero-status">
            <span class="status-dot"></span>
            Read-only Monitoring
        </div>


        <div class="hero-clock">

            <small>
                {{ $now->translatedFormat('l, d F Y') }}
            </small>

            <b>
                {{ $now->format('H:i') }}
            </b>

            <span>WIB</span>

        </div>

    </section>


    {{-- ===================================================== --}}
    {{-- SUMMARY --}}
    {{-- ===================================================== --}}

    <div class="stats public-five">

        <div class="stat-card green">

            <div class="stat-top">

                <div class="stat-icon">
                    ▤
                </div>

                <div>
                    <div class="stat-label">
                        Cache HIT
                    </div>

                    <div class="stat-value">
                        {{ number_format($hit, 0, ',', '.') }}
                    </div>
                </div>

            </div>

            <div class="stat-foot">
                {{ $formatBytes($hitBytes) }}
                dilayani dari cache
            </div>

        </div>


        <div class="stat-card red">

            <div class="stat-top">

                <div class="stat-icon">
                    ⊘
                </div>

                <div>
                    <div class="stat-label">
                        Cache MISS
                    </div>

                    <div class="stat-value">
                        {{ number_format($miss, 0, ',', '.') }}
                    </div>
                </div>

            </div>

            <div class="stat-foot">
                {{ $formatBytes($missBytes) }}
                dari origin
            </div>

        </div>


        <div class="stat-card purple">

            <div class="stat-top">

                <div class="stat-icon">
                    %
                </div>

                <div>
                    <div class="stat-label">
                        Hit Ratio
                    </div>

                    <div class="stat-value">
                        {{ number_format($hitRatio, 1, ',', '.') }}%
                    </div>
                </div>

            </div>

            <div class="stat-foot">
                Dari {{ number_format($totalCache, 0, ',', '.') }}
                transaksi cache
            </div>

        </div>


        <div class="stat-card blue">

            <div class="stat-top">

                <div class="stat-icon">
                    ◴
                </div>

                <div>
                    <div class="stat-label">
                        Avg HIT
                    </div>

                    <div
                        class="stat-value"
                        style="font-size:23px">
                        @if($avgHit !== null)
                        {{ number_format((float) $avgHit, 2, ',', '.') }} ms
                        @else
                        -
                        @endif
                    </div>
                </div>

            </div>

            <div class="stat-foot">
                Rata-rata response cache
            </div>

        </div>


        <div class="stat-card orange">

            <div class="stat-top">

                <div class="stat-icon">
                    ◴
                </div>

                <div>
                    <div class="stat-label">
                        Avg MISS
                    </div>

                    <div
                        class="stat-value"
                        style="font-size:23px">
                        @if($avgMiss !== null)
                        {{ number_format((float) $avgMiss, 2, ',', '.') }} ms
                        @else
                        -
                        @endif
                    </div>
                </div>

            </div>

            <div class="stat-foot">
                Rata-rata response origin
            </div>

        </div>

    </div>


    {{-- ===================================================== --}}
    {{-- TREND + TOP DOMAIN + RATIO --}}
    {{-- ===================================================== --}}

    <div class="mc-grid-three section-pad">

        {{-- TREND --}}
        <div class="panel">

            <div class="panel-head">

                <div>
                    <div class="panel-title">
                        ▥ Perbandingan Cache HIT dan MISS
                    </div>
                </div>

                <select
                    class="tiny-select"
                    disabled>
                    <option>
                        24 Jam Terakhir
                    </option>
                </select>

            </div>


            <div class="chart">

                <div class="chart-grid"></div>


                <svg
                    viewBox="0 0 800 210"
                    preserveAspectRatio="none"
                    aria-label="Grafik cache hit dan miss">

                    @if($trendCollection->isNotEmpty())

                    <polygon
                        points="{{ $hitPoints }} 800,210 0,210"
                        fill="#12b76a"
                        opacity=".07"></polygon>

                    <polyline
                        points="{{ $hitPoints }}"
                        fill="none"
                        stroke="#12b76a"
                        stroke-width="4"
                        stroke-linecap="round"
                        stroke-linejoin="round"></polyline>


                    <polygon
                        points="{{ $missPoints }} 800,210 0,210"
                        fill="#f04455"
                        opacity=".06"></polygon>

                    <polyline
                        points="{{ $missPoints }}"
                        fill="none"
                        stroke="#f04455"
                        stroke-width="4"
                        stroke-linecap="round"
                        stroke-linejoin="round"></polyline>

                    @endif

                </svg>


                <div class="axis">

                    @forelse($trendCollection as $index => $item)

                    @if($index % 4 === 0)

                    <span>
                        {{ $item['hour'] ?? '-' }}
                    </span>

                    @endif

                    @empty

                    <span>00:00</span>
                    <span>04:00</span>
                    <span>08:00</span>
                    <span>12:00</span>
                    <span>16:00</span>
                    <span>20:00</span>

                    @endforelse

                </div>

            </div>


            <div class="legend">

                <span>
                    <i style="background:#12b76a"></i>
                    HIT
                </span>

                <span>
                    <i style="background:#f04455"></i>
                    MISS
                </span>

            </div>


            <div class="mc-note">

                @if(
                $responseDifference !== null
                &&
                $responseImprovement !== null
                )

                Selisih rata-rata response:
                <strong>
                    {{ number_format(
                            (float) $responseDifference,
                            2,
                            ',',
                            '.'
                        ) }} ms
                </strong>.

                Cache HIT tercatat sekitar
                <strong>
                    {{ number_format(
                            (float) $responseImprovement,
                            2,
                            ',',
                            '.'
                        ) }}%
                </strong>
                lebih cepat dibanding MISS pada data periode ini.

                @else

                Belum tersedia pasangan data HIT dan MISS yang cukup
                untuk menghitung perbandingan response time.

                @endif

            </div>

        </div>


        {{-- TOP CACHE DOMAINS --}}
        <div class="panel">

            <div class="panel-title">
                ◎ Domain dengan Aktivitas Cache
            </div>


            <div
                class="ranking"
                style="margin-top:14px">

                @forelse($topDomainsCollection as $domain)

                @php
                $domainRequest =
                (int) ($domain['total_request'] ?? 0);

                $barWidth =
                $maxDomainRequest > 0
                ? (
                $domainRequest
                /
                $maxDomainRequest
                )
                *
                100
                : 0;
                @endphp


                <div class="rank-row">

                    <div
                        class="mc-domain"
                        title="{{ $domain['domain'] ?? '-' }}">
                        {{ $domain['domain'] ?? '-' }}
                    </div>


                    <div class="progress green">

                        <i
                            style="
                                    width:
                                    {{ min(100, max(0, $barWidth)) }}%
                                "></i>

                    </div>


                    <b>
                        {{ number_format(
                                (float) ($domain['hit_ratio'] ?? 0),
                                1,
                                ',',
                                '.'
                            ) }}%
                    </b>

                </div>

                @empty

                <div class="mc-empty">
                    Belum ada domain cache pada periode ini.
                </div>

                @endforelse

            </div>


            <div class="mc-note">
                Persentase di sisi kanan merupakan
                <strong>hit ratio per domain</strong>,
                bukan persentase seluruh trafik internet.
            </div>

        </div>


        {{-- HIT RATIO --}}
        <div class="panel">

            <div class="panel-title">
                ◕ Rasio Cache Hit
            </div>


            <div class="mc-donut-wrap">

                <div class="mc-donut"></div>

                <div class="mc-donut-center">

                    <b>
                        {{ number_format(
                            $hitRatio,
                            1,
                            ',',
                            '.'
                        ) }}%
                    </b>

                    <small>
                        Hit Ratio
                    </small>

                </div>

            </div>


            <div class="mc-note">
                HIT/MISS dan response time hanya dihitung
                dari transaksi cache Squid dengan kategori
                <strong>CACHE_HIT</strong> dan
                <strong>CACHE_MISS</strong>.
            </div>

        </div>

    </div>


    {{-- ===================================================== --}}
    {{-- OBJECT COMPARISONS --}}
    {{-- ===================================================== --}}

    <div class="section-pad">

        <div class="panel">

            <div class="panel-head">

                <div class="panel-title">
                    ◫ Objek dengan Data HIT dan MISS
                </div>

                <span
                    style="
                        color:#8391a5;
                        font-size:10px;
                        font-weight:700;
                    ">
                    Bukti pembanding cache
                </span>

            </div>


            <div class="mc-object-list">

                @forelse($objectComparisonCollection as $object)

                @php
                $totalObjectRequest =
                (int) ($object['hit_count'] ?? 0)
                +
                (int) ($object['miss_count'] ?? 0);

                $objectHitRatio =
                $totalObjectRequest > 0
                ? (
                (int) ($object['hit_count'] ?? 0)
                /
                $totalObjectRequest
                )
                *
                100
                : 0;

                $objectName =
                $shortObjectName(
                $object['url'] ?? null,
                $object['domain'] ?? null
                );
                @endphp


                <div class="mc-object-row">

                    <div class="mc-object-top">

                        <span title="{{ $object['url'] ?? $objectName }}">
                            {{ $objectName }}
                        </span>

                        <b>
                            HIT
                            {{ (int) ($object['hit_count'] ?? 0) }}
                            ·
                            MISS
                            {{ (int) ($object['miss_count'] ?? 0) }}
                        </b>

                    </div>


                    <div class="mc-track">

                        <i
                            style="
                                    width:
                                    {{ min(
                                        100,
                                        max(
                                            0,
                                            $objectHitRatio
                                        )
                                    ) }}%
                                "></i>

                    </div>


                    <div
                        style="
                                color:#8290a3;
                                font-size:9px;
                            ">
                        Avg HIT:
                        {{ number_format(
                                (float) ($object['avg_hit_ms'] ?? 0),
                                2,
                                ',',
                                '.'
                            ) }} ms

                        ·

                        Avg MISS:
                        {{ number_format(
                                (float) ($object['avg_miss_ms'] ?? 0),
                                2,
                                ',',
                                '.'
                            ) }} ms

                        @if(
                        ($object['improvement_percent'] ?? null)
                        !== null
                        )
                        ·
                        Improvement:
                        {{ number_format(
                                    (float) $object['improvement_percent'],
                                    2,
                                    ',',
                                    '.'
                                ) }}%
                        @endif
                    </div>

                </div>

                @empty

                <div class="mc-empty">
                    Belum ada objek yang mempunyai data
                    CACHE_HIT dan CACHE_MISS sekaligus.
                </div>

                @endforelse

            </div>

        </div>

    </div>


    {{-- ===================================================== --}}
    {{-- RECENT CACHE ACTIVITY --}}
    {{-- ===================================================== --}}

    <div
        class="section-pad"
        style="margin-top:12px">

        <div class="panel">

            <div class="panel-head">

                <div class="panel-title">
                    ◴ Aktivitas Cache Terbaru
                </div>

                <span
                    style="
                        color:#8391a5;
                        font-size:10px;
                        font-weight:700;
                    ">
                    12 aktivitas terbaru
                </span>

            </div>


            <div class="table-wrap">

                <table class="table">

                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Jenis</th>
                            <th>Objek / Domain</th>
                            <th>IP Client</th>
                            <th>Response Time</th>
                            <th>Ukuran Data</th>
                            <th>Sumber</th>
                            <th>Squid Result</th>
                        </tr>
                    </thead>


                    <tbody>

                        @forelse($recentCollection as $activity)

                        @php
                        $isHit =
                        $activity->category
                        === 'CACHE_HIT';

                        $objectLabel =
                        $shortObjectName(
                        $activity->url,
                        $activity->domain
                        );
                        @endphp


                        <tr>

                            <td>
                                {{ $activity->logged_at?->format('H:i:s') ?? '-' }}
                            </td>


                            <td>

                                @if($isHit)

                                <span class="badge ok">
                                    HIT
                                </span>

                                @else

                                <span class="badge bad">
                                    MISS
                                </span>

                                @endif

                            </td>


                            <td>

                                <div
                                    class="mc-url"
                                    title="{{
                                            $activity->url
                                            ?: $activity->domain
                                            ?: '-'
                                        }}">
                                    {{ $objectLabel }}
                                </div>

                            </td>


                            <td>
                                {{ $activity->client_ip ?: '-' }}
                            </td>


                            <td>
                                @if($activity->elapsed_ms !== null)
                                {{ number_format(
                                            (float) $activity->elapsed_ms,
                                            2,
                                            ',',
                                            '.'
                                        ) }} ms
                                @else
                                -
                                @endif
                            </td>


                            <td>
                                {{ $formatBytes(
                                        $activity->bytes
                                        ?? 0
                                    ) }}
                            </td>


                            <td>
                                {{ $isHit
                                        ? 'Cache Lokal'
                                        : 'Origin Server'
                                    }}
                            </td>


                            <td>
                                <span class="badge blue">
                                    {{ $activity->squid_code ?: '-' }}
                                </span>
                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td
                                colspan="8"
                                style="
                                        text-align:center;
                                        padding:36px;
                                    ">
                                Belum ada aktivitas cache.
                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection