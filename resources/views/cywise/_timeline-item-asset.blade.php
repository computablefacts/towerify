<li id='aid-{{ $asset->id }}' class="timeline-item">
  <span class="timeline-item-hour">
    <span style="margin-left: -92px">{{ $time }}</span>
  </span>
  <span class="timeline-item-icon | faded-icon"
        style="background-color: var(--c-blue-500) !important; color: white !important;">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         class="icon icon-tabler icons-tabler-outline icon-tabler-world-www">
      <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
      <path d="M19.5 7a9 9 0 0 0 -7.5 -4a8.991 8.991 0 0 0 -7.484 4"/>
      <path d="M11.5 3a16.989 16.989 0 0 0 -1.826 4"/>
      <path d="M12.5 3a16.989 16.989 0 0 1 1.828 4"/>
      <path d="M19.5 17a9 9 0 0 1 -7.5 4a8.991 8.991 0 0 1 -7.484 -4"/>
      <path d="M11.5 21a16.989 16.989 0 0 1 -1.826 -4"/>
      <path d="M12.5 21a16.989 16.989 0 0 0 1.828 -4"/>
      <path d="M2 10l1 4l1.5 -4l1.5 4l1 -4"/>
      <path d="M17 10l1 4l1.5 -4l1.5 4l1 -4"/>
      <path d="M9.5 10l1 4l1.5 -4l1.5 4l1 -4"/>
    </svg>
  </span>
  <div class="timeline-item-wrapper">
    <div class="timeline-item-description">
      <span>
        <b>{{ $asset->createdBy()->name }}</b> a ajouté l'actif <b>{{ $asset->asset }}</b>
      </span>
    </div>
    <div style="display: flex; gap: 10px;">
      @if(!$asset->is_monitored)
      <button class="show-replies" title="{{ __('Start Monitoring') }}"
              onclick="startMonitoringAsset('{{ $asset->id }}')">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             class="icon icon-tabler icons-tabler-outline icon-tabler-eye">
          <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
          <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/>
          <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/>
        </svg>
      </button>
      <button class="show-replies" title="{{ __('Delete') }}" onclick="deleteAsset('{{ $asset->id }}')">
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
      @else
      <button class="show-replies" title="{{ __('Stop Monitoring') }}"
              onclick="stopMonitoringAsset('{{ $asset->id }}')">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             class="icon icon-tabler icons-tabler-outline icon-tabler-eye-minus">
          <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
          <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/>
          <path
            d="M12 18c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6c-.713 1.188 -1.478 2.199 -2.296 3.034"/>
          <path d="M16 19h6"/>
        </svg>
      </button>
      @endif
    </div>
  </div>
</li>