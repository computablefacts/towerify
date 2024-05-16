@if(Auth::user()->canListServers())
@include('home.cards._adversarymeter')
@if($security_events['authorized_keys'])
<div class="card card-accent-secondary tw-card mt-4">
  <div class="card-header">
    <h3 class="m-0"><b>{{ __('Authorized Keys (Last 20 Events)') }}</b></h3>
  </div>
  @if(count($security_events['authorized_keys']) === 0)
  <div class="card-body">
    <div class="p-3" style="background-color:#fff3cd;">
      {{ __('An access key has been added or removed.') }}
    </div>
    <div class="row">
      <div class="col">
        None.
      </div>
    </div>
  </div>
  @else
  <div class="card-body p-0">
    <div class="p-3" style="background-color:#fff3cd;">
      {{ __('An access key has been added or removed.') }}
    </div>
    <table class="table table-hover">
      <thead>
      <tr>
        <th>{{ __('Server') }}</th>
        <th>{{ __('Timestamp') }}</th>
        <th>{{ __('Key File') }}</th>
        <th>{{ __('Key') }}</th>
        <th>{{ __('Key Comment') }}</th>
        <th>{{ __('Algorithm') }}</th>
        <th>{{ __('Event') }}</th>
      </tr>
      </thead>
      <tbody>
      @foreach($security_events['authorized_keys'] as $event)
      <tr>
        <td>
          <span class="font-lg mb-3 fw-bold">
            <a href="{{ route('ynh.servers.edit', $event->ynh_server_id) }}">
              {{ $event->ynh_server_name }}
            </a>
          </span>
        </td>
        <td>
          {{ $event->timestamp }}
        </td>
        <td>
          {{ $event->key_file }}
        </td>
        <td>
          {{ \Illuminate\Support\Str::limit($event->key, 50, '...') }}
        </td>
        <td>
          {{ $event->key_comment }}
        </td>
        <td>
          {{ $event->algorithm }}
        </td>
        <td>
          <span class="tw-pill rounded-pill bg-primary">
            {{ $event->action }}
          </span>
        </td>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
@endif
@if($security_events['last_logins_and_logouts'])
<div class="card card-accent-secondary tw-card mt-4">
  <div class="card-header">
    <h3 class="m-0"><b>{{ __('Logins and Logouts (Last 20 Events)') }}</b></h3>
  </div>
  @if(count($security_events['last_logins_and_logouts']) === 0)
  <div class="card-body">
    <div class="p-3" style="background-color:#fff3cd;">
      {{ __('System logins and logouts.') }}
    </div>
    <div class="row">
      <div class="col">
        None.
      </div>
    </div>
  </div>
  @else
  <div class="card-body p-0">
    <div class="p-3" style="background-color:#fff3cd;">
      {{ __('System logins and logouts.') }}
    </div>
    <table class="table table-hover">
      <thead>
      <tr>
        <th>{{ __('Server') }}</th>
        <th>{{ __('Timestamp') }}</th>
        <th>{{ __('PID') }}</th>
        <th>{{ __('Entry Host') }}</th>
        <th>{{ __('Entry Timestamp') }}</th>
        <th>{{ __('Entry Terminal') }}</th>
        <th>{{ __('Entry Type') }}</th>
        <th>{{ __('Entry Username') }}</th>
        <th>{{ __('Event') }}</th>
      </tr>
      </thead>
      <tbody>
      @foreach($security_events['last_logins_and_logouts'] as $event)
      <tr>
        <td>
          <span class="font-lg mb-3 fw-bold">
            <a href="{{ route('ynh.servers.edit', $event->ynh_server_id) }}">
              {{ $event->ynh_server_name }}
            </a>
          </span>
        </td>
        <td>
          {{ $event->timestamp }}
        </td>
        <td>
          {{ $event->pid }}
        </td>
        <td>
          {{ $event->entry_host }}
        </td>
        <td>
          {{ $event->entry_timestamp }}
        </td>
        <td>
          {{ $event->entry_terminal }}
        </td>
        <td>
          {{ $event->entry_type }}
        </td>
        <td>
          {{ $event->entry_username }}
        </td>
        <td>
          <span class="tw-pill rounded-pill bg-primary">
            {{ $event->action }}
          </span>
        </td>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
@endif
@if($security_events['users'])
<div class="card card-accent-secondary tw-card mt-4">
  <div class="card-header">
    <h3 class="m-0"><b>{{ __('User Accounts (Last 20 Events)') }}</b></h3>
  </div>
  @if(count($security_events['users']) === 0)
  <div class="card-body">
    <div class="p-3" style="background-color:#fff3cd;">
      {{ __('System logins and logouts.') }}
    </div>
    <div class="row">
      <div class="col">
        None.
      </div>
    </div>
  </div>
  @else
  <div class="card-body p-0">
    <div class="p-3" style="background-color:#fff3cd;">
      {{ __('A user has been added or removed.') }}
    </div>
    <table class="table table-hover">
      <thead>
      <tr>
        <th>{{ __('Server') }}</th>
        <th>{{ __('Timestamp') }}</th>
        <th>{{ __('User ID') }}</th>
        <th>{{ __('Group ID') }}</th>
        <th>{{ __('Username') }}</th>
        <th>{{ __('Home Directory') }}</th>
        <th>{{ __('Default Shell') }}</th>
        <th>{{ __('Event') }}</th>
      </tr>
      </thead>
      <tbody>
      @foreach($security_events['users'] as $event)
      <tr>
        <td>
          <span class="font-lg mb-3 fw-bold">
            <a href="{{ route('ynh.servers.edit', $event->ynh_server_id) }}">
              {{ $event->ynh_server_name }}
            </a>
          </span>
        </td>
        <td>
          {{ $event->timestamp }}
        </td>
        <td>
          {{ $event->user_id }}
        </td>
        <td>
          {{ $event->group_id }}
        </td>
        <td>
          {{ $event->username }}
        </td>
        <td>
          {{ $event->home_directory }}
        </td>
        <td>
          {{ $event->default_shell }}
        </td>
        <td>
          <span class="tw-pill rounded-pill bg-primary">
            {{ $event->action }}
          </span>
        </td>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
@endif
@if($security_events['kernel_modules'])
<div class="card card-accent-secondary tw-card mt-4">
  <div class="card-header">
    <h3 class="m-0"><b>{{ __('Kernel Modules (Last 20 Events)') }}</b></h3>
  </div>
  @if(count($security_events['kernel_modules']) === 0)
  <div class="card-body">
    <div class="p-3" style="background-color:#fff3cd;">
      {{ __('A kernel module has been added or removed.') }}
    </div>
    <div class="row">
      <div class="col">
        None.
      </div>
    </div>
  </div>
  @else
  <div class="card-body p-0">
    <div class="p-3" style="background-color:#fff3cd;">
      {{ __('A kernel module has been added or removed.') }}
    </div>
    <table class="table table-hover">
      <thead>
      <tr>
        <th>{{ __('Server') }}</th>
        <th>{{ __('Timestamp') }}</th>
        <th>{{ __('Name') }}</th>
        <th>{{ __('Size') }}</th>
        <th>{{ __('Address') }}</th>
        <th>{{ __('Status') }}</th>
        <th>{{ __('Used By') }}</th>
        <th>{{ __('Event') }}</th>
      </tr>
      </thead>
      <tbody>
      @foreach($security_events['kernel_modules'] as $event)
      <tr>
        <td>
          <span class="font-lg mb-3 fw-bold">
            <a href="{{ route('ynh.servers.edit', $event->ynh_server_id) }}">
              {{ $event->ynh_server_name }}
            </a>
          </span>
        </td>
        <td>
          {{ $event->timestamp }}
        </td>
        <td>
          {{ $event->name }}
        </td>
        <td>
          {{ $event->size }}
        </td>
        <td>
          {{ $event->address }}
        </td>
        <td>
          {{ $event->status }}
        </td>
        <td>
          {{ $event->used_by }}
        </td>
        <td>
          <span class="tw-pill rounded-pill bg-primary">
            {{ $event->action }}
          </span>
        </td>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
@endif
@if($security_events['suid_bin'])
<div class="card card-accent-secondary tw-card mt-4">
  <div class="card-header">
    <h3 class="m-0"><b>{{ __('Suid Binaries (Last 20 Events)') }}</b></h3>
  </div>
  @if(count($security_events['suid_bin']) === 0)
  <div class="card-body">
    <div class="p-3" style="background-color:#fff3cd;">
      {{ __('A suid binary has been detected (often used in backdoors).') }}
    </div>
    <div class="row">
      <div class="col">
        None.
      </div>
    </div>
  </div>
  @else
  <div class="card-body p-0">
    <div class="p-3" style="background-color:#fff3cd;">
      {{ __('A suid binary has been detected (often used in backdoors).') }}
    </div>
    <table class="table table-hover">
      <thead>
      <tr>
        <th>{{ __('Server') }}</th>
        <th>{{ __('Timestamp') }}</th>
        <th>{{ __('Path') }}</th>
        <th>{{ __('Group') }}</th>
        <th>{{ __('Username') }}</th>
        <th>{{ __('Permissions') }}</th>
        <th>{{ __('Event') }}</th>
      </tr>
      </thead>
      <tbody>
      @foreach($security_events['suid_bin'] as $event)
      <tr>
        <td>
          <span class="font-lg mb-3 fw-bold">
            <a href="{{ route('ynh.servers.edit', $event->ynh_server_id) }}">
              {{ $event->ynh_server_name }}
            </a>
          </span>
        </td>
        <td>
          {{ $event->timestamp }}
        </td>
        <td>
          {{ $event->path }}
        </td>
        <td>
          {{ $event->groupname }}
        </td>
        <td>
          {{ $event->username }}
        </td>
        <td>
          {{ $event->permissions }}
        </td>
        <td>
          <span class="tw-pill rounded-pill bg-primary">
            {{ $event->action }}
          </span>
        </td>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
@endif
@endif