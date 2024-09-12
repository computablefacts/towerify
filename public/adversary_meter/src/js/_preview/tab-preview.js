'use strict'

export class TabPreview extends com.computablefacts.widgets.Widget {

  constructor(container, datastore) {
    super(container)
    this.datastore_ = datastore;
    this.observers_ = new com.computablefacts.observers.Subject();
    this.honeypots_ = []
    this.render();
  }

  onShowEvents(callback) {
    if (callback) {
      this.observers_.register('show-events', (params) => callback(params));
    }
  }

  onConfigureHoneypots(callback) {
    if (callback) {
      this.observers_.register('configure-honeypots', (params) => callback(params));
    }
  }

  _handleClick(event) {
    const target = event.target;

    if (target.classList.contains('see-all')) {
      this.observers_.notify('show-events', target.dataset);
    }

    if (target.classList.contains('configure-honeypot')) {
      this.observers_.notify('configure-honeypots', this.honeypots_)
    }
  }

  _generateAllDates(days) {
    const allDates = [];
    const endDate = moment().startOf('day');
    for (let i = 0; i < days; i++) {
      allDates.push(endDate.clone().subtract(i, 'days').format('YYYY-MM-DD'));
    }
    allDates.reverse();
    return allDates;
  }

  _generateData(allDates, data) {

    const humanOrTargetedEvents = Array(allDates.length).fill(0);
    const notHumanOrTargetedEvents = Array(allDates.length).fill(0);

    for (const event of data) {

      const dateIndex = allDates.indexOf(event.date);

      if (dateIndex >= 0) {
        if (typeof event.human_or_targeted !== 'undefined') {
          humanOrTargetedEvents[dateIndex] = event.human_or_targeted;
        }
        if (typeof event.not_human_or_targeted !== 'undefined') {
          notHumanOrTargetedEvents[dateIndex] = event.not_human_or_targeted;
        }
      }
    }

    return {humanOrTargeted: humanOrTargetedEvents, notHumanOrTargeted: notHumanOrTargetedEvents};
  }

  _createChart(elementId, allDates, humanOrTargeted, notHumanOrTargeted) {
    return new Chart(document.getElementById(elementId), {
      type: 'bar', data: {
        labels: allDates, datasets: [{
          label: i18next.t('Attaques Manuelles'),
          data: humanOrTargeted,
          backgroundColor: 'rgba(255, 99, 132, 0.2)',
          borderColor: 'rgba(255, 99, 132, 1)',
          borderWidth: 1,
          categoryPercentage: 1
        }, {
          label: i18next.t('Attaques Automatisées'),
          data: notHumanOrTargeted,
          backgroundColor: 'rgba(54, 162, 235, 0.2)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1,
          categoryPercentage: 1
        }]
      }, options: {
        responsive: true, maintainAspectRatio: false, scales: {
          y: {
            beginAtZero: true, stacked: true, grid: {
              display: false
            }
          }, x: {
            stacked: true, grid: {
              display: false
            }
          }
        }
      }
    });
  }

  _getStatsAndCreateChart(honeypotIndex, tab, honeypot, loader) {
    this.datastore_.getHoneypotStats(honeypot.dns, 7).then((data) => {
      loader.destroy();
      const el = tab.querySelector('.element-' + (honeypotIndex + 1));
      el.innerHTML = `
        <div class="row">
          <div class="col my-2">
            <div class="d-flex my-auto justify-content-between">
                <h4>${honeypot.dns}&nbsp;(&nbsp;<span style="color: #ff9704;">${honeypot.cloud_sensor.toUpperCase()}</span>&nbsp;)</h4>
            </div>
          </div>
          </div>
        </div>
        <div class="row flex-grow-1">
          <div class="col">
            <div class="h-100">
                <canvas id="honeypot${honeypotIndex + 1}Chart" class="w-100 h-100"></canvas>
            </div>
          </div>
        </div>
      `;

      const allDates = data.map(event => event.date);
      const {humanOrTargeted, notHumanOrTargeted} = this._generateData(allDates, data);
      const chart = this._createChart(`honeypot${honeypotIndex + 1}Chart`, allDates, humanOrTargeted,
        notHumanOrTargeted);
    });
  }

  _sortByLevel(alert1, alert2) {
    const levels = ["High", "High (unverified)", "Medium", "Low"];
    const index1 = levels.indexOf(alert1.level);
    const index2 = levels.indexOf(alert2.level);
    return index1 - index2;
  }

  _createDoughnutChart(tab, data, loader) {

    loader.destroy();

    const el = tab.querySelector('.element-4');
    let template = `
      <div class="my-2">
        <h4>${i18next.t('Criticité des vulnérabilités découvertes')}</h4>
      </div>`;

    if (!data.length) {
      template += `
        <div class="background-light-grey border flex-grow-1 p-2 d-flex justify-content-center">
          <p class="my-auto">${i18next.t('Il n\'y a aucune vulnérabilité de détectée.')}</p>
        </div>`;
      el.innerHTML = template;
      return;
    }
    template += `
      <div class="flex-grow-1 h-0 background-light-grey border">
          <div class="h-100">
              <canvas id="alertChart" class="w-100 h-100"></canvas>
          </div>
      </div>`;

    el.innerHTML = template;
    data.sort(this._sortByLevel);

    const translations = {
      "high": i18next.t("Élevé"),
      "High": i18next.t("Élevé"),
      "High (unverified)": i18next.t("Élevé (à vérifier)"),
      "Medium": i18next.t("Moyen"),
      "Low": i18next.t("Faible")
    };

    const chartData = {
      labels: data.map(({level}) => level || 'Unknown'), datasets: [{
        label: i18next.t('Vulnérabilités'), data: data.map(({count}) => count), backgroundColor: data.map(({level}) => {
          switch (level) {
            case "High":
              return "rgb(255, 99, 132)";
            case "High (unverified)":
              return "rgb(255, 222, 150)";
            case "Medium":
              return "rgb(253, 247, 191)";
            case "Low":
              return "rgb(197, 252, 188)";
            default:
              return "rgb(255, 255, 255)";
          }
        }), hoverOffset: 4
      }],
    };

    new Chart(document.getElementById('alertChart'), {
      data: chartData, type: 'doughnut', plugins: [{
        id: 'translation', beforeDraw: function (chart) {

          //Translate legends
          const labels = chart.legend.legendItems;
          labels.forEach(label => {
            label.text = translations[label.text] ? translations[label.text] : label.text;
          });
          chart.legend.legendItems = labels;

          //Translate labels from tooltip
          chart.data.labels.forEach(function (label, index) {
            if (translations[label]) {
              chart.data.labels[index] = translations[label];
            }
          });
        }
      }], options: {
        rotation: -90,
        circumference: 180,
        maintainAspectRatio: false,
        responsive: true,
        title: 'Vulnérabilités',
        legend: {
          labels: {
            position: 'right'
          }
        },
        plugins: {
          translations: true
        }
      }
    });
  }

  _newElement() {
    const template = `
      <style>
        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: 1fr 0.5fr;
            grid-template-areas:
                "element1 element2 element3"
                "element4 element4 element5";
            gap: 20px;
            width: 100%;
            font-size: 1.2em;
        }

        .element {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #4caf50;
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
      </style>
      <div class="grid flex-grow-1">
        <div class="d-flex flex-column element-1">
            <div class="my-auto" id="honeypot1-loader"></div>
        </div>
        <div class="d-flex flex-column element-2">
            <div class="my-auto" id="honeypot2-loader"></div>
        </div>
        <div class="d-flex flex-column element-3">
            <div class="my-auto" id="honeypot3-loader"></div>
        </div>
        <div class="d-flex flex-column element-4">
            <div class="my-auto" id="alerts-loader"></div>
        </div>
        <div class="d-flex flex-column element-5">
            <div class="my-auto" id="last-loader"></div>
        </div>
      </div>
    `;

    const tab = document.createElement('div');
    tab.innerHTML = template;
    tab.className = 'p-3 flex-grow-1 d-flex flex-column ';

    const honeypot1Loader = new com.computablefacts.blueprintjs.MinimalSpinner(tab.querySelector('#honeypot1-loader'))
    const honeypot2Loader = new com.computablefacts.blueprintjs.MinimalSpinner(tab.querySelector('#honeypot2-loader'))
    const honeypot3Loader = new com.computablefacts.blueprintjs.MinimalSpinner(tab.querySelector('#honeypot3-loader'))
    const alertsLoader = new com.computablefacts.blueprintjs.MinimalSpinner(tab.querySelector('#alerts-loader'))
    const lastLoader = new com.computablefacts.blueprintjs.MinimalSpinner(tab.querySelector('#last-loader'))
    const honeypotLoaders = [honeypot1Loader, honeypot2Loader, honeypot3Loader]

    this.datastore_.getHoneypots().then((honeypots) => {
      this.honeypots_ = honeypots;
      for (let i = 0; i < 3; i++) {
        if (honeypots[i]) {
          this._getStatsAndCreateChart(i, tab, honeypots[i], honeypotLoaders[i]);
        } else {
          honeypotLoaders[i].destroy();
          const el = tab.querySelector(`.element-${i + 1}`);
          el.classList.add('background-light-grey', 'border');
          el.innerHTML = `
            <div class="d-flex flex-column justify-content-center align-items-center my-auto">
                <button class="btn btn-primary float-end rounded-0 me-1 configure-honeypot">
                  <i class="fal fa-plus me-2"></i>${i18next.t('Configurer un honeypot')}
                </button>
            </div>`;
        }
      }
    });
    this.datastore_.getAlertStats().then((response) => {
      this._createDoughnutChart(tab, response, alertsLoader)
    })
    this.datastore_.getMostRecentEvent().then((events) => {
      lastLoader.destroy()
      const el = tab.querySelector('.element-5');
      if (events.length > 0) {
        el.innerHTML = `
          <div class="d-flex justify-content-between">
            <h4 class="my-2">${i18next.t('Activité récente')}</h4>
            <span class="mt-auto mb-2">
                <a href="#" class="see-all">${i18next.t("Voir toute l'activité")}</a>
            </span>
          </div>
          <div class="flex-grow-1 h-0 overflow-auto">
            ${events.map(event => `
              <div class="m-1 p-1 bg-white border row">
                <div class="col-10">
                   <div class="fw-bold red d-flex">${event.event}</div>
                   <div class="text-muted d-flex">
                      <div class="me-2">
                        ${i18next.t('Dernier contact le')} ${event.timestamp.replace('+0000', 'UTC')}
                      </div>
                   </div>
                </div>
                <div class="col d-flex justify-content-end align-self-center">
                    <a href="#" class="see-all" class="my-auto">
                        >
                    </a>
                </div>
              </div>
            `).join('')}
          </div>`
      } else {
        el.innerHTML = `
      <div class="d-flex justify-content-between">
        <h4 class="my-2">${i18next.t('Activité des 7 derniers jours')}</h4>
      </div>
      <div class="background-light-grey border flex-grow-1 p-2 d-flex justify-content-center">
        <p class="my-auto">${i18next.t('Aucun événement enregistré.')}</p>
      </div>`
      }
    })

    tab.addEventListener('click', (event) => this._handleClick(event));

    return tab;
  }
}
