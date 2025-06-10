@extends('cywise.iframes.app')

@section('content')
<div class="row">
  <div class="col-4">
    <div class="card">
      <div class="card-body p-3">
        <div class="row align-items-center">
          <div class="col-auto">
            <div class="d-flex align-content-center" style="background-color:#ffaa00 !important;">
              <span class="bg-primary text-white avatar" style="background-color:#ffaa00 !important;">
                <i class="fa-solid fa-bell fa-3x icon-primary"></i>
              </span>
            </div>
          </div>
          <div class="col">
            <div class="font-weight-medium">
              <b>{{ $nb_monitored }}</b>
            </div>
            <div style="opacity:.55">
              Monitored Assets
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-4">

  </div>
  <div class="col-4">

  </div>
</div>
@endsection
