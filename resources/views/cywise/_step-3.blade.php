<h1>Vos honeypots</h1>
<p>Configuration automatique de vos honeypots</p>
@include('cywise._loader', [ 'title' => 'Création des honeypots...', 'subtitle' => 'Compter environ 60 secondes' ])
<form action="{{ route('public.cywise.onboarding', [ 'hash' => $hash, 'step' => 4 ]) }}" method="post">
  @csrf
  <div class="button-group">
    <button class="back-button" name="action" value="back">
      Retour
    </button>
    <button class="next-button-300p disabled" name="action" value="next" type="submit" disabled>
      Suivant
    </button>
  </div>
</form>
<script>
  const setCheckmark = () => {

    const elLoader = document.querySelector('.loader-picto');
    elLoader.classList.remove('loader-picto');
    elLoader.classList.add('checkmark');

    const elTitle = document.querySelector('.loader-info h2');
    elTitle.innerText = "Vos honeypots sont prêts!"

    const elText = document.querySelector('.loader-info p');
    elText.remove();

    const elSubmitButton = document.querySelector('button[type="submit"]');
    elSubmitButton.disabled = false;
    elSubmitButton.classList.remove('disabled');
  };
</script>
@if($trial->honeypots)
<script>
  setCheckmark();
</script>
@else
<script>
  setTimeout(setCheckmark, 3000);
</script>
@endif
