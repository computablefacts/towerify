<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name') }}</title>

  <!-- Fonts -->
  <link rel="dns-prefetch" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

  <!-- Styles -->
  <link href="{{ asset('css/app.css') }}" rel="stylesheet">
  <link href="{{ asset('css/appshell.css') }}" rel="stylesheet">
  <link href="{{ asset('css/custom.css') }}" rel="stylesheet">

  <!-- favicons -->
  @include('layouts._favicons')

  <!-- Tom Select -->
  <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap4.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/tom-select"></script>

  <!-- Icons -->
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/material-design-iconic-font/2.2.0/css/material-design-iconic-font.min.css"
    media="all" type="text/css" rel="stylesheet" integrity="sha256-3sPp8BkKUE7QyPSl6VfBByBroQbKxKG7tsusY2mhbVY="
    crossorigin="anonymous">

  <!-- Reactjs -->
  <script src="{{ asset('adversary_meter/src/blueprintjs/reactjs/react.production.min.js') }}"></script>
  <script src="{{ asset('adversary_meter/src/blueprintjs/reactjs/react-dom.production.min.js') }}"></script>
  <script src="{{ asset('adversary_meter/src/blueprintjs/reactjs/react-is.production.min.js') }}"></script>

  <!-- Blueprintjs -->
  <link href="{{ asset('adversary_meter/src/blueprintjs/normalize/normalize.css') }}" rel="stylesheet"/>
  <link href="{{ asset('adversary_meter/src/blueprintjs/blueprintjs/blueprint-icons.css') }}" rel="stylesheet"/>
  <link href="{{ asset('adversary_meter/src/blueprintjs/blueprintjs/blueprint.css') }}" rel="stylesheet"/>
  <link href="{{ asset('adversary_meter/src/blueprintjs/blueprintjs/blueprint-popover2.css') }}" rel="stylesheet"/>
  <link href="{{ asset('adversary_meter/src/blueprintjs/blueprintjs/table.css') }}" rel="stylesheet"/>
  <link href="{{ asset('adversary_meter/src/blueprintjs/blueprintjs/blueprint-select.css') }}" rel="stylesheet"/>
  <link href="{{ asset('adversary_meter/src/blueprintjs/blueprintjs/blueprint-datetime.css') }}" rel="stylesheet"/>

  <!-- CyberBuddy -->
  <style>
    #botmanWidgetRoot .desktop-closed-message-avatar {
      background: unset !important;
    }
  </style>
</head>
<body>
<div id="app">
  <nav class="navbar navbar-expand-md navbar-light navbar-laravel">
    <div class="container">
      <a class="navbar-brand" href="{{ url('/') }}">
        <div class="tw-logo">
          <div class="image">
            <img src="{{ asset('images/logo.png') }}" alt="Cywise's logo">
          </div>
          <div class="text">
            {{ config('app.name') }}
          </div>
        </div>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-target="#navbarSupportedContent"
              aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <!-- Left Side Of Navbar -->
        <ul class="navbar-nav me-auto"></ul>
        <!-- Right Side Of Navbar -->
        <ul class="navbar-nav ms-auto">
          <!-- Authentication Links -->
          @guest
          <li class="nav-item">
            <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
          </li>
          <li class="nav-item">
            @if (Route::has('register'))
            <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
            @endif
          </li>
          @else
          @if(isset($notifications) && count($notifications) > 0)
          <li class="nav-item d-flex align-items-center">
            @include('layouts._notifications')
          </li>
          @endif
          @if(Auth::user()->isAdmin())
          <li class="nav-item">
            <a class="nav-link" href="{{ config('konekt.app_shell.ui.url') }}" target="_blank">
              Admin
            </a>
          </li>
          @endif
          <li class="nav-item">
            <a class="nav-link" href="{{ route('home') }}">Dashboard</a>
          </li>
          @if(Auth::user()->canBuyStuff())
          <li class="nav-item">
            <a class="nav-link" href="{{ route('product.index') }}">App Store</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ route('cart.show') }}">Cart
              @if (Cart::isNotEmpty())
              <span class="tw-pill rounded-pill bg-secondary">{{ Cart::itemCount() }}</span>
              @endif
            </a>
          </li>
          @endif
          <li class="nav-item dropdown">
            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
               aria-haspopup="true" aria-expanded="false" v-pre>
              {{ Auth::user()->name }} <span class="caret"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
              <a class="dropdown-item" href="{{ route('terms') }}">
                {{ __('Mentions légales') }}
              </a>
              <a class="dropdown-item" href="{{ route('reset-password') }}"
                 onclick="event.preventDefault();document.getElementById('reset-password-form').submit();">
                {{ __('Reset Password') }}
              </a>
              <form id="reset-password-form" action="{{ route('reset-password') }}" method="POST" style="display:none;">
                @csrf
              </form>
              <a class="dropdown-item" href="{{ route('logout') }}"
                 onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                {{ __('Logout') }}
              </a>
              <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
              </form>
            </div>
          </li>
          @endguest
        </ul>
      </div>
    </div>
  </nav>
  <main class="py-4">
    <div class="container">
      <div class="row">
        <div class="col-md-9 d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 bg-transparent tw-breadcrumbs">
              @yield('breadcrumbs')
            </ol>
          </nav>
        </div>
        <div class="col-md-3">
          @yield('categories-menu')
        </div>
      </div>
      <div class="row mt-3">
        @include('flash::message')
      </div>
    </div>
    @yield('content')
  </main>
</div>
<div id="toaster"></div>
<div id="drawer-33"></div>

<!-- Scripts -->
@stack('alpine')
<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
<script>
  /*
   * Fix Blueprintjs issue.
   *
   * https://adambien.blog/roller/abien/entry/uncaught_referenceerror_process_is_not
   */
  window.process = {
    env: {
      NODE_ENV: 'production'
    }
  }
</script>
<script src="{{ asset('adversary_meter/src/blueprintjs/main.min.js') }}"></script>
<script>

  const toaster = {
    el: new com.computablefacts.blueprintjs.MinimalToaster(document.getElementById('toaster')),
    toast: (msg, intent) => toaster.el.toast(msg, intent)
  };
  const drawer33 = {
    el: new com.computablefacts.blueprintjs.MinimalDrawer(document.getElementById('drawer-33'), '33%'),
    redraw: null,
    render: null
  };
  drawer33.el.onOpen(el => {
    // console.log(drawer);
    const div = document.createElement('div');
    div.innerHTML = drawer33.render ? drawer33.render() : '';
    el.appendChild(div);
    drawer33.redraw = () => div.innerHTML = drawer33.render ? drawer33.render() : '';
  });
  drawer33.el.onClose(() => {
    drawer33.redraw = null;
    drawer33.render = null;
  });

</script>
@if(Auth::user() && Auth::user()->canUseCyberBuddy())
<script>
  window.botmanWidget = {
    title: 'CyberBuddy',
    aboutText: '⚡ Powered by {{ config('
    app
    .name
    ') }}',
    aboutLink: '{{ app_url() }}',
    userId: '{{ Auth::user() ? Auth::user()->id : \Illuminate\Support\Str::random(10) }}',
    chatServer: '/cb/web/botman',
    bubbleAvatarUrl: '/images/icons/cyber-buddy.svg',
    frameEndpoint: '/cb/web/cyber-buddy/chat',
    introMessage: 'Que puis-je faire pour vous?',
    desktopHeight: 900,
    desktopWidth: 2 * window.innerWidth / 3,
    mainColor: '#47627F',
    bubbleBackground: '#00264b',
    headerTextColor: 'white',
  };
</script>
<script src='/cyber_buddy/botman/widget.js'></script>
@endif
</body>
</html>
