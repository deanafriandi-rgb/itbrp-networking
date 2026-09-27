@extends('layouts.admin')

@section('title', 'Akses Jaringan')

@section('content')

@php
$totalAccess = (int) ($summary['total'] ?? 0);
$allowed = (int) ($summary['allowed'] ?? 0);
$blocked = (int) ($summary['blocked'] ?? 0);
$uniqueClients = (int) ($summary['unique_clients'] ?? 0);

$avgResponse = (float) ($summary['avg_response_ms'] ?? 0);
$totalBytes = (int) ($summary['total_bytes'] ?? 0);

$now = now('Asia/Jakarta');

$trafficData = collect($traffic ?? [])
->values()
->all();

$peakData = [];

for ($hour = 0; $hour < 24; $hour++) {

    $row=$peakHours->get($hour);

    $peakData[] = [
    'hour' => str_pad(
    (string) $hour,
    2,
    '0',
    STR_PAD_LEFT
    ) . ':00',

    'total' => $row
    ? (int) $row->total
    : 0,
    ];
    }

    $maxPeak = max(
    1,
    collect($peakData)->max('total')
    );

    $maxSegment = max(
    1,
    (int) (
    collect($topSegments)->max('total')
    ?? 0
    )
    );

    function formatAccessBytes($bytes)
    {
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
    }
    @endphp


    {{-- HERO --}}
    <section class="hero">

        <div class="hero-bg"></div>

        <div class="hero-copy">

            <div class="eyebrow">
                ITBRP NETWORK MONITORING
            </div>

            <h1>
                Akses Jaringan
            </h1>

            <p>
                Pantau lalu lintas jaringan kampus secara real-time,
                lihat aktivitas akses, dan pastikan keamanan jaringan.
            </p>

            <div class="hero-chips">

                <span class="hero-chip">
                    <span class="chip-icon green">●</span>
                    Jaringan Stabil
                </span>

                <span class="hero-chip">
                    <span class="chip-icon">◆</span>
                    Akses Terkendali
                </span>

                <span class="hero-chip">
                    <span class="chip-icon purple">▥</span>
                    Monitoring Real-time
                </span>

            </div>

        </div>


        <div class="hero-status">
            <span class="status-dot"></span>
            Monitoring Aktif
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


    {{-- SUMMARY --}}
    <div class="stats">

        <div class="stat-card blue">

            <div class="stat-top">

                <div class="stat-icon">
                    ↕
                </div>

                <div>

                    <div class="stat-label">
                        Total Akses
                    </div>

                    <div class="stat-value">
                        {{ number_format(
                        $totalAccess,
                        0,
                        ',',
                        '.'
                    ) }}
                    </div>

                </div>

            </div>

            <div class="stat-foot">
                24 jam terakhir
            </div>

        </div>


        <div class="stat-card green">

            <div class="stat-top">

                <div class="stat-icon">
                    ✓
                </div>

                <div>

                    <div class="stat-label">
                        Akses Diizinkan
                    </div>

                    <div class="stat-value">
                        {{ number_format(
                        $allowed,
                        0,
                        ',',
                        '.'
                    ) }}
                    </div>

                </div>

            </div>

            <div class="stat-foot">
                Request berhasil
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
                        {{ number_format(
                        $blocked,
                        0,
                        ',',
                        '.'
                    ) }}
                    </div>

                </div>

            </div>

            <div class="stat-foot">
                Request ditolak Squid
            </div>

        </div>


        <div class="stat-card blue">

            <div class="stat-top">

                <div class="stat-icon">
                    ◉
                </div>

                <div>

                    <div class="stat-label">
                        Klien Unik
                    </div>

                    <div class="stat-value">
                        {{ number_format(
                        $uniqueClients,
                        0,
                        ',',
                        '.'
                    ) }}
                    </div>

                </div>

            </div>

            <div class="stat-foot">
                Berdasarkan IP client
            </div>

        </div>


        <div class="stat-card purple">

            <div class="stat-top">

                <div class="stat-icon">
                    ◴
                </div>

                <div>

                    <div class="stat-label">
                        Rata-rata Response
                    </div>

                    <div class="stat-value">
                        {{ number_format(
                        $avgResponse,
                        0,
                        ',',
                        '.'
                    ) }}
                        ms
                    </div>

                </div>

            </div>

            <div class="stat-foot">
                Request non-CONNECT
            </div>

        </div>


        <div class="stat-card teal">

            <div class="stat-top">

                <div class="stat-icon">
                    ▤
                </div>

                <div>

                    <div class="stat-label">
                        Data Tercatat
                    </div>

                    <div class="stat-value">
                        {{ formatAccessBytes(
                        $totalBytes
                    ) }}
                    </div>

                </div>

            </div>

            <div class="stat-foot">
                Berdasarkan access.log
            </div>

        </div>

    </div>


    {{-- TRAFFIC --}}
    <div class="grid two">

        <div class="panel">

            <div class="panel-head">

                <div class="panel-title">
                    ▥ Grafik Lalu Lintas Jaringan
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

                    <polygon
                        id="accessTotalArea"
                        fill="#1e86fa"
                        opacity=".07"></polygon>

                    <polyline
                        id="accessTotalLine"
                        fill="none"
                        stroke="#1e86fa"
                        stroke-width="4"
                        stroke-linecap="round"
                        stroke-linejoin="round"></polyline>


                    <polygon
                        id="accessAllowedArea"
                        fill="#12b76a"
                        opacity=".07"></polygon>

                    <polyline
                        id="accessAllowedLine"
                        fill="none"
                        stroke="#12b76a"
                        stroke-width="4"
                        stroke-linecap="round"
                        stroke-linejoin="round"></polyline>

                </svg>


                <div class="axis">

                    @foreach($traffic as $index => $item)

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
                    <i style="background:#1e86fa"></i>
                    Total
                </span>

                <span>
                    <i style="background:#12b76a"></i>
                    Diizinkan
                </span>

            </div>

        </div>


        {{-- RIGHT SIDE --}}
        <div class="grid" style="gap:12px">


            {{-- PEAK HOURS --}}
            <div class="panel">

                <div class="panel-head">

                    <div class="panel-title">
                        ◴ Analisis Jam Puncak Akses
                    </div>

                    <select class="tiny-select">
                        <option>
                            24 Jam Terakhir
                        </option>
                    </select>

                </div>


                <div class="bar-chart">

                    @foreach($peakData as $peak)

                    @php
                    $height = $maxPeak > 0
                    ? ($peak['total'] / $maxPeak) * 100
                    : 0;
                    @endphp

                    <span
                        title="
                            {{ $peak['hour'] }}
                            •
                            {{ $peak['total'] }}
                            request
                        "
                        style="height:{{ max(4,
                                min($height, 100)) }}%"></span>

                    @endforeach

                </div>

            </div>



            {{-- TOP SEGMENT --}}
            <div class="panel">

                <div class="panel-head">

                    <div class="panel-title">
                        ⌘ Top Segmen Jaringan
                    </div>

                    <select class="tiny-select">
                        <option>
                            24 Jam Terakhir
                        </option>
                    </select>

                </div>


                <div class="ranking">

                    @forelse($topSegments as $segment)

                    @php
                    $width = $maxSegment > 0
                    ? (
                    $segment['total']
                    /
                    $maxSegment
                    ) * 100
                    : 0;
                    @endphp

                    <div class="rank-row">

                        <div>
                            {{ $segment['segment'] }}
                        </div>

                        <div class="progress">

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
                            {{
                                number_format(
                                    $segment[
                                        'percentage'
                                    ],
                                    1,
                                    ',',
                                    '.'
                                )
                            }}%
                        </b>

                    </div>

                    @empty

                    <div
                        style="
                            padding:25px;
                            text-align:center;
                        ">
                        Belum ada data segmen.
                    </div>

                    @endforelse

                </div>

            </div>

        </div>

    </div>


    {{-- ACCESS LOG TABLE --}}
    <div style="margin-top:12px">

        <div class="panel">

            <div class="panel-head">

                <div class="panel-title">
                    ▧ Aktivitas Akses Jaringan Terbaru
                </div>

            </div>


            {{-- FILTER --}}
            <form
                method="GET"
                action="{{ route(
                'admin.access.index'
            ) }}"
                style="
                display:flex;
                gap:10px;
                flex-wrap:wrap;
                margin-bottom:15px;
            ">

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="
                    Cari IP, domain atau URL...
                "
                    class="search-input"
                    style="flex:1;min-width:240px">


                <select
                    name="protocol"
                    class="tiny-select">

                    <option value="">
                        Semua Protokol
                    </option>

                    <option
                        value="HTTP"
                        @selected(
                        request('protocol')==='HTTP'
                        )>
                        HTTP
                    </option>

                    <option
                        value="HTTPS"
                        @selected(
                        request('protocol')==='HTTPS'
                        )>
                        HTTPS
                    </option>

                </select>


                <select
                    name="category"
                    class="tiny-select">

                    <option value="">
                        Semua Hasil
                    </option>

                    <option
                        value="ALLOWED"
                        @selected(
                        request('category')==='ALLOWED'
                        )>
                        Diizinkan
                    </option>

                    <option
                        value="BLOCKED"
                        @selected(
                        request('category')==='BLOCKED'
                        )>
                        Diblokir
                    </option>

                    <option
                        value="CACHE_HIT"
                        @selected(
                        request('category')==='CACHE_HIT'
                        )>
                        Cache HIT
                    </option>

                    <option
                        value="CACHE_MISS"
                        @selected(
                        request('category')==='CACHE_MISS'
                        )>
                        Cache MISS
                    </option>

                    <option
                        value="FAILED"
                        @selected(
                        request('category')==='FAILED'
                        )>
                        Gagal
                    </option>

                </select>


                <button
                    type="submit"
                    class="link-button">
                    Filter
                </button>

            </form>


            <div class="table-wrap">

                <table class="table">

                    <thead>

                        <tr>

                            <th>
                                Waktu
                            </th>

                            <th>
                                IP Client
                            </th>

                            <th>
                                Domain
                            </th>

                            <th>
                                Metode
                            </th>

                            <th>
                                Hasil
                            </th>

                            <th>
                                Squid
                            </th>

                            <th>
                                Response
                            </th>

                            <th>
                                Data
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($logs as $log)

                        @php

                        $resultClass = match(
                        $log->category
                        ) {

                        'BLOCKED'
                        => 'bad',

                        'CACHE_HIT'
                        => 'ok',

                        'CACHE_MISS'
                        => 'orange',

                        'ALLOWED'
                        => 'ok',

                        'FAILED'
                        => 'bad',

                        default
                        => 'blue',
                        };


                        $resultLabel = match(
                        $log->category
                        ) {

                        'BLOCKED'
                        => 'Diblokir',

                        'CACHE_HIT'
                        => 'Cache HIT',

                        'CACHE_MISS'
                        => 'Cache MISS',

                        'ALLOWED'
                        => 'Diizinkan',

                        'FAILED'
                        => 'Gagal',

                        default
                        => 'Lainnya',
                        };

                        @endphp


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
                                {{ $log->client_ip }}
                            </td>


                            <td>
                                {{
                                $log->domain
                                ??
                                $log->destination_ip
                                ??
                                '-'
                            }}
                            </td>


                            <td>

                                <span class="badge blue">

                                    {{
                                    $log->protocol
                                    ??
                                    $log->method
                                    ??
                                    '-'
                                }}

                                </span>

                            </td>


                            <td>

                                <span
                                    class="
                                    badge
                                    {{ $resultClass }}
                                ">
                                    {{ $resultLabel }}
                                </span>

                            </td>


                            <td>
                                {{ $log->squid_code ?? '-' }}
                            </td>


                            <td>

                                @if(
                                $log->method === 'CONNECT'
                                )

                                <span
                                    title="
                                        Durasi tunnel HTTPS,
                                        bukan latency jaringan
                                    ">
                                    {{ number_format(
                                        $log->elapsed_ms
                                    ) }}
                                    ms*
                                </span>

                                @else

                                {{ number_format(
                                    $log->elapsed_ms
                                ) }}
                                ms

                                @endif

                            </td>


                            <td>
                                {{
                                formatAccessBytes(
                                    $log->bytes
                                )
                            }}
                            </td>

                        </tr>


                        @empty

                        <tr>

                            <td
                                colspan="8"
                                style="
                                text-align:center;
                                padding:30px;
                            ">
                                Belum ada aktivitas jaringan.
                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            @if($logs->hasPages())

            <div
                style="
                    margin-top:16px;
                ">
                {{ $logs->links() }}
            </div>

            @endif

        </div>

    </div>


    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function() {

                const traffic =
                    @json($trafficData);

                if (!traffic.length) {
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

                    ...traffic.map(
                        item =>
                        Math.max(
                            Number(
                                item.total || 0
                            ),

                            Number(
                                item.allowed || 0
                            )
                        )
                    )
                );

                const step =
                    traffic.length > 1

                    ?
                    width /
                    (
                        traffic.length - 1
                    )

                    :
                    width;


                function createPoints(field) {

                    return traffic
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


                const totalPoints =
                    createPoints(
                        'total'
                    );

                const allowedPoints =
                    createPoints(
                        'allowed'
                    );


                const totalLine =
                    document.getElementById(
                        'accessTotalLine'
                    );

                const allowedLine =
                    document.getElementById(
                        'accessAllowedLine'
                    );

                const totalArea =
                    document.getElementById(
                        'accessTotalArea'
                    );

                const allowedArea =
                    document.getElementById(
                        'accessAllowedArea'
                    );


                if (totalLine) {

                    totalLine.setAttribute(
                        'points',
                        totalPoints
                    );

                }


                if (allowedLine) {

                    allowedLine.setAttribute(
                        'points',
                        allowedPoints
                    );

                }


                if (totalArea) {

                    totalArea.setAttribute(
                        'points',

                        `0,${height} ` +
                        totalPoints +
                        ` ${width},${height}`
                    );

                }


                if (allowedArea) {

                    allowedArea.setAttribute(
                        'points',

                        `0,${height} ` +
                        allowedPoints +
                        ` ${width},${height}`
                    );

                }

            }
        );
    </script>

    @endsection