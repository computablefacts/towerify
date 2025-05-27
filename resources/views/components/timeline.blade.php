<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/charts.css/dist/charts.min.css">
<style>

  @import url("https://fonts.googleapis.com/css2?family=Outfit:wght@100;200;300;400;500;600;700;800;900&display=swap");

  :root {
    --c-grey-100: #f4f6f8;
    --c-grey-200: #e3e3e3;
    --c-grey-300: #b2b2b2;
    --c-grey-400: #7b7b7b;
    --c-grey-500: #3d3d3d;
    --c-blue-500: #688afd;
  }

  .timeline {
    width: 85%;
    max-width: 100%;
    margin-left: 80px;
    margin-right: auto;
    display: flex;
    flex-direction: column;
    padding: 32px 0 32px 32px;
    border-left: 2px solid var(--c-grey-200);
    font-size: 1rem;
    margin-bottom: 0;
  }

  .timeline-item {
    display: flex;
    gap: 24px;
  }

  .timeline-item + * {
    margin-top: 24px;
  }

  .timeline-item + .extra-space {
    margin-top: 48px;
  }

  .new-comment {
    width: 100%;
  }

  .new-comment input {
    border: 1px solid var(--c-grey-200);
    border-radius: 6px;
    height: 48px;
    padding: 0 16px;
    width: 100%;
  }

  .new-comment input::-moz-placeholder {
    color: var(--c-grey-300);
  }

  .new-comment input:-ms-input-placeholder {
    color: var(--c-grey-300);
  }

  .new-comment input::placeholder {
    color: var(--c-grey-300);
  }

  .new-comment input:focus {
    border-color: var(--c-grey-300);
    outline: 0;
    box-shadow: 0 0 0 4px var(--c-grey-100);
  }

  .timeline-item-hour {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    margin-left: -65px;
    flex-shrink: 0;
    color: var(--c-grey-400);
  }

  .timeline-item-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-left: -52px;
    flex-shrink: 0;
    overflow: hidden;
    box-shadow: 0 0 0 6px #fff;
  }

  .timeline-item-icon svg {
    width: 20px;
    height: 20px;
  }

  .timeline-item-icon.faded-icon {
    background-color: var(--c-grey-100);
    color: var(--c-grey-400);
  }

  .timeline-item-icon.filled-icon {
    background-color: var(--c-blue-500);
    color: #fff;
  }

  .timeline-item-description {
    display: flex;
    gap: 8px;
    color: var(--c-grey-400);
    align-items: center;
  }

  .timeline-item-description img {
    flex-shrink: 0;
  }

  .timeline-item-description b {
    color: var(--c-grey-500);
    font-weight: 500;
    text-decoration: none;
  }

  .timeline-item-description a {
    color: var(--c-grey-500);
    font-weight: 500;
    text-decoration: none;
    border-bottom: 1px dashed;
  }

  .timeline-item-description a:hover, .timeline-item-description a:focus {
    outline: 0;
    color: var(--c-blue-500);
  }

  .avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    overflow: hidden;
    aspect-ratio: 1/1;
    flex-shrink: 0;
    width: 40px;
    height: 40px;
  }

  .avatar.small {
    width: 28px;
    height: 28px;
  }

  .avatar img {
    -o-object-fit: cover;
    object-fit: cover;
  }

  .comment {
    margin-top: 12px;
    color: var(--c-grey-500);
    border: 1px solid var(--c-grey-200);
    box-shadow: 0 4px 4px 0 var(--c-grey-100);
    border-radius: 6px;
    padding: 16px;
    font-size: 0.8rem;
  }

  .button {
    border: 0;
    padding: 0;
    display: inline-flex;
    vertical-align: middle;
    margin-right: 4px;
    margin-top: 12px;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    height: 32px;
    padding: 0 8px;
    background-color: var(--c-grey-100);
    flex-shrink: 0;
    cursor: pointer;
    border-radius: 99em;
  }

  .button:hover {
    background-color: var(--c-grey-200);
  }

  .button.square {
    border-radius: 50%;
    color: var(--c-grey-400);
    f
    width: 32px;
    height: 32px;
    padding: 0;
  }

  .button.square svg {
    width: 24px;
    height: 24px;
  }

  .button.square:hover {
    background-color: var(--c-grey-200);
    color: var(--c-grey-500);
  }

  .show-replies {
    color: var(--c-grey-300);
    background-color: transparent;
    border: 0;
    padding: 0;
    margin-top: 16px;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 1rem;
    cursor: pointer;
  }

  .show-replies svg {
    flex-shrink: 0;
    width: 24px;
    height: 24px;
  }

  .show-replies:hover, .show-replies:focus {
    color: var(--c-grey-500);
  }

  .avatar-list {
    display: flex;
    align-items: center;
  }

  .avatar-list > * {
    position: relative;
    box-shadow: 0 0 0 2px #fff;
    margin-right: -8px;
  }
</style>
<div class="container p-0">
  @if(Auth::user()->isInTrial())
  <div class="alert alert-danger border border-danger">
    {{ __('Your account is in the trial period until :date.', ['date' => Auth::user()->endOfTrial()->format('Y-m-d')])
    }}
  </div>
  @endif
  <div class="row">
    <div class="col-md-8">
      <div class="card mb-3">
        <div class="card-body">
          <h6 class="card-title">{{ __('Filtrer la timeline par...') }}</h6>
          <div class="row">
            <div class="col-4">
              <b>{{ __('Category') }}</b>
            </div>
            <div class="col-4">
              <b>{{ __('Asset') }}</b>
            </div>
            <div class="col">
              <b>{{ __('Date') }}</b>
            </div>
          </div>
          <div class="row" style="margin-top: 5px;">
            <div class="col-4">
              <div id="categories"></div>
            </div>
            <div class="col-4">
              <div id="assets"></div>
            </div>
            <div class="col">
              <div id="dates"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="card mb-3">
        <div class="card-body">
          <ol class="timeline">
            <li class="timeline-item">
              <span class="timeline-item-icon | filled-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="icon icon-tabler icons-tabler-outline icon-tabler-pencil">
                  <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                  <path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4"/>
                  <path d="M13.5 6.5l4 4"/>
                </svg>
              </span>
              <div class="new-comment">
                <input type="text" placeholder="Ajouter une note... (entrée pour valider)"/>
              </div>
            </li>
          </ol>
          @foreach($messages as $date => $times)
          @if(empty($dateId) || $date === $dateId)
          @include('cywise._timeline-separator', ['date' => $date])
          <ol class="timeline">
            @foreach($times as $time => $events)
            @foreach($events as $event)
            {!! $event['html'] !!}
            @endforeach
            @endforeach
          </ol>
          @endif
          @endforeach
        </div>
      </div>
    </div>
    <div class="col" style="padding-left: 0;">
      <div class="card mb-3">
        <div class="card-body">
          <h6 class="card-title">
            {{ __('Vous souhaitez poser une question à CyberBuddy ?') }}
          </h6>
          <div class="card-text mb-3">
            {{ __('Cliquez ici pour lancer CyberBuddy :') }}
          </div>
          <form>
            <div class="row">
              <div class="col align-content-center">
                <a href="{{ route('home', ['tab' => 'ama2']) }}"
                   class="btn btn-primary" style="width: 100%;">
                  {{ __('Start Conversation >') }}
                </a>
              </div>
            </div>
          </form>
        </div>
      </div>
      <x-onboarding-monitor-asset2/>
      @if(count($blacklist) > 0)
      <div class="card mb-3">
        <div class="card-body">
          <h6 class="card-title">
            {{ __('Vous souhaitez réduire vos risques ?') }}
          </h6>
          <div class="card-text mb-3">
            {{ __('Cliquez ici pour télécharger une blacklist d\'IP suspectes ciblant votre infrastructure :') }}
          </div>
          <form>
            <div class="row">
              <div class="col align-content-center">
                <a href="#" class="btn btn-primary" style="width: 100%;" onclick="downloadBlacklist()">
                  {{ __('Download!') }}
                </a>
              </div>
            </div>
          </form>
        </div>
      </div>
      @endif
      @foreach($honeypots as $honeypot)
      <div class="card mb-3">
        <div class="card-body">
          <h6 class="card-title text-truncate">
            {{ $honeypot['type'] }}&nbsp;<span style="color: #ffaa00;">/</span>&nbsp;{{ $honeypot['name'] }}
          </h6>
          @if(\Illuminate\Support\Str::endsWith($honeypot['name'], '.cywise.io'))
          <p>{{ __('Vous souhaitez rediriger un de vos domaines vers ce honeypot ? Contactez le support !') }}</p>
          @endif
          @if(count($honeypot['counts']) <= 0)
          <p>{{ __('Aucun événement récent.') }}</p>
          @else
          <div class="card-text mb-3">
            <table
              class="charts-css column hide-data show-labels show-primary-axis show-3-secondary-axes data-spacing-3 multiple stacked">
              <thead>
              <tr>
                <th scope="col">Date</th>
                <th scope="col">Human or Targeted</th>
                <th scope="col">Bots</th>
              </tr>
              </thead>
              <tbody>
              @foreach($honeypot['counts'] as $count)
              <tr>
                <th scope="row">{{ \Illuminate\Support\Str::after($count['date'], '-') }}</th>
                <td
                  style="--size: calc({{ $count['human_or_targeted'] }} / {{ $honeypot['max'] }});">
                  <span class="data">{{ $count['human_or_targeted'] }}</span>
                  <span class="tooltip">Human or Targeted: {{ $count['human_or_targeted'] }}</span>
                </td>
                <td
                  style="--size: calc({{ $count['not_human_or_targeted'] }} / {{ $honeypot['max'] }});">
                  <span class="data">{{ $count['not_human_or_targeted'] }}</span>
                  <span class="tooltip">Bots: {{ $count['not_human_or_targeted'] }}</span>
                </td>
              </tr>
              @endforeach
              </tbody>
            </table>
          </div>
          @endif
          @if(isset($mostRecentHoneypotEvents[$honeypot['name']]))
          <div class="card-text mb-3">
            <table class="table">
              <thead>
              <tr>
                <th colspan="3">
                  {!! __('The&nbsp;<span style="color: #ffaa00;">5</span>&nbsp;most recent attacks') !!}
                </th>
              </tr>
              </thead>
              <tbody>
              @foreach($mostRecentHoneypotEvents[$honeypot['name']]['events'] as $event)
              <tr title="{{ $event['event_details'] }}">
                <td style="color: var(--c-blue-500);">
                  @if($event['attacker_name'] !== '-')
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                       class="icon icon-tabler icons-tabler-outline icon-tabler-user">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/>
                    <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/>
                  </svg>
                  @else
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                       class="icon icon-tabler icons-tabler-outline icon-tabler-robot">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M6 4m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z"/>
                    <path d="M12 2v2"/>
                    <path d="M9 12v9"/>
                    <path d="M15 12v9"/>
                    <path d="M5 16l4 -2"/>
                    <path d="M15 14l4 2"/>
                    <path d="M9 18h6"/>
                    <path d="M10 8v.01"/>
                    <path d="M14 8v.01"/>
                  </svg>
                  @endif
                </td>
                <td>
                  {{ $event['timestamp'] }}
                </td>
                <td>
                  {{ $event['event_type'] }}
                </td>
              </tr>
              @endforeach
              </tbody>
            </table>
          </div>
          @endif
        </div>
      </div>
      @endforeach
    </div>
  </div>
</div>
@once
<script>

  /* MISC. */
  const downloadCsv = (filename, csv) => {

    // Here, filename = "my_file.csv"
    // Here, csv = [["asset","creation_date","type"], ["www.computablefacts.com","2020-09-07T12:34:29Z","DNS"], ["127.0.0.1","2020-09-07T12:34:29Z","IP"], ...]

    const rows = csv.map((row) => row.join(","));
    const blob = new Blob([rows.join("\n")], {type: "text/csv;charset=utf-8"});
    const isIE = false || !!document.documentMode;

    if (isIE) {
      window.navigator.msSaveBlob(blob, filename);
    } else {
      const url = window.URL || window.webkitURL;
      const link = url.createObjectURL(blob);
      const a = document.createElement("a");
      a.download = filename;
      a.href = link;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
    }
  }

  const downloadBlacklist = () => downloadCsv(`blacklist_${today}.csv`,
    [['IP', 'Premier contact', 'Dernier contact', 'Pays', 'Fournisseur']].concat(
      @json($blacklist).map(item => [item.ip, item.firstContact, item.lastContact, item.countryCode, item.provider])));

  const today = (() => {
    const date = new Date();
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  })();

  const todaySeparatorHtmlTemplate = '{!! $todaySeparator !!}';

  /* FILTERS */
  const elDates = new com.computablefacts.blueprintjs.MinimalSelect(document.getElementById('dates'));
  elDates.items = @json($dates);
  elDates.disabled = elDates.items.length === 0;
  elDates.onSelectionChange(item => {
    const url = new URL(window.location);
    if (item) {
      url.searchParams.set('date', item);
    } else {
      url.searchParams.set('date', '');
    }
    window.location.href = url.toString();
  });
  elDates.defaultText = "{{ __('Select a date...') }}";

  if ('{{ $dateId }}' !== '') {
    elDates.selectedItem = elDates.items.find(date => date === '{{ $dateId }}');
  }

  const assets = @json($assets);
  const servers = @json($servers);
  const elAssets = new com.computablefacts.blueprintjs.MinimalSelect(document.getElementById('assets'),
    asset => asset.name, asset => {
      if (asset.type === 'server') {
        return `${asset.ip_address}`;
      }
      return `${asset.high} high - ${asset.medium} medium - ${asset.low} low`;
    });
  elAssets.items = assets.concat(servers).sort((a, b) => a.name.localeCompare(b.name));
  elAssets.disabled = elAssets.items.length === 0;
  elAssets.onSelectionChange(item => {
    const url = new URL(window.location);
    if (item) {
      if (item.type === 'server') {
        url.searchParams.set('server_id', item.id);
        url.searchParams.set('asset_id', 0);
      } else {
        url.searchParams.set('server_id', 0);
        url.searchParams.set('asset_id', item.id);
      }
    } else {
      url.searchParams.set('server_id', 0);
      url.searchParams.set('asset_id', 0);
    }
    window.location.href = url.toString();
  });
  elAssets.defaultText = "{{ __('Select an asset...') }}";

  if ('{{ $assetId }}' > 0) {
    elAssets.selectedItem = elAssets.items.find(asset => asset.type === 'asset' && asset.id == '{{ $assetId }}');
  }
  if ('{{ $serverId }}' > 0) {
    elAssets.selectedItem = elAssets.items.find(asset => asset.type === 'server' && asset.id == '{{ $serverId }}');
  }

  const elCategories = new com.computablefacts.blueprintjs.MinimalSelect(document.getElementById('categories'));
  elCategories.items = @json($categories);
  elCategories.disabled = elCategories.items.length === 0;
  elCategories.onSelectionChange(item => {
    const url = new URL(window.location);
    if (item) {
      url.searchParams.set('category', item);
    } else {
      url.searchParams.set('category', '');
    }
    url.searchParams.set('server_id', 0);
    url.searchParams.set('asset_id', 0);
    window.location.href = url.toString();
  });
  elCategories.defaultText = "{{ __('Select a category...') }}";

  if ('{{ $categoryId }}' !== '') {
    elCategories.selectedItem = elCategories.items.find(date => date === '{{ $categoryId }}');
  }

  /* NOTES */
  const elInputField = document.querySelector('.new-comment input');
  elInputField.addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
      event.preventDefault();
      if (elInputField.value.trim() !== '') {
        createNoteApiCall(elInputField.value.trim(), (response) => {
          elInputField.value = null;
          const elNote = (new DOMParser()).parseFromString(response.html, 'text/html');
          let elTodaySeparator = document.querySelector(`#sid-${today}`);
          if (elTodaySeparator) {
            const elOl = elTodaySeparator.nextElementSibling;
            elOl.insertBefore(elNote.body.firstChild, elOl.firstElementChild);
          } else {
            elTodaySeparator = (new DOMParser()).parseFromString(todaySeparatorHtmlTemplate, 'text/html');
            const elTimelines = document.querySelectorAll(`.timeline`);
            if (elTimelines.length >= 1) {

              // Insert OL
              const elOl = document.createElement('ol')
              elOl.classList.add('timeline');
              elTimelines[0].parentNode.insertBefore(elOl, elTimelines[0].nextElementSibling);

              // Insert separator
              elTimelines[0].parentNode.insertBefore(elTodaySeparator.body.firstChild,
                elTimelines[0].nextElementSibling);

              // Fill OL with note
              elOl.appendChild(elNote.body.firstChild);
            }
          }
          return response;
        });
      }
    }
  });

  /* VULNERABILITIES */
  const hideByUid = (uid) => {
    toggleVulnerabilityVisibilityApiCall(uid, null, null);
  }

  const hideByType = (type) => {
    toggleVulnerabilityVisibilityApiCall(null, type, null);
  }

  const hideByTitle = (title) => {
    toggleVulnerabilityVisibilityApiCall(null, null, title);
  }

  /* EVENTS */
  const dismissEvent = (eventId) => {
    dismissEventApiCall(eventId);
  }

  /* API CALLS */
  const apiCall = (method, url, params = {}, body = null) => {

    let fullUrl = "{{ app_url() }}/api" + url;

    if (method.toUpperCase() === "GET" && Object.keys(params).length > 0) {
      const queryParams = new URLSearchParams(params).toString();
      fullUrl += "?" + queryParams;
    }

    const headers = {
      'Content-Type': 'application/json', 'Authorization': 'Bearer {{ Auth::user()->sentinelApiToken() }}',
    };

    const options = {
      method: method, headers: headers, body: body ? JSON.stringify(body) : null,
    };

    return fetch(fullUrl, options).catch(error => {
      toaster.toastError(error);
      console.error(error);
    });
  }

  const startMonitoringAsset = (assetId) => {
    apiCall('POST', `/inventory/asset/${assetId}/monitoring/begin`)
    .then((response) => {
      if (response.ok) {
        toaster.toastSuccess('The monitoring started.');
      } else {
        toaster.toastError('An error occurred.')
      }
    });
  }

  const stopMonitoringAsset = (assetId) => {
    apiCall('POST', `/inventory/asset/${assetId}/monitoring/end`)
    .then((response) => {
      if (response.ok) {
        toaster.toastSuccess('The monitoring stopped.');
      } else {
        toaster.toastError('An error occurred.')
      }
    });
  }

  const deleteAsset = (assetId) => {
    apiCall('DELETE', `/adversary/assets/${assetId}`)
    .then((response) => {
      if (response.ok) {
        toaster.toastSuccess('The asset will be deleted soon.');
      } else {
        toaster.toastError('An error occurred.')
      }
    });
  }

  const restartScan = (assetId) => {
    apiCall('POST', `/adversary/assets/restart/${assetId}`)
    .then((response) => {
      if (response.ok) {
        toaster.toastSuccess('The scan has been restarted.');
      } else {
        toaster.toastError('An error occurred.')
      }
    });
  }

  const deleteNote = (noteId) => {
    deleteNoteApiCall(noteId, (response) => {
      const elNote = document.querySelector(`#nid-${noteId}`);
      if (elNote) {
        elNote.remove();
      }
      return response;
    });
  }

  const deleteConversation = (conversationId) => {

    const response = confirm("{{ __('Are you sure you want to delete this conversation?') }}");

    if (response) {
      axios.delete(`/conversations/${conversationId}`).then(function (response) {
        if (response.data.success) {
          toaster.toastSuccess(response.data.success);
        } else if (response.data.error) {
          toaster.toastError(response.data.error);
        } else {
          console.log(response.data);
        }
      }).catch(error => toaster.toastAxiosError(error));
    }
  }

</script>
@endonce