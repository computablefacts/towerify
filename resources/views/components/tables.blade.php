<style>

  table {
    table-layout: fixed;
  }

  td {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .steps {
    display: flex;
    justify-content: space-between;
  }

  .step {
    padding: 10px 15px;
    color: #007bff;
    flex: 1;
    text-align: center;
  }

  .step.active {
    background: #007bff;
    color: white;
    font-weight: bold;
  }

  .content {
    background-color: white;
  }

  .step-content {
    display: none;
  }

  .step-content.active {
    display: block;
  }

</style>
<div class="steps mb-2">
  <div class="step active" data-step="1">
    {{ __('Step 1') }}
  </div>
  <div class="step" data-step="2">
    {{ __('Step 2') }}
  </div>
  <div class="step" data-step="3">
    {{ __('Step 3') }}
  </div>
  <div class="step" data-step="4">
    {{ __('Step 4') }}
  </div>
  <div class="step" data-step="5">
    {{ __('Step 5') }}
  </div>
  <div class="step" data-step="6">
    {{ __('Step 6') }}
  </div>
</div>
<div class="content">
  <div class="card step-content active">
    <div class="card-body">
      <h5 class="card-title">
        {{ __('1. What kind of table would you like to create?') }}
      </h5>
      <div class="row mt-2">
        <div class="col">
          <div id="table-kinds-container"></div>
        </div>
      </div>
      <div class="row mt-2">
        <div class="col text-center">
          <button class="btn btn-primary next-button" data-next="2">{{ __('Next >') }}</button>
        </div>
      </div>
    </div>
  </div>
  <div class="card step-content">
    <div class="card-body">
      <h5 class="card-title">
        {{ __('2. What are the credentials for your AWS bucket?') }}
      </h5>
      <div class="row mt-2">
        <div class="col col-2 align-content-center text-end">
          <b>{{ __('Region') }}</b>
        </div>
        <div class="col">
          <div id="aws-region"></div>
        </div>
      </div>
      <div class="row mt-2">
        <div class="col col-2 align-content-center text-end">
          <b>{{ __('Access Key Id') }}</b>
        </div>
        <div class="col">
          <div id="aws-access-key-id"></div>
        </div>
      </div>
      <div class="row mt-2">
        <div class="col col-2 align-content-center text-end">
          <b>{{ __('Secret Access Key') }}</b>
        </div>
        <div class="col">
          <div id="aws-secret-access-key"></div>
        </div>
      </div>
      <div class="row mt-2">
        <div class="col col-2 align-content-center text-end">
          <b>{{ __('Input Folder') }}</b>
        </div>
        <div class="col">
          <div id="aws-input-folder"></div>
        </div>
      </div>
      <div class="row mt-2">
        <div class="col col-2 align-content-center text-end">
          <b>{{ __('Output Folder') }}</b>
        </div>
        <div class="col">
          <div id="aws-output-folder"></div>
        </div>
      </div>
      <div class="row mt-2">
        <div class="col text-center">
          <button class="btn btn-primary prev-button" data-prev="1">{{ __('< Previous') }}</button>
          <button class="btn btn-primary next-button" data-next="3">{{ __('Next >') }}</button>
        </div>
      </div>
    </div>
  </div>
  <div class="card step-content">
    <div class="card-body">
      <h5 class="card-title">
        {{ __('3. Which table would you like to import?') }}
      </h5>
      <div class="row mt-2">
        <div class="col">
          <table class="table">
            <thead>
            <tr>
              <th style="width:30px"></th>
              <th>{{ __('Filename') }}</th>
              <th class="text-end">{{ __('File Size') }}</th>
              <th class="text-end">{{ __('Last Modified') }}</th>
            </tr>
            </thead>
            <tbody id="aws-tables">
            <!-- FILLED DYNAMICALLY -->
            </tbody>
          </table>
        </div>
      </div>
      <div class="row mt-2">
        <div class="col text-center">
          <button class="btn btn-primary prev-button" data-prev="2">{{ __('Previous') }}</button>
          <button id="get-columns" class="btn btn-primary next-button" data-next="4">{{ __('Next >') }}</button>
        </div>
      </div>
    </div>
  </div>
  <div class="card step-content">
    <div class="card-body">
      <h5 class="card-title">
        {{ __('4. Which columns would you like to retain?') }}
      </h5>
      <div class="row mt-2">
        <div class="col">
          <textarea id="aws-table-description"
                    class="form-control mt-2"
                    rows="4"
                    placeholder="{{ __('Please provide a detailed description of the table and explain the significance of the key columns. The more information you include, the better.') }}"></textarea>
        </div>
      </div>
      <div class="row mt-2">
        <div class="col">
          <input type="checkbox" id="toggle-columns-selection"/>
          <label for="toggle-columns-selection">{{ __('Toggle selection') }}</label>
        </div>
      </div>
      <div class="row mt-2">
        <div class="col">
          <input type="checkbox" id="toggle-deduplicate" checked/>
          <label for="toggle-deduplicate">{{ __('Deduplicate rows') }}</label>
        </div>
      </div>
      <div class="row mt-2">
        <div class="col">
          <input type="checkbox" id="toggle-copy"/>
          <label for="toggle-copy">{{ __('Copy') }}</label>
        </div>
      </div>
      <div class="row mt-2">
        <div class="col">
          <input type="checkbox" id="toggle-updatable"/>
          <label for="toggle-updatable">{{ __('Update Automatically') }}</label>
        </div>
      </div>
      <div class="row mt-2">
        <div class="col">
          <table class="table">
            <thead>
            <tr>
              <th style="width:30px"></th>
              <th>{{ __('Filename') }}</th>
              <th>{{ __('Old Column Name') }}</th>
              <th>{{ __('New Column Name') }}</th>
              <th>{{ __('Column Type') }}</th>
            </tr>
            </thead>
            <tbody id="aws-tables-columns">
            <!-- FILLED DYNAMICALLY -->
            </tbody>
          </table>
        </div>
      </div>
      <div class="row mt-2">
        <div class="col text-center">
          <button class="btn btn-primary prev-button" data-prev="3">{{ __('Previous') }}</button>
          <button id="import-tables" class="btn btn-primary next-button" data-next="6">{{ __('Next') }}</button>
        </div>
      </div>
    </div>
  </div>
  <div class="card step-content">
    <div class="card-body">
      <h5 class="card-title">
        {{ __('5. Input the SQL query to generate a new virtual table.') }}
      </h5>
      <div class="row mt-2">
        <div class="col">
          <div id="aws-vtable-name"></div>
        </div>
      </div>
      <div class="row mt-2">
        <div class="col">
          <textarea id="aws-vtable-description"
                    class="form-control mt-2"
                    rows="4"
                    placeholder="{{ __('Please provide a detailed description of the table and explain the significance of the key columns. The more information you include, the better.') }}"></textarea>
        </div>
      </div>
      <div class="row mt-2">
        <div class="col">
          <input type="checkbox" id="toggle-materialize"/>
          <label for="toggle-materialize">{{ __('Materialize') }}</label>
        </div>
      </div>
      <div class="row mt-2">
        <div class="col">
          <x-sql-editor/>
        </div>
      </div>
      <div class="row mt-2">
        <div class="col text-center">
          <button class="btn btn-primary prev-button" data-prev="1">{{ __('Previous') }}</button>
          <button id="create-vtable" class="btn btn-primary next-button" data-next="6">{{ __('Next') }}</button>
        </div>
      </div>
    </div>
  </div>
  <div class="card step-content">
    <div class="card-body">
      <h5 class="card-title">
        {{ __('6. Your data will be accessible shortly!') }}
      </h5>
      <div class="row mt-2">
        <div class="col text-center">
          <!-- TODO : ADD IMAGE HERE -->
        </div>
      </div>
      <div class="row mt-2">
        <div class="col text-center">
          <button class="btn btn-primary prev-button" data-prev="1">{{ __('New import') }}</button>
        </div>
      </div>
    </div>
  </div>
</div>
<script>

  const steps = document.querySelectorAll('.step');
  const stepContents = document.querySelectorAll('.step-content');
  const nextButtons = document.querySelectorAll('.next-button');
  const prevButtons = document.querySelectorAll('.prev-button');
  const toggleColumnsSelection = document.getElementById('toggle-columns-selection');

  toggleColumnsSelection.onchange = () => {
    const checkboxes = document.querySelectorAll('#aws-tables-columns input[type="checkbox"]');
    checkboxes.forEach(checkbox => checkbox.checked = !checkbox.checked);
  };

  nextButtons.forEach(button => {
    button.addEventListener('click', (event) => {
      const currentStep = parseInt(button.getAttribute('data-next')) - 1;
      let moveToNextStep = true;
      if (event.target && event.target.id === 'get-columns') {
        event.preventDefault();
        event.stopPropagation();
        moveToNextStep = getAwsTablesColumns();
      } else if (event.target && event.target.id === 'import-tables') {
        event.preventDefault();
        event.stopPropagation();
        moveToNextStep = importAwsTables();
      } else if (event.target && event.target.id === 'create-vtable') {
        event.preventDefault();
        event.stopPropagation();
        moveToNextStep = createAwsVirtualTables();
      }
      if (moveToNextStep) {
        goToStep(currentStep);
      }
    });
  });

  prevButtons.forEach(button => {
    button.addEventListener('click', () => {
      const prevStep = parseInt(button.getAttribute('data-prev')) - 1;
      goToStep(prevStep);
    });
  });

  const goToStep = (stepIndex) => {
    if (stepIndex === 1 /* 0-based */ && elTableType.el.selectedItem === VIRTUAL_TABLE.value) {
      stepIndex = 4; // 0-based, when next is clicked bypass steps 2, 3 and 4
    }
    steps.forEach((step, index) => step.classList.toggle('active', index === stepIndex));
    stepContents.forEach((content, index) => content.classList.toggle('active', index === stepIndex));
    if (stepIndex === 2 /* 0-based */) {
      listAwsTables();
    }
  };

  const PHYSICAL_TABLE = {
    label: "{{ __('Physical') }}", value: 'physical'
  };
  const VIRTUAL_TABLE = {
    label: "{{ __('Virtual') }}", value: 'virtual'
  };

  const elTableType = com.computablefacts.blueprintjs.Blueprintjs.component(document, {
    type: 'RadioGroup',
    container: 'table-kinds-container',
    inline: false,
    items: [PHYSICAL_TABLE, VIRTUAL_TABLE],
    selected_item: PHYSICAL_TABLE.value,
  });

  const elAwsRegion = com.computablefacts.blueprintjs.Blueprintjs.component(document, {
    type: 'TextInput', container: 'aws-region', placeholder: 'ex. eu-west-3'
  });

  const elAwsAccessKeyId = com.computablefacts.blueprintjs.Blueprintjs.component(document, {
    type: 'TextInput', container: 'aws-access-key-id', placeholder: 'ex. AKIAIOSFODNN7EXAMPLE',
  });

  const elAwsSecretAccessKey = com.computablefacts.blueprintjs.Blueprintjs.component(document, {
    type: 'TextInput', container: 'aws-secret-access-key', placeholder: 'ex. wJalrXUtnFEMI/K7MDENG/bPxRfiCYzEXAMPLEKEY',
  });

  const elAwsInputFolder = com.computablefacts.blueprintjs.Blueprintjs.component(document, {
    type: 'TextInput', container: 'aws-input-folder', placeholder: 'ex. in/',
  });

  const elAwsOutputFolder = com.computablefacts.blueprintjs.Blueprintjs.component(document, {
    type: 'TextInput', container: 'aws-output-folder', placeholder: 'ex. out/',
  });

  const elAwsVirtualTableName = com.computablefacts.blueprintjs.Blueprintjs.component(document, {
    type: 'TextInput',
    container: 'aws-vtable-name',
    placeholder: "{{ __('The virtual table name such as active_users') }}"
  });

  const listAwsTables = () => {

    const elAwsTables = document.getElementById('aws-tables');
    elAwsTables.innerHTML = "<tr><td colspan=\"4\" class=\"text-center\">{{ __('Loading...') }}</td></tr>";

    if (elTableType.el.selectedItem === PHYSICAL_TABLE.value) {
      axios.get(
        `/cb/web/aws/tables/?region=${elAwsRegion.el.value}&access_key_id=${elAwsAccessKeyId.el.value}&secret_access_key=${elAwsSecretAccessKey.el.value}&input_folder=${elAwsInputFolder.el.value}&output_folder=${elAwsOutputFolder.el.value}`).then(
        response => {
          if (response.data.success) {
            if (!response.data.tables || response.data.tables.length === 0) {
              elAwsTables.innerHTML = "<tr><td colspan=\"4\" class=\"text-center\">{{ __('No files found.') }}</td></tr>";
            } else {
              const rows = response.data.tables.map(table => {
                return `
                <tr>
                  <td><input type="checkbox" value="${table.object}" data-file="${table.object}"/></td>
                  <td>${table.object}</td>
                  <td class="text-end">${table.size}</td>
                  <td class="text-end">${table.last_modified}</td>
                </tr>
              `;
              });
              elAwsTables.innerHTML = rows.join('');
            }
          } else if (response.data.error) {
            toaster.toastError(response.data.error);
          } else {
            console.log(response.data);
          }
        })
      .catch(error => toaster.toastAxiosError(error));
    } else if (elTableType.el.selectedItem === VIRTUAL_TABLE.value) {
      // TODO
    } else {
      // TODO
    }
  };

  const getAwsTablesColumns = () => {

    const checkboxes = Array.from(document.querySelectorAll('#aws-tables input[type="checkbox"]:checked'));
    const tables = checkboxes.map(checkbox => checkbox.getAttribute('data-file'));

    if (tables.length !== 1) {
      toaster.toastError("{{ __('Please select the table to import.') }}");
      return false;
    }

    const elAwsTablesColumns = document.getElementById('aws-tables-columns');
    elAwsTablesColumns.innerHTML = "<tr><td colspan=\"5\" class=\"text-center\">{{ __('Loading...') }}</td></tr>";

    axios.post('/cb/web/aws/tables/columns', {
      region: elAwsRegion.el.value,
      access_key_id: elAwsAccessKeyId.el.value,
      secret_access_key: elAwsSecretAccessKey.el.value,
      input_folder: elAwsInputFolder.el.value,
      output_folder: elAwsOutputFolder.el.value,
      tables: tables,
    }).then(response => {
      if (response.data.success) {
        if (!response.data.tables || response.data.tables.length === 0) {
          elAwsTablesColumns.innerHTML = "<tr><td colspan=\"5\" class=\"text-center\">{{ __('No columns found.') }}</td></tr>";
        } else {
          const rows = response.data.tables.flatMap(table => {
            return table.columns.map(column => {
              column.table = table.table;
              return `
                <tr>
                  <td><input type="checkbox" data-file="${com.computablefacts.helpers.toBase64(JSON.stringify(column))}" checked/></td>
                  <td>${table.table}</td>
                  <td>${column.old_name}</td>
                  <td>${column.new_name}</td>
                  <td>${column.type}</td>
                </tr>
              `;
            });
          });
          elAwsTablesColumns.innerHTML = rows.join('');
        }
      } else if (response.data.error) {
        toaster.toastError(response.data.error);
      } else {
        console.log(response.data);
      }
    })
    .catch(error => toaster.toastAxiosError(error));

    return true;
  };

  const importAwsTables = () => {

    const description = document.getElementById('aws-table-description').value;
    const checkboxes = Array.from(document.querySelectorAll('#aws-tables-columns input[type="checkbox"]:checked'));
    const tables = checkboxes.map(
      checkbox => JSON.parse(com.computablefacts.helpers.fromBase64(checkbox.getAttribute('data-file'))));

    if (tables.length === 0) {
      toaster.toastError("{{ __('Please select the table to import.') }}");
      return false;
    }
    if (description.trim() === '') {
      toaster.toastError("{{ __('Please enter a table description.') }}");
      return false;
    }

    const updatable = document.getElementById('toggle-updatable').checked === true;
    const copy = document.getElementById('toggle-copy').checked === true;
    const deduplicate = document.getElementById('toggle-deduplicate').checked === true;

    axios.post('/cb/web/aws/tables/import', {
      region: elAwsRegion.el.value,
      access_key_id: elAwsAccessKeyId.el.value,
      secret_access_key: elAwsSecretAccessKey.el.value,
      input_folder: elAwsInputFolder.el.value,
      output_folder: elAwsOutputFolder.el.value,
      tables: tables,
      updatable: updatable,
      copy: copy,
      deduplicate: deduplicate,
      description: description,
    }).then(response => {
      if (response.data.success) {
        toaster.toastSuccess(response.data.success);
      } else if (response.data.error) {
        toaster.toastError(response.data.error);
      } else {
        console.log(response.data);
      }
    }).catch(error => toaster.toastAxiosError(error));

    return true;
  };

  const createAwsVirtualTables = () => {

    const description = document.getElementById('aws-vtable-description').value;
    const name = elAwsVirtualTableName.el.value;
    const sql = editor.getValue(); // from x-sql-editor
    const materialize = document.getElementById('toggle-materialize').checked === true;

    if (name.trim() === '') {
      toaster.toastError("{{ __('Please enter a table name.') }}");
      return false;
    }
    if (description.trim() === '') {
      toaster.toastError("{{ __('Please enter a table description.') }}");
      return false;
    }
    if (sql.trim() === '') {
      toaster.toastError("{{ __('Please enter a SQL query.') }}");
      return false;
    }

    axios.post(`/cb/web/aws/tables/query`,
      {query: sql, store: true, name: name, materialize: materialize, description: description}).then(response => {
      if (response.data.success) {
        toaster.toastSuccess(response.data.success);
      } else if (response.data.error) {
        toaster.toastError(response.data.error);
      } else {
        console.log(response.data);
      }
    })
    .catch(error => toaster.toastAxiosError(error));

    return true;
  };

</script>