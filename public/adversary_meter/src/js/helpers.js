'use strict'

export function chunk(array, length) {
  const chunks = [];
  for (let i = 0; i < array.length; i += length) {
    chunks.push(array.slice(i, length + i));
  }
  return chunks;
}

export function downloadCsv(filename, csv) {

  // Here, filename = "my_file.csv"
  // Here, csv = [["asset","creation_date","type"], ["www.computablefacts.com","2020-09-07T12:34:29Z","DNS"], ["127.0.0.1","2020-09-07T12:34:29Z","IP"], ...]

  let rows = [];

  csv.forEach(function (row) {
    rows.push(row.join("|"));
  });

  const blob = new Blob([rows.join("\n")], {type: "text/csv;charset=utf-8"});
  const isIE = false || !!document.documentMode;

  if (isIE) {
    window.navigator.msSaveBlob(blob, filename);
  } else {
    const url = window.URL || window.webkitURL;
    const link = url.createObjectURL(blob);
    const a = document.createElement("a");
    a.download = filename;
    a.href = link;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
  }
}

export function escapeHtml(html) {
  const specialChars = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };

  return html.replace(/[&<>"']/g, (char) => specialChars[char]);
}

export function uniqueBy(array, property, compareFn, caseInsensitive) {
  if (!array.every((obj) => obj.hasOwnProperty(property))) {
    throw new Error(`Not all objects in the array have the "${property}" property.`);
  }
  const getKey = (obj) => {
    let value = obj[property];
    if (caseInsensitive && typeof value === 'string') {
      value = value.toLowerCase();
    }
    return compareFn ? compareFn(value) : value;
  };
  const map = new Map(array.map((obj) => [getKey(obj), obj]));
  return Array.from(map.values());
}

export function createNode(tag, html, className) {
  const node = document.createElement(tag);

  if (html) {
    const content = document.createElement('template');
    content.innerHTML = html;
    node.appendChild(content.content.cloneNode(true));
  }

  if (className) {
    node.className = className;
  }

  return node;
}


export function isValidDomainOrIp(value) {
  return isIpV4(value) || isDomain(value) || isIpV4Range(value)
}

export function isIpV4(value){
  const ipv4Regex = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
  return ipv4Regex.test(value);
}

export function isIpV4Range(value){
  const ipv4RangeRegex = /^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\/([1-2]?[0-9]|3[0-2])$/;
  return ipv4RangeRegex.test(value);
}

export function isDomain(value){
  const domainRegex = /^(?:(?:[a-zA-Z0-9](?:[a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}\.?|[a-zA-Z0-9](?:[a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)$/;
  return domainRegex.test(value);
}

