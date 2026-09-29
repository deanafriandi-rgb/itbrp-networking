@extends('layouts.admin')

@section('title', 'Beranda')

@section('content')

    @php

        /*

        |--------------------------------------------------------------------------

        | Dashboard Summary

        |--------------------------------------------------------------------------

        */

        $totalRequest = (int) ($summary['total_request'] ?? 0);

        $allowed = (int) ($summary['allowed'] ?? 0);

        $blocked = (int) ($summary['blocked'] ?? 0);

        $failed = (int) ($summary['failed'] ?? 0);

        $uniqueClients = (int) ($summary['unique_clients'] ?? 0);

        $cacheHit = (int) ($summary['cache_hit'] ?? 0);

        $cacheMiss = (int) ($summary['cache_miss'] ?? 0);

        $hitRatio = (float) ($summary['hit_ratio'] ?? 0);

        $totalBytes = (int) ($summary['total_bytes'] ?? 0);

        /*

        |--------------------------------------------------------------------------

        | Waktu

        |--------------------------------------------------------------------------

        */

        $now = now('Asia/Jakarta');

        /*

        |--------------------------------------------------------------------------

        | Top Domain

        |--------------------------------------------------------------------------

        */

        $maxDomainRequest = max(

            1,

            (int) ($topDomains->max('total_request') ?? 0)

        );

        /*

        |--------------------------------------------------------------------------

        | Traffic untuk JavaScript

        |--------------------------------------------------------------------------

        */

        $trafficData = collect($traffic ?? [])

            ->values()

            ->all();

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

                Dashboard Monitoring Jaringan ITBRP

            </h1>

            <p>

                Pemantauan aktivitas, akses, perangkat, dan performa jaringan kampus

                secara terpusat dan real-time.

            </p>

            <div class="hero-chips">

                <span class="hero-chip">

                    <span class="chip-icon green">●</span>

                    Monitoring Terpusat

                </span>

                <span class="hero-chip">

                    <span class="chip-icon">◆</span>

                    Kontrol Akses

                </span>

                <span class="hero-chip">

                    <span class="chip-icon purple">▥</span>

                    Data Real-time

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

    {{-- ========================================================= --}}

    {{-- SUMMARY CARDS --}}

    {{-- ========================================================= --}}

    <div class="stats">

        {{-- TOTAL REQUEST --}}

        <div class="stat-card blue">

            <div class="stat-top">

                <div class="stat-icon">

                    ▧

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

                <span class="up">

                    ●

                </span>

                24 jam terakhir

            </div>

        </div>

        {{-- ALLOWED --}}

        <div class="stat-card green">

            <div class="stat-top">

                <div class="stat-icon">

                    ✓

                </div>

                <div>

                    <div class="stat-label">

                        Diizinkan

                    </div>

                    <div class="stat-value">

                        {{ number_format($allowed, 0, ',', '.') }}

                    </div>

                </div>

            </div>

            <div class="stat-foot">

                <span class="up">

                    ●

                </span>

                Request diizinkan

            </div>

        </div>

        {{-- BLOCKED --}}

        <div class="stat-card red">

            <div class="stat-top">

                <div class="stat-icon">

                    ⊘

                </div>

                <div>

                    <div class="stat-label">

                        Diblokir

                    </div>

                    <div class="stat-value">

                        {{ number_format($blocked, 0, ',', '.') }}

                    </div>

                </div>

            </div>

            <div class="stat-foot">

                <span class="down">

                    ●

                </span>

                Request diblokir

            </div>

        </div>

        {{-- CLIENT --}}

        <div class="stat-card blue">

            <div class="stat-top">

                <div class="stat-icon">

                    ◉

                </div>

                <div>

                    <div class="stat-label">

                        Klien Aktif

                    </div>

                    <div class="stat-value">

                        {{ number_format($uniqueClients, 0, ',', '.') }}

                    </div>

                </div>

            </div>

            <div class="stat-foot">

                <span class="up">

                    ●

                </span>

                IP client unik

            </div>

        </div>

        {{-- CACHE HIT --}}

        <div class="stat-card teal">

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

                <span class="up">

                    HIT {{ number_format($cacheHit, 0, ',', '.') }}

                </span>

                · MISS {{ number_format($cacheMiss, 0, ',', '.') }}

            </div>

        </div>

        {{-- PROXY STATUS --}}

        <div class="stat-card green">

            <div class="stat-top">

                <div class="stat-icon">

                    ✓

                </div>

                <div>

                    <div class="stat-label">

                        Proxy Monitor

                    </div>

                    <div class="stat-value" style="font-size:20px">

                        Health Check

                    </div>

                </div>

            </div>

            <div class="stat-foot">

                Detail kondisi tersedia di menu Status Sistem

            </div>

        </div>

    </div>

    {{-- ========================================================= --}}

    {{-- TRAFFIC + TOP DOMAIN --}}

    {{-- ========================================================= --}}

    <div class="grid two">

        {{-- TRAFFIC --}}

        <div class="panel">

            <div class="panel-head">

                <div>

                    <div class="panel-title">

                        ▥ Lalu Lintas Jaringan

                    </div>

                </div>

                <select class="tiny-select">

                    <option>

                        24 Jam Terakhir

                    </option>

                </select>

            </div>

            <div class="chart">

                <div class="chart-grid"></div>

                <svg id="trafficChart" viewBox="0 0 800 210" preserveAspectRatio="none">

                    {{-- TOTAL AREA --}}

                    <polygon id="trafficTotalArea" fill="#1e86fa" opacity=".07"></polygon>

                    {{-- TOTAL LINE --}}

                    <polyline id="trafficTotalLine" fill="none" stroke="#1e86fa" stroke-width="4" stroke-linecap="round"
                        stroke-linejoin="round"></polyline>

                    {{-- ALLOWED AREA --}}

                    <polygon id="trafficDiizinkanArea" fill="#12b76a" opacity=".07"></polygon>

                    {{-- ALLOWED LINE --}}

                    <polyline id="trafficDiizinkanLine" fill="none" stroke="#12b76a" stroke-width="4" stroke-linecap="round"
                        stroke-linejoin="round"></polyline>

                </svg>

                <div class="axis">

                    @forelse($traffic as $index => $item)

                        @if($index % 4 === 0)

                            <span>

                                {{ $item['hour'] }}

                            </span>

                        @endif

                    @empty

                        <span>00:00</span>

                    @endforelse

                </div>

            </div>

            <div class="legend">

                <span>

                    <i style="background:#1e86fa"></i>

                    Total Akses

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

                <select class="tiny-select">

                    <option>

                        24 Jam Terakhir

                    </option>

                </select>

            </div>

            <div class="ranking">

                @forelse($topDomains as $domain)

                    @php

                        $percentage = $totalRequest > 0

                            ? ($domain['total_request'] / $totalRequest) * 100

                            : 0;

                        $barWidth = $maxDomainRequest > 0

                            ? ($domain['total_request'] / $maxDomainRequest) * 100

                            : 0;

                        $domainInitial = strtoupper(

                            substr($domain['domain'], 0, 1)

                        );

                    @endphp

                    <div class="rank-row">

                        <div class="rank-name">

                            <span class="brand-dot">

                                {{ $domainInitial }}

                            </span>

                            <span>

                                {{ $domain['domain'] }}

                            </span>

                        </div>

                        <div class="progress">

                            <i style="width:

                                    {{ min($barWidth, 100) }}%"></i>

                        </div>

                        <b>

                            {{ number_format($percentage, 1, ',', '.') }}%

                        </b>

                    </div>

                @empty

                    <div style="

                                padding:30px;

                                text-align:center;

                                color:#7b8794;

                            ">

                        Belum ada data domain.

                    </div>

                @endforelse

            </div>

        </div>

    </div>

    {{-- ========================================================= --}}

    {{-- ACTIVITY + SYSTEM STATUS --}}

    {{-- ========================================================= --}}

    <div class="grid two section-gap" style="margin-top:12px">

        {{-- LATEST ACTIVITIES --}}

        <div class="panel">

            <div class="panel-head">

                <div class="panel-title">

                    ▧ Aktivitas Terbaru

                </div>

                <a href="{{ route('admin.access.index') }}" class="link-button">

                    Lihat Semua →

                </a>

            </div>

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

                                Domain / Destination

                            </th>

                            <th>

                                Protokol

                            </th>

                            <th>

                                Hasil

                            </th>

                            <th>

                                Squid Result

                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($latestActivities as $log)

                                        @php

                                            $resultClass = match ($log->category) {

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

                                            $resultLabel = match ($log->category) {

                                                'BLOCKED'

                                                => 'Diblokir',

                                                'CACHE_HIT'

                                                => 'Cache Ratio',

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

                                                {{ $log->logged_at?->format('H:i:s') ?? '-' }}

                                            </td>

                                            <td>

                                                {{ $log->client_ip }}

                                            </td>

                                            <td>

                                                {{

                            $log->domain

                            ?? $log->destination_ip

                            ?? '-'

                                                }}

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

                                                <span class="badge {{ $resultClass }}">

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

                                <td colspan="6" style="

                                        text-align:center;

                                        padding:32px;

                                    ">

                                    Belum ada aktivitas jaringan.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        {{-- SYSTEM STATUS --}}

        <div class="panel">

            <div class="panel-title">

                ⌁ Status Sistem

            </div>

            <div class="kv-list" style="margin-top:10px">

                {{-- DATABASE --}}

                <div class="kv-row">

                    <span>

                        <span class="status-dot" style="margin-right:7px"></span>

                        Database Laravel

                    </span>

                    <span>

                        <b style="color:#11945a">

                            Terhubung

                        </b>

                        &nbsp;

                        MySQL

                    </span>

                </div>

                {{-- SQUID --}}

                <div class="kv-row">

                    <span>

                        <span class="status-dot" style="margin-right:7px"></span>

                        Squid Proxy

                    </span>

                    <span>

                        <b style="color:#d08a00">

                            Health Check

                        </b>

                    </span>

                </div>

                {{-- CACHE --}}

                <div class="kv-row">

                    <span>

                        <span class="status-dot" style="margin-right:7px"></span>

                        Cache Server

                    </span>

                    <span>

                        <b style="color:#11945a">

                            HIT

                            {{ number_format($cacheHit, 0, ',', '.') }}

                        </b>

                        &nbsp;

                        MISS

                        {{ number_format($cacheMiss, 0, ',', '.') }}

                    </span>

                </div>

                {{-- HIT RATIO --}}

                <div class="kv-row">

                    <span>

                        <span class="status-dot" style="margin-right:7px"></span>

                        Cache Hit Ratio

                    </span>

                    <span>

                        <b style="color:#11945a">

                            {{ number_format($hitRatio, 1, ',', '.') }}%

                        </b>

                    </span>

                </div>

                {{-- FAIL / OTHER --}}

                <div class="kv-row">

                    <span>

                        <span class="status-dot" style="margin-right:7px"></span>

                        Request Gagal

                    </span>

                    <span>

                        <b>

                            {{ number_format($failed, 0, ',', '.') }}

                        </b>

                    </span>

                </div>

                {{-- MIKROTIK --}}

                <div class="kv-row">

                    <span>

                        <span class="status-dot" style="margin-right:7px"></span>

                        MikroTik

                    </span>

                    <span>

                        <b style="color:#d08a00">

                            Terhubung API

                        </b>

                    </span>

                </div>

                {{-- DATA TRANSFER --}}

                <div class="kv-row">

                    <span>

                        <span class="status-dot" style="margin-right:7px"></span>

                        Volume Data Log

                    </span>

                    <span>

                        <b>

                            @if($totalBytes >= 1073741824)

                                                {{

                                number_format(

                                    $totalBytes / 1073741824,

                                    2,

                                    ',',

                                    '.'

                                )

                                                    }}

                                                GB

                            @elseif($totalBytes >= 1048576)

                                                {{

                                number_format(

                                    $totalBytes / 1048576,

                                    2,

                                    ',',

                                    '.'

                                )

                                                    }}

                                                MB

                            @elseif($totalBytes >= 1024)

                                                {{

                                number_format(

                                    $totalBytes / 1024,

                                    2,

                                    ',',

                                    '.'

                                )

                                                    }}

                                                KB

                            @else

                                {{ number_format($totalBytes) }}

                                Bytes

                            @endif

                        </b>

                    </span>

                </div>

            </div>

        </div>

    </div>

    {{-- ========================================================= --}}

    {{-- TRAFFIC CHART JS --}}

    {{-- ========================================================= --}}

    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const traffic = @json($trafficData);

            if (!traffic.length) {

                return;

            }

            const width = 800;

            const height = 210;

            const topPadding = 20;

            const bottomPadding = 20;

            const usableHeight =

                height - topPadding - bottomPadding;

            const maxValue = Math.max(

                1,

                ...traffic.map(item =>

                    Math.max(

                        Number(item.total_request || 0),

                        Number(item.allowed || 0)

                    )

                )

            );

            const step = traffic.length > 1 ?

                width / (traffic.length - 1) :

                width;

            function createPoints(field) {

                return traffic.map((item, index) => {

                    const x = index * step;

                    const value = Number(item[field] || 0);

                    const y =

                        height -

                        bottomPadding -

                        ((value / maxValue) * usableHeight);

                    return `${x.toFixed(1)},${y.toFixed(1)}`;

                }).join(' ');

            }

            const totalPoints = createPoints('total_request');

            const allowedPoints = createPoints('allowed');

            const totalLine =

                document.getElementById('trafficTotalLine');

            if (totalLine) {

                totalLine.setAttribute('points', totalPoints);

            }

            const allowedLine =

                document.getElementById('trafficDiizinkanLine');

            if (allowedLine) {

                allowedLine.setAttribute('points', allowedPoints);

            }

            const totalArea =

                document.getElementById('trafficTotalArea');

            if (totalArea) {

                totalArea.setAttribute(

                    'points',

                    `0,${height} ${totalPoints} ${width},${height}`

                );

            }

            const allowedArea =

                document.getElementById('trafficDiizinkanArea');

            if (allowedArea) {

                allowedArea.setAttribute(

                    'points',

                    `0,${height} ${allowedPoints} ${width},${height}`

                );

            }

        });

    </script>

@endsection