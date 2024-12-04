<div class="card">
  @if($entries->isEmpty())
  <div class="card-body">
    <div class="row">
      <div class="col">
        {{ __('None.') }}
      </div>
    </div>
  </div>
  @else
  <div class="card-body p-0">
    <table class="table table-condensed mb-0">
      <thead>
      <tr>
        <th style="width:165px">{{ __('Date') }}</th>
        <th>{{ __('Message') }}</th>
        <th class="text-end" style="width:100px">{{ __('Event Id') }}</th>
      </tr>
      </thead>
      <tbody>
      @foreach($entries as $entry)
      <tr>
        <td style="width:165px">{{ $entry->timestamp }}</td>
        <td>{{ $entry->message }}</td>
        <td class="text-end">
          <span class="lozenge new">
            {{ Illuminate\Support\Number::format($entry->id, locale:'sv') }}
          </span>
        </td>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
