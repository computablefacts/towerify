<html>
<head>
  <title>Cyber TODO (Powered by {{ config('app.name') }})</title>
  <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
  <meta name="keywords"
        content="honeypot, vulnerability scanner, assets discovery, attack surface management, shadow it">
  <meta name="description"
        content="{{ config('app.name') }} is a hybrid between a Honeypot and a Vulnerability Scanner that helps you get a better understanding of your organization's security posture and what should be done to take it to the next level. No installation required.">
  <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.0/build/pure-min.css"
        integrity="sha384-nn4HPE8lTHyVtfCBi5yW9d20FjT8BJwUXyWZT9InLYax14RDjBj46LmSztkmNP9w" crossorigin="anonymous">
  <style>

    :root {
      --app-header: #00264b;
      --app-background: #fff6e2;
      --app-borders: #c7cdd1;
      --app-width: 1100px
    }

    body {
      font: normal 12px Arial, Helvetica, sans-serif;
      line-height: 180%;
      text-align: left;
      color: var(--app-header);
      margin: 0;
      background: var(--app-background);
    }

    .page {
      width: var(--app-width);
      background: #ffffff;
      margin: auto;
      border-left: 1px solid var(--app-borders);
      border-right: 1px solid var(--app-borders);
      border-bottom: 1px solid var(--app-borders);
    }

    .header {
      width: var(--app-width);
      padding-top: 8px;
      padding-bottom: 8px;
      border-top: 18px solid #ff9704;
      border-bottom: 1px solid var(--app-borders);
    }

    .header .logo {
      float: left;
      margin: 0;
      padding: 5px 0 5px 15px;
    }

    .content {
      padding: 30px;
      border-bottom: 1px solid var(--app-borders);
    }

    .content h1 {
      font: normal 27px/28px Arial, Helvetica, sans-serif;
      margin-top: 0;
    }

    .content p {
      font-size: 16px;
    }

    .content .list {
      font-size: 16px;
      background-color: #f0f0f7;
      padding: 30px;
      margin-bottom: 30px;
    }

    .content .form button {
      margin-top: 1em;
    }

    .content *, .content *:before, .content *:after {
      box-sizing: border-box;
    }

    .footer {
      width: var(--app-width);
      padding-top: 8px;
      padding-bottom: 8px;
    }

    .copyright {
      padding-left: 30px;
      padding-right: 0;
    }

    .terms {
      padding-left: 0;
      padding-right: 30px;
      float: right;
    }

    .loader {
      border: 8px solid #f3f3f3;
      border-top: 8px solid var(--app-header);
      border-radius: 50%;
      width: 50px;
      height: 50px;
      animation: spin 1s linear infinite;
      margin: auto;
      display: block;
    }

    .small-loader {
      border: 5px solid #f3f3f3;
      border-top: 5px solid var(--app-header);
      border-radius: 50%;
      width: 20px;
      height: 20px;
      animation: spin 1s linear infinite;
      margin: auto;
      float: right;
    }

    .d-none {
      display: none;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }
      100% {
        transform: rotate(360deg);
      }
    }

    .high {
      background-color: #FCD7D7 !important;
      border: 1px solid red;
    }

    .high-unverified {
      background-color: #e0e0e0 !important;
      border: 1px solid #4f4c4c;
    }

    .medium {
      background-color: #ffde96 !important;
      border: 1px solid #ffc107;
    }

    .low {
      background-color: #FDF7BF !important;
      border: 1px solid #dcbd35;
    }

    .description-text {
      font-size: smaller;
      color: #888888;
    }

    .description-text > div:first-child {
      margin-bottom: 0.25rem;
      margin-top: 0.25rem;
    }

    #vulnerabilities > div {
      margin-bottom: 0.75rem;
    }

    .fw-bold {
      font-weight: bold;
    }

    .badge {
      float: right; /* Align to the right */
      color: gray; /* Gray text color */
      --bs-badge-padding-x: 0.65em;
      --bs-badge-padding-y: 0.35em;
      --bs-badge-font-size: 0.75em;
      --bs-badge-font-weight: 700;
      --bs-badge-color: #fff;
      --bs-badge-border-radius: 0.375rem;
      display: inline-block;
      padding: 0.35em 0.65em;
      font-size: 0.75em;
      font-weight: 700;
      line-height: 1;
      text-align: center;
      white-space: nowrap;
      vertical-align: baseline;
      border-radius: 1rem !important;
      background-color: transparent !important;
      border: 1px solid #7F87B3;
      color: #7F87B3;
    }

    .no-vuln {
      background-color: transparent;
      text-align: center;
    }

  </style>
</head>
<body>
<div class="page">
  <div class="header">
    <div class="logo">
      <a href="{{ config('towerify.website') }}" rel="noreferrer">
        <img src="/favicon-cywise.png" alt="Cywise" title="Cywise" height="55">
      </a>
    </div>
    <br clear="all">
  </div>
  <div class="content">
    <h1>Cyber TODO</h1>
    <p>
      {{ __('Someone has shared vulnerabilities with you on assets for which you are responsible. Please fix the
      vulnerabilities below and check the corresponding box. A new scan will verify that the problem has been solved.')
      }}
    </p>
    <div class="loader" id="loader"></div>
    <div id="vulnerabilities" class="list d-none"></div>
  </div>
  <div class="footer">
    <span class="copyright">{{ config('app.name') }} - 178 boulevard Haussmann 75008 Paris France - 844389882</span>
    <a href="/terms" class="terms">Conditions d'utilisation</a>
  </div>
</div>
<script>
  document.addEventListener("DOMContentLoaded", function () {

    const hash = "{{ $hash->hash }}"

    const disableInputs = (disable) => {
      const inputs = document.querySelectorAll('input[type="checkbox"]');
      inputs.forEach(input => {
        input.disabled = disable;
      });
    };

    fetch(`/am/api/v2/public/vulnerabilities/${hash}`)
    .then(response => response.json())
    .then(data => {
      let vulnerabilitiesDiv = document.getElementById('vulnerabilities');

      if (data.length === 0) {
        document.getElementById('loader').style.display = "none";

        let img = document.createElement('img');
        img.src = '/images/nothing-to-show.png';
        img.alt = 'Nothing to Show';

        vulnerabilitiesDiv.appendChild(img);
        vulnerabilitiesDiv.classList.remove('d-none');
        vulnerabilitiesDiv.classList.add('no-vuln');
      } else {
        vulnerabilitiesDiv.classList.remove('d-none');

        const levelOrder = {
          'High': 1, 'High (unverified)': 2, 'Medium': 3, 'Low': 4
        };

        data.sort((a, b) => {
          return levelOrder[a.level] - levelOrder[b.level];
        });

        data.forEach((vuln, index) => {
          let listItem = document.createElement('div');
          const level = {
            'High': 'high', 'Medium': 'medium', 'High (unverified)': 'high-unverified', 'Low': 'low'
          };
          listItem.innerHTML = `
              ${index + 1}. <input id=${vuln.id} type="checkbox" data-asset="${vuln.asset}">
              ${vuln.asset} (ip=${vuln.ip}, port=${vuln.port})
              <span class="level ${level[vuln.level]}" style="float:right; border-radius: 50%; width: 15px; height: 15px;"></span>
              <span class="badge" style="display: none;">Vérification...</span>
              <span class="small-loader" style="display: none;"></span>
              <div class="description-text">
                  <div><span class="fw-bold">Vulnérabilités:</span> ${vuln.vulnerability}</div>
                  <div><span class="fw-bold">Comment y rémédier ?</span> ${vuln.remediation}</div>
              </div>
          `;
          vulnerabilitiesDiv.appendChild(listItem);
          if (vuln.is_scan_in_progress) {

            listItem.style.textDecoration = 'line-through';

            const checkboxes = document.querySelectorAll(`input[data-asset="${vuln.asset}"]`);
            checkboxes.forEach(checkbox => {
              checkbox.disabled = true;
            });

            const levelSpan = listItem.querySelector(".level");
            levelSpan.style.display = "none";

            const badgeSpan = listItem.querySelector(".badge");
            badgeSpan.style.display = "inline-block";
          }
        });

        document.getElementById('loader').style.display = "none";
      }
    })
    .catch(error => console.error('Error fetching vulnerabilities:', error));

    document.addEventListener('change', function (e) {
      if (e.target.tagName === 'INPUT' && e.target.getAttribute('type') === 'checkbox') {

        const asset = e.target.getAttribute('data-asset');
        const checkboxes = document.querySelectorAll(`input[data-asset="${asset}"]`);
        const parentDiv = e.target.closest('div');
        const levelSpan = parentDiv.querySelector(".level");
        const badgeSpan = parentDiv.querySelector(".badge");
        const loaderSpan = parentDiv.querySelector(".small-loader");

        if (e.target.checked) {

          disableInputs(true);
          levelSpan.style.display = "none";
          loaderSpan.style.diplay = "inline-block";
          fetch(`/am/api/v2/public/alert/${e.target.id}/mark-and-check-again`, {
            method: 'POST', headers: {
              'Content-Type': 'application/json'
            }, body: JSON.stringify({
              hash: hash
            })
          })
          .then(() => {
            disableInputs(false);
            checkboxes.forEach(checkbox => {
              checkbox.disabled = true;
            });
            parentDiv.style.textDecoration = 'line-through';
            badgeSpan.style.display = "inline-block";
            loaderSpan.style.diplay = "none";
          })
          .finally(() => loaderSpan.style.diplay = "none")
        } else {
          checkboxes.forEach(checkbox => {
            checkbox.disabled = false;
          });
          parentDiv.style.textDecoration = 'none';
          levelSpan.style.display = "inline-block";
          badgeSpan.style.display = "none";
        }
      }
    });
  });

</script>
</body>
</html>
