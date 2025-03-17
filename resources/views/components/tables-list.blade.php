<table class="table table-hover no-bottom-margin">
  <thead>
  <tr>
    <th>{{ __('Table') }}</th>
    <th class="text-end">{{ __('Number of Rows') }}</th>
    <th class="text-end">{{ __('Number of Columns') }}</th>
    <th>{{ __('Description') }}</th>
    <th>{{ __('Last Update') }}</th>
    <th>{{ __('Status') }}</th>
  </tr>
  </thead>
  <tbody id="databases-and-tables">
  <!-- FILLED DYNAMICALLY -->
  </tbody>
</table>
<script>

  const elDatabasesAndTables = document.getElementById('databases-and-tables');
  elDatabasesAndTables.innerHTML = "<tr><td colspan='6'>{{ __('Loading...') }}</td></tr>";

  document.addEventListener('DOMContentLoaded', function () {
    axios.get(`/cb/web/tables/available`).then(response => {
      if (response.data.success) {
        if (!response.data.tables || response.data.tables.length === 0) {
          elDatabasesAndTables.innerHTML = "<tr><td colspan='6'>{{ __('No tables found.') }}</td></tr>";
        } else {
          const rows = response.data.tables.map(table => {
            return `
                <tr>
                  <td>
                    <span class="lozenge new">${table.name}</span>
                  </td>
                  <td class="text-end">${table.nb_rows}</td>
                  <td class="text-end">${table.nb_columns}</td>
                  <td>${table.description}</td>
                  <td>${table.last_update}</td>
                  <td>${table.status}</td>
                </tr>
              `;
          });
          elDatabasesAndTables.innerHTML = rows.join('');
        }
      } else if (response.data.error) {
        toaster.toastError(response.data.error);
      } else {
        console.log(response.data);
      }
    })
        .catch(error => toaster.toastAxiosError(error));
  });

</script>