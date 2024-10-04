@if(Auth::user()->canListUsers())
<div class="card card-accent-secondary tw-card">
  <div class="card-header">
    <h3 class="m-0">
      <b>
        {{ __('Towerify\'s Users') }}
      </b>
    </h3>
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
          <i class="zmdi zmdi-long-arrow-down"></i>&nbsp;{{ __('Username') }}
        </th>
        <th>{{ __('Email') }}</th>
        <th></th>
        <th></th>
      </tr>
      </thead>
      <tbody>
      @foreach($users->sortBy('name', SORT_NATURAL|SORT_FLAG_CASE) as $user)
      <tr>
        <td>
          {{ $user->name }}
        </td>
        <td>
          <a href="mailto:{{ $user->email }}" target="_blank">
            {{ $user->email }}&nbsp;&nbsp;<i class="zmdi zmdi-open-in-new"></i>
          </a>
        </td>
        <td>
          @if(Auth::user()->canManageUsers())
          @php
          $userAvailablePermissions = \App\Models\YnhPermission::availablePermissions($user);
          @endphp
          @if(!$userAvailablePermissions->isEmpty())
          <div class="card-actionbar">
            <div class="btn-group">
              <button type="button"
                      class="btn btn-sm btn-outline-success dropdown-toggle dropdown-toggle-split"
                      data-bs-toggle="dropdown"
                      aria-expanded="true">
                {{ __('Add Access') }}
              </button>
              <div class="dropdown-menu" x-placement="bottom-start">
                @foreach($userAvailablePermissions as $permission)
                <a
                  onclick="addUserPermission('{{ $permission->server_id }}', '{{ $user->id }}', '{{ $permission->permission }}')"
                  class="dropdown-item">
                  @if(\Illuminate\Support\Str::after($permission->permission, '.') === 'main')
                  {{ \Illuminate\Support\Str::before($permission->permission, '.') }} / {{ $permission->server_name }}
                  @else
                  {{ \Illuminate\Support\Str::before($permission->permission, '.') }}
                  &nbsp;({{ \Illuminate\Support\Str::after($permission->permission, '.') }}) / {{
                  $permission->server_name }}
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
          $userCurrentPermissions = \App\Models\YnhPermission::currentPermissions($user);
          @endphp
          @if(!$userCurrentPermissions->isEmpty())
          <div class="card-actionbar">
            <div class="btn-group">
              <button type="button"
                      class="btn btn-sm btn-outline-danger dropdown-toggle dropdown-toggle-split"
                      data-bs-toggle="dropdown"
                      aria-expanded="true">
                {{ __('Remove Access') }}
              </button>
              <div class="dropdown-menu" x-placement="bottom-start">
                @foreach($userCurrentPermissions as $permission)
                <a
                  onclick="removeUserPermission('{{ $permission->server_id }}', '{{ $permission->ynh_user_id }}', '{{ $permission->permission }}')"
                  class="dropdown-item">
                  @if(\Illuminate\Support\Str::after($permission->permission, '.') === 'main')
                  {{ \Illuminate\Support\Str::before($permission->permission, '.') }} / {{ $permission->server_name }}
                  @else
                  {{ \Illuminate\Support\Str::before($permission->permission, '.') }}
                  &nbsp;({{ \Illuminate\Support\Str::after($permission->permission, '.') }}) / {{
                  $permission->server_name }}
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
      @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
<script>

  function addUserPermission(serverId, userId, permission) {
    axios.post(`/ynh/servers/${serverId}/twr-users/${userId}/permissions/${permission}`).then(function (response) {
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