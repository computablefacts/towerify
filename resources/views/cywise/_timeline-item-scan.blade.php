<li class="timeline-item">
  <span class="timeline-item-hour">
    <span style="margin-left: -92px">{{ $time }}</span>
  </span>
  <span class="timeline-item-icon | faded-icon"
        style="@if($vulnsScanEndsAt) background-color: var(--c-blue-500) !important; color: white !important; @endif">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         class="icon icon-tabler icons-tabler-outline icon-tabler-scan">
      <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
      <path d="M4 7v-1a2 2 0 0 1 2 -2h2"/>
      <path d="M4 17v1a2 2 0 0 0 2 2h2"/>
      <path d="M16 4h2a2 2 0 0 1 2 2v1"/>
      <path d="M16 20h2a2 2 0 0 0 2 -2v-1"/>
      <path d="M5 12l14 0"/>
    </svg>
  </span>
  <div class="timeline-item-description">
    <span>
    @if(!$vulnsScanEndsAt)
    Le scan de <a href="#aid-{{ $asset->id }}">{{ $asset->asset }}</a> est en cours.&nbsp;
    @if(!$vulnsScanBeginsAt)
    Recherche de <b>ports ouverts</b>...
    @else
    Recherche de <b>vulnérabilités</b>... ({{ $remaining }}/{{ $total }})
    @endif
    @else
    Le scan de <a
        href="#aid-{{ $asset->id }}">{{ $asset->asset }}</a> s'est terminé <b>sans découvrir de vulnérabilités</b>.
    @endif
    </span>
  </div>
</li>