<div class="card mb-3">
  <div class="card-body">
    <h6 class="card-title">{{ __('Vous souhaitez protéger un nouveau domaine ?') }}</h6>
    <div class="card-text mb-3">
      {{ __('Enter a domain name or an IP address belonging to you below :') }}
    </div>
    <form>
      <div class="row">
        <div class="col">
          <input type="text"
                 class="form-control"
                 id="asset"
                 placeholder="example.com ou 93.184.215.14"
                 autofocus>
        </div>
      </div>
      <div class="row mt-3">
        <div class="col align-content-center">
          <button type="button"
                  onclick="createAsset()"
                  class="btn btn-primary"
                  style="width: 100%;">
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