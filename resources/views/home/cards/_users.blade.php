@if(Auth::user()->canListUsers())
<div class="card card-accent-secondary tw-card">
  <div class="card-header">
    <h3 class="m-0"><b>{{ __('Users') }}</b></h3>
  </div>
  <div id="result-4" class="alert alert-dismissible fade show m-2" style="display:none;">
    <button type="button" class="btn-close" aria-label="Close" onclick="closeResult4()"></button>
    <span id="result-message-4"></span>
  </div>
  @if($users->isEmpty())
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
        <th>
          <i class="zmdi zmdi-long-arrow-down"></i>&nbsp;{{ __('Name') }}
        </th>
        <th>{{ __('Username') }}</th>
        <th>{{ __('Email') }}</th>
        <th></th>
        <th></th>
      </tr>
      </thead>
      <tbody>
      @foreach($users->sortBy('fullname', SORT_NATURAL|SORT_FLAG_CASE) as $user)
      <tr>
        <td>
          <span class="font-lg mb-3 fw-bold">
            {{ $user->fullname }}
          </span>
        </td>
        <td>
          {{ $user->username }}
        </td>
        <td>
          <a href="mailto:{{ $user->email }}" target="_blank">
            {{ $user->email }}&nbsp;&nbsp;<i class="zmdi zmdi-open-in-new"></i>
          </a>
        </td>
        <td>
          @if(Auth::user()->canManageUsers())
          @php
          $userAvailablePermissions = $server->availablePermissionsYnh($user);
          @endphp
          @if(!$userAvailablePermissions->isEmpty())
          <div class="card-actionbar">
            <div class="btn-group">
              <button type="button"
                      class="btn btn-sm btn-outline-success dropdown-toggle dropdown-toggle-split"
                      data-bs-toggle="dropdown"
                      aria-expanded="true">
                {{ __('Add') }}
              </button>
              <div class="dropdown-menu" x-placement="bottom-start">
                @foreach($userAvailablePermissions as $permission)
                <a onclick="addUserPermission('{{ $server->id }}', '{{ $user->id }}', '{{ $permission }}')"
                   class="dropdown-item">
                  @if(\Illuminate\Support\Str::after($permission, '.') === 'main')
                  {{ \Illuminate\Support\Str::before($permission, '.') }}
                  @else
                  {{ \Illuminate\Support\Str::before($permission, '.') }}
                  &nbsp;({{ \Illuminate\Support\Str::after($permission, '.') }})
                  @endif
                </a>
                @endforeach
              </div>
            </div>
          </div>
          @endif
          @endif
        </td>
        <td>
          @if(Auth::user()->canManageUsers())
          @php
          $userCurrentPermissions = $server->currentPermissionsYnh($user);
          @endphp
          @if(!$userCurrentPermissions->isEmpty())
          <div class="card-actionbar">
            <div class="btn-group">
              <button type="button"
                      class="btn btn-sm btn-outline-danger dropdown-toggle dropdown-toggle-split"
                      data-bs-toggle="dropdown"
                      aria-expanded="true">
                {{ __('Remove') }}
              </button>
              <div class="dropdown-menu" x-placement="bottom-start">
                @foreach($userCurrentPermissions as $permission)
                <a onclick="removeUserPermission('{{ $server->id }}', '{{ $user->id }}', '{{ $permission }}')"
                   class="dropdown-item">
                  @if(\Illuminate\Support\Str::after($permission, '.') === 'main')
                  {{ \Illuminate\Support\Str::before($permission, '.') }}
                  @else
                  {{ \Illuminate\Support\Str::before($permission, '.') }}
                  &nbsp;({{ \Illuminate\Support\Str::after($permission, '.') }})
                  @endif
                </a>
                @endforeach
              </div>
            </div>
          </div>
          @endif
          @endif
        </td>
      </tr>
      <tr>
        <td colspan="5">
          @php
          $userPermissions = $user
          ->permissions
          ->filter(fn($perm) => $perm->application->ynh_server_id === $server->id)
          ->sortBy('application.name');
          @endphp
          @foreach($userPermissions as $permission)
          <span class="tw-pill rounded-pill bg-primary">
            <a href="https://{{ $permission->application->path }}" target="_blank" class="text-white">
              {{ $permission->application->name }}
            </a>
          </span>
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

  function closeResult4() {
    const resultDiv = document.getElementById('result-4');
    resultDiv.style.display = 'none';
  }

  function addUserPermission(serverId, userId, permission) {

    const resultDiv = document.getElementById('result-4');
    const messageSpan = document.getElementById('result-message-4');

    axios.post(`/ynh/servers/${serverId}/users/${userId}/permissions/${permission}`)
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

  function removeUserPermission(serverId, userId, permission) {

    const resultDiv = document.getElementById('result-4');
    const messageSpan = document.getElementById('result-message-4');

    axios.delete(`/ynh/servers/${serverId}/users/${userId}/permissions/${permission}`)
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

</script>
@endif