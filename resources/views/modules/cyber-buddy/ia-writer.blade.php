@if(Auth::check() && Auth::user()->canUseCyberBuddy())
<div class="card mb-3">
  <div class="card-body">
    <div id="templates"></div>
  </div>
</div>
<div class="container mb-3">
  <div class="row">
    <div class="col text-end">
      <a href="#" onclick="saveDocument()">
        {{ __('save') }}
      </a>
    </div>
  </div>
</div>
<div class="card mb-3">
  <div class="card-body p-2">
    <x-block-note/>
  </div>
</div>
<script>

  const elTemplates = new com.computablefacts.blueprintjs.MinimalSelect(document.getElementById('templates'),
    item => item.name, item => item.type === 'template' ? item.type : `${item.type} (${item.user})`, null,
    query => query);
  elTemplates.onSelectionChange(template => {
    if (window.BlockNote.observers) {
      window.BlockNote.template = template;
      window.BlockNote.observers.notify('template-change', template);
    }
  });
  elTemplates.defaultText = "{{ __('Load template...') }}";

  document.addEventListener('DOMContentLoaded', function (event) {
    axios.get('/cb/web/templates').then(response => {
      elTemplates.items = response.data;
    }).catch(error => toaster.toastAxiosError(error));
  });
  
  const saveDocument = () => {
    axios.post('/cb/web/templates', {
      id: window.BlockNote && window.BlockNote.template ? window.BlockNote.template.id : null,
      name: window.BlockNote && window.BlockNote.template ? window.BlockNote.template.name : null,
      template: window.BlockNote && window.BlockNote.ctx ? window.BlockNote.ctx.blocks : null,
    })
    .then(response => toaster.toastSuccess("{{ __('The document has been saved!') }}"))
    .catch(error => toaster.toastAxiosError(error));
  };

</script>
@endif