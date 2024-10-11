@if(Auth::user()->canListServers())
<div class="card card-accent-secondary tw-card">
  <div class="card-header d-flex flex-row">
    <div class="align-items-start">
      <h3 class="m-0">
        {{ __('Backups') }}
      </h3>
    </div>
    @if(Auth::user()->canManageServers() && isset($url))
    <div class="align-items-end">
      <h3 class="m-0">
        <a href="#" class="float-end" onclick="createBackup()">
          {{ __('+ new') }}
        </a>
      </h3>
    </div>
    @endif
  </div>
  @if(count($backups) <= 0)
  <div class="card-body">
    <div class="row">
      <div class="col">
        {{ __('None.') }}
      </div>
    </div>
  </div>
  @else
  <div class="card-body p-0">
    <table class="table table-hover">
      <thead>
      <tr>
        <th>
          <i class="zmdi zmdi-long-arrow-up"></i>&nbsp;{{ __('Date') }}
        </th>
        <th>{{ __('Name') }}</th>
        <th>{{ __('Size') }}</th>
        <th></th>
      </tr>
      </thead>
      <tbody>
      @foreach($backups->sortByDesc('updated_at') as $backup)
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
            <i class="zmdi zmdi-download"></i>
          </a>
          @endif
        </td>
      </tr>
      <tr>
        <td colspan="4">
          @foreach($backup->result['apps'] as $app => $status)
          @if($status === 'Success')
          <span class="tw-pill rounded-pill bg-success">{{ $app }}</span>
          @else
          <span class="tw-pill rounded-pill bg-danger">{{ $app }}</span>
          @endif
          @endforeach
        </td>
      </tr>
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