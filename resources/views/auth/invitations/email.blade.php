<html lang="fr">
<head>
  <style>
    * {
      font-size: 16px;
    }

    html {
      font-family: monospace;
      max-width: 900px; /* For Desktop PC (see @media  for Tablets/Phones) */
      padding-left: 2%;
      padding-right: 3%;
      margin: 0 auto;
      background: #F5F5F0;
    }

    body {
      background-color: unset !important;
    }

    p {
      margin-top: 0px;
      text-align: justify;
    }

    pre {
      font-family: fabfont, monospace;
      background-color: white;
      border: 1px solid Black;
      padding-left: 2%;
      padding-top: 1ch;
      padding-bottom: 1ch;
      /* Only take care of X overflow since this is the only part that can be too wide.
         The Y axis will never overflow.
      */
      overflow: hidden;
    }

    div.heading {
      font-weight: bold;
      text-transform: uppercase;
      margin-top: 2ch;
    }

    div.section {
      font-weight: bold;
      text-transform: uppercase;
      margin-top: 2ch;
      margin-bottom: 2ch;
    }

    @media (max-width: 500px) {
      /* For small screen decices */
      * {
        font-size: 12px;
      }
    }

    table {
      width: 100%;
      text-align: left;
      border-collapse: collapse;
      table-layout: fixed;
    }

    table thead {
      background-color: #00264b;
      color: #F5F5F0;
    }

    table thead tr {
      border: 3px solid #00264b;
    }

    table thead tr th {
      padding: 5px;
      border: 1px solid #F5F5F0;
    }

    table tbody {
      border-bottom: 3px solid #00264b;
    }

    table tbody tr {
      border-left: 3px solid #00264b;
      border-right: 3px solid #00264b;
    }

    table tbody tr.end-of-block {
      border-bottom: 3px solid #00264b;
    }

    table tbody tr td {
      padding: 5px;
      border: 1px solid #00264b;
    }

    .ellipsis {
      overflow: hidden;
      white-space: nowrap;
      text-overflow: ellipsis;
    }

    .grey {
      color: #47627f !important;
    }

    .badge {
      display: inline-block;
      font-size: 60%;
      font-weight: 700;
      line-height: 1;
      text-align: center;
      white-space: nowrap;
      vertical-align: baseline;
      padding: 0.6em .6em;
      border-radius: 0.25rem;
      color: #F5F5F0;
      background-color: #47627F;
    }
  </style>
  <title>Invitation</title>
  <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=12.0, minimum-scale=1.0, user-scalable=yes">
</head>
<body>
<br>
<center>
  <div class="grey" style="display:inline-block;vertical-align:middle;">
    <b>INVITATION</b>
  </div>
</center>
<br/>
<div class="grey">
  <p>Bonjour,</p>
  <p>
    Suite à la communication reçue ce jour, vous trouverez <a
      href="{{ route('appshell.public.invitation.show', $invitation->hash) }}">ici</a> le lien d'activation de votre
    compte Cywise pour accéder à <b>CyberBuddy</b>.
  </p>
  <p>Bonne journée!</p>
</div>
<center class="grey"><b style="color:#f8b502;font-weight:bolder">*</b></center>
</body>
</html>
