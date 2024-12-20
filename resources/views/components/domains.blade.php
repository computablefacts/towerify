@if(Auth::user()->canListServers())
<div class="card">
  @if($domains->isEmpty())
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
        <th>{{ __('IP V4') }}</th>
        <th>{{ __('IP V6') }}</th>
        <th>{{ __('Name') }}</th>
        <th></th>
      </tr>
      </thead>
      <tbody>
      @foreach($domains as $domain)
      <tr>
        <td>
          <span class="font-lg mb-3 fw-bold">
            <a href="{{ route('ynh.servers.edit', $domain->server->id) }}">
              {{ $domain->server->name }}
            </a>
          </span>
        </td>
        <td>
          {{ $domain->server->ip() }}
        </td>
        <td>
          {{ $domain->server->ipv6() }}
        </td>
        <td>
          {{ $domain->name }}
        </td>
        <td>
          @if($domain->is_principal)
          <span class="lozenge success float-end">
            principal
          </span>
          @endif
        </td>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
@endif