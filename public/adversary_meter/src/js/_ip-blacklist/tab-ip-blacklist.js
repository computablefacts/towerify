'use strict'

import {Table} from "../_shared/table.js";
import {downloadCsv} from "../helpers.js";
import {createNode} from "../helpers.js";

export class TabIPBlacklist extends com.computablefacts.widgets.Widget {

  constructor(container, datastore) {
    super(container)
    this.datastore_ = datastore;
    this.render();
  }


  /**
   * @override
   */
  _newElement() {

    const template = `
      <style>
          #export-button {
              height: 45px;
          }
      </style>
      <div class="d-flex">
          <h1> ${i18next.t('IP à blacklister')} </h1>
          <button id="export-button" type="button" class="btn btn-primary my-auto ms-auto rounded-0">
            ${i18next.t('Export CSV')} <i class="fal fa-download"></i>
          </button>
      </div>
      <div class="pb-3">
        <b>Note.</b> ${i18next.t('La liste des IP est mise à jour toutes les 30 minutes.')}
      </div>
      <div id="table-activities" class="flex-grow-1 h-0 mb-3"></div>
    `;

    const tab = document.createElement('div');
    tab.innerHTML = template;
    tab.className = 'px-3 flex-grow-1 d-flex flex-column'

    const columns = [
      document.createTextNode(i18next.t('IP')),
      document.createTextNode(i18next.t('Premier contact')),
      document.createTextNode(i18next.t('Dernier contact')),
      document.createTextNode(i18next.t('Pays')),
      document.createTextNode(i18next.t('Fournisseur'))
    ];
    const alignment = ['left', 'left', 'left', 'left', 'left']
    const table = new Table(tab.querySelector('#table-activities'), columns, alignment,
      {
        main: [
          data => {
            let node = createNode('div', data.ip, 'text-truncate');
            node.title = data.ip;
            return node;
          },
          data => createNode('div', moment(data.firstContact, "YYYY-MM-DD HH:mm:ss Z").format('YYYY-MM-DD HH:mm:ss UTC')),
          data => createNode('div', moment(data.lastContact, "YYYY-MM-DD HH:mm:ss Z").format('YYYY-MM-DD HH:mm:ss UTC')),
          data => createNode('div', `<span class="fi fi-${data.countryCode.toLowerCase()}"></span>`),
          data => createNode('div', data.provider),
        ]
      },
      ['200', '200', '200', '100', null],
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
          return moment(a.firstContact, "YYYY-MM-DD HH:mm:ss  Z").diff(moment(b.firstContact, "YYYY-MM-DD HH:mm:ss  Z"))
        },
        (a, b) => {
          if (!a.lastContact || !b.lastContact) {
            return !a.lastContact ? 1 : -1;
          }
          return moment(a.lastContact, "YYYY-MM-DD HH:mm:ss  Z").diff(moment(b.lastContact, "YYYY-MM-DD HH:mm:ss  Z"))
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

    this.datastore_.getBlacklistIps().then(response => table.data = response);

    const exportButton = tab.querySelector('#export-button');
    exportButton.onclick = (() => {
      if (table.data.length) {
        let csv = [[
          i18next.t('IP'),
          i18next.t('Premier contact'),
          i18next.t('Dernier contact'),
          i18next.t('Pays'),
          i18next.t('Fournisseur')
        ]].concat(table.data.map((el) => [el.ip, el.firstContact, el.lastContact, el.countryCode, el.provider]))
        downloadCsv('blacklist.csv', csv)
      }
    })
    return tab;
  }

}
