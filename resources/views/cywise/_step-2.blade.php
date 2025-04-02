<style>

  input[type=checkbox] {
    appearance: none;
    width: 16px;
    height: 16px;
    border: 2px solid #FFA500;
    border-radius: 3px;
    position: relative;
    cursor: pointer;
  }

  input[type=checkbox]:checked {
    background-color: #FFA500; /* couleur de fond */
  }

  input[type=checkbox]:checked::after {
    content: '✓'; /* ou '✔' */
    position: absolute;
    color: white;
    font-size: 16px;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
  }

  .domain-list {
    margin-bottom: 20px;
  }

  .domain-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px;
    border: 1px solid #ccc;
    margin-bottom: 10px;
  }

  .domain-item input {
    margin-right: 10px;
    accent-color: #FFA500;
  }

  .domain-item label {
    flex-grow: 1;
  }

  .certificate-note {
    margin-bottom: 20px;
    text-align: left;
    accent-color: #FFA500;
    display: flex;
    align-items: center;
  }

  .certificate-note label {
    margin-left: 10px;
  }

  .header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
  }

  .info h1 {
    font-size: 20px;
    margin: 0;
  }

  .info p {
    font-size: 14px;
    color: #555;
    margin: 5px 0 0 0;
  }

  .loader {
    width: 48px;
    height: 48px;
    border: 5px solid #FFA500;
    border-bottom-color: transparent;
    border-radius: 50%;
    display: inline-block;
    box-sizing: border-box;
    animation: rotation 1s linear infinite;
    margin-right: 20px;
  }

  @keyframes rotation {
    0% {
      transform: rotate(0deg);
    }
    100% {
      transform: rotate(360deg);
    }
  }

  .hidden {
    display: none;
  }

</style>
<h1>Vos sous-domaines</h1>
<p>Liste des sous-domaines de <b>{{ $trial->domain }}</b> compatibles avec le scanner</p>
<div class="header">
  <div class="logo">
    <span class="loader"></span>
  </div>
  <div class="info">
    <h1>Recherche de sous-domaines...</h1>
    <p>Compter environ 60 secondes</p>
  </div>
</div>
<form action="{{ route('public.cywise.onboarding', [ 'hash' => $hash, 'step' => 3 ]) }}" method="post" class="hidden">
  @csrf
  <div class="domain-list">
    <!-- FILLED DYNAMICALLY -->
  </div>
  <div class="certificate-note">
    <input type="checkbox" name="certificate" checked>
    <label for="certificate">Je certifie être propriétaire de ces domaines</label>
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
    const elDomains = document.querySelector('.domain-list');
    domains.sort().forEach((domain, idx) => {

      const elDomain = document.createElement('div');
      elDomain.classList.add('domain-item');
      elDomain.innerHTML = `
        <input type="checkbox" name="d1-${idx}" checked>
        <input type="text" name="d2-${idx}" value="${domain}" hidden>
        <label for="d1-${idx}">${domain}</label>
      `;
      elDomains.appendChild(elDomain);
    });

    // Hide the loader
    const elLoader = document.querySelector('.header');
    elLoader.classList.add('hidden');

    // Display the list of domains
    const elForm = document.querySelector('form');
    elForm.classList.remove('hidden');
  })
  .catch(error => console.error('Error:', error));

</script>
