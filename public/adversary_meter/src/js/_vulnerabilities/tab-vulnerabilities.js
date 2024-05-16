'use strict'

import {Panel} from "../_shared/panel.js";
import {Table} from "../_shared/table.js";
import {createNode} from "../helpers.js";

export class TabVulnerabilities extends com.computablefacts.widgets.Widget {

    constructor(container, datastore, level = null, asset = null) {
        super(container)
        this.datastore_ = datastore;
        this.level_ = level;
        this.asset_ = asset;
        this.vulnerabilities_ = [];
        this.render();
    }

    createRiskTemplate(level, cve, title) {
        const riskLevels = {
            high: '<span class="dot high"></span>',
            'high (unverified)': '<span class="dot high-unverified"></span>',
            medium: '<span class="dot medium"></span>',
            low: '<span class="dot low"></span>',
        };
        return `${riskLevels[level] || ''}${cve
            ? `<a href="https://nvd.nist.gov/vuln/detail/${cve}" target="_blank" class="ms-2">${cve}</a> : ${title}`
            : `&nbsp;&nbsp;${title}`}`;
    }

    createTestedTemplate() {
        return `
          <div class="h-100 d-flex flex-column justify-content-center position-absolute start-0 end-0 top-0" style="background-color: #ffdea17a!important;">
            <div class="d-flex justify-content-center">
              <i class="fas fa-swords fa-lg"></i>
            </div>
          </div>`;
    }

    createValidatedTemplate(data) {
        return `
            <div class="h-100 d-flex flex-column justify-content-center position-absolute start-0 end-0 top-0">
              <div class="d-flex justify-content-center">
                ${data.flarum_url && data.flarum_url != ''
            ? `<a href="${data.flarum_url}" target="_blank"><i class="fal fa-user-check fa-lg"></i></a>` : ''}
              </div>
            </div>`;
    }

    compareRiskLevels(a, b) {
        const order = {
            high: 4, 'high (unverified)': 3, medium: 2, low: 1
        };
        return order[b] - order[a];
    }

    /**
     * @override
     */
    _newElement() {

        function refreshVulnerabilities(table) {
            this.datastore_.getVulnerabilities().then((response) => {

                let data = response;
                select.disabled = false;
                select.items = [...new Set(data.flatMap((vuln) => vuln.tags.map(tag => tag.toLowerCase())))].sort();

                if (this.level_) {
                    data = data.filter(el => el.level === this.level_);
                }
                if (this.asset_) {
                    data = data.filter(el => el.asset === this.asset_);
                }

                // Sort data based on tested, criticality, and vulnerability type
                data.sort((a, b) => {
                    if (a.tested !== b.tested) {
                        return b.tested - a.tested;
                    }
                    if (a.level !== b.level) {
                        return this.compareRiskLevels(a.level, b.level);
                    }
                    return a.type.localeCompare(b.type);
                });

                // Add priority numbering
                data.forEach((el, index) => {
                    el.priority = index + 1;
                });

                this.vulnerabilities_ = data;
                table.data = data;
            });

        }

        const template = `
          <style>
           .legend-container .dot {
                cursor:pointer;
           }
            table {
                table-layout: fixed;
            }
            #search-input {
                width: 350px;
            }
            #tags-multiselect {
                width: 260px;
            }
          </style>
          <div class="d-flex">
            <h1>${i18next.t('Mes vulnérabilités')}</h1>
            <div class="d-flex my-auto mx-3 legend-container">
              <div class="fw-bold">${i18next.t('Risque :')}</div>
              <div>&nbsp;${i18next.t('élevé')}&nbsp;<span id="high" class="dot high"></span></div>
              <div class="ms-3">&nbsp;${i18next.t('élevé à vérifier')}&nbsp;<span id="high (unverified)" class="dot high-unverified"></span></div>
              <div class="mx-3">&nbsp;${i18next.t('modéré')}&nbsp;<span id="medium" class="dot medium"></span></div>
              <div class="me-3">&nbsp;${i18next.t('faible')}&nbsp;<span id="low" class="dot low"></span></div>
              <div class="fw-bold">${i18next.t('Testé par un attaquant :')}</div>
              <div>&nbsp;<i class="fas fa-swords"></i></div>
            </div>
            <div id="tags-multiselect" class="ms-auto my-auto me-1"></div>
            <div class="bp4-input-group me-1 my-auto">
                <span class="bp4-icon-standard bp4-icon bp4-icon-search"></span>
                <input id="search-input" class="bp4-input my-auto" type="text" placeholder="${i18next.t(
            'Rechercher un actif ou un type de vulnérabilité...')}">
            </div>
          </div>
          <div class="pb-3">
            <b>Note.</b> ${i18next.t('La liste des vulnérabilités est mise à jour toutes les 15 minutes.')}
          </div>
          <div id="table-vulnerabilities" class="flex-grow-1 h-0 mb-3"></div>
        `;

        const tab = createNode('div', template, 'px-3 flex-grow-1 d-flex flex-column');
        const columns = [document.createTextNode(i18next.t('Priorité')), document.createTextNode(i18next.t('Actif')),
            document.createTextNode(i18next.t('IP')), document.createTextNode(i18next.t('Port')),
            document.createTextNode(i18next.t('Service')), document.createTextNode(i18next.t('Produit')),
            document.createTextNode(i18next.t('Type')), document.createTextNode(i18next.t('Criticité')),
            document.createTextNode(i18next.t('Validé')), document.createTextNode(i18next.t('Testé')),
            document.createTextNode('')];
        const alignment = ['center', 'left', 'left', 'left', 'left', 'left', 'left', 'left', 'left', 'left', 'center'];
        const width = ['100', '300', '150', '100', '100', '100', '200', null, '80', '80', '70'];
        const table = new Table(tab.querySelector('#table-vulnerabilities'), columns, alignment, {
            txtColor: (data, defaultColor) => data && data.is_valid ? defaultColor : '#a8a6a6', main: [data => {
                if (data.is_valid) {
                    return createNode('div', data.priority)
                } else {
                    return createNode('div', `<i class="p-1 fal fa-hourglass" title="${i18next.t('Scannage...')}"></i>`)
                }
            }, data => {
                const node = createNode('div', data.asset, 'text-truncate');
                node.title = data.asset
                return node;
            }, data => createNode('div', `${data.ip}<span class="ms-2 fi fi-${data.country_code}"></span>`),
                data => createNode('div', data.port, 'text-truncate'),
                data => createNode('div', data.service, 'text-truncate'), data => {
                    const node = createNode('div', data.product, 'text-truncate');
                    node.title = data.product
                    return node;
                }, data => {
                    const node = createNode('div', data.type, 'text-truncate');
                    node.title = data.type
                    node.style.flex = "1 1 auto"
                    node.style.minWidth = "0";
                    return node;
                }, data => createNode('div', this.createRiskTemplate(data.level, data.cve_id, data.title2),
                    'text-truncate'), data => createNode('div', this.createValidatedTemplate(data), 'text-truncate'),
                data => createNode('div', data.tested ? this.createTestedTemplate() : '', 'text-truncate'),
                () => createNode('div', '<i class="p-1 fal fa-chevron-right" role="button"></i>&nbsp;&nbsp;')]
        }, width, [(a, b) => {
            if (!a.priority || !b.priority) {
                return !a.priority ? 1 : -1;
            }
            return a.priority - b.priority
        }, (a, b) => {
            if (!a.asset || !b.asset) {
                return !a.asset ? 1 : -1;
            }
            return a.asset.localeCompare(b.asset)
        }, (a, b) => {
            if (!a.ip || !b.ip) {
                return !a.ip ? 1 : -1;
            }
            return a.ip.localeCompare(b.ip)
        }, (a, b) => {
            if (!a.port || !b.port) {
                return !a.port ? 1 : -1;
            }
            return a.port - b.port
        }, (a, b) => {
            if (!a.service || !b.service) {
                return !a.service ? 1 : -1;
            }
            return a.service.localeCompare(b.service)
        }, (a, b) => {
            if (!a.product || !b.product) {
                return !a.product ? 1 : -1;
            }
            return a.product.localeCompare(b.product)
        }, (a, b) => {
            if (!a.type || !b.type) {
                return !a.type ? 1 : -1;
            }
            return a.type.localeCompare(b.type)
        }, (a, b) => {
            if (!a.level || !b.level) {
                return !a.level ? 1 : -1;
            }
            return this.compareRiskLevels(a.level, b.level);
        }, null, (a, b) => {
            if (!a.tested || !b.tested) {
                return !a.tested ? 1 : -1;
            }
            return a.tested - b.tested
        },], true, 0, 'ASC');

        table.onRowClick((data) => {

            const target = data.target

            if (target.classList.contains('fa-chevron-right')) {
                const asset = data.value.asset;
                const vulnerabilities = this.vulnerabilities_.filter(vuln => vuln.asset === asset);
                const panel = new Panel(document.getElementById('panel'), this.datastore_, asset, data.value);
                panel.onClose(() => {
                    table.loading = true;
                    refreshVulnerabilities.bind(this)(table)
                })
            }

        });

        const select = new com.computablefacts.blueprintjs.MinimalMultiSelect(tab.querySelector('#tags-multiselect'),
            (tag) => tag, () => null, (tag) => tag, null)
        select.defaultText = i18next.t('Filtrer par une ou plusieurs étiquettes...');
        select.disabled = true;

        refreshVulnerabilities.bind(this)(table)

        const searchInput = tab.querySelector('#search-input');
        const debounce = com.computablefacts.helpers.debounceLast;

        searchInput.addEventListener('input', debounce(() => {
            const filter = searchInput.value.toLowerCase();
            table.data = this.vulnerabilities_.filter(item => {
                const assetMatch = item.asset.toLowerCase().includes(filter);
                const ipMatch = item.ip.toLowerCase().includes(filter);
                const vulnerabilityMatch = item.type.toLowerCase().includes(filter);
                return assetMatch || ipMatch || vulnerabilityMatch;
            });
        }));

        select.onSelectionChange((selection) => {
            table.data = this.vulnerabilities_.filter(vuln => {
                return selection.every(tag => vuln.tags.includes(tag));
            });
        })

        const legendContainer = tab.querySelector(".legend-container");
        const filters = {"high": false, "high (unverified)": false, "medium": false, "low": false};

        legendContainer.addEventListener('click', (e) => {

            const dot = e.target;

            if (dot.classList.contains("dot")) {

                const riskLevel = dot.id;
                filters[riskLevel] = !filters[riskLevel];

                for (let level in filters) {
                    if (level !== riskLevel) {
                        filters[level] = false;
                    }
                }

                table.data = filters[riskLevel] ? this.vulnerabilities_.filter(vuln => vuln.level === riskLevel)
                    : this.vulnerabilities_;
            }
        });
        return tab;
    }

}
