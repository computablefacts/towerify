@if(Auth::check() && Auth::user()->canUseCyberBuddy())
<div class="card mb-3">
  <div class="card-body">
    <div id="templates"></div>
  </div>
</div>
<div class="container mb-3">
  <div class="row">
    <div class="col text-end">
      <a href="#" onclick="clearDocument()">
        {{ __('clear') }}
      </a>&nbsp;&nbsp;&nbsp;
      <a href="#" onclick="importDocument()">
        {{ __('import') }}
      </a>&nbsp;&nbsp;&nbsp;
      <a href="#" onclick="exportDocument()">
        {{ __('export') }}
      </a>&nbsp;&nbsp;&nbsp;
      <a href="#" onclick="deleteDocument()">
        {{ __('delete') }}
      </a>&nbsp;&nbsp;&nbsp;
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

    const template = window.BlockNote ? window.BlockNote.template : null;
    const ctx = window.BlockNote ? window.BlockNote.ctx : null;

    if (!template || !ctx) {
      toaster.toastError("{{ __('The document is not loaded!') }}");
      return;
    }
    axios.post('/cb/web/templates', {id: template.id, name: template.name, template: ctx.blocks})
    .then(response => {
      if (!template.id) {
        template.id = response.data.id;
        template.type = response.data.type;
        elTemplates.items = elTemplates.items.concat(response.data);
        elTemplates.selectedItem = response.data;
      }
      toaster.toastSuccess("{{ __('The document has been saved!') }}");
    })
    .catch(error => toaster.toastAxiosError(error));
  };

  const exportDocument = () => {

    const ctx = window.BlockNote ? window.BlockNote.ctx : null;

    if (!ctx) {
      toaster.toastError("{{ __('The document is not loaded!') }}");
      return;
    }

    const editor = ctx.editor;
    const blocks = ctx.blocks;
    const markdownContent = editor.blocksToMarkdownLossy(blocks);

    markdownContent.then(md => {
      const blob = new Blob([md], {type: 'text/markdown'});
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = 'draft.md';
      link.click();
      window.URL.revokeObjectURL(url);
    });
  };

  const importDocument = () => {

    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.md';
    input.onchange = event => {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = e => {
          const markdownContent = e.target.result;
          const editor = window.BlockNote.ctx.editor;
          const blocksFromMarkdown = editor.tryParseMarkdownToBlocks(markdownContent);
          blocksFromMarkdown.then(blocks => {
            for (let i = 0; i < blocks.length; i++) {
              const block = blocks[i];
              if (block.type === 'paragraph') {
                if (block.content.length === 1) {
                  for (let j = 0; j < block.content.length; j++) {
                    if (block.content[j].type === 'text') {
                      const text = block.content[j].text.trim();
                      if (text.startsWith('Q:')) {
                        blocks[i] = {
                          id: block.id, type: "ai_block", props: {
                            prompt: "CyberBuddy",
                            collection: "pssi",
                            collections: ["pssi"],
                            instructions: text.substring(2)
                          }, content: []
                        };
                      }
                    }
                  }
                }
              }
            }
            const template = {
              name: file.name.slice(0, -3), template: blocks, type: 'draft', user: '{{ Auth::user()->name }}',
            };
            window.BlockNote.template = template;
            window.BlockNote.observers.notify('template-change', template);
          });
        };
        reader.readAsText(file);
      }
    };
    input.click();
  };

  const clearDocument = () => {
    window.BlockNote.template = null;
    window.BlockNote.observers.notify('template-change', null);
    elTemplates.selectedItem = null;
  };

  const deleteDocument = () => {
    if (!elTemplates.selectedItem || !elTemplates.selectedItem.id || elTemplates.selectedItem.type === 'template') {
      clearDocument();
      return;
    }
    axios.delete(`/cb/web/templates/${elTemplates.selectedItem.id}`).then(response => {
      elTemplates.items = elTemplates.items.filter(item => item.id !== elTemplates.selectedItem.id);
      clearDocument();
      toaster.toastSuccess("{{ __('The document has been deleted!') }}");
    })
    .catch(error => toaster.toastAxiosError(error));
  };

</script>
@endif