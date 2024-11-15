<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name') }}</title>

  <!-- favicons -->
  @include('layouts._favicons')

  <!-- FastBootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/fastbootstrap@2.2.0/dist/css/fastbootstrap.min.css" rel="stylesheet"
        integrity="sha256-V6lu+OdYNKTKTsVFBuQsyIlDiRWiOmtC8VQ8Lzdm2i4=" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
          integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
          crossorigin="anonymous"></script>

  <!-- App-specific -->
  <link href="{{ asset('css/app.css') }}" rel="stylesheet">

</head>
<body class="bd-bg-light">
@auth
@include('layouts._blueprintjs')
@endauth
@include('layouts._header')
@include('layouts._menu')
<div class="bd-layout">
  @auth
  @if(Auth::user() && !Auth::user()->isBarredFromAccessingTheApp())
  @include('layouts._sidebar')
  @endif
  @endauth
  <main class="bd-main">
    <div class="bd-main-content">
      <div class="bd-content py-3">
        <div class="container mb-2">
          <div class="row">
            <div class="col">
              <x-block-note/>
            </div>
          </div>
        </div>
        @if(Auth::user() && Auth::user()->isBarredFromAccessingTheApp())
        @include('layouts._trial-ended')
        @endif
        @yield('content')
      </div>
    </div>
  </main>
</div>
@auth
@include('layouts._toaster')
@include('layouts._drawer')
@endauth
@include('layouts._freshdesk')
@stack('alpine')
<script src="{{ asset('js/app.js') }}"></script> <!-- Axios, Alpine, etc. -->
@stack('scripts')
</body>
</html>
