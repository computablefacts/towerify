<style>

  .tw-actions {
    flex-grow: 1;
    overflow: hidden;
    width: 100%;
    height: 100%;
  }

  .tw-actions .tw-centered {
    align-items: center;
    justify-content: center;
    display: flex;
    height: 100%;
    padding: 0.5rem
  }

  .tw-actions .tw-centered .tw-grid {
    grid-template-columns: repeat(2, minmax(0px, 1fr));
    grid-template-rows: 160px 160px;
    display: grid;
    width: 100%;
    max-width: 48rem;
    gap: 16px
  }

</style>
<div class="tw-actions" style="display: none;">
  <div class="tw-centered">
    <div class="tw-grid">
      @php

      $urlCharteInformatique = route('home', ['tab' => 'ai_writer']);
      $charteInformatique = "Vous souhaitez créer ou importer une Charte Informatique? Cliquez <a href=\"$urlCharteInformatique\">ici</a>!";

      $urlPssi = route('home', ['tab' => 'ai_writer']);
      $pssi = "Vous souhaitez créer ou importer une Politique de Sécurité des Systèmes d\'Information? Cliquez <a href=\"$urlPssi\">ici</a>!";

      $urlHoneypots = App\Helpers\AdversaryMeter::redirectUrl('setup_honeypots');
      $honeypots = "Vous souhaitez piéger les acteurs malveillants? Cliquez <a href=\"$urlHoneypots\">ici</a>!";

      $urlScanner = App\Helpers\AdversaryMeter::redirectUrl('assets');
      $scanner = "Vous souhaitez surveiller vos serveurs exposés sur Internet? Cliquez <a href=\"$urlScanner\">ici</a>!";

      @endphp
      @include('modules.cyber-buddy._action', [
      'title' => 'Charte Informatique',
      'subtitle' => 'Définissez les règles d\'utilisation de vos systèmes',
      'text' => $charteInformatique
      ])
      @include('modules.cyber-buddy._action', [
      'title' => 'PSSI',
      'subtitle' => 'Définissez la politique de sécurité de vos systèmes',
      'text' => $pssi
      ])
      @include('modules.cyber-buddy._action', [
      'title' => 'Honeypots',
      'subtitle' => 'Evaluez l\'état des menaces qui vous ciblent',
      'text' => $honeypots
      ])
      @include('modules.cyber-buddy._action', [
      'title' => 'Scanner de vulnérabilités',
      'subtitle' => 'Testez votre infrastructure',
      'text' => $scanner
      ])
    </div>
  </div>
</div>