<div class="card card-accent-secondary tw-card">
  <div class="card-header">
    <h3 class="m-0"><b>{{ __('Applications') }}</b></h3>
  </div>
  @if($apps->isEmpty())
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
        <th width="70px"></th>
        <th>{{ __('Name') }}</th>
        <th>{{ __('Version') }}</th>
        <th>{{ __('Server') }}</th>
      </tr>
      </thead>
      <tbody>
      @foreach($apps as $app)
      <tr>
        @php
        $product = \App\Models\Product::where('sku', $app->application->sku)->first()
        @endphp
        <td>
          @if($product)
          <img
            src="{{ \App\Helpers\ProductOrProductVariant::create($product)->getThumbnailUrl() ?: $defaultThumbnail }}"
            alt="{{ $app->application->name }}"
            class="mw-100" style="height: 2.5em;"/>
          @endif
        </td>
        <td>
          <span class="font-lg mb-3 fw-bold">
            <a href="https://{{ $app->application->path }}" target="_blank">
              @if($app->application->description === 'Deploy an app with Docker Swarm')
              {{ $app->application->sku }}
              @else
              {{ $app->application->name }}
              @endif
              &nbsp;&nbsp;<i class="zmdi zmdi-open-in-new"></i>
            </a>
          </span>
          <div class="text-muted">
            {{ $app->application->description }}
          </div>
        </td>
        <td>
          {{ $app->application->version }}
        </td>
        <td>
          <span class="font-lg mb-3 fw-bold">
            @if(Auth::user()->canListServers())
            <a href="{{ route('ynh.servers.edit', $app->application->server) }}">
              {{ $app->application->server->name }}
            </a>
            @else
            {{ $app->application->server->name }}
            @endif
          </span>
          <div class="text-muted">
            {{ $app->application->server->ip_address }}
          </div>
        </td>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>