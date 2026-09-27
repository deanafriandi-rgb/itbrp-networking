@extends('layouts.monitoring')

@section('title', 'Status Sistem')

@section('content')

@php
/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

$now =
now('Asia/Jakarta');

$checkedAt =
$checked_at
?? $now;

$total =
(int) ($summary['total'] ?? 0);

$online =
(int) ($summary['online'] ?? 0);

$warning =
(int) ($summary['warning'] ?? 0);

$offline =
(int) ($summary['offline'] ?? 0);

$local =
(int) ($summary['local'] ?? 0);


$statusLabel =
function ($status) {
return match ($status) {
'online' => 'Online',
'warning' => 'Warning',
'offline' => 'Offline',
'local' => 'Local',
'not_checked' => 'Belum Diperiksa',
default => ucfirst((string) $status),
};
};


$statusBadge =
function ($status) {
return match ($status) {
'online' => 'ok',
'warning' => 'warn',
'offline' => 'bad',
'local',
'not_checked' => 'blue',
default => 'blue',
};
};


$formatDateTime =
function ($value) {
if (!$value) {
return '-';
}

try {
return \Illuminate\Support\Carbon::parse(
$value
)
->timezone(
'Asia/Jakarta'
)
->format(
'd/m/Y H:i:s'
);
} catch (\Throwable $e) {
return (string) $value;
}
};


$formatBytes =
function ($bytes) {
if ($bytes === null || $bytes === '') {
return '-';
}

$bytes =
(float) $bytes;

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


/*
|--------------------------------------------------------------------------
| Main Status
|--------------------------------------------------------------------------
*/

$databaseStatus =
$database['status']
?? 'warning';

$accessLogStatus =
$accessLog['status']
?? 'warning';

$blacklistStatus =
$blacklist['status']
?? 'warning';

$squidStatus =
$squid['status']
?? 'local';

$mikrotikStatus =
$mikrotik['status']
?? 'local';


/*
|--------------------------------------------------------------------------
| Cache Summary
|--------------------------------------------------------------------------
*/

$cacheHit =
(int) ($cacheSummary['hit'] ?? 0);

$cacheMiss =
(int) ($cacheSummary['miss'] ?? 0);

$cacheRatio =
(float) ($cacheSummary['hit_ratio'] ?? 0);


/*
|--------------------------------------------------------------------------
| Overall Label
|--------------------------------------------------------------------------
*/

if ($offline > 0) {
$overallLabel =
'Perlu Dicek';

$overallClass =
'bad';

} elseif ($warning > 0) {
$overallLabel =
'Warning';

$overallClass =
'warn';

} elseif (
strtolower(
(string) config(
'app.env',
'local'
)
) === 'local'
) {
$overallLabel =
'Local';

$overallClass =
'blue';

} else {
$overallLabel =
'Normal';

$overallClass =
'ok';
}
@endphp


<style>
    .monitoring-status {
        --ms-border: #dce7f2;
        --ms-text: #17385f;
        --ms-muted: #78879b;
    }

    .monitoring-status * {
        box-sizing: border-box;
    }

    .ms-status-grid {
        display: grid;
        grid-template-columns:
            repeat(2,
                minmax(0, 1fr));
        gap: 12px;
        margin-top: 12px;
    }

    .ms-service {
        padding: 16px;
        border: 1px solid #e1eaf4;
        border-radius: 15px;
        background: #fbfdff;
    }

    .ms-service-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 12px;
    }

    .ms-service-title {
        color: #173b66;
        font-size: 13px;
        font-weight: 800;
    }

    .ms-service-sub {
        margin-top: 3px;
        color: #8190a3;
        font-size: 9px;
    }

    .ms-kv {
        display: grid;
    }

    .ms-kv-row {
        display: grid;
        grid-template-columns:
            145px minmax(0, 1fr);
        gap: 12px;
        padding: 9px 0;
        border-bottom:
            1px solid #edf2f7;
        font-size: 10px;
    }

    .ms-kv-row:last-child {
        border-bottom: 0;
    }

    .ms-kv-row span {
        color: #7d8b9f;
    }

    .ms-kv-row strong {
        color: #24496f;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .ms-port-grid {
        display: grid;
        grid-template-columns:
            repeat(3,
                minmax(0, 1fr));
        gap: 10px;
        margin-top: 12px;
    }

    .ms-port-card {
        padding: 14px;
        border: 1px solid #e3ebf4;
        border-radius: 14px;
        background: #f9fbfe;
    }

    .ms-port-top {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        align-items: flex-start;
    }

    .ms-port-title {
        color: #173b66;
        font-size: 11px;
        font-weight: 800;
        line-height: 1.5;
    }

    .ms-port-address {
        margin-top: 4px;
        color: #8290a3;
        font-family:
            Consolas,
            Monaco,
            monospace;
        font-size: 9px;
    }

    .ms-port-message {
        margin-top: 11px;
        color: #647891;
        font-size: 9px;
        line-height: 1.6;
    }

    .ms-note {
        margin-top: 12px;
        padding: 13px 14px;
        border: 1px solid #e2ebf5;
        border-radius: 13px;
        background: #f7faff;
        color: #60758e;
        font-size: 10px;
        line-height: 1.75;
    }

    .ms-readonly-grid {
        display: grid;
        grid-template-columns:
            1.2fr .8fr;
        gap: 12px;
        margin-top: 12px;
    }

    .ms-metric-grid {
        display: grid;
        grid-template-columns:
            repeat(3,
                minmax(0, 1fr));
        gap: 10px;
        margin-top: 14px;
    }

    .ms-metric {
        padding: 14px;
        border: 1px solid #e3ebf4;
        border-radius: 14px;
        background: #f9fbfe;
    }

    .ms-metric span {
        display: block;
        color: #7e8ca0;
        font-size: 9px;
    }

    .ms-metric strong {
        display: block;
        margin-top: 6px;
        color: #163b67;
        font-size: 21px;
    }

    .ms-readonly {
        height: 100%;
        min-height: 220px;
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

    .ms-readonly-tag {
        width: fit-content;
        margin-bottom: 12px;
        padding: 6px 9px;
        border-radius: 999px;
        background: #fff;
        color: #1680ea;
        border: 1px solid #dce9f7;
        font-size: 9px;
        font-weight: 900;
    }

    .ms-readonly h3 {
        margin: 0;
        color: #0e3265;
        font-size: 20px;
        line-height: 1.35;
    }

    .ms-readonly p {
        margin: 9px 0 0;
        color: #58708e;
        font-size: 10px;
        line-height: 1.8;
    }

    @media (max-width: 1000px) {

        .ms-status-grid,
        .ms-readonly-grid {
            grid-template-columns: 1fr;
        }

        .ms-port-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        .ms-metric-grid {
            grid-template-columns: 1fr;
        }

        .ms-kv-row {
            grid-template-columns: 1fr;
            gap: 4px;
        }

        .ms-kv-row strong {
            text-align: left;
        }
    }
</style>


<div class="monitoring-status">

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
                Status Infrastruktur
                <br>

                <span class="accent">
                    untuk Layanan Jaringan yang Andal
                </span>
            </h1>

            <p>
                Pantau status database, Squid Proxy, access log,
                blacklist, MikroTik, serta port layanan yang sudah
                terintegrasi dengan dashboard monitoring.
            </p>

            <div class="hero-chips">

                <span class="hero-chip">
                    <span class="chip-icon green">▣</span>
                    Health Check
                </span>

                <span class="hero-chip">
                    <span class="chip-icon">◆</span>
                    Squid + MikroTik
                </span>

                <span class="hero-chip">
                    <span class="chip-icon purple">▥</span>
                    Read-only
                </span>

            </div>

        </div>


        <div class="hero-status">
            <span class="status-dot"></span>
            {{ $overallLabel }}
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
                        Database
                    </div>

                    <div
                        class="stat-value"
                        style="font-size:20px">
                        {{ $statusLabel($databaseStatus) }}
                    </div>
                </div>

            </div>

            <div class="stat-foot">
                {{ $database['message'] ?? '-' }}
            </div>

        </div>


        <div class="stat-card blue">

            <div class="stat-top">

                <div class="stat-icon">
                    SQ
                </div>

                <div>
                    <div class="stat-label">
                        Squid Control
                    </div>

                    <div
                        class="stat-value"
                        style="font-size:20px">
                        {{ $statusLabel($squidStatus) }}
                    </div>
                </div>

            </div>

            <div class="stat-foot">
                Mode
                {{ strtoupper(
                    $squid['meta']['mode']
                    ?? config('squid.mode', 'local')
                ) }}
            </div>

        </div>


        <div class="stat-card purple">

            <div class="stat-top">

                <div class="stat-icon">
                    LOG
                </div>

                <div>
                    <div class="stat-label">
                        Access Log
                    </div>

                    <div
                        class="stat-value"
                        style="font-size:20px">
                        {{ $statusLabel($accessLogStatus) }}
                    </div>
                </div>

            </div>

            <div class="stat-foot">
                {{ $formatBytes(
                    $accessLog['meta']['size']
                    ?? null
                ) }}
            </div>

        </div>


        <div class="stat-card orange">

            <div class="stat-top">

                <div class="stat-icon">
                    MT
                </div>

                <div>
                    <div class="stat-label">
                        MikroTik
                    </div>

                    <div
                        class="stat-value"
                        style="font-size:20px">
                        {{ $statusLabel($mikrotikStatus) }}
                    </div>
                </div>

            </div>

            <div class="stat-foot">
                {{ $mikrotik['message'] ?? '-' }}
            </div>

        </div>


        <div class="stat-card red">

            <div class="stat-top">

                <div class="stat-icon">
                    BL
                </div>

                <div>
                    <div class="stat-label">
                        Blacklist
                    </div>

                    <div
                        class="stat-value"
                        style="font-size:20px">
                        {{ $statusLabel($blacklistStatus) }}
                    </div>
                </div>

            </div>

            <div class="stat-foot">
                {{
                    number_format(
                        (int) (
                            $blacklist['meta']['line_count']
                            ?? 0
                        ),
                        0,
                        ',',
                        '.'
                    )
                }}
                baris file
            </div>

        </div>

    </div>


    {{-- ===================================================== --}}
    {{-- SERVICE DETAIL --}}
    {{-- ===================================================== --}}

    <div class="ms-status-grid section-pad">

        {{-- DATABASE --}}
        <div class="ms-service">

            <div class="ms-service-head">

                <div>
                    <div class="ms-service-title">
                        Database Laravel / MySQL
                    </div>

                    <div class="ms-service-sub">
                        Koneksi penyimpanan data monitoring
                    </div>
                </div>

                <span class="badge {{ $statusBadge($databaseStatus) }}">
                    ● {{ $statusLabel($databaseStatus) }}
                </span>

            </div>


            <div class="ms-kv">

                <div class="ms-kv-row">
                    <span>Connection</span>

                    <strong>
                        {{ $database['meta']['connection'] ?? '-' }}
                    </strong>
                </div>

                <div class="ms-kv-row">
                    <span>Database</span>

                    <strong>
                        {{ $database['meta']['database'] ?? '-' }}
                    </strong>
                </div>

                <div class="ms-kv-row">
                    <span>Response</span>

                    <strong>
                        @if(isset($database['response_ms']))
                        {{ number_format(
                                (float) $database['response_ms'],
                                2,
                                ',',
                                '.'
                            ) }} ms
                        @else
                        -
                        @endif
                    </strong>
                </div>

            </div>

        </div>


        {{-- SQUID --}}
        <div class="ms-service">

            <div class="ms-service-head">

                <div>
                    <div class="ms-service-title">
                        Squid Proxy Control
                    </div>

                    <div class="ms-service-sub">
                        Mode dan safety environment
                    </div>
                </div>

                <span class="badge {{ $statusBadge($squidStatus) }}">
                    ● {{ $statusLabel($squidStatus) }}
                </span>

            </div>


            <div class="ms-kv">

                <div class="ms-kv-row">
                    <span>Host</span>

                    <strong>
                        {{
                            $squid['meta']['host']
                            ?? config('squid.host', '-')
                        }}
                    </strong>
                </div>

                <div class="ms-kv-row">
                    <span>Mode</span>

                    <strong>
                        {{
                            strtoupper(
                                $squid['meta']['mode']
                                ?? config(
                                    'squid.mode',
                                    'local'
                                )
                            )
                        }}
                    </strong>
                </div>

                <div class="ms-kv-row">
                    <span>Reconfigure</span>

                    <strong>
                        {{
                            (
                                $squid['meta']['reconfigure_enabled']
                                ?? false
                            )
                                ? 'Enabled'
                                : 'Disabled'
                        }}
                    </strong>
                </div>

            </div>

        </div>


        {{-- ACCESS LOG --}}
        <div class="ms-service">

            <div class="ms-service-head">

                <div>
                    <div class="ms-service-title">
                        Squid Access Log
                    </div>

                    <div class="ms-service-sub">
                        Sumber aktivitas jaringan
                    </div>
                </div>

                <span class="badge {{ $statusBadge($accessLogStatus) }}">
                    ● {{ $statusLabel($accessLogStatus) }}
                </span>

            </div>


            <div class="ms-kv">

                <div class="ms-kv-row">
                    <span>Ukuran File</span>

                    <strong>
                        {{ $formatBytes(
                            $accessLog['meta']['size']
                            ?? null
                        ) }}
                    </strong>
                </div>

                <div class="ms-kv-row">
                    <span>Terakhir Diubah</span>

                    <strong>
                        {{ $formatDateTime(
                            $accessLog['meta']['modified_at']
                            ?? null
                        ) }}
                    </strong>
                </div>

                <div class="ms-kv-row">
                    <span>Path</span>

                    <strong>
                        {{ $accessLog['meta']['path'] ?? '-' }}
                    </strong>
                </div>

            </div>

        </div>


        {{-- MIKROTIK --}}
        <div class="ms-service">

            <div class="ms-service-head">

                <div>
                    <div class="ms-service-title">
                        MikroTik RouterOS
                    </div>

                    <div class="ms-service-sub">
                        API dan sinkronisasi perangkat
                    </div>
                </div>

                <span class="badge {{ $statusBadge($mikrotikStatus) }}">
                    ● {{ $statusLabel($mikrotikStatus) }}
                </span>

            </div>


            <div class="ms-kv">

                <div class="ms-kv-row">
                    <span>Host</span>

                    <strong>
                        {{
                            $mikrotik['meta']['host']
                            ?? config(
                                'mikrotik.host',
                                '-'
                            )
                        }}
                    </strong>
                </div>

                <div class="ms-kv-row">
                    <span>Port API</span>

                    <strong>
                        {{
                            $mikrotik['meta']['port']
                            ?? config(
                                'mikrotik.port',
                                '-'
                            )
                        }}
                    </strong>
                </div>

                <div class="ms-kv-row">
                    <span>Device Sync</span>

                    <strong>
                        {{
                            (
                                $mikrotik['meta']['device_sync_enabled']
                                ?? false
                            )
                                ? 'Enabled'
                                : 'Disabled'
                        }}
                    </strong>
                </div>

            </div>

        </div>

    </div>


    {{-- ===================================================== --}}
    {{-- PORTS --}}
    {{-- ===================================================== --}}

    <div class="panel section-pad">

        <div class="panel-head">

            <div class="panel-title">
                ⌘ Port Monitoring Squid Proxy
            </div>

            <span
                style="
                    color:#8391a5;
                    font-size:10px;
                    font-weight:700;
                ">
                {{ $formatDateTime($checkedAt) }}
            </span>

        </div>


        <div class="ms-port-grid">

            @foreach($ports as $port)

            @php
            $portStatus =
            $port['status']
            ?? 'not_checked';
            @endphp

            <div class="ms-port-card">

                <div class="ms-port-top">

                    <div>

                        <div class="ms-port-title">
                            {{ $port['name'] ?? '-' }}
                        </div>

                        <div class="ms-port-address">
                            {{
                                    $port['meta']['host']
                                    ?? '-'
                                }}
                            :
                            {{
                                    $port['meta']['port']
                                    ?? '-'
                                }}
                        </div>

                    </div>


                    <span class="badge {{ $statusBadge($portStatus) }}">
                        ● {{ $statusLabel($portStatus) }}
                    </span>

                </div>


                <div class="ms-port-message">

                    {{ $port['message'] ?? '-' }}

                    @if(isset($port['response_ms']))
                    <br>
                    Response:
                    {{ number_format(
                                (float) $port['response_ms'],
                                2,
                                ',',
                                '.'
                            ) }} ms
                    @endif

                </div>

            </div>

            @endforeach

        </div>


        <div class="ms-note">

            @if(
            strtolower(
            (string) config(
            'squid.mode',
            'local'
            )
            )
            === 'local'
            )

            Mode <strong>LOCAL</strong> sedang aktif.
            Port Squid sengaja tidak diprobe dari laptop development,
            sehingga status port ditampilkan sebagai
            <strong>Belum Diperiksa</strong>, bukan Offline.

            @else

            Mode <strong>PRODUCTION</strong> aktif.
            Port Squid diprobe melalui TCP berdasarkan host dan port
            yang dikonfigurasi.

            @endif

        </div>

    </div>


    {{-- ===================================================== --}}
    {{-- CACHE + READ ONLY / FAIL-OPEN NOTE --}}
    {{-- ===================================================== --}}

    <div class="ms-readonly-grid section-pad">

        <div class="panel">

            <div class="panel-head">

                <div class="panel-title">
                    ▤ Ringkasan Cache Squid
                </div>

                <span
                    style="
                        color:#8391a5;
                        font-size:10px;
                        font-weight:700;
                    ">
                    24 jam
                </span>

            </div>


            <div class="ms-metric-grid">

                <div class="ms-metric">

                    <span>
                        Cache HIT
                    </span>

                    <strong>
                        {{ number_format(
                            $cacheHit,
                            0,
                            ',',
                            '.'
                        ) }}
                    </strong>

                </div>


                <div class="ms-metric">

                    <span>
                        Cache MISS
                    </span>

                    <strong>
                        {{ number_format(
                            $cacheMiss,
                            0,
                            ',',
                            '.'
                        ) }}
                    </strong>

                </div>


                <div class="ms-metric">

                    <span>
                        Hit Ratio
                    </span>

                    <strong>
                        {{ number_format(
                            $cacheRatio,
                            1,
                            ',',
                            '.'
                        ) }}%
                    </strong>

                </div>

            </div>


            <div class="ms-note">
                Ringkasan cache berasal dari access log dengan kategori
                <strong>CACHE_HIT</strong> dan
                <strong>CACHE_MISS</strong>.
                Nilai ini menunjukkan aktivitas cache,
                bukan health check proses Squid secara langsung.
            </div>

        </div>


        <div class="ms-readonly">

            <div class="ms-readonly-tag">
                READ-ONLY STATUS
            </div>

            <h3>
                Status yang ditampilkan
                hanya yang benar-benar terukur
            </h3>

            <p>
                CPU, memory, disk, uptime server, status firewall,
                Internet Gateway, dan Netwatch fail-open belum dikoleksi
                oleh service Laravel saat ini sehingga tidak ditampilkan
                sebagai angka/status palsu.
            </p>

            <p>
                Saat integrasi production berikutnya dibuat, data tersebut
                dapat ditambahkan dari Ubuntu dan RouterOS tanpa mengubah
                konsep halaman monitoring.
            </p>

        </div>

    </div>


    {{-- ===================================================== --}}
    {{-- ALL CHECKS --}}
    {{-- ===================================================== --}}

    <div
        class="panel section-pad"
        style="margin-top:12px">

        <div class="panel-head">

            <div class="panel-title">
                ⚙ Status Layanan yang Diperiksa
            </div>

            <span class="badge {{ $overallClass }}">
                {{ $overallLabel }}
            </span>

        </div>


        <div class="table-wrap">

            <table class="table">

                <thead>
                    <tr>
                        <th>Layanan</th>
                        <th>Status</th>
                        <th>Response</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>


                <tbody>

                    @forelse($checks as $check)

                    @php
                    $checkStatus =
                    $check['status']
                    ?? 'warning';
                    @endphp

                    <tr>

                        <td>
                            <strong>
                                {{ $check['name'] ?? '-' }}
                            </strong>
                        </td>


                        <td>

                            <span class="badge {{ $statusBadge($checkStatus) }}">
                                ● {{ $statusLabel($checkStatus) }}
                            </span>

                        </td>


                        <td>
                            @if(isset($check['response_ms']))
                            {{ number_format(
                                        (float) $check['response_ms'],
                                        2,
                                        ',',
                                        '.'
                                    ) }} ms
                            @else
                            -
                            @endif
                        </td>


                        <td>
                            {{ $check['message'] ?? '-' }}
                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td
                            colspan="4"
                            style="
                                    text-align:center;
                                    padding:36px;
                                ">
                            Belum ada hasil pemeriksaan sistem.
                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection