<style>

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

  .checkmark {
    width: 28px;
    height: 48px;
    display: inline-block;
    box-sizing: border-box;
    transform: rotate(45deg);
    border-bottom: 7px solid #78b13f;
    border-right: 7px solid #78b13f;
    margin-right: 40px;
    margin-left: 10px;
  }

</style>
<h1>Vos honeypots</h1>
<p>Configuration automatique de vos honeypots</p>
<div class="header">
  <div class="logo">
    <span class="loader"></span>
  </div>
  <div class="info">
    <h1>Création des honeypots...</h1>
    <p>Compter environ 60 secondes</p>
  </div>
</div>
<form action="{{ route('public.cywise.onboarding', [ 'hash' => $hash, 'step' => 4 ]) }}" method="post">
  @csrf
  <div class="button-group">
    <button class="back-button" name="action" value="back">
      Retour
    </button>
    <button class="next-button-300p" name="action" value="next" type="submit">
      Suivant
    </button>
  </div>
</form>
@if($trial->honeypots)
<script>

  const elLoader = document.querySelector('.loader');
  elLoader.classList.remove('loader');
  elLoader.classList.add('checkmark');

  const elTitle = document.querySelector('.info h1');
  elTitle.innerText = "Vos honeypots sont prêts!"

  const elText = document.querySelector('.info p');
  elText.remove();

</script>
@else
<script>
  setTimeout(() => {

    const elLoader = document.querySelector('.loader');
    elLoader.classList.remove('loader');
    elLoader.classList.add('checkmark');

    const elTitle = document.querySelector('.info h1');
    elTitle.innerText = "Vos honeypots sont prêts!"

    const elText = document.querySelector('.info p');
    elText.remove();

  }, 3000);
</script>
@endif
