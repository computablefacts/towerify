<div class="row mt-2">
  <div class="col">
    <div id="editor" style="height:300px;width:100%;"></div>
  </div>
</div>
<div class="row mt-2">
  <div class="col text-center">
    <button id="execute-sql-query" class="btn btn-primary">{{ __('Execute!') }}</button>
  </div>
</div>
<div class="row mt-2">
  <div class="col">
    <table id="query-result" class="table">
      <thead>
      <!-- FILLED DYNAMICALLY -->
      </thead>
      <tbody>
      <!-- FILLED DYNAMICALLY -->
      </tbody>
    </table>
  </div>
</div>
<div class="row mt-2">
  <div class="col">
    <table class="table">
      <thead>
      <tr>
        <th>{{ __('Table') }}</th>
        <th>{{ __('Description') }}</th>
        <th>{{ __('Last Update') }}</th>
        <th>{{ __('Last Error') }}</th>
      </tr>
      </thead>
      <tbody id="databases-and-tables">
      <!-- FILLED DYNAMICALLY -->
      </tbody>
    </table>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.6.0/ace.js"></script>
<script>

  const editor = ace.edit("editor");
  editor.setTheme("ace/theme/monokai");
  editor.session.setMode("ace/mode/sql");

  const elDatabasesAndTables = document.getElementById('databases-and-tables');
  elDatabasesAndTables.innerHTML = "<tr><td colspan='4'>{{ __('Loading...') }}</td></tr>";

  document.addEventListener('DOMContentLoaded', function () {
    axios.get(`/cb/web/aws/tables/available`).then(response => {
      if (response.data.success) {
        if (!response.data.tables || response.data.tables.length === 0) {
          elDatabasesAndTables.innerHTML = "<tr><td colspan='4'>{{ __('No tables found.') }}</td></tr>";
        } else {
          const rows = response.data.tables.map(table => {
            return `
                <tr>
                  <td>${table.name}</td>
                  <td>${table.description}</td>
                  <td>${table.last_update}</td>
                  <td>${table.last_error}</td>
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

  const elExecuteSqlQuery = document.getElementById('execute-sql-query');
  elExecuteSqlQuery.addEventListener('click', (event) => {

    event.preventDefault();
    event.stopPropagation();

    const elQueryResult = document.getElementById('query-result');
    const elTableHead = elQueryResult.querySelector('thead');
    const elTableBody = elQueryResult.querySelector('tbody');

    elTableHead.innerHTML = "<tr><th></th></tr>";
    elTableBody.innerHTML = "<tr><td>{{ __('Loading...') }}</td></tr>";

    const sql = editor.getValue();
    axios.post(`/cb/web/aws/tables/query`, {query: sql, store: false}).then(response => {
      if (response.data.success) {
        elTableHead.innerHTML = `
          <tr>${response.data.result[0].map(column => `<th>${column}</th>`).join('')}</tr>
        `;
        elTableBody.innerHTML = response.data.result.slice(1)
        .map(row => `<tr>${row.map(column => `<td>${column}</td>`).join('')}</tr>`)
        .join('');
      } else if (response.data.error) {
        toaster.toastError(response.data.error);
      } else {
        console.log(response.data);
      }
    })
    .catch(error => toaster.toastAxiosError(error));
  });

</script>