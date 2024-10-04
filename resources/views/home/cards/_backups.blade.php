@if(Auth::user()->canListServers())
<?php

function formatBytes($bytes, $precision = 2)
{
    $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . $units[$pow];
}

?>
<div class="card card-accent-secondary tw-card">
  <div class="card-header d-flex flex-row">
    <div class="align-items-start">
      <h3 class="m-0">
        {{ __('Backups') }}
      </h3>
    </div>
    @if(Auth::user()->canManageServers())
    <div class="align-items-end">
      <h3 class="m-0">
        <a href="#" class="float-end" onclick="createBackup()">
          {{ __('+ new') }}
        </a>
      </h3>
    </div>
    @endif
  </div>
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
          {{ formatBytes($backup->size) }}
        </td>
        <td>
          @if($server->isReady() && Auth::user()->canManageServers())
          <a href="/ynh/servers/{{ $server->id }}/backup/{{ $backup->id }}"
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
<script>

  function createBackup() {
    axios.post("{{ route('ynh.servers.create-backup', $server) }}", {}).then(function (response) {
      if (response.data.success) {
        toaster.toastSuccess(response.data.success);
      } else if (response.data.error) {
        toaster.toastError(response.data.error);
      } else {
        console.log(data.data);
      }
    }).catch(error => toaster.toastAxiosError(error));
  }

</script>
@endif