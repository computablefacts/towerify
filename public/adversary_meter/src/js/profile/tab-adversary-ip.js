'use strict'

import {Table} from "../_shared/table.js";

export class TabAdversaryIp extends com.computablefacts.widgets.Widget {

  constructor(container, datastore, name = null, id) {
    super(container)
    this.datastore_ = datastore;
    this.id_ = id;
    this.render();
  }


  /**
   * @override
   */
  _newElement() {

    const template = `
            <div class="d-flex flex-column bg-white flex-grow-1 orange-shadow px-3">
                <div class="d-flex">
                    <h1>${i18next.t('Source IP')}</h1>
                </div>
                <div id="table-profile-ips" class="flex-grow-1 h-0 mb-3"></div>
            </div>
    `

    const tab = document.createElement('div');
    tab.innerHTML = template;
    tab.className = 'flex-grow-1 d-flex flex-column border'

    const columns = [
      document.createTextNode(i18next.t('IP')),
      document.createTextNode(i18next.t('Premier contact')),
      document.createTextNode(i18next.t('Dernier contact')),
      document.createTextNode(i18next.t('Pays')),
      document.createTextNode(i18next.t('Fournisseur'))
    ];
    const alignment = ['left', 'left', 'left', 'left', 'left']
    const table = new Table(tab.querySelector('#table-profile-ips'), columns, alignment,
      {
        main: [
          data => {
            const node = document.createElement('template');
            node.innerHTML = data.ip;
            return node.content.cloneNode(true);
          },
          data =>{
            const node = document.createElement('template');
            node.innerHTML = moment(data.firstContact, "YYYY-MM-DD HH:mm:ss  Z").format('YYYY-MM-DD HH:mm:ss UTC');
            return node.content.cloneNode(true);
          },
          data => {
            const node = document.createElement('template');
            node.innerHTML = moment(data.lastContact, "YYYY-MM-DD HH:mm:ss  Z").format('YYYY-MM-DD HH:mm:ss UTC');
            return node.content.cloneNode(true);
          },
          data => {
            const node = document.createElement('template');
            node.innerHTML = `<div><span class="fi fi-${data.countryCode.toLowerCase()}"></span></div>`;
            return node.content.cloneNode(true)
          },
          data => {
            const node = document.createElement('template');
            node.innerHTML = data.provider;
            return node.content.cloneNode(true)
          },
        ]
      },
      ['medium-cell', 'medium-cell', 'medium-cell', 'small-cell'],
      [
        (a, b) => {
          if (!a.ip || !b.ip) {
            return !a.ip ? 1 : -1;
          }
          return a.ip.localeCompare(b.ip)
        },
        (a, b) => {
          if (!a.firstContact || !b.firstContact) {
            return !a.firstContact ? 1 : -1;
          }
          return moment(a.firstContact, "YYYY-MM-DD HH:mm:ss  Z").diff(moment(b.firstContact, "YYYY-MM-DD HH:mm:ss Z"))
        },
        (a, b) => {
          if (!a.lastContact || !b.lastContact) {
            return !a.lastContact ? 1 : -1;
          }
          return moment(a.lastContact,  "YYYY-MM-DD HH:mm:ss Z").diff(moment(b.lastContact,  "YYYY-MM-DD HH:mm:ss Z"))
        },
        (a, b) => {
          if (!a.countryCode || !b.countryCode) {
            return !a.countryCode ? 1 : -1;
          }
          return a.countryCode.localeCompare(b.countryCode)
        },
        (a, b) => {
          if (!a.provider || !b.provider) {
            return !a.provider ? 1 : -1;
          }
          return a.provider.localeCompare(b.provider)
        },
      ],
      true);
    this.datastore_.getBlacklistIps(this.id_).then(response => table.data = response);
    return tab;
  }

}