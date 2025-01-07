@if(Auth::check() && Auth::user()->canUseCyberBuddy())
<iframe id="chatBotManFrame" src="" style="width:100%"></iframe>
<script>

  window.botmanWidget = {
    title: 'CyberBuddy',
    aboutText: null, // "⚡ Powered by {{ config('app.name') }}",
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

  const iframe = document.getElementById('chatBotManFrame');
  const url = `/cb/web/cyber-buddy/chat?conf=${encodeURIComponent(JSON.stringify(window.botmanWidget))}`;

  iframe.onload = () => {
    const iframeDocument = iframe.contentDocument || iframe.contentWindow.document;
    if (iframeDocument) {
      const input = iframeDocument.getElementById('userText');
      input.placeholder = "Saisissez ici votre question...";
    }
  };
  iframe.placeholder = "test";
  iframe.style.height = 'calc(100vh - 58px)'; // 56px = --bs-navbar-height
  iframe.src = url;

</script>
@endif