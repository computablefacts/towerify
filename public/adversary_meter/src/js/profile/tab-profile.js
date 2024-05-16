'use strict'

export class TabProfile extends com.computablefacts.widgets.Widget {

  constructor(container, datastore, name = null, id) {
    super(container)
    this.datastore_ = datastore;
    this.name_ = name;
    this.id_ = id;
    this.render();
  }

  _createRiskTemplate(level){
    switch (level) {
      case 'high':
        return `<span class="badge background-red">${i18next.t('Très actif')}</span>`;
      case 'medium':
        return `<span class="badge background-orange">${i18next.t('Assez actif')}</span>`;
      case 'low':
        return `<span class="badge">${i18next.t('Peu actif')}</span>`;
      default:
        return `<span class="badge">${level}</span>`;
    }
  }

  _newElement() {
    const template = `
      <style>
        .grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            grid-template-rows: 0.8fr 0.8fr 0.2fr 1fr 0.8fr;
            grid-template-areas:
                "element1 element4"
                "element2 element5"
                "element2 element6"
                "element3 element6"
                "element3 element6";
            gap: 20px;
            width: 100%;
            font-size: 1.2em;
        }

        .attacker-name {
          font-size: 1.5em;
        }

        .element {
            display: flex;
            padding: 20px;
        }

        .element-1 {
            grid-area: element1;
        }

        .element-2 {
            grid-area: element2;
        }

        .element-3 {
            grid-area: element3;
        }

        .element-4 {
            grid-area: element4;
        }

        .element-5 {
            grid-area: element5;
        }

        .element-6 {
            grid-area: element6;
        }
      </style>
      <div class="grid flex-grow-1">
        <div class="d-flex flex-column element-1">
            <div id="date-loader" class="mx-auto my-auto"></div>
        </div>
        <div class="d-flex flex-column element-2">
            <div id="activity-loader" class="my-auto"></div>
        </div>
        <div class="d-flex flex-column element-3">
            <div id ="last-loader" class="my-auto"></div>
        </div>
        <div class="d-flex flex-column element-4">
            <div id="ip-loader" class="my-auto"></div>
        </div>
        <div class="d-flex flex-column element-5">
            <div id="tool-loader" class="my-auto"></div>
        </div>
        <div class="d-flex flex-column element-6">
            <div id="competency-loader" class="my-auto"></div>
        </div>
      </div>
    `;

    const tab = document.createElement('div');
    tab.innerHTML = template;
    tab.className = 'p-3 flex-grow-1 d-flex flex-column bg-white border orange-shadow'

    const dateLoader = new com.computablefacts.blueprintjs.MinimalSpinner(tab.querySelector('#date-loader'))
    const activityLoader = new com.computablefacts.blueprintjs.MinimalSpinner(tab.querySelector('#activity-loader'))
    const lastLoader = new com.computablefacts.blueprintjs.MinimalSpinner(tab.querySelector('#last-loader'))
    const ipLoader = new com.computablefacts.blueprintjs.MinimalSpinner(tab.querySelector('#ip-loader'))
    const toolLoader = new com.computablefacts.blueprintjs.MinimalSpinner(tab.querySelector('#tool-loader'))
    const competencyLoader = new com.computablefacts.blueprintjs.MinimalSpinner(tab.querySelector('#competency-loader'))
    this.datastore_.getAttackerProfile(this.id_).then((response) => {
      dateLoader.destroy();
      const el = tab.querySelector('.element-1');
      el.innerHTML = `
        <div class="d-flex justify-content-between mb-2">
            <div class="d-flex">
                <h4 class="my-auto">${i18next.t('Nom interne')} :&nbsp;</h4>
                <span class="attacker-name my-auto blue">${this.name_}</span>
            </div>
        </div>
        <div class="d-flex border justify-content-between flex-grow-1">
            <div class="d-flex mx-2 my-auto">
                <div class="fw-bold">${i18next.t('Premier contact')} :&nbsp;</div>
                <div class="blue me-2">${moment(response.first_contact, "YYYY-MM-DD HH:mm:ss Z").format('YYYY-MM-DD HH:mm:ss UTC')}</div>
            </div>
            <div class="d-flex mx-2 my-auto">
                <div class="fw-bold">${i18next.t('Dernier contact')} :&nbsp;</div>
                <div class="blue">${moment(response.last_contact, "YYYY-MM-DD HH:mm:ss Z").format('YYYY-MM-DD HH:mm:ss UTC')}</div>
            </div>
            <div class="me-2 my-auto">${this._createRiskTemplate(response.aggressiveness)}</div>
        </div>`
    })

    this.datastore_.getAttackerStats(this.id_).then((response) => {
      activityLoader.destroy();
      const el = tab.querySelector('.element-2');
      el.innerHTML = `
        <h4 class="my-2">${i18next.t('Activité')}</h4>
        <div class="d-flex border flex-grow-1">
            <div class="col-3 my-auto">
                <div class="d-flex flex-column align-items-center">
                    <div style="font-size: 1.2rem;">${i18next.t('Attaques')}</div>
                    <div>
                        <span class="font-size-35 fw-bold red">${response.attacks}</span>
                    </div>
                </div>
            </div>
            <div class="col-3 my-auto">
                <div class="d-flex flex-column align-items-center">
                    <div style="font-size: 1.2rem;">${i18next.t('Human')}</div>
                    <div>
                        <span class="font-size-35 fw-bold green">${response.human}</span>
                    </div>
                </div>
            </div>
            <div class="col-3 my-auto">
                <div class="d-flex flex-column align-items-center">
                    <div style="font-size: 1.2rem;">${i18next.t('Targeted')}</div>
                    <div>
                        <span class="font-size-35 fw-bold orange">${response.targeted}</span>
                    </div>
                </div>
            </div>
            <div class="col-3 my-auto">
                <div class="d-flex flex-column align-items-center">
                    <div style="font-size: 1.2rem;">${i18next.t('CVE')}</div>
                    <div>
                        <span class="font-size-35 fw-bold lightblue">${response.cve}</span>
                    </div>
                </div>
            </div>
        </div>`
    });

    this.datastore_.getMostRecentEvent(this.id_).then((events) => {
      lastLoader.destroy();
      const el = tab.querySelector('.element-3');
      if (events.length > 0) {
        el.innerHTML = `
            <div class="d-flex justify-content-between">
                <h4 class="my-2">${i18next.t('Activité des 7 derniers jours')}</h4>
                <span class="mt-auto mb-2">
                    <span class="ms-2">${events[0].timestamp.replace('+0000', 'UTC')}</span>
                </span>
            </div>
            <div class="flex-grow-1 h-0 overflow-auto">
                ${events.map(event => `
                <div class="m-1 p-2 bg-white border">
                    <div class="fw-bold red d-flex justify-content-between">
                        <span>${event.event}</span>
                        <span>${event.ip}</span>
                    </div>
                    <div class="row">
                        <div class="col-2">
                            <span class="fw-bold">${i18next.t('Point d\'accès')}</span>
                        </div>
                        <div class="col">
                            <span class="ms-1">${event.honeypot}</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-2">
                            <span class="fw-bold">${i18next.t('Détails')}</span>
                        </div>
                        <div class="col">
                            <span class="ms-1">${event.detail}</span>
                        </div>
                    </div>
                </div>
                `).join('')}
            </div>`
      } else {
        el.innerHTML = `
            <div class="d-flex justify-content-between">
                <h4 class="my-2">${i18next.t('Activité des 7 derniers jours')}</h4>
            </div>
            <div class="border flex-grow-1 p-2">
                <p>${i18next.t('Aucun événement trouvé pour cet attaquant.')}</p>
            </div>
        `;
      }
    });

    this.datastore_.getBlacklistIps(this.id_).then((ips) => {
      ipLoader.destroy();
      const el = tab.querySelector('.element-4');
      let flags = [...new Set(ips.filter(ip => ip.countryCode !== '-').map(ip => ip.countryCode.toLowerCase()))]
        .map(code => `<span class="fi fi-${code} me-2"></span>`)
        .join('');

      if (flags === '') {
        flags = `<div class='m-auto'>${i18next.t('Aucune localisation n\'a été détectée.')}</div>`;
      }

      el.innerHTML = `
        <div class="d-flex justify-content-between">
            <h4 class="my-2">${i18next.t('Localisation des IP utilisées')}</h4>
        </div>
        <div class="h-0 border flex-grow-1 p-2 d-flex flex-wrap overflow-auto justify-content-center">
            ${flags}
        </div>
      `;
    });

    this.datastore_.getTools(this.id_).then((tools) => {
      toolLoader.destroy();
      const el = tab.querySelector('.element-5');
      let toolIcons = tools
        .map(tool => `<span class="blue my-auto">${tool}</span><i class="fal fa-check green mx-2 my-auto"></i>`)
        .join('');

      if (toolIcons === ''){
        toolIcons = `<div class='m-auto'>${i18next.t('Aucun outil n\'a été détecté.')}</div>`;
      }

      el.innerHTML = `
    <div class="d-flex justify-content-between">
        <h4 class="my-2">${i18next.t('Outils utilisés')}</h4>
    </div>
    <div class="h-0 border flex-grow-1 p-2 d-flex flex-wrap overflow-auto justify-content-center">
        ${toolIcons}
    </div>
  `;
    });
    this.datastore_.calculateCompetencyScores(this.id_).then((competencyScores) => {
      competencyLoader.destroy();
      const el = tab.querySelector('.element-6');
      el.innerHTML = `
        <h4 class="my-2">${i18next.t('Compétences')}</h4>
        <div class="flex-grow-1 h-0">
            <canvas id="competencyChart" class="w-100 h-100"></canvas>
        </div>
    `;
      const ctx = document.getElementById('competencyChart').getContext('2d');

      new Chart(ctx, {
        type: 'radar',
        data: {
          labels: ['Toolbox', 'Stealth Techniques', 'Curated Wordlist', 'Persistence', 'Manual Testing', 'Cve Collection'],
          datasets: [{
            label: 'Skill Scores',
            data: [competencyScores.toolbox, competencyScores.stealth_tech, competencyScores.curated_wordlist, competencyScores.persistence, competencyScores.manual_testing, competencyScores.cve_collection],
            backgroundColor: 'rgba(255, 159, 64, 0.2)',
            borderColor: 'rgba(255, 159, 64, 1)',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            r: {
              beginAtZero: true,
              ticks: {
                min: 0,
                max: 10,
                display: false
              },
              grid: {
                color: ['transparent', 'transparent', 'transparent', 'transparent', 'transparent' ,
                  'rgba(0, 0, 0, 0.1)', 'transparent', 'transparent', 'transparent', 'transparent', 'rgba(0, 0, 0, 0.1)'],
              }
            }
          }
        }
      });
    });

    return tab;
  }
}
