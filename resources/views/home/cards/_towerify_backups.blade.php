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
  </div>
  <div id="result-8" class="alert alert-dismissible fade show m-2" style="display:none;">
    <button type="button" class="btn-close" aria-label="Close" onclick="closeResult8()"></button>
    <span id="result-message-8"></span>
  </div>
  @if($backups->isEmpty())
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
        <th>{{ __('Date') }}</th>
        <th>{{ __('Server') }}</th>
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
          {{ $backup->server->name }}
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
            <img src="{{ asset('images/download.png') }}" height="20" class="float-end">
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

  function closeResult8() {
    const resultDiv = document.getElementById('result-8');
    resultDiv.style.display = 'none';
  }

</script>
@endif