@extends('cywise.iframes.app')

@section('content')
<!-- ASSETS -->
<div class="row pt-3">
  <div class="col-4">
    <div class="card">
      <div class="card-body p-3">
        <div class="row align-items-center">
          <div class="col-auto">
            <div class="d-flex align-content-center">
              <span class="bg-blue text-white avatar">
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
  <div class="col-4 ps-0">
    <div class="card">
      <div class="card-body p-3">
        <div class="row align-items-center">
          <div class="col-auto">
            <div class="d-flex align-content-center">
              <span class="bg-blue text-white avatar">
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
  <div class="col-4 ps-0">
    <div class="card">
      <div class="card-body p-3">
        <div class="row align-items-center">
          <div class="col-auto">
            <div class="d-flex align-content-center">
              <span class="bg-blue text-white avatar">
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
<!-- VULNERABILITIES -->
<div class="row pt-3">
  <div class="col-4">
    <div class="card">
      <div class="card-body p-3">
        <div class="row align-items-center">
          <div class="col-auto">
            <div class="d-flex align-content-center">
              <span class="bg-red text-white avatar">
                <span class="bp4-icon bp4-icon-issue"></span>
              </span>
            </div>
          </div>
          <div class="col">
            <div class="h5 mb-0">
              <b>{{ $nb_high }}</b>
            </div>
            <div class="text-muted">
              {{ __('Vuln. High') }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-4 ps-0">
    <div class="card">
      <div class="card-body p-3">
        <div class="row align-items-center">
          <div class="col-auto">
            <div class="d-flex align-content-center">
              <span class="bg-orange text-white avatar">
                <span class="bp4-icon bp4-icon-issue"></span>
              </span>
            </div>
          </div>
          <div class="col">
            <div class="h5 mb-0">
              <b>{{ $nb_medium }}</b>
            </div>
            <div class="text-muted">
              {{ __('Vuln. Medium') }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-4 ps-0">
    <div class="card">
      <div class="card-body p-3">
        <div class="row align-items-center">
          <div class="col-auto">
            <div class="d-flex align-content-center">
              <span class="bg-green text-white avatar">
                <span class="bp4-icon bp4-icon-issue"></span>
              </span>
            </div>
          </div>
          <div class="col">
            <div class="h5 mb-0">
              <b>{{ $nb_low }}</b>
            </div>
            <div class="text-muted">
              {{ __('Vuln. Low') }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- CYBERTODO -->
@if(count($todo) > 0)
<style>

  .todo-item a:hover, .todo-item a:focus {
    outline: 0;
    color: var(--c-blue-500);
  }

  .todo-item a {
    color: var(--c-grey-500);
    font-weight: 500;
    text-decoration: none;
    border-bottom: 1px dashed;
  }

</style>
<div class="row pt-3">
  <div class="col-4">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">
          {!! __('Your 5 most critical vulnerabilities to fix!') !!}
        </h6>
        <div class="card-text mb-3">
          @foreach($todo as $item)
          <div class="d-flex justify-content-start align-items-center text-truncate mb-2 todo-item">
            @if($item->level === 'High')
            <span class="dot-red"></span>
            @elseif ($item->level === 'Medium')
            <span class="dot-orange"></span>
            @elseif($item->level === 'Low')
            <span class="dot-green"></span>
            @else
            <span class="dot-blue"></span>
            @endif
            &nbsp;<a href="#vid-{{ $item->id }}">
              {{ $item->asset()->asset }}
            </a>
          </div>
          <div class="d-flex justify-content-start align-items-center text-truncate mb-3">
            @if(empty($item->cve_id))
            {{ $item->title }}
            @else
            {{ $item->cve_id }}&nbsp;/&nbsp;{{ $item->title }}
            @endif
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
@endif
<!-- HONEYPOTS -->
<div class="row pt-3">
  <div class="col-4">

  </div>
  <div class="col-4">

  </div>
  <div class="col-4">

  </div>
</div>
@endsection
