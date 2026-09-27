<header class="public-header">
  <div class="public-header-inner">
    <div class="brand"><img src="{{ asset('assets/img/itbrp-mark.svg') }}" alt="ITBRP">
      <div><strong>ITBRP</strong><small>Network Monitoring</small></div>
    </div>
    <nav class="public-nav">
      <a class="{{ request()->routeIs('monitoring.home') ? 'active' : '' }}" href="{{ route('monitoring.home') }}"><span class="nav-ico">⌂</span>Beranda</a>
      <a class="{{ request()->routeIs('monitoring.access*') ? 'active' : '' }}" href="{{ route('monitoring.access') }}"><span class="nav-ico">⌁</span>Akses Jaringan</a>
      <a class="{{ request()->routeIs('monitoring.devices*') ? 'active' : '' }}" href="{{ route('monitoring.devices') }}"><span class="nav-ico">▣</span>Perangkat</a>
      <a class="{{ request()->routeIs('monitoring.cache*') ? 'active' : '' }}" href="{{ route('monitoring.cache') }}"><span class="nav-ico">▤</span>Cache</a>
      <a class="{{ request()->routeIs('monitoring.statistics*') ? 'active' : '' }}" href="{{ route('monitoring.statistics') }}"><span class="nav-ico">▥</span>Statistik</a>
      <a class="{{ request()->routeIs('monitoring.blacklist*') ? 'active' : '' }}" href="{{ route('monitoring.blacklist') }}"><span class="nav-ico">◈</span>Filter & Blacklist</a>
      <a class="{{ request()->routeIs('monitoring.status*') ? 'active' : '' }}" href="{{ route('monitoring.status') }}"><span class="nav-ico">⌁</span>Status Sistem</a>
    </nav>
    <a class="public-profile-link" href="{{ route('monitoring.profile') }}" title="Profil Pengembang"><span class="admin-avatar">DA</span><span class="public-profile-text"><b>Dian Afriandi</b><small>Profil</small></span></a>
  </div>
</header>
<div class="public-toolbar">
  <div class="public-toolbar-inner">
    <div class="public-toolbar-status"><span class="status-dot"></span><span>Monitoring jaringan ITBRP • Mode baca</span></div><input class="searchbox public-search" placeholder="Cari IP, perangkat, domain, atau data monitoring...">
  </div>
</div>