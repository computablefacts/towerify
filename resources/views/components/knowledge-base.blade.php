<div class="card">
  <div class="card-body p-3">
    <h6 class="card-title">{{ __('Import your documents !') }}</h6>
    <div class="row">
      <div class="col-3">
        <div id="collections"></div>
      </div>
      <div class="col">
        <div id="files"></div>
      </div>
      <div class="col-3">
        <div id="submit">
        </div>
      </div>
    </div>
  </div>
</div>
<div class="card mt-3">
  @if($files->isEmpty())
  <div class="card-body">
    <div class="row">
      <div class="col">
        {{ __('None.') }}
      </div>
    </div>
  </div>
  @else
  <div class="card-body p-0">
    <table class="table table-hover no-bottom-margin">
      <thead>
      <tr>
        <th>{{ __('Collection') }}</th>
        <th>{{ __('Filename') }}</th>
        <th style="text-align:right">{{ __('File Size') }}</th>
        <th style="text-align:right">{{ __('Number of Chunks') }}</th>
        <th style="text-align:right">{{ __('Number of Vectors') }}</th>
        <th>{{ __('Imported At') }}</th>
        <th>{{ __('Imported By') }}</th>
        <th style="text-align:right">{{ __('Integration Status') }}</th>
      </tr>
      </thead>
      <tbody>
      @foreach($files as $file)
      <tr>
        <td>
          <span class="lozenge new">
            <a
              href="{{ route('home', ['tab' => 'chunks', 'page' => 1, 'collection' => $file['collection']]) }}">
            {{ $file['collection'] }}
            </a>
          </span>
        </td>
        <td>
          <a href="{{ $file['download_url'] }}">
            {{ $file['filename'] }}
          </a>
        </td>
        <td style="text-align:right">
          {{ Illuminate\Support\Number::format($file['size'], locale:'sv') }}
        </td>
        <td style="text-align:right">
          {{ Illuminate\Support\Number::format($file['nb_chunks'], locale:'sv') }}
        </td>
        <td style="text-align:right">
          {{ Illuminate\Support\Number::format($file['nb_vectors'], locale:'sv') }}
        </td>
        <td>{{ $file['created_at']->format('Y-m-d H:i') }}</td>
        <td>{{ $file['created_by']->name }}</td>
        <td style="text-align:right">
          @if($file['status'] === 'processed')
          <span class="lozenge success">{{ __($file['status']) }}</span>
          @else
          <span class="lozenge information">{{ __($file['status']) }}</span>
          @endif
        </td>
      </tr>
      @endforeach
      </tbody>
    </table>
    <div class="row">
      <div class="col">
        <ul class="pagination justify-content-center mt-3 mb-3">
          <li class="page-item {{ $currentPage <= 1 ? 'disabled' : '' }}">
            <a class="page-link"
               href="{{ route('home', ['tab' => 'knowledge_base', 'page' => 1, 'collection' => $collection]) }}">
              <span>&laquo;&nbsp;{{ __('First') }}</span>
            </a>
          </li>
          <li class="page-item {{ $currentPage <= 1 ? 'disabled' : '' }}">
            <a class="page-link"
               href="{{ route('home', ['tab' => 'knowledge_base', 'page' => $currentPage <= 1 ? 1 : $currentPage - 1, 'collection' => $collection]) }}">
              <span>&lt;&nbsp;{{ __('Previous') }}</span>
            </a>
          </li>
          <!--
          @if($currentPage > 1)
          <li class="page-item">
            <a class="page-link" href="{{ route('home', ['tab' => 'knowledge_base', 'page' => $currentPage - 1, 'collection' => $collection]) }}">
              {{ $currentPage - 1 }}
            </a>
          </li>
          @endif
          -->
          <li class="page-item">
            <a class="page-link active"
               href="{{ route('home', ['tab' => 'knowledge_base', 'page' => $currentPage, 'collection' => $collection]) }}">
              {{ $currentPage }}
            </a>
          </li>
          <!--
          @if($currentPage < $nbPages)
          <li class="page-item">
            <a class="page-link" href="{{ route('home', ['tab' => 'knowledge_base', 'page' => $currentPage + 1, 'collection' => $collection]) }}">
              {{ $currentPage + 1 }}
            </a>
          </li>
          @endif
          -->
          <li class="page-item {{ $currentPage >= $nbPages ? 'disabled' : '' }}">
            <a class="page-link"
               href="{{ route('home', ['tab' => 'knowledge_base', 'page' => $currentPage >= $nbPages ? $nbPages : $currentPage + 1, 'collection' => $collection])}}">
              <span>{{ __('Next') }}&nbsp;&gt;</span>
            </a>
          </li>
          <li class="page-item {{ $currentPage >= $nbPages ? 'disabled' : '' }}">
            <a class="page-link" href="{{ route('home', ['tab' => 'knowledge_base', 'page' => $nbPages]) }}">
              <span>{{ __('Last') }}&nbsp;&raquo;</span>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
  @endif
</div>
<script>

  let files = null;
  let collection = null;

  const elSubmit = new com.computablefacts.blueprintjs.MinimalButton(document.getElementById('submit'),
    "{{ __('Submit') }}");
  elSubmit.disabled = true;
  elSubmit.onClick(() => {

    elSubmit.loading = true;
    elSubmit.disabled = true;

    const formData = new FormData();
    formData.append('collection', collection);

    for (let i = 0; i < files.length; i++) {
      formData.append('files[]', files[i]);
    }

    axios.post('/cb/web/files/many', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      }
    }).then(response => {
      toaster.toastSuccess("{{ __('Your file has been successfully uploaded. It will be available shortly.') }}");
    }).catch(error => toaster.toastAxiosError(error)).finally(() => {
      elSubmit.loading = false;
      elSubmit.disabled = false;
    });
  });

  const elFile = new com.computablefacts.blueprintjs.MinimalFileInput(document.getElementById('files'), true);
  elFile.onSelectionChange(items => {
    files = items;
    elSubmit.disabled = !files || !collection;
  });
  elFile.buttonText = "{{ __('Browse') }}";

  const elCollections = new com.computablefacts.blueprintjs.MinimalSelect(document.getElementById('collections'), null,
    null, null, query => query);
  elCollections.onSelectionChange(item => {
    collection = item;
    elSubmit.disabled = !files || !collection;
  });
  elCollections.defaultText = "{{ __('Select or create collection...') }}";

  document.addEventListener('DOMContentLoaded', function (event) {
    axios.get('/cb/web/collections').then(response => {
      elCollections.items = response.data.map(collection => collection.name);
    }).catch(error => toaster.toastAxiosError(error));
  });
</script>