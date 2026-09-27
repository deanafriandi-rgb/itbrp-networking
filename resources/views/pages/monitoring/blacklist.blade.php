@extends('layouts.monitoring')

@section('title', 'Filter & Blacklist')

@section('content')

@php
$now =
now('Asia/Jakarta');

$total =
(int) ($summary['total'] ?? 0);

$active =
(int) ($summary['active'] ?? 0);

$inactive =
(int) ($summary['inactive'] ?? 0);

$categoryCount =
(int) ($summary['categories'] ?? 0);

$synced =
(int) ($summary['synced'] ?? 0);

$pending =
(int) ($summary['pending'] ?? 0);

$errorCount =
(int) ($summary['error'] ?? 0);

$includeSubdomains =
(int) ($summary['include_subdomains'] ?? 0);


/*
|--------------------------------------------------------------------------
| Blocked Trend
|--------------------------------------------------------------------------
*/

$blockedTrendCollection =
collect(
$blockedTrend ?? []
)->values();


$maxBlocked =
max(
1,
(int) (
$blockedTrendCollection
->max('blocked')
?? 0
)
);


$trendCount =
$blockedTrendCollection->count();


$trendStep =
$trendCount > 1
? 800 / ($trendCount - 1)
: 800;


$blockedPointList = [];


foreach (
$blockedTrendCollection
as $index => $item
) {
$x =
$index * $trendStep;

$value =
(int) ($item['blocked'] ?? 0);

$y =
190
-
(
($value / $maxBlocked)
* 170
);


$blockedPointList[] =
number_format(
$x,
1,
'.',
''
)
.
','
.
number_format(
$y,
1,
'.',
''
);
}


$blockedPoints =
implode(
' ',
$blockedPointList
);


$policyConfigured =
$active > 0;


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
@endphp


<style>
    .monitoring-blacklist {
        --mb-border: #dce7f2;
        --mb-text: #17385f;
        --mb-muted: #78879b;
    }

    .monitoring-blacklist * {
        box-sizing: border-box;
    }

    .mb-main-grid {
        display: grid;
        grid-template-columns:
            minmax(0, 1.45fr) minmax(330px, .55fr);
        gap: 12px;
    }

    .mb-filterbar {
        display: grid;
        grid-template-columns:
            minmax(240px, 1fr) 160px 145px 145px auto auto;
        gap: 9px;
        margin-bottom: 14px;
        padding: 11px;
        border: 1px solid #e1eaf4;
        border-radius: 14px;
        background: #f8fbff;
    }

    .mb-input,
    .mb-select {
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

    .mb-input {
        padding: 0 13px;
    }

    .mb-select {
        padding: 0 10px;
    }

    .mb-input:focus,
    .mb-select:focus {
        border-color: #78b9ff;
        box-shadow:
            0 0 0 4px rgba(30, 134, 250, .10);
    }

    .mb-btn {
        min-height: 42px;
        border: 0;
        border-radius: 11px;
        padding: 0 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        cursor: pointer;
        font: inherit;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .mb-btn.primary {
        color: #fff;
        background:
            linear-gradient(135deg,
                #1e86fa,
                #4da5ff);
    }

    .mb-btn.neutral {
        color: #52627a;
        background: #eef3f8;
    }

    .mb-policy-list {
        display: grid;
        gap: 10px;
    }

    .mb-policy {
        display: grid;
        grid-template-columns:
            36px minmax(0, 1fr) auto;
        gap: 10px;
        align-items: center;
        padding: 12px;
        border: 1px solid #e3ebf4;
        border-radius: 13px;
        background: #f9fbfe;
    }

    .mb-picon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        background: #eaf4ff;
        color: #1785f8;
        font-size: 13px;
        font-weight: 900;
    }

    .mb-policy b {
        display: block;
        color: #193b64;
        font-size: 10px;
    }

    .mb-policy small {
        display: block;
        margin-top: 3px;
        color: #7e8ca0;
        font-size: 9px;
        line-height: 1.5;
    }

    .mb-policy-value {
        max-width: 110px;
        text-align: right;
        color: #355775;
        font-size: 9px;
        font-weight: 800;
    }

    .mb-note {
        margin-top: 12px;
        padding: 12px 13px;
        border: 1px solid #e2ebf5;
        border-radius: 12px;
        background: #f7faff;
        color: #647891;
        font-size: 10px;
        line-height: 1.7;
    }

    .mb-domain {
        font-weight: 800;
        color: #153765;
    }

    .mb-empty {
        padding: 36px 18px;
        text-align: center;
        color: #8190a3;
        font-size: 11px;
        line-height: 1.7;
    }

    .mb-chart-panel {
        margin-top: 12px;
    }

    @media (max-width: 1150px) {
        .mb-filterbar {
            grid-template-columns:
                1fr 1fr 1fr;
        }

        .mb-input {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 950px) {
        .mb-main-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        .mb-filterbar {
            grid-template-columns: 1fr;
        }

        .mb-input {
            grid-column: auto;
        }

        .mb-btn {
            width: 100%;
        }
    }
</style>


<div class="monitoring-blacklist">

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
                Filter & Blacklist
                <br>

                <span class="accent">
                    untuk Akses Internet yang Lebih Terkontrol
                </span>
            </h1>

            <p>
                Lihat domain yang dibatasi oleh kebijakan Squid Proxy,
                status sinkronisasi blacklist, serta tren request
                yang terblokir dalam mode read-only.
            </p>

            <div class="hero-chips">

                <span class="hero-chip">
                    <span class="chip-icon green">▣</span>
                    Domain ACL
                </span>

                <span class="hero-chip">
                    <span class="chip-icon">◆</span>
                    Filtering Terkontrol
                </span>

                <span class="hero-chip">
                    <span class="chip-icon purple">▥</span>
                    Read-only
                </span>

            </div>

        </div>


        <div class="hero-status">
            <span class="status-dot"></span>
            Mode {{ $policyInfo['squid_mode'] ?? 'LOCAL' }}
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

        <div class="stat-card red">

            <div class="stat-top">

                <div class="stat-icon">
                    ⊘
                </div>

                <div>
                    <div class="stat-label">
                        Domain Aktif
                    </div>

                    <div class="stat-value">
                        {{ number_format($active, 0, ',', '.') }}
                    </div>
                </div>

            </div>

            <div class="stat-foot">
                Dari {{ number_format($total, 0, ',', '.') }}
                domain tersimpan
            </div>

        </div>


        <div class="stat-card blue">

            <div class="stat-top">

                <div class="stat-icon">
                    ▦
                </div>

                <div>
                    <div class="stat-label">
                        Kategori Aktif
                    </div>

                    <div class="stat-value">
                        {{ number_format($categoryCount, 0, ',', '.') }}
                    </div>
                </div>

            </div>

            <div class="stat-foot">
                Berdasarkan blacklist aktif
            </div>

        </div>


        <div class="stat-card orange">

            <div class="stat-top">

                <div class="stat-icon">
                    ▧
                </div>

                <div>
                    <div class="stat-label">
                        Request Terblokir
                    </div>

                    <div class="stat-value">
                        {{ number_format($blocked24, 0, ',', '.') }}
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
                    ◆
                </div>

                <div>
                    <div class="stat-label">
                        Sinkronisasi
                    </div>

                    <div
                        class="stat-value"
                        style="font-size:22px">
                        {{ number_format($synced, 0, ',', '.') }}
                    </div>
                </div>

            </div>

            <div class="stat-foot">
                Pending {{ number_format($pending, 0, ',', '.') }}
                · Error {{ number_format($errorCount, 0, ',', '.') }}
            </div>

        </div>


        <div class="stat-card purple">

            <div class="stat-top">

                <div class="stat-icon">
                    ⌁
                </div>

                <div>
                    <div class="stat-label">
                        HTTPS Policy
                    </div>

                    <div
                        class="stat-value"
                        style="font-size:22px">
                        SNI
                    </div>
                </div>

            </div>

            <div class="stat-foot">
                Tanpa full TLS decrypt
            </div>

        </div>

    </div>


    {{-- ===================================================== --}}
    {{-- TABLE + POLICY --}}
    {{-- ===================================================== --}}

    <div class="mb-main-grid section-pad">

        {{-- BLACKLIST TABLE --}}
        <div class="panel">

            <div class="panel-head">

                <div class="panel-title">
                    ▤ Daftar Blacklist Domain
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
                action="{{ route('monitoring.blacklist') }}"
                class="mb-filterbar">

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    class="mb-input"
                    placeholder="Cari domain, kategori, sumber...">


                <select
                    name="category"
                    class="mb-select">
                    <option value="">
                        Semua Kategori
                    </option>

                    @foreach($categories as $category)

                    <option
                        value="{{ $category }}"
                        @selected(request('category')===$category)>
                        {{ $category }}
                    </option>

                    @endforeach
                </select>


                <select
                    name="status"
                    class="mb-select">
                    <option value="">
                        Semua Status
                    </option>

                    <option
                        value="active"
                        @selected(request('status')==='active' )>
                        Aktif
                    </option>

                    <option
                        value="inactive"
                        @selected(request('status')==='inactive' )>
                        Nonaktif
                    </option>
                </select>


                <select
                    name="sync_status"
                    class="mb-select">
                    <option value="">
                        Semua Sync
                    </option>

                    <option
                        value="synced"
                        @selected(request('sync_status')==='synced' )>
                        Synced
                    </option>

                    <option
                        value="pending"
                        @selected(request('sync_status')==='pending' )>
                        Pending
                    </option>

                    <option
                        value="error"
                        @selected(request('sync_status')==='error' )>
                        Error
                    </option>
                </select>


                <button
                    type="submit"
                    class="mb-btn primary">
                    Filter
                </button>


                <a
                    href="{{ route('monitoring.blacklist') }}"
                    class="mb-btn neutral">
                    Reset
                </a>

            </form>


            <div class="table-wrap">

                <table class="table">

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
                                <span class="mb-domain">
                                    {{ $domain->domain }}
                                </span>
                            </td>


                            <td>
                                {{ $domain->category ?: '-' }}
                            </td>


                            <td>

                                @if($domain->include_subdomains)

                                <span class="badge blue">
                                    Ya
                                </span>

                                @else

                                <span class="badge">
                                    Tidak
                                </span>

                                @endif

                            </td>


                            <td>
                                {{ $domain->source ?: '-' }}
                            </td>


                            <td>

                                @if($domain->is_active)

                                <span class="badge ok">
                                    ● Aktif
                                </span>

                                @else

                                <span class="badge orange">
                                    ● Nonaktif
                                </span>

                                @endif

                            </td>


                            <td>

                                @if($domain->sync_status === 'synced')

                                <span class="badge ok">
                                    Synced
                                </span>

                                @elseif($domain->sync_status === 'error')

                                <span class="badge bad">
                                    Error
                                </span>

                                @else

                                <span class="badge orange">
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

                            <td
                                colspan="7"
                                style="
                                        text-align:center;
                                        padding:36px;
                                    ">
                                Belum ada domain blacklist.
                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            @if($domains->hasPages())

            <div style="margin-top:16px">
                {{ $domains->links() }}
            </div>

            @endif

        </div>


        {{-- POLICY INFORMATION --}}
        <div>

            <div class="panel">

                <div class="panel-head">

                    <div class="panel-title">
                        ⚙ Ringkasan Kebijakan Filter
                    </div>

                </div>


                <div class="mb-policy-list">

                    <div class="mb-policy">

                        <div class="mb-picon">
                            ≡
                        </div>

                        <div>
                            <b>
                                Domain Blacklist
                            </b>

                            <small>
                                Rule domain yang sedang aktif.
                            </small>
                        </div>

                        <div class="mb-policy-value">
                            {{ number_format($active, 0, ',', '.') }}
                            aktif
                        </div>

                    </div>


                    <div class="mb-policy">

                        <div class="mb-picon">
                            ◆
                        </div>

                        <div>
                            <b>
                                Include Subdomains
                            </b>

                            <small>
                                Rule yang berlaku untuk subdomain.
                            </small>
                        </div>

                        <div class="mb-policy-value">
                            {{ number_format(
                                $includeSubdomains,
                                0,
                                ',',
                                '.'
                            ) }}
                            rule
                        </div>

                    </div>


                    <div class="mb-policy">

                        <div class="mb-picon">
                            ↻
                        </div>

                        <div>
                            <b>
                                Sinkronisasi Squid
                            </b>

                            <small>
                                Status database blacklist terhadap file policy.
                            </small>
                        </div>

                        <div class="mb-policy-value">
                            {{ $synced }} synced
                        </div>

                    </div>


                    <div class="mb-policy">

                        <div class="mb-picon">
                            ⌁
                        </div>

                        <div>
                            <b>
                                HTTPS Filtering
                            </b>

                            <small>
                                Hostname/SNI policy tanpa full TLS decrypt.
                            </small>
                        </div>

                        <div class="mb-policy-value">
                            {{ $policyInfo['https_method'] ?? 'SNI' }}
                        </div>

                    </div>

                </div>


                <div class="mb-note">
                    Mode Squid:
                    <strong>
                        {{ $policyInfo['squid_mode'] ?? 'LOCAL' }}
                    </strong>.
                    HTTPS intercept port:
                    <strong>
                        {{ $policyInfo['https_port'] ?? 3130 }}
                    </strong>.
                    Halaman ini hanya menampilkan konfigurasi dan data
                    monitoring; perubahan blacklist dilakukan melalui admin.
                </div>

            </div>


            {{-- BLOCKED TREND --}}
            <div class="panel mb-chart-panel">

                <div class="panel-head">

                    <div>
                        <div class="panel-title">
                            ▥ Tren Request Terblokir
                        </div>
                    </div>

                    <select
                        class="tiny-select"
                        disabled>
                        <option>
                            24 Jam Terakhir
                        </option>
                    </select>

                </div>


                <div class="chart small">

                    <div class="chart-grid"></div>


                    <svg
                        viewBox="0 0 800 210"
                        preserveAspectRatio="none"
                        aria-label="Tren request terblokir">

                        @if($blockedTrendCollection->isNotEmpty())

                        <polygon
                            points="{{ $blockedPoints }} 800,210 0,210"
                            fill="#f04455"
                            opacity=".07"></polygon>

                        <polyline
                            points="{{ $blockedPoints }}"
                            fill="none"
                            stroke="#f04455"
                            stroke-width="4"
                            stroke-linecap="round"
                            stroke-linejoin="round"></polyline>

                        @endif

                    </svg>


                    <div class="axis">

                        @forelse($blockedTrendCollection as $index => $item)

                        @if($index % 4 === 0)

                        <span>
                            {{ $item['hour'] ?? '-' }}
                        </span>

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


                <div class="legend">

                    <span>
                        <i style="background:#f04455"></i>
                        Blocked
                    </span>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection