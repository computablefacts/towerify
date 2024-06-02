@if(Auth::user()->canListServers())
<div class="card card-accent-secondary tw-card">
  <div class="card-header">
    <h3 class="m-0"><b>{{ __('Domains') }}</b></h3>
  </div>
  @if($domains->isEmpty())
  <div class="card-body">
    <div class="row">
      <div class="col">
        None.
      </div>
    </div>
  </div>
  @else
  <div class="card-body p-0">
    <table class="table table-hover">
      <thead>
      <tr>
        <th>
          <i class="zmdi zmdi-long-arrow-down"></i>&nbsp;{{ __('Name') }}
        </th>
        <th></th>
      </tr>
      </thead>
      <tbody>
      @foreach($domains->sortBy('name', SORT_NATURAL|SORT_FLAG_CASE) as $domain)
      <tr>
        <td>
          {{ $domain->name }}
        </td>
        <td>
          @if($domain->is_principal)
          <span class="tw-pill rounded-pill bg-success float-end">
            principal
          </span>
          @endif
        </td>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
@endif