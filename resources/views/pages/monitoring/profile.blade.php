@extends('layouts.monitoring')

@section('title', 'Profil Pengembang')

@section('content')

<style>
    .monitoring-profile {
        --mp-border: #dce7f2;
        --mp-text: #17385f;
        --mp-muted: #78879b;
        --mp-primary: #1785f8;
        --mp-soft: #eef6ff;
    }

    .monitoring-profile * {
        box-sizing: border-box;
    }

    .mp-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .mp-profile-card {
        display: grid;
        grid-template-columns: 120px minmax(0, 1fr);
        gap: 18px;
        align-items: center;
    }

    .mp-avatar {
        width: 104px;
        height: 104px;
        border-radius: 26px;
        display: grid;
        place-items: center;
        background:
            linear-gradient(135deg,
                rgba(23, 133, 248, .14),
                rgba(122, 90, 248, .16)),
            #f8fbff;
        border: 1px solid #dbe8f6;
        color: #176fc7;
        font-size: 34px;
        font-weight: 900;
        letter-spacing: -2px;
    }

    .mp-identity h2 {
        margin: 10px 0 5px;
        color: #0d2b5f;
        font-size: 24px;
        line-height: 1.2;
    }

    .mp-identity p {
        margin: 0;
        color: #6f8095;
        font-size: 11px;
        line-height: 1.7;
    }

    .mp-meta {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 9px;
        margin-top: 16px;
    }

    .mp-meta span {
        padding: 11px;
        border-radius: 12px;
        border: 1px solid #e3ebf4;
        background: #f9fbfe;
        color: #66788f;
        font-size: 9px;
        line-height: 1.5;
    }

    .mp-meta b {
        display: block;
        margin-bottom: 3px;
        color: #24466e;
        font-size: 9px;
    }

    .mp-copy {
        margin: 13px 0 0;
        color: #536b88;
        font-size: 11px;
        line-height: 1.8;
    }

    .mp-feature-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: 14px;
    }

    .mp-feature {
        min-height: 94px;
        padding: 13px;
        border-radius: 13px;
        border: 1px solid #e3ebf4;
        background: #f9fbfe;
    }

    .mp-feature b {
        display: block;
        color: #183b66;
        font-size: 11px;
    }

    .mp-feature span {
        display: block;
        margin-top: 5px;
        color: #718096;
        font-size: 10px;
        line-height: 1.6;
    }

    .mp-kv {
        margin-top: 12px;
    }

    .mp-kv .kv-row {
        align-items: flex-start;
    }

    .mp-kv .kv-row b {
        max-width: 65%;
        text-align: right;
        line-height: 1.6;
    }

    .mp-research-title {
        margin-top: 12px;
        padding: 13px 14px;
        border-radius: 13px;
        border: 1px solid #e2ebf5;
        background: #f7faff;
        color: #335677;
        font-size: 10px;
        line-height: 1.7;
    }

    .mp-research-title b {
        display: block;
        margin-bottom: 5px;
        color: #173c69;
        font-size: 10px;
    }

    .mp-timeline {
        display: grid;
        gap: 12px;
        margin-top: 14px;
    }

    .mp-timeline-item {
        display: grid;
        grid-template-columns: 32px minmax(0, 1fr);
        gap: 11px;
        align-items: start;
    }

    .mp-timeline-number {
        width: 30px;
        height: 30px;
        border-radius: 9px;
        display: grid;
        place-items: center;
        background: #eaf4ff;
        color: #1785f8;
        font-size: 10px;
        font-weight: 900;
    }

    .mp-timeline-copy b {
        display: block;
        color: #1a3e68;
        font-size: 11px;
        margin-bottom: 3px;
    }

    .mp-timeline-copy span {
        display: block;
        color: #74849a;
        font-size: 10px;
        line-height: 1.65;
    }

    .mp-readonly {
        margin-top: 14px;
        padding: 12px 14px;
        border-radius: 13px;
        border: 1px solid #dce8f5;
        background: #f4f9ff;
        color: #58708e;
        font-size: 10px;
        line-height: 1.7;
    }

    @media (max-width: 900px) {
        .mp-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 650px) {
        .mp-profile-card {
            grid-template-columns: 1fr;
        }

        .mp-meta,
        .mp-feature-grid {
            grid-template-columns: 1fr;
        }

        .mp-kv .kv-row {
            display: grid;
            gap: 4px;
        }

        .mp-kv .kv-row b {
            max-width: 100%;
            text-align: left;
        }
    }
</style>


<div class="monitoring-profile">

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
                Profil Pengembang
                <br>

                <span class="accent">
                    {{ $system['name'] }}
                </span>
            </h1>

            <p>
                Kenali pengembang sistem, institusi, ruang lingkup penelitian,
                dan komponen utama dashboard monitoring jaringan ITBRP.
            </p>

            <div class="hero-chips">

                <span class="hero-chip">
                    <span class="chip-icon green">▣</span>
                    Squid Proxy
                </span>

                <span class="hero-chip">
                    <span class="chip-icon">◆</span>
                    MikroTik Multi-WAN
                </span>

                <span class="hero-chip">
                    <span class="chip-icon purple">▥</span>
                    Laravel Monitoring
                </span>

            </div>

        </div>


        <div class="hero-status">
            <span class="status-dot"></span>
            Read-only Profile
        </div>

    </section>


    {{-- ===================================================== --}}
    {{-- PROFILE GRID --}}
    {{-- ===================================================== --}}

    <div class="section-pad">

        <div class="mp-grid">

            {{-- PROFILE --}}
            <section class="panel">

                <div class="mp-profile-card">

                    <div class="mp-avatar">
                        DA
                    </div>


                    <div class="mp-identity">

                        <span class="badge blue">
                            PENGEMBANG SISTEM
                        </span>

                        <h2>
                            {{ $profile['name'] }}
                        </h2>

                        <p>
                            {{ $profile['role'] }}
                            dan pengembang
                            {{ $system['name'] }}.
                        </p>


                        <div class="mp-meta">

                            <span>
                                <b>Institusi</b>
                                {{ $profile['institution'] }}
                            </span>

                            <span>
                                <b>Program Studi</b>
                                {{ $profile['study_program'] }}
                            </span>

                            <span>
                                <b>Tahun</b>
                                {{ $profile['year'] }}
                            </span>

                        </div>

                    </div>

                </div>

            </section>


            {{-- ABOUT SYSTEM --}}
            <section class="panel">

                <div class="panel-title">
                    ▧ Tentang Sistem
                </div>

                <p class="mp-copy">
                    {{ $system['description'] }}
                </p>


                <div class="mp-feature-grid">

                    @foreach($system['features'] as $feature)

                    <div class="mp-feature">

                        <b>
                            {{ $feature['title'] }}
                        </b>

                        <span>
                            {{ $feature['description'] }}
                        </span>

                    </div>

                    @endforeach

                </div>

            </section>


            {{-- ACADEMIC --}}
            <section class="panel">

                <div class="panel-title">
                    ◎ Informasi Akademik
                </div>


                <div class="kv-list mp-kv">

                    <div class="kv-row">
                        <span>Nama Pengembang</span>

                        <b>
                            {{ $profile['name'] }}
                        </b>
                    </div>


                    <div class="kv-row">
                        <span>Kampus</span>

                        <b>
                            {{ $profile['institution'] }}
                        </b>
                    </div>


                    <div class="kv-row">
                        <span>Program Studi</span>

                        <b>
                            {{ $profile['study_program'] }}
                        </b>
                    </div>


                    <div class="kv-row">
                        <span>Proyek</span>

                        <b>
                            {{ $academic['project'] }}
                        </b>
                    </div>


                    <div class="kv-row">
                        <span>Fokus</span>

                        <b>
                            {{ $academic['focus'] }}
                        </b>
                    </div>

                </div>


                <div class="mp-research-title">

                    <b>
                        Judul Penelitian
                    </b>

                    {{ $academic['research_title'] }}

                </div>

            </section>


            {{-- CONTRIBUTION --}}
            <section class="panel">

                <div class="panel-title">
                    ◈ Kontribusi Pengembangan
                </div>


                <div class="mp-timeline">

                    @foreach($contributions as $item)

                    <div class="mp-timeline-item">

                        <div class="mp-timeline-number">
                            {{ $item['number'] }}
                        </div>


                        <div class="mp-timeline-copy">

                            <b>
                                {{ $item['title'] }}
                            </b>

                            <span>
                                {{ $item['description'] }}
                            </span>

                        </div>

                    </div>

                    @endforeach

                </div>


                <div class="mp-readonly">
                    Halaman ini hanya menampilkan informasi profil dan
                    ruang lingkup pengembangan sistem. Tidak terdapat
                    fungsi perubahan data pada mode monitoring.
                </div>

            </section>

        </div>

    </div>

</div>

@endsection