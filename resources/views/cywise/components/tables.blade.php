<div class="card">
  <div class="card-header d-flex flex-row">
    <div class="d-flex align-content-end">
      <h6 class="m-0">
        <a href="{{ route('home', ['tab' => 'tables-add']) }}">
          {{ __('+ new') }}
        </a>
      </h6>
    </div>
  </div>
  <div class="card-body p-0">
    <x-tables-list/>
  </div>
</div>
