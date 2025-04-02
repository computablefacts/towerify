<style>

</style>
<h1>Votre adresse email</h1>
<p>Rentrez votre adresse email pour recevoir les résultats</p>
<form action="{{ route('public.cywise.onboarding', [ 'hash' => $hash, 'step' => 5 ]) }}" method="post">
  @csrf
  <input type="text" placeholder="Votre adresse email ex. j.doe@example.com" name="email" value="{{ $trial->email }}">
  <button class="next-button-100p" name="action" value="next" type="submit">
    Lancer le scan
  </button>
  <button class="back-button" name="action" value="back">
    Retour
  </button>
</form>
