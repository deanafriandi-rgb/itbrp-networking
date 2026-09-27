@extends('layouts.monitoring')

@section('title', 'Akses Jaringan')

@section('content')

@php
/*
|--------------------------------------------------------------------------
| Summary 24 Jam
|--------------------------------------------------------------------------
*/

$totalRequest =
(int) ($summary['total_request'] ?? 0);

$allowed =
(int) ($summary['allowed'] ?? 0);

$blocked =
(int) ($summary['blocked'] ?? 0);

$failed =
(int) ($summary['failed'] ?? 0);

$uniqueClients =
(int) ($summary['unique_clients'] ?? 0);

$totalBytes =
(int) ($summary['total_bytes'] ?? 0);

$now =
now('Asia/Jakarta');


/*
|--------------------------------------------------------------------------
| Traffic
|--------------------------------------------------------------------------
*/

$trafficCollection =
collect(
$traffic ?? []
)->values();

$maxTrafficValue =
max(
1,
(int) (
$trafficCollection
->max(
function ($item) {
return max(
(int) ($item['total_request'] ?? 0),
(int) ($item['allowed'] ?? 0)
);
}
)
?? 0
)
);

$trafficCount =
$trafficCollection->count();

$trafficStep =
$trafficCount > 1
? 800 / ($trafficCount - 1)
: 800;

$totalPointList = [];
$allowedPointList = [];

foreach (
$trafficCollection
as $index => $item
) {
$x =
$index * $trafficStep;

$totalValue =
(int) ($item['total_request'] ?? 0);

$allowedValue =
(int) ($item['allowed'] ?? 0);

$totalY =
190
-
(
($totalValue / $maxTrafficValue)
* 170
);

$allowedY =
190
-
(
($allowedValue / $maxTrafficValue)
* 170
);

$totalPointList[] =
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
$totalY,
1,
'.',
''
);

$allowedPointList[] =
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
$allowedY,
1,
'.',
''
);
}

$totalPoints =
implode(
' ',
$totalPointList
);

$allowedPoints =
implode(
' ',
$allowedPointList
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
->take(8)
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
| Latest Activity
|--------------------------------------------------------------------------
*/

$activityCollection =
collect(
$latestActivities ?? []
)
->take(10)
->values();


/*
|--------------------------------------------------------------------------
| Helper
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
@endphp


<style>
    .monitoring-access {
        --ma-text: #17385f;
        --ma-muted: #78879b;
        --ma-border: #dce7f2;
    }

    .monitoring-access * {
        box-sizing: border-box;
    }

    .ma-empty {
        padding: 34px 18px;
        text-align: center;
        color: #8190a3;
        font-size: 11px;
        line-height: 1.7;
    }

    .ma-readonly {
        height: 100%;
        min-height: 250px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 24px;
        border-radius: 18px;
        border: 1px solid #dce8f5;
        background:
            radial-gradient(circle at 80% 20%,
                rgba(23, 133, 248, .14),
                transparent 35%),
            linear-gradient(145deg,
                #f7fbff,
                #eef6ff);
    }

    .ma-readonly-tag {
        width: fit-content;
        margin-bottom: 12px;
        padding: 6px 9px;
        border-radius: 999px;
        background: #fff;
        color: #1680ea;
        border: 1px solid #dce9f7;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .05em;
    }

    .ma-readonly h3 {
        margin: 0;
        color: #0e3265;
        font-size: 20px;
        line-height: 1.35;
    }

    .ma-readonly p {
        margin: 9px 0 0;
        color: #58708e;
        font-size: 11px;
        line-height: 1.8;
    }

    .ma-domain {
        max-width: 230px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    @media (max-width: 850px) {
        .ma-access-bottom {
            grid-template-columns: 1fr !important;
        }
    }
</style>


<div class="monitoring-access">

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
                Akses Jaringan
                <br>

                <span class="accent">
                    yang Aman dan Produktif
                </span>
            </h1>

            <p>
                Pantau aktivitas akses internet berdasarkan access log
                Squid untuk mendukung kegiatan akademik, riset,
                dan layanan digital di lingkungan ITBRP.
            </p>

            <div class="hero-chips">

                <span class="hero-chip">
                    <span class="chip-icon green">▣</span>
                    Access Log
                </span>

                <span class="hero-chip">
                    <span class="chip-icon">◆</span>
                    Filtering Terpantau
                </span>

                <span class="hero-chip">
                    <span class="chip-icon purple">▥</span>
                    Data 24 Jam
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

        <div class="stat-card blue">

            <div class="stat-top">

                <div class="stat-icon">
                    ◎
                </div>

                <div>
                    <div class="stat-label">
                        Total Akses
                    </div>

                    <div class="stat-value">
                        {{ number_format($totalRequest, 0, ',', '.') }}
                    </div>
                </div>

            </div>

            <div class="stat-foot">
                Request 24 jam terakhir
            </div>

        </div>


        <div class="stat-card green">

            <div class="stat-top">

                <div class="stat-icon">
                    ◆
                </div>

                <div>
                    <div class="stat-label">
                        Akses Diizinkan
                    </div>

                    <div class="stat-value">
                        {{ number_format($allowed, 0, ',', '.') }}
                    </div>
                </div>

            </div>

            <div class="stat-foot">
                Request yang tidak diblokir
            </div>

        </div>


        <div class="stat-card red">

            <div class="stat-top">

                <div class="stat-icon">
                    ⊘
                </div>

                <div>
                    <div class="stat-label">
                        Akses Diblokir
                    </div>

                    <div class="stat-value">
                        {{ number_format($blocked, 0, ',', '.') }}
                    </div>
                </div>

            </div>

            <div class="stat-foot">
                Terkena kebijakan filtering
            </div>

        </div>


        <div class="stat-card purple">

            <div class="stat-top">

                <div class="stat-icon">
                    ◉
                </div>

                <div>
                    <div class="stat-label">
                        Pengguna Aktif
                    </div>

                    <div class="stat-value">
                        {{ number_format($uniqueClients, 0, ',', '.') }}
                    </div>
                </div>

            </div>

            <div class="stat-foot">
                IP client unik
            </div>

        </div>


        <div class="stat-card orange">

            <div class="stat-top">

                <div class="stat-icon">
                    ▤
                </div>

                <div>
                    <div class="stat-label">
                        Penggunaan Data
                    </div>

                    <div
                        class="stat-value"
                        style="font-size:22px">
                        {{ $formatBytes($totalBytes) }}
                    </div>
                </div>

            </div>

            <div class="stat-foot">
                Data tercatat pada access log
            </div>

        </div>

    </div>


    {{-- ===================================================== --}}
    {{-- TREND + TOP DOMAIN --}}
    {{-- ===================================================== --}}

    <div class="grid two section-pad">

        {{-- TREND --}}
        <div class="panel">

            <div class="panel-head">

                <div>
                    <div class="panel-title">
                        ▥ Tren Akses Jaringan
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
                    aria-label="Tren akses jaringan 24 jam">

                    @if($trafficCollection->isNotEmpty())

                    <polygon
                        points="{{ $totalPoints }} 800,210 0,210"
                        fill="#267ff5"
                        opacity=".07"></polygon>

                    <polyline
                        points="{{ $totalPoints }}"
                        fill="none"
                        stroke="#267ff5"
                        stroke-width="4"
                        stroke-linecap="round"
                        stroke-linejoin="round"></polyline>


                    <polygon
                        points="{{ $allowedPoints }} 800,210 0,210"
                        fill="#12b76a"
                        opacity=".06"></polygon>

                    <polyline
                        points="{{ $allowedPoints }}"
                        fill="none"
                        stroke="#12b76a"
                        stroke-width="4"
                        stroke-linecap="round"
                        stroke-linejoin="round"></polyline>

                    @endif

                </svg>


                <div class="axis">

                    @forelse($trafficCollection as $index => $item)

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
                    <i style="background:#1e86fa"></i>
                    Total Request
                </span>

                <span>
                    <i style="background:#12b76a"></i>
                    Diizinkan
                </span>

            </div>

        </div>


        {{-- TOP DOMAIN --}}
        <div class="panel">

            <div class="panel-head">

                <div class="panel-title">
                    ◎ Top Domain yang Diakses
                </div>

                <select
                    class="tiny-select"
                    disabled>
                    <option>
                        24 Jam
                    </option>
                </select>

            </div>


            <div class="ranking">

                @forelse($topDomainsCollection as $domain)

                @php
                $domainRequest =
                (int) ($domain['total_request'] ?? 0);

                $percentage =
                $totalRequest > 0
                ? ($domainRequest / $totalRequest) * 100
                : 0;

                $barWidth =
                $maxDomainRequest > 0
                ? ($domainRequest / $maxDomainRequest) * 100
                : 0;

                $domainName =
                $domain['domain'] ?? '-';

                $domainInitial =
                $domainName !== '-'
                ? strtoupper(
                substr(
                $domainName,
                0,
                1
                )
                )
                : '?';
                @endphp


                <div class="rank-row">

                    <div class="rank-name">

                        <span class="brand-dot">
                            {{ $domainInitial }}
                        </span>

                        <span title="{{ $domainName }}">
                            {{ \Illuminate\Support\Str::limit($domainName, 26) }}
                        </span>

                    </div>


                    <div class="progress">

                        <i
                            style="
                                    width:
                                    {{ min(100, max(0, $barWidth)) }}%
                                "></i>

                    </div>


                    <b>
                        {{ number_format($percentage, 1, ',', '.') }}%
                    </b>

                </div>

                @empty

                <div class="ma-empty">
                    Belum ada data domain pada periode
                    24 jam terakhir.
                </div>

                @endforelse

            </div>

        </div>

    </div>


    {{-- ===================================================== --}}
    {{-- LATEST ACCESS + READ ONLY INFO --}}
    {{-- ===================================================== --}}

    <div
        class="grid two section-pad ma-access-bottom"
        style="
            margin-top:12px;
            grid-template-columns:2.2fr 1fr;
        ">

        <div class="panel">

            <div class="panel-head">

                <div class="panel-title">
                    ▧ Akses Jaringan Terbaru
                </div>

                <span
                    style="
                        color:#8391a5;
                        font-size:10px;
                        font-weight:700;
                    ">
                    10 aktivitas terbaru
                </span>

            </div>


            <div class="table-wrap">

                <table class="table">

                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>IP Client</th>
                            <th>Domain / Tujuan</th>
                            <th>Protokol</th>
                            <th>Hasil</th>
                            <th>Squid Result</th>
                        </tr>
                    </thead>


                    <tbody>

                        @forelse($activityCollection as $log)

                        @php
                        $resultClass =
                        match($log->category) {
                        'BLOCKED' => 'bad',
                        'CACHE_HIT' => 'ok',
                        'CACHE_MISS' => 'orange',
                        'ALLOWED' => 'ok',
                        'FAILED' => 'bad',
                        default => 'blue',
                        };

                        $resultLabel =
                        match($log->category) {
                        'BLOCKED' => 'Diblokir',
                        'CACHE_HIT' => 'Cache HIT',
                        'CACHE_MISS' => 'Cache MISS',
                        'ALLOWED' => 'Diizinkan',
                        'FAILED' => 'Gagal',
                        default => 'Lainnya',
                        };
                        @endphp


                        <tr>

                            <td>
                                {{ $log->logged_at?->format('H:i:s') ?? '-' }}
                            </td>


                            <td>
                                {{ $log->client_ip ?: '-' }}
                            </td>


                            <td>
                                <div
                                    class="ma-domain"
                                    title="{{
                                            $log->domain
                                            ?? $log->destination_ip
                                            ?? '-'
                                        }}">
                                    {{
                                            $log->domain
                                            ?? $log->destination_ip
                                            ?? '-'
                                        }}
                                </div>
                            </td>


                            <td>

                                <span class="badge blue">
                                    {{
                                            $log->protocol
                                            ?? $log->method
                                            ?? '-'
                                        }}
                                </span>

                            </td>


                            <td>

                                <span
                                    class="badge {{ $resultClass }}">
                                    {{ $resultLabel }}
                                </span>

                            </td>


                            <td>

                                <span class="badge blue">
                                    {{ $log->squid_code ?? '-' }}
                                </span>

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td
                                colspan="6"
                                style="
                                        text-align:center;
                                        padding:34px;
                                    ">
                                Belum ada aktivitas akses jaringan.
                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        <div class="ma-readonly">

            <div class="ma-readonly-tag">
                READ-ONLY
            </div>

            <h3>
                Monitoring aktivitas
                akses internet
            </h3>

            <p>
                Informasi pada halaman ini berasal dari access log
                Squid. User dan dosen hanya dapat melihat data.
                Perubahan aturan filtering tetap dilakukan melalui
                dashboard administrator.
            </p>

            <p>
                Failed:
                <strong>
                    {{ number_format($failed, 0, ',', '.') }}
                </strong>
                request dalam 24 jam terakhir.
            </p>

        </div>

    </div>

</div>

@endsection