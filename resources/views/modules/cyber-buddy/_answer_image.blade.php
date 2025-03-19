@once
<style>

  .tw-answer-image {
    width: 802.5px;
    height: auto;
    max-width: 100%;
    margin-top: 0.5rem;
    margin-bottom: 0.5rem;
    border-radius: 8px
  }

</style>
@endonce

@include('modules.cyber-buddy._answer', [
'paragraphs' => [$message],
'html' => "<img src='{$url}' class='tw-answer-image'/>"
])