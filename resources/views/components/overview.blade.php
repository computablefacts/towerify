<style>

  .pr-0 {
    padding-right: 0 !important;
  }

  .pl-2 {
    padding-left: .5rem !important;
  }

</style>
<div class="container p-0">
  @if(Auth::user()->isInTrial())
  <div class="alert alert-danger border border-danger">
    {{ __('Your account is in the trial period until :date.', ['date' => Auth::user()->endOfTrial()->format('Y-m-d')])
    }}
  </div>
  @endif
  <div class="row">
    <div class="col">
      <x-monitor-asset/>
    </div>
  </div>
  <div class="row mt-2">
    <div class="col-6">
      <div class="row">
        <div class="col col-6 pr-0">
            <?php $ip_monitored = $overview['ip_monitored'] ?: 0 ?>
          <x-big-number
            :number="$ip_monitored"
            :title="__('IP Monitored')"
            icon="ip"
            color="var(--ds-background-brand-bold)"/>
        </div>
        <div class="col pr-0 pl-2">
            <?php $dns_monitored = $overview['dns_monitored'] ?: 0 ?>
          <x-big-number
            :number="$dns_monitored"
            :title="__('DNS Monitored')"
            icon="dns"
            color="var(--ds-background-brand-bold)"/>
        </div>
      </div>
    </div>
    <div class="col pl-2">
        <?php $servers_monitored = $overview['servers_monitored'] ?: 0 ?>
      <x-big-number
        :number="$servers_monitored"
        :title="__('Agents Deployed')"
        icon="server"
        color="var(--ds-background-brand-bold)"/>
    </div>
  </div>
  <div class="row mt-2">
    <div class="col-6 pr-0">
      <div class="row">
        <div class="col col-4 pr-0">
            <?php $vulns_high = $overview['vulns_high'] ?: 0 ?>
          <x-big-number
            :number="$vulns_high"
            :title="__('High')"
            icon="vulnerability"
            color="#dc3545"/>
        </div>
        <div class="col col-4 pl-2 pr-0">
            <?php $vulns_medium = $overview['vulns_medium'] ?: 0 ?>
          <x-big-number
            :number="$vulns_medium"
            :title="__('Medium')"
            icon="vulnerability"
            color="#fd7e14"/>
        </div>
        <div class="col pl-2">
            <?php $vulns_low = $overview['vulns_low'] ?: 0 ?>
          <x-big-number
            :number="$vulns_low"
            :title="__('Low')"
            icon="vulnerability"
            color="#fff700"/>
        </div>
      </div>
    </div>
    <div class="col pl-2">
      <div class="row">
        <div class="col col-6 pr-0">
            <?php $events_collected = $overview['events_collected'] ?: 0 ?>
          <x-big-number
            :number="$events_collected"
            :title="__('Events Collected')"
            icon="event"
            color="var(--ds-background-brand-bold)"/>
        </div>
        <div class="col pl-2">
            <?php $metrics_collected = $overview['metrics_collected'] ?: 0 ?>
          <x-big-number
            :number="$metrics_collected"
            :title="__('Metrics Collected')"
            icon="metric"
            color="var(--ds-background-brand-bold)"/>
        </div>
      </div>
    </div>
  </div>
  @if(App\Modules\AdversaryMeter\Models\Asset::exists() > 0 || App\Models\YnhServer::exists() > 0)
  <div class="row mt-2">
    <x-suspicious-activity/>
  </div>
  @endif
  <div class="row mt-2">
    @if(Auth::user()->canUseAdversaryMeter())
    <div class="col col-6 pr-0">
      <x-faq-adversary-meter/>
    </div>
    @endif
    @if(Auth::user()->canManageServers())
    <div class="col col-6 pl-2">
      <x-faq-sentinel/>
    </div>
    @endif
  </div>
</div>