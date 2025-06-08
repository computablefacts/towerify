@if(Auth::user()->canManageServers() && $server->secret)
<div class="card mb-4" style="border-top:1px solid #becdcf;background-color:#fff3cd;">
  <div class="card-body">
    <div class="row">
      <div class="col">
        {{ __('To configure or restore metrics and security event collection on this server, execute the following
        command with root privileges:') }}
        <br><br>
        @if($server->platform === App\Enums\OsqueryPlatformEnum::WINDOWS)
        <pre class="m-0">
Invoke-WebRequest -Uri '<a href="{{ app_url() }}/update/{{ $server->secret }}">{{ app_url() }}/update/{{ $server->secret }}</a>' -UseBasicParsing | Invoke-Expression</pre>
        @else
        <pre class="m-0">
curl -s <a href="{{ app_url() }}/update/{{ $server->secret }}">{{ app_url() }}/update/{{ $server->secret }}</a> | bash</pre>
        @endif
        <br>
        {{ __('The command is idempotent, meaning you can run it multiple times, but it will produce the same result
        each time without creating additional changes or effects beyond the initial execution. This ensures consistency
        and prevents duplication of settings or data each time it is run.') }}
      </div>
    </div>
  </div>
</div>
@endif
@if(Auth::user()->canListServers() && $server->isYunoHost())
{!! Form::model($server, [
'route' => ['ynh.servers.edit', $server],
'method' => 'POST',
'id' => 'server-update-form'
]) !!}
<div class="card">
  <div class="card-header">
    <h3 class="m-0">
      <b>
        <span class="{{ $server->is_ready ? 'tw-dot-green' : 'tw-dot-red' }}" style="vertical-align:middle;"></span>
        &nbsp;{{ __('Server Settings') }}
      </b>
    </h3>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="mb-3 col-md">
        <div class="form-control-label">
          {{ Form::label('name', __('Your server name')) }}
        </div>
        {{ Form::text('name', null, [
        !Auth::user()->canManageServers() || $server->isReady() ? 'readonly' : '',
        'class' => 'form-control' . ($errors->has('name') ? ' is-invalid' : ''),
        'placeholder' => __('Name')
        ]) }}
        @if ($errors->has('name'))
        <div class="invalid-feedback">
          {{ $errors->first('name') }}
        </div>
        @endif
      </div>
    </div>
    <div class="row">
      <div class="mb-3 col-md-6">
        <div class="form-control-label">
          {{ Form::label('ip_address', __('Your server IP address')) }}
        </div>
        {{ Form::text('ip_address', null, [
        !Auth::user()->canManageServers() || $server->isReady() ? 'readonly' : '',
        'class' => 'form-control' . ($errors->has('ip_address') ? ' is-invalid' : ''),
        'placeholder' => __('IP Address')
        ]) }}
        @if ($errors->has('ip_address'))
        <div class="invalid-feedback">
          {{ $errors->first('ip_address') }}
        </div>
        @endif
      </div>
      <div class="mb-3 col-md-6">
        <div class="form-control-label">
          {{ Form::label('principal_domain', __('Your server domain')) }}
        </div>
        {{ Form::text('principal_domain', $server->domain()?->name, [
        !Auth::user()->canManageServers() || $server->isReady() ? 'readonly' : '',
        'class' => 'form-control' . ($errors->has('principal_domain') ? ' is-invalid' : ''),
        'placeholder' => __('Domain')
        ]) }}
        @if ($errors->has('principal_domain'))
        <div class="invalid-feedback">
          {{ $errors->first('principal_domain') }}
        </div>
        @endif
      </div>
    </div>
    <div class="row">
      <div class="mb-3 col-md-6">
        <div class="form-control-label">
          {{ Form::label('ssh_port', __('Your server port waiting for a SSH connection')) }}
        </div>
        {{ Form::number('ssh_port', $server->port ?? 22, [
        !Auth::user()->canManageServers() || $server->isReady() ? 'readonly' : '',
        'class' => 'form-control' . ($errors->has('ssh_port') ? ' is-invalid' : ''),
        'placeholder' => __('Port')
        ]) }}
        @if ($errors->has('ssh_port'))
        <div class="invalid-feedback">
          {{ $errors->first('ssh_port') }}
        </div>
        @endif
      </div>
      <div class="mb-3 col-md-6">
        <div class="form-control-label">
          {{ Form::label('ssh_username', __('Your server SSH username (with sudo privileges)')) }}
        </div>
        {{ Form::text('ssh_username', null, [
        !Auth::user()->canManageServers() || $server->isReady() ? 'readonly' : '',
        'class' => 'form-control' . ($errors->has('ssh_username') ? ' is-invalid' : ''),
        'placeholder' => __('Username')
        ]) }}
        @if ($errors->has('ssh_username'))
        <div class="invalid-feedback">
          {{ $errors->first('ssh_username') }}
        </div>
        @endif
      </div>
    </div>
    {{-- Authorized Key --}}
    <div class="mb-3">
      <div class="d-flex justify-content-between align-items-center">
        <label for="authorizedKey" class="mb-0 form-control-label">
          {{ __('Log-in to your server using the username above and execute the following command :') }}
        </label>
        <button type="button" onclick="copyToClipboard()" class="m-1 btn" title="{{ __('Copy') }}">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
               class="bi bi-clipboard" viewBox="0 0 16 16">
            <path
              d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1z"/>
            <path
              d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0z"/>
          </svg>
        </button>
      </div>
      <textarea id="authorizedKey" class="form-control" rows="5" readonly>{{ $server->ssh_public_key ? $server->sshKeyPair()->echoAuthorizedKey() : '-' }}</textarea>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-between">
    <div>
      <button type="button" onclick="testSshConnection()" class="btn btn-outline-primary">
        {{ __('Test SSH Connection') }}
      </button>
      @if(Auth::user()->canManageServers() && !$server->addedWithCurl() && !$server->isReady())
      <button type="button" onclick="setupHost()" class="btn btn-outline-primary mx-4">
        {{ __('Configure Host') }}
      </button>
      @endif
    </div>
    @if(Auth::user()->canManageServers())
    <div>
      <a href="#" onclick="removeFromInventory()" class="btn btn-link text-muted">
        {{ __('Delete') }}
      </a>
    </div>
    @endif
  </div>
</div>
{!! Form::close() !!}
<script>

  function copyToClipboard() {
    const copyText = document.getElementById("authorizedKey").value;
    navigator.clipboard.writeText(copyText)
    .then(() => toaster.toastSuccess("{{ __('Text successfully copied to clipboard.') }}"))
    .catch(error => toaster.toastError("{{ __('An error occurred.') }}"));
  }

  function testSshConnection() {

    const ip = document.querySelector('[name="ip_address"]').value;
    const port = document.querySelector('[name="ssh_port"]').value;
    const username = document.querySelector('[name="ssh_username"]').value;

    testSshConnectionApiCall('{{ $server->id }}', ip, port, username);
  }

  function setupHost() {

    const name = document.querySelector('[name="name"]').value;
    const ip = document.querySelector('[name="ip_address"]').value;
    const port = document.querySelector('[name="ssh_port"]').value;
    const username = document.querySelector('[name="ssh_username"]').value;
    const domain = document.querySelector('[name="principal_domain"]').value;

    configureServerApiCall('{{ $server->id }}', name, domain, ip, port, username);
  }

  function removeFromInventory() {
    const response = confirm('Are you sure you want to remove {{ $server->name }} from the inventory?');
    if (response) {
      deleteServerApiCall('{{ $server->id }}');
    }
  }

</script>
@endif