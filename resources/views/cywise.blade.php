<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name') }}</title>

  <!-- favicons -->
  @include('layouts._favicons')

  <!-- FastBootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/fastbootstrap@2.2.0/dist/css/fastbootstrap.min.css" rel="stylesheet"
        integrity="sha256-V6lu+OdYNKTKTsVFBuQsyIlDiRWiOmtC8VQ8Lzdm2i4=" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
          integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
          crossorigin="anonymous"></script>

  <style>

    .circle-container {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .circle {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background-color: white;
      color: #FFA500;
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 0 10px;
      font-weight: bold;
      font-size: 25px;
      position: relative;
      border: 3px solid #FFA500;
    }

    .circle.selected {
      background-color: #FFA500;
      color: white;
    }

    .line-container {
      width: 10%;
    }

    .line {
      width: 100%;
      height: 3px;
      background-color: #FFA500;
    }

    body {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    h1 {
      font-size: 24px;
      margin-top: 20px;
    }

    p {
      font-size: 14px;
      color: #555;
    }

    input[type="text"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
    }

    button {
      width: 100%;
      padding: 10px;
      background-color: #FFA500;
      color: white;
      border: none;
      cursor: pointer;
      font-size: 16px;
    }

    button:hover {
      background-color: #e08e00;
    }

    .domain-list {
      margin-bottom: 20px;
    }

    .domain-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px;
      border: 1px solid #ccc;
      margin-bottom: 10px;
    }

    .domain-item2 {
      display: flex;
      align-items: center;
      padding: 10px;
      border: 1px solid #ccc;
      margin-bottom: 10px;
      justify-content: start;
    }

    .domain-item input {
      margin-right: 10px;
      accent-color: #FFA500;
    }

    .domain-item label {
      flex-grow: 1;
    }

    .certificate-note {
      margin-bottom: 20px;
      text-align: left;
      accent-color: #FFA500;
      display: flex;
      align-items: center;
    }

    .certificate-note label {
      margin-left: 10px;
    }

    input[type=checkbox] {
      appearance: none;
      width: 16px;
      height: 16px;
      border: 2px solid #FFA500;
      border-radius: 3px;
      position: relative;
      cursor: pointer;
    }

    input[type=checkbox]:checked {
      background-color: #FFA500; /* couleur de fond */
    }

    input[type=checkbox]:checked::after {
      content: '✓'; /* ou '✔' */
      position: absolute;
      color: white;
      font-size: 16px;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
    }

    .button-group {
      display: flex;
      justify-content: space-between;
    }

    .back-button, .next-button {
      padding: 10px 20px;
      border: none;
      cursor: pointer;
    }

    .back-button {
      background-color: white;
      color: #FFA500;
      border: 2px solid #FFA500;
    }

    .back-button:hover {
      background-color: #e08e00;
      color: white;
      border: 2px solid #e08e00;
    }

    .next-button {
      background-color: #FFA500;
      color: white;
      width: 300%;
      margin-left: 20px;
    }

    .next-button:hover {
      background-color: #e08e00;
    }

    .next-button2 {
      background-color: #FFA500;
      color: white;
      width: 100%;
      margin-bottom: 20px;
    }

    .next-button2:hover {
      background-color: #e08e00;
    }

    .header {
      display: flex;
      align-items: center;
      margin-bottom: 20px;
    }

    .logo svg {
      margin-right: 15px;
    }

    .info h1 {
      font-size: 20px;
      margin: 0;
    }

    .info p {
      font-size: 14px;
      color: #555;
      margin: 5px 0 0 0;
    }

    .loader {
      width: 48px;
      height: 48px;
      border: 5px solid #FFA500;
      border-bottom-color: transparent;
      border-radius: 50%;
      display: inline-block;
      box-sizing: border-box;
      animation: rotation 1s linear infinite;
      margin-right: 20px;
    }

    @keyframes rotation {
      0% {
        transform: rotate(0deg);
      }
      100% {
        transform: rotate(360deg);
      }
    }

    .badge-container.high {
      display: inline-table;
      vertical-align: middle;
      width: 25px;
      height: 25px;
      border-radius: 50%;
      background-color: #ff4d4d;
      color: white;
    }

    .badge-container.medium {
      display: inline-table;
      vertical-align: middle;
      width: 25px;
      height: 25px;
      border-radius: 50%;
      background-color: #ffaa00;
      color: white;
    }

    .badge-container.low {
      display: inline-table;
      vertical-align: middle;
      width: 25px;
      height: 25px;
      border-radius: 50%;
      background-color: #4bd28f;
      color: white;
    }

    .badge-content {
      display: table-cell;
      vertical-align: middle;
      text-align: center;
    }

    .right {
      display: flex;
      align-items: center;
      justify-content: end;
    }

    .circular-progress {
      --size: 250px;
      --half-size: calc(var(--size) / 2);
      --stroke-width: 20px;
      --radius: calc((var(--size) - var(--stroke-width)) / 2);
      --circumference: calc(var(--radius) * pi * 2);
      --dash: calc((var(--progress) * var(--circumference)) / 100);
      animation: progress-animation 5s linear 0s 1 forwards;
      margin-left: 15px;
    }

    .circular-progress circle {
      cx: var(--half-size);
      cy: var(--half-size);
      r: var(--radius);
      stroke-width: var(--stroke-width);
      fill: none;
      stroke-linecap: round;
    }

    .circular-progress circle.bg {
      stroke: #ddd;
    }

    .circular-progress circle.fg {
      transform: rotate(-90deg);
      transform-origin: var(--half-size) var(--half-size);
      stroke-dasharray: var(--dash) calc(var(--circumference) - var(--dash));
      transition: stroke-dasharray 0.3s linear 0s;
      stroke: black;
    }

  </style>
</head>
<body>
<div style="width:550px;">
  <div class="circle-container">
    <div class="circle @if($step >= 1) selected @endif" style="margin-left:0">1</div>
    <div class="line-container">
      <div class="line"></div>
    </div>
    <div class="circle @if($step >= 2) selected @endif">2</div>
    <div class="line-container">
      <div class="line"></div>
    </div>
    <div class="circle @if($step >= 3) selected @endif">3</div>
    <div class="line-container">
      <div class="line"></div>
    </div>
    <div class="circle @if($step >= 4) selected @endif">4</div>
    <div class="line-container">
      <div class="line"></div>
    </div>
    <div class="circle @if($step >= 5) selected @endif" style="margin-right:0">5</div>
  </div>

  @if($step == 1)
  <div class="content">
    <h1>Votre nom de domaine</h1>
    <p>Rentrez votre nom de domaine et on se charge de trouver les vulnérabilités pour vous</p>
    <input type="text" placeholder="Nom de domaine">
    <button>Suivant →</button>
  </div>
  @elseif($step == 2)
  <div class="content">
    <h1>Vos sous-domaines</h1>
    <p>Liste des sous-domaines de <b>cywise.io</b> compatibles avec le scanner</p>
    <div class="domain-list">
      <div class="domain-item">
        <input type="checkbox" checked>
        <label for="domain1">cywise.io</label>
      </div>
      <div class="domain-item">
        <input type="checkbox" checked>
        <label for="domain2">dev.cywise.io</label>
      </div>
      <div class="domain-item">
        <input type="checkbox" checked>
        <label for="domain3">auth.cywise.io</label>
      </div>
      <div class="domain-item">
        <input type="checkbox" checked>
        <label for="domain4">admin.cywise.io</label>
      </div>
    </div>
    <div class="certificate-note">
      <input type="checkbox" checked>
      <label for="certificate">Je certifie être propriétaire de ces domaines</label>
    </div>
    <div class="button-group">
      <button class="back-button">Retour</button>
      <button class="next-button">Suivant</button>
    </div>
  </div>
  @elseif($step == 3)
  <div class="content">
    <h1>Votre honeypot</h1>
    <p>Configuration automatique du honeypot <b>cywise.cywise.io</b></p>
    <div class="header">
      <div class="logo">
        <span class="loader"></span>
      </div>
      <div class="info">
        <h1>cywise.cywise.io</h1>
        <p>Création du serveur</p>
      </div>
    </div>
    <div class="button-group">
      <button class="back-button">Retour</button>
      <button class="next-button">Suivant</button>
    </div>
  </div>
  @elseif($step == 4)
  <div class="content">
    <h1>Votre adresse email</h1>
    <p>Rentrez votre adresse email pour recevoir les résultats</p>
    <input type="text" placeholder="Adresse email">
    <button class="next-button2">Lancer le scan</button>
    <button class="back-button">Retour</button>
  </div>
  @elseif($step == 5)
  <div class="content">
    <h1>Vos résultats</h1>
    <p>Retrouvez ici toutes les vulnérabilités trouvées</p>
    <div class="domain-list">
      <div class="domain-item2">
        <div class="" style="width:50%">
          <img src="https://www.svgrepo.com/show/12134/info-circle.svg" width="20" height="20" alt="">
          <span style="padding-left:10px">cywise.io</span>
        </div>
        <div class="right" style="width:50%">
          <div class="badge-container high" style="">
            <div class="badge-content">2</div>
          </div>
          <div class="badge-container medium" style="margin-left: 10px">
            <div class="badge-content">1</div>
          </div>
          <div class="badge-container low" style="margin-left: 10px">
            <div class="badge-content">3</div>
          </div>
        </div>
      </div>
      <div class="domain-item2">
        <div class="" style="width:50%">
          <img src="https://www.svgrepo.com/show/12134/info-circle.svg" width="20" height="20" alt="">
          <span style="padding-left:10px">dev.cywise.io</span>
        </div>
        <div class="right" style="width:50%">
          <span style="float: right"><b>Scan des ports</b></span>
          <svg width="20" height="20" viewBox="0 0 250 250" class="circular-progress" style="--progress:75">
            <circle class="bg"></circle>
            <circle class="fg"></circle>
          </svg>
        </div>
      </div>
      <div class="domain-item2">
        <div class="" style="width:50%">
          <img src="https://www.svgrepo.com/show/12134/info-circle.svg" width="20" height="20" alt="">
          <span style="padding-left:10px">auth.cywise.io</span>
        </div>
        <div class="right" style="width:50%">
          <span style="float: right"><b>Scan des vulnérabilités</b></span>
          <svg width="20" height="20" viewBox="0 0 250 250" class="circular-progress" style="--progress:25">
            <circle class="bg"></circle>
            <circle class="fg"></circle>
          </svg>
        </div>
      </div>
      <div class="domain-item2">
        <div class="" style="width:50%">
          <img src="https://www.svgrepo.com/show/12134/info-circle.svg" width="20" height="20" alt="">
          <span style="padding-left:10px">admin.cywise.io</span>
        </div>
        <div class="right" style="width:50%">
          <span style="float: right"><b>En attente</b></span>
          <svg width="20" height="20" viewBox="0 0 250 250" class="circular-progress" style="--progress:50">
            <circle class="bg"></circle>
            <circle class="fg"></circle>
          </svg>
        </div>
      </div>
    </div>
    <button class="next-button2">Télécharger les résultats</button>
  </div>
  @endif

</div>
</body>
</html>
