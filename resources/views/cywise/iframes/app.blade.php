<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('app.name') }}</title>

  <!-- FastBootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/fastbootstrap@2.2.0/dist/css/fastbootstrap.min.css"
        rel="stylesheet"
        integrity="sha256-V6lu+OdYNKTKTsVFBuQsyIlDiRWiOmtC8VQ8Lzdm2i4="
        crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
          integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
          crossorigin="anonymous"></script>

  <!-- app-specific styles -->
  <link href="{{ asset('cywise/css/app.css') }}" rel="stylesheet">

  <!-- page-specific styles -->
  @stack('styles')
</head>
<body data-bs-theme="light">
@include('cywise.iframes._blueprintjs')
@include('cywise.iframes._toaster')
@include('cywise.iframes._json-rpc')
<div class="container-fluid">
  @yield('content')
</div>
<!-- page-specific scripts -->
@stack('scripts')
</body>
</html>
