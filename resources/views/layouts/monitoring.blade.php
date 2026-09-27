<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="description" content="ITBRP Network Monitoring">
  <title>@yield('title', 'Monitoring') • ITBRP Network Monitoring</title>
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  @stack('styles')
</head>
<body>
<div class="app public-app">
  @include('partials.monitoring.header')
  <main class="public-content">@yield('content')</main>
  @include('partials.shared.footer')
</div>
<script src="{{ asset('assets/js/ui.js') }}"></script>
@stack('scripts')
</body>
</html>
