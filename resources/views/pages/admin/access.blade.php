@extends('layouts.admin')

@section('title', 'Akses Jaringan')

@section('content')


<style>
/* =========================================================
   ACCESS PAGE — SEMINAR POLISH
   Hanya tampilan. Tidak mengubah query/database.
   ========================================================= */
.access-log-panel{
    margin-top:14px;
    overflow:hidden;
}

.access-log-panel .panel-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:14px;
    margin-bottom:12px;
}

.access-log-title-wrap{
    min-width:0;
}

.access-log-title{
    display:flex;
    align-items:center;
    gap:8px;
    font-size:16px;
    font-weight:850;
    color:#0b285c;
    letter-spacing:-.015em;
}

.access-log-subtitle{
    margin-top:4px;
    font-size:10.5px;
    line-height:1.5;
    color:#71829a;
}

.access-result-guide{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:10px;
    margin:0 0 14px;
}

.access-guide-item{
    min-width:0;
    padding:11px 12px;
    border:1px solid #e1eaf3;
    border-radius:12px;
    background:#f9fbfd;
}

.access-guide-head{
    display:flex;
    align-items:center;
    gap:7px;
    margin-bottom:4px;
    font-size:10.5px;
    font-weight:800;
    color:#18355f;
}

.access-guide-dot{
    width:8px;
    height:8px;
    flex:0 0 8px;
    border-radius:50%;
}

.access-guide-dot.ok{ background:#12b76a; }
.access-guide-dot.blocked{ background:#f04438; }
.access-guide-dot.failed{ background:#f79009; }

.access-guide-item p{
    margin:0;
    font-size:9.5px;
    line-height:1.45;
    color:#708199;
}

.access-filter{
    display:grid;
    grid-template-columns:minmax(260px,1fr) 132px 150px auto auto;
    gap:9px;
    align-items:center;
    margin-bottom:13px;
}

.access-filter .search-input,
.access-filter .tiny-select{
    width:100%;
    min-width:0;
    height:38px;
}

.access-filter .search-input{
    border:1px solid #d9e5f0;
    border-radius:10px;
    padding:0 13px;
    outline:none;
    font-size:11px;
    color:#16325c;
    background:#fff;
}

.access-filter .search-input:focus,
.access-filter .tiny-select:focus{
    border-color:#8ec5ff;
    box-shadow:0 0 0 3px rgba(30,134,250,.08);
}

.access-filter .link-button,
.access-reset-button{
    min-height:38px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border-radius:10px;
    white-space:nowrap;
    font-size:10px;
    font-weight:800;
    text-decoration:none;
}

.access-reset-button{
    padding:0 13px;
    border:1px solid #dce7f2;
    background:#fff;
    color:#60748f;
}

.access-reset-button:hover{
    background:#f6f9fc;
    color:#244d7d;
}

.access-table-shell{
    overflow:hidden;
    border:1px solid #e1e9f2;
    border-radius:13px;
    background:#fff;
}

.access-table-scroll{
    max-height:545px;
    overflow:auto;
    scrollbar-width:thin;
}

.access-log-table{
    width:100%;
    min-width:930px;
    border-collapse:separate;
    border-spacing:0;
    table-layout:fixed;
    font-size:9.5px;
}

.access-log-table thead th{
    position:sticky;
    top:0;
    z-index:4;
    padding:10px 10px;
    border-bottom:1px solid #dfe8f1;
    background:#f2f6fa;
    color:#526985;
    font-size:9px;
    font-weight:800;
    text-align:left;
    letter-spacing:.01em;
}

.access-log-table tbody td{
    padding:9px 10px;
    border-bottom:1px solid #edf2f6;
    color:#284766;
    vertical-align:middle;
    overflow:hidden;
    white-space:nowrap;
    text-overflow:ellipsis;
}

.access-log-table tbody tr:nth-child(even){
    background:#fbfdff;
}

.access-log-table tbody tr:hover{
    background:#f4f9ff;
}

.access-log-table tbody tr:last-child td{
    border-bottom:0;
}

.access-log-table th:nth-child(1),
.access-log-table td:nth-child(1){ width:136px; }

.access-log-table th:nth-child(2),
.access-log-table td:nth-child(2){ width:115px; }

.access-log-table th:nth-child(3),
.access-log-table td:nth-child(3){ width:225px; }

.access-log-table th:nth-child(4),
.access-log-table td:nth-child(4){ width:82px; }

.access-log-table th:nth-child(5),
.access-log-table td:nth-child(5){ width:100px; }

.access-log-table th:nth-child(6),
.access-log-table td:nth-child(6){ width:112px; }

.access-log-table th:nth-child(7),
.access-log-table td:nth-child(7){ width:95px; }

.access-log-table th:nth-child(8),
.access-log-table td:nth-child(8){ width:82px; }

.access-log-table .badge{
    max-width:100%;
    padding:4px 8px;
    overflow:hidden;
    font-size:8.5px;
    white-space:nowrap;
    text-overflow:ellipsis;
}

.access-result-failed{
    color:#b54708;
    background:#fff3e8;
}

.access-table-note{
    padding:9px 12px;
    border-top:1px solid #e7eef5;
    background:#fbfdff;
    font-size:9.5px;
    line-height:1.45;
    color:#718299;
}

.access-pagination{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    margin-top:13px;
    padding:11px 12px;
    border:1px solid #e0e9f2;
    border-radius:12px;
    background:#f9fbfd;
}

.access-pagination-info{
    min-width:0;
    font-size:10px;
    color:#6a7e98;
}

.access-pagination-info b{
    color:#173866;
}

.access-pagination-buttons{
    display:flex;
    align-items:center;
    justify-content:flex-end;
    gap:5px;
    flex-wrap:wrap;
}

.access-page-link,
.access-page-disabled,
.access-page-ellipsis{
    min-width:32px;
    height:32px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:0 9px;
    border-radius:9px;
    font-size:10px;
    font-weight:750;
    text-decoration:none;
}

.access-page-link{
    border:1px solid #d8e4ef;
    background:#fff;
    color:#375a84;
}

.access-page-link:hover{
    border-color:#a8cff7;
    background:#edf6ff;
    color:#0b71de;
}

.access-page-link.active{
    border-color:#1e86fa;
    background:#1e86fa;
    color:#fff;
}

.access-page-disabled{
    border:1px solid #e7edf3;
    background:#f3f6f9;
    color:#a3afbd;
    cursor:not-allowed;
}

.access-page-ellipsis{
    min-width:22px;
    padding:0 3px;
    color:#8b99aa;
}

@media(max-width:1180px){
    .access-result-guide{
        grid-template-columns:1fr;
    }

    .access-filter{
        grid-template-columns:1fr 1fr;
    }

    .access-filter .search-input{
        grid-column:1 / -1;
    }

    .access-pagination{
        align-items:flex-start;
        flex-direction:column;
    }

    .access-pagination-buttons{
        justify-content:flex-start;
    }
}

@media(max-width:650px){
    .access-filter{
        grid-template-columns:1fr;
    }

    .access-filter .search-input{
        grid-column:auto;
    }

    .access-log-table{
        min-width:850px;
    }

    .access-pagination-buttons{
        gap:4px;
    }

    .access-page-link,
    .access-page-disabled{
        min-width:30px;
        height:30px;
        padding:0 7px;
    }
}
</style>


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
<div class="access-log-panel">
    <div class="panel">
        <div class="panel-head">
            <div class="access-log-title-wrap">
                <div class="access-log-title">
                    ▧ Aktivitas Akses Jaringan Terbaru
                </div>
                <div class="access-log-subtitle">
                    Data berasal dari <strong>Squid access.log</strong>. Status di bawah menjelaskan hasil setiap request, bukan status keseluruhan server.
                </div>
            </div>
        </div>

        {{-- STATUS GUIDE --}}
        <div class="access-result-guide">
            <div class="access-guide-item">
                <div class="access-guide-head">
                    <span class="access-guide-dot ok"></span>
                    Diizinkan
                </div>
                <p>
                    Request berhasil diteruskan oleh Squid, termasuk tunnel HTTPS normal dan request yang berhasil diproses.
                </p>
            </div>

            <div class="access-guide-item">
                <div class="access-guide-head">
                    <span class="access-guide-dot blocked"></span>
                    Diblokir
                </div>
                <p>
                    Request sengaja ditolak oleh kebijakan Squid, misalnya <strong>TCP_DENIED</strong>. Ini menandakan aturan filtering bekerja.
                </p>
            </div>

            <div class="access-guide-item">
                <div class="access-guide-head">
                    <span class="access-guide-dot failed"></span>
                    Tidak Selesai
                </div>
                <p>
                    Koneksi/request berakhir tanpa hasil normal. Ini tidak otomatis berarti konfigurasi Squid gagal dan perlu dilihat bersama kode Squid-nya.
                </p>
            </div>
        </div>

        {{-- FILTER --}}
        <form
            method="GET"
            action="{{ route('admin.access.index') }}"
            class="access-filter">

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari IP, domain atau URL..."
                class="search-input">

            <select
                name="protocol"
                class="tiny-select">

                <option value="">Semua Protokol</option>

                <option
                    value="HTTP"
                    @selected(request('protocol') === 'HTTP')>
                    HTTP
                </option>

                <option
                    value="HTTPS"
                    @selected(request('protocol') === 'HTTPS')>
                    HTTPS
                </option>
            </select>

            <select
                name="category"
                class="tiny-select">

                <option value="">Semua Hasil</option>

                <option
                    value="ALLOWED"
                    @selected(request('category') === 'ALLOWED')>
                    Diizinkan
                </option>

                <option
                    value="BLOCKED"
                    @selected(request('category') === 'BLOCKED')>
                    Diblokir
                </option>

                <option
                    value="CACHE_HIT"
                    @selected(request('category') === 'CACHE_HIT')>
                    Cache HIT
                </option>

                <option
                    value="CACHE_MISS"
                    @selected(request('category') === 'CACHE_MISS')>
                    Cache MISS
                </option>

                <option
                    value="FAILED"
                    @selected(request('category') === 'FAILED')>
                    Tidak Selesai
                </option>
            </select>

            <button
                type="submit"
                class="link-button">
                Filter
            </button>

            <a
                href="{{ route('admin.access.index') }}"
                class="access-reset-button">
                Reset
            </a>
        </form>

        <div class="access-table-shell">
            <div class="access-table-scroll">
                <table class="access-log-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>IP Client</th>
                            <th>Domain / Tujuan</th>
                            <th>Protokol</th>
                            <th>Hasil</th>
                            <th>Squid Result</th>
                            <th>Response</th>
                            <th>Data</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($logs as $log)
                            @php
                                $resultClass = match($log->category) {
                                    'BLOCKED' => 'bad',
                                    'CACHE_HIT' => 'ok',
                                    'CACHE_MISS' => 'orange',
                                    'ALLOWED' => 'ok',
                                    'FAILED' => 'orange',
                                    default => 'blue',
                                };

                                $resultLabel = match($log->category) {
                                    'BLOCKED' => 'Diblokir',
                                    'CACHE_HIT' => 'Cache HIT',
                                    'CACHE_MISS' => 'Cache MISS',
                                    'ALLOWED' => 'Diizinkan',
                                    'FAILED' => 'Tidak Selesai',
                                    default => 'Lainnya',
                                };

                                $domainOrDestination =
                                    $log->domain
                                    ?? $log->destination_ip
                                    ?? '-';

                                $isConnect =
                                    strtoupper((string) $log->method) === 'CONNECT';
                            @endphp

                            <tr>
                                <td title="{{ $log->logged_at?->format('d/m/Y H:i:s') ?? '-' }}">
                                    {{ $log->logged_at?->format('d/m/Y H:i:s') ?? '-' }}
                                </td>

                                <td title="{{ $log->client_ip }}">
                                    {{ $log->client_ip }}
                                </td>

                                <td title="{{ $domainOrDestination }}">
                                    {{ $domainOrDestination }}
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
                                        class="badge {{ $resultClass }}"
                                        title="{{ $resultLabel }}">
                                        {{ $resultLabel }}
                                    </span>
                                </td>

                                <td title="{{ $log->squid_code ?? '-' }}">
                                    {{ $log->squid_code ?? '-' }}
                                </td>

                                <td>
                                    @if($isConnect)
                                        <span
                                            title="Durasi tunnel HTTPS, bukan latency jaringan murni">
                                            {{ number_format((int) $log->elapsed_ms) }} ms*
                                        </span>
                                    @else
                                        {{ number_format((int) $log->elapsed_ms) }} ms
                                    @endif
                                </td>

                                <td>
                                    {{ formatAccessBytes((int) $log->bytes) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="8"
                                    style="text-align:center;padding:32px;color:#8291a5;">
                                    Belum ada aktivitas jaringan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="access-table-note">
                <strong>Catatan:</strong>
                tanda <strong>ms*</strong> pada metode CONNECT menunjukkan durasi tunnel HTTPS,
                bukan latency jaringan murni.
            </div>
        </div>

        {{-- CUSTOM PAGINATION --}}
        @if($logs->hasPages())
            @php
                $currentPage = $logs->currentPage();
                $lastPage = $logs->lastPage();
                $startPage = max(1, $currentPage - 2);
                $endPage = min($lastPage, $currentPage + 2);

                if (($endPage - $startPage) < 4) {
                    if ($startPage === 1) {
                        $endPage = min($lastPage, 5);
                    } elseif ($endPage === $lastPage) {
                        $startPage = max(1, $lastPage - 4);
                    }
                }
            @endphp

            <div class="access-pagination">
                <div class="access-pagination-info">
                    Menampilkan
                    <b>{{ number_format((int) $logs->firstItem(), 0, ',', '.') }}</b>
                    –
                    <b>{{ number_format((int) $logs->lastItem(), 0, ',', '.') }}</b>
                    dari
                    <b>{{ number_format((int) $logs->total(), 0, ',', '.') }}</b>
                    data
                    · Halaman
                    <b>{{ number_format($currentPage, 0, ',', '.') }}</b>
                    dari
                    <b>{{ number_format($lastPage, 0, ',', '.') }}</b>
                </div>

                <div class="access-pagination-buttons">
                    @if($logs->onFirstPage())
                        <span class="access-page-disabled">‹ Sebelumnya</span>
                    @else
                        <a
                            class="access-page-link"
                            href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}">
                            ‹ Sebelumnya
                        </a>
                    @endif

                    @if($startPage > 1)
                        <a
                            class="access-page-link"
                            href="{{ request()->fullUrlWithQuery(['page' => 1]) }}">
                            1
                        </a>

                        @if($startPage > 2)
                            <span class="access-page-ellipsis">…</span>
                        @endif
                    @endif

                    @for($page = $startPage; $page <= $endPage; $page++)
                        @if($page === $currentPage)
                            <span class="access-page-link active">
                                {{ $page }}
                            </span>
                        @else
                            <a
                                class="access-page-link"
                                href="{{ request()->fullUrlWithQuery(['page' => $page]) }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endfor

                    @if($endPage < $lastPage)
                        @if($endPage < ($lastPage - 1))
                            <span class="access-page-ellipsis">…</span>
                        @endif

                        <a
                            class="access-page-link"
                            href="{{ request()->fullUrlWithQuery(['page' => $lastPage]) }}">
                            {{ $lastPage }}
                        </a>
                    @endif

                    @if($logs->hasMorePages())
                        <a
                            class="access-page-link"
                            href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}">
                            Berikutnya ›
                        </a>
                    @else
                        <span class="access-page-disabled">Berikutnya ›</span>
                    @endif
                </div>
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
