<!doctype html>
<html>
<head>
  <title>CyberBuddy (Powered by AdversaryMeter)</title>
  <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
  <meta name="keywords"
        content="honeypot, vulnerability scanner, assets discovery, attack surface management, shadow it">
  <meta name="description"
        content="AdversaryMeter is a hybrid between a Honeypot and a Vulnerability Scanner that helps you get a better understanding of your organization's security posture and what should be done to take it to the next level. No installation required.">
</head>
<body>
<!-- TODO -->
</body>
<script>

  const botmanInterval = setInterval(checkBotman, 1000);

  function checkBotman() {

    if (window.botmanChatWidget) {
      console.log(window.botmanChatWidget);
      window.botmanChatWidget.open();
    }

    const elChatBotManFrame = document.getElementById('chatBotManFrame');

    if (elChatBotManFrame) {

      const elChatWidget = elChatBotManFrame.contentWindow.document.getElementById('botmanChatRoot');
      const elMessageArea = elChatBotManFrame.contentWindow.document.getElementById('messageArea');
      const elTextInput = elChatBotManFrame.contentWindow.document.getElementById('userText');

      if (!elChatWidget || !elMessageArea || !elTextInput) {
        return;
      }

      clearInterval(botmanInterval);

      // Observe incoming messages and react accordingly
      const observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
          mutation.addedNodes.forEach(addedNode => {
            if (addedNode.nodeType === Node.ELEMENT_NODE) {
              if (addedNode.dataset.json) { // Manipulate the bot reply if needed

                const data = JSON.parse(atob(addedNode.dataset.json));

                addedNode.addEventListener('mouseover', event => {
                  event.preventDefault();
                  event.stopPropagation();
                  console.log(data);
                });
              }
            }
          });
        });
      });

      const elChatArea = elMessageArea.getElementsByClassName('chat')[0];

      observer.observe(elChatArea, {subtree: true, childList: true});
    }
  }

  window.botmanWidget = {
    title: 'CyberBuddy',
    aboutText: '⚡ Powered by Towerify',
    aboutLink: 'https://towerify.io',
    userId: '{{ Auth::user() ? Auth::user()->id : \Illuminate\Support\Str::random(10) }}',
    chatServer: '/cb/web/botman',
    frameEndpoint: '/cb/web/cyber-buddy/chat',
    introMessage: 'Que puis-je faire pour vous?',
    desktopHeight: 900,
    desktopWidth: 740,
    mainColor: '#00264b',
    bubbleBackground: '#00264b',
    headerTextColor: 'white',
  };
</script>
<script src='/cyber_buddy/botman/widget.js'></script>
</html>
