@if(Auth::user()->canListServers())
<div class="card card-accent-secondary tw-card">
  <div class="card-header">
    <h3 class="m-0"><b>{{ __('Pending Actions') }}</b></h3>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="mb-3 col">
        @if($pendingActions->isEmpty())
        <div class="row">
          <div class="col">
            None.
          </div>
        </div>
        @else
        @foreach($pendingActions as $pendingAction)
        <div>
          @if($pendingAction->state === \App\Enums\SshTraceStateEnum::PENDING)
          <span class="me-2 tw-dot-blue"></span>
          @elseif ($pendingAction->state === \App\Enums\SshTraceStateEnum::IN_PROGRESS)
          <span class="me-2 tw-dot-orange"></span>
          @elseif ($pendingAction->state === \App\Enums\SshTraceStateEnum::DONE)
          <span class="me-2 tw-dot-green"></span>
          @else
          <span class="me-2 tw-dot-red"></span>
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