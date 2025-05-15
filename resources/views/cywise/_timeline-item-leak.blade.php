@once
<style>
  .timeline-item-wrapper table {
    border-collapse: collapse;
    caption-side: bottom;
    display: table;
    width: 100%;
    font-size: 0.8rem;
    margin-top: 0;
  }

  .timeline-item-wrapper table thead {
    border-top-width: 1px;
    display: table-header-group;
    font-weight: 500;
    border-color: rgb(226, 232, 240);
    border-style: solid;
  }

  .timeline-item-wrapper table tr {
    border-bottom-width: 1px;
    display: table-row;
    border-color: rgb(226, 232, 240);
    border-style: solid;
  }

  .timeline-item-wrapper table tbody {
    display: table-row-group
  }

  .timeline-item-wrapper table thead tr th {
    padding: 0.5rem;
    vertical-align: middle;
    display: table-cell;
    height: 2rem;
  }

  .timeline-item-wrapper table tbody tr td {
    padding: 0.5rem;
    vertical-align: middle;
    display: table-cell;
  }

</style>
@endonce
<li class="timeline-item">
  <span class="timeline-item-hour">
    <span style="margin-left: -92px">{{ $time }}</span>
  </span>
  <span class="timeline-item-icon | faded-icon"
        style="background-color: #ff4d4d !important; color: white !important;">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         class="icon icon-tabler icons-tabler-outline icon-tabler-password-user">
      <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
      <path d="M12 17v4"/>
      <path d="M10 20l4 -2"/>
      <path d="M10 18l4 2"/>
      <path d="M5 17v4"/>
      <path d="M3 20l4 -2"/>
      <path d="M3 18l4 2"/>
      <path d="M19 17v4"/>
      <path d="M17 20l4 -2"/>
      <path d="M17 18l4 2"/>
      <path d="M9 6a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"/>
      <path d="M7 14a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2"/>
    </svg>
  </span>
  <div class="timeline-item-wrapper">
    <div class="timeline-item-description">
      <span>Nous avons trouvé <b>{{ count(json_decode($leak->attributes()['credentials'])) }} identifiants fuités ou compromis</b>. Si aucune action n'a encore été entreprise, demandez aux utilisateurs concernés de modifier leur mot de passe.</span>
    </div>
    <div class="comment" style="margin-bottom: 0;">
    <table>
      <thead>
      <tr>
        <th>{{ __('Email') }}</th>
        <th>{{ __('Website') }}</th>
        <th>{{ __('Password') }}</th>
        <th></th>
      </tr>
      </thead>
      <tbody>
      @foreach(json_decode($leak->attributes()['credentials']) as $l)
      <tr>
        <td>{{ $l->email }}</td>
        <td>{{ empty($l->website) ? '-' : $l->website }}</td>
        <td>{{ empty($l->password) ? '-' : $l->password }}</td>
        <td>
          <span class="lozenge new" style="font-size: 0.8rem;">
            {{ empty($l->website) ? __('fuite de données') : __('possible compromission') }}
          </span>
        </td>
      </tr>
      @endforeach
      </tbody>
    </table>
    </div>
  </div>
</li>