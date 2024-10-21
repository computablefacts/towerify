'use strict'

import {HiddenAlert} from "../_datastore/hidden-alert.js";

export class Panel extends com.computablefacts.widgets.Widget {

  static IS_OPEN = false;

  constructor(container, datastore, asset, selectedVuln = null) {

    super(container);

    this.observers_ = new com.computablefacts.observers.Subject();
    this.datastore_ = datastore;
    this.asset_ = asset;
    this.selectedVuln_ = selectedVuln;
    this.closeDetail = false;
    this.render();
  }

  onClose(callback) {
    this.observers_.register('close', data => {
      if (callback) {
        callback(data);
      }
    });
  }

  /**
   * @override
   */
  _newElement() {
    if (Panel.IS_OPEN) {
      return null;
    }

    Panel.IS_OPEN = true;

    const div = document.createElement('div');
    const drawer = new com.computablefacts.blueprintjs.MinimalDrawer(div);
    const clickHandler = (event) => {
      if (Panel.IS_OPEN) {

        const target = event.target;
        const modal = document.getElementById('screenshotModal');

        if (event.target === modal || target.classList.contains('close')) {
          modal.style.display = "none";
        }

        if (target.classList.contains('screenshot-icon')) {
          const screenshotId = target.getAttribute('data-screenshot-id');
          if (screenshotId) {
            this.datastore_.getScreenshot(screenshotId).then((response) => {
              if (response.screenshot) {
                this._showModal(response.screenshot);
              }
            })
          }
        }
      }
    };

    drawer.onClose(() => {
      Panel.IS_OPEN = false
      this.observers_.notify('close');
    });
    drawer.onOpen((drawerEl) => {

      Panel.IS_OPEN = true;

      this.datastore_.getInfosFromAsset(this.asset_).then(infos => this._redraw(drawerEl, infos));
      drawerEl.removeEventListener('click', clickHandler);
      drawerEl.addEventListener('click', clickHandler);
    });
    drawer.show = true;

    this.register(drawer);
    return div;
  }

  _redraw(drawerEl, infos) {

    this.asset_ = infos.asset;
    drawerEl.innerHTML = this._newBody(infos);
    const closeDetailsEl = drawerEl.querySelector('.fa-times-circle');

    if (closeDetailsEl) {
      closeDetailsEl.addEventListener('click', () => {
        this.selectedVuln_ = null;
        this._redraw(drawerEl, infos);
      });
    }

    const vulnsEl = drawerEl.querySelector('#tbl-vulns');

    if (vulnsEl) {
      vulnsEl.addEventListener('click', event => {

        const tr = event.target.closest('tr');
        const vulnId = tr.getAttribute('vuln-id');
        const selectedVuln = infos.vulnerabilities.find(vuln => vuln.id == vulnId); // force type coercion

        if (selectedVuln) {
          this._setSelectedVuln(selectedVuln)
          this._redraw(drawerEl, infos);
        }
      });
    }

    const hiddenVulnsEl = drawerEl.querySelector('#tbl-hidden-vulns');
    const toggleBtn = drawerEl.querySelector('#toggleHiddenVulns');

    if (hiddenVulnsEl) {
      hiddenVulnsEl.addEventListener('click', event => {

        const tr = event.target.closest('tr');
        const vulnId = tr.getAttribute('vuln-id');
        const selectedVuln = infos.vulnerabilities.find(vuln => vuln.id == vulnId); // force type coercion

        if (selectedVuln) {
          this._setSelectedVuln(selectedVuln)
          this._redraw(drawerEl, infos);
        }
      });
    }

    if (toggleBtn && hiddenVulnsEl) {
      toggleBtn.addEventListener('click', () => {
        hiddenVulnsEl.classList.toggle('d-none');

        toggleBtn.classList.toggle('fa-chevron-right');
        toggleBtn.classList.toggle('fa-chevron-down');
      });
    }

    const form = drawerEl.querySelector('#hideVulnerabilitiesForm');

    if (form) {
      form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const formData = new FormData(form);
        const selectedOptions = formData.getAll('hideOption');
        const submitButton = form.querySelector('button[type="submit"]');
        const loader = submitButton.querySelector('.loader');

        form.querySelectorAll('input, button').forEach(el => el.disabled = true);
        loader.style.display = 'inline-block';

        const existingAlertsMap = {
          uid: infos.hiddenAlerts.some(alert => alert.uid === this.selectedVuln_.uid),
          type: infos.hiddenAlerts.some(alert => alert.type === this.selectedVuln_.fullType),
          title: infos.hiddenAlerts.some(alert => alert.title === this.selectedVuln_.title2),
        };

        const alertsToAdd = selectedOptions.filter(option => !existingAlertsMap[option]);
        const addPromises = alertsToAdd.map(option => {
          const payload = {};
          if (option === 'uid') {
            payload.uid = this.selectedVuln_.uid;
          }
          if (option === 'type') {
            payload.type = this.selectedVuln_.fullType;
          }
          if (option === 'title') {
            payload.title = this.selectedVuln_.title2;
          }
          return this.datastore_.postHiddenAlert(payload).then(response => infos.hiddenAlerts.push(response))
          .catch(error => {
            this.datastore_.toastDanger(error.message);
          })
        });

        const alertsToRemove = Object.keys(existingAlertsMap).filter(
          option => existingAlertsMap[option] && !selectedOptions.includes(option));
        const removePromises = alertsToRemove.map(option => {
          let alertToRemove;
          if (option === 'uid') {
            alertToRemove = infos.hiddenAlerts.find(alert => alert.uid === this.selectedVuln_.uid);
          } else if (option === 'type') {
            alertToRemove = infos.hiddenAlerts.find(alert => alert.type === this.selectedVuln_.fullType);
          } else if (option === 'title') {
            alertToRemove = infos.hiddenAlerts.find(alert => alert.title === this.selectedVuln_.title2);
          }

          if (alertToRemove && alertToRemove.id) {
            return this.datastore_.deleteHiddenAlert(alertToRemove.id).then(
              () => infos.hiddenAlerts = infos.hiddenAlerts.filter(alert => alert.id !== Number(alertToRemove.id)))
            .catch(error => {
              this.datastore_.toastDanger(error.message);
            });
          } else {
            // Handle the case where the alert to remove wasn't found
            console.error(`Alert to remove not found for option: ${option}`);
            return Promise.resolve(); // Return a resolved promise to maintain the array for Promise.all
          }
        });

        Promise.all([...addPromises, ...removePromises]).then(() => {
          this._redraw(drawerEl, infos);
        }).catch(error => {
          this.datastore_.toastDanger(error.message);
        }).finally(() => {
          form.querySelectorAll('input, button').forEach(el => el.disabled = false);
          loader.style.display = 'none';
        });
      });
    }
  }

  _newBody(infos) {

    function compareRiskLevels(a, b) {
      const order = {
        high: 4, 'high (unverified)': 3, medium: 2, low: 1
      };
      return order[b] - order[a];
    }

    const tags = infos.tags.map(tag => `<span class="badge me-2">${tag.toLowerCase()}</span>`).join('');
    const ports = infos.ports;
    const vulnerabilities = infos.vulnerabilities.sort((a, b) => {
      if (a.tested !== b.tested) {
        return b.tested - a.tested;
      }
      if (a.level !== b.level) {
        return compareRiskLevels(a.level.toLowerCase(), b.level.toLowerCase());
      }
      return a.type.localeCompare(b.type);
    });
    const hiddenAlerts = infos.hiddenAlerts.map((alert) => new HiddenAlert(alert));
    return `
            <style>
            .modal {
                display: none;
                position: fixed;
                z-index: 2;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.9);
            }

            .modal-content {
                margin: auto;
                display: block;
                width: 80%;
                max-width: 700px;
                position: relative;
                top: 50%;
                transform: translateY(-50%);
            }

            .close {
                position: absolute;
                top: 10px;
                right: 25px;
                color: #f1f1f1;
                font-size: 35px;
                font-weight: bold;
                transition: 0.3s;
            }

            .close:hover,
            .close:focus {
                color: #bbb;
                text-decoration: none;
                cursor: pointer;
            }
            </style>
            <div id="screenshotModal" class="modal">
                <span class="close">&times;</span>
                <img id="modalImage" class="modal-content" alt="screenshot">
            </div>
            <div class="container-fluid background-dark-grey border-bottom-dark-grey">
                <div class="row">
                  <div class="col pl-3 pr-3 pt-3">
                    <h4 class="mb-0"><strong>${this.asset_}</strong></h4>
                  </div>
                  <div class="col-6 p-3 d-flex align-items-center justify-content-end">
                    ${tags}
                  </div>
                </div>
                <div class="row">
                  <div class="col pb-3">
                    ${infos.modifications.length === 0 ? i18next.t('Découverte automatique') : i18next.t(
      'Ajouté le {{date}} UTC par <a href="mailto:{{user}}">{{user}}</a>', {
        date: infos.modifications[0].timestamp.replace('T', ' ').substring(0,
          infos.modifications[0].timestamp.lastIndexOf(':')), user: infos.modifications[0].user
      })}
                  </div>
                </div>
            </div>
            <div class="container-fluid">
              ${this.selectedVuln_ ? this._newSelectedVuln(ports, hiddenAlerts) : ''}
            </div>
            <div class="container-fluid mb-3 v-scroll">
                ${this._newAlerts(ports, vulnerabilities, hiddenAlerts)}
                ${this._newHiddenAlerts(ports, vulnerabilities, hiddenAlerts)}
                ${this._newOpenPorts(ports)}
                ${this._newTimeline(infos.timeline)}
            </div>
        `;
  }

  _newSelectedVuln(ports, hiddenAlerts) {

    function newBgColor(vuln) {
      const riskLevels = {
        high: 'high', 'high (unverified)': 'high-unverified', medium: 'medium', low: 'low'
      };
      return riskLevels[vuln.level.toLowerCase()] || 'background-light-grey';
    }

    function newTitle(vuln) {
      const subtitle = vuln.cve_id ? `${vuln.cve_id} : ${vuln.title2}` : `${vuln.title2}`;
      return subtitle.replace(/(_alert|_v3_alert)$/, '');
    }

    const isCategoryHidden = (category) => {
      let vulnProperty = category;
      if (category === 'type') {
        vulnProperty = 'fullType';
      }
      if (category === 'title') {
        vulnProperty = 'title2';
      }
      return hiddenAlerts.some(alert => alert[category] === this.selectedVuln_[vulnProperty]);
    }

    const createFormWithCheckboxes = (vuln) => `
            <form id="hideVulnerabilitiesForm" class="d-flex align-items-center">
                <label class="my-auto me-2">
                  <input type="checkbox" name="hideOption" value="uid" ${isCategoryHidden('uid') ? 'checked' : ''}>
                  ${i18next.t('même vulnérabilité pour le même actif')}
                </label>
                <label class="my-auto me-2">
                  <input type="checkbox" name="hideOption" value="type" ${isCategoryHidden('type') ? 'checked' : ''}>
                  ${i18next.t('de même type ({{type}})', {type: vuln.type.replace(/(_alert|_v3_alert)$/, '')})}
                </label>
                <label class="my-auto me-2">
                  <input type="checkbox" name="hideOption" value="title" ${isCategoryHidden('title') ? 'checked' : ''}>
                  ${i18next.t('de même titre ({{title}})', {title: vuln.title2})}
                </label>
                <button type="submit" class="btn btn-outline-primary ms-auto">
                    ${i18next.t('Sauvegarder')}
                    <span class="loader" style="display: none;"><i class="ms-2 fas fa-spinner fa-spin"></i></span>
                </button>
            </form>
        `;

    const formHtml = createFormWithCheckboxes(this.selectedVuln_);

    const port = ports.find(vuln => {
      return vuln.ip === this.selectedVuln_.ip && vuln.port === this.selectedVuln_.port && vuln.protocol
        === this.selectedVuln_.protocol
    });
    const service = port && port.services && port.services.length > 0 ? port.services[0] : '-';
    const product = port && port.products && port.products.length > 0 ? port.products[0] : '-';
    const tags = port && port.tags ? port.tags.sort().map(
      tag => `<span class="badge me-2">${tag.toLowerCase()}</span>`).join('&nbsp;') : [];

    return `
            <div class="row ${newBgColor(this.selectedVuln_)} border-bottom-dark-grey">
                <div class="row">
                  <div class="col p-3">
                    <h5 class="mb-0">
                      <strong>
                        ${newTitle(this.selectedVuln_)}
                      </strong>
                    </h5>
                  </div>
                  <div class="col-1 d-flex align-items-center justify-content-end">
                    <i class="fas fa-times-circle fa-lg cursor-pointer"></i>
                  </div>
                </div>
                <!-- BEGIN HIDDEN ROW : DO NOT REMOVE! -->
                <div class="row mb-2 d-none">
                  <div class="col-2 fw-bold px-2 text-right">ID :</div>
                  <div class="col">${this.selectedVuln_.id}</div>
                </div>
                <!-- END HIDDEN ROW : DO NOT REMOVE! -->
                <div class="row mb-2">
                  <div class="col-2 fw-bold px-2 text-right">${i18next.t('IP')} :</div>
                  <div class="col">${this.selectedVuln_.ip}</div>
                </div>
                <div class="row mb-2">
                  <div class="col-2 fw-bold px-2 text-right">${i18next.t('Port')} :</div>
                  <div class="col">${this.selectedVuln_.port}</div>
                </div>
                <div class="row mb-2">
                  <div class="col-2 fw-bold px-2 text-right">${i18next.t('Service')} :</div>
                  <div class="col">${service}</div>
                </div>
                <div class="row mb-2">
                  <div class="col-2 fw-bold px-2 text-right">${i18next.t('Produit')} :</div>
                  <div class="col">${product}</div>
                </div>
                <div class="row mb-2">
                  <div class="col-2 fw-bold px-2 text-right">${i18next.t('Alerte :')}</div>
                  <div class="col">${this.selectedVuln_.type.replace(/(_alert|_v3_alert)$/, '')}</div>
                </div>
                <div class="row mb-2">
                  <div class="col-2 fw-bold px-2 text-right text-nowrap">${i18next.t('Vulnérabilité :')}</div>
                  <div class="col">${this.selectedVuln_.vulnerability}</div>
                </div>
                <div class="row mb-2">
                  <div class="col-2 fw-bold px-2 text-right">${i18next.t('Comment y remédier ?')}</div>
                  <div class="col">${this.selectedVuln_.remediation}</div>
                </div>
                <div class="row mb-2">
                  <div class="col-2 fw-bold px-2 text-right">${i18next.t('Produits & Services Exposés :')}</div>
                  <div class="col">${tags.length ? tags : '-'}</div>
                </div>
                <div class="row pb-3 last-row">
                   <div class="col-2 fw-bold px-2 text-right my-auto">${i18next.t('Masquer les vulnérabilités')} :</div>
                   <div class="col">${formHtml}</div>
                </div>
            </div>
        `;
  }

  _newOpenPorts(ports) {

    let rows = '';

    if (ports.length === 0) {
      rows = `
                <tr>
                  <td class="text-center" colspan="6">
                    Il n'y a pas d'éléments à afficher.
                  </td>
                </tr>
            `;
    } else {
      rows = ports.map((port, index) => {

        const tags = port.tags.map(tag => `<span class="badge me-2">${tag.toLowerCase()}</span>`).join('');

        return `
                    <tr class="${index % 2 === 0 ? 'row-light' : ''}">
                      <td class="text-truncate" style="width:150px">${port.ip}</td>
                      <td class="text-truncate" style="width:100px">${port.port}</td>
                      <td class="text-truncate" style="width:100px">${port.services[0] ? port.services[0] : '-'}</td>
                      <td class="text-truncate" style="width:100px" title="${port.products[0] ? port.products[0]
          : '-'}">${port.products[0] ? port.products[0] : '-'}</td>
                      <td class="text-truncate">${tags}</td>
                      <td class="text-center" style="width:100px">
                        ${port.screenshotId
          ? `<a href="#"><i class="fal fa-image screenshot-icon" data-screenshot-id="${port.screenshotId}"></i></a>`
          : ''}
                      </td>
                    </tr>
                `;
      }).join('');
    }
    return `
            <div class="row border-top-dark-grey border-bottom-dark-grey background-light-grey mt-3 mb-3">
              <div class="col p-3">
                <h5 class="mb-0">
                  <strong>${i18next.t('Ports ouverts')}&nbsp;(&nbsp;<span class="orange">${ports.length}</span>&nbsp;)</strong>
                </h5>
              </div>
            </div>
            <div class="row">
              <div class="col">
                <table class="table table-fixed mb-0 border-top-0 border">
                  <thead class="fw-bold">
                    <tr>
                        <th style="width:150px">${i18next.t('IP')}</th>
                        <th style="width:100px">${i18next.t('Port')}</th>
                        <th style="width:100px">${i18next.t('Service')}</th>
                        <th style="width:100px">${i18next.t('Produit')}</th>
                        <th>${i18next.t('Tags')}</th>
                        <th width="100px">${i18next.t('Screenshot')}</th>
                    </tr>
                  </thead>
                  <tbody>
                    ${rows}
                  </tbody>
                </table>
              </div>
            </div>
        `;
  }

  _newAlerts(ports, vulnerabilities, hiddenAlerts) {

    let rows = '';

    const displayedVulnerabilities = vulnerabilities.filter(vuln => {
      return !hiddenAlerts.some(hiddenAlert => {
        return vuln.uid === hiddenAlert.uid || vuln.type === hiddenAlert.type || vuln.title === hiddenAlert.title;
      });
    });
    if (displayedVulnerabilities.length === 0) {
      rows = `
                <tr>
                  <td class="text-center" colspan="10">
                    Il n'y a pas d'éléments à afficher.
                  </td>
                </tr>
            `;
    } else {

      function newCriticityCell(vuln) {

        const riskLevels = {
          high: '<span class="dot high"></span>',
          'high (unverified)': '<span class="dot high-unverified"></span>',
          medium: '<span class="dot medium"></span>',
          low: '<span class="dot low"></span>',
        };

        const severity = riskLevels[vuln.level.toLowerCase()] || '';
        const message = vuln.cve_id
          ? `<a href="https://nvd.nist.gov/vuln/detail/${vuln.cve_id}" target="_blank" class="ms-2">${vuln.cve_id}</a> : ${vuln.title}`
          : `&nbsp;&nbsp;${vuln.title}`;

        return `${severity}${message}`;
      }

      function newTestedCell(vuln) {
        return vuln.tested ? `
                  <div class="h-100 d-flex flex-column justify-content-center position-absolute start-0 end-0 top-0" style="background-color:#ffdea17a!important;">
                    <div class="d-flex justify-content-center">
                      <i class="fas fa-swords fa-lg"></i>
                    </div>
                  </div>` : '';
      }

      function newValidatedCell(data) {
        return `
                    <div class="h-100 d-flex flex-column justify-content-center position-absolute start-0 end-0 top-0">
                      <div class="d-flex justify-content-center">
                        ${data.flarum_url && data.flarum_url != ''
          ? `<a href="${data.flarum_url}" target="_blank"><i class="fal fa-user-check fa-lg"></i></a>` : ''}
                      </div>
                    </div>`;
      }

      rows = displayedVulnerabilities.map((vuln, index) => {

        const port = ports.find(
          port => port.ip === vuln.ip && port.port === vuln.port && port.protocol === vuln.protocol);
        const service = port && port.services && port.services.length > 0 ? port.services[0] : '-';
        const product = port && port.products && port.products.length > 0 ? port.products[0] : '-';

        return `
                    <tr class="${index % 2 === 0 ? 'row-light' : ''}" vuln-id="${vuln.id}">
                      <td class="text-truncate" style="width:180px">${vuln.start_date}</td>
                      <td class="text-truncate" style="width:150px">${vuln.ip}</td>
                      <td class="text-truncate" style="width:100px">${vuln.port}</td>
                      <td class="text-truncate" style="width:100px">${service}</td>
                      <td class="text-truncate" style="width:100px" title="${product}">${product}</td>
                      <td class="text-truncate" style="width:100px" title="${vuln.type}">${vuln.type}</td>
                      <td class="text-truncate">${newCriticityCell(vuln)}</td>
                      <td style="width:80px">${newValidatedCell(vuln)}</td>
                      <td style="width:80px">${newTestedCell(vuln)}</td>
                      <td class="position-relative text-center" style="width:80px"><i class="p-1 fal fa-chevron-right" role="button"></i></td>
                    </tr>
                `;
      }).join('');
    }
    return `
            <div class="row border-top-dark-grey border-bottom-dark-grey background-light-grey mb-3">
              <div class="col p-3">
                <h5 class="mb-0">
                  <strong>${i18next.t(
      'Vulnérabilités')}&nbsp;(&nbsp;<span class="orange">${displayedVulnerabilities.length}</span>&nbsp;)</strong>
                </h5>
              </div>
            </div>
            <div class="row">
              <div class="col">
                <table id="tbl-vulns" class="table table-fixed mb-0 border-top-0 border">
                  <thead class="fw-bold">
                    <tr>
                        <th style="width:180px">${i18next.t('Timestamp')} (UTC)</th>
                        <th style="width:150px">${i18next.t('IP')}</th>
                        <th style="width:100px">${i18next.t('Port')}</th>
                        <th style="width:100px">${i18next.t('Service')}</th>
                        <th style="width:100px">${i18next.t('Produit')}</th>
                        <th style="width:100px">${i18next.t('Alerte')}</th>
                        <th>${i18next.t('Criticité')}</th>
                        <th style="width:80px">${i18next.t('Validé')}</th>
                        <th style="width:80px">${i18next.t('Testé')}</th>
                        <th style="width:80px"></th>
                    </tr>
                  </thead>
                  <tbody>
                    ${rows}
                  </tbody>
                </table>
              </div>
            </div>
        `;
  }

  _newHiddenAlerts(ports, vulnerabilities, hiddenAlerts) {

    const displayedVulnerabilities = vulnerabilities.filter(vuln => {
      return hiddenAlerts.some(hiddenAlert => {
        return vuln.uid === hiddenAlert.uid || vuln.type === hiddenAlert.type || vuln.title === hiddenAlert.title;
      });
    });

    let rows = '';

    if (displayedVulnerabilities.length === 0) {
      rows = `
                <tr>
                  <td class="text-center" colspan="10">
                    Il n'y a pas d'éléments à afficher.
                  </td>
                </tr>
            `;
    } else {

      function newCriticityCell(vuln) {

        const riskLevels = {
          high: '<span class="dot high"></span>',
          'high (unverified)': '<span class="dot high-unverified"></span>',
          medium: '<span class="dot medium"></span>',
          low: '<span class="dot low"></span>',
        };

        const severity = riskLevels[vuln.level.toLowerCase()] || '';
        const message = vuln.cve_id
          ? `<a href="https://nvd.nist.gov/vuln/detail/${vuln.cve_id}" target="_blank" class="ms-2">${vuln.cve_id}</a> : ${vuln.title}`
          : `&nbsp;&nbsp;${vuln.title}`;

        return `${severity}${message}`;
      }

      function newTestedCell(vuln) {
        return vuln.tested ? `
                  <div class="h-100 d-flex flex-column justify-content-center position-absolute start-0 end-0 top-0" style="background-color:#ffdea17a!important;">
                    <div class="d-flex justify-content-center">
                      <i class="fas fa-swords fa-lg"></i>
                    </div>
                  </div>` : '';
      }

      function newValidatedCell(data) {
        return `
                    <div class="h-100 d-flex flex-column justify-content-center position-absolute start-0 end-0 top-0">
                      <div class="d-flex justify-content-center">
                        ${data.flarum_url && data.flarum_url != ''
          ? `<a href="${data.flarum_url}" target="_blank"><i class="fal fa-user-check fa-lg"></i></a>` : ''}
                      </div>
                    </div>`;
      }

      rows = displayedVulnerabilities.map((vuln, index) => {

        const port = ports.find(
          port => port.ip === vuln.ip && port.port === vuln.port && port.protocol === vuln.protocol);
        const service = port && port.services && port.services.length > 0 ? port.services[0] : '-';
        const product = port && port.products && port.products.length > 0 ? port.products[0] : '-';

        return `
                    <tr class="${index % 2 === 0 ? 'row-light' : ''}" vuln-id="${vuln.id}">
                      <td class="text-truncate" style="width:180px">${vuln.start_date}</td>
                      <td class="text-truncate" style="width:150px">${vuln.ip}</td>
                      <td class="text-truncate" style="width:100px">${vuln.port}</td>
                      <td class="text-truncate" style="width:100px">${service}</td>
                      <td class="text-truncate" style="width:100px" title="${product}">${product}</td>
                      <td class="text-truncate" style="width:100px" title="${vuln.type}">${vuln.type}</td>
                      <td class="text-truncate">${newCriticityCell(vuln)}</td>
                      <td style="width:80px">${newValidatedCell(vuln)}</td>
                      <td style="width:80px">${newTestedCell(vuln)}</td>
                      <td class="position-relative text-center" style="width:80px"><i class="p-1 fal fa-chevron-right" role="button"></i></td>
                    </tr>
                `;
      }).join('');
    }
    return `
            <div class="row border-top-dark-grey border-bottom-dark-grey background-light-grey mb-3 mt-3">
              <div class="col p-3">
                <h5 class="mb-0">
                    <strong>${i18next.t(
      'Vulnérabilités Cachées')}&nbsp;(&nbsp;<span class="orange">${displayedVulnerabilities.length}</span>&nbsp;)</strong>
                    <i class="fal fa-chevron-right cursor-pointer float-end" id="toggleHiddenVulns"></i>
                </h5>
              </div>
            </div>
            <div class="row">
              <div class="col">
                <table id="tbl-hidden-vulns" class="table table-fixed mb-0 border-top-0 border d-none">
                  <thead class="fw-bold">
                    <tr>
                        <th style="width:180px">${i18next.t('Timestamp')} (UTC)</th>
                        <th style="width:150px">${i18next.t('IP')}</th>
                        <th style="width:100px">${i18next.t('Port')}</th>
                        <th style="width:100px">${i18next.t('Service')}</th>
                        <th style="width:100px">${i18next.t('Produit')}</th>
                        <th style="width:100px">${i18next.t('Alerte')}</th>
                        <th>${i18next.t('Criticité')}</th>
                        <th style="width:80px">${i18next.t('Validé')}</th>
                        <th style="width:80px">${i18next.t('Testé')}</th>
                        <th style="width:80px"></th>
                    </tr>
                  </thead>
                  <tbody>
                    ${rows}
                  </tbody>
                </table>
              </div>
            </div>
        `;
  }

  _newTimeline(timeline) {

    function getCardColor(taskData, isNextScan = false) {
      if (!taskData.start) {
        return 'bg-danger-lightest';
      }
      if (isNextScan) {
        return 'bg-info-lightest';
      }
      if (taskData.end) {
        return 'bg-success-lightest';
      }
      if (taskData.start) {
        return 'bg-warning-lightest';
      }
    }

    function createTimelineItem(taskName, taskData, isNextScan = false) {

      const start = taskData.start ? moment(taskData.start).format('YYYY-MM-DD HH:mm:ss UTC') : '';
      const end = taskData.end ? moment(taskData.end).format('YYYY-MM-DD HH:mm:ss UTC') : i18next.t('en cours...');
      const nextScan = isNextScan && taskData.start ? moment(taskData.start).format('YYYY-MM-DD HH:mm:ss UTC')
        : i18next.t('inconnue');
      const cardColor = getCardColor(taskData, isNextScan);

      return `
                <div class="d-flex flex-column align-items-start">
                    <div class="card w-100 rounded-0 h-100 ${cardColor}">
                        <div class="card-header fw-bold">${taskName}</div>
                        <div class="card-body">
                          <div class="container-fluid">
                            <div class="row ${isNextScan || !taskData.id ? 'd-none' : ''}">
                              <div class="col-3">
                                <span class="fw-bold">${i18next.t('Début')}</span>
                              </div>
                              <div class="col">
                                ${start}
                              </div>
                            </div>
                            <div class="row ${isNextScan || !taskData.id ? 'd-none' : ''}">
                              <div class="col-3">
                                <span class="fw-bold">${i18next.t('Fin')}</span>
                              </div>
                              <div class="col">
                                ${end}
                              </div>
                            </div>
                            <!-- BEGIN HIDDEN ROW : DO NOT REMOVE! -->
                            <div class="row d-none">
                              <div class="col-3">
                                <span class="fw-bold">ID</span>
                              </div>
                              <div class="col">
                                ${taskData.id}
                              </div>
                            </div>
                            <!-- END HIDDEN ROW : DO NOT REMOVE! -->
                            <div class="row ${isNextScan || !taskData.id ? '' : 'd-none'}">
                              <div class="col-3">
                                <span class="fw-bold">${i18next.t('Est. à')}</span>
                              </div>
                              <div class="col">
                                ${nextScan}
                              </div>
                            </div>
                          </div>
                        </div>
                    </div>
                </div>
            `;
    }

    if (!timeline.nmap.start) {
      return '-';
    }
    return `
            <div class="row border-top-dark-grey border-bottom-dark-grey background-light-grey mt-3 mb-3">
              <div class="col p-3">
                <h5 class="mb-0"><strong>${i18next.t('Statut du scan')}</strong></h5>
              </div>
            </div>
            <div class="row">
              <div class="col-4">
                ${createTimelineItem(i18next.t('Recherche de ports ouverts...'), timeline.nmap)}
              </div>
              <div class="col-4">
                ${createTimelineItem(i18next.t('Recherche de vulnérabilités...'), timeline.sentinel)}
              </div>
              <div class="col-4">
                ${createTimelineItem(i18next.t('Prochain scan...'), {start: timeline.next_scan}, true)}
              </div>
            </div>
        `;
  }

  _showModal(src) {
    const modal = document.getElementById('screenshotModal');
    const modalImg = document.getElementById('modalImage');
    modal.style.display = "block";
    modalImg.src = src;
  }

  _setSelectedVuln(vuln) {
    vuln.fullType = vuln.type;
    vuln.title2 = vuln.title;
    this.selectedVuln_ = vuln;
  }
}
