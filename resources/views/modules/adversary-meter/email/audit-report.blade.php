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
  <title>Cywise : Rapport d'audit</title>
  <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=12.0, minimum-scale=1.0, user-scalable=yes">
</head>
<body>
<br>
<center>
  <div class="grey" style="display:inline-block;vertical-align:middle;">
    <b>CYWISE <span style="color:#f8b502;font-weight:bolder">:</span> RAPPORT D'AUDIT</b>
    <br><br>
  </div>
</center>
<hr/>
<div class='heading grey'>
  Table des matières
</div>
<ol>
  <li><a href="#vulns">Vulnérabilités</a>
    <ul>
      <li><a href="#vulns-high">Hautes ({{ $alerts_high->count() }})</a></li>
      <li><a href="#vulns-medium">Moyennes ({{ $alerts_medium->count() }})</a></li>
      <li><a href="#vulns-low">Basses ({{ $alerts_low->count() }})</a></li>
    </ul>
  </li>
  <li><a href="#assets">Actifs</a>
    <ul>
      <li><a href="#assets-monitored">Surveillés ({{ $assets_monitored->count() }})</a></li>
      <li><a href="#assets-not-monitored">À surveiller ({{ $assets_not_monitored->count() }})</a></li>
    </ul>
  </li>
</ol>
<hr/>
<div class='heading'>
  <a name="vulns">1. Vulnérabilités</a>
</div>
<div class="section">
  <a name="vulns-high">1.1. Hautes ({{ $alerts_high->count() }})</a><br>
</div>
@if($alerts_high->count())
<table>
  <colgroup>
    <col span="1">
    <col span="1" style="width: 140px">
    <col span="1" style="width: 50px">
    <col span="1" style="width: 100px">
    <col span="1" style="width: 140px">
  </colgroup>
  <thead>
  <tr>
    <th>Actif</th>
    <th>IP</th>
    <th>Port</th>
    <th>Protocole</th>
    <th>CVE</th>
  </tr>
  </thead>
  <tbody>
  @foreach ($alerts_high as $vuln)
  <tr>
    <td class="ellipsis" title="{{ $vuln->asset()->first()?->asset }}">
      <span style="color:#f8b502;font-weight:bolder">{{ $vuln->asset()->first()?->asset }}</span>
    </td>
    <td>{{ $vuln->port()?->ip }}</td>
    <td>{{ $vuln->port()?->port }}</td>
    <td>{{ $vuln->port()?->protocol }}</td>
    <td>
      @if($vuln->cve_id)
      <a href="https://nvd.nist.gov/vuln/detail/{{$vuln->cve_id}}" target="_blank">{{ $vuln->cve_id }}</a>
      @else
      n/a
      @endif
    </td>
  </tr>
  <tr>
    <td colspan="5">
      <b>Vulnérabilité.</b> {{ $vuln->vulnerability }}
    </td>
  </tr>
  <tr class="end-of-block">
    <td colspan="5">
      <b>Remédiation.</b> {{ $vuln->remediation }}
    </td>
  </tr>
  @endforeach
  </tbody>
</table>
@else
<div class="grey">
  <p>Aucune vulnérabilité n'a été détectée pour le moment.</p>
</div>
@endif
<div class="section">
  <a name="vulns-medium">1.2. Moyennes ({{ $alerts_medium->count() }})</a>
</div>
@if($alerts_medium->count())
<table>
  <colgroup>
    <col span="1">
    <col span="1" style="width: 140px">
    <col span="1" style="width: 50px">
    <col span="1" style="width: 100px">
    <col span="1" style="width: 140px">
  </colgroup>
  <thead>
  <tr>
    <th>Actif</th>
    <th>IP</th>
    <th>Port</th>
    <th>Protocole</th>
    <th>CVE</th>
  </tr>
  </thead>
  <tbody>
  @foreach ($alerts_medium as $vuln)
  <tr>
    <td class="ellipsis" title="{{ $vuln->asset()->first()?->asset }}">
      <span style="color:#f8b502;font-weight:bolder">{{ $vuln->asset()->first()?->asset }}</span>
    </td>
    <td>{{ $vuln->port()?->ip }}</td>
    <td>{{ $vuln->port()?->port }}</td>
    <td>{{ $vuln->port()?->protocol }}</td>
    <td>
      @if($vuln->cve_id)
      <a href="https://nvd.nist.gov/vuln/detail/{{$vuln->cve_id}}" target="_blank">{{ $vuln->cve_id }}</a>
      @else
      n/a
      @endif
    </td>
  </tr>
  <tr>
    <td colspan="5">
      <b>Vulnérabilité.</b> {!! $vuln->vulnerability !!}
    </td>
  </tr>
  <tr class="end-of-block">
    <td colspan="5">
      <b>Remédiation.</b> {!! $vuln->remediation !!}
    </td>
  </tr>
  @endforeach
  </tbody>
</table>
@else
<div class="grey">
  <p>Aucune vulnérabilité n'a été détectée pour le moment.</p>
</div>
@endif
<div class="section">
  <a name="vulns-low">1.3. Basses ({{ $alerts_low->count() }})</a>
</div>
@if($alerts_low->count())
<table>
  <colgroup>
    <col span="1">
    <col span="1" style="width: 140px">
    <col span="1" style="width: 50px">
    <col span="1" style="width: 100px">
    <col span="1" style="width: 140px">
  </colgroup>
  <thead>
  <tr>
    <th>Actif</th>
    <th>IP</th>
    <th>Port</th>
    <th>Protocole</th>
    <th>CVE</th>
  </tr>
  </thead>
  <tbody>
  @foreach ($alerts_low as $vuln)
  <tr>
    <td class="ellipsis" title="{{ $vuln->asset()->first()?->asset }}">
      <span style="color:#f8b502;font-weight:bolder">{{ $vuln->asset()->first()?->asset }}</span>
    </td>
    <td>{{ $vuln->port()?->ip }}</td>
    <td>{{ $vuln->port()?->port }}</td>
    <td>{{ $vuln->port()?->protocol }}</td>
    <td>
      @if($vuln->cve_id)
      <a href="https://nvd.nist.gov/vuln/detail/{{$vuln->cve_id}}" target="_blank">{{ $vuln->cve_id }}</a>
      @else
      n/a
      @endif
    </td>
  </tr>
  <tr>
    <td colspan="5">
      <b>Vulnérabilité.</b> {!! $vuln->vulnerability !!}
    </td>
  </tr>
  <tr class="end-of-block">
    <td colspan="5">
      <b>Remédiation.</b> {!! $vuln->remediation !!}
    </td>
  </tr>
  @endforeach
  </tbody>
</table>
@else
<div class="grey">
  <p>Aucune vulnérabilité n'a été détectée pour le moment.</p>
</div>
@endif
<div class='heading'>
  <a name="assets">2. Actifs</a>
</div>
<div class="section">
  <a name="assets-monitored">2.1. Surveillés ({{ $assets_monitored->count() }})</a>
</div>
@if($assets_monitored->count())
<table>
  <colgroup>
    <col span="1">
  </colgroup>
  <thead>
  <tr>
    <th>Actif</th>
  </tr>
  </thead>
  <tbody>
  @foreach ($assets_monitored as $asset)
  <tr>
    <td class="ellipsis" title="{{$asset->asset}}">
      {{ $asset->asset }}
      @if($asset->scanInProgress()->isEmpty())
      <span class="badge" style="float:right;color:#00264b;background-color:#4bd28f">
        scan terminé
      </span>
      @else
      <span class="badge" style="float:right;color:#00264b;background-color:#ffaa00">
        scan en cours
      </span>
      @endif
    </td>
  </tr>
  @endforeach
  </tbody>
</table>
@else
<div class="grey">
  <p>Aucun actif n'a été ajouté récemment.</p>
</div>
@endif
<div class="section">
  <a name="assets-not-monitored">2.2. À surveiller ({{ $assets_not_monitored->count() }})</a>
</div>
@if($assets_not_monitored->count())
<table>
  <colgroup>
    <col span="1">
  </colgroup>
  <thead>
  <tr>
    <th>Actif</th>
  </tr>
  </thead>
  <tbody>
  @foreach ($assets_not_monitored as $asset)
  <tr>
    <td class="ellipsis" title="{{ $asset->asset }}">
      {{ $asset->asset }}
    </td>
  </tr>
  @endforeach
  </tbody>
</table>
@else
<div class="grey">
  <p>Aucun actif n'a été ajouté récemment. </p>
</div>
@endif
<center class="grey"><b style="color:#f8b502;font-weight:bolder">*</b></center>
</body>
</html>