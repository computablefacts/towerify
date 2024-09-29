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
      <h3 class="m-0">
        <a href="{{ route('product.index') }}" class="float-end">
          {{ __('+ new') }}
        </a>
      </h3>
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
      <?php $defaultThumbnail = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAC8AAAAjCAMAAAAzO6PlAAAAt1BMVEX///+tq6u2tLSXlZW0srKenJ2LiYqzsLGZmJiNi4yqqKmvra2cmpqRj5CmpKSPjY6koqKUk5O4traopqagnp+Ih4exr6/Mycq5t7fOzMy7ubmioKCGhIWTkZLEwsK9u7vCv8DV09PS0NDQzs7Jx8fHxcWBgIF/fX58envX1dXZ19eDgoNwb3C/vb3U0dH7+vt1dHXh4eHe29vb2dl4d3j39/dpaGrx8fHo5+djYmPt7e309PRbW1zggo2RAAAC0UlEQVQ4y22T23qCMBCEA6LWA1qlchIBBaViPdVabe37P1dnN4GU72su9OafnZkNER9FsYs6Y7v11H55dvqDruknaZx72WG53Zbl5Z3O5VJut8vDapWJ12ITzTrjRZNfe8FqWQkUnAWB54lis4tmPc27lh/umV8doICgrOF8vSZ+1unZFW+41pT4PMgyFiia4ThNBY1H/AnzI8Wn69yTAhyaDTqO030SCsJ7DV4V9oIAgkOD9k0RMb/QPBf27pkUAKckTE9NyxXAEV/zvKDtURzfSQBaDqfZVndgiBmNR93WsOaTu6BzDzyKklfDQfcdgfEyfs3HDyHPdempLKE/peHOaC6AN3lkqc9ZFa3xtuDxNe9YlEWfb6/G+87zvP0k+UXFx1fRPMfDPgzVdOAtgTg1Pyo1qDMlqOoafeDDlt3gje9/+NseazeQ5mXYWozBc37e5/wfg3PIe+9zGruj5hM/BO+kzQLXwOc0BpedjGfM0z4p0BwfaPf+dz1p6KvxHIf4vwWcbNA3VsdqN5ck3Jd+V/LUttfk0feR9o3kxvgjT8LgKm6Ja6jtLMDje1CBkGcJrDSMwZmL+gn9v1su7kry4w6+N31jw3b4QGjTMLxbYJkxfK45x+f8TxPwkRJMIEAk54zcHiy6FvW4hxbx3Jf22RMRPxjlQJ09YGd3YN0h3E5NrMcd0HVRoImN9ygdtMDCLd+CB35iX/PSYCI2m91OWti2FMxHF3mzpjnFE2SeDdCgJYqigAQe9bPBtcVXFMVLNnm+MmCBeMWBQj97+o6e3YuFl2+Bl4EqQVucPj4gKMhCvXskwsPBS3PBk0ElIIV4O51OUEgBSksD4mGgBRQJFiPxhqMEqoI2IB4C1YEV4vMTAlhAIBNpgwEaT32fLZTCEF9fULADJdIGzMPA91lRScCzQBtUgVQBPwxDLbHEz48W7CJeEQWSBZhPEkgq0S8ch3VdK2koBAAAAABJRU5ErkJggg=='; ?>
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