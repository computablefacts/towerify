@if(Auth::user()->canListApps())
<div class="card">
  @if($orders->isEmpty())
  <div class="card-body">
    <div class="row">
      <div class="col">
        {{ __('None.') }}
      </div>
    </div>
  </div>
  @else
  <div class="card-body">
    <table class="table table-hover" style="margin-bottom:0">
      <thead>
      <tr>
        <th class="ps-4" width="25px"></th>
        <th width="70px"></th>
        <th>
          <i class="zmdi zmdi-long-arrow-down"></i>&nbsp;{{ __('Date') }}
        </th>
        <th>{{ __('Name') }}</th>
        <th>{{ __('Categories') }}</th>
        <th>{{ __('Order') }}</th>
        <th>{{ __('Status') }}</th>
        <th></th>
      </tr>
      </thead>
      <tbody>
      @foreach($orders->filter(fn($order) => !$order->isApplicationDeployed()) as $order)
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
          <div class="text-muted">
            {{ $order->orderIdentifier() }}
          </div>
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
          @if(Auth::user()->canManageApps())
          <button type="button"
                  onclick="installApp('{{ $server->id }}', '{{ $order->id }}', '{{ $order->name() }}')"
                  class="btn btn-xs btn-outline-success float-end">
            {{ __('install') }}
          </button>
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

  function installApp(serverId, orderId, appName) {

    const response = confirm(`Are you sure you want to install ${appName} on this server?`);

    if (response) {
      axios.post(`/ynh/servers/${serverId}/orders/${orderId}`, {}).then(function (data) {
        if (data.data.success) {
          toaster.toastSuccess(data.data.success);
        } else if (data.data.error) {
          toaster.toastError(data.data.error);
        } else {
          console.log(response.data);
        }
      }).catch(error => toaster.toastAxiosError(error));
    }
  }

</script>
@endif