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
        <th>{{ __('Date') }}</th>
        <th>{{ __('Message') }}</th>
      </tr>
      </thead>
      <tbody>
      @foreach($entries as $entry)
      <tr>
        <td>{{ $entry->timestamp }}</td>
        <td>{{ $entry->message }}</td>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
