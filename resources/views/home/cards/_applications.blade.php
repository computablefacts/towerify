@if(Auth::user()->canListApps())
<div class="card card-accent-secondary tw-card">
  <div class="card-header">
    <h3 class="m-0"><b>{{ __('Applications Deployed') }}</b></h3>
  </div>
  @if($applications->isEmpty())
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
          <i class="zmdi zmdi-long-arrow-down"></i>&nbsp;{{ __('Name') }}
        </th>
        <th>{{ __('Description') }}</th>
        <th>{{ __('Sku') }}</th>
        <th>{{ __('Version') }}</th>
        <th></th>
      </tr>
      </thead>
      <tbody>
      @foreach($applications->sortBy('name', SORT_NATURAL|SORT_FLAG_CASE) as $app)
      <tr>
        <td>
          <span class="font-lg mb-3 fw-bold">
            <a href="https://{{ $app->path }}" target="_blank">
              {{ $app->name }}&nbsp;&nbsp;<i class="zmdi zmdi-open-in-new"></i>
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
      axios.delete(`/ynh/servers/${serverId}/apps/${appId}`).then(function (data) {
        if (data.data.success) {
          toaster.toastSuccess(data.data.success);
        } else if (data.data.error) {
          toaster.toastError(data.data.error);
        } else {
          console.log(data.data);
        }
      }).catch(error => toaster.toastAxiosError(error));
    }
  }

</script>
@endif