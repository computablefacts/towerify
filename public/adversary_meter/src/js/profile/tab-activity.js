'use strict'

import {Table} from "../_shared/table.js";

export class TabActivity extends com.computablefacts.widgets.Widget {

    constructor(container, datastore, name, id) {
        super(container)
        this.datastore_ = datastore;
        this.name_ = name;
        this.id_ = id;
        this.render();
    }

    _injectFilter(container){

        const filters = [
            {
                name: i18next.t('Humain'),
                value: 'human'
            },
            {
                name: i18next.t('Ciblé'),
                value: 'targeted'
            },
            {
                name: i18next.t('CVE'),
                value: 'cve_tested'
            }
        ];
        const select = new com.computablefacts.blueprintjs.MinimalMultiSelect(
          container, (tag) => tag.name, () => null, (tag) => tag.name)
        select.defaultText = i18next.t('Filtrer...');
        select.items = filters;
        select.disabled = true;
        this.register(select)
        return select;
    }

    /**
     * @override
     */
    _newElement() {

        const template = `
             <style>
                .summary-row{
                    height: 50px;
                }
                #filter-activity {
                    width: 350px;
                }
            </style>
             <div class="d-flex bg-white orange-shadow  justify-content-between px-3 mb-3 border summary-row">
                <div class="my-auto">${i18next.t('Profile connu depuis le')}&nbsp;<span id="profile-date">-</span></div>
                <div class="d-flex" id="profile-top-3">
                </div>
            </div>
            <div class="d-flex flex-column bg-white flex-grow-1 orange-shadow px-3 border">
               <div class="d-flex justify-content-between">
                <h1> ${i18next.t("Activité de l'attaquant")} ${this.name_.toUpperCase()}</h1>
                <div id="filter-activity" class="my-auto"></div>
               </div>
               <div id="table-profile-activity" class="flex-grow-1 h-0 mb-3"></div>
            </div>
    `

        const tab = document.createElement('div');
        tab.innerHTML = template;
        tab.className = 'flex-grow-1 d-flex flex-column'
        const dateEl = tab.querySelector('#profile-date');
        const top3El = tab.querySelector('#profile-top-3');

        const columns = [
            document.createTextNode(i18next.t('Date')),
            document.createTextNode(i18next.t("Nature de l'alerte")),
            document.createTextNode(i18next.t('IP')),
            document.createTextNode(i18next.t("Honeypot")),
            document.createTextNode(i18next.t("Détail"))
        ];
        const alignment = ['left', 'left', 'left', 'left', 'left', 'center']
        const table = new Table(tab.querySelector('#table-profile-activity'), columns, alignment, {
            main: [data => {
                const node = document.createElement('template');
                node.innerHTML = data.timestampFormatted;
                return node.content.cloneNode(true);
            }, data => {
                const node = document.createElement('template');
                node.innerHTML = data.event;
                return node.content.cloneNode(true);
            }, data => {
                const node = document.createElement('template');
                node.innerHTML = data.ip;
                return node.content.cloneNode(true)
            }, data => {
                const node = document.createElement('template');
                node.innerHTML = data.honeypot;
                return node.content.cloneNode(true)
            }, data => {
                const node = document.createElement('template');
                node.innerHTML = data.detail;
                return node.content.cloneNode(true)
            },]
        }, ['200', '200', '200', '200', null], [(a, b) => {
            if (!a.timestamp || !b.timestamp) {
                return !a.timestamp ? 1 : -1;
            }
            return moment(a.timestamp, "YYYY-MMM-DD HH:mm:ss +0000").diff(moment(b.timestamp, "YYYY-MMM-DD HH:mm:ss +0000"))
        }, (a, b) => {
            if (!a.event || !b.event) {
                return !a.event ? 1 : -1;
            }
            return a.event.localeCompare(b.event)
        }, (a, b) => {
            if (!a.ip || !b.ip) {
                return !a.ip ? 1 : -1;
            }
            return a.ip.localeCompare(b.ip)
        }, (a, b) => {
            if (!a.honeypot || !b.honeypot) {
                return !a.honeypot ? 1 : -1;
            }
            return a.honeypot.localeCompare(b.honeypot)
        }, (a, b) => {
            if (!a.detail || !b.detail) {
                return !a.detail ? 1 : -1;
            }
            return a.detail.localeCompare(b.detail)
        },], true, 0, 'DESC');

        const filter = this._injectFilter(tab.querySelector('#filter-activity'));
        let originalData = [];

        this.datastore_.getAttackerActivity(this.id_).then(response => {
            filter.disabled = false;
            table.data = response.events;
            originalData = response.events;
            dateEl.innerText = response.firstDate.replace('T', ' ').substring(0, response.firstDate.lastIndexOf(':')) + ' UTC';
            top3El.innerHTML = response.top3.map((eventType, index) => {
                return `<div class="me-2 my-auto d-flex"><div class="fw-bold my-auto">${eventType.count} :&nbsp;</div><div class="my-auto"> ${eventType.type}</div></div>`;
            }).join('');
        })

        filter.onSelectionChange((selection) => {
            const values = selection.map(item => item.value);
            table.data = selection.length === 0
              ? originalData
              : originalData.filter(row =>
                (values.includes('human') && row.human) ||
                (values.includes('targeted') && row.targeted) ||
                values.includes(row.event)
              );
        });


        return tab;
    }

}
