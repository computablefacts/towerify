@if(Auth::user()->canListServers())
<div class="card">
  <div class="card-body">
    <h6 class="card-title">{{ __('Pending Actions') }}</h6>
    <div class="row">
      <div class="col">
        @if($pendingActions->isEmpty())
        <div class="row">
          <div class="col">
            {{ __('None.') }}
          </div>
        </div>
        @else
        @foreach($pendingActions as $pendingAction)
        <div>
          @if($pendingAction->state === \App\Enums\SshTraceStateEnum::PENDING)
          <span class="tw-dot-blue"></span>
          @elseif ($pendingAction->state === \App\Enums\SshTraceStateEnum::IN_PROGRESS)
          <span class="tw-dot-orange"></span>
          @elseif ($pendingAction->state === \App\Enums\SshTraceStateEnum::DONE)
          <span class="tw-dot-green"></span>
          @else
          <span class="tw-dot-red"></span>
          @endif
          {{ $pendingAction->updated_at }} - {{ $pendingAction->server->name }} - {{ $pendingAction->trace }}
        </div>
        @endforeach
        @endif
      </div>
    </div>
  </div>
</div>
@endif