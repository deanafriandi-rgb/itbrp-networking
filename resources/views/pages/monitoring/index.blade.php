@extends('layouts.monitoring')

@section('title', 'Monitoring')

@section('content')

@php
$totalRequest = (int) ($summary['total_request'] ?? 0);
$allowed = (int) ($summary['allowed'] ?? 0);
$blocked = (int) ($summary['blocked'] ?? 0);
$failed = (int) ($summary['failed'] ?? 0);
$uniqueClients = (int) ($summary['unique_clients'] ?? 0);
$totalBytes = (int) ($summary['total_bytes'] ?? 0);

$cacheSummary = $cacheData['summary'] ?? [];
$cacheHit = (int) ($cacheSummary['hit'] ?? 0);
$cacheMiss = (int) ($cacheSummary['miss'] ?? 0);
$cacheHitRatio = (float) ($cacheSummary['hit_ratio'] ?? 0);
$avgHit = $cacheSummary['avg_hit_ms'] ?? null;
$avgMiss = $cacheSummary['avg_miss_ms'] ?? null;
$responseDifference = $cacheSummary['response_difference_ms'] ?? null;
$responseImprovement = $cacheSummary['response_improvement_percent'] ?? null;
$hitBytes = (int) ($cacheSummary['hit_bytes'] ?? 0);
$missBytes = (int) ($cacheSummary['miss_bytes'] ?? 0);

$cacheTrendCollection = collect($cacheData['trend'] ?? [])->values();
$cacheDomainCollection = collect($cacheData['topDomains'] ?? [])->take(8)->values();
$cacheObjectCollection = collect($cacheData['objectComparisons'] ?? [])->take(10)->values();
$cacheRecentCollection = collect($cacheData['recentActivities'] ?? [])->take(12)->values();

$trafficCollection = collect($traffic ?? [])->values();
$topDomainsCollection = collect($topDomains ?? [])->take(8)->values();
$topClientsCollection = collect($topClients ?? [])->take(8)->values();
$latestActivitiesCollection = collect($latestActivities ?? [])->take(10)->values();
$peakHour = $trafficCollection->sortByDesc('total_request')->first();

$blacklistTotal = (int) ($blacklistSummary['total'] ?? 0);
$blacklistActive = (int) ($blacklistSummary['active'] ?? 0);
$blacklistCategoryCount = (int) ($blacklistSummary['categories'] ?? 0);
$blacklistSynced = (int) ($blacklistSummary['synced'] ?? 0);
$blacklistPending = (int) ($blacklistSummary['pending'] ?? 0);
$blacklistError = (int) ($blacklistSummary['error'] ?? 0);
$includeSubdomains = (int) ($blacklistSummary['include_subdomains'] ?? 0);
$blockedTrendCollection = collect($blockedTrend ?? [])->values();

$deviceTotal = (int) ($deviceSummary['total'] ?? 0);
$deviceOnline = (int) ($deviceSummary['online'] ?? 0);
$deviceOffline = (int) ($deviceSummary['offline'] ?? 0);
$deviceStaff = (int) ($deviceSummary['staff'] ?? 0);
$deviceStudent = (int) ($deviceSummary['student'] ?? 0);
$deviceGuest = (int) ($deviceSummary['guest'] ?? 0);

$now = $nowMonitoring ?? now('Asia/Jakarta');

$formatBytes = function ($bytes) {
    $bytes = (int) $bytes;
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2, ',', '.') . ' GB';
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2, ',', '.') . ' KB';
    return number_format($bytes, 0, ',', '.') . ' B';
};

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

$shortObjectName = function ($url, $domain = null) {
    $url = trim((string) ($url ?? ''));
    if ($url !== '') {
        $path = parse_url($url, PHP_URL_PATH);
        if (is_string($path) && trim($path, '/') !== '') {
            $name = basename($path);
            if ($name !== '') return \Illuminate\Support\Str::limit($name, 72);
        }
    }
    return $domain
        ? \Illuminate\Support\Str::limit((string) $domain, 72)
        : '-';
};

/* Traffic line chart */
$maxTrafficValue = max(1, (int) ($trafficCollection->max(function ($item) {
    return max(
        (int) ($item['total_request'] ?? 0),
        (int) ($item['allowed'] ?? 0)
    );
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

    $totalPointList[] =
        number_format($x, 1, '.', '') . ',' .
        number_format($totalY, 1, '.', '');

    $allowedPointList[] =
        number_format($x, 1, '.', '') . ',' .
        number_format($allowedY, 1, '.', '');
}

$totalPoints = implode(' ', $totalPointList);
$allowedPoints = implode(' ', $allowedPointList);
$peakMax = max(1, (int) ($trafficCollection->max('total_request') ?? 0));

/* Blocked trend */
$maxBlockedValue = max(1, (int) ($blockedTrendCollection->max('blocked') ?? 0));
$blockedCount = $blockedTrendCollection->count();
$blockedStep = $blockedCount > 1 ? 800 / ($blockedCount - 1) : 800;
$blockedPointList = [];

foreach ($blockedTrendCollection as $index => $item) {
    $x = $index * $blockedStep;
    $value = (int) ($item['blocked'] ?? 0);
    $y = 190 - (($value / $maxBlockedValue) * 170);

    $blockedPointList[] =
        number_format($x, 1, '.', '') . ',' .
        number_format($y, 1, '.', '');
}

$blockedPoints = implode(' ', $blockedPointList);

/* Cache trend */
$maxCacheTrendValue = max(1, (int) ($cacheTrendCollection->max(function ($item) {
    return max(
        (int) ($item['hit'] ?? 0),
        (int) ($item['miss'] ?? 0)
    );
}) ?? 0));

$cacheTrendCount = $cacheTrendCollection->count();
$cacheTrendStep = $cacheTrendCount > 1 ? 800 / ($cacheTrendCount - 1) : 800;
$hitPointList = [];
$missPointList = [];

foreach ($cacheTrendCollection as $index => $item) {
    $x = $index * $cacheTrendStep;
    $hitValue = (int) ($item['hit'] ?? 0);
    $missValue = (int) ($item['miss'] ?? 0);

    $hitY = 190 - (($hitValue / $maxCacheTrendValue) * 170);
    $missY = 190 - (($missValue / $maxCacheTrendValue) * 170);

    $hitPointList[] =
        number_format($x, 1, '.', '') . ',' .
        number_format($hitY, 1, '.', '');

    $missPointList[] =
        number_format($x, 1, '.', '') . ',' .
        number_format($missY, 1, '.', '');
}

$hitPoints = implode(' ', $hitPointList);
$missPoints = implode(' ', $missPointList);

$maxDomainRequest = max(1, (int) ($topDomainsCollection->max('total_request') ?? 0));
$maxClientRequest = max(1, (int) ($topClientsCollection->max('total_request') ?? 0));
$maxCacheDomainRequest = max(1, (int) ($cacheDomainCollection->max('total_request') ?? 0));

$other = max(0, $totalRequest - $allowed - $blocked - $failed);
$allowedPercent = $totalRequest > 0 ? ($allowed / $totalRequest) * 100 : 0;
$blockedPercent = $totalRequest > 0 ? ($blocked / $totalRequest) * 100 : 0;
$failedPercent = $totalRequest > 0 ? ($failed / $totalRequest) * 100 : 0;
$otherPercent = $totalRequest > 0
    ? max(0, 100 - $allowedPercent - $blockedPercent - $failedPercent)
    : 0;

$allowedEnd = $allowedPercent;
$blockedEnd = $allowedEnd + $blockedPercent;
$failedEnd = $blockedEnd + $failedPercent;

$percentOfTotal = function ($value) use ($totalRequest) {
    return $totalRequest > 0
        ? round(((int) $value / $totalRequest) * 100, 1)
        : 0;
};

$resultRows = [
    [
        'label' => 'Request Diizinkan',
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
        'percent' => $cacheHitRatio,
        'note' => 'Objek dilayani dari cache Squid.',
    ],
    [
        'label' => 'Cache MISS',
        'value' => $cacheMiss,
        'percent' => ($cacheHit + $cacheMiss) > 0
            ? round(($cacheMiss / ($cacheHit + $cacheMiss)) * 100, 1)
            : 0,
        'note' => 'Objek perlu diambil dari origin server.',
    ],
    [
        'label' => 'Request Tidak Selesai',
        'value' => $failed,
        'percent' => $percentOfTotal($failed),
        'note' => 'Request error / aborted / tidak selesai yang tercatat.',
    ],
];
@endphp

<style>
html{scroll-behavior:smooth;scroll-padding-top:82px}
.unified-monitoring{--b:#dce7f2;--t:#17385f;--m:#74849a;--blue:#1e86fa;--green:#12b76a;--red:#f04455;--orange:#ffb648}
.unified-monitoring *{box-sizing:border-box}
.um-section{width:100%;max-width:1500px;margin:14px auto 0;padding:0 24px;scroll-margin-top:82px}
.um-section-head{display:flex;align-items:flex-end;justify-content:space-between;gap:14px;margin-bottom:10px}
.um-section-head h2{margin:0;color:#0d2f62;font-size:20px;line-height:1.15}
.um-section-head p{margin:5px 0 0;color:#74849a;font-size:10px;line-height:1.55}
.um-muted-pill{flex:0 0 auto;padding:6px 9px;border:1px solid #dce8f5;border-radius:999px;background:#f8fbff;color:#61758e;font-size:8.5px;font-weight:850}
.um-grid-2{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.um-grid-3{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.um-panel{min-width:0;border:1px solid var(--b);border-radius:16px;background:#fff;overflow:hidden}
.um-panel-head{min-height:52px;padding:13px 15px 10px;display:flex;align-items:center;justify-content:space-between;gap:12px}
.um-panel-title{color:#0d3266;font-size:13px;font-weight:900}
.um-panel-body{padding:0 15px 15px}
.um-chart{position:relative;min-height:250px;border-radius:12px;overflow:hidden;background:linear-gradient(to bottom,transparent 24%,#edf2f7 25%,transparent 26%),linear-gradient(to bottom,transparent 49%,#edf2f7 50%,transparent 51%),linear-gradient(to bottom,transparent 74%,#edf2f7 75%,transparent 76%)}
.um-chart svg{position:absolute;inset:10px 8px 31px 8px;width:calc(100% - 16px);height:calc(100% - 41px)}
.um-axis{position:absolute;left:10px;right:10px;bottom:7px;display:flex;justify-content:space-between;gap:4px;color:#758ba5;font-size:8px}
.um-legend{display:flex;flex-wrap:wrap;gap:14px;margin-top:8px;color:#60758e;font-size:9px}
.um-legend span{display:inline-flex;align-items:center;gap:5px}.um-legend i{width:8px;height:8px;border-radius:50%}
.um-ranking{display:grid;gap:10px}
.um-rank{display:grid;grid-template-columns:minmax(0,1fr) minmax(90px,42%) 55px;gap:8px;align-items:center;color:#2e4d6d;font-size:9px}
.um-rank-name{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.um-progress{height:8px;border-radius:999px;background:#eaf0f6;overflow:hidden}
.um-progress i{display:block;height:100%;border-radius:inherit;background:#62adf7}.um-progress.green i{background:#43cb91}
.um-rank b{text-align:right;color:#173866;font-size:9px}
.um-kv-list{display:grid;gap:0}.um-kv{min-height:44px;display:flex;align-items:center;justify-content:space-between;gap:12px;border-bottom:1px solid #edf2f7;color:#405d7a;font-size:9.5px}.um-kv:last-child{border-bottom:0}.um-kv b{color:#173866}
.um-policy-list{display:grid;gap:9px}.um-policy{min-height:58px;padding:10px 11px;display:grid;grid-template-columns:35px minmax(0,1fr) auto;gap:10px;align-items:center;border:1px solid #e4ecf4;border-radius:12px;background:#fbfdff}
.um-policy-icon{width:34px;height:34px;display:grid;place-items:center;border-radius:10px;color:#1684f8;background:#eaf4ff;font-size:12px;font-weight:900}
.um-policy b{display:block;color:#173866;font-size:9.5px}.um-policy small{display:block;margin-top:3px;color:#8391a4;font-size:8px}.um-policy-value{color:#16375f;font-size:8.5px;font-weight:900;text-align:right}
.um-note{margin-top:10px;padding:11px 12px;border:1px solid #dfe9f4;border-radius:12px;color:#647892;background:#f8fbff;font-size:9px;line-height:1.6}
.um-donut-wrap{min-height:235px;padding:15px;display:grid;grid-template-columns:180px minmax(0,1fr);gap:20px;align-items:center}
.um-donut-box{position:relative;width:170px;height:170px;margin:auto}.um-donut{position:absolute;inset:0;border-radius:50%}.um-donut:after{content:'';position:absolute;inset:29px;border-radius:50%;background:#fff}
.um-donut-center{position:absolute;inset:0;z-index:2;display:grid;place-content:center;text-align:center}.um-donut-center b{color:#0d3266;font-size:18px}.um-donut-center small{margin-top:3px;color:#7b8ba0;font-size:8px}
.um-bar-chart{height:235px;padding:18px 12px 12px;display:flex;align-items:flex-end;gap:6px;border-bottom:1px solid #dfe8f1}.um-bar-chart span{flex:1 1 0;min-width:3px;max-width:18px;border-radius:5px 5px 2px 2px;background:linear-gradient(180deg,#74b9ff,#2789f5)}
.um-table-tools{display:grid;grid-template-columns:minmax(260px,1fr) repeat(3,minmax(130px,170px)) auto auto;gap:8px;padding:11px;margin:0 15px 12px;border:1px solid #e1eaf4;border-radius:13px;background:#f8fbff}
.um-input,.um-select{width:100%;height:40px;border:1px solid #d7e2ee;border-radius:10px;outline:none;background:#fff;color:#18345f;font:inherit;font-size:10px}.um-input{padding:0 12px}.um-select{padding:0 9px}
.um-input:focus,.um-select:focus{border-color:#78b9ff;box-shadow:0 0 0 4px rgba(30,134,250,.09)}
.um-btn{min-height:40px;padding:0 13px;display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:10px;text-decoration:none;cursor:pointer;font:inherit;font-size:10px;font-weight:850;white-space:nowrap}.um-btn.primary{color:#fff;background:linear-gradient(135deg,#1e86fa,#4da5ff)}.um-btn.neutral{color:#52627a;background:#edf2f7}
.um-table-wrap{width:100%;overflow:auto;scrollbar-width:thin}.um-table{width:100%;min-width:900px;border-collapse:collapse}.um-table.device-table{min-width:1400px}.um-table.blacklist-table{min-width:1000px}
.um-table th{padding:9px 10px;text-align:left;color:#49617e;background:#f1f6fc;border-bottom:1px solid #dce7f2;font-size:8.5px;font-weight:850;white-space:nowrap}
.um-table td{padding:9px 10px;color:#36516e;border-bottom:1px solid #eaf0f6;font-size:8.8px;vertical-align:middle}.um-table tbody tr:hover{background:#fbfdff}.um-table tbody tr:last-child td{border-bottom:0}
.um-device-name{min-width:130px}.um-device-name strong,.um-device-name small{display:block}.um-device-name strong{color:#123965;font-size:9.5px}.um-device-name small{margin-top:3px;color:#8694a6;font-size:8px}
.um-code{font-family:Consolas,Monaco,monospace;font-size:8.5px;white-space:nowrap}
.um-badge{display:inline-flex;align-items:center;gap:4px;padding:4px 7px;border-radius:999px;font-size:8px;font-weight:850;white-space:nowrap}.um-badge.ok{color:#087a49;background:#ddf8e9}.um-badge.bad{color:#c32649;background:#ffe6ec}.um-badge.warn{color:#a56506;background:#fff1d8}.um-badge.blue{color:#0876df;background:#e5f1ff}
.um-device-list{display:grid;gap:7px}.um-device-row{min-height:52px;padding:8px 10px;display:grid;grid-template-columns:35px minmax(0,1fr) auto;gap:9px;align-items:center;border:1px solid #e7eef6;border-radius:11px;background:#fbfdff}
.um-device-icon{width:34px;height:34px;display:grid;place-items:center;border-radius:10px;color:#1785f8;background:#eaf4ff;font-weight:900}.um-device-copy{min-width:0}.um-device-copy b,.um-device-copy small{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.um-device-copy b{color:#173866;font-size:9.5px}.um-device-copy small{margin-top:3px;color:#8391a5;font-size:8px}
.um-object-list{display:grid;gap:11px;padding:0 15px 15px}.um-object-row{display:grid;gap:6px}.um-object-top{display:flex;justify-content:space-between;gap:12px;color:#4d6380;font-size:8.5px}.um-object-top span{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.um-object-top b{flex:0 0 auto;color:#173866}.um-object-meta{color:#8290a3;font-size:8px;line-height:1.5}
.um-pagination{padding:12px 15px 15px;display:flex;align-items:center;justify-content:space-between;gap:10px;border-top:1px solid #edf2f7}.um-pagination-info{color:#73849b;font-size:9px}.um-pagination-actions{display:flex;align-items:center;gap:6px}
.um-page{min-width:32px;height:32px;padding:0 9px;display:inline-flex;align-items:center;justify-content:center;border:1px solid #dbe6f1;border-radius:9px;color:#36526f;background:#fff;text-decoration:none;font-size:9px;font-weight:800}.um-page.current{color:#fff;border-color:#258bf8;background:#258bf8}.um-page.disabled{opacity:.45;pointer-events:none}
.um-empty{padding:28px 15px!important;text-align:center!important;color:#8492a4!important;font-size:9.5px!important}
.um-anchor-menu{max-width:1500px;margin:0 auto;padding:0 24px 12px;display:flex;flex-wrap:wrap;gap:7px}.um-anchor-menu a{padding:7px 10px;border:1px solid #dce8f4;border-radius:999px;color:#4e6783;background:#fff;text-decoration:none;font-size:9px;font-weight:800}.um-anchor-menu a:hover{color:#0878e8;border-color:#bad9fb;background:#eef7ff}
@media(max-width:1200px){.um-grid-3{grid-template-columns:1fr}.um-table-tools{grid-template-columns:repeat(3,minmax(0,1fr))}.um-table-tools .um-input{grid-column:1/-1}}
@media(max-width:800px){.um-section,.um-anchor-menu{padding-left:12px;padding-right:12px}.um-grid-2{grid-template-columns:1fr}.um-donut-wrap{grid-template-columns:1fr}.um-table-tools{grid-template-columns:1fr;margin-left:10px;margin-right:10px}.um-table-tools .um-input{grid-column:auto}}
@media(max-width:520px){.um-section-head{align-items:flex-start;flex-direction:column}.um-pagination{align-items:flex-start;flex-direction:column}}
</style>

<div class="unified-monitoring">

{{-- ===================================================== --}}
{{-- HERO --}}
{{-- ===================================================== --}}

<section class="hero">
    <div class="hero-bg"></div>

    <div class="hero-copy">
        <div class="eyebrow">ITBRP NETWORK MONITORING</div>

        <h1>
            Monitoring Jaringan Terpadu
            <br>
            <span class="accent">Multi-WAN & Squid Proxy</span>
        </h1>

        <p>
            Dashboard read-only untuk memantau akses internet,
            perangkat MikroTik, filtering domain, cache Squid,
            statistik jaringan, dan kondisi layanan dalam satu halaman.
        </p>

        <div class="hero-chips">
            <span class="hero-chip">
                <span class="chip-icon green">●</span>
                Access Log
            </span>

            <span class="hero-chip">
                <span class="chip-icon">◆</span>
                Filtering
            </span>

            <span class="hero-chip">
                <span class="chip-icon purple">▥</span>
                Cache & Monitoring
            </span>
        </div>
    </div>

    <div class="hero-status">
        <span class="status-dot"></span>
        {{ $systemStatus['label'] ?? 'Monitoring' }}
    </div>

    <div class="hero-clock">
        <small>{{ $now->translatedFormat('l, d F Y') }}</small>
        <b>{{ $now->format('H:i') }}</b>
        <span>WIB</span>
    </div>
</section>


{{-- ===================================================== --}}
{{-- SUMMARY CARDS --}}
{{-- ===================================================== --}}

<div class="stats public-five">

    <div class="stat-card blue">
        <div class="stat-top">
            <div class="stat-icon">▧</div>
            <div>
                <div class="stat-label">Total Request</div>
                <div class="stat-value">
                    {{ number_format($totalRequest, 0, ',', '.') }}
                </div>
            </div>
        </div>
        <div class="stat-foot">Access log 24 jam terakhir</div>
    </div>

    <div class="stat-card green">
        <div class="stat-top">
            <div class="stat-icon">◉</div>
            <div>
                <div class="stat-label">Client Unik</div>
                <div class="stat-value">
                    {{ number_format($uniqueClients, 0, ',', '.') }}
                </div>
            </div>
        </div>
        <div class="stat-foot">Berdasarkan IP client</div>
    </div>

    <div class="stat-card red">
        <div class="stat-top">
            <div class="stat-icon">⊘</div>
            <div>
                <div class="stat-label">Request Diblokir</div>
                <div class="stat-value">
                    {{ number_format($blocked, 0, ',', '.') }}
                </div>
            </div>
        </div>
        <div class="stat-foot">Kebijakan filtering Squid</div>
    </div>

    <div class="stat-card purple">
        <div class="stat-top">
            <div class="stat-icon">%</div>
            <div>
                <div class="stat-label">Cache Hit Ratio</div>
                <div class="stat-value">
                    {{ number_format($cacheHitRatio, 1, ',', '.') }}%
                </div>
            </div>
        </div>
        <div class="stat-foot">
            HIT {{ number_format($cacheHit, 0, ',', '.') }}
            · MISS {{ number_format($cacheMiss, 0, ',', '.') }}
        </div>
    </div>

    <div class="stat-card green">
        <div class="stat-top">
            <div class="stat-icon">✓</div>
            <div>
                <div class="stat-label">Status Sistem</div>
                <div class="stat-value" style="font-size:20px">
                    {{ $systemStatus['label'] ?? 'Monitoring' }}
                </div>
            </div>
        </div>
        <div class="stat-foot">
            {{ $systemStatus['description'] ?? '-' }}
        </div>
    </div>

</div>


<div class="um-anchor-menu">
    <a href="#grafik-monitoring">Grafik Monitoring</a>
    <a href="#analisis">Analisis</a>
    <a href="#ringkasan-hasil">Ringkasan</a>
    <a href="#akses-terbaru">Akses Terbaru</a>
    <a href="#perangkat">Perangkat</a>
    <a href="#cache">Cache</a>
    <a href="#filter-blacklist">Filter & Blacklist</a>
</div>


{{-- ===================================================== --}}
{{-- GRAFIK MONITORING --}}
{{-- ===================================================== --}}

<section id="grafik-monitoring" class="um-section">

    <div class="um-section-head">
        <div>
            <h2>Grafik Monitoring 24 Jam</h2>
            <p>
                Seluruh grafik utama akses, filtering, cache,
                statistik per jam, jam puncak, dan distribusi hasil akses.
            </p>
        </div>

        <span class="um-muted-pill">24 JAM TERAKHIR</span>
    </div>


    <div class="um-grid-2">

        {{-- AKTIVITAS AKSES JARINGAN --}}
        <div class="um-panel">
            <div class="um-panel-head">
                <div class="um-panel-title">
                    ▥ Aktivitas Akses Jaringan
                </div>
                <span class="um-muted-pill">TOTAL + DIIZINKAN</span>
            </div>

            <div class="um-panel-body">
                <div class="um-chart">

                    <svg viewBox="0 0 800 210"
                         preserveAspectRatio="none"
                         aria-label="Aktivitas akses jaringan">

                        @if($totalPoints !== '')
                            <polygon
                                points="0,210 {{ $totalPoints }} 800,210"
                                fill="#1e86fa"
                                opacity=".07">
                            </polygon>

                            <polyline
                                points="{{ $totalPoints }}"
                                fill="none"
                                stroke="#1e86fa"
                                stroke-width="4"
                                stroke-linecap="round"
                                stroke-linejoin="round">
                            </polyline>
                        @endif

                        @if($allowedPoints !== '')
                            <polyline
                                points="{{ $allowedPoints }}"
                                fill="none"
                                stroke="#12b76a"
                                stroke-width="4"
                                stroke-linecap="round"
                                stroke-linejoin="round">
                            </polyline>
                        @endif
                    </svg>

                    <div class="um-axis">
                        @forelse($trafficCollection as $index => $item)
                            @if($index % 4 === 0)
                                <span>{{ $item['hour'] ?? '-' }}</span>
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

                <div class="um-legend">
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
        </div>


        {{-- TREN BLOCKED --}}
        <div class="um-panel">
            <div class="um-panel-head">
                <div class="um-panel-title">
                    ▥ Tren Request Terblokir
                </div>
                <span class="um-muted-pill">BLOCKED</span>
            </div>

            <div class="um-panel-body">
                <div class="um-chart">

                    <svg viewBox="0 0 800 210"
                         preserveAspectRatio="none"
                         aria-label="Tren request terblokir">

                        @if($blockedPoints !== '')
                            <polygon
                                points="0,210 {{ $blockedPoints }} 800,210"
                                fill="#f04455"
                                opacity=".07">
                            </polygon>

                            <polyline
                                points="{{ $blockedPoints }}"
                                fill="none"
                                stroke="#f04455"
                                stroke-width="4"
                                stroke-linecap="round"
                                stroke-linejoin="round">
                            </polyline>
                        @endif
                    </svg>

                    <div class="um-axis">
                        @forelse($blockedTrendCollection as $index => $item)
                            @if($index % 4 === 0)
                                <span>{{ $item['hour'] ?? '-' }}</span>
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

                <div class="um-legend">
                    <span>
                        <i style="background:#f04455"></i>
                        Request Terblokir
                    </span>
                </div>
            </div>
        </div>

    </div>


    <div class="um-grid-2" style="margin-top:12px">

        {{-- CACHE HIT / MISS --}}
        <div class="um-panel">
            <div class="um-panel-head">
                <div class="um-panel-title">
                    ▥ Perbandingan Cache HIT dan MISS
                </div>
                <span class="um-muted-pill">CACHE SQUID</span>
            </div>

            <div class="um-panel-body">
                <div class="um-chart">

                    <svg viewBox="0 0 800 210"
                         preserveAspectRatio="none"
                         aria-label="Perbandingan Cache HIT dan MISS">

                        @if($hitPoints !== '')
                            <polyline
                                points="{{ $hitPoints }}"
                                fill="none"
                                stroke="#12b76a"
                                stroke-width="4"
                                stroke-linecap="round"
                                stroke-linejoin="round">
                            </polyline>
                        @endif

                        @if($missPoints !== '')
                            <polygon
                                points="0,210 {{ $missPoints }} 800,210"
                                fill="#f04455"
                                opacity=".05">
                            </polygon>

                            <polyline
                                points="{{ $missPoints }}"
                                fill="none"
                                stroke="#f04455"
                                stroke-width="4"
                                stroke-linecap="round"
                                stroke-linejoin="round">
                            </polyline>
                        @endif
                    </svg>

                    <div class="um-axis">
                        @forelse($cacheTrendCollection as $index => $item)
                            @if($index % 4 === 0)
                                <span>{{ $item['hour'] ?? '-' }}</span>
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

                <div class="um-legend">
                    <span>
                        <i style="background:#12b76a"></i>
                        HIT
                    </span>

                    <span>
                        <i style="background:#f04455"></i>
                        MISS
                    </span>
                </div>

                @if($responseDifference !== null && $responseImprovement !== null)
                    <div class="um-note">
                        Selisih rata-rata response:
                        <strong>
                            {{ number_format((float) $responseDifference, 2, ',', '.') }} ms
                        </strong>.
                        Perubahan response HIT terhadap MISS:
                        <strong>
                            {{ number_format((float) $responseImprovement, 2, ',', '.') }}%
                        </strong>.
                    </div>
                @endif
            </div>
        </div>


        {{-- AKTIVITAS JARINGAN PER JAM --}}
        <div class="um-panel">
            <div class="um-panel-head">
                <div class="um-panel-title">
                    ▥ Aktivitas Jaringan per Jam
                </div>
                <span class="um-muted-pill">TRAFFIC HOURLY</span>
            </div>

            <div class="um-panel-body">
                <div class="um-chart">

                    <svg viewBox="0 0 800 210"
                         preserveAspectRatio="none"
                         aria-label="Aktivitas jaringan per jam">

                        @if($totalPoints !== '')
                            <polygon
                                points="0,210 {{ $totalPoints }} 800,210"
                                fill="#1e86fa"
                                opacity=".07">
                            </polygon>

                            <polyline
                                points="{{ $totalPoints }}"
                                fill="none"
                                stroke="#1e86fa"
                                stroke-width="4"
                                stroke-linecap="round"
                                stroke-linejoin="round">
                            </polyline>
                        @endif

                        @if($allowedPoints !== '')
                            <polyline
                                points="{{ $allowedPoints }}"
                                fill="none"
                                stroke="#12b76a"
                                stroke-width="4"
                                stroke-linecap="round"
                                stroke-linejoin="round">
                            </polyline>
                        @endif
                    </svg>

                    <div class="um-axis">
                        @forelse($trafficCollection as $index => $item)
                            @if($index % 4 === 0)
                                <span>{{ $item['hour'] ?? '-' }}</span>
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

                <div class="um-legend">
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
        </div>

    </div>


    <div class="um-grid-2" style="margin-top:12px">

        {{-- PEAK HOUR --}}
        <div class="um-panel">
            <div class="um-panel-head">
                <div class="um-panel-title">
                    ◴ Analisis Jam Puncak Akses
                </div>
                <span class="um-muted-pill">24 JAM</span>
            </div>

            <div class="um-panel-body">
                <div class="um-bar-chart">

                    @forelse($trafficCollection as $item)
                        @php
                            $requestCount = (int) ($item['total_request'] ?? 0);
                            $height = ($requestCount / $peakMax) * 100;
                        @endphp

                        <span
                            title="{{ $item['hour'] ?? '-' }} • {{ number_format($requestCount, 0, ',', '.') }} request"
                            style="height:{{ max(4, min($height, 100)) }}%">
                        </span>
                    @empty
                        @for($i = 0; $i < 24; $i++)
                            <span style="height:4%" title="Belum ada data"></span>
                        @endfor
                    @endforelse

                </div>

                <div class="um-note">
                    @if($peakHour)
                        Jam dengan aktivitas tertinggi:
                        <strong>{{ $peakHour['hour'] ?? '-' }}</strong>
                        dengan
                        <strong>
                            {{ number_format((int) ($peakHour['total_request'] ?? 0), 0, ',', '.') }}
                            request
                        </strong>.
                    @else
                        Belum ada data jam puncak.
                    @endif
                </div>
            </div>
        </div>


        {{-- DONUT DISTRIBUTION --}}
        <div class="um-panel">
            <div class="um-panel-head">
                <div class="um-panel-title">
                    ◕ Distribusi Hasil Akses
                </div>
                <span class="um-muted-pill">ACCESS RESULT</span>
            </div>

            <div class="um-donut-wrap">

                <div class="um-donut-box">
                    <div
                        class="um-donut"
                        style="
                            background:
                            conic-gradient(
                                #12b76a 0 {{ $allowedEnd }}%,
                                #f04455 {{ $allowedEnd }}% {{ $blockedEnd }}%,
                                #ffc248 {{ $blockedEnd }}% {{ $failedEnd }}%,
                                #e3eaf2 {{ $failedEnd }}% 100%
                            );
                        ">
                    </div>

                    <div class="um-donut-center">
                        <b>
                            {{ number_format($totalRequest, 0, ',', '.') }}
                        </b>
                        <small>Total Request</small>
                    </div>
                </div>

                <div class="um-kv-list">
                    <div class="um-kv">
                        <span>● Diizinkan</span>
                        <b>{{ number_format($allowedPercent, 1, ',', '.') }}%</b>
                    </div>

                    <div class="um-kv">
                        <span>● Diblokir</span>
                        <b>{{ number_format($blockedPercent, 1, ',', '.') }}%</b>
                    </div>

                    <div class="um-kv">
                        <span>● Tidak Selesai</span>
                        <b>{{ number_format($failedPercent, 1, ',', '.') }}%</b>
                    </div>

                    <div class="um-kv">
                        <span>● Lainnya</span>
                        <b>{{ number_format($otherPercent, 1, ',', '.') }}%</b>
                    </div>
                </div>

            </div>
        </div>

    </div>

</section>


{{-- ===================================================== --}}
{{-- ANALISIS MONITORING --}}
{{-- ===================================================== --}}

<section id="analisis" class="um-section">

    <div class="um-section-head">
        <div>
            <h2>Analisis Monitoring</h2>
            <p>
                Top domain, top client, kebijakan filter,
                perangkat terbaru, ringkasan, dan analisis cache.
            </p>
        </div>
        <span class="um-muted-pill">READ-ONLY</span>
    </div>


    <div class="um-grid-3">

        {{-- TOP DOMAIN --}}
        <div class="um-panel">
            <div class="um-panel-head">
                <div class="um-panel-title">
                    ◎ Top Domain Diakses
                </div>
            </div>

            <div class="um-panel-body">
                <div class="um-ranking">

                    @forelse($topDomainsCollection as $domain)
                        @php
                            $domainRequest = (int) ($domain['total_request'] ?? 0);
                            $width = $maxDomainRequest > 0
                                ? ($domainRequest / $maxDomainRequest) * 100
                                : 0;
                            $percentage = $totalRequest > 0
                                ? ($domainRequest / $totalRequest) * 100
                                : 0;
                        @endphp

                        <div class="um-rank">
                            <div
                                class="um-rank-name"
                                title="{{ $domain['domain'] ?? '-' }}">
                                {{ $domain['domain'] ?? '-' }}
                            </div>

                            <div class="um-progress">
                                <i style="width:{{ min(100, max(0, $width)) }}%"></i>
                            </div>

                            <b>
                                {{ number_format($percentage, 1, ',', '.') }}%
                            </b>
                        </div>
                    @empty
                        <div class="um-empty">Belum ada data domain.</div>
                    @endforelse

                </div>
            </div>
        </div>


        {{-- TOP CLIENT --}}
        <div class="um-panel">
            <div class="um-panel-head">
                <div class="um-panel-title">
                    ◎ Top Client IP
                </div>
            </div>

            <div class="um-panel-body">
                <div class="um-ranking">

                    @forelse($topClientsCollection as $client)
                        @php
                            $clientRequest = (int) ($client['total_request'] ?? 0);
                            $width = $maxClientRequest > 0
                                ? ($clientRequest / $maxClientRequest) * 100
                                : 0;
                            $percentage = $totalRequest > 0
                                ? ($clientRequest / $totalRequest) * 100
                                : 0;
                        @endphp

                        <div class="um-rank">
                            <div class="um-rank-name">
                                {{ $client['client_ip'] ?? '-' }}
                            </div>

                            <div class="um-progress">
                                <i style="width:{{ min(100, max(0, $width)) }}%"></i>
                            </div>

                            <b>
                                {{ number_format($percentage, 1, ',', '.') }}%
                            </b>
                        </div>
                    @empty
                        <div class="um-empty">Belum ada data client.</div>
                    @endforelse

                </div>
            </div>
        </div>


        {{-- FILTER POLICY --}}
        <div class="um-panel">
            <div class="um-panel-head">
                <div class="um-panel-title">
                    ⚙ Ringkasan Kebijakan Filter
                </div>
            </div>

            <div class="um-panel-body">
                <div class="um-policy-list">

                    <div class="um-policy">
                        <div class="um-policy-icon">≡</div>
                        <div>
                            <b>Domain Blacklist</b>
                            <small>Rule domain yang sedang aktif.</small>
                        </div>
                        <div class="um-policy-value">
                            {{ number_format($blacklistActive) }} aktif
                        </div>
                    </div>

                    <div class="um-policy">
                        <div class="um-policy-icon">◆</div>
                        <div>
                            <b>Include Subdomains</b>
                            <small>Rule yang berlaku untuk subdomain.</small>
                        </div>
                        <div class="um-policy-value">
                            {{ number_format($includeSubdomains) }} rule
                        </div>
                    </div>

                    <div class="um-policy">
                        <div class="um-policy-icon">↻</div>
                        <div>
                            <b>Sinkronisasi Squid</b>
                            <small>Status database terhadap file policy.</small>
                        </div>
                        <div class="um-policy-value">
                            {{ number_format($blacklistSynced) }} synced
                        </div>
                    </div>

                    <div class="um-policy">
                        <div class="um-policy-icon">⌁</div>
                        <div>
                            <b>HTTPS Filtering</b>
                            <small>Hostname/SNI tanpa full TLS decrypt.</small>
                        </div>
                        <div class="um-policy-value">
                            {{ $policyInfo['https_method'] ?? 'SNI' }}
                        </div>
                    </div>

                </div>

                <div class="um-note">
                    Mode Squid:
                    <strong>{{ $policyInfo['squid_mode'] ?? 'LOCAL' }}</strong>.
                    HTTPS intercept port:
                    <strong>{{ $policyInfo['https_port'] ?? 3130 }}</strong>.
                </div>
            </div>
        </div>

    </div>


    <div class="um-grid-3" style="margin-top:12px">

        {{-- LATEST DEVICES --}}
        <div class="um-panel">
            <div class="um-panel-head">
                <div class="um-panel-title">
                    ▣ Perangkat Terbaru
                </div>
                <span class="um-muted-pill">5 TERBARU</span>
            </div>

            <div class="um-panel-body">
                <div class="um-device-list">

                    @forelse($latestDevices as $device)
                        @php
                            $latestOnline = (bool) ($device->{$onlineColumn} ?? false);
                            $latestName = $device->device_name
                                ?: $device->hostname
                                ?: 'Perangkat Tanpa Nama';
                        @endphp

                        <div class="um-device-row">
                            <div class="um-device-icon">▣</div>

                            <div class="um-device-copy">
                                <b>{{ $latestName }}</b>
                                <small>
                                    {{ $device->mac_address ?: '-' }}
                                    ·
                                    {{ $device->ip_address ?: '-' }}
                                </small>
                            </div>

                            <span class="um-badge {{ $latestOnline ? 'ok' : 'bad' }}">
                                ● {{ $latestOnline ? 'Online' : 'Offline' }}
                            </span>
                        </div>
                    @empty
                        <div class="um-empty">Belum ada perangkat.</div>
                    @endforelse

                </div>
            </div>
        </div>


        {{-- MONITORING SUMMARY --}}
        <div class="um-panel">
            <div class="um-panel-head">
                <div class="um-panel-title">
                    ▧ Ringkasan Hasil Monitoring
                </div>
                <span class="um-muted-pill">24 JAM</span>
            </div>

            <div class="um-panel-body">
                <div class="um-kv-list">
                    <div class="um-kv">
                        <span>Total Request</span>
                        <b>{{ number_format($totalRequest, 0, ',', '.') }}</b>
                    </div>

                    <div class="um-kv">
                        <span>Request Diizinkan</span>
                        <b>{{ number_format($allowed, 0, ',', '.') }}</b>
                    </div>

                    <div class="um-kv">
                        <span>Request Diblokir</span>
                        <b>{{ number_format($blocked, 0, ',', '.') }}</b>
                    </div>

                    <div class="um-kv">
                        <span>Client Unik</span>
                        <b>{{ number_format($uniqueClients, 0, ',', '.') }}</b>
                    </div>

                    <div class="um-kv">
                        <span>Data Tercatat</span>
                        <b>{{ $formatBytes($totalBytes) }}</b>
                    </div>

                    <div class="um-kv">
                        <span>Cache Hit Ratio</span>
                        <b>{{ number_format($cacheHitRatio, 1, ',', '.') }}%</b>
                    </div>
                </div>
            </div>
        </div>


        {{-- CACHE DOMAIN --}}
        <div class="um-panel">
            <div class="um-panel-head">
                <div class="um-panel-title">
                    ◎ Domain dengan Aktivitas Cache
                </div>
                <span class="um-muted-pill">HIT / MISS</span>
            </div>

            <div class="um-panel-body">
                <div class="um-ranking">

                    @forelse($cacheDomainCollection as $domain)
                        @php
                            $cacheDomainRequest = (int) ($domain['total_request'] ?? 0);
                            $width = $maxCacheDomainRequest > 0
                                ? ($cacheDomainRequest / $maxCacheDomainRequest) * 100
                                : 0;
                        @endphp

                        <div class="um-rank">
                            <div
                                class="um-rank-name"
                                title="{{ $domain['domain'] ?? '-' }}">
                                {{ $domain['domain'] ?? '-' }}
                            </div>

                            <div class="um-progress green">
                                <i style="width:{{ min(100, max(0, $width)) }}%"></i>
                            </div>

                            <b>
                                {{ number_format((float) ($domain['hit_ratio'] ?? 0), 1, ',', '.') }}%
                            </b>
                        </div>
                    @empty
                        <div class="um-empty">Belum ada data domain cache.</div>
                    @endforelse

                </div>
            </div>
        </div>

    </div>


    <div class="um-grid-2" style="margin-top:12px">

        {{-- CACHE RATIO --}}
        <div class="um-panel">
            <div class="um-panel-head">
                <div class="um-panel-title">
                    ◕ Rasio Cache Hit
                </div>
                <span class="um-muted-pill">CACHE EFFICIENCY</span>
            </div>

            <div class="um-donut-wrap">

                <div class="um-donut-box">
                    <div
                        class="um-donut"
                        style="
                            background:
                            conic-gradient(
                                #12b76a 0 {{ min(100, max(0, $cacheHitRatio)) }}%,
                                #edf2f7 {{ min(100, max(0, $cacheHitRatio)) }}% 100%
                            );
                        ">
                    </div>

                    <div class="um-donut-center">
                        <b>
                            {{ number_format($cacheHitRatio, 1, ',', '.') }}%
                        </b>
                        <small>Hit Ratio</small>
                    </div>
                </div>

                <div class="um-kv-list">
                    <div class="um-kv">
                        <span>CACHE HIT</span>
                        <b>{{ number_format($cacheHit, 0, ',', '.') }}</b>
                    </div>

                    <div class="um-kv">
                        <span>CACHE MISS</span>
                        <b>{{ number_format($cacheMiss, 0, ',', '.') }}</b>
                    </div>

                    <div class="um-kv">
                        <span>Avg HIT</span>
                        <b>
                            {{
                                $avgHit !== null
                                    ? number_format((float) $avgHit, 2, ',', '.') . ' ms'
                                    : '-'
                            }}
                        </b>
                    </div>

                    <div class="um-kv">
                        <span>Avg MISS</span>
                        <b>
                            {{
                                $avgMiss !== null
                                    ? number_format((float) $avgMiss, 2, ',', '.') . ' ms'
                                    : '-'
                            }}
                        </b>
                    </div>

                    <div class="um-kv">
                        <span>Data HIT</span>
                        <b>{{ $formatBytes($hitBytes) }}</b>
                    </div>

                    <div class="um-kv">
                        <span>Data MISS</span>
                        <b>{{ $formatBytes($missBytes) }}</b>
                    </div>
                </div>

            </div>
        </div>


        {{-- CACHE OBJECT COMPARISON --}}
        <div class="um-panel">
            <div class="um-panel-head">
                <div class="um-panel-title">
                    ◫ Objek dengan Data HIT dan MISS
                </div>
                <span class="um-muted-pill">
                    BUKTI PEMBANDING CACHE
                </span>
            </div>

            <div class="um-object-list">

                @forelse($cacheObjectCollection as $object)
                    @php
                        $objectTotal =
                            (int) ($object['hit_count'] ?? 0)
                            +
                            (int) ($object['miss_count'] ?? 0);

                        $objectHitRatio = $objectTotal > 0
                            ? ((int) ($object['hit_count'] ?? 0) / $objectTotal) * 100
                            : 0;

                        $objectName = $shortObjectName(
                            $object['url'] ?? null,
                            $object['domain'] ?? null
                        );
                    @endphp

                    <div class="um-object-row">

                        <div class="um-object-top">
                            <span title="{{ $object['url'] ?? $objectName }}">
                                {{ $objectName }}
                            </span>

                            <b>
                                HIT {{ (int) ($object['hit_count'] ?? 0) }}
                                ·
                                MISS {{ (int) ($object['miss_count'] ?? 0) }}
                            </b>
                        </div>

                        <div class="um-progress green">
                            <i style="width:{{ min(100, max(0, $objectHitRatio)) }}%"></i>
                        </div>

                        <div class="um-object-meta">
                            Avg HIT:
                            {{ number_format((float) ($object['avg_hit_ms'] ?? 0), 2, ',', '.') }} ms
                            ·
                            Avg MISS:
                            {{ number_format((float) ($object['avg_miss_ms'] ?? 0), 2, ',', '.') }} ms

                            @if(($object['improvement_percent'] ?? null) !== null)
                                · Perubahan:
                                {{ number_format((float) $object['improvement_percent'], 2, ',', '.') }}%
                            @endif
                        </div>

                    </div>
                @empty
                    <div class="um-empty">
                        Belum ada objek yang mempunyai CACHE_HIT dan CACHE_MISS sekaligus.
                    </div>
                @endforelse

            </div>
        </div>

    </div>

</section>

{{-- ===================================================== --}}
{{-- TABLE: RINGKASAN HASIL MONITORING --}}
{{-- ===================================================== --}}

<section id="ringkasan-hasil" class="um-section">

    <div class="um-panel">
        <div class="um-panel-head">
            <div class="um-panel-title">
                ▧ Ringkasan Hasil Monitoring
            </div>

            <span class="um-muted-pill">
                TRAFIK {{ $formatBytes($totalBytes) }}
            </span>
        </div>

        <div class="um-table-wrap">
            <table class="um-table">

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
                            <td>{{ $index + 1 }}</td>

                            <td>
                                <strong>{{ $row['label'] }}</strong>
                            </td>

                            <td>
                                {{ number_format($row['value'], 0, ',', '.') }}
                            </td>

                            <td>
                                {{ number_format($row['percent'], 1, ',', '.') }}%
                            </td>

                            <td>{{ $row['note'] }}</td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>

</section>


{{-- ===================================================== --}}
{{-- TABLE: AKSES JARINGAN TERBARU --}}
{{-- ===================================================== --}}

<section id="akses-terbaru" class="um-section">

    <div class="um-panel">
        <div class="um-panel-head">
            <div class="um-panel-title">
                ▧ Akses Jaringan Terbaru
            </div>

            <span class="um-muted-pill">
                10 AKTIVITAS TERBARU
            </span>
        </div>

        <div class="um-table-wrap">
            <table class="um-table">

                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>IP Client</th>
                        <th>Domain / Tujuan</th>
                        <th>Protokol</th>
                        <th>Hasil</th>
                        <th>Squid Result</th>
                        <th>Response Time</th>
                        <th>Data</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($latestActivitiesCollection as $log)

                        @php
                            $resultClass = match($log->category) {
                                'BLOCKED' => 'bad',
                                'CACHE_HIT' => 'ok',
                                'CACHE_MISS' => 'warn',
                                'ALLOWED' => 'ok',
                                'FAILED' => 'warn',
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
                        @endphp

                        <tr>
                            <td>
                                {{ $log->logged_at?->format('H:i:s') ?? '-' }}
                            </td>

                            <td>
                                <span class="um-code">
                                    {{ $log->client_ip ?: '-' }}
                                </span>
                            </td>

                            <td>
                                {{
                                    $log->domain
                                    ?? $log->destination_ip
                                    ?? '-'
                                }}
                            </td>

                            <td>
                                <span class="um-badge blue">
                                    {{ $log->protocol ?: '-' }}
                                </span>
                            </td>

                            <td>
                                <span class="um-badge {{ $resultClass }}">
                                    {{ $resultLabel }}
                                </span>
                            </td>

                            <td>
                                <span class="um-badge blue">
                                    {{ $log->squid_code ?: '-' }}
                                </span>
                            </td>

                            <td>
                                {{
                                    $log->elapsed_ms !== null
                                        ? number_format(
                                            (float) $log->elapsed_ms,
                                            2,
                                            ',',
                                            '.'
                                        ) . ' ms'
                                        : '-'
                                }}
                            </td>

                            <td>
                                {{ $formatBytes($log->bytes ?? 0) }}
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="um-empty">
                                Belum ada aktivitas access log terbaru.
                            </td>
                        </tr>

                    @endforelse
                </tbody>

            </table>
        </div>
    </div>

</section>


{{-- ===================================================== --}}
{{-- TABLE: PERANGKAT TERHUBUNG LENGKAP --}}
{{-- ===================================================== --}}

<section id="perangkat" class="um-section">

    <div class="um-section-head">
        <div>
            <h2>Perangkat Terhubung</h2>
            <p>
                Tabel lengkap perangkat: nama, pemilik, IP, MAC,
                sistem operasi, segmen, interface, lokasi, status,
                penggunaan data 24 jam, dan last seen.
            </p>
        </div>

        <span class="um-muted-pill">
            {{ number_format($deviceTotal, 0, ',', '.') }} TOTAL
            ·
            {{ number_format($deviceOnline, 0, ',', '.') }} ONLINE
            ·
            {{ number_format($deviceOffline, 0, ',', '.') }} OFFLINE
        </span>
    </div>


    <div id="perangkat-table" class="um-panel">

        <div class="um-panel-head">
            <div class="um-panel-title">
                ▣ Daftar Perangkat Terhubung
            </div>

            <span class="um-muted-pill">READ-ONLY</span>
        </div>


        <form
            method="GET"
            action="{{ route('monitoring.home') }}#perangkat-table"
            class="um-table-tools"
        >
            <input
                type="text"
                name="device_search"
                value="{{ request('device_search') }}"
                class="um-input"
                placeholder="Cari perangkat, pemilik, IP, MAC, interface, lokasi..."
            >

            <select
                name="device_status"
                class="um-select"
            >
                <option value="">Semua Status</option>

                <option
                    value="online"
                    @selected(request('device_status') === 'online')
                >
                    Online
                </option>

                <option
                    value="offline"
                    @selected(request('device_status') === 'offline')
                >
                    Offline
                </option>
            </select>

            <select
                name="device_segment"
                class="um-select"
            >
                <option value="">Semua Segmen</option>

                @foreach($segments as $segment)
                    <option
                        value="{{ $segment }}"
                        @selected(request('device_segment') === $segment)
                    >
                        {{ $segment }}
                    </option>
                @endforeach
            </select>

            <select
                name="device_interface"
                class="um-select"
            >
                <option value="">Semua Interface</option>

                @foreach($interfaces as $interface)
                    <option
                        value="{{ $interface }}"
                        @selected(request('device_interface') === $interface)
                    >
                        {{ $interface }}
                    </option>
                @endforeach
            </select>

            <button
                type="submit"
                class="um-btn primary"
            >
                Filter
            </button>

            <a
                href="{{ route('monitoring.home') }}#perangkat-table"
                class="um-btn neutral"
            >
                Reset
            </a>
        </form>


        <div class="um-table-wrap">
            <table class="um-table device-table">

                <thead>
                    <tr>
                        <th>Nama Perangkat</th>
                        <th>Pemilik</th>
                        <th>IP Address</th>
                        <th>MAC Address</th>
                        <th>Sistem Operasi</th>
                        <th>Segmen</th>
                        <th>Interface</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                        <th>Data 24 Jam</th>
                        <th>Last Seen</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($devices as $device)

                        @php
                            $isOnline = (bool) ($device->{$onlineColumn} ?? false);
                            $lastSeen = $device->{$lastSeenColumn} ?? null;

                            $displayName =
                                $device->device_name
                                ?: $device->hostname
                                ?: 'Perangkat Tanpa Nama';

                            $usage = $device->ip_address
                                ? $usageByIp->get($device->ip_address)
                                : null;

                            $usageBytes = (int) ($usage->total_bytes ?? 0);

                            $locationDisplay = trim(
                                (string) ($device->location ?? '')
                            );

                            if (
                                $locationDisplay === ''
                                &&
                                !empty($device->segment)
                            ) {
                                $locationDisplay = (string) $device->segment;
                            }
                        @endphp

                        <tr>
                            <td>
                                <div class="um-device-name">
                                    <strong>▣ {{ $displayName }}</strong>

                                    <small>
                                        {{
                                            $device->hostname
                                            ?: $device->device_type
                                            ?: 'Hostname belum tersedia'
                                        }}
                                    </small>
                                </div>
                            </td>

                            <td>
                                <div class="um-device-name">
                                    <strong>
                                        {{ $device->owner_name ?: '-' }}
                                    </strong>

                                    <small>
                                        {{
                                            $device->owner_type
                                                ? ucfirst(
                                                    str_replace(
                                                        '_',
                                                        ' ',
                                                        $device->owner_type
                                                    )
                                                )
                                                : 'UNKNOWN'
                                        }}
                                    </small>
                                </div>
                            </td>

                            <td>
                                <span class="um-code">
                                    {{ $device->ip_address ?: '-' }}
                                </span>
                            </td>

                            <td>
                                <span class="um-code">
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
                                {{ $device->interface ?: '-' }}
                            </td>

                            <td>
                                {{ $locationDisplay ?: '-' }}
                            </td>

                            <td>
                                <span class="um-badge {{ $isOnline ? 'ok' : 'bad' }}">
                                    ● {{ $isOnline ? 'Online' : 'Offline' }}
                                </span>
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
                            <td colspan="11" class="um-empty">
                                Belum ada data perangkat.
                            </td>
                        </tr>

                    @endforelse
                </tbody>

            </table>
        </div>


        <div class="um-pagination">

            <div class="um-pagination-info">
                Menampilkan
                {{ $devices->firstItem() ?? 0 }}
                sampai
                {{ $devices->lastItem() ?? 0 }}
                dari
                {{ number_format($devices->total(), 0, ',', '.') }}
                perangkat.
            </div>

            <div class="um-pagination-actions">
                <a
                    class="um-page {{ $devices->onFirstPage() ? 'disabled' : '' }}"
                    href="{{
                        $devices->previousPageUrl()
                            ? $devices->previousPageUrl() . '#perangkat-table'
                            : '#'
                    }}"
                >
                    ‹
                </a>

                <span class="um-page current">
                    {{ $devices->currentPage() }}
                </span>

                <span class="um-page">
                    / {{ $devices->lastPage() }}
                </span>

                <a
                    class="um-page {{ $devices->hasMorePages() ? '' : 'disabled' }}"
                    href="{{
                        $devices->nextPageUrl()
                            ? $devices->nextPageUrl() . '#perangkat-table'
                            : '#'
                    }}"
                >
                    ›
                </a>
            </div>

        </div>


        <div class="um-note" style="margin:0 15px 15px">
            Penggunaan data dihitung dari access log 24 jam berdasarkan
            IP Address yang saat ini tersimpan pada perangkat.
        </div>

    </div>

</section>


{{-- ===================================================== --}}
{{-- TABLE: CACHE TERBARU --}}
{{-- ===================================================== --}}

<section id="cache" class="um-section">

    <div class="um-panel">

        <div class="um-panel-head">
            <div class="um-panel-title">
                ◴ Aktivitas Cache Terbaru
            </div>

            <span class="um-muted-pill">
                12 AKTIVITAS TERBARU
            </span>
        </div>

        <div class="um-table-wrap">
            <table class="um-table">

                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Jenis</th>
                        <th>Objek / Domain</th>
                        <th>IP Client</th>
                        <th>Response Time</th>
                        <th>Ukuran Data</th>
                        <th>Sumber</th>
                        <th>Squid Result</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($cacheRecentCollection as $activity)

                        @php
                            $isHit =
                                $activity->category
                                === 'CACHE_HIT';

                            $objectLabel =
                                $shortObjectName(
                                    $activity->url,
                                    $activity->domain
                                );
                        @endphp

                        <tr>
                            <td>
                                {{ $activity->logged_at?->format('H:i:s') ?? '-' }}
                            </td>

                            <td>
                                <span class="um-badge {{ $isHit ? 'ok' : 'bad' }}">
                                    {{ $isHit ? 'HIT' : 'MISS' }}
                                </span>
                            </td>

                            <td
                                title="{{
                                    $activity->url
                                    ?: $activity->domain
                                    ?: '-'
                                }}"
                            >
                                {{ $objectLabel }}
                            </td>

                            <td>
                                <span class="um-code">
                                    {{ $activity->client_ip ?: '-' }}
                                </span>
                            </td>

                            <td>
                                {{
                                    $activity->elapsed_ms !== null
                                        ? number_format(
                                            (float) $activity->elapsed_ms,
                                            2,
                                            ',',
                                            '.'
                                        ) . ' ms'
                                        : '-'
                                }}
                            </td>

                            <td>
                                {{ $formatBytes($activity->bytes ?? 0) }}
                            </td>

                            <td>
                                {{ $isHit ? 'Cache Lokal' : 'Origin Server' }}
                            </td>

                            <td>
                                <span class="um-badge blue">
                                    {{ $activity->squid_code ?: '-' }}
                                </span>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="um-empty">
                                Belum ada aktivitas cache terbaru.
                            </td>
                        </tr>

                    @endforelse
                </tbody>

            </table>
        </div>

    </div>

</section>


{{-- ===================================================== --}}
{{-- TABLE: BLACKLIST DOMAIN LENGKAP --}}
{{-- ===================================================== --}}

<section id="filter-blacklist" class="um-section">

    <div class="um-section-head">
        <div>
            <h2>Filter & Blacklist</h2>
            <p>
                Tabel lengkap domain, kategori, subdomain, sumber,
                status, sinkronisasi, dan waktu sinkronisasi terakhir.
            </p>
        </div>

        <span class="um-muted-pill">
            {{ number_format($blacklistActive) }} AKTIF
            ·
            {{ number_format($blacklistCategoryCount) }} KATEGORI
        </span>
    </div>


    <div id="blacklist-table" class="um-panel">

        <div class="um-panel-head">
            <div class="um-panel-title">
                ▤ Daftar Blacklist Domain
            </div>

            <span class="um-muted-pill">READ-ONLY</span>
        </div>


        <form
            method="GET"
            action="{{ route('monitoring.home') }}#blacklist-table"
            class="um-table-tools"
        >
            <input
                type="text"
                name="blacklist_search"
                value="{{ request('blacklist_search') }}"
                class="um-input"
                placeholder="Cari domain, kategori, sumber..."
            >

            <select
                name="blacklist_category"
                class="um-select"
            >
                <option value="">Semua Kategori</option>

                @foreach($blacklistCategories as $category)
                    <option
                        value="{{ $category }}"
                        @selected(request('blacklist_category') === $category)
                    >
                        {{ $category }}
                    </option>
                @endforeach
            </select>

            <select
                name="blacklist_status"
                class="um-select"
            >
                <option value="">Semua Status</option>

                <option
                    value="active"
                    @selected(request('blacklist_status') === 'active')
                >
                    Aktif
                </option>

                <option
                    value="inactive"
                    @selected(request('blacklist_status') === 'inactive')
                >
                    Nonaktif
                </option>
            </select>

            <select
                name="blacklist_sync_status"
                class="um-select"
            >
                <option value="">Semua Sync</option>

                <option
                    value="synced"
                    @selected(request('blacklist_sync_status') === 'synced')
                >
                    Synced
                </option>

                <option
                    value="pending"
                    @selected(request('blacklist_sync_status') === 'pending')
                >
                    Pending
                </option>

                <option
                    value="error"
                    @selected(request('blacklist_sync_status') === 'error')
                >
                    Error
                </option>
            </select>

            <button
                type="submit"
                class="um-btn primary"
            >
                Filter
            </button>

            <a
                href="{{ route('monitoring.home') }}#blacklist-table"
                class="um-btn neutral"
            >
                Reset
            </a>
        </form>


        <div class="um-table-wrap">
            <table class="um-table blacklist-table">

                <thead>
                    <tr>
                        <th>Domain</th>
                        <th>Kategori</th>
                        <th>Subdomain</th>
                        <th>Sumber</th>
                        <th>Status</th>
                        <th>Sinkronisasi</th>
                        <th>Terakhir Sync</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($domains as $domain)

                        <tr>
                            <td>
                                <strong>{{ $domain->domain }}</strong>
                            </td>

                            <td>
                                {{ $domain->category ?: '-' }}
                            </td>

                            <td>
                                @if($domain->include_subdomains)
                                    <span class="um-badge blue">Ya</span>
                                @else
                                    <span class="um-badge">Tidak</span>
                                @endif
                            </td>

                            <td>
                                {{ $domain->source ?: '-' }}
                            </td>

                            <td>
                                @if($domain->is_active)
                                    <span class="um-badge ok">
                                        ● Aktif
                                    </span>
                                @else
                                    <span class="um-badge warn">
                                        ● Nonaktif
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if($domain->sync_status === 'synced')
                                    <span class="um-badge ok">
                                        Synced
                                    </span>
                                @elseif($domain->sync_status === 'error')
                                    <span class="um-badge bad">
                                        Error
                                    </span>
                                @else
                                    <span class="um-badge warn">
                                        Pending
                                    </span>
                                @endif
                            </td>

                            <td>
                                {{ $formatDateTime($domain->synced_at) }}
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="um-empty">
                                Belum ada domain blacklist.
                            </td>
                        </tr>

                    @endforelse
                </tbody>

            </table>
        </div>


        <div class="um-pagination">

            <div class="um-pagination-info">
                Menampilkan
                {{ $domains->firstItem() ?? 0 }}
                sampai
                {{ $domains->lastItem() ?? 0 }}
                dari
                {{ number_format($domains->total(), 0, ',', '.') }}
                domain.
            </div>

            <div class="um-pagination-actions">
                <a
                    class="um-page {{ $domains->onFirstPage() ? 'disabled' : '' }}"
                    href="{{
                        $domains->previousPageUrl()
                            ? $domains->previousPageUrl() . '#blacklist-table'
                            : '#'
                    }}"
                >
                    ‹
                </a>

                <span class="um-page current">
                    {{ $domains->currentPage() }}
                </span>

                <span class="um-page">
                    / {{ $domains->lastPage() }}
                </span>

                <a
                    class="um-page {{ $domains->hasMorePages() ? '' : 'disabled' }}"
                    href="{{
                        $domains->nextPageUrl()
                            ? $domains->nextPageUrl() . '#blacklist-table'
                            : '#'
                    }}"
                >
                    ›
                </a>
            </div>

        </div>

    </div>


    <div class="um-note" style="margin-bottom:20px">
        Halaman monitoring ini bersifat read-only.
        Penambahan, perubahan, penghapusan, sinkronisasi blacklist,
        sinkronisasi perangkat, dan reconfigure Squid tetap dilakukan
        melalui dashboard admin.
    </div>

</section>

</div>

@endsection
