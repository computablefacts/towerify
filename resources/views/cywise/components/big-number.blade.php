<div class="card">
  <div class="card-body p-3">
    <div class="row align-items-center">
      <div class="col-auto">
        <div class="d-flex align-content-center" style="background-color:{{ $color }} !important;">
        <span class="bg-primary text-white avatar" style="background-color:{{ $color }} !important;">
          {!! $icon !!}
        </div>
      </div>
      <div class="col">
        <div class="font-weight-medium">
          <b>{{ $number }}</b>
        </div>
        <div style="opacity:.55">
          {{ $title }}
        </div>
      </div>
    </div>
  </div>
</div>