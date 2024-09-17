'use strict'

import {createNode} from "../helpers.js";
import {Table} from "../_shared/table.js";
import {isDomain, isIpV4, isIpV4Range, isValidDomainOrIp} from "../helpers.js";

export class TabAssetsImporter extends com.computablefacts.widgets.Widget {

  constructor(container, datastore, assets) {
    super(container)
    this.observers_ = new com.computablefacts.observers.Subject();
    this.datastore_ = datastore;
    this.assets_ = assets;
    this.importTable_ = null;
    this.handleClickEvent = this._handleClick.bind(this);  // bind to have `this` in the context of the class instance
    this.render()
  }

  onBackClick(callback) {
    this.observers_.register('back-click', data => {
      if (callback) {
        callback(data);
      }
    });
  }

  destroy() {
    if (this.container) {
      this.container.removeEventListener('click', this.handleClickEvent);
    }
    super.destroy();
  }

  _handleClick(event) {
    const target = event.target;
    if (target.tagName === 'BUTTON' || target.parentElement.tagName === 'BUTTON') {
      if (target.id === "add-button"){
        event.stopPropagation();
        console.log('add');
        const input = this.container.querySelector('#add-input');
        const inputValue = input.value;
        this._addAsset(inputValue)
      }
      else if (target.id === "search-button"){
        const input = this.container.querySelector('#search-input');
        const inputValue = input.value;
        this._searchAsset(inputValue)
      }
      else if (target.id === 'add-assets-button'){
        this._importAssets();
      }
      else if (target.id === "csv-upload-button"){
        const input = this.container.querySelector('#csv-input');
        this._uploadCSV(input.files[0]);
      }
    }
    if (target.tagName === 'INPUT' && target.type === 'checkbox' ||
      target.parentElement.tagName === 'INPUT' && target.parentElement.type === 'checkbox'
    ){
      if (target.id === 'select-all-checkbox'){
        const input = this.container.querySelector('#select-all-checkbox');
        this._changeImportState(input.checked)
      }
    }
    if (target.tagName === "A"){
      if (target.id === "back-assets") {
        this.observers_.notify('back-click');
      }
      else if (target.id === "download-example-button") {
        this._downloadExampleCsv();
      }
      else if (target.id === "cancel-import-button"){
        this.container.querySelector('#import-table-view').classList.add('d-none')
        this.container.querySelector('#assets-import').classList.remove('d-none')
      }
    }
  }

  _handleImportModeChange(selectedMode){
    switch (selectedMode.value) {
      case 'domain_ip':
        this._showDomainIpInput()
        break;
      case 'tld':
        this._showTLDInput()
        break;
      case 'csv':
        this._showCSVInput()
        break;
      default:
        break;
    }
  }

  _showCSVInput(){
    const form = this.container.querySelector("#form-import");

    form.innerHTML = `
    <div class="d-flex flex-column">
      <input id="csv-input" type="file" class="form-control mt-1 mb-2 rounded-0" accept=".csv" aria-label="CSV file" placeholder="Select CSV file">
      <div class="d-flex justify-content-end">
        <a id="download-example-button" role="button" class="my-auto">${i18next.t('Télécharger un fichier d\'exemple')}</a>
        <button id="csv-upload-button" type="button" class="btn btn-primary my-auto ms-2 rounded-0"> <i class="fal fa-download me-2"></i>${i18next.t('Importer')}</button>
      </div>
    </div>`;
  }

  _showTLDInput() {
    const form = this.container.querySelector("#form-import");

    form.innerHTML = `
    <div class="d-flex flex-column">
      <input id="search-input" type="text" class="bp4-input flex-grow-1 mt-1 mb-2" aria-label="domain or ip" placeholder="${i18next.t('ex: computablefacts.com')}">
      <button id="search-button" type="button" class="btn btn-primary my-auto ms-auto rounded-0">${i18next.t('Rechercher')}</button>
    </div>`;
  }

  _showDomainIpInput() {
    const form = this.container.querySelector("#form-import");

    form.innerHTML = `
    <div class="d-flex flex-column">
      <input id="add-input" type="text" class="bp4-input flex-grow-1 mt-1 mb-2" aria-label="domain or ip" placeholder="${i18next.t('ex: google.com')}">
      <button id="add-button" type="button" class="btn btn-primary my-auto ms-auto rounded-0">${i18next.t('+ Ajouter')}</button>
    </div>`;
  }


  _handleEvents(){
    this.container.addEventListener('click', this.handleClickEvent);
  }

  _addAsset(value){
    if (isValidDomainOrIp(value)){
      const button = this.container.querySelector('#add-button')
      button.disabled = true;
      this.datastore_.saveAsset(value, isDomain(value) ? 'DNS': 'IP')
        .then((asset) => {
          this.container.querySelector('#back-assets').classList.remove('d-none')
          this.assets_.push(asset)
          this.datastore_.toastSuccess(i18next.t("Actif ajouté avec succès."))
        })
        .catch(error => {
          this.datastore_.toastDanger(`${error}`);
        }).finally(() => button.disabled = false)
    }
    else {
      this.datastore_.toastWarning(i18next.t("Ce n'est pas un nom de domaine ou une adresse ip valide."))
    }
  }

  _searchAsset(value){
    if (isDomain(value)){
      this._createTableImport()
      this.datastore_.discoverFromDomain(value).then(response => {
        this.container.querySelector('#assets-count-import').innerHTML = `
            ( <span class="orange">${response.subdomains.length}</span> )
        `
        this.importTable_.data = response.subdomains.filter((data) => data !== '').map(data => {
          return {
            asset: data,
            checked: false,
            inDatabase: !!this.assets_.find((asset) => asset.asset === data)
          }
        })

      })
    }
    else if (isIpV4(value) || isIpV4Range(value)){
      this.datastore_.discoverFromIp(value).then(response => console.log(response));
    }
  }

  _createTableImport(isCsv = false){
    const container = this.container.querySelector('#import-table-view');
    this.container.querySelector('#assets-import').classList.add('d-none');
    container.classList.remove('d-none');
    if (this.importTable_){
      this.importTable_.destroy();
    }
    const columns = isCsv ? [
      document.createTextNode(i18next.t('Actif')), document.createTextNode(i18next.t('Étiquettes')),
      document.createTextNode(i18next.t('Status')), (() => {
        const checkbox = document.createElement('input');
        checkbox.classList.add('me-2')
        checkbox.type = 'checkbox';
        checkbox.id = 'select-all-checkbox';
        return checkbox;
      })()
    ] : [
      document.createTextNode(i18next.t('Actif')),
      document.createTextNode(i18next.t('Status')), (() => {
        const checkbox = document.createElement('input');
        checkbox.classList.add('me-2')
        checkbox.type = 'checkbox';
        checkbox.id = 'select-all-checkbox';
        return checkbox;
      })()
    ];
    const alignment = isCsv ? ['left', 'left', 'left', 'right'] : ['left', 'right', 'right']
    this.importTable_ = new Table(this.container_.querySelector('#table-assets-import'),
      columns,
      alignment,
      {
        main: isCsv ? [
          (data) => document.createTextNode(data.asset ? data.asset : '-'),
          (data) => {
            if (!data.tags.length){
              return document.createTextNode('-')
            }
            const tags = data.tags.map(tag => `<span class="badge me-2 mb-1">${tag}</span>`).join('');
            return createNode('div', tags)
          },
          (data) => document.createTextNode(data.inDatabase ? i18next.t('importé') : ''),
          (data) => {
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.classList.add('me-2')
            checkbox.checked = data.checked
            checkbox.disabled = data.inDatabase;
            return checkbox
          }
        ] : [
          (data) => document.createTextNode(data.asset ? data.asset : '-'),
          (data) => document.createTextNode(data.inDatabase ? i18next.t('importé') : ''),
          (data) => {
            const checkbox = document.createElement('input');
            checkbox.classList.add('me-2')
            checkbox.type = 'checkbox';
            checkbox.checked = data.checked
            checkbox.disabled = data.inDatabase;
            return checkbox
          }
        ]
      },
      isCsv ?[null, '300', '100', '50'] : [null, '100', '50'],
      [
        (a, b) => a.asset.localeCompare(b.asset)
      ],
      true)

    this.importTable_.onRowClick((data) => {
      const target = data.target;
      if (target.type === 'checkbox'){
        data.value.checked = target.checked;
      }
    })
  }

  _changeImportState(value){
    if (this.importTable_){
      this.importTable_.data.forEach((row) => row.checked = value)
      this.importTable_.redraw()
    }
  }

  _importAssets(){
    const button = this.container.querySelector('#add-assets-button');
    const spinner =this.container.querySelector('.fa-spinner');
    spinner.classList.remove('d-none');
    button.disabled = true;
    const data = this.importTable_.data.filter(data => data.checked && !data.inDatabase);
    const promises = data.map(row => {
      const assetType = isDomain(row.asset) ? 'DNS' : 'IP';
      return this.datastore_.saveAsset(row.asset, assetType).then(asset => {
        this.assets_.push(asset)
        if (asset) {
          return Promise.all(row.tags.map(tag => asset.addTag(tag)));
        }
      });
    });

    Promise.allSettled(promises)
      .then(() => {
        return this.assets_;
      })
      .then(response => {
        this.container.querySelector('#back-assets').classList.remove('d-none')
        data.forEach(asset => {
          if (response.find(a => a.asset === asset.asset)) {
            asset.inDatabase = true;
          }
        });
        this.observers_.notify('back-click');
      })
      .catch(error => {
        console.error(i18next.t("Une erreur s'est produite, veuillez réessayer plus tard."), error);
        this.datastore_.toastDanger(error);
      })
      .finally(() => {
        this.importTable_.redraw();
        button.disabled = false;
        spinner.classList.add('d-none');
      });
  }

  _uploadCSV(file) {
    if (!file) {
      this.datastore_.toastWarning(i18next.t("Aucun fichier sélectionné."));
      return;
    }

    const reader = new FileReader();

    reader.onload = (event) => {
      const csvData = event.target.result;
      this._addAssetsFromCSV(csvData);
    };

    reader.onerror = (error) => {
      console.error(i18next.t("Erreur lors de la lecture du fichier CSV."), error);
      this.datastore_.toastDanger(i18next.t("Erreur lors de la lecture du fichier CSV."));
    };

    reader.readAsText(file);
  }

  _addAssetsFromCSV(csvData) {
    const rows = csvData.split('\n');
    const assets = rows
      .filter(row => row.trim())
      .slice(1)
      .map(row => {
        const columns = row.split(',');

        const asset = columns[0].trim();
        const tags = columns[1].trim().split('|');

        return {
          asset: asset,
          tags: tags,
          checked: false,
          inDatabase: false
        };
      });

    this.container.querySelector('#assets-count-import').innerHTML = `
            ( <span class="orange">${assets.length}</span> )
        `
    this._createTableImport(true);
    this.importTable_.data = assets.map(data => {
      return {
        asset: data.asset,
        tags: data.tags,
        checked: false,
        inDatabase: !!this.assets_.find((asset) => asset.asset === data.asset)
      }
    })
  }

  _downloadExampleCsv(){
    const exampleCSV = this._generateExampleCSV();
    const blob = new Blob([exampleCSV], {type: 'text/csv'});
    const url = URL.createObjectURL(blob);

    const link = document.createElement('a');
    link.href = url;
    link.download = 'example.csv';
    link.click();

    URL.revokeObjectURL(url);
  }

  _generateExampleCSV() {
    const exampleData = [
      {ip: '192.168.1.1', tags: ['tag1', 'tag2']},
      {ip: 'example.com', tags: ['tag3', 'tag4']}
    ];

    let csvContent = '';

    exampleData.forEach(({ip, tags}) => {
      const tagsJoined = tags.join('|');
      csvContent += `${ip},${tagsJoined}\n`;
    });

    return csvContent;
  }

  _createImportSelect(container){

    const importModes = [
      { name: i18next.t('Ajouter un nom de domaine, un sous-domaine ou une adresse IP'), value: 'domain_ip' },
      { name: i18next.t('Rechercher des sous-domaines à partir d\'un TLD'), value: 'tld' },
      { name: i18next.t('Importer depuis un fichier CSV'), value: 'csv' },
    ];

    const select = new com.computablefacts.blueprintjs.MinimalSelect(container, (mode) => mode.name);

    select.items = importModes;
    select.defaultText = i18next.t('Sélectionnez une méthode d\'ajout')

    return select;
  }

  _newElement() {
    const template = `
    <style>
        #assets-import {
            background-image: url('./img/hive.png');
            background-repeat: no-repeat;
            background-position: top calc(100% + 76px) left calc(100% + 72px);
        }
    </style>
    <div id="assets-import" class="px-3 d-flex flex-grow-1">
        <div>
            <h1> ${!this.assets_.length ? i18next.t('Première connexion ? Ajouter vos premiers actifs !') : i18next.t('Ajoutez des actifs !')}</h1>
            <div class="fw-bold mb-2">${i18next.t('Choisissez un mode d\'import d\'actifs parmi ceux de la liste ci-dessous.')}</div>
            <div>${i18next.t('Une fois la surveillance automatique des actifs mise en place, vous pourrez visualiser les types de vulnérabilités.')}</div>
            <div id="select-import" class="my-2"></div>
            <div id="form-import"></div>
        </div>
        <div class="d-flex justify-content-end flex-grow-1 mt-3">
            <div><a role="button" href="#" id="back-assets" class="${!this.assets_.length ? ' d-none': ''}">${i18next.t('< revenir à mes actifs')}</a></div>
        </div>
    </div>
    <div id="import-table-view" class="px-3 d-flex flex-column flex-grow-1 d-none">
        <div class="d-flex justify-content-between">
            <h1>${i18next.t('Actifs à importer')} <span id="assets-count-import">( <span class="orange">0</span> )</span> </h1>
            <a role="button" id="cancel-import-button" class="my-auto">${i18next.t('< revenir à l\'étape précédente')}</a>
        </div>
        <div id="table-assets-import" class="flex-grow-1 h-0"></div>
        <div class="d-flex justify-content-end my-2">
            <button id="add-assets-button" type="button" class="btn btn-primary my-auto ms-2 rounded-0">${i18next.t('+ Ajouter')} <i class="d-none my-auto fal fa-spinner fa-spin"></i></button>
        </div>
    </div>
    `;

    const tab = createNode('div', template, 'flex-grow-1 d-flex flex-column');
    const select = this._createImportSelect(tab.querySelector("#select-import"));

    select.onSelectionChange((selectedMode) => {
      this._handleImportModeChange(selectedMode);
    });

    this._handleEvents();

    return tab;
  }
}
