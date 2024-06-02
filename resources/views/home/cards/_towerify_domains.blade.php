@if(Auth::user()->canListServers())
<div class="card card-accent-secondary tw-card">
  <div class="card-header d-flex flex-row">
    <div class="align-items-start">
      <h3 class="m-0">
        {{ __('Domains') }}
      </h3>
    </div>
  </div>
  @if($domains->isEmpty())
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
        <th>{{ __('IP V4') }}</th>
        <th>{{ __('IP V6') }}</th>
        <th>{{ __('Domain') }}</th>
        <th></th>
      </tr>
      </thead>
      <tbody>
      @foreach($domains->sortBy('name') as $domain)
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
          <span class="tw-pill rounded-pill bg-success float-end">
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