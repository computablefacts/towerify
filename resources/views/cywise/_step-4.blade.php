<style>

</style>
<h1>Votre adresse email</h1>
<p>Saisissez votre adresse email pour recevoir les résultats du test.</p>
<form action="{{ route('public.cywise.onboarding', [ 'hash' => $hash, 'step' => 5 ]) }}" method="post">
  @csrf
  <input type="email" placeholder="Votre adresse email ex. j.doe@cywise.io" name="email" value="{{ $trial->email }}">
  <button class="next-button-100p" name="action" value="next" type="submit">
    Lancer le test
  </button>
  <button class="back-button" name="action" value="back">
    Retour
  </button>
</form>
<script>

  const elInput = document.querySelector('input[name="email"]');
  const elSubmitButton = document.querySelector('button[type="submit"]');

  const toggleButtonState = () => {
    const isInputEmpty = elInput.value.trim() === '';
    if (isInputEmpty) {
      elSubmitButton.classList.add('disabled');
    } else {
      elSubmitButton.classList.remove('disabled');
    }
    elSubmitButton.disabled = isInputEmpty;
  };

  toggleButtonState();

  elInput.addEventListener('input', toggleButtonState);

</script>
