<div class="container">
  <div class="row">
    <div class="col-4 pr-0">
      <x-big-number
        :number="$assetsDiscovered->count()"
        :title="__('Discovered Assets') . ' / 24h'"
        icon="world"
        color="var(--ds-background-brand-bold)"/>
    </div>
    <div class="col-4 pl-2 pr-0">
      <x-big-number
        :number="$events->count()"
        :title="__('Suspicious Events') . ' / 24h'"
        icon="event"
        color="var(--ds-background-brand-bold)"/>
    </div>
    <div class="col-4 pl-2">
      <x-big-number
        :number="$metrics->count()"
        :title="__('Important Metrics') . ' / 24h'"
        icon="metric"
        color="var(--ds-background-brand-bold)"/>
    </div>
  </div>
  <div class="card mt-2">
    <div class="card-body">
      <h6 class="card-title">{{ __('Assets Discovered During The Last 24 Hours') }}</h6>
      @if($assetsDiscovered->isEmpty())
      <div class="row">
        <div class="col">
          {{ __('None.') }}
        </div>
      </div>
      @else
      <table class="table table-hover no-bottom-margin">
        <thead>
        <tr>
          <th style="width:165px">{{ __('Discovery Date') }}</th>
          <th style="width:100px">{{ __('Asset Type') }}</th>
          <th>{{ __('Asset') }}</th>
          <th class="text-end">{{ __('Scan Status') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($assetsDiscovered as $asset)
        <tr>
          <td>
            {{ $asset->created_at->format('Y-m-d H:i') }}
          </td>
          <td>
            <span class="lozenge new">
              {{ $asset->type }}
            </span>
          </td>
          <td>
            {{ $asset->asset }}
          </td>
          <td class="text-end">
            @if($asset->scanInProgress()->isEmpty())
            <span class="lozenge success">
              scan terminé
            </span>
            @else
            <span class="lozenge error">
              scan en cours
            </span>
            @endif
          </td>
        </tr>
        @endforeach
        </tbody>
      </table>
      @endif
    </div>
  </div>
  <div class="card mt-2">
    <div class="card-body">
      <h6 class="card-title">{{ __('Suspicious Activity From The Last 24 Hours') }}</h6>
      @if($events->isEmpty())
      <div class="row">
        <div class="col">
          {{ __('None.') }}
        </div>
      </div>
      @else
      <table class="table table-hover no-bottom-margin">
        <thead>
        <tr>
          <th style="width:165px">{{ __('Date') }}</th>
          <th>{{ __('Server') }}</th>
          <th style="width:75px">{{ __('IP') }}</th>
          <th>{{ __('Message') }}</th>
          <th></th>
          <th class="text-end" style="width:100px">{{ __('Event Id') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($events as $event)
        <tr id="eid-{{ $event['id'] }}">
          <td>{{ $event['timestamp'] }}</td>
          <td>{{ $event['server'] }}</td>
          <td>{{ $event['ip'] }}</td>
          <td class="text-muted">{{ $event['message'] }}</td>
          <th class="align-content-center">
            <a href="javascript:;" onclick="dismissEvent({{ $event['id'] }})">
              {{ __('dismiss') }}
            </a>
          </th>
          <td class="text-end">
            <span class="lozenge new">
              {{ Illuminate\Support\Number::format($event['id'], locale:'sv') }}
            </span>
          </td>
        </tr>
        @endforeach
        </tbody>
      </table>
      @endif
    </div>
  </div>
  <div class="card mt-2">
    <div class="card-body">
      <h6 class="card-title">{{ __('Important Metrics From The Last 24 Hours') }}</h6>
      @if($metrics->isEmpty())
      <div class="row">
        <div class="col">
          {{ __('None.') }}
        </div>
      </div>
      @else
      <table class="table table-hover no-bottom-margin">
        <thead>
        <tr>
          <th style="width:165px">{{ __('Date') }}</th>
          <th>{{ __('Server') }}</th>
          <th style="width:75px">{{ __('IP') }}</th>
          <th>{{ __('Message') }}</th>
          <th class="text-end" style="width:100px">{{ __('Metric Id') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($metrics as $metric)
        <tr>
          <td>{{ $metric['timestamp'] }}</td>
          <td>{{ $metric['server'] }}</td>
          <td>{{ $metric['ip'] }}</td>
          <td class="text-muted">{{ $metric['message'] }}</td>
          <td class="text-end">
            <span class="lozenge new">
            {{ Illuminate\Support\Number::format($metric['id'], locale:'sv') }}
            </span>
          </td>
        </tr>
        @endforeach
        </tbody>
      </table>
      @endif
    </div>
  </div>
</div>
<script>

  // set a.href to "javascript:;" in order to avoid scrolling the page
  function dismissEvent(eventId) {
    axios.get(`/events/${eventId}/dismiss`).then(function (response) {
      if (response.data.success) {
        toaster.toastSuccess(response.data.success);
        document.getElementById(`eid-${eventId}`).remove();
      } else if (response.data.error) {
        toaster.toastError(response.data.error);
      } else {
        console.log(response.data);
      }
    }).catch(error => toaster.toastAxiosError(error));
  }

</script>
