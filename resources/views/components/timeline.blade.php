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
    max-width: 800px;
    margin-left: 100px;
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

  .timeline-item-description a {
    color: var(--c-grey-500);
    font-weight: 500;
    text-decoration: none;
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
    <div class="col">
      <x-onboarding-monitor-asset2/>
    </div>
    <div class="col-5">
      <div class="card mb-3">
        <div class="card-body">
          <h6 class="card-title">{{ __('Vous souhaitez poser une question à CyberBuddy ?') }}</h6>
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
    </div>
  </div>
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
@once
<script>

  /* MISC. */
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

  const elAssets = new com.computablefacts.blueprintjs.MinimalSelect(document.getElementById('assets'),
    asset => asset.name, asset => `${asset.high} high - ${asset.medium} medium - ${asset.low} low`);
  elAssets.items = @json($assets);
  elAssets.disabled = elAssets.items.length === 0;
  elAssets.onSelectionChange(item => {
    const url = new URL(window.location);
    if (item) {
      url.searchParams.set('asset_id', item.id);
    } else {
      url.searchParams.set('asset_id', 0);
    }
    window.location.href = url.toString();
  });
  elAssets.defaultText = "{{ __('Select an asset...') }}";

  if ('{{ $assetId }}' > 0) {
    elAssets.selectedItem = elAssets.items.find(asset => asset.id == '{{ $assetId }}');
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