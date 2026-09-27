<aside class="sidebar">
  <div class="brand">
    <img src="{{ asset('assets/img/itbrp-mark.svg') }}" alt="ITBRP">
    <div><strong>ITBRP</strong><small>Network Monitoring</small></div>
  </div>
  <nav class="side-nav">
    <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><span class="nav-ico">•</span>Beranda</a>
    <a class="{{ request()->routeIs('admin.access*') ? 'active' : '' }}" href="{{ route('admin.access.index') }}"><span class="nav-ico">⌁</span>Akses Jaringan</a>
    <a class="{{ request()->routeIs('admin.devices*') ? 'active' : '' }}" href="{{ route('admin.devices.index') }}"><span class="nav-ico">▣</span>Perangkat</a>
    <a class="{{ request()->routeIs('admin.cache*') ? 'active' : '' }}" href="{{ route('admin.cache.index') }}"><span class="nav-ico">▤</span>Cache</a>
    <a class="{{ request()->routeIs('admin.statistics*') ? 'active' : '' }}" href="{{ route('admin.statistics.index') }}"><span class="nav-ico">▥</span>Statistik</a>
    <a class="{{ request()->routeIs('admin.blacklist*') ? 'active' : '' }}" href="{{ route('admin.blacklist.index') }}"><span class="nav-ico">◈</span>Filter & Blacklist</a>
    <a class="{{ request()->routeIs('admin.status*') ? 'active' : '' }}" href="{{ route('admin.status.index') }}"><span class="nav-ico">⌁</span>Status Sistem</a>
    <a class="{{ request()->routeIs('admin.profile') ? 'active' : '' }}" href="{{ route('admin.profile') }}"><span class="nav-ico">◎</span>Profil Pengembang</a>
  </nav>
  <div class="side-sep"></div>
  <div class="side-label">SYSTEM</div>
  <nav class="side-nav">
    <a href="#"><span class="nav-ico">◔</span>Notifikasi <span class="badge bad" style="margin-left:auto">3</span></a>
    <a href="#"><span class="nav-ico">?</span>Bantuan</a>
    <a href="{{ route('admin.profile') }}"><span class="nav-ico">◉</span>Tentang Sistem</a>
  </nav>
</aside>