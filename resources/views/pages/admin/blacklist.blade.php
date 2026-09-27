@extends('layouts.admin')

@section('title', 'Filter & Blacklist')

@section('content')

@php
    $now = now('Asia/Jakarta');

    $total = (int) ($summary['total'] ?? 0);
    $active = (int) ($summary['active'] ?? 0);
    $inactive = (int) ($summary['inactive'] ?? 0);
    $synced = (int) ($summary['synced'] ?? 0);
    $pending = (int) ($summary['pending'] ?? 0);
    $errorCount = (int) ($summary['error'] ?? 0);

    $squidMode = strtoupper(
        $controlStatus['mode'] ?? 'LOCAL'
    );

    $fileLines = collect(
        $currentFile['lines'] ?? []
    );
@endphp

<style>
    .blacklist-page {
        --bl-border: #dbe6f2;
        --bl-muted: #71809a;
        --bl-text: #17305d;
        --bl-primary: #1785f8;
        --bl-primary-soft: #eaf4ff;
        --bl-card: #ffffff;
        --bl-bg: #f7faff;
        --bl-danger: #ef476f;
        --bl-success: #12b76a;
        --bl-warning: #f5a524;
    }

    .blacklist-page * {
        box-sizing: border-box;
    }

    .bl-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(320px, .65fr);
        gap: 14px;
        align-items: stretch;
    }

    .bl-card {
        background: var(--bl-card);
        border: 1px solid var(--bl-border);
        border-radius: 18px;
        box-shadow: 0 8px 26px rgba(18, 42, 76, .045);
        padding: 18px;
    }

    .bl-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    .bl-card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 17px;
        font-weight: 800;
        color: #0d2b5f;
    }

    .bl-title-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        background: var(--bl-primary-soft);
        color: var(--bl-primary);
        font-weight: 900;
        flex: 0 0 auto;
    }

    .bl-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .bl-field {
        display: grid;
        gap: 7px;
    }

    .bl-field.full {
        grid-column: 1 / -1;
    }

    .bl-label {
        color: #263c63;
        font-size: 13px;
        font-weight: 700;
    }

    .bl-hint {
        font-size: 11px;
        color: #8a98ad;
        margin-top: -2px;
    }

    .bl-input,
    .bl-select,
    .bl-textarea {
        width: 100%;
        border: 1px solid #d7e2ee;
        background: #f9fbfe;
        color: #18345f;
        border-radius: 12px;
        outline: none;
        font: inherit;
        transition: .18s ease;
    }

    .bl-input,
    .bl-select {
        height: 44px;
        padding: 0 13px;
    }

    .bl-textarea {
        min-height: 92px;
        padding: 12px 13px;
        resize: vertical;
    }

    .bl-input::placeholder,
    .bl-textarea::placeholder {
        color: #9ca9ba;
    }

    .bl-input:focus,
    .bl-select:focus,
    .bl-textarea:focus {
        background: #fff;
        border-color: #78b9ff;
        box-shadow: 0 0 0 4px rgba(30, 134, 250, .10);
    }

    .bl-options {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 2px;
    }

    .bl-check {
        min-height: 42px;
        padding: 0 12px;
        display: inline-flex;
        align-items: center;
        gap: 9px;
        border: 1px solid #dce7f2;
        border-radius: 12px;
        background: #f8fbff;
        color: #2d456d;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        user-select: none;
    }

    .bl-check input {
        width: 16px;
        height: 16px;
        accent-color: var(--bl-primary);
    }

    .bl-btn {
        min-height: 40px;
        border: 0;
        border-radius: 11px;
        padding: 0 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        text-decoration: none;
        transition: .18s ease;
        white-space: nowrap;
    }

    .bl-btn:hover {
        transform: translateY(-1px);
    }

    .bl-btn-primary {
        background: linear-gradient(135deg, #1e86fa, #4da5ff);
        color: #fff;
        box-shadow: 0 8px 18px rgba(30, 134, 250, .18);
    }

    .bl-btn-soft {
        background: #eaf4ff;
        color: #0c76de;
    }

    .bl-btn-danger {
        background: #fff0f3;
        color: #e7375e;
    }

    .bl-btn-neutral {
        background: #f1f5f9;
        color: #52627a;
    }

    .bl-submit-row {
        display: flex;
        justify-content: flex-end;
        margin-top: 2px;
    }

    .bl-status-list {
        display: grid;
        gap: 0;
    }

    .bl-status-row {
        display: grid;
        grid-template-columns: 135px minmax(0, 1fr);
        align-items: start;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #edf2f7;
        font-size: 12px;
    }

    .bl-status-row:last-child {
        border-bottom: 0;
    }

    .bl-status-row span {
        color: #71809a;
    }

    .bl-status-row strong {
        color: #19355f;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .bl-mode-note {
        margin-top: 14px;
        border-radius: 13px;
        padding: 13px 14px;
        background: #f3f8fe;
        color: #385376;
        font-size: 12px;
        line-height: 1.7;
        border: 1px solid #e4edf7;
    }

    .bl-table-card {
        margin-top: 14px;
    }

    .bl-filterbar {
        display: grid;
        grid-template-columns: minmax(260px, 1fr) 150px 150px auto auto;
        gap: 9px;
        align-items: center;
        padding: 11px;
        border: 1px solid #e1eaf4;
        border-radius: 14px;
        background: #f8fbff;
        margin-bottom: 14px;
    }

    .bl-searchbox {
        position: relative;
    }

    .bl-searchbox .bl-input {
        padding-left: 39px;
        background: #fff;
    }

    .bl-search-icon {
        position: absolute;
        left: 13px;
        top: 50%;
        transform: translateY(-50%);
        color: #8090a7;
        pointer-events: none;
        font-size: 15px;
    }

    .bl-table-wrap {
        overflow-x: auto;
        border: 1px solid #e4ecf5;
        border-radius: 14px;
    }

    .bl-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 920px;
    }

    .bl-table th {
        background: #f2f7fd;
        color: #49617f;
        padding: 11px 12px;
        font-size: 11px;
        font-weight: 800;
        text-align: left;
        border-bottom: 1px solid #dde8f3;
    }

    .bl-table td {
        padding: 12px;
        color: #26415f;
        font-size: 11px;
        border-bottom: 1px solid #edf2f7;
        vertical-align: middle;
    }

    .bl-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .bl-table tbody tr:hover td {
        background: #fbfdff;
    }

    .bl-domain {
        font-weight: 800;
        color: #153765;
    }

    .bl-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
    }

    .bl-actions .bl-btn {
        min-height: 34px;
        padding: 0 10px;
        font-size: 10px;
        border-radius: 9px;
    }

    .bl-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 22px;
    }

    .bl-modal.is-open {
        display: flex;
    }

    .bl-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(10, 25, 50, .48);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }

    .bl-modal-dialog {
        position: relative;
        z-index: 1;
        width: min(560px, 100%);
        max-height: calc(100vh - 44px);
        overflow: auto;
        background: #fff;
        border: 1px solid #dce6f1;
        border-radius: 20px;
        box-shadow: 0 28px 80px rgba(10, 31, 66, .24);
        animation: blModalIn .18s ease-out;
    }

    .bl-modal-dialog.delete {
        width: min(440px, 100%);
    }

    @keyframes blModalIn {
        from {
            opacity: 0;
            transform: translateY(10px) scale(.985);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .bl-modal-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 20px;
        border-bottom: 1px solid #e8eef5;
    }

    .bl-modal-title-wrap {
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }

    .bl-modal-title-wrap .bl-title-icon {
        margin-top: 1px;
    }

    .bl-modal-title {
        color: #0d2b5f;
        font-size: 17px;
        font-weight: 800;
        line-height: 1.3;
    }

    .bl-modal-subtitle {
        margin-top: 4px;
        color: #7b899d;
        font-size: 11px;
        line-height: 1.5;
    }

    .bl-modal-close {
        width: 34px;
        height: 34px;
        border: 0;
        border-radius: 10px;
        display: grid;
        place-items: center;
        cursor: pointer;
        background: #f3f6fa;
        color: #5f7088;
        font-size: 18px;
        line-height: 1;
        flex: 0 0 auto;
    }

    .bl-modal-close:hover {
        background: #e9eff6;
    }

    .bl-modal-body {
        padding: 20px;
    }

    .bl-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 9px;
        padding: 15px 20px 20px;
    }

    .bl-delete-warning {
        display: flex;
        align-items: flex-start;
        gap: 13px;
        padding: 15px;
        border: 1px solid #ffd8e0;
        border-radius: 14px;
        background: #fff6f8;
    }

    .bl-delete-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        background: #ffe7ec;
        color: #e7375e;
        font-weight: 900;
        font-size: 18px;
    }

    .bl-delete-warning b {
        display: block;
        color: #7d1d36;
        margin-bottom: 4px;
    }

    .bl-delete-warning p {
        margin: 0;
        color: #7d5663;
        font-size: 12px;
        line-height: 1.65;
    }

    body.bl-modal-open {
        overflow: hidden;
    }

    .bl-code-card {
        margin-top: 14px;
    }

    .bl-code {
        background: #0f1b2f;
        color: #d8e4f4;
        border-radius: 14px;
        padding: 16px;
        min-height: 120px;
        max-height: 300px;
        overflow: auto;
        font: 12px/1.8 Consolas, Monaco, monospace;
    }

    .bl-alert {
        margin-bottom: 12px;
        padding: 13px 15px;
        border-radius: 13px;
        background: #fff;
        border: 1px solid #e1eaf4;
        font-size: 12px;
        line-height: 1.6;
    }

    .bl-alert.success {
        border-left: 4px solid var(--bl-success);
    }

    .bl-alert.warning {
        border-left: 4px solid var(--bl-warning);
    }

    .bl-alert.error {
        border-left: 4px solid var(--bl-danger);
    }

    @media (max-width: 1100px) {
        .bl-grid {
            grid-template-columns: 1fr;
        }

        .bl-filterbar {
            grid-template-columns: 1fr 1fr 1fr;
        }

        .bl-searchbox {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 700px) {
        .bl-form-grid,
        .bl-filterbar {
            grid-template-columns: 1fr;
        }

        .bl-field.full,
        .bl-searchbox {
            grid-column: auto;
        }

        .bl-options {
            display: grid;
        }

        .bl-check,
        .bl-btn {
            width: 100%;
        }

        .bl-submit-row {
            display: block;
        }

        .bl-card {
            padding: 14px;
        }

        .bl-modal {
            padding: 12px;
        }

        .bl-modal-dialog {
            border-radius: 17px;
            max-height: calc(100vh - 24px);
        }

        .bl-modal-head,
        .bl-modal-body {
            padding: 16px;
        }

        .bl-modal-footer {
            padding: 12px 16px 16px;
            display: grid;
            grid-template-columns: 1fr;
        }

        .bl-modal-footer .bl-btn {
            width: 100%;
        }
    }
</style>

<div class="blacklist-page">

    {{-- HERO --}}
    <section class="hero">
        <div class="hero-bg"></div>

        <div class="hero-copy">
            <div class="eyebrow">ITBRP NETWORK MONITORING</div>

            <h1>Filter & Blacklist</h1>

            <p>
                Kelola domain yang dibatasi melalui Laravel dan
                sinkronkan aturan blacklist ke konfigurasi Squid.
            </p>

            <div class="hero-chips">
                <span class="hero-chip">
                    <span class="chip-icon green">●</span>
                    Database Terintegrasi
                </span>

                <span class="hero-chip">
                    <span class="chip-icon">◆</span>
                    Squid Blacklist
                </span>

                <span class="hero-chip">
                    <span class="chip-icon purple">▥</span>
                    Mode {{ $squidMode }}
                </span>
            </div>
        </div>

        <div class="hero-status">
            <span class="status-dot"></span>

            @if(($controlStatus['mode'] ?? 'local') === 'production')
                Terhubung Production
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


    {{-- ALERTS --}}
    @if(session('success'))
        <div class="bl-alert success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="bl-alert warning">
            {{ session('warning') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bl-alert error">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bl-alert error">
            <strong>Data belum dapat disimpan.</strong>

            <div style="margin-top:6px">
                @foreach($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif


    {{-- SUMMARY --}}
    <div class="stats">

        <div class="stat-card blue">
            <div class="stat-top">
                <div class="stat-icon">▧</div>
                <div>
                    <div class="stat-label">Total Domain</div>
                    <div class="stat-value">{{ number_format($total) }}</div>
                </div>
            </div>
            <div class="stat-foot">Semua rule blacklist</div>
        </div>

        <div class="stat-card green">
            <div class="stat-top">
                <div class="stat-icon">✓</div>
                <div>
                    <div class="stat-label">Aktif</div>
                    <div class="stat-value">{{ number_format($active) }}</div>
                </div>
            </div>
            <div class="stat-foot">Masuk blacklist.txt</div>
        </div>

        <div class="stat-card orange">
            <div class="stat-top">
                <div class="stat-icon">◴</div>
                <div>
                    <div class="stat-label">Nonaktif</div>
                    <div class="stat-value">{{ number_format($inactive) }}</div>
                </div>
            </div>
            <div class="stat-foot">Tidak diterapkan</div>
        </div>

        <div class="stat-card purple">
            <div class="stat-top">
                <div class="stat-icon">◆</div>
                <div>
                    <div class="stat-label">Synced</div>
                    <div class="stat-value">{{ number_format($synced) }}</div>
                </div>
            </div>
            <div class="stat-foot">File telah disinkronkan</div>
        </div>

        <div class="stat-card red">
            <div class="stat-top">
                <div class="stat-icon">!</div>
                <div>
                    <div class="stat-label">Pending / Error</div>
                    <div class="stat-value">
                        {{ number_format($pending + $errorCount) }}
                    </div>
                </div>
            </div>
            <div class="stat-foot">Perlu diperiksa</div>
        </div>

    </div>


    {{-- FORM + STATUS --}}
    <div class="bl-grid">

        <div class="bl-card">

            <div class="bl-card-head">
                <div class="bl-card-title">
                    <span class="bl-title-icon">＋</span>
                    <span>Tambah Domain Blacklist</span>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('admin.blacklist.store') }}"
            >
                @csrf

                <div class="bl-form-grid">

                    <div class="bl-field">
                        <label class="bl-label">Domain</label>

                        <input
                            type="text"
                            name="domain"
                            value="{{ old('domain') }}"
                            class="bl-input"
                            placeholder="facebook.com"
                            autocomplete="off"
                            required
                        >

                        <div class="bl-hint">
                            Masukkan hostname saja, tanpa http:// atau path.
                        </div>
                    </div>


                    <div class="bl-field">
                        <label class="bl-label">Kategori</label>

                        <input
                            type="text"
                            name="category"
                            value="{{ old('category') }}"
                            class="bl-input"
                            placeholder="social-media"
                            list="blacklistCategoryOptions"
                        >

                        <datalist id="blacklistCategoryOptions">
                            <option value="social-media">
                            <option value="streaming">
                            <option value="entertainment">
                            <option value="advertising">
                            <option value="other">
                        </datalist>

                        <div class="bl-hint">
                            Opsional, untuk pengelompokan domain.
                        </div>
                    </div>


                    <div class="bl-field full">
                        <label class="bl-label">Catatan</label>

                        <textarea
                            name="notes"
                            class="bl-textarea"
                            placeholder="Contoh: dibatasi untuk kebijakan akses jaringan kampus..."
                        >{{ old('notes') }}</textarea>
                    </div>


                    <div class="bl-field full">
                        <div class="bl-options">

                            <label class="bl-check">
                                <input
                                    type="checkbox"
                                    name="include_subdomains"
                                    value="1"
                                    checked
                                >
                                Sertakan subdomain
                            </label>

                            <label class="bl-check">
                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    checked
                                >
                                Aktifkan blacklist
                            </label>

                        </div>
                    </div>


                    <div class="bl-field full">
                        <div class="bl-submit-row">
                            <button
                                type="submit"
                                class="bl-btn bl-btn-primary"
                            >
                                ＋ Tambah Domain
                            </button>
                        </div>
                    </div>

                </div>
            </form>

        </div>


        {{-- SQUID STATUS --}}
        <div class="bl-card">

            <div class="bl-card-head">

                <div class="bl-card-title">
                    <span class="bl-title-icon">⌁</span>
                    <span>Status Integrasi Squid</span>
                </div>

                <form
                    method="POST"
                    action="{{ route('admin.blacklist.sync') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="bl-btn bl-btn-soft"
                    >
                        ↻ Sinkronkan
                    </button>
                </form>

            </div>


            <div class="bl-status-list">

                <div class="bl-status-row">
                    <span>Mode</span>
                    <strong>{{ $squidMode }}</strong>
                </div>

                <div class="bl-status-row">
                    <span>Squid Host</span>
                    <strong>{{ $controlStatus['host'] ?? '-' }}</strong>
                </div>

                <div class="bl-status-row">
                    <span>Blacklist File</span>
                    <strong>
                        {{ $controlStatus['blacklist_file'] ?? '-' }}
                    </strong>
                </div>

                <div class="bl-status-row">
                    <span>File tersedia</span>
                    <strong>
                        {{ ($currentFile['exists'] ?? false) ? 'Ya' : 'Tidak' }}
                    </strong>
                </div>

                <div class="bl-status-row">
                    <span>Baris aktif</span>
                    <strong>{{ $fileLines->count() }}</strong>
                </div>

                <div class="bl-status-row">
                    <span>Reconfigure</span>
                    <strong>
                        {{
                            ($controlStatus['reconfigure_enabled'] ?? false)
                                ? 'ENABLED'
                                : 'DISABLED'
                        }}
                    </strong>
                </div>

            </div>


            <div class="bl-mode-note">
                @if(($controlStatus['mode'] ?? 'local') === 'local')
                    <strong>Mode LOCAL.</strong>
                    Perubahan saat ini menulis file blacklist lokal.
                    Perintah Squid production tidak dieksekusi.
                @else
                    <strong>Mode PRODUCTION.</strong>
                    Konfigurasi akan divalidasi lebih dahulu sebelum
                    Squid direconfigure.
                @endif
            </div>

        </div>

    </div>


    {{-- TABLE --}}
    <div class="bl-card bl-table-card">

        <div class="bl-card-head">
            <div class="bl-card-title">
                <span class="bl-title-icon">▧</span>
                <span>Daftar Domain Blacklist</span>
            </div>

            <span class="badge blue">
                {{ number_format($total) }} domain
            </span>
        </div>


        <form
            method="GET"
            action="{{ route('admin.blacklist.index') }}"
            class="bl-filterbar"
        >

            <div class="bl-searchbox">
                <span class="bl-search-icon">⌕</span>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    class="bl-input"
                    placeholder="Cari domain, kategori, atau catatan..."
                >
            </div>


            <select
                name="status"
                class="bl-select"
            >
                <option value="">Semua Status</option>

                <option
                    value="active"
                    @selected(request('status') === 'active')
                >
                    Aktif
                </option>

                <option
                    value="inactive"
                    @selected(request('status') === 'inactive')
                >
                    Nonaktif
                </option>
            </select>


            <select
                name="sync_status"
                class="bl-select"
            >
                <option value="">Semua Sync</option>

                <option
                    value="synced"
                    @selected(request('sync_status') === 'synced')
                >
                    Synced
                </option>

                <option
                    value="pending"
                    @selected(request('sync_status') === 'pending')
                >
                    Pending
                </option>

                <option
                    value="error"
                    @selected(request('sync_status') === 'error')
                >
                    Error
                </option>
            </select>


            <button
                type="submit"
                class="bl-btn bl-btn-primary"
            >
                ⌕ Filter
            </button>


            <a
                href="{{ route('admin.blacklist.index') }}"
                class="bl-btn bl-btn-neutral"
            >
                Reset
            </a>

        </form>


        <div class="bl-table-wrap">

            <table class="bl-table">

                <thead>
                    <tr>
                        <th>Domain</th>
                        <th>Kategori</th>
                        <th>Subdomain</th>
                        <th>Status</th>
                        <th>Sinkronisasi</th>
                        <th>Sumber</th>
                        <th>Terakhir Sync</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($domains as $domain)

                        <tr>

                            <td>
                                <span class="bl-domain">
                                    {{ $domain->domain }}
                                </span>
                            </td>

                            <td>
                                {{ $domain->category ?? '-' }}
                            </td>

                            <td>
                                @if($domain->include_subdomains)
                                    <span class="badge blue">Ya</span>
                                @else
                                    <span class="badge">Tidak</span>
                                @endif
                            </td>

                            <td>
                                @if($domain->is_active)
                                    <span class="badge ok">Aktif</span>
                                @else
                                    <span class="badge orange">Nonaktif</span>
                                @endif
                            </td>

                            <td>
                                @if($domain->sync_status === 'synced')
                                    <span class="badge ok">Synced</span>
                                @elseif($domain->sync_status === 'error')
                                    <span class="badge bad">Error</span>
                                @else
                                    <span class="badge orange">Pending</span>
                                @endif
                            </td>

                            <td>
                                {{ $domain->source ?? '-' }}
                            </td>

                            <td>
                                {{
                                    $domain->synced_at
                                        ?->format('d/m/Y H:i:s')
                                    ?? '-'
                                }}
                            </td>

                            <td>
                                <div class="bl-actions">

                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'admin.blacklist.toggle',
                                            $domain
                                        ) }}"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="bl-btn bl-btn-soft"
                                        >
                                            @if($domain->is_active)
                                                Nonaktifkan
                                            @else
                                                Aktifkan
                                            @endif
                                        </button>
                                    </form>


                                    <button
                                        type="button"
                                        class="bl-btn bl-btn-neutral"
                                        data-bl-modal-open="editBlacklistModal{{ $domain->id }}"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        class="bl-btn bl-btn-danger"
                                        data-bl-modal-open="deleteBlacklistModal{{ $domain->id }}"
                                    >
                                        Hapus
                                    </button>

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="8"
                                style="
                                    text-align:center;
                                    padding:34px;
                                    color:#7d8ba0;
                                "
                            >
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


    {{-- EDIT + DELETE MODALS --}}
    @foreach($domains as $domain)

        {{-- EDIT MODAL --}}
        <div
            class="bl-modal"
            id="editBlacklistModal{{ $domain->id }}"
            aria-hidden="true"
        >
            <div
                class="bl-modal-backdrop"
                data-bl-modal-close
            ></div>

            <div
                class="bl-modal-dialog"
                role="dialog"
                aria-modal="true"
                aria-labelledby="editBlacklistTitle{{ $domain->id }}"
            >

                <div class="bl-modal-head">

                    <div class="bl-modal-title-wrap">

                        <span class="bl-title-icon">✎</span>

                        <div>
                            <div
                                class="bl-modal-title"
                                id="editBlacklistTitle{{ $domain->id }}"
                            >
                                Edit Domain Blacklist
                            </div>

                            <div class="bl-modal-subtitle">
                                Perbarui domain, kategori, catatan,
                                dan status blacklist.
                            </div>
                        </div>

                    </div>

                    <button
                        type="button"
                        class="bl-modal-close"
                        data-bl-modal-close
                        aria-label="Tutup modal"
                    >
                        ×
                    </button>

                </div>


                <form
                    method="POST"
                    action="{{ route(
                        'admin.blacklist.update',
                        $domain
                    ) }}"
                >
                    @csrf
                    @method('PUT')

                    <div class="bl-modal-body">

                        <div class="bl-form-grid">

                            <div class="bl-field">
                                <label class="bl-label">
                                    Domain
                                </label>

                                <input
                                    type="text"
                                    name="domain"
                                    value="{{ $domain->domain }}"
                                    class="bl-input"
                                    autocomplete="off"
                                    required
                                >

                                <div class="bl-hint">
                                    Gunakan hostname saja, contoh facebook.com.
                                </div>
                            </div>


                            <div class="bl-field">
                                <label class="bl-label">
                                    Kategori
                                </label>

                                <input
                                    type="text"
                                    name="category"
                                    value="{{ $domain->category }}"
                                    class="bl-input"
                                    placeholder="social-media"
                                    list="blacklistCategoryOptions"
                                >

                                <div class="bl-hint">
                                    Opsional untuk pengelompokan domain.
                                </div>
                            </div>


                            <div class="bl-field full">
                                <label class="bl-label">
                                    Catatan
                                </label>

                                <textarea
                                    name="notes"
                                    class="bl-textarea"
                                    placeholder="Catatan domain..."
                                >{{ $domain->notes }}</textarea>
                            </div>


                            <div class="bl-field full">

                                <div class="bl-options">

                                    <label class="bl-check">
                                        <input
                                            type="checkbox"
                                            name="include_subdomains"
                                            value="1"
                                            @checked($domain->include_subdomains)
                                        >
                                        Sertakan subdomain
                                    </label>

                                    <label class="bl-check">
                                        <input
                                            type="checkbox"
                                            name="is_active"
                                            value="1"
                                            @checked($domain->is_active)
                                        >
                                        Blacklist aktif
                                    </label>

                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="bl-modal-footer">

                        <button
                            type="button"
                            class="bl-btn bl-btn-neutral"
                            data-bl-modal-close
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            class="bl-btn bl-btn-primary"
                        >
                            Simpan Perubahan
                        </button>

                    </div>

                </form>

            </div>
        </div>


        {{-- DELETE MODAL --}}
        <div
            class="bl-modal"
            id="deleteBlacklistModal{{ $domain->id }}"
            aria-hidden="true"
        >
            <div
                class="bl-modal-backdrop"
                data-bl-modal-close
            ></div>

            <div
                class="bl-modal-dialog delete"
                role="dialog"
                aria-modal="true"
                aria-labelledby="deleteBlacklistTitle{{ $domain->id }}"
            >

                <div class="bl-modal-head">

                    <div class="bl-modal-title-wrap">

                        <span
                            class="bl-title-icon"
                            style="
                                background:#fff0f3;
                                color:#e7375e;
                            "
                        >
                            !
                        </span>

                        <div>
                            <div
                                class="bl-modal-title"
                                id="deleteBlacklistTitle{{ $domain->id }}"
                            >
                                Hapus Domain Blacklist
                            </div>

                            <div class="bl-modal-subtitle">
                                Tindakan ini akan menghapus domain dari database
                                lalu menyinkronkan ulang blacklist.txt.
                            </div>
                        </div>

                    </div>

                    <button
                        type="button"
                        class="bl-modal-close"
                        data-bl-modal-close
                        aria-label="Tutup modal"
                    >
                        ×
                    </button>

                </div>


                <div class="bl-modal-body">

                    <div class="bl-delete-warning">

                        <div class="bl-delete-icon">
                            !
                        </div>

                        <div>
                            <b>
                                Yakin ingin menghapus
                                {{ $domain->domain }}?
                            </b>

                            <p>
                                Domain ini akan dihapus dari daftar blacklist.
                                File blacklist akan disinkronkan kembali setelah
                                proses penghapusan berhasil.
                            </p>
                        </div>

                    </div>

                </div>


                <form
                    method="POST"
                    action="{{ route(
                        'admin.blacklist.destroy',
                        $domain
                    ) }}"
                >
                    @csrf
                    @method('DELETE')

                    <div class="bl-modal-footer">

                        <button
                            type="button"
                            class="bl-btn bl-btn-neutral"
                            data-bl-modal-close
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            class="bl-btn bl-btn-danger"
                        >
                            Ya, Hapus Domain
                        </button>

                    </div>

                </form>

            </div>
        </div>

    @endforeach


    {{-- FILE PREVIEW --}}
    <div class="bl-card bl-code-card">

        <div class="bl-card-head">

            <div class="bl-card-title">
                <span class="bl-title-icon">◫</span>
                <span>Preview blacklist.txt</span>
            </div>

            <span class="badge blue">
                {{ $fileLines->count() }} baris
            </span>

        </div>


        <div class="bl-code">

            @forelse($fileLines->take(50) as $line)
                <div>{{ $line }}</div>
            @empty
                <div># blacklist.txt masih kosong</div>
            @endforelse

        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    function openModal(modal) {
        if (!modal) {
            return;
        }

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('bl-modal-open');

        const firstInput = modal.querySelector(
            'input:not([type="hidden"]), textarea, select'
        );

        if (firstInput) {
            window.setTimeout(function () {
                firstInput.focus();
            }, 80);
        }
    }

    function closeModal(modal) {
        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');

        if (!document.querySelector('.bl-modal.is-open')) {
            document.body.classList.remove('bl-modal-open');
        }
    }

    document.addEventListener('click', function (event) {

        const openButton = event.target.closest(
            '[data-bl-modal-open]'
        );

        if (openButton) {
            const modalId = openButton.getAttribute(
                'data-bl-modal-open'
            );

            openModal(
                document.getElementById(modalId)
            );

            return;
        }

        const closeButton = event.target.closest(
            '[data-bl-modal-close]'
        );

        if (closeButton) {
            closeModal(
                closeButton.closest('.bl-modal')
            );
        }
    });

    document.addEventListener('keydown', function (event) {

        if (event.key !== 'Escape') {
            return;
        }

        document
            .querySelectorAll('.bl-modal.is-open')
            .forEach(function (modal) {
                closeModal(modal);
            });
    });

});
</script>

@endsection
