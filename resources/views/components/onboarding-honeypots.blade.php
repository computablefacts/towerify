<div class="card">
  <div class="card-body">
    <h6 class="card-title">{{ __('Honeypots') }}</h6>
    <div class="card-text mb-3">
      Le honeypot est votre appât numérique. <b>Il attire et piège les acteurs malveillants</b> en simulant des systèmes
      vulnérables, permettant ainsi d'observer leurs tactiques sans risque pour votre véritable infrastructure.
    </div>
    <div class="card-text mb-3">
      En complément du scanner de vulnérabilités, il aide également à mieux prioriser les vulnérabilités détectées en
      révélant celles qui attirent réellement l’attention des attaquants, optimisant ainsi la protection de vos systèmes
      critiques.
    </div>
    <div class="card-text">
      Pour configurer vos honeypots SSH, HTTPS et HTTP, cliquez <a
        href="{{ App\Modules\AdversaryMeter\Helpers\AdversaryMeter::redirectUrl('setup_honeypots') }}" target="_blank">ici</a>.
    </div>
  </div>
</div>