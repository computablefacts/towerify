<div class="row mt-2">
  <div class="col">
    <textarea id="prompt"
              class="form-control mt-2"
              rows="4"
              placeholder="{{ __('Please provide a detailed prompt to generate a draft SQL query.') }}"></textarea>
  </div>
</div>
<div class="row mt-2">
  <div class="col text-center">
    <button id="prompt-to-query" class="btn btn-primary">{{ __('Generate!') }}</button>
  </div>
</div>
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
        <th class="text-end">{{ __('Number of Rows') }}</th>
        <th class="text-end">{{ __('Number of Columns') }}</th>
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

  const elPromptToQuery = document.getElementById('prompt-to-query');
  elPromptToQuery.addEventListener('click', (event) => {

    event.preventDefault();
    event.stopPropagation();

    const prompt = document.getElementById('prompt').value;

    axios.post(`/cb/web/tables/prompt-to-query`, {prompt: prompt}).then(response => {
      if (response.data.success && response.data.result) {
        editor.setValue(response.data.result);
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
    axios.post(`/cb/web/tables/query`, {query: sql, store: false}).then(response => {
      if (response.data.success) {
        elTableHead.innerHTML = `
          <tr>${response.data.result[0].map(column => `<th>${column}</th>`).join('')}</tr>
        `;
        if (response.data.result.length === 1) {
          elTableBody.innerHTML = `<tr><td colspan='${response.data.result[0].length}'>{{ __('No results found.') }}</td></tr>`;
        } else {
          elTableBody.innerHTML = response.data.result.slice(1)
          .map(row => `<tr>${row.map(column => `<td>${column}</td>`).join('')}</tr>`)
          .join('');
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