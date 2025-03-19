@once
<style>

  .tw-answer-command {
    background-color: rgb(255, 255, 255);
    align-items: center;
    cursor: pointer;
    justify-content: center;
    padding-left: 0.75rem;
    padding-right: 0.75rem;
    text-align: center;
    display: inline-flex;
    width: 160.55px;
    height: 2.25rem;
    border-width: 1px;
    border-color: rgb(226, 232, 240);
    border-style: solid;
    border-radius: 6px
  }

  .tw-answer-command:hover {
    background-color: #444aee;
    color: white;
  }

</style>
@endonce

@include('modules.cyber-buddy._answer', [
'paragraphs' => [$message],
'html' => '
<button class="tw-answer-command">Write or review code</button>
<button class="tw-answer-command">Generate images</button>
<button class="tw-answer-command">Create charts</button>
<button class="tw-answer-command">Generate files</button>
<button class="tw-answer-command">Display tables</button>
'
])

