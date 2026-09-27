@extends('layouts.admin')

@section('title', 'Status Sistem')

@section('content')

@php
$now = now('Asia/Jakarta');

$total = (int) ($summary['total'] ?? 0);
$online = (int) ($summary['online'] ?? 0);
$warning = (int) ($summary['warning'] ?? 0);
$offline = (int) ($summary['offline'] ?? 0);
$local = (int) ($summary['local'] ?? 0);

$checkedAt = $checked_at ?? $now;

$formatDateTime = function ($value) {
if (!$value) {
return '-';
}

try {
return \Illuminate\Support\Carbon::parse($value)
->timezone('Asia/Jakarta')
->format('d/m/Y H:i:s');
} catch (\Throwable $e) {
return (string) $value;
}
};

$formatBytes = function ($bytes) {
if ($bytes === null || $bytes === '') {
return '-';
}

$bytes = (float) $bytes;

if ($bytes < 1024) {
    return number_format($bytes, 0) . ' B' ;
    }

    if ($bytes < 1024 ** 2) {
    return number_format($bytes / 1024, 2) . ' KB' ;
    }

    if ($bytes < 1024 ** 3) {
    return number_format($bytes / (1024 ** 2), 2) . ' MB' ;
    }

    return number_format($bytes / (1024 ** 3), 2) . ' GB' ;
    };

    $statusLabel=function ($status) {
    return match ($status) { 'online'=> 'Online',
    'warning' => 'Warning',
    'offline' => 'Offline',
    'local' => 'Local',
    'not_checked' => 'Belum Diperiksa',
    default => ucfirst((string) $status),
    };
    };

    $statusClass = function ($status) {
    return match ($status) {
    'online' => 'ok',
    'warning' => 'warn',
    'offline' => 'bad',
    'local' => 'local',
    'not_checked' => 'local',
    default => 'local',
    };
    };

    $statusDot = function ($status) {
    return match ($status) {
    'online' => '●',
    'warning' => '▲',
    'offline' => '●',
    'local' => '◆',
    'not_checked' => '○',
    default => '○',
    };
    };

    $databaseStatus = $database['status'] ?? 'warning';
    $accessLogStatus = $accessLog['status'] ?? 'warning';
    $blacklistStatus = $blacklist['status'] ?? 'warning';
    $squidStatus = $squid['status'] ?? 'local';
    $mikrotikStatus = $mikrotik['status'] ?? 'local';
    @endphp

    <style>
        .system-status-page {
            --ss-border: #dbe6f2;
            --ss-text: #18345e;
            --ss-muted: #74839a;
            --ss-title: #0d2b5f;
            --ss-primary: #1785f8;
            --ss-primary-soft: #eaf4ff;
            --ss-success: #12b76a;
            --ss-success-soft: #eafaf3;
            --ss-warning: #f59e0b;
            --ss-warning-soft: #fff8e8;
            --ss-danger: #ef476f;
            --ss-danger-soft: #fff0f3;
            --ss-local: #7a5af8;
            --ss-local-soft: #f2efff;
        }

        .system-status-page * {
            box-sizing: border-box;
        }

        .ss-card {
            background: #fff;
            border: 1px solid var(--ss-border);
            border-radius: 18px;
            box-shadow: 0 8px 26px rgba(18, 42, 76, .045);
            padding: 18px;
        }

        .ss-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-top: 14px;
        }

        .ss-card-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 15px;
        }

        .ss-title-wrap {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .ss-title-icon {
            width: 36px;
            height: 36px;
            border-radius: 11px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            background: var(--ss-primary-soft);
            color: var(--ss-primary);
            font-weight: 900;
        }

        .ss-title {
            color: var(--ss-title);
            font-size: 16px;
            font-weight: 800;
            line-height: 1.3;
        }

        .ss-subtitle {
            margin-top: 3px;
            color: var(--ss-muted);
            font-size: 10px;
            line-height: 1.5;
        }

        .ss-status {
            min-height: 30px;
            padding: 0 10px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 10px;
            font-weight: 800;
            white-space: nowrap;
        }

        .ss-status.ok {
            color: #087a49;
            background: var(--ss-success-soft);
            border: 1px solid #ccefdc;
        }

        .ss-status.warn {
            color: #9a6500;
            background: var(--ss-warning-soft);
            border: 1px solid #f6e4b4;
        }

        .ss-status.bad {
            color: #ba2449;
            background: var(--ss-danger-soft);
            border: 1px solid #ffd6df;
        }

        .ss-status.local {
            color: #6547d8;
            background: var(--ss-local-soft);
            border: 1px solid #ded7ff;
        }

        .ss-message {
            margin-bottom: 14px;
            color: #415a78;
            font-size: 12px;
            line-height: 1.7;
        }

        .ss-kv {
            display: grid;
            gap: 0;
        }

        .ss-kv-row {
            display: grid;
            grid-template-columns: 150px minmax(0, 1fr);
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #edf2f7;
            font-size: 11px;
        }

        .ss-kv-row:last-child {
            border-bottom: 0;
        }

        .ss-kv-row span {
            color: var(--ss-muted);
        }

        .ss-kv-row strong {
            color: var(--ss-text);
            text-align: right;
            overflow-wrap: anywhere;
        }

        .ss-wide {
            grid-column: 1 / -1;
        }

        .ss-port-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 12px;
        }

        .ss-port {
            padding: 15px;
            border: 1px solid #e3ebf4;
            border-radius: 14px;
            background: #f9fbfe;
        }

        .ss-port-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 12px;
        }

        .ss-port-name {
            color: #163865;
            font-size: 12px;
            font-weight: 800;
            line-height: 1.4;
        }

        .ss-port-number {
            margin-top: 4px;
            color: #8390a2;
            font-size: 10px;
        }

        .ss-port-message {
            color: #64748b;
            font-size: 10px;
            line-height: 1.6;
        }

        .ss-table-card {
            margin-top: 14px;
        }

        .ss-table-wrap {
            overflow-x: auto;
            border: 1px solid #e4ecf5;
            border-radius: 14px;
        }

        .ss-table {
            width: 100%;
            min-width: 860px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .ss-table th {
            padding: 11px 12px;
            border-bottom: 1px solid #dde8f3;
            background: #f2f7fd;
            color: #49617f;
            font-size: 11px;
            font-weight: 800;
            text-align: left;
            white-space: nowrap;
        }

        .ss-table td {
            padding: 12px;
            border-bottom: 1px solid #edf2f7;
            color: #26415f;
            font-size: 11px;
            vertical-align: middle;
        }

        .ss-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .ss-table tbody tr:hover td {
            background: #fbfdff;
        }

        .ss-code {
            font-family: Consolas, Monaco, monospace;
            font-size: 10px;
            color: #2b4d72;
        }

        .ss-error {
            margin-top: 10px;
            padding: 10px 12px;
            border-radius: 11px;
            background: #fff5f7;
            border: 1px solid #ffd8e0;
            color: #9c2948;
            font-size: 10px;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        .ss-note {
            margin-top: 14px;
            padding: 13px 14px;
            border: 1px solid #e4edf7;
            border-radius: 13px;
            background: #f3f8fe;
            color: #3d5677;
            font-size: 11px;
            line-height: 1.7;
        }

        .ss-checked {
            color: #7a899f;
            font-size: 10px;
            white-space: nowrap;
        }

        @media (max-width: 1000px) {
            .ss-grid {
                grid-template-columns: 1fr;
            }

            .ss-wide {
                grid-column: auto;
            }

            .ss-port-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 650px) {
            .ss-kv-row {
                grid-template-columns: 1fr;
                gap: 4px;
            }

            .ss-kv-row strong {
                text-align: left;
            }

            .ss-card {
                padding: 14px;
            }

            .ss-card-head {
                align-items: flex-start;
                flex-direction: column;
            }

            .ss-status {
                align-self: flex-start;
            }
        }
    </style>

    <div class="system-status-page">

        {{-- HERO --}}
        <section class="hero">
            <div class="hero-bg"></div>

            <div class="hero-copy">
                <div class="eyebrow">
                    ITBRP NETWORK MONITORING
                </div>

                <h1>
                    Status Sistem
                </h1>

                <p>
                    Pantau kondisi database, Squid, access log, blacklist,
                    MikroTik, dan port layanan jaringan dari satu halaman.
                </p>

                <div class="hero-chips">
                    <span class="hero-chip">
                        <span class="chip-icon green">●</span>
                        {{ number_format($online) }} Online
                    </span>

                    <span class="hero-chip">
                        <span class="chip-icon">◆</span>
                        {{ number_format($local) }} Local / Belum Diperiksa
                    </span>

                    <span class="hero-chip">
                        <span class="chip-icon purple">▥</span>
                        {{ number_format($total) }} Pemeriksaan
                    </span>
                </div>
            </div>

            <div class="hero-status">
                <span class="status-dot"></span>
                Pemeriksaan Selesai
            </div>

            <div class="hero-clock">
                <small>
                    {{ $checkedAt->translatedFormat('l, d F Y') }}
                </small>

                <b>
                    {{ $checkedAt->format('H:i') }}
                </b>

                <span>WIB</span>
            </div>
        </section>


        {{-- SUMMARY --}}
        <div class="stats">

            <div class="stat-card blue">
                <div class="stat-top">
                    <div class="stat-icon">▣</div>
                    <div>
                        <div class="stat-label">Total Pemeriksaan</div>
                        <div class="stat-value">{{ number_format($total) }}</div>
                    </div>
                </div>
                <div class="stat-foot">Semua komponen yang diperiksa</div>
            </div>

            <div class="stat-card green">
                <div class="stat-top">
                    <div class="stat-icon">●</div>
                    <div>
                        <div class="stat-label">Online</div>
                        <div class="stat-value">{{ number_format($online) }}</div>
                    </div>
                </div>
                <div class="stat-foot">Komponen yang berhasil diperiksa</div>
            </div>

            <div class="stat-card orange">
                <div class="stat-top">
                    <div class="stat-icon">▲</div>
                    <div>
                        <div class="stat-label">Warning</div>
                        <div class="stat-value">{{ number_format($warning) }}</div>
                    </div>
                </div>
                <div class="stat-foot">Perlu diperhatikan</div>
            </div>

            <div class="stat-card red">
                <div class="stat-top">
                    <div class="stat-icon">●</div>
                    <div>
                        <div class="stat-label">Offline</div>
                        <div class="stat-value">{{ number_format($offline) }}</div>
                    </div>
                </div>
                <div class="stat-foot">Komponen tidak dapat dijangkau</div>
            </div>

            <div class="stat-card purple">
                <div class="stat-top">
                    <div class="stat-icon">◆</div>
                    <div>
                        <div class="stat-label">Local / Belum Diperiksa</div>
                        <div class="stat-value">{{ number_format($local) }}</div>
                    </div>
                </div>
                <div class="stat-foot">Tidak diuji ke production</div>
            </div>

        </div>


        {{-- CORE COMPONENTS --}}
        <div class="ss-grid">

            {{-- DATABASE --}}
            <div class="ss-card">

                <div class="ss-card-head">

                    <div class="ss-title-wrap">
                        <div class="ss-title-icon">DB</div>

                        <div>
                            <div class="ss-title">
                                Database
                            </div>

                            <div class="ss-subtitle">
                                Laravel / MySQL
                            </div>
                        </div>
                    </div>

                    <span class="ss-status {{ $statusClass($databaseStatus) }}">
                        {{ $statusDot($databaseStatus) }}
                        {{ $statusLabel($databaseStatus) }}
                    </span>

                </div>


                <div class="ss-message">
                    {{ $database['message'] ?? '-' }}
                </div>


                <div class="ss-kv">

                    <div class="ss-kv-row">
                        <span>Connection</span>
                        <strong>
                            {{ $database['meta']['connection'] ?? '-' }}
                        </strong>
                    </div>

                    <div class="ss-kv-row">
                        <span>Database</span>
                        <strong>
                            {{ $database['meta']['database'] ?? '-' }}
                        </strong>
                    </div>

                    <div class="ss-kv-row">
                        <span>Response Time</span>
                        <strong>
                            @if(isset($database['response_ms']))
                            {{ number_format((float) $database['response_ms'], 2) }} ms
                            @else
                            -
                            @endif
                        </strong>
                    </div>

                </div>


                @if(!empty($database['error']))
                <div class="ss-error">
                    {{ $database['error'] }}
                </div>
                @endif

            </div>


            {{-- ACCESS LOG --}}
            <div class="ss-card">

                <div class="ss-card-head">

                    <div class="ss-title-wrap">
                        <div class="ss-title-icon">LOG</div>

                        <div>
                            <div class="ss-title">
                                Squid Access Log
                            </div>

                            <div class="ss-subtitle">
                                Sumber monitoring request
                            </div>
                        </div>
                    </div>

                    <span class="ss-status {{ $statusClass($accessLogStatus) }}">
                        {{ $statusDot($accessLogStatus) }}
                        {{ $statusLabel($accessLogStatus) }}
                    </span>

                </div>


                <div class="ss-message">
                    {{ $accessLog['message'] ?? '-' }}
                </div>


                <div class="ss-kv">

                    <div class="ss-kv-row">
                        <span>File</span>
                        <strong class="ss-code">
                            {{ $accessLog['meta']['path'] ?? '-' }}
                        </strong>
                    </div>

                    <div class="ss-kv-row">
                        <span>Ukuran</span>
                        <strong>
                            {{ $formatBytes($accessLog['meta']['size'] ?? null) }}
                        </strong>
                    </div>

                    <div class="ss-kv-row">
                        <span>Terakhir Diubah</span>
                        <strong>
                            {{ $formatDateTime($accessLog['meta']['modified_at'] ?? null) }}
                        </strong>
                    </div>

                </div>


                @if(!empty($accessLog['error']))
                <div class="ss-error">
                    {{ $accessLog['error'] }}
                </div>
                @endif

            </div>


            {{-- BLACKLIST --}}
            <div class="ss-card">

                <div class="ss-card-head">

                    <div class="ss-title-wrap">
                        <div class="ss-title-icon">BL</div>

                        <div>
                            <div class="ss-title">
                                Blacklist Squid
                            </div>

                            <div class="ss-subtitle">
                                File domain filtering
                            </div>
                        </div>
                    </div>

                    <span class="ss-status {{ $statusClass($blacklistStatus) }}">
                        {{ $statusDot($blacklistStatus) }}
                        {{ $statusLabel($blacklistStatus) }}
                    </span>

                </div>


                <div class="ss-message">
                    {{ $blacklist['message'] ?? '-' }}
                </div>


                <div class="ss-kv">

                    <div class="ss-kv-row">
                        <span>File</span>
                        <strong class="ss-code">
                            {{ $blacklist['meta']['path'] ?? '-' }}
                        </strong>
                    </div>

                    <div class="ss-kv-row">
                        <span>Jumlah Baris</span>
                        <strong>
                            {{ number_format((int) ($blacklist['meta']['line_count'] ?? 0)) }}
                        </strong>
                    </div>

                    <div class="ss-kv-row">
                        <span>Status</span>
                        <strong>
                            {{ $statusLabel($blacklistStatus) }}
                        </strong>
                    </div>

                </div>


                @if(!empty($blacklist['error']))
                <div class="ss-error">
                    {{ $blacklist['error'] }}
                </div>
                @endif

            </div>


            {{-- SQUID CONTROL --}}
            <div class="ss-card">

                <div class="ss-card-head">

                    <div class="ss-title-wrap">
                        <div class="ss-title-icon">SQ</div>

                        <div>
                            <div class="ss-title">
                                Squid Control
                            </div>

                            <div class="ss-subtitle">
                                Environment & command safety
                            </div>
                        </div>
                    </div>

                    <span class="ss-status {{ $statusClass($squidStatus) }}">
                        {{ $statusDot($squidStatus) }}
                        {{ $statusLabel($squidStatus) }}
                    </span>

                </div>


                <div class="ss-message">
                    {{ $squid['message'] ?? '-' }}
                </div>


                <div class="ss-kv">

                    <div class="ss-kv-row">
                        <span>Mode</span>
                        <strong>
                            {{ strtoupper($squid['meta']['mode'] ?? config('squid.mode', 'local')) }}
                        </strong>
                    </div>

                    <div class="ss-kv-row">
                        <span>Host</span>
                        <strong>
                            {{ $squid['meta']['host'] ?? config('squid.host', '-') }}
                        </strong>
                    </div>

                    <div class="ss-kv-row">
                        <span>Reconfigure</span>
                        <strong>
                            {{
                            ($squid['meta']['reconfigure_enabled'] ?? false)
                                ? 'Enabled'
                                : 'Disabled'
                        }}
                        </strong>
                    </div>

                </div>


                @if(!empty($squid['meta']['issues']))
                <div class="ss-error">
                    {{ implode(' ', $squid['meta']['issues']) }}
                </div>
                @endif

                @if(!empty($squid['error']))
                <div class="ss-error">
                    {{ $squid['error'] }}
                </div>
                @endif

            </div>


            {{-- MIKROTIK --}}
            <div class="ss-card ss-wide">

                <div class="ss-card-head">

                    <div class="ss-title-wrap">
                        <div class="ss-title-icon">MT</div>

                        <div>
                            <div class="ss-title">
                                MikroTik RouterOS
                            </div>

                            <div class="ss-subtitle">
                                Router API & sinkronisasi perangkat
                            </div>
                        </div>
                    </div>

                    <span class="ss-status {{ $statusClass($mikrotikStatus) }}">
                        {{ $statusDot($mikrotikStatus) }}
                        {{ $statusLabel($mikrotikStatus) }}
                    </span>

                </div>


                <div class="ss-message">
                    {{ $mikrotik['message'] ?? '-' }}
                </div>


                <div class="ss-kv">

                    <div class="ss-kv-row">
                        <span>Host</span>
                        <strong>
                            {{ $mikrotik['meta']['host'] ?? config('mikrotik.host', '-') }}
                        </strong>
                    </div>

                    <div class="ss-kv-row">
                        <span>Port API</span>
                        <strong>
                            {{ $mikrotik['meta']['port'] ?? config('mikrotik.port', '-') }}
                        </strong>
                    </div>

                    <div class="ss-kv-row">
                        <span>SSL</span>
                        <strong>
                            {{
                            ($mikrotik['meta']['ssl'] ?? config('mikrotik.use_ssl', false))
                                ? 'Aktif'
                                : 'Nonaktif'
                        }}
                        </strong>
                    </div>

                    <div class="ss-kv-row">
                        <span>Device Sync</span>
                        <strong>
                            {{
                            ($mikrotik['meta']['device_sync_enabled'] ?? false)
                                ? 'Enabled'
                                : 'Disabled'
                        }}
                        </strong>
                    </div>

                    @if(!empty($mikrotik['meta']['identity']))
                    <div class="ss-kv-row">
                        <span>Router Identity</span>
                        <strong>
                            {{ $mikrotik['meta']['identity'] }}
                        </strong>
                    </div>
                    @endif

                </div>


                @if(!empty($mikrotik['error']))
                <div class="ss-error">
                    {{ $mikrotik['error'] }}
                </div>
                @endif

            </div>

        </div>


        {{-- SQUID PORTS --}}
        <div class="ss-card ss-table-card">

            <div class="ss-card-head">

                <div class="ss-title-wrap">
                    <div class="ss-title-icon">TCP</div>

                    <div>
                        <div class="ss-title">
                            Port Layanan Squid
                        </div>

                        <div class="ss-subtitle">
                            Forward proxy dan intercept ports
                        </div>
                    </div>
                </div>

                <div class="ss-checked">
                    Diperiksa: {{ $formatDateTime($checkedAt) }}
                </div>

            </div>


            <div class="ss-port-grid">

                @foreach($ports as $port)

                @php
                $portStatus = $port['status'] ?? 'not_checked';
                @endphp

                <div class="ss-port">

                    <div class="ss-port-top">

                        <div>
                            <div class="ss-port-name">
                                {{ $port['name'] ?? '-' }}
                            </div>

                            <div class="ss-port-number">
                                {{ $port['meta']['host'] ?? '-' }}
                                :
                                {{ $port['meta']['port'] ?? '-' }}
                            </div>
                        </div>

                        <span class="ss-status {{ $statusClass($portStatus) }}">
                            {{ $statusDot($portStatus) }}
                            {{ $statusLabel($portStatus) }}
                        </span>

                    </div>


                    <div class="ss-port-message">
                        {{ $port['message'] ?? '-' }}
                    </div>


                    @if(isset($port['response_ms']))
                    <div
                        style="
                                margin-top:8px;
                                color:#64748b;
                                font-size:10px;
                            ">
                        Response:
                        {{ number_format((float) $port['response_ms'], 2) }} ms
                    </div>
                    @endif


                    @if(!empty($port['error']))
                    <div class="ss-error">
                        {{ $port['error'] }}
                    </div>
                    @endif

                </div>

                @endforeach

            </div>


            <div class="ss-note">

                @if(strtolower((string) config('squid.mode', 'local')) === 'local')

                Mode <strong>LOCAL</strong> aktif.
                Port 3128, 3129, dan 3130 tidak diprobe dari laptop development,
                sehingga statusnya ditampilkan sebagai
                <strong>Belum Diperiksa</strong>, bukan Offline.

                @else

                Mode <strong>PRODUCTION</strong> aktif.
                Laravel melakukan TCP probe ke host dan port Squid yang dikonfigurasi.

                @endif

            </div>

        </div>


        {{-- ALL CHECKS --}}
        <div class="ss-card ss-table-card">

            <div class="ss-card-head">

                <div class="ss-title-wrap">
                    <div class="ss-title-icon">▦</div>

                    <div>
                        <div class="ss-title">
                            Semua Pemeriksaan
                        </div>

                        <div class="ss-subtitle">
                            Ringkasan seluruh komponen health check
                        </div>
                    </div>
                </div>

                <span class="badge blue">
                    {{ number_format($total) }} komponen
                </span>

            </div>


            <div class="ss-table-wrap">

                <table class="ss-table">

                    <thead>
                        <tr>
                            <th>Komponen</th>
                            <th>Status</th>
                            <th>Pesan</th>
                            <th>Response</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($checks as $check)

                        @php
                        $checkStatus = $check['status'] ?? 'warning';
                        @endphp

                        <tr>

                            <td>
                                <strong>
                                    {{ $check['name'] ?? '-' }}
                                </strong>
                            </td>


                            <td>
                                <span class="ss-status {{ $statusClass($checkStatus) }}">
                                    {{ $statusDot($checkStatus) }}
                                    {{ $statusLabel($checkStatus) }}
                                </span>
                            </td>


                            <td>
                                {{ $check['message'] ?? '-' }}
                            </td>


                            <td>
                                @if(isset($check['response_ms']))
                                {{ number_format((float) $check['response_ms'], 2) }} ms
                                @else
                                -
                                @endif
                            </td>

                        </tr>

                        @empty

                        <tr>
                            <td colspan="4" style="text-align:center;padding:30px">
                                Belum ada data pemeriksaan.
                            </td>
                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    @endsection