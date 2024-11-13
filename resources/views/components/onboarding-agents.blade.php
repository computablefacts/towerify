<div class="card">
  <div class="card-body">
    <h6 class="card-title">{{ __('Agents') }}</h6>
    <div class="card-text mb-3">
      L'agent est votre vigile numérique. <b>Il surveille en continu l'état de vos serveurs</b>, remontant
      rapidement toute anomalie ou non-conformité afin de prévenir les risques avant qu'ils ne deviennent des
      menaces réelles.
    </div>
    <div class="row mt-2">
      <div class="col">
        Pour configurer l'agent, connectez-vous en <i>root</i> au serveur que vous souhaitez surveiller et exécutez cette ligne de commande :
        <br><br>
        <pre style="margin-bottom:0">
curl -s "{{ app_url() }}/setup/script?api_token={{ Auth::user()->sentinelApiToken() }}&server_ip=$(curl -s ipinfo.io | jq -r '.ip')&server_name=$(hostname)" | bash
            </pre>
      </div>
    </div>
  </div>
</div>