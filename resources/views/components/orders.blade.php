@if(Auth::user()->canListOrders())
<div class="card card-accent-secondary tw-card">
  <div class="card-header d-flex flex-row">
    <div class="align-items-start">
      <h3 class="m-0">
        {{ __('Orders') }}
      </h3>
    </div>
    @if(Auth::user()->canBuyStuff())
    <div class="align-items-end">
      <h6 class="m-0">
        <a href="{{ route('product.index') }}">
          {{ __('+ new') }}
        </a>
      </h6>
    </div>
    @endif
  </div>
  @if($orders->isEmpty())
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
        <th class="ps-4" width="25px"></th>
        <th width="70px"></th>
        <th>
          <i class="zmdi zmdi-long-arrow-up"></i>&nbsp;{{ __('Date') }}
        </th>
        <th>{{ __('Name') }}</th>
        <th>{{ __('Categories') }}</th>
        <th>{{ __('Order') }}</th>
        <th>{{ __('Order Status') }}</th>
        <th>{{ __('Deployment Status') }}</th>
        <th></th>
      </tr>
      </thead>
      <tbody>
      @foreach($orders as $order)
      <tr>
        <td class="ps-4" width="25px">
          <span class="{{ $order->isFulfilled() ? 'tw-dot-green' : 'tw-dot-red' }}"></span>
        </td>
        <td>
          <img src="{{ $order->thumbnailUrl() ?: $defaultThumbnail }}"
               alt="{{ $order->name() }}"
               class="mw-100" style="height: 2.5em;"/>
        </td>
        <td>
          {{ $order->updated_at->format('Y-m-d H:i') }}
        </td>
        <td>
          <span class="font-lg mb-3 fw-bold">
            {{ $order->name() }}
          </span>
          <div class="text-muted">
            {{ $order->product_type->value }}
          </div>
        </td>
        <td>
          @foreach($order->taxons() as $taxon)
          <span class="tw-pill rounded-pill bg-dark">
              {{ $taxon->name }}
            </span>
          @endforeach
        </td>
        <td>
          <span class="text-muted">{{ $order->orderIdentifier() }}</span>
        </td>
        <td>
          <span class="tw-pill rounded-pill bg-{{
            $order->orderIsCompleted() ? 'success' :
              ($order->orderIsCancelled() ? 'warning' :
                ($order->orderIsProcessing() ? 'info' : 'secondary')) }}">
            {{ $order->orderStatus() }}
          </span>
        </td>
        <td>
          @if($order->isApplicationDeployable())
          <span class="tw-pill rounded-pill bg-info">
              {{ __('deployable') }}
            <span>
            @elseif($order->isApplicationDeployed())
            <span class="tw-pill rounded-pill bg-success">
              {{ __('deployed') }}
            <span>
            @elseif($order->isServerDeployable())
            <span class="tw-pill rounded-pill bg-info">
              {{ __('deployable') }}
            <span>
            @elseif($order->isServerDeployed())
            <span class="tw-pill rounded-pill bg-success">
              {{ __('deployed') }}
            <span>
            @endif
        </td>
        <td>
          @if(Auth::user()->canManageApps() && $order->isApplicationDeployable())
          <div class="card-actionbar">
            <div class="btn-group">
              <button type="button"
                      class="btn btn-sm btn-outline-success dropdown-toggle dropdown-toggle-split"
                      data-bs-toggle="dropdown"
                      aria-expanded="true">
                {{ __('Deploy') }}
              </button>
              <div class="dropdown-menu" x-placement="bottom-start">
                @foreach(\App\Models\YnhServer::forUser(Auth::user(), true) as $server)
                <a href="{{ route('ynh.servers.edit', $server->id) }}?tab=applications" class="dropdown-item">
                  {{ $server->name }} ({{ $server->ip() }})
                </a>
                @endforeach
              </div>
            </div>
          </div>
          @elseif(Auth::user()->canManageApps() && $order->isApplicationDeployed())
          <div class="card-actionbar">
            <div class="btn-group">
              <a
                href="{{ route('ynh.servers.edit', \App\Models\YnhApplication::where('ynh_order_id', $order->id)->first()->server) }}?tab=applications">
                  <span href="#" class="btn btn-sm btn-outline-success">
                    {{ __('Go To') }}
                  </span>
              </a>
            </div>
          </div>
          @elseif(Auth::user()->canManageServers() && ($order->isServerDeployable() || $order->isServerDeployed()))
          <div class="card-actionbar">
            <div class="btn-group">
              <a href="{{ route('ynh.servers.create') }}?order={{ $order->id }}">
                <span href="#" class="btn btn-sm btn-outline-success">
                  @if($order->isServerDeployable())
                  {{ __('Configure') }}
                  @else
                  {{ __('Settings') }}
                  @endif
                </span>
              </a>
            </div>
          </div>
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