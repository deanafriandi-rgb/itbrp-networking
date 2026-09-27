<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="description" content="ITBRP Network Monitoring">
  <title>@yield('title', 'ITBRP Admin') • ITBRP Network Monitoring</title>
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  @stack('styles')
</head>

<body>
  <div class="app admin-shell">
    @include('partials.admin.sidebar')
    <main class="admin-main">
      @include('partials.admin.header')
      <div class="admin-content">@yield('content')</div>
      @include('partials.shared.footer', ['admin' => true])
    </main>
  </div>
  <script src="{{ asset('assets/js/ui.js') }}"></script>
  @stack('scripts')
</body>

</html>