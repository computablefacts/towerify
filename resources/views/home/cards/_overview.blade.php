<div class="container">
  <div class="row">
    <div class="col card card-accent-secondary tw-card p-0">
      <div class="card-header">
        <h3 class="m-0"><b>{{ __('Excited to get started ?') }}</b></h3>
      </div>
      <div class="card-body">
        <div class="row mt-2">
          <div class="col">
            {{ __('Enter a domain name or an IP address belonging to you below :') }}
          </div>
        </div>
        <div class="row mt-2">
          <div class="col">
            <div id="form-invitation" class="container-fluid">
              <div class="row">
                <div class="col-10 p-0 align-content-center">
                  <input type="text"
                         class="form-control"
                         id="asset"
                         placeholder="www.example.com ou 93.184.215.14">
                </div>
                <div class="col align-content-center">
                  <button type="button"
                          onclick="createAsset()"
                          class="form-control btn btn-xs btn-outline-success">
                    {{ __('Monitor >') }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="row mt-2">
    <div class="col-6 mr-1">
      <div class="row">
        <div class="col col-6 card card-accent-secondary tw-card mr-1">
          <div class="fs-1 text-end" style="opacity: .55">
            <i class="zmdi zmdi-shield-security"></i>
          </div>
          <div id="ip-monitored" class="fs-4 mb-0 fw-bold text-uppercase">
            {{ $overview['ip_monitored'] ? Illuminate\Support\Number::format($overview['ip_monitored'], locale: 'sv') :
            0
            }}
          </div>
          <small class="text-uppercase fw-bold mb-3" style="opacity: .55">
            {{ __('IP Monitored') }}
          </small>
        </div>
        <div class="col card card-accent-secondary tw-card ml-1">
          <div class="fs-1 text-end" style="opacity: .55">
            <i class="zmdi zmdi-shield-security"></i>
          </div>
          <div id="dns-monitored" class="fs-4 mb-0 fw-bold text-uppercase">
            {{ $overview['dns_monitored'] ? Illuminate\Support\Number::format($overview['dns_monitored'], locale: 'sv')
            :
            0 }}
          </div>
          <small class="text-uppercase fw-bold mb-3" style="opacity: .55">
            {{ __('DNS Monitored') }}
          </small>
        </div>
      </div>
    </div>
    <div class="col ml-1">
      <div class="row">
        <div class="col card card-accent-secondary tw-card">
          <div class="fs-1 text-end" style="opacity: .55">
            <i class="zmdi zmdi-dns"></i>
          </div>
          <div class="fs-4 mb-0 fw-bold text-uppercase">
            {{ $overview['servers_monitored'] ? Illuminate\Support\Number::format($overview['servers_monitored'],
            locale:
            'sv') : 0 }}
          </div>
          <small class="text-uppercase fw-bold mb-3" style="opacity: .55">
            {{ __('Servers Monitored') }}
          </small>
        </div>
      </div>
    </div>
  </div>
  <div class="row mt-2">
    <div class="col-6 mr-1">
      <div class="row">
        <div class="col col-4 card card-accent-secondary tw-card mr-1">
          <div class="fs-1 text-end" style="color: red;">
            <i class="zmdi zmdi-gps-dot"></i>
          </div>
          <div class="fs-4 mb-0 fw-bold text-uppercase">
            {{ $overview['vulns_high'] ? Illuminate\Support\Number::format($overview['vulns_high'], locale:
            'sv') : 0 }}
          </div>
          <small class="text-uppercase fw-bold mb-3" style="opacity: .55">
            {{ __('Vulnerabilities high') }}
          </small>
        </div>
        <div class="col col-4 card card-accent-secondary tw-card ml-1 mr-1">
          <div class="fs-1 text-end" style="color: orange">
            <i class="zmdi zmdi-gps-dot"></i>
          </div>
          <div class="fs-4 mb-0 fw-bold text-uppercase">
            {{ $overview['vulns_medium'] ? Illuminate\Support\Number::format($overview['vulns_medium'], locale:
            'sv') : 0 }}
          </div>
          <small class="text-uppercase fw-bold mb-3" style="opacity: .55">
            {{ __('Vulnerabilities medium') }}
          </small>
        </div>
        <div class="col card card-accent-secondary tw-card ml-1">
          <div class="fs-1 text-end" style="color: blue">
            <i class="zmdi zmdi-gps-dot"></i>
          </div>
          <div class="fs-4 mb-0 fw-bold text-uppercase">
            {{ $overview['vulns_low'] ? Illuminate\Support\Number::format($overview['vulns_low'], locale:
            'sv') : 0 }}
          </div>
          <small class="text-uppercase fw-bold mb-3" style="opacity: .55">
            {{ __('Vulnerabilities low') }}
          </small>
        </div>
      </div>
    </div>
    <div class="col ml-1">
      <div class="row">
        <div class="col col-6 card card-accent-secondary tw-card mr-1">
          <div class="fs-1 text-end" style="opacity: .55">
            <i class="zmdi zmdi-layers"></i>
          </div>
          <div class="fs-4 mb-0 fw-bold text-uppercase">
            {{ $overview['metrics_collected'] ? Illuminate\Support\Number::format($overview['metrics_collected'],
            locale:
            'sv') : 0 }}
          </div>
          <small class="text-uppercase fw-bold mb-3" style="opacity: .55">
            {{ __('Metrics Collected') }}
          </small>
        </div>
        <div class="col card card-accent-secondary tw-card ml-1">
          <div class="fs-1 text-end" style="opacity: .55">
            <i class="zmdi zmdi-layers "></i>
          </div>
          <div class="fs-4 mb-0 fw-bold text-uppercase">
            {{ $overview['events_collected'] ? Illuminate\Support\Number::format($overview['events_collected'], locale:
            'sv') : 0 }}
          </div>
          <small class="text-uppercase fw-bold mb-3" style="opacity: .55">
            {{ __('Events Collected') }}
          </small>
        </div>
      </div>
    </div>
  </div>
  <div class="row mt-2">
    @if(Auth::user()->canUseAdversaryMeter())
    <div class="col col-6 card card-accent-secondary tw-card p-0 mr-1">
      <div class="card-header">
        <h3 class="m-0"><b>{{ __('AdversaryMeter') }}</b></h3>
      </div>
      <div class="card-body">
        <div class="row mt-2">
          <div class="col">
            AdversaryMeter est votre garde du corps numérique. <b>Il veille sur vos serveurs exposés sur Internet</b> en
            détectant les vulnérabilités avant que des acteurs malveillants ne les exploitent.
          </div>
        </div>
        <div class="row mt-2">
          <div class="col">
            Pour configurer AdversaryMeter, cliquez <a
              href="{{ App\Modules\AdversaryMeter\Helpers\AdversaryMeter::redirectUrl() }}" target="_blank">ici</a>.
          </div>
        </div>
        <div class="row mt-2">
          <div class="col">
            Pour accéder à la documentation détaillée, cliquez <a
              href="https://computablefacts.notion.site/AdversaryMeter-a30527edc0554ea8aabf7cb7d0137258?pvs=4"
              target="_blank">ici</a>.
          </div>
        </div>
      </div>
    </div>
    @endif
    @if(Auth::user()->canManageServers())
    <div class="col card card-accent-secondary tw-card p-0 ml-1">
      <div class="card-header">
        <h3 class="m-0"><b>{{ __('Sentinel') }}</b></h3>
      </div>
      <div class="card-body">
        <div class="row mt-2">
          <div class="col">
            Sentinel est votre vigile numérique. <b>Il surveille en continu l'état de vos serveurs</b>, remontant
            rapidement toute anomalie ou non-conformité afin de prévenir les risques avant qu'ils ne deviennent des
            menaces réelles.
          </div>
        </div>
        <div class="row mt-2">
          <div class="col">
            Pour configurer Sentinel, connectez-vous en <i>root</i> au serveur que vous souhaitez surveiller et exécutez
            cette ligne de commande :
            <br><br>
            <pre>
curl -s "{{ app_url() }}/setup/script?api_token={{ Auth::user()->sentinelApiToken() }}&server_ip=$(curl -s ipinfo.io | jq -r '.ip')" | bash
            </pre>
          </div>
        </div>
      </div>
    </div>
    @endif
  </div>
</div>
<script>

  function createAsset() {

    const asset = document.querySelector('#asset').value;

    axios.post(`${window.location.protocol}/am/api/v2/inventory/assets`, {
      asset: asset, watch: true,
    }, {
      headers: {
        'Authorization': 'Bearer {{Auth::user()->adversaryMeterApiToken()}}'
      }
    }).then(function (asset) {
      toaster.toast(`La surveillance de ${asset.data.asset.asset} a commencé.`, 'success');
      if (asset.data.asset.type === 'IP') {
        const div = document.getElementById('ip-monitored');
        div.innerText = parseInt(div.innerText) + 1;
      } else if (asset.data.asset.type === 'DNS') {
        const div = document.getElementById('dns-monitored');
        div.innerText = parseInt(div.innerText, 10) + 1;
      }
    }).catch(function (error) {
      console.error('Error:', error.response.data);
      if (error.response && error.response.data && error.response.data.errors) {
        toaster.toast(error.response.data.message || 'Une erreur est survenue.', 'danger');
      } else {
        toaster.toast('Une erreur est survenue.', 'danger');
      }
    });
  }
</script>