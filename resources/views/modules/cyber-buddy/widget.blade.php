@if(Auth::check() && Auth::user()->canUseCyberBuddy())
<div class="container mb-3">
  <div class="row">
    <div class="col text-end">
      <a href="#" onclick="startConversation()">
        {{ __('+ start conversation') }}
      </a>
    </div>
  </div>
</div>
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
  window.botmanWidget = {
    title: 'CyberBuddy',
    aboutText: "⚡ Powered by {{ config('app.name') }}",
    aboutLink: '{{ app_url() }}',
    userId: '{{ Auth::user() ? Auth::user()->id : \Illuminate\Support\Str::random(10) }}',
    chatServer: '/cb/web/botman',
    bubbleAvatarUrl: '/images/icons/cyber-buddy.svg',
    frameEndpoint: '/cb/web/cyber-buddy/chat',
    introMessage: 'Bonjour! Je suis votre cyber assistant. Que puis-je faire pour vous?',
    desktopHeight: 900,
    desktopWidth: 2 * window.innerWidth / 3,
    mainColor: '#47627F',
    bubbleBackground: '#00264b',
    headerTextColor: 'white',
  };
</script>
<script src='/cyber_buddy/botman/widget.js'></script>
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

  const startConversation = () => {

    const botmanInterval = setInterval(checkBotman, 300);

    function checkBotman() {
      if (window.botmanChatWidget) {
        clearInterval(botmanInterval);
        window.botmanChatWidget.open();
      }
    }
  };

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