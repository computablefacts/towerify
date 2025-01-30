<style>

  .cell {
    text-align: left;
  }

</style>
<div class="row mt-2">
  <div class="col">
    <div id="editor" style="height:300px;width:100%;"></div>
  </div>
</div>
<div class="row mt-2">
  <div class="col align-content-end">
    <button id="execute-sql-query" class="btn btn-primary">{{ __('Execute!') }}</button>
  </div>
</div>
<div class="row mt-2">
  <div class="col">
    <table class="table">
      <thead>
      <tr>
        <th class="cell">{{ __('Tables') }}</th>
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
  elDatabasesAndTables.innerHTML = "<tr><td>{{ __('Loading...') }}</td></tr>";

  document.addEventListener('DOMContentLoaded', function () {
    axios.get(`/cb/web/aws/tables/available`).then(response => {
      if (response.data.success) {
        if (!response.data.tables || response.data.tables.length === 0) {
          elDatabasesAndTables.innerHTML = "<tr><td>{{ __('No tables found.') }}</td></tr>";
        } else {
          const rows = response.data.tables.map(table => {
            return `
                <tr>
                  <td class="cell">${table}</td>
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