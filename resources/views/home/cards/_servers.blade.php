@if(Auth::user()->canListServers())
<div class="card card-accent-secondary tw-card">
  <div class="card-header d-flex flex-row">
    <div class="align-items-start">
      <h3 class="m-0">
        {{ __('Servers') }}
      </h3>
    </div>
    @if(Auth::user()->canManageServers())
    <div class="align-items-end">
      <h3 class="m-0">
        <a href="{{ route('ynh.servers.create') }}" class="float-end">
          {{ __('+ new') }}
        </a>
      </h3>
    </div>
    @endif
  </div>
  <div id="result-7" class="alert alert-dismissible fade show m-2" style="display:none;">
    <button type="button" class="btn-close" aria-label="Close" onclick="closeResult7()"></button>
    <span id="result-message-7"></span>
  </div>
  @if($servers->isEmpty())
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
        <th class="ps-4" width="25px"></th>
        <th>
          <i class="zmdi zmdi-long-arrow-down"></i>&nbsp;{{ __('Name') }}
        </th>
        <th>{{ __('OS') }}</th>
        <th>{{ __('IP V4') }}</th>
        <th>{{ __('IP V6') }}</th>
        <th>{{ __('Domain') }}</th>
        <th>{{ __('Domains') }}</th>
        <th>{{ __('Applications') }}</th>
        <th>{{ __('Users') }}</th>
        <th></th>
      </tr>
      </thead>
      <tbody>
      @foreach($servers->sortBy('name', SORT_NATURAL|SORT_FLAG_CASE) as $server)
      <tr>
        <td class="ps-4" width="25px" title="{{ $server->lastHeartbeat()?->format('Y-m-d H:i:s') }}">
          @if($server->isFrozen())
          <span class="tw-dot-blue"></span>
          @elseif($server->status() === \App\Enums\ServerStatusEnum::RUNNING)
          <span class="tw-dot-green"></span>
          @elseif($server->status() === \App\Enums\ServerStatusEnum::UNKNOWN)
          <span class="tw-dot-orange"></span>
          @else
          <span class="tw-dot-red"></span>
          @endif
        </td>
        <td>
          <span class="font-lg mb-3 fw-bold">
            @if($server->isFrozen())
            {{ $server->name }}
            @else
            <a href="{{ route('ynh.servers.edit', $server->id) }}">
              {{ $server->name }}
            </a>
            @endif
          </span>
        </td>
        <td>
          {{ isset($os_infos[$server->id]) && $os_infos[$server->id]->count() >= 1 ? $os_infos[$server->id][0]->os : '-' }}
        </td>
        <td>
          {{ $server->ip() }}
        </td>
        <td>
          @if($server->isFrozen() || $server->ipv6() === '<unavailable>')
          -
          @else
          {{ $server->ipv6() }}
          @endif
        </td>
        <td>
          @if($server->isFrozen())
          -
          @else
          {{ $server->domain()?->name }}
          @endif
        </td>
        <td>
          @if($server->isFrozen() || $server->addedWithCurl())
          -
          @else
          <a href="{{ route('ynh.servers.edit', $server->id) }}?tab=domains">
            {{ $server->domains->count() }}
          </a>
          @endif
        </td>
        <td>
          @if($server->isFrozen() || $server->addedWithCurl())
          -
          @else
          <a href="{{ route('ynh.servers.edit', $server->id) }}?tab=applications">
            {{ $server->applications->count() }}
          </a>
          @endif
        </td>
        <td>
          @if($server->isFrozen() || $server->addedWithCurl())
          -
          @else
          <a href="{{ route('ynh.servers.edit', $server->id) }}?tab=users">
            {{ $server->users->count() }}
          </a>
          @endif
        </td>
        <th>
          @if($server->isReady() && Auth::user()->canManageServers())
          <a id="refresh-{{ $server->id }}"
             onclick="refresh('{{ $server->id }}')"
             class="cursor-pointer"
             title="refresh">
            <span class=refresh>&#x27f3;</span>
          </a>
          @endif
        </th>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
<script>

  function closeResult7() {
    const resultDiv = document.getElementById('result-7');
    resultDiv.style.display = 'none';
  }

  function refresh(serverId) {

    const refreshBtn = document.getElementById(`refresh-${serverId}`);
    const resultDiv = document.getElementById('result-7');
    const messageSpan = document.getElementById('result-message-7');

    if (refreshBtn.classList.contains('loading')) {
      return;
    }

    refreshBtn.classList.add('loading');
    refreshBtn.innerHTML = '<span class=refresh>&#x25cc;</span>';

    axios.post(`/ynh/servers/${serverId}/pull-server-infos`, {})
    .then(function (data) {
      resultDiv.className = 'alert alert-dismissible fade show m-2';
      resultDiv.style.display = 'block';
      if (data.data.success) {
        resultDiv.classList.add('alert-success');
        resultDiv.classList.remove('alert-danger');
        messageSpan.textContent = data.data.success;
      } else if (data.data.error) {
        resultDiv.classList.add('alert-danger');
        resultDiv.classList.remove('alert-success');
        messageSpan.textContent = data.data.error;
      } else {
        console.log(data.data);
      }
    }).catch(error => {
      console.error('Error:', error);
      resultDiv.className = 'alert alert-dismissible fade show m-2';
      resultDiv.style.display = 'block';
      resultDiv.classList.add('alert-danger');
      messageSpan.textContent = 'An error occurred.';
    }).finally(() => {
      refreshBtn.innerHTML = '<span class=refresh>&#x27f3;</span>';
      refreshBtn.classList.remove('loading');
      setTimeout(() => window.location.reload(), 5000);
    });
  }

</script>
@endif