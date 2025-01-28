<style>

  .steps {
    display: flex;
    justify-content: space-between;
  }

  .step {
    padding: 10px 15px;
    color: #007bff;
    flex: 1;
    text-align: center;
  }

  .step.active {
    background: #007bff;
    color: white;
    font-weight: bold;
  }

  .content {
    background-color: white;
  }

  .step-content {
    display: none;
  }

  .step-content.active {
    display: block;
  }

</style>
<div class="steps mb-2">
  <div class="step active" data-step="1">
    Step 1
  </div>
  <div class="step" data-step="2">
    Step 2
  </div>
  <div class="step" data-step="3">
    Step 3
  </div>
</div>
<div class="content">
  <div class="card step-content active">
    <div class="card-body">
      <h5 class="card-title">Step 1</h5>
      <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's
        content.</p>
      <button class="btn btn-primary next-button" data-next="2">Next</button>
    </div>
  </div>
  <div class="card step-content">
    <div class="card-body">
      <h5 class="card-title">Step 2</h5>
      <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's
        content.</p>
      <button class="btn btn-primary prev-button" data-prev="1">Prev</button>
      <button class="btn btn-primary next-button" data-next="3">Next</button>
    </div>
  </div>
  <div class="card step-content">
    <div class="card-body">
      <h5 class="card-title">Step 3</h5>
      <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's
        content.</p>
      <button class="btn btn-primary prev-button" data-prev="2">Prev</button>
      <button class="btn btn-primary next-button">Submit</button>
    </div>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function () {

    const steps = document.querySelectorAll('.step');
    const stepContents = document.querySelectorAll('.step-content');
    const nextButtons = document.querySelectorAll('.next-button');
    const prevButtons = document.querySelectorAll('.prev-button');

    nextButtons.forEach(button => {
      button.addEventListener('click', () => {
        const currentStep = parseInt(button.getAttribute('data-next')) - 1;
        goToStep(currentStep);
      });
    });

    prevButtons.forEach(button => {
      button.addEventListener('click', () => {
        const prevStep = parseInt(button.getAttribute('data-prev')) - 1;
        goToStep(prevStep);
      });
    });

    function goToStep(stepIndex) {
      steps.forEach((step, index) => {
        step.classList.toggle('active', index === stepIndex);
      });
      stepContents.forEach((content, index) => {
        content.classList.toggle('active', index === stepIndex);
      });
    }
  });
</script>