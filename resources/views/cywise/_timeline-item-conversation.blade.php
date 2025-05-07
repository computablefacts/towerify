<li id="cid-{{ $conversation->id }}" class="timeline-item">
  <span class="timeline-item-hour">
    <span style="margin-left: -92px">{{ $time }}</span>
  </span>
  <span class="timeline-item-icon | faded-icon"
        style="background-color: var(--c-blue-500) !important; color: white !important;">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
				<path fill="none" d="M0 0h24v24H0z"/>
				<path fill="currentColor"
              d="M6.455 19L2 22.5V4a1 1 0 0 1 1-1h18a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H6.455zM7 10v2h2v-2H7zm4 0v2h2v-2h-2zm4 0v2h2v-2h-2z"/>
			</svg>
  </span>
  @if(empty($conversation->description))
  <div class="timeline-item-description">
      <span>
        <a href='#'>{{ $conversation->createdBy()->name }}</a> a lancé une <a
          href="{{ route('home', ['tab' => 'ama2', 'conversation_id' => $conversation->id]) }}">conversation</a>
      </span>
  </div>
  @else
  <div class="timeline-item-wrapper">
    <div class="timeline-item-description">
      <span>
        @if($conversation->format === \App\Models\Conversation::FORMAT_V1)
        <a href='#'>{{ $conversation->createdBy()->name }}</a> a lancé une <a
          href="{{ route('home', ['tab' => 'ama2', 'conversation_id' => $conversation->id]) }}">conversation</a>
        @else
        <a href='#'>{{ $conversation->createdBy()->name }}</a> a lancé une <a href="#">conversation</a>
        @endif
      </span>
    </div>
    <div class="comment">
      {{ $conversation->description }}
    </div>
    <div style="display: flex; gap: 10px;">
      <button class="show-replies" title="{{ __('Delete') }}" onclick="deleteConversation('{{ $conversation->id }}')">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
          <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
          <path d="M4 7l16 0"/>
          <path d="M10 11l0 6"/>
          <path d="M14 11l0 6"/>
          <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/>
          <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/>
        </svg>
      </button>
    </div>
  </div>
  @endif
</li>