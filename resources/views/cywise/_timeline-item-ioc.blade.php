@if($ioc['in_between'] >= 1)
<li id="eid-{{ $ioc['first']['ioc']->id }}" class="timeline-item">
  <span class="timeline-item-hour">
    <span style="margin-left: -92px">{{ $ioc['first']['time'] }}</span>
  </span>
  <span class="timeline-item-icon | faded-icon"
        style="background-color:{{ $ioc['first']['bgColor'] }} !important; color:{{ $ioc['first']['txtColor'] }} !important;">
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
      <span>
        <b>{{ $ioc['first']['ioc']->server_name }} ({{ $ioc['first']['ioc']->server_ip_address }})</b> - {{ $ioc['first']['ioc']->comments }} {{ $ioc['first']['level'] }}
      </span>
    </div>
    <pre class="comment"
         style="margin-bottom: 0;">{{ json_encode($ioc['first']['ioc']->columns, JSON_PRETTY_PRINT) }}</pre>
  </div>
</li>
@endif
@if($ioc['in_between'] > 2)
<li class="timeline-item">
  <span class="timeline-item-hour">
    <span style="margin-left: -92px"></span>
  </span>
  <span class="timeline-item-icon | faded-icon">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         class="icon icon-tabler icons-tabler-outline icon-tabler-dots">
      <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
      <path d="M5 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/>
      <path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/>
      <path d="M19 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/>
    </svg>
  </span>
  <div class="timeline-item-description">
      <span>
        &plus;&nbsp;<b>{{ $ioc['in_between'] - 2 }}</b>&nbsp;événements similaires
      </span>
  </div>
</li>
@endif
@if($ioc['in_between'] >= 2)
<li id="eid-{{ $ioc['last']['ioc']->id }}" class="timeline-item">
  <span class="timeline-item-hour">
    <span style="margin-left: -92px">{{ $ioc['last']['time'] }}</span>
  </span>
  <span class="timeline-item-icon | faded-icon"
        style="background-color:{{ $ioc['last']['bgColor'] }} !important; color:{{ $ioc['last']['txtColor'] }} !important;">
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
      <span>
        <b>{{ $ioc['last']['ioc']->server_name }} ({{ $ioc['last']['ioc']->server_ip_address }})</b> - {{ $ioc['last']['ioc']->comments }} {{ $ioc['last']['level'] }}
      </span>
    </div>
    <pre class="comment"
         style="margin-bottom: 0;">{{ json_encode($ioc['last']['ioc']->columns, JSON_PRETTY_PRINT) }}</pre>
  </div>
</li>
@endif