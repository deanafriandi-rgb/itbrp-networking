@extends('layouts.monitoring')

@section('title', 'Perangkat')

@section('content')

@php
$now =
now('Asia/Jakarta');

$total =
(int) ($summary['total'] ?? 0);

$online =
(int) ($summary['online'] ?? 0);

$offline =
(int) ($summary['offline'] ?? 0);

$staff =
(int) ($summary['staff'] ?? 0);

$student =
(int) ($summary['student'] ?? 0);

$guest =
(int) ($summary['guest'] ?? 0);


$ownerTotal =
max(
1,
(int) collect(
$ownerDistribution ?? []
)->sum('total')
);


$statusOnlinePercentage =
$total > 0
? round(
($online / $total) * 100,
1
)
: 0;

$statusOfflinePercentage =
$total > 0
? round(
($offline / $total) * 100,
1
)
: 0;


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


$ownerLabel =
function ($value) {

return match ($value) {
'dosen' => 'Dosen',
'staff' => 'Staff',
'dosen_staff' => 'Dosen / Staff',
'mahasiswa' => 'Mahasiswa',
'tamu' => 'Tamu',
'belum_diklasifikasikan' => 'Belum Diklasifikasikan',
null, '' => 'Belum Diklasifikasikan',
default => ucfirst(
str_replace(
'_',
' ',
(string) $value
)
),
};
};
@endphp


<style>
    .monitoring-devices {
        --md-border: #dce7f2;
        --md-text: #17385f;
        --md-muted: #78879b;
        --md-primary: #1785f8;
    }

    .monitoring-devices * {
        box-sizing: border-box;
    }

    .md-table-tools {
        display: grid;
        grid-template-columns:
            minmax(260px, 1fr) 150px 170px 170px auto auto;
        gap: 9px;
        margin-bottom: 14px;
        padding: 11px;
        border: 1px solid #e1eaf4;
        border-radius: 14px;
        background: #f8fbff;
    }

    .md-input,
    .md-select {
        width: 100%;
        height: 42px;
        border: 1px solid #d7e2ee;
        border-radius: 11px;
        outline: none;
        background: #fff;
        color: #18345f;
        font: inherit;
        font-size: 11px;
    }

    .md-input {
        padding: 0 13px;
    }

    .md-select {
        padding: 0 10px;
    }

    .md-input:focus,
    .md-select:focus {
        border-color: #78b9ff;
        box-shadow:
            0 0 0 4px rgba(30, 134, 250, .10);
    }

    .md-btn {
        min-height: 42px;
        border: 0;
        border-radius: 11px;
        padding: 0 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        text-decoration: none;
        cursor: pointer;
        font: inherit;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .md-btn.primary {
        color: #fff;
        background:
            linear-gradient(135deg,
                #1e86fa,
                #4da5ff);
    }

    .md-btn.neutral {
        color: #52627a;
        background: #eef3f8;
    }

    .md-empty {
        padding: 40px 18px;
        text-align: center;
        color: #8190a3;
        font-size: 11px;
        line-height: 1.7;
    }

    .md-device-name {
        display: grid;
        gap: 3px;
    }

    .md-device-name strong {
        color: #173b66;
        font-size: 11px;
    }

    .md-device-name small {
        color: #8794a7;
        font-size: 9px;
    }

    .md-code {
        font-family:
            Consolas,
            Monaco,
            monospace;
        font-size: 10px;
    }

    .md-bottom-grid {
        display: grid;
        grid-template-columns:
            repeat(3,
                minmax(0, 1fr));
        gap: 12px;
        margin-top: 12px;
    }

    .md-progress-list {
        display: grid;
        gap: 12px;
        margin-top: 14px;
    }

    .md-progress-row {
        display: grid;
        gap: 6px;
    }

    .md-progress-top {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        color: #526983;
        font-size: 10px;
    }

    .md-progress-top b {
        color: #21476f;
    }

    .md-progress-track {
        height: 7px;
        overflow: hidden;
        border-radius: 999px;
        background: #edf2f7;
    }

    .md-progress-track i {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: #318ef6;
    }

    .md-status-box {
        display: grid;
        grid-template-columns:
            repeat(2,
                minmax(0, 1fr));
        gap: 10px;
        margin-top: 14px;
    }

    .md-status-mini {
        padding: 14px;
        border: 1px solid #e3ebf4;
        border-radius: 14px;
        background: #f9fbfe;
    }

    .md-status-mini span {
        display: block;
        color: #7e8ca0;
        font-size: 10px;
    }

    .md-status-mini strong {
        display: block;
        margin-top: 6px;
        color: #163b67;
        font-size: 22px;
    }

    .md-status-mini small {
        display: block;
        margin-top: 5px;
        color: #8a97a8;
        font-size: 9px;
    }

    .md-latest-row {
        display: grid;
        grid-template-columns:
            36px minmax(0, 1fr) auto;
        gap: 10px;
        align-items: center;
        padding: 10px 0;
        border-bottom:
            1px solid #edf2f7;
    }

    .md-latest-row:last-child {
        border-bottom: 0;
    }

    .md-latest-icon {
        width: 35px;
        height: 35px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        background: #eef6ff;
        color: #1785f8;
        font-weight: 900;
    }

    .md-latest-copy {
        min-width: 0;
    }

    .md-latest-copy b,
    .md-latest-copy small {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .md-latest-copy b {
        color: #183a63;
        font-size: 10px;
    }

    .md-latest-copy small {
        margin-top: 3px;
        color: #8794a7;
        font-size: 9px;
    }

    .md-note {
        margin-top: 12px;
        color: #78879b;
        font-size: 9px;
        line-height: 1.6;
    }

    @media (max-width: 1200px) {
        .md-table-tools {
            grid-template-columns:
                1fr 1fr 1fr;
        }

        .md-input {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 950px) {
        .md-bottom-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        .md-table-tools {
            grid-template-columns: 1fr;
        }

        .md-input {
            grid-column: auto;
        }

        .md-status-box {
            grid-template-columns: 1fr;
        }

        .md-btn {
            width: 100%;
        }
    }
</style>


<div class="monitoring-devices">

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
                Monitoring Perangkat
                <br>

                <span class="accent">
                    untuk Kampus yang Lebih Terhubung
                </span>
            </h1>

            <p>
                Pantau perangkat yang tercatat dari ARP dan DHCP MikroTik,
                lihat status online/offline, identitas perangkat,
                segmen jaringan, serta aktivitas data yang terhubung
                dengan IP perangkat.
            </p>

            <div class="hero-chips">

                <span class="hero-chip">
                    <span class="chip-icon green">▣</span>
                    ARP + DHCP
                </span>

                <span class="hero-chip">
                    <span class="chip-icon">◆</span>
                    MAC sebagai Identitas
                </span>

                <span class="hero-chip">
                    <span class="chip-icon purple">▥</span>
                    Read-only
                </span>

            </div>

        </div>


        <div class="hero-status">
            <span class="status-dot"></span>
            Monitoring Perangkat
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
                    ▣
                </div>

                <div>
                    <div class="stat-label">
                        Total Perangkat
                    </div>

                    <div class="stat-value">
                        {{ number_format($total, 0, ',', '.') }}
                    </div>
                </div>

            </div>

            <div class="stat-foot">
                Tersimpan di database
            </div>

        </div>


        <div class="stat-card green">

            <div class="stat-top">

                <div class="stat-icon">
                    ⌁
                </div>

                <div>
                    <div class="stat-label">
                        Perangkat Online
                    </div>

                    <div class="stat-value">
                        {{ number_format($online, 0, ',', '.') }}
                    </div>
                </div>

            </div>

            <div class="stat-foot">
                Terlihat aktif saat sinkronisasi
            </div>

        </div>


        <div class="stat-card purple">

            <div class="stat-top">

                <div class="stat-icon">
                    ◉
                </div>

                <div>
                    <div class="stat-label">
                        Dosen / Staff
                    </div>

                    <div class="stat-value">
                        {{ number_format($staff, 0, ',', '.') }}
                    </div>
                </div>

            </div>

            <div class="stat-foot">
                Berdasarkan owner type
            </div>

        </div>


        <div class="stat-card red">

            <div class="stat-top">

                <div class="stat-icon">
                    ◆
                </div>

                <div>
                    <div class="stat-label">
                        Mahasiswa
                    </div>

                    <div class="stat-value">
                        {{ number_format($student, 0, ',', '.') }}
                    </div>
                </div>

            </div>

            <div class="stat-foot">
                Berdasarkan owner type
            </div>

        </div>


        <div class="stat-card orange">

            <div class="stat-top">

                <div class="stat-icon">
                    ◉
                </div>

                <div>
                    <div class="stat-label">
                        Tamu
                    </div>

                    <div class="stat-value">
                        {{ number_format($guest, 0, ',', '.') }}
                    </div>
                </div>

            </div>

            <div class="stat-foot">
                Berdasarkan owner type
            </div>

        </div>

    </div>


    {{-- ===================================================== --}}
    {{-- DEVICE TABLE --}}
    {{-- ===================================================== --}}

    <div class="section-pad">

        <div class="panel">

            <div class="panel-head">

                <div class="panel-title">
                    ▣ Daftar Perangkat Terhubung
                </div>

                <span
                    style="
                        color:#8391a5;
                        font-size:10px;
                        font-weight:700;
                    ">
                    Read-only
                </span>

            </div>


            <form
                method="GET"
                action="{{ route('monitoring.devices') }}"
                class="md-table-tools">

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    class="md-input"
                    placeholder="Cari perangkat, pemilik, IP, atau MAC...">


                <select
                    name="status"
                    class="md-select">
                    <option value="">
                        Semua Status
                    </option>

                    <option
                        value="online"
                        @selected(request('status')==='online' )>
                        Online
                    </option>

                    <option
                        value="offline"
                        @selected(request('status')==='offline' )>
                        Offline
                    </option>
                </select>


                <select
                    name="owner_type"
                    class="md-select">
                    <option value="">
                        Semua Pemilik
                    </option>

                    @foreach($ownerTypes as $type)

                    <option
                        value="{{ $type }}"
                        @selected(request('owner_type')===$type)>
                        {{ $ownerLabel($type) }}
                    </option>

                    @endforeach
                </select>


                <select
                    name="segment"
                    class="md-select">
                    <option value="">
                        Semua Segmen
                    </option>

                    @foreach($segments as $segment)

                    <option
                        value="{{ $segment }}"
                        @selected(request('segment')===$segment)>
                        {{ $segment }}
                    </option>

                    @endforeach
                </select>


                <button
                    type="submit"
                    class="md-btn primary">
                    Filter
                </button>


                <a
                    href="{{ route('monitoring.devices') }}"
                    class="md-btn neutral">
                    Reset
                </a>

            </form>


            <div class="table-wrap">

                <table class="table">

                    <thead>
                        <tr>
                            <th>Nama Perangkat</th>
                            <th>Pemilik</th>
                            <th>IP Address</th>
                            <th>MAC Address</th>
                            <th>Sistem Operasi</th>
                            <th>Segmen</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th>Data 24 Jam</th>
                            <th>Last Seen</th>
                        </tr>
                    </thead>


                    <tbody>

                        @forelse($devices as $device)

                        @php
                        $isOnline =
                        (bool) (
                        $device->{$onlineColumn}
                        ?? false
                        );

                        $lastSeen =
                        $device->{$lastSeenColumn}
                        ?? null;

                        $displayName =
                        $device->device_name
                        ?: $device->hostname
                        ?: 'Perangkat Tanpa Nama';

                        $usage =
                        $device->ip_address
                        ? $usageByIp->get(
                        $device->ip_address
                        )
                        : null;

                        $usageBytes =
                        (int) (
                        $usage->total_bytes
                        ?? 0
                        );
                        @endphp


                        <tr>

                            <td>

                                <div class="md-device-name">

                                    <strong>
                                        ▣ {{ $displayName }}
                                    </strong>

                                    <small>
                                        {{ $device->device_type ?: 'Jenis belum diisi' }}
                                    </small>

                                </div>

                            </td>


                            <td>

                                <div class="md-device-name">

                                    <strong>
                                        {{ $device->owner_name ?: '-' }}
                                    </strong>

                                    <small>
                                        {{ $ownerLabel($device->owner_type) }}
                                    </small>

                                </div>

                            </td>


                            <td>
                                <span class="md-code">
                                    {{ $device->ip_address ?: '-' }}
                                </span>
                            </td>


                            <td>
                                <span class="md-code">
                                    {{ $device->mac_address ?: '-' }}
                                </span>
                            </td>


                            <td>
                                {{ $device->operating_system ?: '-' }}
                            </td>


                            <td>
                                {{ $device->segment ?: '-' }}
                            </td>


                            <td>
                                {{ $device->location ?: '-' }}
                            </td>


                            <td>

                                @if($isOnline)

                                <span class="badge ok">
                                    ● Online
                                </span>

                                @else

                                <span class="badge bad">
                                    ● Offline
                                </span>

                                @endif

                            </td>


                            <td>

                                <strong>
                                    {{ $formatBytes($usageBytes) }}
                                </strong>

                            </td>


                            <td>
                                {{ $formatDateTime($lastSeen) }}
                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td
                                colspan="10"
                                style="
                                        text-align:center;
                                        padding:36px;
                                    ">
                                Belum ada data perangkat.
                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            @if($devices->hasPages())

            <div style="margin-top:16px">
                {{ $devices->links() }}
            </div>

            @endif


            <div class="md-note">
                Penggunaan data dihitung dari access log 24 jam berdasarkan
                IP Address yang saat ini tersimpan pada perangkat. Jika IP
                perangkat berubah, histori lama tidak otomatis dipindahkan
                ke MAC Address perangkat.
            </div>

        </div>

    </div>


    {{-- ===================================================== --}}
    {{-- DISTRIBUTION / STATUS / LATEST --}}
    {{-- ===================================================== --}}

    <div class="md-bottom-grid section-pad">

        {{-- OWNER DISTRIBUTION --}}
        <div class="panel">

            <div class="panel-head">

                <div class="panel-title">
                    ◕ Distribusi Pemilik
                </div>

            </div>


            <div class="md-progress-list">

                @forelse($ownerDistribution as $item)

                @php
                $percentage =
                $ownerTotal > 0
                ? (
                $item['total']
                /
                $ownerTotal
                )
                *
                100
                : 0;
                @endphp


                <div class="md-progress-row">

                    <div class="md-progress-top">

                        <span>
                            {{ $ownerLabel($item['owner_type']) }}
                        </span>

                        <b>
                            {{ number_format(
                                    $percentage,
                                    1,
                                    ',',
                                    '.'
                                ) }}%
                        </b>

                    </div>


                    <div class="md-progress-track">

                        <i
                            style="
                                    width:
                                    {{ min(100, max(0, $percentage)) }}%
                                "></i>

                    </div>

                </div>

                @empty

                <div class="md-empty">
                    Belum ada klasifikasi pemilik perangkat.
                </div>

                @endforelse

            </div>

        </div>


        {{-- CURRENT STATUS --}}
        <div class="panel">

            <div class="panel-head">

                <div class="panel-title">
                    ▥ Status Perangkat Saat Ini
                </div>

            </div>


            <div class="md-status-box">

                <div class="md-status-mini">

                    <span>
                        Online
                    </span>

                    <strong>
                        {{ number_format($online, 0, ',', '.') }}
                    </strong>

                    <small>
                        {{ number_format(
                            $statusOnlinePercentage,
                            1,
                            ',',
                            '.'
                        ) }}%
                        dari total
                    </small>

                </div>


                <div class="md-status-mini">

                    <span>
                        Offline
                    </span>

                    <strong>
                        {{ number_format($offline, 0, ',', '.') }}
                    </strong>

                    <small>
                        {{ number_format(
                            $statusOfflinePercentage,
                            1,
                            ',',
                            '.'
                        ) }}%
                        dari total
                    </small>

                </div>

            </div>


            <div class="md-progress-list">

                @foreach($segmentDistribution as $segment)

                @php
                $percentage =
                $total > 0
                ? (
                $segment['total']
                /
                $total
                )
                *
                100
                : 0;
                @endphp


                <div class="md-progress-row">

                    <div class="md-progress-top">

                        <span>
                            {{ $segment['segment'] }}
                        </span>

                        <b>
                            {{ number_format(
                                    $segment['total'],
                                    0,
                                    ',',
                                    '.'
                                ) }}
                        </b>

                    </div>


                    <div class="md-progress-track">

                        <i
                            style="
                                    width:
                                    {{ min(100, max(0, $percentage)) }}%
                                "></i>

                    </div>

                </div>

                @endforeach

            </div>

        </div>


        {{-- LATEST DEVICES --}}
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
            $device->{$onlineColumn}
            ?? false
            );

            $displayName =
            $device->device_name
            ?: $device->hostname
            ?: 'Perangkat Tanpa Nama';
            @endphp


            <div class="md-latest-row">

                <div class="md-latest-icon">
                    ▣
                </div>


                <div class="md-latest-copy">

                    <b>
                        {{ $displayName }}
                    </b>

                    <small>
                        {{ $device->mac_address ?: '-' }}
                        ·
                        {{ $device->ip_address ?: '-' }}
                    </small>

                </div>


                @if($isOnline)

                <span class="badge ok">
                    ●
                </span>

                @else

                <span class="badge bad">
                    ●
                </span>

                @endif

            </div>

            @empty

            <div class="md-empty">
                Belum ada perangkat terbaru.
            </div>

            @endforelse

        </div>

    </div>

</div>

@endsection