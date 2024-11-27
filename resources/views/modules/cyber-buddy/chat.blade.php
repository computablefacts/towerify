<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <title>CyberBuddy</title>
  <meta charset="UTF-8">
  <link rel="stylesheet" type="text/css" href="/cyber_buddy/botman/chat_1.css">
  <link rel="stylesheet" type="text/css" href="/cyber_buddy/botman/chat_2.css">
</head>
<body>
<script id="botmanWidget" src='/cyber_buddy/botman/chat.js'></script>
</body>
<script>

  const botmanInterval = setInterval(checkBotman, 1000);

  function checkBotman() {

    const elBotmanChatRoot = document.getElementById('botmanChatRoot');
    const elMessageArea = document.getElementById('messageArea');
    const elTextInput = document.getElementById('userText');

    if (!elBotmanChatRoot || !elMessageArea || !elTextInput) {
      return;
    }

    clearInterval(botmanInterval);

    // Observe incoming messages and react accordingly
    const observer = new MutationObserver(mutations => {
      mutations.forEach(mutation => {
        mutation.addedNodes.forEach(addedNode => {
          if (addedNode.nodeType === Node.ELEMENT_NODE) {

            const elRow = addedNode.closest('li.chatbot');
            const elMessage = addedNode.closest('div.msg');

            if (elRow && elMessage && addedNode.dataset.type) {
              elRow.style.overflow = 'visible';
              elMessage.style.width = '100%';
              elMessage.style.maxWidth = '100%';
              elMessage.style.background = 'unset';
            }
          }
        });
      });
    });

    const elChatArea = elMessageArea.getElementsByClassName('chat')[0];

    observer.observe(elChatArea, {subtree: true, childList: true});
  }

</script>
</html>