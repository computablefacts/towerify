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
      @include('modules.cyber-buddy._action', [
      'title' => 'Charte Informatique',
      'subtitle' => 'Définissez les règles d\'utilisation de vos systèmes',
      'text' => 'Vous souhaitez créer ou importer une Charte Informatique? Cliquez <a href="">ici</a>!'
      ])
      @include('modules.cyber-buddy._action', [
      'title' => 'PSSI',
      'subtitle' => 'Définissez la politique de sécurité de vos systèmes',
      'text' => 'Vous souhaitez créer ou importer une Politique de Sécurité des Systèmes d\'Information? Cliquez <a
        href="">ici</a>!'
      ])
      @include('modules.cyber-buddy._action', [
      'title' => 'Honeypots',
      'subtitle' => 'Evaluez l\'état des menaces qui vous ciblent',
      'text' => 'Vous souhaitez piéger les acteurs malveillants? Cliquez <a href="">ici</a>!'
      ])
      @include('modules.cyber-buddy._action', [
      'title' => 'Scanner de vulnérabilités',
      'subtitle' => 'Testez votre infrastructure',
      'text' => 'Vous souhaitez surveiller vos serveurs exposés sur Internet? Cliquez <a href="">ici</a>!'
      ])
    </div>
  </div>
</div>