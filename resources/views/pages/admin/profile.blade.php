@extends('layouts.admin')

@section('title', 'Profile')

@section('content')

<style>
    .profile-page {
        --pf-border: #dbe6f2;
        --pf-text: #18345e;
        --pf-title: #0d2b5f;
        --pf-muted: #74839a;
        --pf-primary: #1785f8;
        --pf-soft: #eaf4ff;
        --pf-green: #12b76a;
        --pf-purple: #7a5af8;
    }

    .profile-page * {
        box-sizing: border-box;
    }

    .pf-grid {
        display: grid;
        grid-template-columns: 360px minmax(0, 1fr);
        gap: 14px;
        margin-top: 14px;
        align-items: start;
    }

    .pf-card {
        background: #fff;
        border: 1px solid var(--pf-border);
        border-radius: 18px;
        box-shadow: 0 8px 26px rgba(18, 42, 76, .045);
        padding: 18px;
    }

    .pf-profile {
        position: sticky;
        top: 92px;
    }

    .pf-avatar {
        width: 92px;
        height: 92px;
        margin-bottom: 15px;
        border-radius: 24px;
        display: grid;
        place-items: center;
        background:
            linear-gradient(135deg, rgba(23, 133, 248, .12), rgba(122, 90, 248, .16)),
            #f8fbff;
        border: 1px solid #dbe8f6;
        color: #176fc7;
        font-size: 30px;
        font-weight: 900;
        letter-spacing: -2px;
    }

    .pf-name {
        color: var(--pf-title);
        font-size: 22px;
        font-weight: 900;
        line-height: 1.25;
    }

    .pf-role {
        margin-top: 5px;
        color: var(--pf-muted);
        font-size: 12px;
        line-height: 1.6;
    }

    .pf-info {
        margin-top: 18px;
        border-top: 1px solid #edf2f7;
    }

    .pf-info-row {
        display: grid;
        grid-template-columns: 115px minmax(0, 1fr);
        gap: 10px;
        padding: 11px 0;
        border-bottom: 1px solid #edf2f7;
        font-size: 11px;
    }

    .pf-info-row span {
        color: #8190a4;
    }

    .pf-info-row strong {
        color: #274465;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .pf-stack {
        margin-top: 18px;
    }

    .pf-label {
        margin-bottom: 10px;
        color: #6d7d92;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .pf-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }

    .pf-chip {
        padding: 7px 10px;
        border-radius: 999px;
        border: 1px solid #dce8f5;
        background: #f7fbff;
        color: #31577d;
        font-size: 10px;
        font-weight: 800;
    }

    .pf-title-row {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 13px;
    }

    .pf-icon {
        width: 36px;
        height: 36px;
        flex: 0 0 auto;
        display: grid;
        place-items: center;
        border-radius: 11px;
        background: var(--pf-soft);
        color: var(--pf-primary);
        font-weight: 900;
    }

    .pf-section-title {
        color: var(--pf-title);
        font-size: 17px;
        font-weight: 900;
        line-height: 1.35;
    }

    .pf-section-sub {
        margin-top: 3px;
        color: var(--pf-muted);
        font-size: 10px;
        line-height: 1.5;
    }

    .pf-thesis {
        padding: 16px;
        border-radius: 15px;
        background: #f8fbff;
        border: 1px solid #e1ebf6;
        color: #173c69;
        font-size: 15px;
        font-weight: 800;
        line-height: 1.65;
    }

    .pf-description {
        margin-top: 14px;
        color: #536b88;
        font-size: 12px;
        line-height: 1.8;
    }

    .pf-components {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 11px;
        margin-top: 14px;
    }

    .pf-component {
        min-height: 125px;
        padding: 15px;
        border-radius: 15px;
        border: 1px solid #e2ebf5;
        background: #fbfdff;
    }

    .pf-component strong {
        display: block;
        margin-bottom: 7px;
        color: #183b66;
        font-size: 13px;
    }

    .pf-component p {
        margin: 0;
        color: #718096;
        font-size: 11px;
        line-height: 1.7;
    }

    .pf-feature-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 9px;
        margin-top: 14px;
    }

    .pf-feature {
        display: flex;
        align-items: center;
        gap: 9px;
        min-height: 45px;
        padding: 10px 12px;
        border-radius: 12px;
        background: #f9fbfe;
        border: 1px solid #e5edf6;
        color: #355677;
        font-size: 11px;
        font-weight: 700;
    }

    .pf-feature-mark {
        width: 22px;
        height: 22px;
        flex: 0 0 auto;
        border-radius: 7px;
        display: grid;
        place-items: center;
        background: #eaf8f1;
        color: var(--pf-green);
        font-size: 11px;
        font-weight: 900;
    }

    .pf-flow {
        display: grid;
        grid-template-columns: repeat(7, auto);
        gap: 8px;
        align-items: center;
        justify-content: start;
        margin-top: 14px;
        overflow-x: auto;
        padding-bottom: 4px;
    }

    .pf-flow-box {
        min-width: 118px;
        padding: 12px 10px;
        border-radius: 12px;
        border: 1px solid #dfe9f4;
        background: #f9fbfe;
        color: #24496f;
        font-size: 10px;
        font-weight: 800;
        text-align: center;
        white-space: nowrap;
    }

    .pf-flow-arrow {
        color: #8ba0b8;
        font-weight: 900;
    }

    @media (max-width: 1000px) {
        .pf-grid {
            grid-template-columns: 1fr;
        }

        .pf-profile {
            position: static;
        }
    }

    @media (max-width: 700px) {

        .pf-components,
        .pf-feature-grid {
            grid-template-columns: 1fr;
        }

        .pf-info-row {
            grid-template-columns: 1fr;
            gap: 4px;
        }

        .pf-info-row strong {
            text-align: left;
        }

        .pf-card {
            padding: 14px;
        }
    }
</style>

<div class="profile-page">

    <section class="hero">
        <div class="hero-bg"></div>

        <div class="hero-copy">
            <div class="eyebrow">
                ITBRP NETWORK MONITORING
            </div>

            <h1>
                Profile Penelitian
            </h1>

            <p>
                Informasi peneliti, fokus penelitian, teknologi,
                dan komponen utama sistem monitoring jaringan.
            </p>

            <div class="hero-chips">
                <span class="hero-chip">
                    <span class="chip-icon green">●</span>
                    Squid Proxy
                </span>

                <span class="hero-chip">
                    <span class="chip-icon">◆</span>
                    Multi-WAN
                </span>

                <span class="hero-chip">
                    <span class="chip-icon purple">▥</span>
                    Laravel Monitoring
                </span>
            </div>
        </div>

        <div class="hero-status">
            <span class="status-dot"></span>
            Penelitian 2026
        </div>

        <div class="hero-clock">
            <small>
                Institut Teknologi dan Bisnis
            </small>

            <b style="font-size:18px">
                Riau Pesisir
            </b>

            <span>ITBRP</span>
        </div>
    </section>


    <div class="pf-grid">

        {{-- LEFT PROFILE --}}
        <aside class="pf-card pf-profile">

            <div class="pf-avatar">
                DA
            </div>

            <div class="pf-name">
                {{ $profile['name'] }}
            </div>

            <div class="pf-role">
                {{ $profile['role'] }}
            </div>

            <div class="pf-info">

                <div class="pf-info-row">
                    <span>Program Studi</span>
                    <strong>
                        {{ $profile['study_program'] }}
                    </strong>
                </div>

                <div class="pf-info-row">
                    <span>Institusi</span>
                    <strong>
                        {{ $profile['institution'] }}
                    </strong>
                </div>

                <div class="pf-info-row">
                    <span>Tahun</span>
                    <strong>
                        {{ $profile['year'] }}
                    </strong>
                </div>

                <div class="pf-info-row">
                    <span>Fokus</span>
                    <strong>
                        Network Monitoring
                    </strong>
                </div>

            </div>

            <div class="pf-stack">

                <div class="pf-label">
                    Fokus Teknologi
                </div>

                <div class="pf-chips">
                    @foreach($research['focus'] as $focus)
                    <span class="pf-chip">
                        {{ $focus }}
                    </span>
                    @endforeach
                </div>

            </div>

        </aside>


        {{-- RIGHT CONTENT --}}
        <div style="display:grid;gap:14px">

            {{-- RESEARCH --}}
            <section class="pf-card">

                <div class="pf-title-row">

                    <div class="pf-icon">
                        R
                    </div>

                    <div>
                        <div class="pf-section-title">
                            Penelitian
                        </div>

                        <div class="pf-section-sub">
                            Judul dan ruang lingkup penelitian
                        </div>
                    </div>

                </div>

                <div class="pf-thesis">
                    {{ $research['title'] }}
                </div>

                <div class="pf-description">
                    {{ $research['description'] }}
                </div>

            </section>


            {{-- COMPONENTS --}}
            <section class="pf-card">

                <div class="pf-title-row">

                    <div class="pf-icon">
                        ▦
                    </div>

                    <div>
                        <div class="pf-section-title">
                            Komponen Sistem
                        </div>

                        <div class="pf-section-sub">
                            Teknologi utama yang membentuk sistem
                        </div>
                    </div>

                </div>

                <div class="pf-components">

                    @foreach($research['components'] as $component)

                    <div class="pf-component">

                        <strong>
                            {{ $component['name'] }}
                        </strong>

                        <p>
                            {{ $component['description'] }}
                        </p>

                    </div>

                    @endforeach

                </div>

            </section>


            {{-- FEATURES --}}
            <section class="pf-card">

                <div class="pf-title-row">

                    <div class="pf-icon">
                        ✓
                    </div>

                    <div>
                        <div class="pf-section-title">
                            Fitur Utama
                        </div>

                        <div class="pf-section-sub">
                            Kapabilitas yang dibangun dalam penelitian
                        </div>
                    </div>

                </div>

                <div class="pf-feature-grid">

                    @foreach($research['features'] as $feature)

                    <div class="pf-feature">

                        <span class="pf-feature-mark">
                            ✓
                        </span>

                        <span>
                            {{ $feature }}
                        </span>

                    </div>

                    @endforeach

                </div>

            </section>


            {{-- ARCHITECTURE --}}
            <section class="pf-card">

                <div class="pf-title-row">

                    <div class="pf-icon">
                        ↔
                    </div>

                    <div>
                        <div class="pf-section-title">
                            Alur Arsitektur
                        </div>

                        <div class="pf-section-sub">
                            Gambaran sederhana integrasi jaringan
                        </div>
                    </div>

                </div>

                <div class="pf-flow">

                    <div class="pf-flow-box">
                        Client Kampus
                    </div>

                    <div class="pf-flow-arrow">
                        →
                    </div>

                    <div class="pf-flow-box">
                        MikroTik
                    </div>

                    <div class="pf-flow-arrow">
                        →
                    </div>

                    <div class="pf-flow-box">
                        Squid Proxy
                    </div>

                    <div class="pf-flow-arrow">
                        →
                    </div>

                    <div class="pf-flow-box">
                        Internet
                    </div>

                </div>

                <div class="pf-description">
                    Laravel digunakan sebagai dashboard untuk membaca dan
                    mengelola data monitoring, blacklist, cache, perangkat,
                    statistik, serta status integrasi Squid dan MikroTik.
                </div>

            </section>

        </div>

    </div>

</div>

@endsection