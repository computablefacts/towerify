@if(Auth::user()->canListUsers())
<div class="card card-accent-secondary tw-card">
  <div class="card-header">
    <h3 class="m-0"><b>{{ __('Users') }}</b></h3>
  </div>
  @if($users->isEmpty())
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
          ->where('is_user_specific', true)
          ->filter(fn($perm) => $perm->application->ynh_server_id === $server->id)
          ->sortBy('application.name');
          @endphp
          @foreach($userPermissions as $permission)
          <span class="tw-pill rounded-pill bg-primary">
            <a href="https://{{ $permission->application->path }}" target="_blank" class="text-white">
              {{ $permission->application->name }}
            </a>
          </span>&nbsp;
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

  function addUserPermission(serverId, userId, permission) {
    axios.post(`/ynh/servers/${serverId}/users/${userId}/permissions/${permission}`).then(function (data) {
      if (data.data.success) {
        toaster.toastSuccess(data.data.success);
      } else if (data.data.error) {
        toaster.toastError(data.data.error);
      } else {
        console.log(data.data);
      }
    }).catch(error => toaster.toastAxiosError(error));
  }

  function removeUserPermission(serverId, userId, permission) {
    axios.delete(`/ynh/servers/${serverId}/users/${userId}/permissions/${permission}`).then(function (data) {
      if (data.data.success) {
        toaster.toastSuccess(data.data.success);
      } else if (data.data.error) {
        toaster.toastError(data.data.error);
      } else {
        console.log(data.data);
      }
    }).catch(error => toaster.toastAxiosError(error));
  }

</script>
@endif