<div class="card">
  @if($frameworks->isEmpty())
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
        <th>{{ __('Provider') }}</th>
        <th>{{ __('Framework') }}</th>
        <th>{{ __('Version') }}</th>
        <th>{{ __('Language') }}</th>
        <th></th>
      </tr>
      </thead>
      <tbody>
      @foreach($frameworks as $framework)
      <tr>
        <td>
          <span class="lozenge information">{{ \Illuminate\Support\Str::lower($framework->provider) }}</span>
        </td>
        <td>
          <span class="font-lg mb-3 fw-bold">
            {{ \Illuminate\Support\Str::upper($framework->name) }}
          </span>
          <div class="text-muted">
            {{ $framework->description }}
          </div>
        </td>
        <td>{{ $framework->version }}</td>
        <td>
          <span class="lozenge new">{{ $framework->locale }}</span>
        </td>
        <td>
          @if($framework->loaded())
          <a href="#" onclick="unload({{ $framework->id }}, event)" class="text-danger">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
              <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
              <path d="M20 11a8.1 8.1 0 0 0 -11.271 -6.305m-2.41 1.624a8.083 8.083 0 0 0 -1.819 2.681m-.5 -4v4h4"/>
              <path d="M4 13a8.1 8.1 0 0 0 13.671 4.691m2.329 -1.691v-1h-1"/>
              <path d="M3 3l18 18"/>
            </svg>
          </a>
          @else
          <a href="#" onclick="load({{ $framework->id }}, event)" class="text-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
              <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
              <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"/>
              <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"/>
            </svg>
          </a>
          @endif
        </td>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
<script>

  const load = (id, event) => {
    event.preventDefault();
    event.stopPropagation();
    axios.post(`/cb/web/frameworks/${id}`, {}).then(function (response) {
      if (response.data.success) {
        toaster.toastSuccess(response.data.success);
      } else if (response.data.error) {
        toaster.toastError(response.data.error);
      } else {
        console.log(response.data);
      }
    }).catch(error => toaster.toastAxiosError(error));
  };

  const unload = (id, event) => {
    event.preventDefault();
    event.stopPropagation();
    axios.delete(`/cb/web/frameworks/${id}`).then(function (response) {
      if (response.data.success) {
        toaster.toastSuccess(response.data.success);
      } else if (response.data.error) {
        toaster.toastError(response.data.error);
      } else {
        console.log(response.data);
      }
    }).catch(error => toaster.toastAxiosError(error));
  };

</script>