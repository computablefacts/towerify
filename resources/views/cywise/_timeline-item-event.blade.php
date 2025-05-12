<li id="eid-{{ $event->id }}" class="timeline-item">
  <span class="timeline-item-hour">
    <span style="margin-left: -92px">{{ $time }}</span>
  </span>
  <span class="timeline-item-icon | faded-icon"
        style="@if($bgColor) background-color:{{ $bgColor }} !important;@endif @if($txtColor) color:{{ $txtColor }} !important; @endif">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         class="icon icon-tabler icons-tabler-outline icon-tabler-server">
      <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
      <path d="M3 4m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z"/>
      <path d="M3 12m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z"/>
      <path d="M7 8l0 .01"/>
      <path d="M7 16l0 .01"/>
    </svg>
  </span>
  <div class="timeline-item-wrapper">
    <div class="timeline-item-description">
      <span><b>{{ $event->server_name }}</b> - {{ $event->comments }}</span>
    </div>
    <pre class="comment" style="margin-bottom: 0;">{{ json_encode($event->columns, JSON_PRETTY_PRINT) }}</pre>
    <div style="display: flex; gap: 10px;">
      <button class="show-replies" title="{{ __('Dismiss') }}" onclick="dismissEvent('{{ $event->id }}')">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             class="icon icon-tabler icons-tabler-outline icon-tabler-cancel">
          <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
          <path d="M3 12a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/>
          <path d="M18.364 5.636l-12.728 12.728"/>
        </svg>
      </button>
    </div>
  </div>
</li>