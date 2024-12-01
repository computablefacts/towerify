<div class="card">
  @if($rules->isEmpty())
  <div class="card-body">
    <div class="row">
      <div class="col">
        None.
      </div>
    </div>
  </div>
  @else
  <div class="card-body p-0">
    <table class="table table-hover no-bottom-margin">
      <thead>
      <tr>
        <th>{{ __('IOC') }}</th>
        <!-- <th class="text-end">{{ __('Weight') }}</th> -->
        <th>{{ __('Name') }}</th>
        <th>{{ __('Version') }}</th>
        <th>{{ __('Interval') }}</th>
        <th>{{ __('Platform') }}</th>
      </tr>
      </thead>
      <tbody>
      @foreach($rules as $rule)
      <tr>
        <td>
          @if($rule->is_ioc)
          <span class="lozenge error">yes</span>
          @else
          <span class="lozenge success">no</span>
          @endif
        </td>
        <!-- <td class="text-end">
          <span class="lozenge information">{{ $rule->score }}</span>
        </td> -->
        <td>
          <span class="font-lg mb-3 fw-bold">
            {{ $rule->name }}&nbsp;{!! $rule->category ? '<span style="color:#ff9704">/</span>&nbsp;' . $rule->category : '' !!}
          </span>
          <div class="text-muted">
            {{ $rule->description }}
          </div>
          @if($rule->attck)
          <div class="text-muted">
            @foreach($rule->mitreAttckTactics() as $tactic)
            <span class="lozenge new">{{ $tactic }}</span>&nbsp;
            @endforeach
          </div>
          <div class="text-muted">
            @foreach(explode(',', $rule->attck) as $attck)
            <a href="https://attack.mitre.org/techniques/{{ $attck }}/">
              {{ $attck }}
            </a>&nbsp;
            @endforeach
          </div>
          @endif
        </td>
        <td>
          {{ $rule->version ? $rule->version : '-' }}
        </td>
        <td>
          {{ \Carbon\CarbonInterval::seconds($rule->interval)->cascade()->forHumans(); }}
        </td>
        <td>
          <span class="lozenge new">
            {{ $rule->platform->value }}
          </span>
        </td>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>