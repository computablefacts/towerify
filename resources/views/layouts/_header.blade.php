<style>

  .navbar-toggler {
    padding: var(--bs-navbar-toggler-padding-y) var(--bs-navbar-toggler-padding-x);
    font-size: var(--bs-navbar-toggler-font-size);
    line-height: 1;
    color: var(--bs-navbar-color);
    background-color: transparent;
    border: var(--bs-border-width) solid var(--bs-navbar-toggler-border-color);
    border-radius: var(--bs-navbar-toggler-border-radius);
    transition: var(--bs-navbar-toggler-transition);
  }

</style>
<header class="navbar navbar-expand-lg py-0 sticky-top bd-navbar">
  <nav class="container-fluid px-md-3 px-lg-4">
    <a class="navbar-brand d-inline-flex align-items-center m-0 p-0 me-lg-6 me-xl-9 p-1 rounded text-reset"
       href="{{ Auth::check() ? '/' : config('towerify.website') }}">
      <span class="text-primary">
        <img src="{{ asset('images/logo.png') }}" alt="Cywise's logo" height="32">
      </span>
      <h2 class="d-none d-md-block fw-semibold fs-5 ls-wide ms-2 mb-0">
        {{ config('app.name') }}
      </h2>
    </a>
    <div class="d-flex align-items-center ms-auto gap-3 me-2 me-lg-3">
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav navbar-nav-underline ps-lg-5 flex-grow-1">
          @if(isset($notifications) && count($notifications) > 0)
          <li class="nav-item d-flex align-items-center">
            @include('layouts._notifications')
          </li>
          @endif
          @foreach(app_header() as $item)
          <li class="nav-item">
            @if(isset($item['post_form']) && $item['post_form'])
              <?php $id = \Illuminate\Support\Str::random(10) ?>
            <a class="nav-link {{ isset($item['active']) && $item['active'] ? 'active' : '' }}"
               href="{{ $item['route'] }}"
               onclick="event.preventDefault();document.getElementById('{{ $id }}').submit();">
              {{ $item['label'] }}
            </a>
            <form id="{{ $id }}" action="{{ $item['route'] }}" method="POST" style="display:none">
              @csrf
            </form>
            @else
            <a class="nav-link {{ isset($item['active']) && $item['active'] ? 'active' : '' }}"
               href="{{ $item['route'] }}">
              {{ $item['label'] }}
            </a>
            @endif
          </li>
          @endforeach
        </ul>
      </div>
      <div class="d-flex align-items-center ms-auto gap-3 me-2 me-lg-3">
        <button class="navbar-toggler" type="bdNavbar" data-bs-toggle="offcanvas" data-bs-target="#bdNavbar">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentcolor" viewBox="0 0 16 16">
            <path fill-rule="evenodd"
                  d="M2.5 11.5A.5.5.0 013 11h10a.5.5.0 010 1H3a.5.5.0 01-.5-.5zm0-4A.5.5.0 013 7h10a.5.5.0 010 1H3a.5.5.0 01-.5-.5zm0-4A.5.5.0 013 3h10a.5.5.0 010 1H3a.5.5.0 01-.5-.5z"></path>
          </svg>
        </button>
      </div>
    </div>
  </nav>
</header>