@if(Auth::user()->canListServers())
<div class="card card-accent-secondary tw-card">
  <div class="card-header">
    <h3 class="m-0"><b>{{ __('Traces') }}</b></h3>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="mb-3 col">
        @if($traces->isEmpty())
        <div class="row">
          <div class="col">
            None.
          </div>
        </div>
        @else
        @foreach($traces as $trace)
        <div>
          @if($trace->state->value === 'pending')
          <span class="me-2 tw-dot-blue"></span>
          @elseif ($trace->state->value === 'in_progress')
          <span class="me-2 tw-dot-orange"></span>
          @elseif ($trace->state->value === 'done')
          <span class="me-2 tw-dot-green"></span>
          @else
          <span class="me-2 tw-dot-red"></span>
          @endif
          {{ $trace->updated_at }} - {{ $trace->trace }}
        </div>
        @endforeach
        @endif
      </div>
    </div>
  </div>
</div>
@endif