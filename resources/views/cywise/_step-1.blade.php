<style>

</style>
<h1>Votre nom de domaine</h1>
<p>Saisissez votre nom de domaine et Cywise se charge de trouver les vulnérabilités pour vous</p>
<form action="{{ route('public.cywise.onboarding', [ 'hash' => $hash, 'step' => 2 ]) }}" method="post">
  @csrf
  <input type="text" placeholder="Votre nom de domaine. Ex. cywise.io" name="domain" value="{{ $trial->domain }}">
  <button type="submit" name="action" value="next">Suivant →</button>
</form>
<script>

  const elInput = document.querySelector('input[name="domain"]');
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
