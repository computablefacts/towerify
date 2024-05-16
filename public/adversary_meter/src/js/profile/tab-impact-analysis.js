'use strict'

import {Panel} from "../_shared/panel.js";
import {Table} from "../_shared/table.js";
import {createNode} from "../helpers.js";

export class TabImpactAnalysis extends com.computablefacts.widgets.Widget {

    constructor(container, datastore, name = null, id = null) {
        super(container)
        this.datastore_ = datastore;
        this.vulnerabilities_ = [];
        this.name_ = name;
        this.id_ = id;
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

        const template = `
            <style>
                .legend-container .dot {
                    cursor:pointer;
                }
            </style>
            <div class="d-flex flex-column bg-white flex-grow-1 orange-shadow px-3">
                <div class="d-flex">
                    <h1>${i18next.t('Analyse d\'impact')}</h1>
                    <div class="d-flex my-auto mx-3 legend-container">
                        <div class="fw-bold">${i18next.t('Risque')} :</div>
                        <div>&nbsp;${i18next.t('élevé')}&nbsp;<span id="high" class="dot high"></span></div>
                        <div class="ms-3">&nbsp;${i18next.t('élevé à vérifier')}&nbsp;<span id="high (unverified)" class="dot high-unverified"></span></div>
                        <div class="mx-3">&nbsp;${i18next.t('modéré')}&nbsp;<span id="medium" class="dot medium"></span></div>
                        <div class="me-3">&nbsp;${i18next.t('faible')}&nbsp;<span id="low" class="dot low"></span></div>
                    </div>
                </div>
                <div id="table-profile-impact" class="flex-grow-1 h-0 mb-3"></div>
            </div>
        `;

        const tab = document.createElement('div');
        tab.innerHTML = template;
        tab.className = 'flex-grow-1 d-flex flex-column border'

        const columns = [document.createTextNode(i18next.t('Priorité')), document.createTextNode(i18next.t('Actif')),
            document.createTextNode(i18next.t('IP')), document.createTextNode(i18next.t('Port')),
            document.createTextNode(i18next.t('Service')), document.createTextNode(i18next.t('Produit')),
            document.createTextNode(i18next.t('Type')), document.createTextNode(i18next.t('Criticité')),
            document.createTextNode('')];

        const alignment = ['center', 'left', 'left', 'left', 'left', 'left', 'left', 'left', 'center'];

        const width = ['100', '300', '150', '100', '100', '100', '150', null, '70'];

        const table = new Table(tab.querySelector('#table-profile-impact'), columns, alignment, {
            main: [data => createNode('div', data.priority), data => {
                const node = createNode('div', data.asset, 'text-truncate');
                node.title = data.asset;
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
                    return node;
                }, data => createNode('div', this.createRiskTemplate(data.level, data.cve_id, data.title2),
                    'text-truncate'), () => createNode('div', '<i class="p-1 fal fa-chevron-right" role="button"></i>')]
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
            return this.compareRiskLevels(a.level, b.level)
        }, null], true);

        table.onRowClick((data) => {

            const target = data.target;

            if (target.classList.contains('fa-chevron-right')) {
                const asset = data.value.asset;
                const vulnerabilities = this.vulnerabilities_.filter(vuln => vuln.asset === asset);
                const panel = new Panel(document.getElementById('panel'), this.datastore_, asset, data.value);
            }
        })

        this.datastore_.getVulnerabilities(this.id_).then((response) => {

            let data = response;

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
