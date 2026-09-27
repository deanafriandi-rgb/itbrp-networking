@extends('layouts.admin')

@section('title', 'Statistik')

@section('content')

@php
$totalRequest = (int) ($summary['total_request'] ?? 0);
$allowed = (int) ($summary['allowed'] ?? 0);
$blocked = (int) ($summary['blocked'] ?? 0);
$failed = (int) ($summary['failed'] ?? 0);
$uniqueClients = (int) ($summary['unique_clients'] ?? 0);
$cacheHit = (int) ($summary['cache_hit'] ?? 0);
$cacheMiss = (int) ($summary['cache_miss'] ?? 0);
$hitRatio = (float) ($summary['hit_ratio'] ?? 0);
$totalBytes = (int) ($summary['total_bytes'] ?? 0);
$now = now('Asia/Jakarta');

$formatBytes = function ($bytes) {
$bytes = (int) $bytes;
if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2, ',', '.') . ' GB';
if ($bytes >= 1048576) return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
if ($bytes >= 1024) return number_format($bytes / 1024, 2, ',', '.') . ' KB';
return number_format($bytes, 0, ',', '.') . ' B';
};

$trafficCollection = collect($traffic ?? [])->values();
$maxTrafficValue = max(1, (int) ($trafficCollection->max(function ($item) {
return max((int) ($item['total_request'] ?? 0), (int) ($item['allowed'] ?? 0));
}) ?? 0));

$trafficCount = $trafficCollection->count();
$trafficStep = $trafficCount > 1 ? 800 / ($trafficCount - 1) : 800;
$totalPointList = [];
$allowedPointList = [];

foreach ($trafficCollection as $index => $item) {
$x = $index * $trafficStep;
$totalValue = (int) ($item['total_request'] ?? 0);
$allowedValue = (int) ($item['allowed'] ?? 0);
$totalY = 190 - (($totalValue / $maxTrafficValue) * 170);
$allowedY = 190 - (($allowedValue / $maxTrafficValue) * 170);
$totalPointList[] = number_format($x, 1, '.', '') . ',' . number_format($totalY, 1, '.', '');
$allowedPointList[] = number_format($x, 1, '.', '') . ',' . number_format($allowedY, 1, '.', '');
}

$totalPoints = implode(' ', $totalPointList);
$allowedPoints = implode(' ', $allowedPointList);
$peakMax = max(1, (int) ($trafficCollection->max('total_request') ?? 0));

$topDomainsCollection = collect($topDomains ?? [])->values();
$topClientsCollection = collect($topClients ?? [])->values();
$maxDomainRequest = max(1, (int) ($topDomainsCollection->max('total_request') ?? 0));
$maxClientRequest = max(1, (int) ($topClientsCollection->max('total_request') ?? 0));

$other = max(0, $totalRequest - $allowed - $blocked - $failed);
$allowedPercent = $totalRequest > 0 ? ($allowed / $totalRequest) * 100 : 0;
$blockedPercent = $totalRequest > 0 ? ($blocked / $totalRequest) * 100 : 0;
$failedPercent = $totalRequest > 0 ? ($failed / $totalRequest) * 100 : 0;
$otherPercent = max(0, 100 - $allowedPercent - $blockedPercent - $failedPercent);
$allowedEnd = $allowedPercent;
$blockedEnd = $allowedEnd + $blockedPercent;
$failedEnd = $blockedEnd + $failedPercent;

$topDomain = $topDomainsCollection->first();
$topClient = $topClientsCollection->first();
@endphp

<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-copy">
        <div class="eyebrow">ITBRP NETWORK MONITORING</div>
        <h1>Statistik Penggunaan Jaringan</h1>
        <p>Analisis aktivitas jaringan, top domain, top client, pola penggunaan, cache, dan hasil akses selama 24 jam terakhir.</p>
        <div class="hero-chips">
            <span class="hero-chip"><span class="chip-icon green">●</span>Data Access Log</span>
            <span class="hero-chip"><span class="chip-icon">◆</span>Analisis Terpusat</span>
            <span class="hero-chip"><span class="chip-icon purple">▥</span>Statistik 24 Jam</span>
        </div>
    </div>
    <div class="hero-status"><span class="status-dot"></span>Statistik Monitoring</div>
    <div class="hero-clock">
        <small>{{ $now->translatedFormat('l, d F Y') }}</small>
        <b>{{ $now->format('H:i') }}</b>
        <span>WIB</span>
    </div>
</section>

<div class="stats">
    <div class="stat-card blue">
        <div class="stat-top">
            <div class="stat-icon">▧</div>
            <div>
                <div class="stat-label">Total Request</div>
                <div class="stat-value">{{ number_format($totalRequest, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="stat-foot">24 jam terakhir</div>
    </div>
    <div class="stat-card red">
        <div class="stat-top">
            <div class="stat-icon">⊘</div>
            <div>
                <div class="stat-label">Request Diblokir</div>
                <div class="stat-value">{{ number_format($blocked, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="stat-foot">Berdasarkan log Squid</div>
    </div>
    <div class="stat-card green">
        <div class="stat-top">
            <div class="stat-icon">◉</div>
            <div>
                <div class="stat-label">Client Unik</div>
                <div class="stat-value">{{ number_format($uniqueClients, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="stat-foot">Berdasarkan IP client</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-top">
            <div class="stat-icon">%</div>
            <div>
                <div class="stat-label">Cache Hit Ratio</div>
                <div class="stat-value">{{ number_format($hitRatio, 1, ',', '.') }}%</div>
            </div>
        </div>
        <div class="stat-foot">HIT {{ number_format($cacheHit, 0, ',', '.') }} · MISS {{ number_format($cacheMiss, 0, ',', '.') }}</div>
    </div>
    <div class="stat-card orange">
        <div class="stat-top">
            <div class="stat-icon">▤</div>
            <div>
                <div class="stat-label">Data Tercatat</div>
                <div class="stat-value">{{ $formatBytes($totalBytes) }}</div>
            </div>
        </div>
        <div class="stat-foot">Berdasarkan access.log</div>
    </div>
</div>

<div class="grid three">
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title">◎ Top Domain</div><select class="tiny-select">
                <option>24 Jam Terakhir</option>
            </select>
        </div>
        <div class="ranking">
            @forelse($topDomainsCollection as $domain)
            @php
            $requestCount = (int) ($domain['total_request'] ?? 0);
            $percentage = $totalRequest > 0 ? ($requestCount / $totalRequest) * 100 : 0;
            $width = ($requestCount / $maxDomainRequest) * 100;
            $domainName = $domain['domain'] ?? '-';
            $initial = strtoupper(substr($domainName, 0, 1));
            @endphp
            <div class="rank-row">
                <div class="rank-name"><span class="brand-dot">{{ $initial }}</span><span title="{{ $domainName }}">{{ \Illuminate\Support\Str::limit($domainName, 28) }}</span></div>
                <div class="progress"><i style="width:{{ min($width, 100) }}%"></i></div>
                <b>{{ number_format($percentage, 1, ',', '.') }}%</b>
            </div>
            @empty
            <div style="padding:30px;text-align:center;color:#7b8794;">Belum ada data domain.</div>
            @endforelse
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <div class="panel-title">◎ Top Client IP</div><select class="tiny-select">
                <option>24 Jam Terakhir</option>
            </select>
        </div>
        <div class="ranking">
            @forelse($topClientsCollection as $client)
            @php
            $requestCount = (int) ($client['total_request'] ?? 0);
            $percentage = $totalRequest > 0 ? ($requestCount / $totalRequest) * 100 : 0;
            $width = ($requestCount / $maxClientRequest) * 100;
            $clientIp = $client['client_ip'] ?? '-';
            @endphp
            <div class="rank-row">
                <div class="rank-name"><span class="brand-dot">▣</span><span>{{ $clientIp }}</span></div>
                <div class="progress"><i style="width:{{ min($width, 100) }}%"></i></div>
                <b>{{ number_format($percentage, 1, ',', '.') }}%</b>
            </div>
            @empty
            <div style="padding:30px;text-align:center;color:#7b8794;">Belum ada data client.</div>
            @endforelse
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <div class="panel-title">◕ Distribusi Hasil Akses</div><select class="tiny-select">
                <option>24 Jam Terakhir</option>
            </select>
        </div>
        <div style="display:grid;grid-template-columns:170px 1fr;align-items:center;gap:12px">
            <div style="position:relative">
                <div class="donut" style="background:conic-gradient(#12b76a 0 {{ $allowedEnd }}%,#f04455 {{ $allowedEnd }}% {{ $blockedEnd }}%,#ffc248 {{ $blockedEnd }}% {{ $failedEnd }}%,#eef3f7 {{ $failedEnd }}% 100%);"></div>
                <div class="donut-center"><b>{{ number_format($totalRequest, 0, ',', '.') }}</b><small>Total Request</small></div>
            </div>
            <div class="kv-list">
                <div class="kv-row"><span><span class="status-dot" style="background:#12b76a;box-shadow:none;margin-right:6px"></span>Diizinkan</span><b>{{ number_format($allowedPercent, 1, ',', '.') }}%</b></div>
                <div class="kv-row"><span><span class="status-dot" style="background:#f04455;box-shadow:none;margin-right:6px"></span>Diblokir</span><b>{{ number_format($blockedPercent, 1, ',', '.') }}%</b></div>
                <div class="kv-row"><span><span class="status-dot" style="background:#ffc248;box-shadow:none;margin-right:6px"></span>Gagal</span><b>{{ number_format($failedPercent, 1, ',', '.') }}%</b></div>
                <div class="kv-row"><span><span class="status-dot" style="background:#d7dee7;box-shadow:none;margin-right:6px"></span>Lainnya</span><b>{{ number_format($otherPercent, 1, ',', '.') }}%</b></div>
            </div>
        </div>
    </div>
</div>

<div class="grid three" style="margin-top:12px">
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title">▥ Aktivitas Jaringan per Jam</div><select class="tiny-select">
                <option>24 Jam Terakhir</option>
            </select>
        </div>
        <div class="chart">
            <div class="chart-grid"></div>
            <svg viewBox="0 0 800 210" preserveAspectRatio="none">
                @if($totalPoints !== '')
                <polygon points="0,210 {{ $totalPoints }} 800,210" fill="#1e86fa" opacity=".07"></polygon>
                <polyline points="{{ $totalPoints }}" fill="none" stroke="#1e86fa" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"></polyline>
                @endif
                @if($allowedPoints !== '')
                <polygon points="0,210 {{ $allowedPoints }} 800,210" fill="#12b76a" opacity=".07"></polygon>
                <polyline points="{{ $allowedPoints }}" fill="none" stroke="#12b76a" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"></polyline>
                @endif
            </svg>
            <div class="axis">
                @forelse($trafficCollection as $index => $item)
                @if($index % 4 === 0)<span>{{ $item['hour'] ?? '--:--' }}</span>@endif
                @empty
                <span>00:00</span>
                @endforelse
            </div>
        </div>
        <div class="legend"><span><i style="background:#1e86fa"></i>Total</span><span><i style="background:#12b76a"></i>Diizinkan</span></div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <div class="panel-title">◴ Analisis Jam Puncak Akses</div><select class="tiny-select">
                <option>24 Jam Terakhir</option>
            </select>
        </div>
        <div class="bar-chart">
            @forelse($trafficCollection as $item)
            @php
            $requestCount = (int) ($item['total_request'] ?? 0);
            $height = ($requestCount / $peakMax) * 100;
            @endphp
            <span title="{{ $item['hour'] ?? '-' }} • {{ $requestCount }} request" style="height:{{ max(4, min($height, 100)) }}%"></span>
            @empty
            @for($i = 0; $i < 24; $i++)<span style="height:4%" title="Belum ada data"></span>@endfor
                @endforelse
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <div class="panel-title">☀ Ringkasan Statistik</div><select class="tiny-select">
                <option>24 Jam Terakhir</option>
            </select>
        </div>
        <div class="insights">
            <div class="insight">
                <div class="iicon">↗</div>
                <div><b>Total Aktivitas Jaringan</b><small>Tercatat {{ number_format($totalRequest, 0, ',', '.') }} request dalam 24 jam terakhir.</small></div><span>›</span>
            </div>
            <div class="insight red">
                <div class="iicon">!</div>
                <div><b>Request Diblokir</b><small>{{ number_format($blocked, 0, ',', '.') }} request tercatat sebagai BLOCKED.</small></div><span>›</span>
            </div>
            <div class="insight blue">
                <div class="iicon">◉</div>
                <div><b>Client Paling Aktif</b><small>@if($topClient) IP {{ $topClient['client_ip'] ?? '-' }} mencatat {{ number_format((int) ($topClient['total_request'] ?? 0), 0, ',', '.') }} request. @else Belum ada data client. @endif</small></div><span>›</span>
            </div>
            <div class="insight purple">
                <div class="iicon">◆</div>
                <div><b>Domain Teratas</b><small>@if($topDomain) {{ $topDomain['domain'] ?? '-' }} mencatat {{ number_format((int) ($topDomain['total_request'] ?? 0), 0, ',', '.') }} request. @else Belum ada data domain. @endif</small></div><span>›</span>
            </div>
            <div class="insight">
                <div class="iicon">%</div>
                <div><b>Efisiensi Cache</b><small>Hit ratio tercatat {{ number_format($hitRatio, 1, ',', '.') }}% dari transaksi cache HIT dan MISS.</small></div><span>›</span>
            </div>
        </div>
    </div>
</div>

@endsection