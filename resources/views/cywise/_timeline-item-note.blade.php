<li id="nid-{{ $note->id }}" class="timeline-item">
  <span class="timeline-item-hour">
    <span style="margin-left: -92px">{{ $time }}</span>
  </span>
  <span class="timeline-item-icon | faded-icon"
        style="background-color: var(--c-blue-500) !important; color: white !important;">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         class="icon icon-tabler icons-tabler-outline icon-tabler-notes">
      <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
      <path d="M5 3m0 2a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2z"/>
      <path d="M9 7l6 0"/>
      <path d="M9 11l6 0"/>
      <path d="M9 15l4 0"/>
    </svg>
  </span>
  <div class="timeline-item-wrapper">
    <div class="timeline-item-description">
      <span>
        <a href='#'>{{ $user->name }}</a> a créé une <a href='#'>note</a>
      </span>
    </div>
    <div class="comment">
      {{ $note->attributes()['body'] ?? '' }}
    </div>
    <div style="display: flex; gap: 10px;">
      <button class="show-replies" title="{{ __('Delete') }}" onclick="deleteNote('{{ $note->id }}')">
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
</li>