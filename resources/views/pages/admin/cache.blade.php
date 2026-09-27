@extends('layouts.admin')

@section('title', 'Cache')

@section('content')

@php
$hit = (int) ($summary['hit'] ?? 0);
$miss = (int) ($summary['miss'] ?? 0);
$totalCache = (int) ($summary['total'] ?? 0);

$hitRatio = (float) ($summary['hit_ratio'] ?? 0);

$avgHit = $summary['avg_hit_ms'] ?? null;
$avgMiss = $summary['avg_miss_ms'] ?? null;

$difference = $summary['response_difference_ms'] ?? null;
$improvement = $summary['response_improvement_percent'] ?? null;

$hitBytes = (int) ($summary['hit_bytes'] ?? 0);
$missBytes = (int) ($summary['miss_bytes'] ?? 0);

$now = now('Asia/Jakarta');

$cacheTrendData = collect($trend ?? [])
->values()
->all();

$maxDomainRequest = max(
1,
(int) (
collect($topDomains ?? [])
->max('total_request')
?? 0
)
);

$formatBytes = function ($bytes) {

$bytes = (int) $bytes;

if ($bytes >= 1073741824) {
return number_format(
$bytes / 1073741824,
2,
',',
'.'
) . ' GB';
}

if ($bytes >= 1048576) {
return number_format(
$bytes / 1048576,
2,
',',
'.'
) . ' MB';
}

if ($bytes >= 1024) {
return number_format(
$bytes / 1024,
2,
',',
'.'
) . ' KB';
}

return number_format($bytes) . ' B';
};
@endphp


{{-- ========================================================= --}}
{{-- HERO --}}
{{-- ========================================================= --}}

<section class="hero">

    <div class="hero-bg"></div>

    <div class="hero-copy">

        <div class="eyebrow">
            ITBRP NETWORK MONITORING
        </div>

        <h1>
            Cache Jaringan
        </h1>

        <p>
            Pantau efisiensi proxy cache, HIT/MISS,
            penggunaan data, dan response time objek cache.
        </p>

        <div class="hero-chips">

            <span class="hero-chip">
                <span class="chip-icon green">●</span>
                Cache Monitoring
            </span>

            <span class="hero-chip">
                <span class="chip-icon">◆</span>
                Analisis HIT & MISS
            </span>

            <span class="hero-chip">
                <span class="chip-icon purple">▥</span>
                Data Real-time
            </span>

        </div>

    </div>


    <div class="hero-status">
        <span class="status-dot"></span>
        Monitoring Cache Aktif
    </div>


    <div class="hero-clock">

        <small>
            {{ $now->translatedFormat('l, d F Y') }}
        </small>

        <b>
            {{ $now->format('H:i') }}
        </b>

        <span>
            WIB
        </span>

    </div>

</section>



{{-- ========================================================= --}}
{{-- SUMMARY --}}
{{-- ========================================================= --}}

<div class="stats">


    {{-- HIT --}}
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
                    {{ number_format(
                        $hit,
                        0,
                        ',',
                        '.'
                    ) }}
                </div>

            </div>

        </div>

        <div class="stat-foot">
            Objek dilayani dari cache
        </div>

    </div>



    {{-- MISS --}}
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
                    {{ number_format(
                        $miss,
                        0,
                        ',',
                        '.'
                    ) }}
                </div>

            </div>

        </div>

        <div class="stat-foot">
            Objek diambil dari origin
        </div>

    </div>



    {{-- HIT RATIO --}}
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
                    {{ number_format(
                        $hitRatio,
                        1,
                        ',',
                        '.'
                    ) }}%
                </div>

            </div>

        </div>

        <div class="stat-foot">
            Dari total HIT + MISS
        </div>

    </div>



    {{-- AVG HIT --}}
    <div class="stat-card blue">

        <div class="stat-top">

            <div class="stat-icon">
                ◴
            </div>

            <div>

                <div class="stat-label">
                    Avg HIT
                </div>

                <div class="stat-value">

                    @if($avgHit !== null)

                    {{ number_format(
                            $avgHit,
                            0,
                            ',',
                            '.'
                        ) }}
                    ms

                    @else

                    -

                    @endif

                </div>

            </div>

        </div>

        <div class="stat-foot">
            Response objek cache
        </div>

    </div>



    {{-- AVG MISS --}}
    <div class="stat-card orange">

        <div class="stat-top">

            <div class="stat-icon">
                ◴
            </div>

            <div>

                <div class="stat-label">
                    Avg MISS
                </div>

                <div class="stat-value">

                    @if($avgMiss !== null)

                    {{ number_format(
                            $avgMiss,
                            0,
                            ',',
                            '.'
                        ) }}
                    ms

                    @else

                    -

                    @endif

                </div>

            </div>

        </div>

        <div class="stat-foot">

            @if($improvement !== null)

            HIT
            {{ number_format(
                    $improvement,
                    1,
                    ',',
                    '.'
                ) }}%
            lebih cepat

            @else

            Menunggu data pembanding

            @endif

        </div>

    </div>



    {{-- HIT DATA --}}
    <div class="stat-card teal">

        <div class="stat-top">

            <div class="stat-icon">
                ▤
            </div>

            <div>

                <div class="stat-label">
                    Data Cache HIT
                </div>

                <div class="stat-value">
                    {{ $formatBytes($hitBytes) }}
                </div>

            </div>

        </div>

        <div class="stat-foot">
            Data yang tercatat sebagai HIT
        </div>

    </div>

</div>



{{-- ========================================================= --}}
{{-- CACHE CHART + DOMAIN + RATIO --}}
{{-- ========================================================= --}}

<div class="grid three">


    {{-- CHART --}}
    <div class="panel">

        <div class="panel-head">

            <div class="panel-title">
                ▥ Perbandingan Cache HIT dan MISS
            </div>

            <select class="tiny-select">
                <option>
                    24 Jam Terakhir
                </option>
            </select>

        </div>


        <div class="chart">

            <div class="chart-grid"></div>

            <svg
                viewBox="0 0 800 210"
                preserveAspectRatio="none">

                {{-- HIT AREA --}}
                <polygon
                    id="cacheHitArea"
                    fill="#12b76a"
                    opacity=".07"></polygon>

                {{-- HIT LINE --}}
                <polyline
                    id="cacheHitLine"
                    fill="none"
                    stroke="#12b76a"
                    stroke-width="4"
                    stroke-linecap="round"
                    stroke-linejoin="round"></polyline>


                {{-- MISS AREA --}}
                <polygon
                    id="cacheMissArea"
                    fill="#f04455"
                    opacity=".07"></polygon>

                {{-- MISS LINE --}}
                <polyline
                    id="cacheMissLine"
                    fill="none"
                    stroke="#f04455"
                    stroke-width="4"
                    stroke-linecap="round"
                    stroke-linejoin="round"></polyline>

            </svg>


            <div class="axis">

                @foreach($trend as $index => $item)

                @if($index % 4 === 0)

                <span>
                    {{ $item['hour'] }}
                </span>

                @endif

                @endforeach

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

    </div>



    {{-- TOP DOMAIN CACHE --}}
    <div class="panel">

        <div class="panel-title">
            ◎ Top Domain Cache
        </div>


        <div
            class="ranking"
            style="margin-top:14px">

            @forelse($topDomains as $domain)

            @php
            $width = $maxDomainRequest > 0
            ? (
            $domain['total_request']
            /
            $maxDomainRequest
            ) * 100
            : 0;
            @endphp


            <div class="rank-row">

                <div
                    title="{{ $domain['domain'] }}">
                    {{ $domain['domain'] }}
                </div>


                <div class="progress green">

                    <i
                        style="
                                width:
                                {{ min(
                                    $width,
                                    100
                                ) }}%
                            "></i>

                </div>


                <b>
                    {{ number_format(
                            $domain['hit_ratio'],
                            1,
                            ',',
                            '.'
                        ) }}%
                </b>

            </div>


            @empty

            <div
                style="
                        text-align:center;
                        padding:30px;
                        color:#7b8794;
                    ">
                Belum ada data cache domain.
            </div>

            @endforelse

        </div>

    </div>



    {{-- HIT RATIO --}}
    <div class="panel">

        <div class="panel-title">
            ◕ Rasio Cache Hit
        </div>


        <div
            style="
                position:relative;
                margin-top:14px;
            ">

            <div
                class="donut"
                style="
                    background:
                    conic-gradient(
                        #12b76a 0
                        {{ min(
                            $hitRatio,
                            100
                        ) }}%,
                        #eef3f7
                        {{ min(
                            $hitRatio,
                            100
                        ) }}%
                        100%
                    )
                "></div>


            <div class="donut-center">

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


        <div
            class="kv-list"
            style="margin-top:12px">

            <div class="kv-row">

                <span>
                    Cache HIT
                </span>

                <b>
                    {{ number_format(
                        $hit,
                        0,
                        ',',
                        '.'
                    ) }}
                </b>

            </div>


            <div class="kv-row">

                <span>
                    Cache MISS
                </span>

                <b>
                    {{ number_format(
                        $miss,
                        0,
                        ',',
                        '.'
                    ) }}
                </b>

            </div>


            <div class="kv-row">

                <span>
                    Total
                </span>

                <b>
                    {{ number_format(
                        $totalCache,
                        0,
                        ',',
                        '.'
                    ) }}
                </b>

            </div>


            <div class="kv-row">

                <span>
                    Data HIT
                </span>

                <b>
                    {{ $formatBytes(
                        $hitBytes
                    ) }}
                </b>

            </div>


            <div class="kv-row">

                <span>
                    Data MISS
                </span>

                <b>
                    {{ $formatBytes(
                        $missBytes
                    ) }}
                </b>

            </div>

        </div>

    </div>

</div>



{{-- ========================================================= --}}
{{-- OBJECT COMPARISON --}}
{{-- ========================================================= --}}

<div style="margin-top:12px">

    <div class="panel">

        <div class="panel-head">

            <div class="panel-title">
                ⚡ Perbandingan Response Time Objek
            </div>

        </div>


        <div class="table-wrap">

            <table class="table">

                <thead>

                    <tr>
                        <th>Objek / URL</th>
                        <th>Domain</th>
                        <th>HIT</th>
                        <th>MISS</th>
                        <th>Avg HIT</th>
                        <th>Avg MISS</th>
                        <th>Selisih</th>
                        <th>Peningkatan</th>
                    </tr>

                </thead>


                <tbody>

                    @forelse($objectComparisons as $object)

                    <tr>

                        <td
                            title="{{ $object['url'] }}">
                            {{
                                \Illuminate\Support\Str::limit(
                                    $object['url'],
                                    55
                                )
                            }}
                        </td>


                        <td>
                            {{ $object['domain'] ?? '-' }}
                        </td>


                        <td>
                            <span class="badge ok">
                                {{ $object['hit_count'] }}
                            </span>
                        </td>


                        <td>
                            <span class="badge bad">
                                {{ $object['miss_count'] }}
                            </span>
                        </td>


                        <td>
                            {{ number_format(
                                $object['avg_hit_ms'],
                                2,
                                ',',
                                '.'
                            ) }}
                            ms
                        </td>


                        <td>
                            {{ number_format(
                                $object['avg_miss_ms'],
                                2,
                                ',',
                                '.'
                            ) }}
                            ms
                        </td>


                        <td>
                            {{ number_format(
                                $object['difference_ms'],
                                2,
                                ',',
                                '.'
                            ) }}
                            ms
                        </td>


                        <td>

                            @if(
                            $object[
                            'improvement_percent'
                            ] !== null
                            )

                            <span class="badge ok">

                                {{
                                        number_format(
                                            $object[
                                                'improvement_percent'
                                            ],
                                            2,
                                            ',',
                                            '.'
                                        )
                                    }}%

                            </span>

                            @else

                            -

                            @endif

                        </td>

                    </tr>


                    @empty

                    <tr>

                        <td
                            colspan="8"
                            style="
                                padding:30px;
                                text-align:center;
                            ">
                            Belum ada pasangan objek
                            MISS → HIT yang dapat dibandingkan.
                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>



{{-- ========================================================= --}}
{{-- RECENT CACHE ACTIVITIES --}}
{{-- ========================================================= --}}

<div style="margin-top:12px">

    <div class="panel">

        <div class="panel-head">

            <div class="panel-title">
                ◴ Aktivitas Cache Terbaru
            </div>

        </div>


        <div class="table-wrap">

            <table class="table">

                <thead>

                    <tr>
                        <th>Waktu</th>
                        <th>Jenis</th>
                        <th>Domain / Objek</th>
                        <th>IP Client</th>
                        <th>Response Time</th>
                        <th>Ukuran Data</th>
                        <th>Sumber</th>
                    </tr>

                </thead>


                <tbody>

                    @forelse($recentActivities as $log)

                    <tr>

                        <td>
                            {{
                                $log->logged_at
                                    ?->format(
                                        'd/m/Y H:i:s'
                                    )
                                ?? '-'
                            }}
                        </td>


                        <td>

                            @if(
                            $log->category
                            === 'CACHE_HIT'
                            )

                            <span class="badge ok">
                                HIT
                            </span>

                            @else

                            <span class="badge bad">
                                MISS
                            </span>

                            @endif

                        </td>


                        <td
                            title="{{ $log->url }}">

                            {{
                                $log->domain
                                ??
                                \Illuminate\Support\Str::limit(
                                    $log->url,
                                    45
                                )
                                ??
                                '-'
                            }}

                        </td>


                        <td>
                            {{ $log->client_ip }}
                        </td>


                        <td>
                            {{ number_format(
                                $log->elapsed_ms,
                                0,
                                ',',
                                '.'
                            ) }}
                            ms
                        </td>


                        <td>
                            {{ $formatBytes(
                                $log->bytes
                            ) }}
                        </td>


                        <td>

                            @if(
                            $log->category
                            === 'CACHE_HIT'
                            )

                            Cache Lokal

                            @else

                            Origin Server

                            @endif

                        </td>

                    </tr>


                    @empty

                    <tr>

                        <td
                            colspan="7"
                            style="
                                padding:30px;
                                text-align:center;
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



{{-- ========================================================= --}}
{{-- CACHE CHART --}}
{{-- ========================================================= --}}

<script>
    document.addEventListener(
        'DOMContentLoaded',
        function() {

            const cacheTrend = @json($cacheTrendData);

            if (!cacheTrend.length) {
                return;
            }

            const width = 800;
            const height = 210;

            const topPadding = 20;
            const bottomPadding = 20;

            const usableHeight =
                height -
                topPadding -
                bottomPadding;


            const maxValue = Math.max(
                1,

                ...cacheTrend.map(
                    item =>
                    Math.max(
                        Number(
                            item.hit || 0
                        ),
                        Number(
                            item.miss || 0
                        )
                    )
                )
            );


            const step =
                cacheTrend.length > 1

                ?
                width /
                (
                    cacheTrend.length - 1
                )

                :
                width;


            function createPoints(field) {

                return cacheTrend
                    .map(
                        (item, index) => {

                            const x =
                                index * step;

                            const value =
                                Number(
                                    item[field] || 0
                                );

                            const y =
                                height -
                                bottomPadding -
                                (
                                    (
                                        value /
                                        maxValue
                                    ) *
                                    usableHeight
                                );

                            return (
                                x.toFixed(1) +
                                ',' +
                                y.toFixed(1)
                            );

                        }
                    )
                    .join(' ');
            }


            const hitPoints =
                createPoints('hit');

            const missPoints =
                createPoints('miss');


            const hitLine =
                document.getElementById(
                    'cacheHitLine'
                );

            const missLine =
                document.getElementById(
                    'cacheMissLine'
                );

            const hitArea =
                document.getElementById(
                    'cacheHitArea'
                );

            const missArea =
                document.getElementById(
                    'cacheMissArea'
                );


            if (hitLine) {

                hitLine.setAttribute(
                    'points',
                    hitPoints
                );

            }


            if (missLine) {

                missLine.setAttribute(
                    'points',
                    missPoints
                );

            }


            if (hitArea) {

                hitArea.setAttribute(
                    'points',

                    `0,${height} ` +
                    hitPoints +
                    ` ${width},${height}`
                );

            }


            if (missArea) {

                missArea.setAttribute(
                    'points',

                    `0,${height} ` +
                    missPoints +
                    ` ${width},${height}`
                );

            }

        }
    );
</script>

@endsection