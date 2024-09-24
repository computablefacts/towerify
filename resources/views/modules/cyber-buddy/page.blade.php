<!doctype html>
<html lang="en">
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
  window.botmanWidget = {
    title: 'CyberBuddy',
    aboutText: '⚡ Powered by Towerify',
    aboutLink: '{{ app_url() }}',
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
