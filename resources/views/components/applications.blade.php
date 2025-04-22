@if(Auth::user()->canListApps())
<div class="card">
  @if($apps->isEmpty())
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
        <th>{{ __('Server') }}</th>
        <th>{{ __('Name') }}</th>
        <th>{{ __('Description') }}</th>
        <th>{{ __('Sku') }}</th>
        <th>{{ __('Version') }}</th>
        <th></th>
      </tr>
      </thead>
      <tbody>
      @foreach($apps as $app)
      <tr>
        <td>
          <span class="font-lg mb-3 fw-bold">
            <a href="{{ route('ynh.servers.edit', $app->server->id) }}">
              {{ $app->server->name }}
            </a>
          </span>
        </td>
        <td>
          <span class="font-lg mb-3 fw-bold">
            <a href="https://{{ $app->path }}" target="_blank">
              {{ $app->name }}
            </a>
          </span>
        </td>
        <td>
          {{ $app->description }}
        </td>
        <td>
          {{ $app->sku }}
        </td>
        <td>
          {{ $app->version }}
        </td>
        <td>
          @if(Auth::user()->canManageApps())
          <button type="button"
                  onclick="uninstallApp('{{ $app->server->id }}', '{{ $app->id }}', '{{ $app->name }}')"
                  class="btn btn-xs btn-outline-danger float-end">
            {{ __('uninstall') }}
          </button>
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

  function uninstallApp(serverId, appId, appName) {
    const response = confirm(`Are you sure you want to remove ${appName} from the server?`);
    if (response) {
      uninstallAppApiCall(serverId, appId);
    }
  }

</script>
@endif