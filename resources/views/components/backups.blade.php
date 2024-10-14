@if(Auth::user()->canListServers())
<div class="card">
  @if(Auth::user()->canManageServers() && isset($url))
  <div class="card-header d-flex flex-row">
    <div class="align-items-end">
      <h6 class="m-0">
        <a href="#" class="float-end" onclick="createBackup()">
          {{ __('+ new') }}
        </a>
      </h6>
    </div>
  </div>
  @endif
  @if($backups->isEmpty())
  <div class="card-body">
    <div class="row">
      <div class="col">
        {{ __('None.') }}
      </div>
    </div>
  </div>
  @else
  <div class="card-body p-0">
    <table class="table table-hover no-bottom-margin">
      <thead>
      <tr>
        <th>{{ __('Date') }}</th>
        <th>{{ __('Name') }}</th>
        <th>{{ __('Size') }}</th>
        <th></th>
      </tr>
      </thead>
      <tbody>
      @foreach($backups as $backup)
      <tr>
        <td>
          {{ $backup->updated_at->format('Y-m-d H:i:s') }}
        </td>
        <td>
          {{ $backup->name }}
        </td>
        <td>
          {{ format_bytes($backup->size) }}
        </td>
        <td>
          @if($backup->server->isReady() && Auth::user()->canManageServers())
          <a href="/ynh/servers/{{ $backup->server->id }}/backup/{{ $backup->id }}"
             class="cursor-pointer"
             title="download">
            {{ __('open') }}
          </a>
          @endif
        </td>
      </tr>
      @if(count($backup->result['apps']) > 0)
      <tr>
        <td colspan="4">
          @foreach($backup->result['apps'] as $app => $status)
          @if($status === 'Success')
          <span class="lozenge success">{{ $app }}</span>
          @else
          <span class="lozenge error">{{ $app }}</span>
          @endif
          @endforeach
        </td>
      </tr>
      @endif
      @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
@if(Auth::user()->canManageServers() && isset($url))
<script>

  function createBackup() {
    axios.post("{{ $url }}", {}).then(function (response) {
      if (response.data.success) {
        toaster.toastSuccess(response.data.success);
      } else if (response.data.error) {
        toaster.toastError(response.data.error);
      } else {
        console.log(response.data);
      }
    }).catch(error => toaster.toastAxiosError(error));
  }

</script>
@endif
@endif