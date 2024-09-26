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
            AdversaryMeter est votre garde du corps numérique. Il veille sur vos <b>serveurs exposés sur Internet</b> en
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
            Sentinel est votre vigile numérique. Il surveille en continu <b>l'état de vos serveurs</b>, remontant
            rapidement toute anomalie ou non-conformité afin de prévenir les risques avant qu'ils ne deviennent des
            menaces réelles.
          </div>
        </div>
        <div class="row mt-3">
          <div class="col">
            Pour configurer Sentinel :
            <ul>
              <li>
                Dans votre navigateur, rendez-vous à l'adresse suivante pour obtenir un jeton d'accès : <br><br>
                <b><a href="{{ app_url() }}/setup/token" target="_blank">{{ app_url() }}/setup/token</a></b>
                <br><br>
                Conservez-le soigneusement, il ne sera affiché qu'une seule fois !
                <br><br>
              </li>
              <li>
                Connectez vous en root au serveur que vous souhaitez surveiller et exécutez cette ligne de commande :
                <br><br>
                <b>curl -s {{ app_url() }}/setup/script?api_token=<i>&lt;token&gt;</i>&server_ip=<i>&lt;ip&gt;</i>&server_name=<i>&lt;name&gt;</i>
                  | bash</b>
                <br><br>
                Avant d'exécuter celle-ci, assurez-vous que :<br><br>
                <ul>
                  <li><i>&lt;token&gt;</i> est bien remplacé par le jeton obtenu lors de l'étape précédente ;</li>
                  <li><i>&lt;ip&gt;</i> est bien remplacé par l'IP de votre serveur ;</li>
                  <li><i>&lt;name&gt;</i> est bien remplacé par une chaîne de caractères identifiant votre serveur
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