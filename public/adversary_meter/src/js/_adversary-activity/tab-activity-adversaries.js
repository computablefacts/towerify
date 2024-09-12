'use strict'

import {Table} from "../_shared/table.js";
import {createNode} from "../helpers.js";

export class TabActivityAdversaries extends com.computablefacts.widgets.Widget {

  constructor(container, datastore) {
    super(container)
    this.observers_ = new com.computablefacts.observers.Subject();
    this.datastore_ = datastore;
    this.render();
  }


  onShowProfile(callback){
    if (callback){
      this.observers_.register('show-profile', (params) => callback(params))
    }
  }

  _handleRowClick(data, table){
    const target = data.target;
    if (target.classList.contains('see-profile')){
      this.observers_.notify('show-profile', {
        name: target.dataset.name,
        id: target.dataset.id
      })
    }
  }

  /**
   * @override
   */
  _newElement() {

    const template = `
      <div class="d-flex justify-content-between">
          <h1>${i18next.t('Activité des attaquants (7 jours glissants)')}</h1>
          <div class="d-flex my-auto"><div class="me-3"id="chk-manual"></div><div id="chk-auto"></div></div>
      </div>
      <div class="pb-3">
        <b>Note.</b> ${i18next.t('La liste des événements est mise à jour toutes les 30 minutes.')}
      </div>
      <div id="table-activities" class="flex-grow-1 h-0 mb-3"></div>
    `;

    const tab = document.createElement('div');
    tab.innerHTML = template;
    tab.className = 'px-3 flex-grow-1 d-flex flex-column'

    const columns = [
      document.createTextNode(i18next.t('Nom interne')),
      document.createTextNode(i18next.t('Date')),
      document.createTextNode(i18next.t("Nature de l'alerte")),
      document.createTextNode(i18next.t('IP')),
      document.createTextNode(i18next.t("Honeypot")),
      document.createTextNode(i18next.t("Detail")),
      document.createTextNode('')
    ];
    const alignment = ['left', 'left', 'left', 'left', 'left', 'left', 'center']
    const table = new Table(tab.querySelector('#table-activities'), columns, alignment,
      {
        main: [
          data => {
            let div = createNode('div', data.internalName.toUpperCase(), 'text-truncate' +
              (data.internalName === '-' ? ' mx-auto' : ''));
            div.title = data.internalName;
            return div;
          },
          data => createNode('div', data.timestamp.replace('+0000', 'UTC'), 'text-truncate'),
          data => createNode('div', data.event, 'text-truncate'),
          data => createNode('div', data.ip, 'text-truncate'),
          data => {
            let div = createNode('div', data.honeypot, 'text-truncate');
            div.title = data.honeypot;
            return div;
          },
          data => {
            let div = createNode('div', data.detail, 'text-truncate');
            div.title = data.detail;
            return div;
          },
          data => data.internalName !== '-' ? createNode('div',`<a href="#" class="see-profile" data-name="${data.internalName}" data-id="${data.attackerId}"> > </a>`, 'text-truncate')
            : createNode('div', ''),
        ]
      },
      ['200', '200', '200', '200', '200', null, '100'],
      [
        (a, b) => {
          if (!a.internalName || !b.internalName) {
            return !a.internalName ? 1 : -1;
          }
          return a.internalName.localeCompare(b.internalName)
        },
        (a, b) => {
          if (!a.timestamp || !b.timestamp) {
            return !a.timestamp ? 1 : -1;
          }
          return moment(a.timestamp, "YYYY-MMM-DD HH:mm:ss +0000").diff(moment(b.timestamp, "YYYY-MMM-DD HH:mm:ss +0000"))
        },
        (a, b) => {
          if (!a.event || !b.event) {
            return !a.event ? 1 : -1;
          }
          return a.event.localeCompare(b.event)
        },
        (a, b) => {
          if (!a.ip || !b.ip) {
            return !a.ip ? 1 : -1;
          }
          return a.ip.localeCompare(b.ip)
        },
        (a, b) => {
          if (!a.honeypot || !b.honeypot) {
            return !a.honeypot ? 1 : -1;
          }
          return a.honeypot.localeCompare(b.honeypot)
        },
        (a, b) => {
          if (!a.detail || !b.detail) {
            return !a.detail ? 1 : -1;
          }
          return a.detail.localeCompare(b.detail)
        },
      ],
      true, 1, 'DESC');

    table.onRowClick((data) => {
      this._handleRowClick(data, table)
    })

    const checkboxAuto = new com.computablefacts.blueprintjs.MinimalCheckbox(tab.querySelector('#chk-auto'),
      true, i18next.t('Automatiques'));
    this.register(checkboxAuto);

    const checkboxManual = new com.computablefacts.blueprintjs.MinimalCheckbox(tab.querySelector('#chk-manual'),
      true,  i18next.t('Manuelles'));
    this.register(checkboxManual);

    this.datastore_.getRecentEvents().then(response => table.data = response)

    checkboxAuto.onSelectionChange(() => {
      table.data = [];
      table.loading = true;
      this.datastore_.getRecentEvents(checkboxAuto.checked, checkboxManual.checked).then(response => table.data = response)
    })
    checkboxManual.onSelectionChange(() => {
      table.data = [];
      table.loading = true;
      this.datastore_.getRecentEvents(checkboxAuto.checked, checkboxManual.checked).then(response => table.data = response)
    })
    return tab;
  }
}
