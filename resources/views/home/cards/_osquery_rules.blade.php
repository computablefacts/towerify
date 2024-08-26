<div class="card card-accent-secondary tw-card">
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
    <table class="table table-hover">
      <thead>
      <tr>
        <th>
          <i class="zmdi zmdi-long-arrow-down"></i>&nbsp;{{ __('Name') }}
        </th>
        <th>{{ __('Version') }}</th>
        <th>{{ __('Interval') }}</th>
        <th>{{ __('Platform') }}</th>
        <!-- <th>{{ __('Snapshot') }}</th> -->
      </tr>
      </thead>
      <tbody>
      @foreach($rules->sortBy('name', SORT_NATURAL|SORT_FLAG_CASE) as $rule)
      <tr>
        <td>
          <span class="font-lg mb-3 fw-bold">
            {{ $rule->name }}&nbsp;{!! $rule->category ? '<span style="color:#ff9704">/</span>&nbsp;' . $rule->category : '' !!}
          </span>
          <div class="text-muted">
            {{ $rule->description }}
          </div>
          @if($rule->value)
          <div class="text-muted">
            {{ $rule->value }}
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
          <span class="tw-pill rounded-pill bg-primary">
            {{ $rule->platform->value }}
          </span>
        </td>
        <!-- <td>
          {{ $rule->snapshot ? 'YES' : 'NO' }}
        </td> -->
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>