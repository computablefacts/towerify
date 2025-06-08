@if(Auth::user()->canListUsers())
<div class="card">
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
    <table class="table table-hover no-bottom-margin">
      <thead>
      <tr>
        <th>{{ __('Name') }}</th>
        <th>{{ __('Username') }}</th>
        <th>{{ __('Email') }}</th>
        <th>{{ __('Audit Report') }}</th>
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
            {{ $user->email }}
          </a>
        </td>
        <td>
          <input
            type="checkbox"
            data-user-id="{{ $user->id }}"
            {{ $user->gets_audit_report ? 'checked' : '' }}>
        </td>
        <td>
          @if(Auth::user()->canManageUsers())
          @php
          $userAvailablePermissions = $server ? $server->availablePermissionsYnh($user) :
          \App\Models\YnhPermission::availablePermissions($user);
          @endphp
          @if($userAvailablePermissions->isNotEmpty())
          <div class="card-actionbar">
            <div class="dropdown">
              <button type="button"
                      class="btn btn-sm btn-outline-success dropdown-toggle"
                      data-bs-toggle="dropdown"
                      aria-expanded="false">
                {{ __('Add') }}
              </button>
              <ul class="dropdown-menu" role="menu">
                @foreach($userAvailablePermissions as $permission)
                  <?php $serverId = $server ? $server->id : $permission->server_id; ?>
                  <?php $serverName = $server ? $server->name : $permission->server_name; ?>
                  <?php $permissionId = $server ? $permission : $permission->permission; ?>
                  <?php $userId = $server ? $user->id : $permission->ynh_user_id; ?>
                <li>
                  <a onclick="addUserPermissionApiCall('{{ $serverId }}', '{{ $userId }}', '{{ $permissionId }}')"
                     class="dropdown-item">
                    @if(\Illuminate\Support\Str::after($permissionId, '.') === 'main')
                    {{ \Illuminate\Support\Str::before($permissionId, '.') }} / {{ $serverName }}
                    @else
                    {{ \Illuminate\Support\Str::before($permissionId, '.') }}
                    &nbsp;({{ \Illuminate\Support\Str::after($permissionId, '.') }}) / {{ $serverName }}
                    @endif
                  </a>
                </li>
                @endforeach
              </ul>
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
            <div class="dropdown">
              <button type="button"
                      class="btn btn-sm btn-outline-danger dropdown-toggle"
                      data-bs-toggle="dropdown"
                      aria-expanded="false">
                {{ __('Remove') }}
              </button>
              <ul class="dropdown-menu" role="menu">
                @foreach($userCurrentPermissions as $permission)
                  <?php $serverId = $server ? $server->id : $permission->server_id; ?>
                  <?php $serverName = $server ? $server->name : $permission->server_name; ?>
                  <?php $permissionId = $server ? $permission : $permission->permission; ?>
                  <?php $userId = $server ? $user->id : $permission->ynh_user_id; ?>
                <li>
                  <a
                    onclick="removeUserPermissionApiCall('{{ $serverId }}', '{{ $userId }}', '{{ $permissionId }}')"
                    class="dropdown-item">
                    @if(\Illuminate\Support\Str::after($permissionId, '.') === 'main')
                    {{ \Illuminate\Support\Str::before($permissionId, '.') }} / {{ $serverName }}
                    @else
                    {{ \Illuminate\Support\Str::before($permissionId, '.') }}
                    &nbsp;({{ \Illuminate\Support\Str::after($permissionId, '.') }}) / {{ $serverName }}
                    @endif
                  </a>
                </li>
                @endforeach
              </ul>
            </div>
          </div>
          @endif
          @endif
        </td>
      </tr>
      @php
      $userPermissions = $user
      ->permissions
      ->where('is_user_specific', true)
      ->filter(fn($perm) => $perm->application->ynh_server_id === $server->id)
      ->sortBy('application.name');
      @endphp
      @if($userPermissions->isNotEmpty())
      <tr>
        <td colspan="5">
          @foreach($userPermissions as $permission)
          <span class="lozenge new">
            <a href="https://{{ $permission->application->path }}" target="_blank">
              {{ $permission->application->name }}
            </a>
          </span>&nbsp;
          @endforeach
        </td>
      </tr>
      @endif
      @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
<script>

  document.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
    checkbox.addEventListener('change', (event) => toggleGetsAuditReport(event.target.getAttribute('data-user-id')));
  });

  function toggleGetsAuditReport(userId) {
    axios.get(`/ynh/users/${userId}/toggle-gets-audit-report`).then(function (response) {
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