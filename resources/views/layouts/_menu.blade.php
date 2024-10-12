<div class="offcanvas offcanvas-end w-100 mw-100" tabindex="-1" id="bdNavbar" role="dialog">
  <div class="offcanvas-header border-bottom px-5">
    <img src="{{ asset('images/logo.png') }}" alt="Cywise's logo" height="32">
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body px-5">
    <div class="d-flex flex-column">
      @foreach(app_menu() as $item)
      @if(!isset($item['hidden']) || !$item['hidden'])
      @if(isset($item['post_form']) && $item['post_form'])
        <?php $id = \Illuminate\Support\Str::random(10) ?>
      <a href="{{ $item['route'] }}" class="py-2 fw-bold text-reset"
         onclick="event.preventDefault();document.getElementById('{{ $id }}').submit();">
        {{ $item['label'] }}
      </a>
      <form id="{{ $id }}" action="{{ $item['route'] }}" method="POST" style="display:none">
        @csrf
      </form>
      @else
      <a class="py-2 fw-bold text-reset" href="{{ $item['route'] }}">
        {{ $item['label'] }}
      </a>
      @endif
      @endif
      @endforeach
    </div>
  </div>
</div>