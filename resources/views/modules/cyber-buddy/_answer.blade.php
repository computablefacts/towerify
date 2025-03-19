<div class="tw-answer-wrapper">
  <div class="tw-answer">
    <div class="tw-answer-avatar-wrapper">
      <div class="tw-answer-avatar">
        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewbox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="tw-answer-avatar-svg">
          <path d="M12 8V4H8" fill="none" stroke="currentColor"></path>
          <rect height="12" x="4" y="8" rx="2" fill="none" stroke="currentColor"
                class="tw-answer-avatar-svg-rect"></rect>
          <path d="M2 14h2" fill="none" stroke="currentColor"></path>
          <path d="M20 14h2" fill="none" stroke="currentColor"></path>
          <path d="M15 13v2" fill="none" stroke="currentColor"></path>
          <path d="M9 13v2" fill="none" stroke="currentColor"></path>
        </svg>
      </div>
    </div>
    <div class="tw-answer-message">
      @foreach($paragraphs as $paragraph)
      <p class="tw-answer-message-paragraph">{{ $paragraph }}</p>
      @endforeach
      @if(!empty($html))
      <div class="tw-answer-message-html">{!! $html !!}</div>
      @endif
    </div>
  </div>
  <div class="tw-answer-timestamp">02:04 PM</div>
</div>