@if(Auth::user()->canListServers())
@if($traces->isEmpty())
<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="mb-3 col">
        <div class="row">
          <div class="col">
            {{ __('None.') }}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@else
@foreach($servers as $server)
<div class="card mt-2">
  <div class="card-body">
    <h6 class="card-title">
      {{ $server->name }}&nbsp;<span style="color:#f8b502">/</span>&nbsp;{{ __('Latest traces') }}
    </h6>
    <div class="row">
      <div class="col">
        @foreach($tracesGroupedByServers[$server->name] as $trace)
        <div>
          @if($trace->state === \App\Enums\SshTraceStateEnum::PENDING)
          <span class="tw-dot-blue"></span>
          @elseif ($trace->state === \App\Enums\SshTraceStateEnum::IN_PROGRESS)
          <span class="tw-dot-orange"></span>
          @elseif ($trace->state === \App\Enums\SshTraceStateEnum::DONE)
          <span class="tw-dot-green"></span>
          @else
          <span class="tw-dot-red"></span>
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