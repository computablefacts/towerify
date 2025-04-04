<style>

  .terms {
    margin-bottom: var(--spacing-large);
    text-align: left;
    accent-color: var(--color-cywise);
    display: flex;
    align-items: center;
  }

  .terms label {
    margin-left: var(--spacing-medium);
  }

</style>
<h1>Vos sous-domaines</h1>
<p>Liste des sous-domaines de <b>{{ $trial->domain }}</b> compatibles avec le scanner</p>
@include('cywise._loader', [ 'title' => 'Recherche de sous-domaines...', 'subtitle' => 'Compter environ 60 secondes' ])
<form action="{{ route('public.cywise.onboarding', [ 'hash' => $hash, 'step' => 3 ]) }}" method="post" class="hidden">
  @csrf
  <div class="list">
    <!-- FILLED DYNAMICALLY -->
  </div>
  <div class="terms">
    <input type="checkbox" name="terms" checked>
    <label for="terms">Je certifie être propriétaire de ces domaines</label>
  </div>
  <div class="button-group">
    <button class="back-button" name="action" value="back">
      Retour
    </button>
    <button class="next-button-300p" name="action" value="next" type="submit">
      Suivant
    </button>
  </div>
</form>
<script>

  fetch("{{ route('public.cywise.discovery', [ 'hash' => $hash ]) }}", {
    method: 'POST', headers: {
      'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}',
    }, body: JSON.stringify({'domain': '{{ $trial->domain }}'}),
  }).then(response => {
    if (!response.ok) {
      throw new Error('Erreur réseau');
    }
    return response.json();
  })
  .then(domains => {

    // Set the list of domains
    const elDomains = document.querySelector('.list');
    domains.sort().forEach((domain, idx) => {

      const elDomain = document.createElement('div');
      elDomain.classList.add('list-item');
      elDomain.innerHTML = `
        <input type="checkbox" name="d1-${idx}" checked>
        <input type="text" name="d2-${idx}" value="${domain}" hidden>
        <label for="d1-${idx}">${domain}</label>
      `;
      elDomains.appendChild(elDomain);
    });

    // Hide the loader
    const elLoader = document.querySelector('.loader-container');
    elLoader.classList.add('hidden');

    // Display the list of domains
    const elForm = document.querySelector('form');
    elForm.classList.remove('hidden');
  })
  .catch(error => console.error('Error:', error));

</script>
