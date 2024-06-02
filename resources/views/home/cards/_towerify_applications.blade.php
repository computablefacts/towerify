@if(Auth::user()->canListApps())
<div class="card card-accent-secondary tw-card">
  <div class="card-header">
    <h3 class="m-0"><b>{{ __('Applications') }}</b></h3>
  </div>
  <div id="result-1" class="alert alert-dismissible fade show m-2" style="display:none;">
    <button type="button" class="btn-close" aria-label="Close" onclick="closeResult1()"></button>
    <span id="result-message-1"></span>
  </div>
  @if($applications->isEmpty())
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
        <th>{{ __('Server') }}</th>
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
            <a href="{{ route('ynh.servers.edit', $app->server->id) }}">
              {{ $app->server->name }}
            </a>
          </span>
        </td>
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

  function closeResult1() {
    const resultDiv = document.getElementById('result-1');
    resultDiv.style.display = 'none';
  }

  function uninstallApp(serverId, appId, appName) {

    const response = confirm(`Are you sure you want to remove ${appName} from the server?`);

    if (response) {

      const resultDiv = document.getElementById('result-1');
      const messageSpan = document.getElementById('result-message-1');

      axios.delete(`/ynh/servers/${serverId}/apps/${appId}`)
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
      });
    }
  }

</script>
@endif