<style>

</style>
<h1>Votre nom de domaine</h1>
<p>Saisissez votre nom de domaine et Cywise se charge de trouver les vulnérabilités pour vous</p>
<form action="{{ route('public.cywise.onboarding', [ 'hash' => $hash, 'step' => 2 ]) }}" method="post">
  @csrf
  <input type="text" placeholder="Votre nom de domaine ex. example.com" name="domain" value="{{ $trial->domain }}">
  <button type="submit" name="action" value="next">Suivant →</button>
</form>