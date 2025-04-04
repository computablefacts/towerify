<style>

  .badge {
    display: inline-table;
    vertical-align: middle;
    width: 25px;
    height: 25px;
    border-radius: 50%;
    color: white;
  }

  .badge.high {
    background-color: var(--color-red);
  }

  .badge.medium {
    background-color: var(--color-orange);
  }

  .badge.low {
    background-color: var(--color-green);
  }

  .badge .content {
    display: table-cell;
    vertical-align: middle;
    text-align: center;
  }

  .left {
    width: 50%;
    display: flex;
    align-items: center;
    justify-content: start;
  }

  .right {
    width: 50%;
    display: flex;
    align-items: center;
    justify-content: end;
  }

  /** PROGRESSBAR */

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

  /** MODAL **/

  ::backdrop {
    background-color: rgba(0, 0, 0, 0.05);
  }

  dialog {
    background-color: white;
    padding: var(--spacing-large);
    border: none;
    border-radius: 8px;
    box-shadow: 0 0 0 rgba(0, 0, 0, 0.5);
    width: calc(100vw - 50%);
  }

  .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .header h1 {
    font-size: 24px;
    margin-top: 0;
  }

  .header button {
    background-color: transparent;
    color: black;
  }

  .section h2 {
    font-size: 18px;
  }

  .tag, .count {
    background-color: #ddd;
    color: black;
    padding: 2px 5px;
    border-radius: 10px;
    font-size: 12px;
  }

  .high {
    background-color: #ff4d4d;
    color: white;
    padding: 2px 5px;
    border-radius: 10px;
    font-size: 12px;
  }

  .medium {
    background-color: #ffaa00;
    color: white;
    padding: 2px 5px;
    border-radius: 10px;
    font-size: 12px;
  }

  .low {
    background-color: #4bd28f;
    color: white;
    padding: 2px 5px;
    border-radius: 10px;
    font-size: 12px;
  }

  /** TABLE */

  table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1rem;
  }

  thead tr {
    border-bottom: 1px solid black;
    color: black;
  }

  thead tr th {
    padding: 5px;
  }

  tbody tr {
    border-bottom: 1px solid lightgray;
    color: #555;
  }

  tbody tr:last-child {
    border-bottom: none;
  }

  tbody tr td {
    padding: 5px;
  }

  .dot {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: var(--spacing-medium);
  }

  .dot.high {
    background-color: var(--color-red);
  }

  .dot.medium {
    background-color: var(--color-orange);
  }

  .dot.low {
    background-color: var(--color-green);
  }

</style>
<h1>Vos résultats</h1>
<p>Retrouvez ici toutes vos vulnérabilités</p>
<div class="list">
  <!-- FILLED DYNAMICALLY -->
</div>
<button>Télécharger les résultats</button>
<dialog>
  <!-- FILLED DYNAMICALLY -->
</dialog>
<script>

  const assets = @json($assets);
  const elDialog = document.querySelector("dialog");
  const elDomains = document.querySelector('.list');

  const showModal = (azzet) => {

    const asset = assets.find(a => a.asset === azzet);
    const ports = asset.ports;
    const vulnerabilities = asset.vulnerabilities;
    const nbVulnsHigh = vulnerabilities.filter(vuln => vuln.level === "high").length;
    const nbVulnsMedium = vulnerabilities.filter(vuln => vuln.level.startsWith("medium")).length;
    const nbVulnsLow = vulnerabilities.filter(vuln => vuln.level.startsWith("low")).length;

    let portsStr = ports.map(port => `
      <tr>
        <td style="float:right">${port.port}</td>
        <td>${port.services[0]}</td>
        <td>${port.products[0]}</td>
        <td>${port.tags.map(tag => `<span class="tag">${tag}</span>`).join("")}</td>
      </tr>
    `).join("");

    if (portsStr.trim() === '') {
      portsStr = "<tr><td colspan='4'>In n'y a aucun port d'ouvert.</td></tr>";
    }

    let vulnsStr = vulnerabilities.map(vuln => `
      <tr>
        <td style="float:right">${vuln.port}</td>
        <td><span class="dot ${vuln.level}"></span> ${vuln.cve_id ? `${vuln.cve_id} - ${vuln.title2}` : vuln.title}</td>
      </tr>
    `).join("");

    if (vulnsStr.trim() === '') {
      vulnsStr = "<tr><td colspan='2'>In n'y a aucune vulnérabilité de détectée.</td></tr>";
    }

    elDialog.innerHTML = `
      <div class="header">
        <h1>Résultats</h1>
        <div class="close">
          <button autofocus><b>X</b></button>
        </div>
      </div>
      <p>${asset.asset}</p>
      <div class="section">
        <h2>Ports ouverts&nbsp;<span class="count">${ports.length}</span></h2>
        <table>
          <thead>
          <tr>
            <th style="float:right">Port</th>
            <th>Service</th>
            <th>Produit</th>
            <th>Technologies</th>
          </tr>
          </thead>
          <tbody>
            ${portsStr}
          </tbody>
        </table>
      </div>
      <div class="section">
        <h2>
          Vulnérabilités&nbsp;
          <span class="high">${nbVulnsHigh}</span>&nbsp;
          <span class="medium">${nbVulnsMedium}</span>&nbsp;
          <span class="low">${nbVulnsLow}</span>&nbsp;
        </h2>
        <table>
          <thead>
          <tr>
            <th style="float:right">Port</th>
            <th>Criticité</th>
          </tr>
          </thead>
          <tbody>
            ${vulnsStr}
          </tbody>
        </table>
      </div>
    `;

    const elCloseButton = document.querySelector("dialog button");
    elCloseButton.addEventListener("click", () => elDialog.close());

    elDialog.showModal();
  };

  assets.forEach(asset => {

    const portScanRunning = asset.timeline.nmap.start && !asset.timeline.nmap.end;
    const portScanCompleted = asset.timeline.nmap.start && asset.timeline.nmap.end;
    const vulnScanRunning = asset.timeline.sentinel.start && !asset.timeline.sentinel.end;
    const vulnScanCompleted = asset.timeline.sentinel.start && asset.timeline.sentinel.end;

    const elAsset = document.createElement('div');
    elAsset.classList.add('list-item');

    if (vulnScanCompleted) { // the scan completed

      const nbVulnsHigh = asset.vulnerabilities.filter(vuln => vuln.level === "high").length;
      const nbVulnsMedium = asset.vulnerabilities.filter(vuln => vuln.level.startsWith("medium")).length;
      const nbVulnsLow = asset.vulnerabilities.filter(vuln => vuln.level.startsWith("low")).length;

      elAsset.innerHTML = `
        <div class="left">
          <a href="#" onclick="showModal('${asset.asset}')">
            <img src="https://www.svgrepo.com/show/12134/info-circle.svg" width="20" height="20">
          </a>
          <span style="padding-left:var(--spacing-medium)">${asset.asset}</span>
        </div>
        <div class="right">
          <div class="badge high">
            <div class="content">${nbVulnsHigh}</div>
          </div>
          <div class="badge medium" style="margin-left:var(--spacing-medium)">
            <div class="content">${nbVulnsMedium}</div>
          </div>
          <div class="badge low" style="margin-left:var(--spacing-medium)">
            <div class="content">${nbVulnsLow}</div>
          </div>
        </div>
      `;

    } else { // the scan is running

      let state = 'En attente';
      let progress = 0;

      if (portScanRunning) {
        state = 'Scan de ports en cours';
        progress = 25;
      }
      if (portScanCompleted) {
        state = 'Scan de ports terminé';
        progress = 50;
      }
      if (vulnScanRunning) {
        state = 'Scan de vulnérabilités en cours';
        progress = 75;
      }

      elAsset.innerHTML = `
        <div class="left">
          <a href="#" onclick="showModal('${asset.asset}')">
            <img src="https://www.svgrepo.com/show/12134/info-circle.svg" width="18" height="18">
          </a>
          <span style="padding-left:var(--spacing-medium)">${asset.asset}</span>
        </div>
        <div class="right">
          <b style="font-size:var(--font-size-small)">${state}</b>
          <svg width="20" height="20" viewBox="0 0 250 250" class="circular-progress" style="--progress:${progress}">
            <circle class="bg"></circle>
            <circle class="fg"></circle>
          </svg>
        </div>
      `;
    }

    elDomains.appendChild(elAsset);
  });

  setTimeout(() => {
    if (!elDialog.open) {
      window.location.reload();
    }
  }, 15000); // update every 15 seconds

</script>
