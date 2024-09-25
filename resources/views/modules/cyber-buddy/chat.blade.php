<!doctype html>
<html>
<head>
  <title>Botman Widget</title>
  <meta charset="UTF-8">
  <link rel="stylesheet" type="text/css" href="/cyber_buddy/botman/chat.css">
  <style>

    /* OVERRIDES CHAT.CSS */
    .chat li {
      overflow: visible;
    }

    /* TABLE */

    table {
      border-collapse: collapse;
      width: 100%;
    }

    th, td {
      border: 1px solid #dddddd;
      text-align: left;
      padding: 8px;
    }

    th {
      background-color: #00264b;
      color: white;
    }

    td {
      background-color: white;
      color: #00264b;
    }

    /* TOOLTIP */

    .tooltip {
      position: relative;
      display: inline-block;
      border-bottom: 1px dotted #f8b500; /* If you want dots under the hoverable text */
      cursor: pointer;
    }

    .tooltip .tooltiptext {
      visibility: hidden;
      width: 250px;
      background-color: #f8b500;
      color: white;
      text-align: left;
      padding: 5px 5px;

      /* Position the tooltip text */
      position: absolute;
      z-index: 1;

      /* Fade in tooltip */
      opacity: 0;
      transition: opacity 0.3s;
    }

    .tooltip:hover .tooltiptext {
      visibility: visible;
      opacity: 1;
    }

    .tooltip-top {
      bottom: 125%;
      left: 50%;
      margin-left: -60px;
    }

    .tooltip-top::after {
      /* content: ""; */
      position: absolute;
      top: 100%;
      left: 50%;
      margin-left: -5px;
      border-width: 5px;
      border-style: solid;
      border-color: #f8b500 transparent transparent transparent;
    }

    .tooltip-bottom {
      top: 135%;
      left: 50%;
      margin-left: -60px;
    }

    .tooltip-bottom::after {
      /* content: ""; */
      position: absolute;
      bottom: 100%;
      left: 50%;
      margin-left: -5px;
      border-width: 5px;
      border-style: solid;
      border-color: transparent transparent #f8b500 transparent;
    }

    .tooltip-left {
      top: -5px;
      bottom: auto;
      right: 128%;
    }

    .tooltip-left::after {
      /* content: ""; */
      position: absolute;
      top: 50%;
      left: 100%;
      margin-top: -5px;
      border-width: 5px;
      border-style: solid;
      border-color: transparent transparent transparent #f8b500;
    }

    .tooltip-right {
      top: -5px;
      left: 125%;
    }

    .tooltip-right::after {
      /* content: ""; */
      position: absolute;
      top: 50%;
      right: 100%;
      margin-top: -5px;
      border-width: 5px;
      border-style: solid;
      border-color: transparent #f8b500 transparent transparent;
    }

  </style>
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
              elRow.style.overflow='visible';
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