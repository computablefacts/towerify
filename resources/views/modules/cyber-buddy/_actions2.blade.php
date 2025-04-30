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
      @include('modules.cyber-buddy._action2', [
      'title' => 'Hardening',
      'subtitle' => 'Durcissement des systèmes',
      'text' => 'Qu\'est-ce que le hardening ?'
      ])
      @include('modules.cyber-buddy._action2', [
      'title' => 'Mots de passe',
      'subtitle' => 'Gestion des mots de passe',
      'text' => 'Quel doit être la complexité d\'un mot de passe administrateur ?'
      ])
      @include('modules.cyber-buddy._action2', [
      'title' => 'COMAR',
      'subtitle' => 'Comité d\'architecture',
      'text' => 'Que peux-tu me dire sur le comité d\'architecture ?'
      ])
      @include('modules.cyber-buddy._action2', [
      'title' => 'USB',
      'subtitle' => 'Gestion des clés USB',
      'text' => 'L\'usage des clefs USB est-il autorisé ?'
      ])
    </div>
  </div>
</div>