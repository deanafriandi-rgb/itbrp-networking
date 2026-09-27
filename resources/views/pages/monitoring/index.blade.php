@extends('layouts.monitoring')

@section('title', 'Beranda')

@section('content')

@php
/*
|--------------------------------------------------------------------------
| Summary
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

$cacheHit =
(int) ($summary['cache_hit'] ?? 0);

$cacheMiss =
(int) ($summary['cache_miss'] ?? 0);

$hitRatio =
(float) ($summary['hit_ratio'] ?? 0);

$totalBytes =
(int) ($summary['total_bytes'] ?? 0);


/*
|--------------------------------------------------------------------------
| Time
|--------------------------------------------------------------------------
*/

$now =
now('Asia/Jakarta');


/*
|--------------------------------------------------------------------------
| Traffic Chart
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
| Result Summary
|--------------------------------------------------------------------------
*/

$percentOfTotal =
function ($value) use ($totalRequest) {
if ($totalRequest <= 0) {
    return 0;
    }

    return round(
    (
    (int) $value
    /
    $totalRequest
    )
    *
    100,
    1
    );
    };


    $resultRows=[
    [ 'label'=> 'Request Diizinkan',
    'value' => $allowed,
    'percent' => $percentOfTotal($allowed),
    'note' => 'Termasuk trafik allowed dan cache.',
    ],
    [
    'label' => 'Request Diblokir',
    'value' => $blocked,
    'percent' => $percentOfTotal($blocked),
    'note' => 'Request yang terkena kebijakan filtering.',
    ],
    [
    'label' => 'Cache HIT',
    'value' => $cacheHit,
    'percent' => $hitRatio,
    'note' => 'Objek dilayani dari cache Squid.',
    ],
    [
    'label' => 'Cache MISS',
    'value' => $cacheMiss,
    'percent' => ($cacheHit + $cacheMiss) > 0
    ? round(
    (
    $cacheMiss
    /
    ($cacheHit + $cacheMiss)
    )
    *
    100,
    1
    )
    : 0,
    'note' => 'Objek perlu diambil dari origin.',
    ],
    [
    'label' => 'Request Gagal',
    'value' => $failed,
    'percent' => $percentOfTotal($failed),
    'note' => 'Request error / aborted yang tercatat.',
    ],
    ];


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    $formatBytes =
    function ($bytes) {
    $bytes = (int) $bytes;

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
        .monitoring-home {
            --mh-border: #dce7f2;
            --mh-muted: #738198;
            --mh-text: #17385f;
            --mh-title: #0c2c5e;
            --mh-soft: #f7faff;
        }

        .monitoring-home * {
            box-sizing: border-box;
        }

        .mh-empty {
            padding: 34px 16px;
            text-align: center;
            color: #8290a3;
            font-size: 11px;
            line-height: 1.7;
        }

        .mh-device-row {
            display: grid;
            grid-template-columns: 38px minmax(0, 1fr) auto;
            gap: 11px;
            align-items: center;
            padding: 11px 0;
            border-bottom: 1px solid #edf2f7;
        }

        .mh-device-row:last-child {
            border-bottom: 0;
        }

        .mh-device-icon {
            width: 36px;
            height: 36px;
            border-radius: 11px;
            display: grid;
            place-items: center;
            background: #eef6ff;
            color: #1785f8;
            font-size: 13px;
            font-weight: 900;
        }

        .mh-device-copy {
            min-width: 0;
        }

        .mh-device-copy b {
            display: block;
            overflow: hidden;
            color: #183a63;
            font-size: 11px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .mh-device-copy small {
            display: block;
            margin-top: 3px;
            overflow: hidden;
            color: #8794a7;
            font-size: 9px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .mh-state {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 8px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 800;
            white-space: nowrap;
        }

        .mh-state.online {
            color: #087a49;
            background: #eafaf3;
            border: 1px solid #ccefdc;
        }

        .mh-state.offline {
            color: #b52d4d;
            background: #fff0f3;
            border: 1px solid #ffd4de;
        }

        .mh-system-local {
            color: #6a4bd8;
        }

        .mh-system-warning {
            color: #ad7200;
        }

        .mh-system-danger {
            color: #c1264b;
        }

        .mh-system-success {
            color: #087a49;
        }

        .mh-table-note {
            color: #7c8a9e;
            font-size: 9px;
            line-height: 1.5;
        }

        .mh-readonly {
            height: 100%;
            min-height: 210px;
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

        .mh-readonly-tag {
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

        .mh-readonly h3 {
            margin: 0;
            color: #0e3265;
            font-size: 20px;
            line-height: 1.3;
        }

        .mh-readonly p {
            margin: 9px 0 0;
            color: #58708e;
            font-size: 11px;
            line-height: 1.8;
        }
    </style>


    <div class="monitoring-home">

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
                    Jaringan Andal
                    <br>

                    <span class="accent">
                        untuk Kampus yang Lebih Maju
                    </span>
                </h1>

                <p>
                    Monitoring penggunaan jaringan untuk mendukung aktivitas
                    akademik, riset, dan layanan digital di lingkungan ITBRP.
                </p>

                <div class="hero-chips">

                    <span class="hero-chip">
                        <span class="chip-icon green">●</span>
                        Monitoring Terpusat
                    </span>

                    <span class="hero-chip">
                        <span class="chip-icon">◆</span>
                        Akses Terkontrol
                    </span>

                    <span class="hero-chip">
                        <span class="chip-icon purple">▥</span>
                        Data Access Log
                    </span>

                </div>

            </div>


            <div class="hero-status">
                <span class="status-dot"></span>

                {{ $systemStatus['label'] ?? 'Monitoring' }}
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
                        ◉
                    </div>

                    <div>
                        <div class="stat-label">
                            Active Client
                        </div>

                        <div class="stat-value">
                            {{ number_format($uniqueClients, 0, ',', '.') }}
                        </div>
                    </div>

                </div>

                <div class="stat-foot">
                    IP client unik · 24 jam
                </div>

            </div>


            <div class="stat-card green">

                <div class="stat-top">

                    <div class="stat-icon">
                        ◎
                    </div>

                    <div>
                        <div class="stat-label">
                            Total Access
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


            <div class="stat-card red">

                <div class="stat-top">

                    <div class="stat-icon">
                        ⊘
                    </div>

                    <div>
                        <div class="stat-label">
                            Blocked Access
                        </div>

                        <div class="stat-value">
                            {{ number_format($blocked, 0, ',', '.') }}
                        </div>
                    </div>

                </div>

                <div class="stat-foot">
                    Diblokir oleh kebijakan filtering
                </div>

            </div>


            <div class="stat-card purple">

                <div class="stat-top">

                    <div class="stat-icon">
                        ▤
                    </div>

                    <div>
                        <div class="stat-label">
                            Cache Ratio
                        </div>

                        <div class="stat-value">
                            {{ number_format($hitRatio, 1, ',', '.') }}%
                        </div>
                    </div>

                </div>

                <div class="stat-foot">
                    HIT {{ number_format($cacheHit, 0, ',', '.') }}
                    ·
                    MISS {{ number_format($cacheMiss, 0, ',', '.') }}
                </div>

            </div>


            <div class="stat-card green">

                <div class="stat-top">

                    <div class="stat-icon">
                        ✓
                    </div>

                    <div>
                        <div class="stat-label">
                            System Status
                        </div>

                        <div
                            class="stat-value mh-system-{{ $systemStatus['class'] ?? 'local' }}"
                            style="font-size:20px">
                            {{ $systemStatus['label'] ?? 'Monitoring' }}
                        </div>
                    </div>

                </div>

                <div class="stat-foot">
                    {{ $systemStatus['description'] ?? 'Status sistem monitoring.' }}
                </div>

            </div>

        </div>


        {{-- ===================================================== --}}
        {{-- TRAFFIC + TOP DOMAIN + DEVICES --}}
        {{-- ===================================================== --}}

        <div class="grid public-main">

            {{-- TRAFFIC --}}
            <div class="panel">

                <div class="panel-head">

                    <div>
                        <div class="panel-title">
                            ▥ Aktivitas Akses Jaringan
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
                        aria-label="Grafik traffic jaringan 24 jam">

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
                        ◎ Top Domain
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

                    <div class="mh-empty">
                        Belum ada domain pada access log
                        untuk periode 24 jam terakhir.
                    </div>

                    @endforelse

                </div>

            </div>


            {{-- DEVICES --}}
            <div class="panel">

                <div class="panel-head">

                    <div class="panel-title">
                        ▣ Perangkat Terbaru
                    </div>

                    <span
                        style="
                        color:#8391a5;
                        font-size:10px;
                        font-weight:700;
                    ">
                        5 terbaru
                    </span>

                </div>


                @forelse($latestDevices as $device)

                @php
                $isOnline =
                (bool) (
                $device->{$deviceOnlineColumn}
                ?? false
                );

                $displayName =
                $device->device_name
                ?: $device->hostname
                ?: 'Perangkat Tanpa Nama';
                @endphp


                <div class="mh-device-row">

                    <div class="mh-device-icon">
                        ▣
                    </div>


                    <div class="mh-device-copy">

                        <b title="{{ $displayName }}">
                            {{ $displayName }}
                        </b>

                        <small>
                            {{ $device->mac_address ?: '-' }}
                            &nbsp;·&nbsp;
                            {{ $device->ip_address ?: '-' }}
                        </small>

                    </div>


                    <span
                        class="
                            mh-state
                            {{ $isOnline ? 'online' : 'offline' }}
                        ">
                        ●
                        {{ $isOnline ? 'Online' : 'Offline' }}
                    </span>

                </div>

                @empty

                <div class="mh-empty">
                    Belum ada data perangkat.
                    Data akan tampil setelah sinkronisasi MikroTik
                    berhasil dijalankan.
                </div>

                @endforelse

            </div>

        </div>


        {{-- ===================================================== --}}
        {{-- RINGKASAN + READ ONLY --}}
        {{-- ===================================================== --}}

        <div
            class="grid two section-pad"
            style="
            margin-top:12px;
            grid-template-columns:3fr 1fr
        ">

            <div class="panel">

                <div class="panel-head">

                    <div class="panel-title">
                        ▧ Ringkasan Hasil Monitoring
                    </div>

                    <span
                        style="
                        color:#8391a5;
                        font-size:10px;
                        font-weight:700;
                    ">
                        Trafik {{ $formatBytes($totalBytes) }}
                    </span>

                </div>


                <div
                    class="table-wrap"
                    style="margin-top:10px">

                    <table class="table">

                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Indikator</th>
                                <th>Jumlah</th>
                                <th>Persentase / Rasio</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>


                        <tbody>

                            @foreach($resultRows as $index => $row)

                            <tr>

                                <td>
                                    {{ $index + 1 }}
                                </td>

                                <td>
                                    <strong>
                                        {{ $row['label'] }}
                                    </strong>
                                </td>

                                <td>
                                    {{ number_format(
                                        $row['value'],
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </td>

                                <td>
                                    {{ number_format(
                                        $row['percent'],
                                        1,
                                        ',',
                                        '.'
                                    ) }}%
                                </td>

                                <td class="mh-table-note">
                                    {{ $row['note'] }}
                                </td>

                            </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>


            <div class="mh-readonly">

                <div class="mh-readonly-tag">
                    READ-ONLY MONITORING
                </div>

                <h3>
                    Informasi jaringan
                    tanpa akses pengelolaan
                </h3>

                <p>
                    Halaman monitoring digunakan untuk melihat kondisi dan
                    statistik jaringan. Perubahan konfigurasi, blacklist,
                    perangkat, maupun layanan tetap dilakukan melalui
                    dashboard admin.
                </p>

            </div>

        </div>

    </div>

    @endsection