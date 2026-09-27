@extends('layouts.admin')

@section('title', 'Perangkat')

@section('content')

@php
$now = now('Asia/Jakarta');
$total = (int) ($summary['total'] ?? 0);
$online = (int) ($summary['online'] ?? 0);
$offline = (int) ($summary['offline'] ?? 0);
$segmentCount = (int) ($summary['segments'] ?? 0);
$interfaceCount = (int) ($summary['interfaces'] ?? 0);

$mode = strtolower((string) ($mikrotikStatus['mode'] ?? 'local'));
$modeLabel = strtoupper($mode);
$canConnect = (bool) ($mikrotikStatus['can_connect'] ?? false);
$syncEnabled = (bool) ($mikrotikStatus['device_sync_enabled'] ?? false);
$host = $mikrotikStatus['host'] ?? '-';
$port = $mikrotikStatus['port'] ?? '-';
$useSsl = (bool) ($mikrotikStatus['use_ssl'] ?? false);

$formatDateTime = function ($value) {
if (!$value) return '-';

try {
return \Illuminate\Support\Carbon::parse($value)
->timezone('Asia/Jakarta')
->format('d/m/Y H:i:s');
} catch (\Throwable $e) {
return (string) $value;
}
};
@endphp

<style>
    .device-page {
        --bd: #dbe6f2;
        --muted: #74839a;
        --text: #18345e;
        --title: #0d2b5f;
        --primary: #1785f8;
        --soft: #eaf4ff;
        --ok: #12b76a;
        --warn: #f59e0b;
        --bad: #ef476f
    }

    .device-page * {
        box-sizing: border-box
    }

    .dv-alert {
        margin-bottom: 12px;
        padding: 13px 15px;
        border: 1px solid #e1eaf4;
        border-radius: 13px;
        background: #fff;
        color: #38506f;
        font-size: 12px;
        line-height: 1.6
    }

    .dv-alert.success {
        border-left: 4px solid var(--ok)
    }

    .dv-alert.warning {
        border-left: 4px solid var(--warn)
    }

    .dv-alert.error {
        border-left: 4px solid var(--bad)
    }

    .dv-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(320px, .55fr);
        gap: 14px;
        margin-top: 14px
    }

    .dv-card {
        background: #fff;
        border: 1px solid var(--bd);
        border-radius: 18px;
        box-shadow: 0 8px 26px rgba(18, 42, 76, .045);
        padding: 18px
    }

    .dv-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px
    }

    .dv-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--title);
        font-size: 17px;
        font-weight: 800
    }

    .dv-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        background: var(--soft);
        color: var(--primary);
        font-weight: 900;
        flex: 0 0 auto
    }

    .dv-btn {
        min-height: 40px;
        border: 0;
        border-radius: 11px;
        padding: 0 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        font: inherit;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        text-decoration: none;
        white-space: nowrap;
        transition: .18s
    }

    .dv-btn:hover {
        transform: translateY(-1px)
    }

    .dv-btn.primary {
        color: #fff;
        background: linear-gradient(135deg, #1e86fa, #4da5ff);
        box-shadow: 0 8px 18px rgba(30, 134, 250, .18)
    }

    .dv-btn.soft {
        background: #eaf4ff;
        color: #0c76de
    }

    .dv-btn.neutral {
        background: #f1f5f9;
        color: #52627a
    }

    .dv-mini {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px
    }

    .dv-mini-card {
        min-height: 100px;
        padding: 14px;
        border: 1px solid #e3ebf4;
        border-radius: 14px;
        background: #f9fbfe;
        display: flex;
        flex-direction: column;
        justify-content: space-between
    }

    .dv-mini-card span {
        color: #7a899f;
        font-size: 11px;
        font-weight: 700
    }

    .dv-mini-card strong {
        color: #143563;
        font-size: 20px
    }

    .dv-mini-card small {
        color: #8a97aa;
        font-size: 10px;
        line-height: 1.4
    }

    .dv-status {
        display: grid
    }

    .dv-status-row {
        display: grid;
        grid-template-columns: 145px minmax(0, 1fr);
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #edf2f7;
        font-size: 12px
    }

    .dv-status-row:last-child {
        border-bottom: 0
    }

    .dv-status-row span {
        color: var(--muted)
    }

    .dv-status-row strong {
        color: var(--text);
        text-align: right;
        overflow-wrap: anywhere
    }

    .dv-note {
        margin-top: 14px;
        padding: 13px 14px;
        border: 1px solid #e4edf7;
        border-radius: 13px;
        background: #f3f8fe;
        color: #3d5677;
        font-size: 12px;
        line-height: 1.7
    }

    .dv-table-card {
        margin-top: 14px
    }

    .dv-filter {
        display: grid;
        grid-template-columns: minmax(260px, 1fr) 140px 155px 155px 140px auto auto;
        gap: 9px;
        align-items: center;
        margin-bottom: 14px;
        padding: 11px;
        border: 1px solid #e1eaf4;
        border-radius: 14px;
        background: #f8fbff
    }

    .dv-search {
        position: relative
    }

    .dv-search span {
        position: absolute;
        left: 13px;
        top: 50%;
        transform: translateY(-50%);
        color: #8090a7;
        pointer-events: none
    }

    .dv-input,
    .dv-select {
        width: 100%;
        height: 44px;
        border: 1px solid #d7e2ee;
        border-radius: 12px;
        outline: none;
        background: #fff;
        color: #18345f;
        font: inherit;
        font-size: 12px
    }

    .dv-input {
        padding: 0 13px
    }

    .dv-search .dv-input {
        padding-left: 39px
    }

    .dv-select {
        padding: 0 10px
    }

    .dv-input:focus,
    .dv-select:focus {
        border-color: #78b9ff;
        box-shadow: 0 0 0 4px rgba(30, 134, 250, .10)
    }

    .dv-table-wrap {
        overflow-x: auto;
        border: 1px solid #e4ecf5;
        border-radius: 14px
    }

    .dv-table {
        width: 100%;
        min-width: 1180px;
        border-collapse: separate;
        border-spacing: 0
    }

    .dv-table th {
        padding: 11px 12px;
        border-bottom: 1px solid #dde8f3;
        background: #f2f7fd;
        color: #49617f;
        font-size: 11px;
        font-weight: 800;
        text-align: left;
        white-space: nowrap
    }

    .dv-table td {
        padding: 12px;
        border-bottom: 1px solid #edf2f7;
        color: #26415f;
        font-size: 11px;
        vertical-align: middle
    }

    .dv-table tbody tr:last-child td {
        border-bottom: 0
    }

    .dv-table tbody tr:hover td {
        background: #fbfdff
    }

    .dv-name {
        display: grid;
        gap: 3px
    }

    .dv-name strong {
        color: #153765
    }

    .dv-name small {
        color: #8a98ab;
        font-size: 9px
    }

    .dv-code {
        font-family: Consolas, Monaco, monospace;
        font-size: 10px;
        color: #2b4d72
    }

    .dv-empty {
        padding: 48px 20px;
        text-align: center;
        color: #7d8ba0
    }

    .dv-empty-icon {
        width: 54px;
        height: 54px;
        margin: 0 auto 12px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        background: #edf5ff;
        color: #1785f8;
        font-size: 22px
    }

    .dv-empty strong {
        display: block;
        margin-bottom: 6px;
        color: #27476c;
        font-size: 14px
    }

    .dv-empty p {
        max-width: 480px;
        margin: 0 auto;
        font-size: 11px;
        line-height: 1.7
    }

    .dv-actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap
    }

    .dv-actions .dv-btn {
        min-height: 34px;
        padding: 0 10px;
        border-radius: 9px;
        font-size: 10px
    }

    .dv-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 22px
    }

    .dv-modal.is-open {
        display: flex
    }

    .dv-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(10, 25, 50, .48);
        backdrop-filter: blur(4px)
    }

    .dv-dialog {
        position: relative;
        z-index: 1;
        width: min(680px, 100%);
        max-height: calc(100vh - 44px);
        overflow: auto;
        background: #fff;
        border: 1px solid #dce6f1;
        border-radius: 20px;
        box-shadow: 0 28px 80px rgba(10, 31, 66, .24)
    }

    .dv-modal-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 20px;
        border-bottom: 1px solid #e8eef5
    }

    .dv-modal-title {
        color: #0d2b5f;
        font-size: 17px;
        font-weight: 800
    }

    .dv-modal-sub {
        margin-top: 4px;
        color: #7b899d;
        font-size: 11px;
        line-height: 1.5
    }

    .dv-close {
        width: 34px;
        height: 34px;
        border: 0;
        border-radius: 10px;
        display: grid;
        place-items: center;
        cursor: pointer;
        background: #f3f6fa;
        color: #5f7088;
        font-size: 18px
    }

    .dv-modal-body {
        padding: 20px
    }

    .dv-detail {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px
    }

    .dv-detail-item {
        min-height: 76px;
        padding: 12px;
        border: 1px solid #e5edf6;
        border-radius: 13px;
        background: #f9fbfe
    }

    .dv-detail-item.full {
        grid-column: 1/-1
    }

    .dv-detail-item span {
        display: block;
        margin-bottom: 6px;
        color: #8491a4;
        font-size: 10px;
        font-weight: 700
    }

    .dv-detail-item strong {
        display: block;
        color: #193961;
        font-size: 12px;
        line-height: 1.5;
        overflow-wrap: anywhere
    }

    body.dv-modal-open {
        overflow: hidden
    }

    @media(max-width:1250px) {
        .dv-filter {
            grid-template-columns: 1fr 1fr 1fr 1fr
        }

        .dv-search {
            grid-column: 1/-1
        }
    }

    @media(max-width:1050px) {
        .dv-grid {
            grid-template-columns: 1fr
        }
    }

    @media(max-width:700px) {

        .dv-filter,
        .dv-mini,
        .dv-detail {
            grid-template-columns: 1fr
        }

        .dv-search,
        .dv-detail-item.full {
            grid-column: auto
        }

        .dv-btn {
            width: 100%
        }

        .dv-card {
            padding: 14px
        }

        .dv-modal {
            padding: 12px
        }
    }
</style>

<div class="device-page">

    <section class="hero">
        <div class="hero-bg"></div>

        <div class="hero-copy">
            <div class="eyebrow">ITBRP NETWORK MONITORING</div>
            <h1>Perangkat Jaringan</h1>
            <p>
                Pantau perangkat dari data MikroTik ARP dan DHCP,
                status online/offline, interface, serta segmen jaringan kampus.
            </p>

            <div class="hero-chips">
                <span class="hero-chip"><span class="chip-icon green">●</span>ARP + DHCP</span>
                <span class="hero-chip"><span class="chip-icon">◆</span>MAC sebagai Identitas</span>
                <span class="hero-chip"><span class="chip-icon purple">▥</span>Mode {{ $modeLabel }}</span>
            </div>
        </div>

        <div class="hero-status">
            <span class="status-dot"></span>
            @if($mode === 'production' && $canConnect)
            Siap Terhubung MikroTik
            @elseif($mode === 'production')
            Production Belum Siap
            @else
            Simulasi Lokal
            @endif
        </div>

        <div class="hero-clock">
            <small>{{ $now->translatedFormat('l, d F Y') }}</small>
            <b>{{ $now->format('H:i') }}</b>
            <span>WIB</span>
        </div>
    </section>

    @if(session('success'))
    <div class="dv-alert success">{{ session('success') }}</div>
    @endif

    @if(session('warning'))
    <div class="dv-alert warning">{{ session('warning') }}</div>
    @endif

    @if(session('error'))
    <div class="dv-alert error">{{ session('error') }}</div>
    @endif

    <div class="stats">
        <div class="stat-card blue">
            <div class="stat-top">
                <div class="stat-icon">▣</div>
                <div>
                    <div class="stat-label">Total Perangkat</div>
                    <div class="stat-value">{{ number_format($total) }}</div>
                </div>
            </div>
            <div class="stat-foot">Tersimpan di database</div>
        </div>

        <div class="stat-card green">
            <div class="stat-top">
                <div class="stat-icon">●</div>
                <div>
                    <div class="stat-label">Online</div>
                    <div class="stat-value">{{ number_format($online) }}</div>
                </div>
            </div>
            <div class="stat-foot">Terlihat aktif</div>
        </div>

        <div class="stat-card orange">
            <div class="stat-top">
                <div class="stat-icon">○</div>
                <div>
                    <div class="stat-label">Offline</div>
                    <div class="stat-value">{{ number_format($offline) }}</div>
                </div>
            </div>
            <div class="stat-foot">Tidak terlihat melewati batas waktu</div>
        </div>

        <div class="stat-card purple">
            <div class="stat-top">
                <div class="stat-icon">◆</div>
                <div>
                    <div class="stat-label">Segmen Terdeteksi</div>
                    <div class="stat-value">{{ number_format($segmentCount) }}</div>
                </div>
            </div>
            <div class="stat-foot">Berdasarkan subnet perangkat</div>
        </div>

        <div class="stat-card blue">
            <div class="stat-top">
                <div class="stat-icon">⌁</div>
                <div>
                    <div class="stat-label">Interface Terdeteksi</div>
                    <div class="stat-value">{{ number_format($interfaceCount) }}</div>
                </div>
            </div>
            <div class="stat-foot">Dari data MikroTik</div>
        </div>
    </div>

    <div class="dv-grid">
        <div class="dv-card">
            <div class="dv-head">
                <div class="dv-title"><span class="dv-icon">▥</span><span>Ringkasan Perangkat</span></div>
            </div>

            <div class="dv-mini">
                <div class="dv-mini-card">
                    <span>Terakhir Terlihat</span>
                    <strong style="font-size:14px">{{ $formatDateTime($summary['latest_seen'] ?? null) }}</strong>
                    <small>Waktu terakhir perangkat tercatat aktif.</small>
                </div>

                <div class="dv-mini-card">
                    <span>Segmen Tersedia</span>
                    <strong>{{ $segments->count() }}</strong>
                    <small>Termasuk mapping segmen dari konfigurasi kampus.</small>
                </div>

                <div class="dv-mini-card">
                    <span>Interface Terdeteksi</span>
                    <strong>{{ $interfaces->count() }}</strong>
                    <small>Muncul setelah data MikroTik mulai masuk.</small>
                </div>

                <div class="dv-mini-card">
                    <span>Status Sinkronisasi</span>
                    <strong style="font-size:14px">
                        @if($mode === 'production' && $syncEnabled)
                        AKTIF
                        @elseif($mode === 'production')
                        NONAKTIF
                        @else
                        LOCAL
                        @endif
                    </strong>
                    <small>Scheduler otomatis akan diaktifkan pada STEP 8F.</small>
                </div>
            </div>
        </div>

        <div class="dv-card">
            <div class="dv-head">
                <div class="dv-title"><span class="dv-icon">⌁</span><span>Integrasi MikroTik</span></div>

                <form method="POST" action="{{ route('admin.devices.sync') }}">
                    @csrf
                    <button type="submit" class="dv-btn soft">↻ Sinkronkan Sekarang</button>
                </form>
            </div>

            <div class="dv-status">
                <div class="dv-status-row"><span>Mode</span><strong>{{ $modeLabel }}</strong></div>
                <div class="dv-status-row"><span>Router</span><strong>{{ $host }}</strong></div>
                <div class="dv-status-row"><span>Port API</span><strong>{{ $port }}</strong></div>
                <div class="dv-status-row"><span>SSL</span><strong>{{ $useSsl ? 'Aktif' : 'Nonaktif' }}</strong></div>
                <div class="dv-status-row"><span>Remote Connection</span><strong>{{ $canConnect ? 'Diizinkan' : 'Tidak Diizinkan' }}</strong></div>
                <div class="dv-status-row"><span>Device Sync</span><strong>{{ $syncEnabled ? 'Enabled' : 'Disabled' }}</strong></div>
            </div>

            <div class="dv-note">
                @if($mode === 'local')
                <strong>Mode LOCAL.</strong>
                Laravel tidak menghubungi MikroTik. Tombol sinkronisasi aman dan menghasilkan status <strong>SKIPPED</strong>.
                @elseif($canConnect)
                <strong>Production siap.</strong>
                Laravel diizinkan mengambil data ARP dan DHCP dari RouterOS.
                @else
                <strong>Production belum siap.</strong>
                Periksa safety guard dan kredensial MikroTik.
                @endif
            </div>
        </div>
    </div>

    <div class="dv-card dv-table-card">
        <div class="dv-head">
            <div class="dv-title"><span class="dv-icon">▣</span><span>Daftar Perangkat Jaringan</span></div>
            <span class="badge blue">{{ number_format($total) }} perangkat</span>
        </div>

        <form method="GET" action="{{ route('admin.devices.index') }}" class="dv-filter">
            <div class="dv-search">
                <span>⌕</span>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    class="dv-input"
                    placeholder="Cari IP, MAC, hostname, nama perangkat, atau pemilik...">
            </div>

            <select name="status" class="dv-select">
                <option value="">Semua Status</option>
                <option value="online" @selected(request('status')==='online' )>Online</option>
                <option value="offline" @selected(request('status')==='offline' )>Offline</option>
            </select>

            <select name="segment" class="dv-select">
                <option value="">Semua Segmen</option>
                @foreach($segments as $segment)
                <option value="{{ $segment }}" @selected(request('segment')===$segment)>{{ $segment }}</option>
                @endforeach
            </select>

            <select name="interface" class="dv-select">
                <option value="">Semua Interface</option>
                @foreach($interfaces as $interface)
                <option value="{{ $interface }}" @selected(request('interface')===$interface)>{{ $interface }}</option>
                @endforeach
            </select>

            <select name="source" class="dv-select">
                <option value="">Semua Sumber</option>
                <option value="mikrotik" @selected(request('source')==='mikrotik' )>MikroTik</option>
                <option value="manual" @selected(request('source')==='manual' )>Manual</option>
            </select>

            <button type="submit" class="dv-btn primary">⌕ Filter</button>
            <a href="{{ route('admin.devices.index') }}" class="dv-btn neutral">Reset</a>
        </form>

        <div class="dv-table-wrap">
            <table class="dv-table">
                <thead>
                    <tr>
                        <th>Perangkat</th>
                        <th>IP Address</th>
                        <th>MAC Address</th>
                        <th>Segmen</th>
                        <th>Interface</th>
                        <th>Pemilik</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                        <th>Last Seen</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($devices as $device)
                    @php
                    $isOnline = (bool) ($device->{$onlineColumn} ?? false);
                    $lastSeen = $device->{$lastSeenColumn} ?? null;
                    $displayName = $device->device_name ?: $device->hostname ?: 'Perangkat Tanpa Nama';
                    @endphp

                    <tr>
                        <td>
                            <div class="dv-name">
                                <strong>{{ $displayName }}</strong>
                                <small>{{ $device->hostname ?: 'Hostname belum tersedia' }}</small>
                            </div>
                        </td>
                        <td><span class="dv-code">{{ $device->ip_address ?: '-' }}</span></td>
                        <td><span class="dv-code">{{ $device->mac_address ?: '-' }}</span></td>
                        <td>{{ $device->segment ?: '-' }}</td>
                        <td>{{ $device->interface ?: '-' }}</td>
                        <td>{{ $device->owner_name ?: '-' }}</td>
                        <td>{{ $device->location ?: '-' }}</td>
                        <td>
                            @if($isOnline)
                            <span class="badge ok">● Online</span>
                            @else
                            <span class="badge bad">● Offline</span>
                            @endif
                        </td>
                        <td>{{ $formatDateTime($lastSeen) }}</td>
                        <td>
                            <div class="dv-actions">
                                <button
                                    type="button"
                                    class="dv-btn soft"
                                    data-dv-open="deviceModal{{ $device->id }}">
                                    Detail
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10">
                            <div class="dv-empty">
                                <div class="dv-empty-icon">▣</div>
                                <strong>Belum ada data perangkat</strong>
                                <p>
                                    Data akan muncul otomatis setelah integrasi MikroTik production diaktifkan
                                    dan sinkronisasi ARP/DHCP berhasil dijalankan.
                                </p>
                            </div>
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
    </div>

    @foreach($devices as $device)
    @php
    $isOnline = (bool) ($device->{$onlineColumn} ?? false);
    $firstSeenValue = $device->first_seen_at ?? $device->first_seen ?? null;
    $lastSeenValue = $device->{$lastSeenColumn} ?? null;
    $displayName = $device->device_name ?: $device->hostname ?: 'Perangkat Tanpa Nama';
    @endphp

    <div class="dv-modal" id="deviceModal{{ $device->id }}" aria-hidden="true">
        <div class="dv-backdrop" data-dv-close></div>

        <div class="dv-dialog" role="dialog" aria-modal="true">
            <div class="dv-modal-head">
                <div style="display:flex;gap:12px;align-items:flex-start">
                    <span class="dv-icon">▣</span>
                    <div>
                        <div class="dv-modal-title">{{ $displayName }}</div>
                        <div class="dv-modal-sub">Informasi perangkat jaringan yang tersimpan pada database monitoring.</div>
                    </div>
                </div>

                <button type="button" class="dv-close" data-dv-close>×</button>
            </div>

            <div class="dv-modal-body">
                <div class="dv-detail">
                    <div class="dv-detail-item"><span>Status</span><strong>{{ $isOnline ? 'Online' : 'Offline' }}</strong></div>
                    <div class="dv-detail-item"><span>Sumber Data</span><strong>{{ strtoupper($device->source ?: '-') }}</strong></div>
                    <div class="dv-detail-item"><span>IP Address</span><strong class="dv-code">{{ $device->ip_address ?: '-' }}</strong></div>
                    <div class="dv-detail-item"><span>MAC Address</span><strong class="dv-code">{{ $device->mac_address ?: '-' }}</strong></div>
                    <div class="dv-detail-item"><span>Hostname</span><strong>{{ $device->hostname ?: '-' }}</strong></div>
                    <div class="dv-detail-item"><span>Nama Perangkat</span><strong>{{ $device->device_name ?: '-' }}</strong></div>
                    <div class="dv-detail-item"><span>Jenis Perangkat</span><strong>{{ $device->device_type ?: '-' }}</strong></div>
                    <div class="dv-detail-item"><span>Sistem Operasi</span><strong>{{ $device->operating_system ?: '-' }}</strong></div>
                    <div class="dv-detail-item"><span>Segmen</span><strong>{{ $device->segment ?: '-' }}</strong></div>
                    <div class="dv-detail-item"><span>Interface</span><strong>{{ $device->interface ?: '-' }}</strong></div>
                    <div class="dv-detail-item"><span>Lokasi</span><strong>{{ $device->location ?: '-' }}</strong></div>
                    <div class="dv-detail-item"><span>Pemilik</span><strong>{{ $device->owner_name ?: '-' }}</strong></div>
                    <div class="dv-detail-item"><span>Tipe Pemilik</span><strong>{{ $device->owner_type ?: '-' }}</strong></div>
                    <div class="dv-detail-item"><span>First Seen</span><strong>{{ $formatDateTime($firstSeenValue) }}</strong></div>
                    <div class="dv-detail-item full"><span>Last Seen</span><strong>{{ $formatDateTime($lastSeenValue) }}</strong></div>
                </div>
            </div>
        </div>
    </div>
    @endforeach

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function openModal(modal) {
            if (!modal) return;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('dv-modal-open');
        }

        function closeModal(modal) {
            if (!modal) return;
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');

            if (!document.querySelector('.dv-modal.is-open')) {
                document.body.classList.remove('dv-modal-open');
            }
        }

        document.addEventListener('click', function(event) {
            const opener = event.target.closest('[data-dv-open]');

            if (opener) {
                openModal(document.getElementById(opener.getAttribute('data-dv-open')));
                return;
            }

            const closer = event.target.closest('[data-dv-close]');

            if (closer) {
                closeModal(closer.closest('.dv-modal'));
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key !== 'Escape') return;

            document.querySelectorAll('.dv-modal.is-open').forEach(function(modal) {
                closeModal(modal);
            });
        });
    });
</script>

@endsection