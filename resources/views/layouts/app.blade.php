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

  <!-- Axios -->
  <script src="{{ asset('js/app.js') }}"></script>

  <style>

    /* Pages Layout */

    .bd-layout {
      display: flex;
      flex-direction: column
    }

    @media (min-width: 992px) {
      .bd-layout {
        display: grid;
        grid-template-areas: "sidebar main";
        grid-template-columns: min-content 1fr;
        gap: 0
      }
    }

    .bd-main {
      grid-area: main;
      display: grid;
      grid-template-areas: "intro" "content" "footer";
      grid-template-rows: auto 1fr
    }

    .bd-main-content {
      display: grid;
      grid-area: content;
      grid-template-areas: "content toc";
      grid-template-columns: 1fr min-content;
    }

    .bd-content {
      min-width: 1px;
      grid-area: content;
      position: relative;
    }

    .bd-bg-light {
      background-color: var(--ds-background-neutral);
    }

    /* CyberBuddy */

    #botmanWidgetRoot .desktop-closed-message-avatar {
      background: unset !important;
    }

  </style>
</head>
<body class="bd-bg-light">
@auth
@include('layouts._blueprintjs')
@endauth
@include('layouts._header')
@include('layouts._menu')
<div class="bd-layout">
  @auth
  @include('layouts._sidebar')
  @endauth
  <main class="bd-main">
    <div class="bd-main-content">
      <div class="bd-content px-4 px-md-6 px-lg-10 px-xl-10 py-8">
        @yield('content')
      </div>
    </div>
  </main>
</div>
@include('layouts._toaster')
@include('layouts._drawer')
@include('layouts._freshdesk')
</body>
</html>
