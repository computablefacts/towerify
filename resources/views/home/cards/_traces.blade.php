@if(Auth::user()->canListServers())
@if($traces->isEmpty())
<div class="card card-accent-secondary mt-4 tw-card">
  <div class="card-header">
    <h3 class="m-0"><b>{{ __('Traces') }}</b></h3>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="mb-3 col">
        <div class="row">
          <div class="col">
            None.
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@else
<?php $tracesGroupedByServers = $traces->groupBy(fn($trace) => $trace->server->name) ?>
<?php $servers = $tracesGroupedByServers->map(fn($traces, $server) => $server)->sort() ?>
@foreach($servers as $server)
<div class="card card-accent-secondary mt-4 tw-card">
  <div class="card-header">
    <h3 class="m-0"><b>{{ $server }} / {{ __('Latest traces') }}</b></h3>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="mb-3 col">
        @foreach($tracesGroupedByServers[$server] as $trace)
        <div>
          @if($trace->state === \App\Enums\SshTraceStateEnum::PENDING)
          <span class="me-2 tw-dot-blue"></span>
          @elseif ($trace->state === \App\Enums\SshTraceStateEnum::IN_PROGRESS)
          <span class="me-2 tw-dot-orange"></span>
          @elseif ($trace->state === \App\Enums\SshTraceStateEnum::DONE)
          <span class="me-2 tw-dot-green"></span>
          @else
          <span class="me-2 tw-dot-red"></span>
          @endif
          {{ $trace->updated_at }} - {{ $trace->trace }}
        </div>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endforeach
@endif
@endif