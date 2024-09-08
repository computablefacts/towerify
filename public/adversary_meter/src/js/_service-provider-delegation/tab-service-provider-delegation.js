'use strict';

export class TabDelegation extends com.computablefacts.widgets.Widget {

  constructor(container, datastore) {
    super(container);
    this.datastore_ = datastore;
    this.observers_ = new com.computablefacts.observers.Subject();
    this.tags_ = [];
    this.loader_ = null;
    this.button_ = null;
    this.render();
    this.fetchAndDisplayDelegates();
  }

  fetchAndDisplayDelegates() {
    this.datastore_.getHashes().then((delegates) => {
     delegates.forEach(delegate => {
        this._displayDelegate(delegate);
      });
      if (this.loader_){
        this.loader_.destroy();
        this.button_.disabled = false;
      }
    });
  }

  _displayDelegate(delegate) {
    const container = this.container.querySelector('#hash-container');
    const line = this._createLineWithSelect(delegate.tag, delegate.hash);

    const hashURL = `${window.location.protocol}//${window.location.hostname}:${window.location.port}/am/web/cyber-todo/${delegate.hash}`;
    line.querySelector('.hash-url').innerHTML = `${hashURL} <i class="ms-1 fal fa-external-link"></i>`;
    line.querySelector('.hash-url').href = hashURL;

    line.querySelector('.count').textContent = delegate.views;

    line.id = delegate.id;

    container.appendChild(line);
  }

  _createLineWithSelect(initialTag = null, initialHash = null) {
    const line = document.createElement('div');
    line.className = 'line mb-2';

    line.innerHTML = `
      <div class="select-tag-container d-flex">
        <div class="select-tag flex-grow-1" id="select-${initialHash || 'new'}" data-selected-tag="${initialTag || ''}"></div>
        <div class="loader my-auto ps-1"></div>
      </div>
      <a href="#" target="_blank" class="hash-url mx-3 my-auto">
      </a>
      <div class="views-count my-auto me-3 text-end views-container">
        <span class="views-label fw-bold">${i18next.t('vues :')}</span>&nbsp;<span class="views-value count orange">0</span>
      </div>
      <div class="cross my-auto text-end">✖</div>
    `;

    if (!initialTag) {
      const select = new com.computablefacts.blueprintjs.MinimalSelect(line.querySelector('.select-tag'), (item) => item);
      select.filterable = false;
      if (!this.tags_.length){
        select.disabled = true;
        select.defaultText = i18next.t('Sélectionner une étiquette...')
        this.datastore_.getAssetTags().then(response => {
          select.disabled = false;
          select.items = response.tags;
          this.tags_ = response.tags;
        })
      }
      else {
        select.items = this.tags_;
      }
      select.onSelectionChange((item) => {
        select.disabled = true;
        const loaderEl = line.querySelector('.loader');
        const loader = new com.computablefacts.blueprintjs.MinimalSpinner(loaderEl, 'small');
        this.datastore_.postHash(item).then(hash => {
          const hashURL = `${window.location.protocol}//${window.location.hostname}:${window.location.port}/am/web/cyber-todo/${hash.hash}`;
          line.querySelector('.hash-url').innerHTML = `${hashURL} <i class="fal fa-external-link"></i>`;
          line.querySelector('.hash-url').href = hashURL;
          line.id = hash.id;
        }).catch((error) => this.datastore_.toastDanger(error.message))
          .finally(() => loader.destroy());
      });
      this.register(select)
    }
    else {
        const select = new com.computablefacts.blueprintjs.MinimalSelect(line.querySelector('.select-tag'), (item) => item);
        select.filterable = false;
        select.items = this.tags_ || [];
        select.disabled = true;
        select.selectedItem = initialTag;
        this.register(select)
    }
    return line;
  }

  _handleClick(event) {
    const target = event.target;

    if (target.id === 'add-line') {
      const container = this.container.querySelector('#hash-container');
      const newLine = this._createLineWithSelect();
      container.appendChild(newLine);
    }

    if (target.classList.contains('cross')) {
      let parentId = target.parentElement.id;
      if(parentId && parentId !== 'select-new'){
        this.datastore_.deleteHash(parentId).then(() => {
          target.parentElement.remove();
        });
      }
      target.parentElement.remove();
    }
  }

  _newElement() {
    const template = `
      <style>
      #hash-container {
        display: grid;
        grid-template-columns: 300px auto 150px 50px;
        gap: 1rem;
      }

      .line {
        display: contents;
        }

      #add-line {
        color: #969ea9;
        border-color: #f5f5f5;
        background-color: white;
      }

      #add-line:hover {
        color: #969ea9;
        background-color: #f5f5f5;
        border-color: #f5f5f5;
      }

      .cross:hover {
        cursor: pointer;
      }

      .select-tag-container, .hash-url, .views-count, .cross {
        align-self: center;
      }

      .views-container {
        display: flex;
        justify-content: end;
        width: 100%;
      }

      .views-label {
        text-align: right;
      }

      .views-value {
        text-align: left;
        padding-left: 5px;
      }
      </style>
      <h1>${i18next.t('Délégation prestataire')}</h1>
      <div id="loader"></div>
      <div id="hash-container">
          <!-- Lines will be added here -->
      </div>
      <button id="add-line" type="button" class="btn btn-outline-secondary ms-auto rounded-0 mt-2 w-100">
        <i class="fal fa-plus"></i>
      </button>
    `;

    const tab = document.createElement('div');
    tab.innerHTML = template;
    tab.className = 'px-3 pb-3 flex-grow-1 d-flex flex-column';
    tab.addEventListener('click', this._handleClick.bind(this));

    this.loader_ = new com.computablefacts.blueprintjs.MinimalSpinner(tab.querySelector('#loader'));
    this.button_ = tab.querySelector('#add-line')
    this.button_.disabled = true;
    return tab;
  }
}

