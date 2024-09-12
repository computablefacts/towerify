'use strict'

import {createNode} from "../helpers.js";
import {Table} from "../_shared/table.js";

export class TabHoneypots extends com.computablefacts.widgets.Widget {

  constructor(container, datastore, user, status, honeypots = []) {
    super(container)
    this.observers_ = new com.computablefacts.observers.Subject();
    this.datastore_ = datastore;
    this.status_ = status;
    this.user = user;
    this.tldRegex_ = '^(?!-)[A-Za-z0-9-]+(\\.[A-Za-z0-9-]+)*\\.[A-Za-z]{2,}$';
    this.honeypots_ = honeypots;
    this.selects_ = [];
    this.render()
  }

  onCancelConfiguration(callback) {
    if (callback) {
      this.observers_.register('cancel-configuration', (params) => callback(params))
    }
  }

  _handleEvents(container) {

    const btnAddHoneypots = container.querySelector('#add-honeypots');

    if (btnAddHoneypots) {
      btnAddHoneypots.disabled = true;
    }

    container.addEventListener('input', (event) => {
      if (event.target.type === 'text') {
        const inputs = container.querySelectorAll('input[type="text"]');
        const hasInput = Array.from(inputs).some(input => input.value.trim() !== '');
        btnAddHoneypots.disabled = !hasInput;
      }
    });

    container.addEventListener('blur', function (e) {
      if (e.target && e.target.matches('input[type="text"]')) {
        const inputValue = e.target.value.trim();
        if (inputValue.length > 0 && !e.target.checkValidity()) {
          e.target.classList.add('is-invalid');
        } else {
          e.target.classList.remove('is-invalid');
        }
      }
    }, true)

    container.addEventListener('click', (event) => {
      if (event.target.id === 'add-honeypots') {

        event.preventDefault();

        const honeypot1Input = document.getElementById('honeypot1-input');
        const honeypot2Input = document.getElementById('honeypot2-input');
        const honeypot3Input = document.getElementById('honeypot3-input');

        const inputs = [honeypot1Input, honeypot2Input, honeypot3Input];

        for (let i = 0; i < inputs.length; i++) {

          const inputValue = inputs[i].value.trim();

          if (inputValue.length > 0 && !inputs[i].checkValidity()) {
            inputs[i].classList.add('is-invalid');
            return;
          }
          inputs[i].classList.remove('is-invalid');
        }

        const honeypots = inputs.map((input, index) => {
          if (!input.disabled && input.value.trim()) {
            return {
              dns: input.value.trim(),
              cloud_provider: 'aws',
              sensor: this.selects_[index].selectedItem.label
            };
          }
          return null
        }).filter(Boolean);

        if (honeypots.length > 0) {

          const loader = event.target.querySelector('#loader');
          loader.classList.remove('d-none');

          this.datastore_.postHoneypots(honeypots).then((honeypotsToConfig) => {
            this.honeypots_ = honeypotsToConfig.filter((h) => h.status === 'dns_setup');
            this.status_ = 'dns_setup';
            const statusContainer = container.querySelector('#status-container');
            statusContainer.innerHTML = this._writeTemplate();
          }).catch(error => {
            this.datastore_.toastDanger(error.message);
          });
        }
      }
      if (event.target.id === 'end-config') {
        this.datastore_.setHoneypotsNextStep().then(() => {
          this.status_ = 'honeypot_setup';
          const statusContainer = container.querySelector('#status-container');
          statusContainer.innerHTML = this._writeTemplate();
        });
      }
      if (event.target.id === 'ignore-config') {

        const btnIgnoreConfig = container.querySelector('#ignore-config');

        if (btnIgnoreConfig) {
          btnIgnoreConfig.disabled = true;
        }

        const loader = event.target.querySelector('#ignore-loader');
        loader.classList.remove('d-none');

        this.observers_.notify('cancel-configuration');
      }
    });
  }

  _writeTemplate() {

    let template = ``;

    if (this.selects_.length) {
      this.selects_.forEach((select) => select.destroy())
    }
    if (this.status_ === 'inactive') {
      template = `
      <h3 class="mt-4">${i18next.t('1 - Selectionnez un fournisseur de Cloud')}</h3>
      <div>${i18next.t('Nous utiliserons ce fournisseur pour déployer vos honeypots.')}</div>
      <div class="radio-group d-flex mt-4" name="cloud-provider">
        <label for="azure" class="d-flex flex-column my-2">
            <div class="fw-bold">Microsoft Azure Cloud</div>
            <div class="logo-container border">
                <img src="./img/logo-microsoft-cloud-azure.png" alt="Azure logo">
            </div>
            <input type="radio" id="azure" name="cloud-provider" value="azure" class="form-check-input" disabled="true">
        </label>
        <label for="amazon" class="d-flex flex-column m-2">
            <div class="fw-bold">Amazon Web Services</div>
            <div class="logo-container border">
                <img src="./img/aws-logo-png-4.png" alt="Amazon logo">
            </div>
            <input type="radio" id="amazon" name="cloud-provider" value="aws" class="form-check-input" checked>
        </label>
        <label for="google" class="d-flex flex-column my-2">
            <div class="fw-bold">Google Cloud Platform</div>
            <div class="logo-container border">
                <img src="./img/Official-Google-Cloud.png" alt="Google logo">
            </div>
            <input type="radio" id="google" name="cloud-provider" value="gcp" class="form-check-input" disabled="true">
        </label>
      </div>
        <h3 class="mt-4">${i18next.t('2 - Choisissez des sous-domaines')}</h3>
        <div>${i18next.t('Saisissez trois noms de sous-domaines vous appartenant MAIS QUE VOUS N\'UTILISEZ PAS. Nous utiliserons ceux-ci pour alimenter vos honeypots.')}</div>      <form class="mt-4" id="tld-form" class="needs-validation" novalidate>
        <div class="d-flex mb-2 mt-3">
            <div class="mb-1 flex-grow-1">
                <input id="honeypot1-input" class="bp4-input w-100" type="text"
                placeholder="ex: dev.lenomdemonsite.com"
                pattern="${this.tldRegex_}" required
                ${this.honeypots_.find((h) => h.cloud_sensor === 'HTTP') ? `value="${this.honeypots_.find((h) => h.cloud_sensor === 'HTTP').dns}" disabled` : ''}>
                <div class="invalid-feedback">
                    ${i18next.t('Veuillez fournir un sous-domaine valide.')}
                </div>
                <div id="honeypot1-select" class="mt-1"></div>
            </div>
            <div class="mb-1 mx-2 flex-grow-1">
                <input id="honeypot2-input" class="bp4-input w-100" type="text"
                placeholder="ex: staging.lenomdemonsite.com"
                pattern="${this.tldRegex_}" required
                ${this.honeypots_.find((h) => h.cloud_sensor === 'HTTPS') ? `value="${this.honeypots_.find((h) => h.cloud_sensor === 'HTTPS').dns}" disabled` : ''}>
                <div class="invalid-feedback">
                    ${i18next.t('Veuillez fournir un sous-domaine valide.')}
                </div>
                <div id="honeypot2-select" class="mt-1"></div>
            </div>
            <div class="mb-1 flex-grow-1">
                <input id="honeypot3-input" class="bp4-input w-100" type="text"
                placeholder="ex: backup.lenomdemonsite.com"
                pattern="${this.tldRegex_}" required
                ${this.honeypots_.find((h) => h.cloud_sensor === 'SSH') ? `value="${this.honeypots_.find((h) => h.cloud_sensor === 'SSH').dns}" disabled` : ''}>
                <div class="invalid-feedback">
                    ${i18next.t('Veuillez fournir un sous-domaine valide.')}
                </div>
                <div id="honeypot3-select" class="mt-1"></div>
            </div>
        </div>
        <button id="add-honeypots" type="submit" class="btn btn-primary float-end rounded-0 mb-1">
          ${i18next.t('Etape suivante')} > <i id="loader" class="ms-2 d-none fas fa-spinner fa-spin"></i>
        </button>
        <div>
          <button id="ignore-config" type="button" class="btn btn-danger rounded-0 mb-1 w-100 mt-4">
            ${i18next.t('Ignorer la configuration des honeypots')} <i id="ignore-loader" class="ms-2 d-none fas fa-spinner fa-spin"></i>
          </button>
        </div>
      </form>`;
    } else if (this.status_ === "dns_setup") {
      template = `
        <div class="d-flex light-orange p-4 border-orange mt-4">
          <div class="my-auto mx-2">
            <i class="fal fa-sync fa-2x float-end"></i>
          </div>
          <div>
            <div class="fw-bold">${i18next.t('Statut de la configuration :')}</div>
            <div>${i18next.t('En attente de la configuration de vos DNS.')}</div>
          </div>
        </div>
        <div class="my-2 d-flex flex-column flex-grow-1">
          <div class="fw-bold px-2 my-2">${i18next.t('Routez chacun de vos DNS vers l\'adresse IP y étant associée :')}</div>
          <div class="flex-grow-1 overflow-auto" id="dns-table"></div>
        </div>
        <div class="my-2 text-right flex-grow-1">
          <a href="#" class="orange fw-bold" id="end-config">${i18next.t('terminer >')}</a>
        </div>`;

      setTimeout(() => {

        let columns = [
          document.createTextNode(i18next.t('Type')),
          document.createTextNode(i18next.t('Nom')),
          document.createTextNode(i18next.t('Valeur'))
        ];

        let alignment = ['left', 'left', 'left'];

        const dnsData = this.honeypots_.map(honeypot => {

          let type, value;

          switch (honeypot.cloud_sensor) {
            case 'HTTP':
            case 'HTTPS':
              type = 'CNAME';
              value = conf.ENV === 'PROD' ? 'alb-5c070138-c1313ac07306b031.elb.eu-west-1.amazonaws.com' : 'alb-4b832d6a-46c631bd88485556.elb.eu-west-3.amazonaws.com';
              break;

            case 'SSH':
              type = 'A';
              value = conf.ENV === 'PROD' ? '34.240.33.0' : '15.236.181.255';
              break;

            default:
              break;
          }
          return {type, nom: honeypot.dns, value: value};
        });

        const table = new Table(this.container.querySelector('#dns-table'), columns, alignment,
          {
            main: [
              data => {
                let node = createNode('div', data.type, 'text-truncate');
                node.title = data.type;
                return node;
              },
              data => createNode('div', data.nom),
              data => createNode('div', data.value),
            ]
          },
          [100, 100, 100],
          [
            (a, b) => {
              if (!a.type || !b.type) {
                return !a.type ? 1 : -1;
              }
              return a.type.localeCompare(b.type)
            },
            (a, b) => {
              if (!a.nom || !b.nom) {
                return !a.nom ? 1 : -1;
              }
              return a.nom.localeCompare(b.nom)
            },
            (a, b) => {
              if (!a.valeur || !b.valeur) {
                return !a.valeur ? 1 : -1;
              }
              return a.valeur.localeCompare(b.valeur)
            },
          ],
          true, 0, 'ASC', false);

        table.data = dnsData;
      }, 2000); // 2000 milliseconds = 2 seconds

    } else if (this.status_ === "honeypot_setup") {
      template = `
        <div class="d-flex light-orange p-4 border-orange mt-4">
          <div class="my-auto mx-2">
            <i class="fal fa-sync fa-2x float-end"></i>
          </div>
          <div>
              <div class="fw-bold">${i18next.t('Statut de la configuration:')}</div>
              <div>${i18next.t('Nous sommes en train de configurer vos honeypots.')}</div>
          </div>
        </div>
        <div class="my-2">
          <div class="fw-bold px-2">
            ${i18next.t('ComputableFacts s\'occupe actuellement de la configuration de vos honeypots.')}
          </div>
          <div class="px-2 my-2">
            ${i18next.t('Nous vous tiendrons au courant dès que ceux-ci seront actifs.')}
          </div>
          <button id="ignore-config" type="button" class="btn btn-primary rounded-0 mb-1 w-100 mt-4">
            ${i18next.t('Accéder au tableau de bord')} <i id="ignore-loader" class="ms-2 d-none fas fa-spinner fa-spin"></i>
          </button>
        </div>`;
    } else if (this.status === "setup_complete") {
      template = `
        <div class="d-flex light-green p-4 border-green mt-4">
          <div class="my-auto mx-2">
            <i class="fal fa-check fa-2x float-end"></i>
          </div>
          <div>
            <div class="fw-bold">${i18next.t('Status HoneyPot:')}</div>
            <div> ${i18next.t('Votre HoneyPot est maintenant opérationnel.')}</div>
          </div>
        </div>
        <div class="my-2">
          <div class="fw-bold px-2">
            ${i18next.t('La configuration de votre HoneyPot est terminée.')}
          </div>
          <div class="px-2 my-2">
            ${i18next.t('Vous pouvez dès maintenant accéder à votre tableau de bord.')}
          </div>
          <button id="add-honeypots" type="button" class="btn btn-primary float-end rounded-0 mb-1">
            ${i18next.t('Accéder au tableau de bord')}
          </button>
        </div>`;
    }
    return template;
  }


  _newElement() {
    const headerTemplate = `
        <style>
         #honeypots-creation {
            background-image: url('./img/hive-orange.png');
            background-repeat: no-repeat;
            background-position: top calc(100% + 76px) left calc(100% + 72px);
        }
        .logo-container {
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
            background-color: white;
            width: 250px;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo-container > img {
            width: 200px;
        }
        .radio-group > label {
            align-items: center;
        }
        .object-fit-contain{
            object-fit: contain;
        }
        </style>
        <div id="honeypots-creation" class="px-3 my-2 d-flex flex-grow-1 bg-very-light-orange black-shadow flex-column align-items-center">
            <div class="orange mt-4">
              <div class="d-flex flex-row align-items-center">
                <img class="img-fluid object-fit-contain me-2" src="./img/emoji_nature.png" alt="bee">
                <h3 class="m-0 fw-bold">Bienvenue!</h3>
              </div>
            </div>
            <div id="status-container" class="flex-grow-1 d-flex flex-column"></div>
        </div>
        `;

    const tab = createNode('div', headerTemplate, 'flex-grow-1 d-flex flex-column')

    const statusContainer = tab.querySelector('#status-container');
    statusContainer.innerHTML = this._writeTemplate();

    if (this.status_ === 'inactive') {
      for (let i = 0; i < 3; i++) {
        const selectContainer = statusContainer.querySelector(`#honeypot${i + 1}-select`);
        const select = new com.computablefacts.blueprintjs.MinimalSelect(
          selectContainer,
          (item) => item.label
        );

        select.filterable = false;
        select.items = [
          {
            id: 1,
            label: 'HTTP'
          },
          {
            id: 2,
            label: 'HTTPS'
          },
          {
            id: 3,
            label: 'SSH'
          }
        ];
        select.defaultText = 'Sélectionner un capteur...';
        select.selectedItem = select.items[i];
        select.disabled = true;

        this.selects_.push(select);
      }
    }

    this._handleEvents(tab);

    return tab;
  }

}
