@once
<style>

  .tw-answer-chart {
    background-color: rgb(255, 255, 255);
    margin-top: 1rem;
    border-radius: 8px;
    padding: 1rem
  }

</style>
@endonce

@include('modules.cyber-buddy._answer', [
'paragraphs' => [$message],
'html' => "<div class='tw-answer-chart'>TODO</div>"
])