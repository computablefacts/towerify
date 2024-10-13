@if(Auth::user()->canListUsers())
<div class="card card-accent-secondary tw-card">
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
    <table class="table table-hover" style="margin-bottom:0">
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
      @foreach($users as $user)
      <tr>
        <td>
          <span class="font-lg mb-3 fw-bold">
            {{ isset($user->fullname) ? $user->fullname : $user->name }}
          </span>
        </td>
        <td>
          {{ isset($user->username) ? $user->username : '-' }}
        </td>
        <td>
          <a href="mailto:{{ $user->email }}" target="_blank">
            {{ $user->email }}&nbsp;&nbsp;<i class="zmdi zmdi-open-in-new"></i>
          </a>
        </td>
        <td>
          @if(Auth::user()->canManageUsers())
          @php
          $userAvailablePermissions = $server ? $server->availablePermissionsYnh($user) :
          \App\Models\YnhPermission::availablePermissions($user);
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
                  <?php $serverId = $server ? $server->id : $permission->server_id; ?>
                  <?php $serverName = $server ? $server->name : $permission->server_name; ?>
                  <?php $permissionId = $server ? $permission : $permission->permission; ?>
                  <?php $userId = $server ? $user->id : $permission->ynh_user_id; ?>
                <a onclick="addUserPermission('{{ $serverId }}', '{{ $userId }}', '{{ $permissionId }}')"
                   class="dropdown-item">
                  @if(\Illuminate\Support\Str::after($permissionId, '.') === 'main')
                  {{ \Illuminate\Support\Str::before($permissionId, '.') }} / {{ $serverName }}
                  @else
                  {{ \Illuminate\Support\Str::before($permissionId, '.') }}
                  &nbsp;({{ \Illuminate\Support\Str::after($permissionId, '.') }}) / {{ $serverName }}
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
          $userCurrentPermissions = $server ? $server->currentPermissionsYnh($user) :
          \App\Models\YnhPermission::currentPermissions($user);
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
                  <?php $serverId = $server ? $server->id : $permission->server_id; ?>
                  <?php $serverName = $server ? $server->name : $permission->server_name; ?>
                  <?php $permissionId = $server ? $permission : $permission->permission; ?>
                  <?php $userId = $server ? $user->id : $permission->ynh_user_id; ?>
                <a
                  onclick="removeUserPermission('{{ $serverId }}', '{{ $userId }}', '{{ $permissionId }}')"
                  class="dropdown-item">
                  @if(\Illuminate\Support\Str::after($permissionId, '.') === 'main')
                  {{ \Illuminate\Support\Str::before($permissionId, '.') }} / {{ $serverName }}
                  @else
                  {{ \Illuminate\Support\Str::before($permissionId, '.') }}
                  &nbsp;({{ \Illuminate\Support\Str::after($permissionId, '.') }}) / {{ $serverName }}
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
    axios.post(`/ynh/servers/${serverId}/users/${userId}/permissions/${permission}`).then(function (response) {
      if (response.data.success) {
        toaster.toastSuccess(response.data.success);
      } else if (response.data.error) {
        toaster.toastError(response.data.error);
      } else {
        console.log(response.data);
      }
    }).catch(error => toaster.toastAxiosError(error));
  }

  function removeUserPermission(serverId, userId, permission) {
    axios.delete(`/ynh/servers/${serverId}/users/${userId}/permissions/${permission}`).then(function (response) {
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