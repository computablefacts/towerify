@if(Auth::user()->isCywiseUser())
<div class="container">
  <div class="row">
    <div class="col col-6 card card-accent-secondary tw-card mr-1">
      <div class="card-header">
        <h3 class="m-0"><b>{{ __('AdversaryMeter') }}</b></h3>
      </div>
      <div class="card-body">
        <div class="row mt-3">
          <div class="col">
            AdversaryMeter est votre garde du corps numérique. <b>Il veille sur vos serveurs exposés sur Internet</b> en
            détectant les vulnérabilités avant que des acteurs malveillants ne les exploitent.
          </div>
        </div>
        <div class="row mt-3">
          <div class="col">
            <ul>
              <li>Pour configurer AdversaryMeter, cliquez <a
                  href="{{ App\Modules\AdversaryMeter\Helpers\AdversaryMeter::redirectUrl() }}" target="_blank">ici</a>.
              </li>
              <li>Pour accéder à la documentation détaillée, cliquez <a
                  href="https://computablefacts.notion.site/AdversaryMeter-a30527edc0554ea8aabf7cb7d0137258?pvs=4"
                  target="_blank">ici</a>.
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <div class="col card card-accent-secondary tw-card ml-1">
      <div class="card-header">
        <h3 class="m-0"><b>{{ __('Sentinel') }}</b></h3>
      </div>
      <div class="card-body">
        <div class="row mt-3">
          <div class="col">
            Sentinel est votre vigile numérique. <b>Il surveille en continu l'état de vos serveurs</b>, remontant
            rapidement toute anomalie ou non-conformité afin de prévenir les risques avant qu'ils ne deviennent des
            menaces réelles.
          </div>
        </div>
        <div class="row mt-3">
          <div class="col">
            Pour configurer Sentinel, connectez-vous en <i>root</i> au serveur que vous souhaitez surveiller et exécutez
            cette ligne de commande :
            <br><br>
            <pre>
curl -s {{ app_url() }}/setup/script?api_token={{ Auth::user()->sentinelApiToken() }}&server_ip=<i>&lt;ip&gt;</i>&server_name=<i>&lt;name&gt;</i> | bash
            </pre>
            Avant d'exécuter celle-ci, assurez-vous que :<br><br>
            <ul>
              <li><i>&lt;ip&gt;</i> a bien été remplacé par l'IP de votre serveur ;</li>
              <li><i>&lt;name&gt;</i> a bien été remplacé par une chaîne de caractères identifiant votre serveur
                (optionnel).
              </li>
            </ul>
            </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endif
<div class="card card-accent-secondary tw-card mt-2">
  <div class="card-header">
    <h3 class="m-0"><b>{{ __('Summary') }}</b></h3>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col">
        Coming soon!
      </div>
    </div>
  </div>
</div>