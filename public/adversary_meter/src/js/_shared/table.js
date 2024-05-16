'use strict'

import {chunk} from "../helpers.js";

export class Table extends com.computablefacts.widgets.Widget {

  constructor(container, columns, alignment, processes, widths, sorts, loading, defaultSort = 0, defaultSortOrder = 'ASC', fixed = true) {
    super(container);
    this.columns_ = columns;
    this.alignment_ = alignment;
    this.processes_ = processes;
    this.data_ = [];
    this.sortedData_ = [];
    this.widths_ = widths;
    this.sorts_ = sorts;
    this.limit_ = 20;
    this.currentPage_ = 0;
    this.pages = [];
    this.activeSortIcon_ = null;
    this.loading_ = !!loading;
    this.table_ = null;
    this.defaultSort_ = defaultSort;
    this.defaultSortOrder_ = defaultSortOrder;
    this.fixed_ = fixed;
    this.observers_ = new com.computablefacts.observers.Subject();
    this._handleRowClick = this._handleRowClick.bind(this)
    this.render();
  }

  get columns() {
    return this.columns_;
  }

  set columns(values) {
    this.columns_ = values;
    this.render();
  }

  get alignment() {
    return this.alignment_;
  }

  set alignment(values) {
    this.alignment_ = values;
    this.render();
  }

  get data() {
    return this.data_;
  }

  set data(data) {
    this.data_ = data;
    this.currentPage_ = 0;
    this._sortData(this.sorts_[this.defaultSort_], this.defaultSortOrder_ !== 'ASC');
  }

  get processes() {
    return this.processes_;
  }

  set processes(processes) {
    this.processes_ = processes;
    this._updateTable(this.sortedData_);
  }

  get loading() {
    return this.loading_;
  }

  set loading(value) {
    this.loading_ = value;
    this.render();
  }

  onRowClick(callback) {
    this.observers_.register('row-clicked', data => {
      if (callback) {
        callback(data);
      }
    });
  }

  updateRow(rowIndex){

    const fragment = document.createDocumentFragment()
    const row = this.table_.querySelector(`tr[data-index="${rowIndex}"]`);
    const pages = chunk(this.sortedData_, this.limit_);
    const data = pages[this.currentPage_];
    const value = data[rowIndex];

    this.processes_.main.forEach((callback, index) => {

      const td = document.createElement('td');
      td.classList.add('position-relative');

      if (this.widths_[index]) {
          td.style.width = this.widths_[index] + 'px';
      }
      switch (this.alignment[index]) {
        case 'right':
          td.classList.add('text-end');
          break;
        case 'center':
          td.classList.add('text-center');
          break;
        default:
          break;
      }
      td.innerHTML = `<div class="d-flex${this.alignment_[index] === 'right' ? ' justify-content-end' : this.alignment_[index] === 'center' ? ' justify-content-center' : ''} text-truncate"></div>`;
      td.querySelector('div').appendChild(callback(value, rowIndex, this));
      fragment.appendChild(td);
    });
    row.innerHTML = ``;
    row.append(fragment);
  }

  redraw(){
    this._updateTable(this.sortedData_)
  }

  _handleRowClick(e){

    const tbody = this.container.querySelector('tbody');
    const tr = e.target.closest('tr');

    if (tr && tbody.contains(tr)) {
      const page = chunk(this.sortedData_, this.limit_)[this.currentPage_];
      const sub = tr.nextSibling && tr.nextSibling.classList.contains('sub-row') ? tr.nextSibling : null;
      const value = page[parseInt(tr.dataset.index, 10)];
      this.observers_.notify('row-clicked', { target: e.target, subRow: sub, value: value, index: parseInt(tr.dataset.index, 10) });
    }
  };

  _injectRows(data) {

    const fragment = document.createDocumentFragment()
    const rows = [];
    const pages = chunk(data, this.limit_);

    data = data.length ? pages[this.currentPage_] : [];

    if (this.table_) {

      const tbody = this.table_.querySelector('tbody');

      // Remove event listener to table body
      tbody.removeEventListener('click', this._handleRowClick);

      // Add event listener to table body
      tbody.addEventListener('click', this._handleRowClick);

      // Generate new rows

      if (data.length === 0) {

        const tr = document.createElement('tr');
        const td = document.createElement('td');

        td.setAttribute('colspan', this.columns.length);
        td.classList.add('text-center')
        td.textContent = i18next.t('Aucune donnée disponible');
        tr.appendChild(td);
        rows.push(tr);
      }
      else {
        data.forEach((value, rowIndex) => {

          const row = [];

          this.processes_.main.forEach((callback, index) => {

            const td = document.createElement('td');
            td.classList.add('position-relative');

            if (this.widths_[index]) {
              td.style.width = this.widths_[index] + 'px';
            } else {
              td.classList.add('w-100');
            }
            switch (this.alignment[index]) {
              case 'right':
                td.classList.add('text-end');
                break;
              case 'center':
                td.classList.add('text-center');
                break;
              default:
                break;
            }

            td.innerHTML = `<div class="d-flex${this.alignment_[index] === 'right' ? ' justify-content-end' : this.alignment_[index] === 'center' ? ' justify-content-center' : ''} text-truncate"></div>`;
            td.querySelector('div').appendChild(callback(value, rowIndex, this));
            row.push(td);
          });

          const tr = document.createElement('tr');
          tr.className = !(rowIndex % 2) ? 'row-light' : '';
          tr.setAttribute('data-index', rowIndex);

          if (this.processes_.txtColor) {
              tr.style.color = this.processes_.txtColor(value, tr.style.color);
          }

          tr.append(...row);
          rows.push(tr);

          if (this.processes_.subRow) {

            const sub = document.createElement('tr');
            sub.setAttribute('data-index', rowIndex);

            if (!(rowIndex % 2)) {
                sub.classList.add('row-light');
            }
            if (this.processes_.txtColor) {
              sub.style.color = this.processes_.txtColor(value, sub.style.color);
            }

            sub.classList.add('d-none', 'sub-row');
            sub.innerHTML = `<td colspan="${this.columns.length}"></td>`;
            sub.querySelector('td').appendChild(this.processes_.subRow(value, rowIndex, this));
            rows.push(sub);
          }
        });
      }

      // Replace old rows with new rows
      tbody.innerHTML = '';
      rows.forEach(row => fragment.appendChild(row));
      tbody.appendChild(fragment);
    }
  }


  _sortData(callback, down){
    this.sortedData_ = this.data.slice(0, this.data.length);
    this.sortedData_.sort((a, b) => {
      if (a == null) return down ? 1 : -1;
      if (b == null) return down ? -1 : 1;
      return callback(a, b) * (down ? -1 : 1)
    });
    this._updateTable(this.sortedData_)
  }

  _injectPagination(data){
    const pagination = this.table_.querySelector('#pagination');
    const pages = chunk(data, this.limit_);

    // Remove old pagination links
    pagination.innerHTML = '';

    // Remove click event
    pagination.onclick = null;

    // Do not add pagination links if there is only one page
    if (pages.length <= 1) {
      pagination.classList.add('d-none');
      return;
    }

    // Build new pagination links
    let template = `
    <nav aria-label="pagination">
      <ul class="pagination">
  `;
    const min = Math.max(0, this.currentPage_ - 1);
    const max = Math.min(min + 3, pages.length);
    if (min > 0) {
      template += `<li class="page-item"><a class="page-link" href="#" id="first">${i18next.t('Première page')}</a></li>`;
    }
    for (let i = min; i < max; i++) {
      template += `<li class="page-item${this.currentPage_ === i ? ' active' : ''}"><a class="page-link" href="#">${i + 1}</a></li>`;
    }
    if (max < pages.length) {
      template += `<li class="page-item"><a class="page-link" href="#" id="last">${i18next.t('Dernière page')}</a></li>`;
    }
    template += `</ul></nav>`;
    pagination.innerHTML = template;
    pagination.classList.remove('d-none');

    // Add event listener to pagination links
    pagination.onclick = (event) => {
      if (event.target.classList.contains('page-link')) {
        if (event.target.id === 'first') {
          this.currentPage_ = 0;
        } else if (event.target.id === 'last') {
          this.currentPage_ = pages.length - 1;
        } else {
          this.currentPage_ = Number(event.target.textContent) - 1;
        }
        this._updateTable(data);
      }
    };
  }

  _updateTable(data){
    this._injectRows(data);
    this._injectPagination(data);
  }



  /**
   * @override
   */
  _newElement() {
    const columns = this.columns.map((node, idx) => {
      const th = document.createElement('th');

      if (this.alignment) {
        switch (this.alignment[idx]) {
          case 'right':
            th.className = 'text-end';
            break;
          case 'center':
            th.className = 'text-center';
            break;
          default:
            break;
        }
      }
      if (this.widths_[idx]) th.style.width = this.widths_[idx] + 'px';

      if (this.sorts_[idx]) {
        const sortIcon = document.createElement('i');
        sortIcon.className = 'fas fa-sort-down me-1';
        sortIcon.setAttribute('role', 'button');
        sortIcon.onclick = () => {
          if (this.activeSortIcon_ && this.activeSortIcon_ !== sortIcon) {
            this.activeSortIcon_.style.transform = 'rotate(0deg)';
            this.activeSortIcon_.classList.remove('orange');
          }

          sortIcon.style.transform = sortIcon.style.transform === 'rotate(180deg)' ? 'rotate(0deg)' : 'rotate(180deg)';
          sortIcon.classList.add('orange');
          this._sortData(this.sorts_[idx], sortIcon.style.transform === 'rotate(180deg)');
          this.activeSortIcon_ = sortIcon;
        };

        if (idx === this.defaultSort_){
          sortIcon.style.transform = this.defaultSortOrder_ === 'ASC' ? 'rotate(0deg)' : 'rotate(180deg)';
          sortIcon.classList.add('orange');
          this.activeSortIcon_ = sortIcon;
        }
        th.appendChild(sortIcon);
      }
      th.appendChild(node);
      return th;
    });

    const table = document.createElement('div');
    table.className = 'h-100 d-flex flex-column';
    table.innerHTML = `
    <div class="h-0 overflow-auto border-top-0 flex-grow-1 bg-white border">
      <table class="table ${this.fixed_ ? 'table-fixed' : ''} mb-0">
        <thead class="fw-bold">
          <tr></tr>
        </thead>
        <tbody>
          <tr>
            <td class="text-center" colspan="${columns.length}">
                ${i18next.t('Il n\'y a pas d\'éléments à afficher.')}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div id="pagination" class="d-flex justify-content-end d-none"></div>
  `;

    table.querySelector('thead tr').append(...columns);

    if (this.loading) {
      const container = table.querySelector('tbody tr td');
      this.register(new com.computablefacts.blueprintjs.MinimalSpinner(container));
    }

    this.table_ = table;
    return table;
  }
}
