@extends('layouts.admin')

@section('title', 'Cache')

@section('content')

<style>

/* =========================================================

   CACHE PAGE — SEMINAR POLISH

   Scoped di halaman ini, tidak mengubah query/database.

   ========================================================= */

.cache-explainer{

    display:grid;

    grid-template-columns:repeat(3,minmax(0,1fr));

    gap:10px;

    margin:12px 0 14px;

}

.cache-explainer-card{

    min-width:0;

    padding:12px 13px;

    border:1px solid #dfe8f2;

    border-radius:13px;

    background:#fbfdff;

}

.cache-explainer-head{

    display:flex;

    align-items:center;

    gap:8px;

    margin-bottom:5px;

    font-size:10.5px;

    font-weight:850;

    color:#173866;

}

.cache-explainer-dot{

    width:9px;

    height:9px;

    flex:0 0 9px;

    border-radius:50%;

}

.cache-explainer-dot.hit{ background:#12b76a; }

.cache-explainer-dot.miss{ background:#f79009; }

.cache-explainer-dot.ratio{ background:#7f56d9; }

.cache-explainer-card p{

    margin:0;

    font-size:9.5px;

    line-height:1.5;

    color:#718299;

}

.cache-overview-grid{

    display:grid;

    grid-template-columns:minmax(0,1.55fr) minmax(235px,.8fr) minmax(235px,.8fr);

    gap:14px;

    align-items:stretch;

}

.cache-overview-grid > .panel{

    min-width:0;

}

.cache-overview-grid .chart{

    min-height:255px;

}

.cache-domain-panel .ranking{

    max-height:285px;

    overflow:auto;

    padding-right:3px;

    scrollbar-width:thin;

}

.cache-ratio-panel{

    display:flex;

    flex-direction:column;

}

.cache-ratio-summary{

    margin-top:10px;

    padding:10px 12px;

    border:1px solid #e3eaf2;

    border-radius:11px;

    background:#f8fbfe;

    font-size:9.5px;

    line-height:1.5;

    color:#6f8197;

}

.cache-section{

    margin-top:14px;

}

.cache-section .panel{

    overflow:hidden;

}

.cache-section-head{

    display:flex;

    align-items:flex-start;

    justify-content:space-between;

    gap:12px;

    margin-bottom:12px;

}

.cache-section-title{

    font-size:15px;

    font-weight:850;

    color:#0b285c;

    letter-spacing:-.015em;

}

.cache-section-subtitle{

    margin-top:4px;

    font-size:9.5px;

    line-height:1.45;

    color:#75869b;

}

.cache-table-shell{

    overflow:hidden;

    border:1px solid #e1e9f2;

    border-radius:13px;

    background:#fff;

}

.cache-table-scroll{

    max-height:410px;

    overflow:auto;

    scrollbar-width:thin;

}

.cache-table{

    width:100%;

    min-width:920px;

    border-collapse:separate;

    border-spacing:0;

    table-layout:fixed;

    font-size:9.5px;

}

.cache-table thead th{

    position:sticky;

    top:0;

    z-index:4;

    padding:10px 10px;

    border-bottom:1px solid #dfe8f1;

    background:#f2f6fa;

    color:#526985;

    font-size:8.8px;

    font-weight:850;

    text-align:left;

}

.cache-table tbody td{

    padding:9px 10px;

    border-bottom:1px solid #edf2f6;

    color:#284766;

    vertical-align:middle;

    overflow:hidden;

    white-space:nowrap;

    text-overflow:ellipsis;

}

.cache-table tbody tr:nth-child(even){

    background:#fbfdff;

}

.cache-table tbody tr:hover{

    background:#f4f9ff;

}

.cache-table tbody tr:last-child td{

    border-bottom:0;

}

.cache-table-note{

    padding:9px 12px;

    border-top:1px solid #e7eef5;

    background:#fbfdff;

    font-size:9.5px;

    line-height:1.45;

    color:#718299;

}

.cache-change{

    display:inline-flex;

    align-items:center;

    gap:4px;

}

.cache-change.positive{

    color:#067647;

}

.cache-change.negative{

    color:#b54708;

}

.cache-change.neutral{

    color:#53657b;

}

.cache-empty{

    padding:32px !important;

    text-align:center !important;

    color:#8291a5 !important;

}

@media(max-width:1180px){

    .cache-overview-grid{

        grid-template-columns:1fr 1fr;

    }

    .cache-overview-grid > .panel:first-child{

        grid-column:1 / -1;

    }

    .cache-explainer{

        grid-template-columns:1fr;

    }

}

@media(max-width:720px){

    .cache-overview-grid{

        grid-template-columns:1fr;

    }

    .cache-overview-grid > .panel:first-child{

        grid-column:auto;

    }

    .cache-section-head{

        flex-direction:column;

    }

}


.cache-domain-note{
    margin-top:12px;
    padding-top:10px;
    border-top:1px solid #edf2f6;
    font-size:9px;
    line-height:1.45;
    color:#718299;
}

.cache-domain-meta{
    min-width:0;
    display:flex;
    flex-direction:column;
    gap:2px;
}

.cache-domain-meta strong{
    overflow:hidden;
    color:#23466f;
    font-size:9.3px;
    font-weight:750;
    white-space:nowrap;
    text-overflow:ellipsis;
}

.cache-domain-meta small{
    color:#8a98aa;
    font-size:8px;
}

.cache-domain-percent{
    min-width:44px;
    text-align:right;
    color:#173866;
    font-size:9px;
    font-weight:850;
}

.cache-explainer{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:10px;
    margin:12px 0 14px;
}

.cache-explainer-card{
    min-width:0;
    padding:12px 13px;
    border:1px solid #dfe8f2;
    border-radius:13px;
    background:#fbfdff;
}

.cache-explainer-head{
    display:flex;
    align-items:center;
    gap:8px;
    margin-bottom:5px;
    font-size:10.5px;
    font-weight:850;
    color:#173866;
}

.cache-explainer-dot{
    width:9px;
    height:9px;
    flex:0 0 9px;
    border-radius:50%;
}

.cache-explainer-dot.hit{ background:#12b76a; }
.cache-explainer-dot.miss{ background:#f79009; }
.cache-explainer-dot.ratio{ background:#7f56d9; }

.cache-explainer-card p{
    margin:0;
    color:#718299;
    font-size:9.5px;
    line-height:1.5;
}

@media(max-width:1180px){
    .cache-explainer{
        grid-template-columns:1fr;
    }
}

</style>

@php

$hit = (int) ($summary['hit'] ?? 0);

$miss = (int) ($summary['miss'] ?? 0);

$totalCache = (int) ($summary['total'] ?? 0);

$hitRatio = (float) ($summary['hit_ratio'] ?? 0);

$missRatio = max(0, 100 - $hitRatio);

$avgHit = $summary['avg_hit_ms'] ?? null;

$avgMiss = $summary['avg_miss_ms'] ?? null;

$difference = $summary['response_difference_ms'] ?? null;

$improvement = $summary['response_improvement_percent'] ?? null;

$hitBytes = (int) ($summary['hit_bytes'] ?? 0);

$missBytes = (int) ($summary['miss_bytes'] ?? 0);

$now = now('Asia/Jakarta');

$cacheTrendData = collect($trend ?? [])

->values()

->all();

$maxDomainRequest = max(

1,

(int) (

collect($topDomains ?? [])

->max('total_request')

?? 0

)

);

$formatBytes = function ($bytes) {

$bytes = (int) $bytes;

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

};

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

            Cache Jaringan

        </h1>

        <p>

            Pantau efektivitas cache Squid, perbandingan HIT/MISS,

            penggunaan data, dan response time objek cache.

        </p>

        <div class="hero-chips">

            <span class="hero-chip">

                <span class="chip-icon green">●</span>

                Cache Monitoring

            </span>

            <span class="hero-chip">

                <span class="chip-icon">◆</span>

                Analisis HIT & MISS

            </span>

            <span class="hero-chip">

                <span class="chip-icon purple">▥</span>

                Data Real-time

            </span>

        </div>

    </div>

    <div class="hero-status">

        <span class="status-dot"></span>

        Monitoring Cache Aktif

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

{{-- SUMMARY --}}

{{-- ========================================================= --}}

<div class="stats">

    {{-- HIT --}}

    <div class="stat-card green">

        <div class="stat-top">

            <div class="stat-icon">

                ▤

            </div>

            <div>

                <div class="stat-label">

                    Cache HIT

                </div>

                <div class="stat-value">

                    {{ number_format(

                        $hit,

                        0,

                        ',',

                        '.'

                    ) }}

                </div>

            </div>

        </div>

        <div class="stat-foot">

            Objek dilayani dari cache

        </div>

    </div>

    {{-- MISS --}}

    <div class="stat-card orange">

        <div class="stat-top">

            <div class="stat-icon">

                ⊘

            </div>

            <div>

                <div class="stat-label">

                    Cache MISS

                </div>

                <div class="stat-value">

                    {{ number_format(

                        $miss,

                        0,

                        ',',

                        '.'

                    ) }}

                </div>

            </div>

        </div>

        <div class="stat-foot">

            Objek diambil dari origin

        </div>

    </div>

    {{-- HIT RATIO --}}

    <div class="stat-card purple">

        <div class="stat-top">

            <div class="stat-icon">

                %

            </div>

            <div>

                <div class="stat-label">

                    Hit Ratio

                </div>

                <div class="stat-value">

                    {{ number_format(

                        $hitRatio,

                        1,

                        ',',

                        '.'

                    ) }}%

                </div>

            </div>

        </div>

        <div class="stat-foot">

            Dari total HIT + MISS

        </div>

    </div>

    {{-- AVG HIT --}}

    <div class="stat-card blue">

        <div class="stat-top">

            <div class="stat-icon">

                ◴

            </div>

            <div>

                <div class="stat-label">

                    Avg HIT

                </div>

                <div class="stat-value">

                    @if($avgHit !== null)

                    {{ number_format(

                            $avgHit,

                            0,

                            ',',

                            '.'

                        ) }}

                    ms

                    @else

                    -

                    @endif

                </div>

            </div>

        </div>

        <div class="stat-foot">

            Response objek cache

        </div>

    </div>

    {{-- AVG MISS --}}

    <div class="stat-card orange">

        <div class="stat-top">

            <div class="stat-icon">

                ◴

            </div>

            <div>

                <div class="stat-label">

                    Avg MISS

                </div>

                <div class="stat-value">

                    @if($avgMiss !== null)

                    {{ number_format(

                            $avgMiss,

                            0,

                            ',',

                            '.'

                        ) }}

                    ms

                    @else

                    -

                    @endif

                </div>

            </div>

        </div>

        <div class="stat-foot">

            @if($improvement !== null && $improvement > 0)

                HIT {{ number_format($improvement, 1, ',', '.') }}% lebih cepat

            @elseif($improvement !== null && $improvement < 0)

                HIT {{ number_format(abs($improvement), 1, ',', '.') }}% lebih lambat

            @elseif($improvement !== null)

                Response HIT dan MISS relatif sama

            @else

                Menunggu data pembanding

            @endif

        </div>

    </div>

    {{-- HIT DATA --}}

    <div class="stat-card teal">

        <div class="stat-top">

            <div class="stat-icon">

                ▤

            </div>

            <div>

                <div class="stat-label">

                    Data Cache HIT

                </div>

                <div class="stat-value">

                    {{ $formatBytes($hitBytes) }}

                </div>

            </div>

        </div>

        <div class="stat-foot">

            Data yang tercatat sebagai HIT

        </div>

    </div>

</div>

{{-- ========================================================= --}}


<div class="cache-explainer">

    <div class="cache-explainer-card">
        <div class="cache-explainer-head">
            <span class="cache-explainer-dot hit"></span>
            Cache HIT
        </div>

        <p>
            Objek ditemukan di cache lokal Squid dan dapat dilayani tanpa mengambil ulang objek yang sama dari origin server.
        </p>
    </div>

    <div class="cache-explainer-card">
        <div class="cache-explainer-head">
            <span class="cache-explainer-dot miss"></span>
            Cache MISS
        </div>

        <p>
            Objek belum tersedia, sudah tidak valid, atau tidak dapat digunakan dari cache sehingga perlu diambil dari origin. MISS bukan berarti Squid gagal.
        </p>
    </div>

    <div class="cache-explainer-card">
        <div class="cache-explainer-head">
            <span class="cache-explainer-dot ratio"></span>
            Hit Ratio {{ number_format($hitRatio, 1, ',', '.') }}%
        </div>

        <p>
            Rasio aktual periode ini: {{ number_format($hitRatio, 1, ',', '.') }}% HIT dan {{ number_format($missRatio, 1, ',', '.') }}% MISS. Nilainya dapat berubah mengikuti trafik.
        </p>
    </div>

</div>

{{-- CACHE CHART + DOMAIN + RATIO --}}

{{-- ========================================================= --}}

<div class="cache-overview-grid">

    {{-- CHART --}}

    <div class="panel">

        <div class="panel-head">

            <div class="panel-title">

                ▥ Perbandingan Cache HIT dan MISS

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

                {{-- HIT AREA --}}

                <polygon

                    id="cacheHitArea"

                    fill="#12b76a"

                    opacity=".07"></polygon>

                {{-- HIT LINE --}}

                <polyline

                    id="cacheHitLine"

                    fill="none"

                    stroke="#12b76a"

                    stroke-width="4"

                    stroke-linecap="round"

                    stroke-linejoin="round"></polyline>

                {{-- MISS AREA --}}

                <polygon

                    id="cacheMissArea"

                    fill="#f79009"

                    opacity=".07"></polygon>

                {{-- MISS LINE --}}

                <polyline

                    id="cacheMissLine"

                    fill="none"

                    stroke="#f79009"

                    stroke-width="4"

                    stroke-linecap="round"

                    stroke-linejoin="round"></polyline>

            </svg>

            <div class="axis">

                @foreach($trend as $index => $item)

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

                <i style="background:#12b76a"></i>

                HIT

            </span>

            <span>

                <i style="background:#f79009"></i>

                MISS

            </span>

        </div>

    </div>

    {{-- TOP DOMAIN CACHE --}}

    <div class="panel cache-domain-panel">

        <div class="panel-title">
            ◎ Top Domain Cache
        </div>

        @php
            /*
            |--------------------------------------------------------------------------
            | Total request dari domain yang tampil
            |--------------------------------------------------------------------------
            |
            | Angka persentase pada sisi kanan menunjukkan kontribusi aktivitas
            | cache domain terhadap total request pada daftar Top Domain.
            |
            | Ini sengaja dipisahkan dari Hit Ratio global agar tampilan tidak
            | membingungkan ketika hit_ratio per-domain sangat kecil.
            |
            */

            $topDomainTotal = max(
                1,
                (int) collect($topDomains ?? [])->sum('total_request')
            );
        @endphp

        <div
            class="ranking"
            style="margin-top:14px">

            @forelse($topDomains as $domain)

                @php
                    $domainRequest =
                        (int) ($domain['total_request'] ?? 0);

                    /*
                    |--------------------------------------------------------------------------
                    | Porsi aktivitas cache domain
                    |--------------------------------------------------------------------------
                    */

                    $domainShare =
                        ($domainRequest / $topDomainTotal) * 100;

                    /*
                    |--------------------------------------------------------------------------
                    | Lebar bar relatif terhadap domain terbesar
                    |--------------------------------------------------------------------------
                    */

                    $width =
                        $maxDomainRequest > 0
                            ? ($domainRequest / $maxDomainRequest) * 100
                            : 0;

                    /*
                    |--------------------------------------------------------------------------
                    | Hit Ratio domain
                    |--------------------------------------------------------------------------
                    |
                    | Tetap dipertahankan untuk tooltip/detail.
                    |
                    */

                    $domainHitRatio =
                        (float) ($domain['hit_ratio'] ?? 0);
                @endphp

                <div
                    class="rank-row"
                    title="{{ $domain['domain'] }} • {{ number_format($domainRequest, 0, ',', '.') }} request cache • Hit Ratio {{ number_format($domainHitRatio, 2, ',', '.') }}%">

                    <div class="cache-domain-meta">

                        <strong title="{{ $domain['domain'] }}">
                            {{ $domain['domain'] }}
                        </strong>

                        <small>
                            {{ number_format(
                                $domainRequest,
                                0,
                                ',',
                                '.'
                            ) }}
                            request
                        </small>

                    </div>

                    <div class="progress green">

                        <i
                            style="
                                width:
                                {{ min(
                                    max($width, 1.5),
                                    100
                                ) }}%
                            ">
                        </i>

                    </div>

                    <div class="cache-domain-percent">

                        {{ number_format(
                            $domainShare,
                            1,
                            ',',
                            '.'
                        ) }}%

                    </div>

                </div>

            @empty

                <div
                    style="
                        text-align:center;
                        padding:30px;
                        color:#7b8794;
                    ">

                    Belum ada data cache domain.

                </div>

            @endforelse

        </div>

        <div class="cache-domain-note">

            <strong>Persentase</strong> menunjukkan porsi aktivitas cache
            masing-masing domain pada daftar Top Domain.
            Hit Ratio keseluruhan tetap ditampilkan pada panel
            <strong>Rasio Cache Hit</strong>.

        </div>

    </div>

    {{-- HIT RATIO --}}

    <div class="panel cache-ratio-panel">

        <div class="panel-title">

            ◕ Rasio Cache Hit

        </div>

        <div

            style="

                position:relative;

                margin-top:14px;

            ">

            <div

                class="donut"

                style="

                    background:

                    conic-gradient(

                        #12b76a 0

                        {{ min(

                            $hitRatio,

                            100

                        ) }}%,

                        #eef3f7

                        {{ min(

                            $hitRatio,

                            100

                        ) }}%

                        100%

                    )

                "></div>

            <div class="donut-center">

                <b>

                    {{ number_format(

                        $hitRatio,

                        1,

                        ',',

                        '.'

                    ) }}%

                </b>

                <small>

                    Hit Ratio

                </small>

            </div>

        </div>

        <div

            class="kv-list"

            style="margin-top:12px">

            <div class="kv-row">

                <span>

                    Cache HIT

                </span>

                <b>

                    {{ number_format(

                        $hit,

                        0,

                        ',',

                        '.'

                    ) }}

                </b>

            </div>

            <div class="kv-row">

                <span>

                    Cache MISS

                </span>

                <b>

                    {{ number_format(

                        $miss,

                        0,

                        ',',

                        '.'

                    ) }}

                </b>

            </div>

            <div class="kv-row">

                <span>

                    Total

                </span>

                <b>

                    {{ number_format(

                        $totalCache,

                        0,

                        ',',

                        '.'

                    ) }}

                </b>

            </div>

            <div class="kv-row">

                <span>

                    Data HIT

                </span>

                <b>

                    {{ $formatBytes(

                        $hitBytes

                    ) }}

                </b>

            </div>

            <div class="kv-row">

                <span>

                    Data MISS

                </span>

                <b>

                    {{ $formatBytes(

                        $missBytes

                    ) }}

                </b>

            </div>

        </div>

        <div class="cache-ratio-summary">

            <strong>Interpretasi:</strong>
            nilai ini bersifat dinamis mengikuti trafik.
            HIT berarti objek dilayani dari cache lokal,
            sedangkan MISS berarti objek perlu diambil dari origin.
            MISS tidak otomatis berubah menjadi HIT apabila objek
            tidak cacheable, berubah, kedaluwarsa, atau tidak digunakan kembali.

        </div>

    </div>

</div>

{{-- ========================================================= --}}

{{-- OBJECT COMPARISON --}}

{{-- ========================================================= --}}

<div class="cache-section">

    <div class="panel">

        <div class="cache-section-head">

            <div>

                <div class="cache-section-title">

                    ⚡ Perbandingan Response Time Objek

                </div>

                <div class="cache-section-subtitle">

                    Membandingkan objek yang memiliki pasangan MISS dan HIT. Nilai positif berarti HIT lebih cepat; nilai negatif berarti pada sampel tersebut HIT lebih lambat.

                </div>

            </div>

        </div>

        <div class="cache-table-shell">

            <div class="cache-table-scroll">

                <table class="cache-table">

                    <thead>

                        <tr>

                            <th style="width:290px">Objek / URL</th>

                            <th style="width:180px">Domain</th>

                            <th style="width:70px">HIT</th>

                            <th style="width:70px">MISS</th>

                            <th style="width:95px">Avg HIT</th>

                            <th style="width:95px">Avg MISS</th>

                            <th style="width:95px">Selisih</th>

                            <th style="width:110px">Perubahan</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($objectComparisons as $object)

                            @php

                                $change = $object['improvement_percent'] ?? null;

                                $changeClass = $change === null

                                    ? 'neutral'

                                    : ($change > 0

                                        ? 'positive'

                                        : ($change < 0 ? 'negative' : 'neutral'));

                            @endphp

                            <tr>

                                <td title="{{ $object['url'] }}">

                                    {{ \Illuminate\Support\Str::limit($object['url'], 60) }}

                                </td>

                                <td title="{{ $object['domain'] ?? '-' }}">

                                    {{ $object['domain'] ?? '-' }}

                                </td>

                                <td>

                                    <span class="badge ok">

                                        {{ $object['hit_count'] }}

                                    </span>

                                </td>

                                <td>

                                    <span class="badge orange">

                                        {{ $object['miss_count'] }}

                                    </span>

                                </td>

                                <td>

                                    {{ number_format($object['avg_hit_ms'], 2, ',', '.') }} ms

                                </td>

                                <td>

                                    {{ number_format($object['avg_miss_ms'], 2, ',', '.') }} ms

                                </td>

                                <td>

                                    {{ number_format($object['difference_ms'], 2, ',', '.') }} ms

                                </td>

                                <td>

                                    @if($change !== null)

                                        <span class="cache-change {{ $changeClass }}">

                                            @if($change > 0)

                                                ↑

                                            @elseif($change < 0)

                                                ↓

                                            @else

                                                •

                                            @endif

                                            {{ number_format(abs($change), 2, ',', '.') }}%

                                        </span>

                                    @else

                                        -

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="8" class="cache-empty">

                                    Belum ada pasangan objek MISS → HIT yang dapat dibandingkan.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="cache-table-note">

                <strong>Catatan:</strong>

                Cache MISS adalah kondisi normal ketika objek belum tersedia di cache.

                Persentase negatif pada kolom Perubahan tidak otomatis berarti Squid bermasalah; hasil dipengaruhi objek, ukuran data, origin server, dan jumlah sampel.

            </div>

        </div>

    </div>

</div>

{{-- ========================================================= --}}

{{-- RECENT CACHE ACTIVITIES --}}

{{-- ========================================================= --}}

<div class="cache-section">

    <div class="panel">

        <div class="cache-section-head">

            <div>

                <div class="cache-section-title">

                    ◴ Aktivitas Cache Terbaru

                </div>

                <div class="cache-section-subtitle">

                    Sampel aktivitas cache terbaru untuk menunjukkan objek yang dilayani dari cache lokal atau diambil dari origin server.

                </div>

            </div>

        </div>

        <div class="cache-table-shell">

            <div class="cache-table-scroll">

                <table class="cache-table">

                    <thead>

                        <tr>

                            <th style="width:145px">Waktu</th>

                            <th style="width:80px">Jenis</th>

                            <th style="width:260px">Domain / Objek</th>

                            <th style="width:120px">IP Client</th>

                            <th style="width:115px">Response</th>

                            <th style="width:105px">Ukuran Data</th>

                            <th style="width:115px">Sumber</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($recentActivities as $log)

                            <tr>

                                <td>

                                    {{ $log->logged_at?->format('d/m/Y H:i:s') ?? '-' }}

                                </td>

                                <td>

                                    @if($log->category === 'CACHE_HIT')

                                        <span class="badge ok">HIT</span>

                                    @else

                                        <span class="badge orange">MISS</span>

                                    @endif

                                </td>

                                <td

                                    title="{{ $log->url }}">

                                    {{

                                        $log->domain

                                        ?? \Illuminate\Support\Str::limit($log->url, 50)

                                        ?? '-'

                                    }}

                                </td>

                                <td>

                                    {{ $log->client_ip }}

                                </td>

                                <td>

                                    {{ number_format($log->elapsed_ms, 0, ',', '.') }} ms

                                </td>

                                <td>

                                    {{ $formatBytes($log->bytes) }}

                                </td>

                                <td>

                                    @if($log->category === 'CACHE_HIT')

                                        Cache Lokal

                                    @else

                                        Origin Server

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="7" class="cache-empty">

                                    Belum ada aktivitas cache.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="cache-table-note">

                <strong>HIT</strong> = objek dilayani dari cache lokal.

                <strong>MISS</strong> = objek perlu diambil dari origin server.

                Keduanya merupakan kondisi normal dalam mekanisme caching.

            </div>

        </div>

    </div>

</div>

{{-- ========================================================= --}}

{{-- CACHE CHART --}}

{{-- ========================================================= --}}

<script>

    document.addEventListener(

        'DOMContentLoaded',

        function() {

            const cacheTrend = @json($cacheTrendData);

            if (!cacheTrend.length) {

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

                ...cacheTrend.map(

                    item =>

                    Math.max(

                        Number(

                            item.hit || 0

                        ),

                        Number(

                            item.miss || 0

                        )

                    )

                )

            );

            const step =

                cacheTrend.length > 1

                ?

                width /

                (

                    cacheTrend.length - 1

                )

                :

                width;

            function createPoints(field) {

                return cacheTrend

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

            const hitPoints =

                createPoints('hit');

            const missPoints =

                createPoints('miss');

            const hitLine =

                document.getElementById(

                    'cacheHitLine'

                );

            const missLine =

                document.getElementById(

                    'cacheMissLine'

                );

            const hitArea =

                document.getElementById(

                    'cacheHitArea'

                );

            const missArea =

                document.getElementById(

                    'cacheMissArea'

                );

            if (hitLine) {

                hitLine.setAttribute(

                    'points',

                    hitPoints

                );

            }

            if (missLine) {

                missLine.setAttribute(

                    'points',

                    missPoints

                );

            }

            if (hitArea) {

                hitArea.setAttribute(

                    'points',

                    `0,${height} ` +

                    hitPoints +

                    ` ${width},${height}`

                );

            }

            if (missArea) {

                missArea.setAttribute(

                    'points',

                    `0,${height} ` +

                    missPoints +

                    ` ${width},${height}`

                );

            }

        }

    );

</script>

@endsection
