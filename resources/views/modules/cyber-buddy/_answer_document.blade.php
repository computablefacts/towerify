@once
<style>

  .tw-answer-document {
    align-items: center;
    display: flex;
    margin-top: 0.5rem;
    font-size: 14px;
    font-weight: 500
  }

  .tw-answer-document-button {
    background-color: rgb(255, 255, 255);
    align-items: center;
    cursor: pointer;
    justify-content: center;
    padding-bottom: 0.5rem;
    padding-left: 1rem;
    padding-right: 1rem;
    padding-top: 0.5rem;
    text-align: center;
    display: flex;
    width: 232.633px;
    height: 2.5rem;
    border-width: 0;
    border-radius: 6px;
    gap: 8px
  }

  .tw-answer-document-button:hover {
    background-color: #444aee;
    color: white;
  }

  .tw-answer-document-icon-svg {
    width: 1rem;
    height: 1rem
  }

  .tw-answer-document-download-svg {
    width: 1rem;
    height: 1rem;
    margin-left: 0.5rem;
  }

</style>
@endonce

@include('modules.cyber-buddy._answer', [
'paragraphs' => [$message],
'html' => "
<div class='tw-answer-document'>
  <button class='tw-answer-document-button'>
    <svg xmlns='http://www.w3.org/2000/svg' height='24' viewbox='0 0 24 24' fill='none'
         stroke='currentColor' stroke-linecap='round' stroke-linejoin='round' class='tw-answer-document-icon-svg'>
      <path d='M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z' fill='none'
            stroke='currentColor'></path>
      <path d='M14 2v4a2 2 0 0 0 2 2h4' fill='none' stroke='currentColor'></path>
      <path d='M10 9H8' fill='none' stroke='currentColor'></path>
      <path d='M16 13H8' fill='none' stroke='currentColor'></path>
      <path d='M16 17H8' fill='none' stroke='currentColor'></path>
    </svg>
    <span class='tw-answer-document-name'>{$document}</span>
    <svg xmlns='http://www.w3.org/2000/svg' height='24' viewbox='0 0 24 24' fill='none'
         stroke='currentColor' stroke-linecap='round' stroke-linejoin='round' class='tw-answer-document-download-svg'>
      <path d='M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z' fill='none'
            stroke='currentColor'></path>
      <path d='M14 2v4a2 2 0 0 0 2 2h4' fill='none' stroke='currentColor'></path>
      <path d='M12 18v-6' fill='none' stroke='currentColor'></path>
      <path d='m9 15 3 3 3-3' fill='none' stroke='currentColor'></path>
    </svg>
  </button>
</div>
"
])