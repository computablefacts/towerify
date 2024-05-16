'use strict'

import {Table} from "../_shared/table.js";
import {createNode} from "../helpers.js";

export class TabDirectoryAdversaries extends com.computablefacts.widgets.Widget {

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

  onShowBlacklist(callback){
    if (callback){
      this.observers_.register('show-blacklist', (params) => callback(params))
    }
  }

  _handleRowClick(data, table){
    const target = data.target;
    if (target.classList.contains('see-profile')){
      this.observers_.notify('show-profile', {
        name: target.dataset.name,
        id: target.dataset.id
      });
    }
    if (target.classList.contains('see-blacklist')){
      this.observers_.notify('show-blacklist', {
        name: target.dataset.name,
        id: target.dataset.id
      });
    }
  }

  /**
   * @override
   */
  _newElement() {

    const template = `
      <div class="d-flex">
          <h1> ${i18next.t('Annuaire des attaquants')} </h1>
          <div class="d-flex my-auto mx-3">
              <div class="fw-bold">${i18next.t('Risque')} :</div>
              <div>&nbsp;${i18next.t('élevé')}&nbsp;<span class="dot high"></span></div>
              <div class="mx-3">&nbsp;${i18next.t('modéré')}&nbsp;<span class="dot medium"></span></div>
              <div>&nbsp;${i18next.t('faible')}&nbsp;<span class="dot low"></span></div>
          </div>
      </div>
      <div class="pb-3">
        <b>Note.</b> ${i18next.t('La liste des événements est mise à jour toutes les 30 minutes.')}
      </div>
      <div id="table-directories" class="flex-grow-1 h-0 mb-3"></div>
    `;

    const tab = document.createElement('div');
    tab.innerHTML = template;
    tab.className = 'px-3 flex-grow-1 d-flex flex-column'

    const columns = [
      document.createTextNode(i18next.t('Nom interne')),
      document.createTextNode(i18next.t('Premier contact')),
      document.createTextNode(i18next.t('Dernier contact')),
      document.createTextNode(i18next.t('Agressivité')),
      document.createTextNode(i18next.t('IP Connues')),
      document.createTextNode('')
    ];

    const alignment = ['left', 'left', 'left', 'center', 'left', 'center']
    const table = new Table(tab.querySelector('#table-directories'), columns, alignment,
      {
        main: [
          data => {
            let node = createNode('div', data.name.toUpperCase(), 'text-truncate');
            node.title = data.name;
            return node;
          },
          data => createNode('div', moment(data.firstContact, "YYYY-MM-DD HH:mm:ss Z").format('YYYY-MM-DD HH:mm:ss UTC')),
          data => createNode('div',  moment(data.lastContact, "YYYY-MM-DD HH:mm:ss Z").format('YYYY-MM-DD HH:mm:ss UTC')),
          data => {
            let template = null;
            switch (data.level){
              case 'high':
                template = `<span class="dot high"></span>`
                break;
              case 'medium':
                template = `<span class="dot medium"></span>`
                break;
              case 'low':
                template = `<span class="dot low"></span>`
                break;
            }
            return createNode('div', template, 'd-flex justify-content-center')
          },
          data => {
            const ips = data.ips;
            return createNode('div',  ips.slice(0, 4).map((ip) => `<span class="badge me-2">${ip}</span>`).join('') + (ips.length >= 5 ? `<a href="#" class="see-blacklist" data-name="${data.name}" data-id="${data.id}">${i18next.t('voir plus')}</a>` : ''))
          },
          data => createNode('div',`<a href="#" class="see-profile" data-name="${data.name}" data-id="${data.id}"> > </a>` )
        ]
      },
    ['200', '200', '200', '150', null, '100'],
      [
        (a, b) => {
          if (!a.name || !b.name) {
            return !a.name ? 1 : -1;
          }
          return a.name.localeCompare(b.name)
        },
        (a, b) => {
          if (!a.firstContact || !b.firstContact) {
            return !a.firstContact ? 1 : -1;
          }
          return a.firstContact.localeCompare(b.firstContact)
        },
        (a, b) => {
          if (!a.lastContact || !b.lastContact) {
            return !a.lastContact ? 1 : -1;
          }
          return a.lastContact.localeCompare(b.lastContact)
        },
        (a, b) => {
          const order = {
            'high': 3,
            'medium': 2,
            'low': 1
          }
          if (!a.level || !b.level) {
            return null;
          }
          if (order[a.level] > order[b.level]) {
            return 1
          }
          else if (order[a.level] < order[b.level]) {
            return -1
          }
          return 0
        },
      ],
      true);

    table.onRowClick((data) => {
      this._handleRowClick(data, table);
    });

    this.datastore_.getAttackerIndex().then(response => table.data = response)
    return tab;
  }

}
