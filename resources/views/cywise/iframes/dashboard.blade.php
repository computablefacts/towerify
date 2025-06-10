@extends('cywise.iframes.app')

@section('content')
<div class="row pt">
  <div class="col-4">
    <div class="card">
      <div class="card-body p-3">
        <div class="row align-items-center">
          <div class="col-auto">
            <div class="d-flex align-content-center">
              <span class="bg-primary text-white avatar">
                <span class="bp4-icon bp4-icon-globe-network"></span>
              </span>
            </div>
          </div>
          <div class="col">
            <div class="h5 mb-0">
              <b>{{ $nb_monitored + $nb_monitorable }}</b>
            </div>
            <div class="text-muted">
              {{ __('Assets') }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-4">
    <div class="card">
      <div class="card-body p-3">
        <div class="row align-items-center">
          <div class="col-auto">
            <div class="d-flex align-content-center">
              <span class="bg-primary text-white avatar">
                <span class="bp4-icon bp4-icon-globe-network"></span>
              </span>
            </div>
          </div>
          <div class="col">
            <div class="h5 mb-0">
              <b>{{ $nb_monitored }}</b>
            </div>
            <div class="text-muted">
              {{ __('Assets Monitored') }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-4">
    <div class="card">
      <div class="card-body p-3">
        <div class="row align-items-center">
          <div class="col-auto">
            <div class="d-flex align-content-center">
              <span class="bg-primary text-white avatar">
                <span class="bp4-icon bp4-icon-globe-network"></span>
              </span>
            </div>
          </div>
          <div class="col">
            <div class="h5 mb-0">
              <b>{{ $nb_monitorable }}</b>
            </div>
            <div class="text-muted">
              {{ __('Assets Monitorable') }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
