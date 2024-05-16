'use strict'

import {Panel} from "../_shared/panel.js";
import {Table} from "../_shared/table.js";
import {createNode, uniqueBy} from "../helpers.js";

export class TabAssets extends com.computablefacts.widgets.Widget {

    constructor(container, datastore) {
        super(container)
        this.observers_ = new com.computablefacts.observers.Subject();
        this.datastore_ = datastore;
        this.assetTags_ = [];
        this.assets_ = [];
        this.assetTable_ = null;
        this.select_ = null;
        this.chkWatch_ = null;
        this.chkNotWatch_ = null;
        this.switchInstances_ = new Map();
        this.render();
    }

    set assets(assets) {

        this.assets_ = assets;
        this.assetTags_ = uniqueBy([...new Set(assets.flatMap((data) => data.tags))], 'name', null, true);
        this.assetTable_.data = assets;
        this.select_.disabled = false;
        this.select_.items = [...new Set(
            this.assets_.flatMap((asset) => asset.tagsFromPorts.map(tag => tag.tag.toLowerCase())))].sort();
        const nbWatched = assets.filter(asset => asset.is_watched).reduce((acc, asset) => {
            if (asset.is_range) {

                const tagsByAsset = {};

                asset.tagsFromPorts.forEach(function (tag) {
                    tagsByAsset[tag.asset] = tagsByAsset[tag.asset] || [];
                    tagsByAsset[tag.asset].push(tag);
                });
                return acc + Object.keys(tagsByAsset).length;
            }
            return acc + 1;
        }, 0);
        const totalAssets = assets.length;

        if (nbWatched === 0) {
            this.container.querySelector('#assets-count').innerHTML = i18next.t(
                '( <span class=\'orange\'>{{count}}</span> )', {count: totalAssets});
        } else if (nbWatched === 1) {
            this.container.querySelector('#assets-count').innerHTML = i18next.t(
                '( <span class=\'orange\'>{{count}}</span> dont <span class=\'orange\'>1</span> surveillé )',
                {count: totalAssets});
        } else {
            this.container.querySelector('#assets-count').innerHTML = i18next.t(
                '( <span class=\'orange\'>{{total}}</span> dont <span class=\'orange\'>{{count}}</span> surveillés )',
                {total: totalAssets, count: nbWatched});
        }
        this._filterData()
    }

    onDelete(callback) {
        this.observers_.register('delete-asset', data => {
            if (callback) {
                callback(data);
            }
        });
    }

    onRadioChange(callback) {
        this.observers_.register('radio-change', data => {
            if (callback) {
                callback(data);
            }
        });
    }

    onFilterClick(callback) {
        this.observers_.register('filter-click', data => {
            if (callback) {
                callback(data);
            }
        });
    }

    _handleRowClick(data, table) {

        const target = data.target;
        const role = target.getAttribute('role');

        if (target.classList.contains('more-tags')) {
            this._toggleTagsRow(table, data)
        }
        if (target.classList.contains('badge') && role === 'button') {
            this._handleBadgeClick(target, data)
        }
        if (target.classList.contains('asset-more')) {
            this.observers_.notify('filter-click', {asset: data.value.asset})
        }
        if (target.classList.contains('fa-trash') && role === 'button') {

            const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            const modalBody = document.querySelector('#confirmDeleteModal .modal-body');

            modalBody.innerHTML = i18next.t(
                "Êtes-vous sûr de vouloir supprimer <strong>{{assetName}}</strong> et toutes les données associées ?",
                {assetName: data.value.asset});
            modal.show();

            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn')

            confirmDeleteBtn.addEventListener('click', () => {
                confirmDeleteBtn.setAttribute('disabled', 'disabled');
                data.value.delete().then(() => {
                    table.loading = true;
                    this.observers_.notify('delete-asset');
                }).finally(() => {
                    confirmDeleteBtn.removeAttribute('disabled');
                    modal.hide()
                });
            });
        }
        if (target.classList.contains('fa-chevron-right')) {
            const asset = data.value.asset;
            const panel = new Panel(document.getElementById('panel'), this.datastore_, asset);
        }

        if (target.classList.contains('fa-sync') && target.getAttribute('role') === 'button') {
            target.classList.add('disabled');

            const assetSwitch = this.switchInstances_.get(data.value.id);
            if (assetSwitch) {
                assetSwitch.disabled = true;
            }

            const asset = data.value;
            asset.restart().then((response) => {
                asset.update(response.asset);
                table.updateRow(data.index);
            }).finally(() => {
                if (assetSwitch) {
                    assetSwitch.disabled = false;
                }
                target.classList.remove('disabled');
            }).catch((error) => {
                this.datastore_.toastDanger(error.message);
                target.classList.remove('disabled');
            });
        }
    }

    _toggleTagsRow(table, data) {
        const trs = table.container.querySelectorAll('.sub-row');
        const subRow = data.subRow
        trs.forEach(tr => {
            if (tr !== subRow) {
                tr.classList.add('d-none');
            }
        })
        subRow.classList.toggle('d-none')
    }

    _handleBadgeClick(target, data) {
        const level = target.dataset.level;
        this.observers_.notify('filter-click', {
            level: level, asset: data.value.asset
        })
    }

    _filterAssets(watched, notWatched) {
        return this.assets_.filter(asset => (watched && asset.is_watched) || (notWatched && !asset.is_watched));
    }

    /**
     * @override
     */
    _newElement() {

        const template = `
            <style>
                #search-input {
                    width: 280px;
                }
                #tags-multiselect {
                    width: 280px;
                }
                .timeline-item {
                    min-width: 475px;
                }
            </style>
            <div id="assets-view" class="px-3 d-flex flex-column flex-grow-1">
                <div class="modal fade" id="confirmDeleteModal" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">${i18next.t("Confirmation de suppression")}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary border rounded-0" data-bs-dismiss="modal">${i18next.t(
            "Annuler")}</button>
                        <button type="button" id="confirmDeleteBtn" class="btn btn-outline-danger rounded-0">${i18next.t(
            "Supprimer")}</button>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="d-flex justify-content-between">
                    <h1> ${i18next.t("Mes actifs")} <span id="assets-count">( <span class="orange">0</span> )</span> </h1>
                    <div class="d-flex">
                        <div id="chk-watch" class="my-auto"></div>
                        <div id="chk-not-watch" class="my-auto mx-2"></div>
                        <div id="tags-multiselect" class="ms-auto my-auto me-1"></div>
                        <div class="bp4-input-group ms-1 my-auto">
                            <span class="bp4-icon-standard bp4-icon bp4-icon-search"></span>
                            <input id="search-input" class="bp4-input my-auto" type="text" placeholder="${i18next.t(
            "Rechercher un actif ou une étiquette...")}">
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end mb-3">
                        <div id="asset-filter" class="form-group">
                            <label class="fw-bold">${i18next.t("Afficher les actifs :")}</label>
                            &nbsp;&nbsp;&nbsp;
                            <span>
                                <input type="radio" id="filter-24h" name="asset-filter" value="24">
                                <label for="filter-24h">${i18next.t("Découverts il y a moins de 24h")}</label>
                            </span>
                            &nbsp;&nbsp;&nbsp;
                            <span>
                                <input type="radio" id="filter-3d" name="asset-filter" value="72">
                                <label for="filter-3d">${i18next.t("Découverts il y a moins de 3 jours")}</label>
                            </span>
                            &nbsp;&nbsp;&nbsp;
                            <span>
                                <input type="radio" id="filter-1w" name="asset-filter" value="168">
                                <label for="filter-1w">${i18next.t("Découverts il y a moins d'1 semaine")}</label>
                            </span>
                            &nbsp;&nbsp;&nbsp;
                            <span>
                                <input type="radio" id="filter-all" name="asset-filter" value="all" checked>
                                <label for="filter-all">${i18next.t("Tous les actifs")}</label>
                            </span>
                        </div>
                    </div>
                <div id="table-assets" class="flex-grow-1 h-0 mb-3"></div>
            </div>
    `

        const tab = createNode('div', template, 'flex-grow-1 d-flex flex-column')
        this.assetTable_ = this._createTable(tab)

        this.select_ = new com.computablefacts.blueprintjs.MinimalMultiSelect(tab.querySelector('#tags-multiselect'),
            (tag) => tag, () => null, (tag) => tag, null)
        this.select_.defaultText = i18next.t('Filtrer par produits et services...')
        this.select_.disabled = true;
        this.chkWatch_ = new com.computablefacts.blueprintjs.MinimalCheckbox(tab.querySelector('#chk-watch'), true,
            i18next.t('Surveillés'));
        this.register(this.chkWatch_);

        this.chkNotWatch_ = new com.computablefacts.blueprintjs.MinimalCheckbox(tab.querySelector('#chk-not-watch'),
            true, i18next.t('Non surveillés'));
        this.register(this.chkNotWatch_);

        this._handleEvents(tab);

        return tab;
    }

    _handleEvents(container) {
        const debounceLast = com.computablefacts.helpers.debounceLast
        const searchInput = container.querySelector('#search-input');
        const radioButtons = container.querySelectorAll('input[name="asset-filter"]');

        radioButtons.forEach(radio => {
            radio.addEventListener('change', () => {
                this.assetTable_.loading = true
                this.observers_.notify('radio-change', {hours: radio.value === 'all' ? null : radio.value})
            });
        });

        searchInput.addEventListener('input', debounceLast(() => {
            this._filterData()
        }));

        this.select_.onSelectionChange(() => {
            this._filterData()
        })

        this.chkWatch_.onSelectionChange(() => {
            this._filterData()
        });

        this.chkNotWatch_.onSelectionChange(() => {
            this._filterData()
        });
    }

    _filterData() {

        const chkWatch = this.chkWatch_;
        const chkNotWatch = this.chkNotWatch_;
        const searchInput = this.container.querySelector('#search-input');
        const selectedTags = this.select_.selectedItems;

        const watched = chkWatch.checked;
        const notWatched = chkNotWatch.checked;
        const query = searchInput.value.toLowerCase();

        let assets = this._filterAssets(watched, notWatched);

        if (query) {
            assets = assets.filter(asset => asset.asset.toLowerCase().includes(query) || asset.tags.some(
                tag => tag.name.toLowerCase().includes(query)));
        }

        if (selectedTags.length > 0) {
            assets = assets.filter(
                asset => selectedTags.every(tag => asset.tagsFromPorts.map(t => t.tag.toLowerCase()).includes(tag)));
        }

        this.assetTable_.data = assets;
    }

    _createTable(tab) {

        const columns = [document.createTextNode(i18next.t('Actif')), document.createTextNode(i18next.t('Etiquettes')),
            document.createTextNode(i18next.t('Vulnérabilités')), document.createTextNode(i18next.t('Surveillance')),
            document.createTextNode(''), document.createTextNode('')];

        const alignment = ['left', 'left', 'center', 'center', 'center', 'center']

        const table = new Table(tab.querySelector('#table-assets'), columns, alignment, {
            subRow: (data, index, self) => {

                const createItem = (query) => {
                    return {id: query, name: query};
                };

                const tagsByAsset = {};

                data.tagsFromPorts.forEach(function (tag) {
                    tagsByAsset[tag.asset] = tagsByAsset[tag.asset] || [];
                    tagsByAsset[tag.asset].push(tag);
                });

                const node = createNode('div', `
                    <div class="me-2 fw-bold d-flex justify-content-between">
                        <div class="fw-bold">${i18next.t('Mes Étiquettes')}</div>
                    </div>
                    <div id="select-tags" class="mt-2 w-50"></div>
                `, 'd-flex flex-column');

                const select = new com.computablefacts.blueprintjs.MinimalMultiSelect(
                    node.querySelector('#select-tags'), (tag) => tag.name, () => null, (tag) => tag.name.toLowerCase(),
                    null, createItem)
                select.defaultText = i18next.t('ex: france, aws, etc.');
                select.selectedItems = [...data.tags];
                select.onSelectionChange((selection) => {
                    data.tags.forEach((tag) => {
                        if (selection.findIndex((v) => v.name.toLowerCase() === tag.name.toLowerCase()) < 0) {
                            data.removeTag(tag.id).then(() => {
                                data.tags = data.tags.filter((t) => t.id !== tag.id)
                                self.updateRow(index)
                            })
                        }
                    })
                    selection.forEach((tag) => {
                        if (data.tags.findIndex(v => v.name.toLowerCase() === tag.name.toLowerCase()) < 0) {
                            data.addTag(tag.name).then((response) => {
                                data.tags.push({
                                    id: response[0].id, name: response[0].key
                                })
                                self.updateRow(index)
                            })
                        }
                    })
                });

                this.register(select)
                select.items = this.assetTags_;

                return node;
            },
            main: [data => createNode('div', data.asset, 'text-truncate'), data => createNode('div',
                `${data.tags.sort((a, b) => a.name.localeCompare(b.name)).map(
                    (tag) => `<span class="badge me-2 mb-1">${tag.name}</span>`).join(
                    ' ')}<a class="more-tags my-auto mx-1" href="#">${i18next.t('+ add tags')}</a>`, 'd-flex'),
                data => {

                    const node = document.createElement('div');

                    if (data.is_watched) {

                        const loader = new com.computablefacts.blueprintjs.MinimalSpinner(node);
                        this.register(loader);
                        this.datastore_.getInfosFromAsset(data.asset).then(infos => {

                            loader.destroy();

                            if (infos.timeline.next_scan) {

                                const nbVulnsHigh = infos.vulnerabilities.filter(vuln => vuln.level === "high").length;
                                const nbVulnsMedium = infos.vulnerabilities.filter(
                                    vuln => vuln.level.startsWith("medium")).length;
                                const nbVulnsLow = infos.vulnerabilities.filter(
                                    vuln => vuln.level.startsWith("low")).length;

                                node.innerHTML = `
                                    <span class="badge high text-muted" role="button" data-level="high">${nbVulnsHigh}</span>
                                    <span class="badge medium text-muted" role="button" data-level="medium">${nbVulnsMedium}</span>
                                    <span class="badge low text-muted" role="button" data-level="low">${nbVulnsLow}</span>
                                `;
                            } else {
                                node.innerHTML = `<span class="badge badge-outline">${i18next.t('Scannage...')}</span>`;
                            }
                        }).finally(() => {
                            if (loader) {
                                loader.destroy()
                            }
                        })
                    } else {
                        node.innerHTML = `<span class="badge badge-outline">${i18next.t('En pause')}</span>`
                    }
                    return node;
                }, (data, index, self) => {

                    const node = document.createElement('div');
                    node.id = `asset-${data.id}-toggle`;

                    const toggle = new com.computablefacts.blueprintjs.MinimalSwitch(node, null, null, null, 'on',
                        'off');
                    toggle.checked = data.is_watched;
                    toggle.onSelectionChange(status => {
                        toggle.disabled = true;
                        data.assetActions(status === 'checked' ? 'start' : 'end')
                        .then((response) => {
                            data.update(response.asset)
                            node.id = `asset-${data.id}-toggle`
                            toggle.disabled = false;
                            self.updateRow(index)
                        }).catch((error) => this.datastore_.toastDanger(error.message));
                    })
                    this.register(toggle)

                    this.switchInstances_.set(data.id, toggle);
                    return node;
                }, (data) => {
                    let htmlContent = '';
                    if (data.is_watched) {
                        htmlContent = `
                            <div class="icon-container">
                                <i class="p-1 fal fa-sync" id="relaunch-scan-${data.id}" role="button" title="${i18next.t("Relancer le scan")}""></i>
                            </div>
                        `;
                    }
                    return createNode('div', htmlContent);
                }, (data) => {
                    let htmlContent = '';
                    if (!data.is_watched) {
                        htmlContent = `<div class="red"><i class="fal fa-trash" id="delete-${data.id}" role="button" title="${i18next.t("Supprimer l'actif")}"></i></div>`;
                    } else {
                        htmlContent = `
                                <div class="icon-container">
                                    <i class="p-1 fal fa-chevron-right" role="button"></i>
                                </div>`;
                    }
                    return createNode('div', htmlContent);
                }]
        }, ['350', null, '150', '120', '70', '70'], [(a, b) => {
            if (!a.asset || !b.asset) {
                return !a.asset ? 1 : -1;
            }
            return a.asset.localeCompare(b.asset)
        }, null, null, null, null, null, null], true);

        table.onRowClick((data) => {
            this._handleRowClick(data, table)
        });
        return table;
    }
}
