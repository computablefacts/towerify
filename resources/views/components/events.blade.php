@if($entries->isNotEmpty())
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
@endif