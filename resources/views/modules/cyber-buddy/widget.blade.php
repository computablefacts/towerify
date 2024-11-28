@if(Auth::check() && Auth::user()->canUseCyberBuddy())
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

  const startConversation = () => {

    const botmanInterval = setInterval(checkBotman, 300);

    function checkBotman() {
      if (window.botmanChatWidget) {
        clearInterval(botmanInterval);
        window.botmanChatWidget.open();
      }
    }
  };

  startConversation();

</script>
@endif