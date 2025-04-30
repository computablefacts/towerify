@if(Auth::user()->canManageServers() && !$is_yunohost)
<div class="card mb-4" style="border-top:1px solid #becdcf;background-color:#fff3cd;">
  <div class="card-body">
    <div class="row">
      <div class="col">
        {{ __('To monitor a new Linux server, log in as root and execute this command line :') }}
        <br><br>
        <pre class="no-bottom-margin">
curl -s "{{ app_url() }}/setup/script?api_token={{ Auth::user()->sentinelApiToken() }}&server_ip=$(curl -s ipinfo.io | jq -r '.ip')&server_name=$(hostname)" | bash
        </pre>
      </div>
    </div>
  </div>
</div>
<div class="card mb-4" style="border-top:1px solid #becdcf;background-color:#fff3cd;">
  <div class="card-body">
    <div class="row">
      <div class="col">
        {{ __('To monitor a new Windows server, log in as administrator and execute this command line :') }}
        <br><br>
        <pre class="no-bottom-margin">
Invoke-WebRequest -Uri "{{ app_url() }}/setup/script?api_token={{ Auth::user()->sentinelApiToken() }}&server_ip=$((Invoke-RestMethod -Uri 'https://ipinfo.io').ip)&server_name=$($env:COMPUTERNAME)&platform=windows" -UseBasicParsing | Invoke-Expression
        </pre>
      </div>
    </div>
  </div>
</div>
@endif
@if(Auth::user()->canListServers())
<div class="card">
  @if(Auth::user()->canManageServers() && $is_yunohost)
  <div class="card-header d-flex flex-row">
    <div class="d-flex align-content-end">
      <h6 class="m-0">
        <a href="{{ route('ynh.servers.create') }}">
          {{ __('+ new') }}
        </a>
      </h6>
    </div>
  </div>
  @endif
  @if($servers->isEmpty())
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
        <th class="ps-4" width="25px"></th>
        <th>{{ __('Name') }}</th>
        <th>{{ __('IP') }}</th>
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
            <a href="{{ route('ynh.servers.edit', $server->id) }}">
              {{ $server->name }}
            </a>
          </span>
          <div class="text-muted">
            {{ isset($os_infos[$server->id]) && $os_infos[$server->id]->count() >= 1 ? $os_infos[$server->id][0]->os :
            '-' }}
          </div>
        </td>
        <td>
          <span class="font-lg mb-3 fw-bold">
          {{ $server->ip() }}
          </span>
          <div class="text-muted">
            {{ $server->ipv6() }}
          </div>
        </td>
        <td>
          @if($server->isYunoHost())
          {{ $server->domain()?->name }}
          @endif
        </td>
        <td>
          @if($server->isYunoHost())
          <a href="{{ route('ynh.servers.edit', $server->id) }}?tab=domains">
            {{ $server->domains->count() }}
          </a>
          @endif
        </td>
        <td>
          @if($server->isYunoHost())
          <a href="{{ route('ynh.servers.edit', $server->id) }}?tab=applications">
            {{ $server->applications->count() }}
          </a>
          @endif
        </td>
        <td>
          @if($server->isYunoHost())
          <a href="{{ route('ynh.servers.edit', $server->id) }}?tab=users">
            {{ $server->users->count() }}
          </a>
          @endif
        </td>
        <td class="text-end">
          @if(Auth::user()->canManageServers() && $server->isYunoHost() && $server->isReady() &&
          !$server->addedWithCurl())
          <a id="refresh-{{ $server->id }}"
             onclick="refresh('{{ $server->id }}')"
             class="cursor-pointer"
             title="refresh">
            <span class=refresh>&#x27f3;</span>
          </a>
          @endif
        </td>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
<script>

  function refresh(serverId) {

    const refreshBtn = document.getElementById(`refresh-${serverId}`);

    if (refreshBtn.classList.contains('loading')) {
      return;
    }

    refreshBtn.classList.add('loading');
    refreshBtn.innerHTML = '<span class=refresh>&#x25cc;</span>';

    pullServerInfosApiCall(serverId, () => {
      refreshBtn.innerHTML = '<span class=refresh>&#x27f3;</span>';
      refreshBtn.classList.remove('loading');
      setTimeout(() => window.location.reload(), 5000);
    });
  }

</script>
@endif