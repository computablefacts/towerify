<div class="card">
  <div class="card-body">
    <h6 class="card-title">{{ __('Vulnerability Scanner') }}</h6>
    <div class="card-text mb-3">
      Le scanner de vulnérabilités est votre garde du corps numérique. <b>Il veille sur vos serveurs exposés sur
        Internet</b> en détectant les vulnérabilités avant que des acteurs malveillants ne les exploitent.
    </div>
    <div class="card-text mb-3">
      {{ __('Enter a domain name or an IP address belonging to you below :') }}
    </div>
    <form>
      <div class="row">
        <div class="col-md-9">
          <input type="text"
                 class="form-control"
                 id="asset"
                 placeholder="example.com ou 93.184.215.14"
                 autofocus>
        </div>
        <div class="col-md-3 align-content-center">
          <button type="button"
                  onclick="createAsset()"
                  class="form-control btn btn-primary text-white">
            {{ __('Monitor >') }}
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
<script>

  function createAsset() {

    const asset = document.querySelector('#asset').value;

    axios.post('/api/inventory/assets', {
      asset: asset, watch: true,
    }, {
      headers: {
        'Authorization': 'Bearer {{Auth::user()->adversaryMeterApiToken()}}'
      }
    }).then(function (asset) {
      toaster.toastSuccess(`La surveillance de ${asset.data.asset.asset} a commencé.`);
      if (asset.data.asset.type === 'IP') {
        const div = document.getElementById('ip-monitored');
        div.innerText = parseInt(div.innerText) + 1;
      } else if (asset.data.asset.type === 'DNS') {
        const div = document.getElementById('dns-monitored');
        div.innerText = parseInt(div.innerText, 10) + 1;
      }
    }).catch((error) => toaster.toastAxiosError(error));
  }

</script>