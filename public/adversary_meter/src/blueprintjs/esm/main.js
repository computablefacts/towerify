import React from 'react';
import ReactDOM from 'react-dom';
import { MenuItem, Menu, Button, Slider, RangeSlider, Drawer, Position, Tab, Tabs, SpinnerSize, Spinner, Alignment, Switch, Intent, Toast, Toaster, Card, Icon, Checkbox, FileInput, RadioGroup, InputGroup, NumericInput } from '@blueprintjs/core';
import { Cell, ColumnHeaderCell, Column, Table2, TableLoadingOption } from '@blueprintjs/table';
import { Select2, MultiSelect2, Suggest2 } from '@blueprintjs/select';
import { TimePrecision } from '@blueprintjs/datetime';
import { DateInput2, DateRangeInput2 } from '@blueprintjs/datetime2';
import { sub, add, format, parse } from 'date-fns';

/**
 * @module arrays
 */
const arrays = {};

/**
 * Removes {@link undefined} elements from an array of strings or numbers and returns distinct values.
 * This function does not preserve the elements order.
 *
 * @param {Array<string|number>} array an array of strings.
 * @return {Array<string|number>} an array of distinct strings.
 * @memberOf module:arrays
 */
arrays.distinct = function (array) {
  return array instanceof Array ? Array.from(new Set(array.filter(el => el !== undefined))) : [];
};

/**
 * Removes {@link undefined} elements from an array of objects and return distinct objects i.e. unique by all properties.
 * This function does not preserve the elements order.
 *
 * @param {Array<Object>} array an array of objects.
 * @return {Array<Object>} an array of distinct objects.
 * @memberOf module:arrays
 */
arrays.distinctObjects = function (array) {
  return array instanceof Array ? array
  .filter(el => el !== undefined)
  .filter((el1, index, self) => self.findIndex(el2 => (JSON.stringify(el2) === JSON.stringify(el1))) === index) : [];
};

/**
 * Computes the intersection of two arrays of strings or numbers.
 *
 * @param {Array<string|number>} array1 the first array.
 * @param {Array<string|number>} array2 the second array.
 * @return {Array<string|number>} the intersection of the two arrays.
 * @memberOf module:arrays
 */
arrays.intersect = function (array1, array2) {
  return array1 instanceof Array && array2 instanceof Array ? array1.filter(el => array2.includes(el)) : [];
};

/**
 * @module observers
 */
const observers = {};

/**
 * The Subject type.
 *
 * The observer pattern is a software design pattern in which an object, named the subject,
 * maintains a list of its dependents, called observers, and notifies them automatically of
 * any state changes, usually by calling one of their methods.
 *
 * @memberOf module:observers
 * @constructor
 * @struct
 * @final
 */
observers.Subject = function () {

  const observers = {};

  /**
   * Returns the number of observers for a given message type.
   *
   * @param {string} message  the observed message.
   * @return {number} the number of observers.
   */
  this.numberOfObservers = function (message) {
    if (message) {
      return observers.hasOwnProperty(message) ? observers[message].length : 0;
    }
    let nbObservers = 0;
    for (const msg in observers) {
      nbObservers += observers[msg].length;
    }
    return nbObservers;
  };

  /**
   * Register a callback for a given message type.
   *
   * @param {string} message the message to observe.
   * @param {Function} observer the callback to notify.
   */
  this.register = function (message, observer) {
    if (message && typeof message === 'string' && observer && observer instanceof Function) {
      if (!observers.hasOwnProperty(message)) {
        observers[message] = [];
      }
      observers[message].push(observer);
    }
  };

  /**
   * Unregister a callback for a given message type.
   *
   * @param {string} message the observed message.
   * @param {Function} observer the notified callback.
   */
  this.unregister = function (message, observer) {
    if (message && typeof message === 'string' && observer && observer instanceof Function && observers.hasOwnProperty(
        message)) {
      observers[message] = observers[message].filter(o => o !== observer);
    }
  };

  /**
   * Notify all observers listening to a given message type.
   *
   * @param {string} message the message type.
   * @param {...*} args a list of arguments to pass to each callback.
   */
  this.notify = function (message, ...args) {
    if (message && typeof message === 'string' && args && observers.hasOwnProperty(message)) {
      observers[message].forEach(observer => observer(...args));
    }
  };
};

/**
 * @module widgets
 */
const widgets = {};

/**
 * A skeleton to ease the creation of simple widgets.
 *
 * @memberOf module:widgets
 * @type {widgets.Widget}
 */
widgets.Widget = class {

  /**
   * @param {Element} container the parent element.
   * @constructor
   */
  constructor(container) {
    this.container_ = container;
  }

  /**
   * Returns the widget parent element.
   *
   * @return {Element} the parent element.
   * @name container
   * @function
   * @public
   */
  get container() {
    return this.container_;
  }

  /**
   * Sets the widget parent element.
   *
   * @param container
   * @name container
   * @function
   * @public
   */
  set container(container) {
    this.container_ = container;
    this.render();
  }

  /**
   * If the current widget creates more widgets, register them using this method.
   * It allows the current widget to properly removes its children from the DOM.
   *
   * @param {Widget} widget the widget to register.
   * @name register
   * @function
   * @protected
   */
  register(widget) {
    if (widget) {
      if (!this.widgets_) {
        this.widgets_ = [];
      }
      this.widgets_.push(widget);
    }
  }

  /**
   * In order to avoid a memory leak, properly remove the widget from the DOM.
   *
   * @name destroy
   * @function
   * @public
   */
  destroy() {

    if (this.widgets_) {

      // Remove registered widgets
      for (let i = 0; i < this.widgets_.length; i++) {
        this.widgets_[i].destroy();
      }
      this.widgets_ = [];
    }

    // Empty the container
    while (this.container.firstChild) {
      this.container.removeChild(this.container.firstChild);
    }
  }

  /**
   * Renders the widget.
   *
   * @name render
   * @function
   * @public
   */
  render() {
    this.destroy();
    const element = this._newElement();
    if (element) {
      this.container.appendChild(element);
    }
  }

  /**
   * Initializes the widget.
   *
   * @return {Element|null}
   * @name _newElement
   * @function
   * @protected
   */
  _newElement() {
    return null;
  }
};

/**
 * @module strings
 */
const strings = {};

/**
 * Escapes characters with special meaning in {@link RegExp}.
 *
 * @param {string} str the string to escape.
 * @return {string} the escaped string.
 * @memberOf module:strings
 */
strings.escapeCharactersWithSpecialMeaningInRegExp = function (str) {
  return str ? ('' + str).replace(/[.*+?^${}()|[\]\\]/g, '\\$&' /* the whole matched string */) : '';
};

/**
 * Converts a string to a {@link RegExp} literal.
 *
 * @param {string} str the string to convert.
 * @param {string} flags the flags of the regular expression.
 * @return {RegExp} a {@link RegExp}.
 * @memberOf module:strings
 */
strings.toRegExp = function (str, flags) {

  // As of 2020-01-06, the DOT_ALL flag is not available on Firefox
  // If truly needed, use [^]* that reads "match any character that is not nothing"
  const newFlags = flags ? flags : 'im';
  const escapedString = strings.escapeCharactersWithSpecialMeaningInRegExp(str).split(/[\s\u00a0]+/).join(
    '(\\s|\u00a0)*');
  return new RegExp(escapedString, newFlags);
};

/**
 * Removes diacritical marks from a string.
 *
 * @param {string} str the string to clean.
 * @param {boolean} preserveStringLength true iif the original string length must be preserved.
 * @return {string} the cleaned text i.e. without diacritical marks.
 * @memberOf module:strings
 * @preserve The code is extracted from https://web.archive.org/web/20121231230126/http://lehelk.com:80/2011/05/06/script-to-remove-diacritics/.
 */
strings.removeDiacritics = function (str, preserveStringLength) {

  str = str ? '' + str : '';

  const diacritics = [{
    base: 'A',
    letters: /[\u0041\u24B6\uFF21\u00C0\u00C1\u00C2\u1EA6\u1EA4\u1EAA\u1EA8\u00C3\u0100\u0102\u1EB0\u1EAE\u1EB4\u1EB2\u0226\u01E0\u00C4\u01DE\u1EA2\u00C5\u01FA\u01CD\u0200\u0202\u1EA0\u1EAC\u1EB6\u1E00\u0104\u023A\u2C6F]/g
  }, {
    base: 'B', letters: /[\u0042\u24B7\uFF22\u1E02\u1E04\u1E06\u0243\u0182\u0181]/g
  }, {
    base: 'C', letters: /[\u0043\u24B8\uFF23\u0106\u0108\u010A\u010C\u00C7\u1E08\u0187\u023B\uA73E]/g
  }, {
    base: 'D', letters: /[\u0044\u24B9\uFF24\u1E0A\u010E\u1E0C\u1E10\u1E12\u1E0E\u0110\u018B\u018A\u0189\uA779]/g
  }, {
    base: 'E',
    letters: /[\u0045\u24BA\uFF25\u00C8\u00C9\u00CA\u1EC0\u1EBE\u1EC4\u1EC2\u1EBC\u0112\u1E14\u1E16\u0114\u0116\u00CB\u1EBA\u011A\u0204\u0206\u1EB8\u1EC6\u0228\u1E1C\u0118\u1E18\u1E1A\u0190\u018E]/g
  }, {
    base: 'F', letters: /[\u0046\u24BB\uFF26\u1E1E\u0191\uA77B]/g
  }, {
    base: 'G', letters: /[\u0047\u24BC\uFF27\u01F4\u011C\u1E20\u011E\u0120\u01E6\u0122\u01E4\u0193\uA7A0\uA77D\uA77E]/g
  }, {
    base: 'H', letters: /[\u0048\u24BD\uFF28\u0124\u1E22\u1E26\u021E\u1E24\u1E28\u1E2A\u0126\u2C67\u2C75\uA78D]/g
  }, {
    base: 'I',
    letters: /[\u0049\u24BE\uFF29\u00CC\u00CD\u00CE\u0128\u012A\u012C\u0130\u00CF\u1E2E\u1EC8\u01CF\u0208\u020A\u1ECA\u012E\u1E2C\u0197]/g
  }, {base: 'J', letters: /[\u004A\u24BF\uFF2A\u0134\u0248]/g}, {
    base: 'K', letters: /[\u004B\u24C0\uFF2B\u1E30\u01E8\u1E32\u0136\u1E34\u0198\u2C69\uA740\uA742\uA744\uA7A2]/g
  }, {
    base: 'L',
    letters: /[\u004C\u24C1\uFF2C\u013F\u0139\u013D\u1E36\u1E38\u013B\u1E3C\u1E3A\u0141\u023D\u2C62\u2C60\uA748\uA746\uA780]/g
  }, {
    base: 'M', letters: /[\u004D\u24C2\uFF2D\u1E3E\u1E40\u1E42\u2C6E\u019C]/g
  }, {
    base: 'N',
    letters: /[\u004E\u24C3\uFF2E\u01F8\u0143\u00D1\u1E44\u0147\u1E46\u0145\u1E4A\u1E48\u0220\u019D\uA790\uA7A4]/g
  }, {
    base: 'O',
    letters: /[\u004F\u24C4\uFF2F\u00D2\u00D3\u00D4\u1ED2\u1ED0\u1ED6\u1ED4\u00D5\u1E4C\u022C\u1E4E\u014C\u1E50\u1E52\u014E\u022E\u0230\u00D6\u022A\u1ECE\u0150\u01D1\u020C\u020E\u01A0\u1EDC\u1EDA\u1EE0\u1EDE\u1EE2\u1ECC\u1ED8\u01EA\u01EC\u00D8\u01FE\u0186\u019F\uA74A\uA74C]/g
  }, {
    base: 'P', letters: /[\u0050\u24C5\uFF30\u1E54\u1E56\u01A4\u2C63\uA750\uA752\uA754]/g
  }, {
    base: 'Q', letters: /[\u0051\u24C6\uFF31\uA756\uA758\u024A]/g
  }, {
    base: 'R',
    letters: /[\u0052\u24C7\uFF32\u0154\u1E58\u0158\u0210\u0212\u1E5A\u1E5C\u0156\u1E5E\u024C\u2C64\uA75A\uA7A6\uA782]/g
  }, {
    base: 'S',
    letters: /[\u0053\u24C8\uFF33\u1E9E\u015A\u1E64\u015C\u1E60\u0160\u1E66\u1E62\u1E68\u0218\u015E\u2C7E\uA7A8\uA784]/g
  }, {
    base: 'T', letters: /[\u0054\u24C9\uFF34\u1E6A\u0164\u1E6C\u021A\u0162\u1E70\u1E6E\u0166\u01AC\u01AE\u023E\uA786]/g
  }, {
    base: 'U',
    letters: /[\u0055\u24CA\uFF35\u00D9\u00DA\u00DB\u0168\u1E78\u016A\u1E7A\u016C\u00DC\u01DB\u01D7\u01D5\u01D9\u1EE6\u016E\u0170\u01D3\u0214\u0216\u01AF\u1EEA\u1EE8\u1EEE\u1EEC\u1EF0\u1EE4\u1E72\u0172\u1E76\u1E74\u0244]/g
  }, {
    base: 'V', letters: /[\u0056\u24CB\uFF36\u1E7C\u1E7E\u01B2\uA75E\u0245]/g
  }, {
    base: 'W', letters: /[\u0057\u24CC\uFF37\u1E80\u1E82\u0174\u1E86\u1E84\u1E88\u2C72]/g
  }, {base: 'X', letters: /[\u0058\u24CD\uFF38\u1E8A\u1E8C]/g}, {
    base: 'Y', letters: /[\u0059\u24CE\uFF39\u1EF2\u00DD\u0176\u1EF8\u0232\u1E8E\u0178\u1EF6\u1EF4\u01B3\u024E\u1EFE]/g
  }, {
    base: 'Z', letters: /[\u005A\u24CF\uFF3A\u0179\u1E90\u017B\u017D\u1E92\u1E94\u01B5\u0224\u2C7F\u2C6B\uA762]/g
  }, {
    base: 'a',
    letters: /[\u0061\u24D0\uFF41\u1E9A\u00E0\u00E1\u00E2\u1EA7\u1EA5\u1EAB\u1EA9\u00E3\u0101\u0103\u1EB1\u1EAF\u1EB5\u1EB3\u0227\u01E1\u00E4\u01DF\u1EA3\u00E5\u01FB\u01CE\u0201\u0203\u1EA1\u1EAD\u1EB7\u1E01\u0105\u2C65\u0250]/g
  }, {
    base: 'b', letters: /[\u0062\u24D1\uFF42\u1E03\u1E05\u1E07\u0180\u0183\u0253]/g
  }, {
    base: 'c', letters: /[\u0063\u24D2\uFF43\u0107\u0109\u010B\u010D\u00E7\u1E09\u0188\u023C\uA73F\u2184]/g
  }, {
    base: 'd', letters: /[\u0064\u24D3\uFF44\u1E0B\u010F\u1E0D\u1E11\u1E13\u1E0F\u0111\u018C\u0256\u0257\uA77A]/g
  }, {
    base: 'e',
    letters: /[\u0065\u24D4\uFF45\u00E8\u00E9\u00EA\u1EC1\u1EBF\u1EC5\u1EC3\u1EBD\u0113\u1E15\u1E17\u0115\u0117\u00EB\u1EBB\u011B\u0205\u0207\u1EB9\u1EC7\u0229\u1E1D\u0119\u1E19\u1E1B\u0247\u025B\u01DD]/g
  }, {
    base: 'f', letters: /[\u0066\u24D5\uFF46\u1E1F\u0192\uA77C]/g
  }, {
    base: 'g', letters: /[\u0067\u24D6\uFF47\u01F5\u011D\u1E21\u011F\u0121\u01E7\u0123\u01E5\u0260\uA7A1\u1D79\uA77F]/g
  }, {
    base: 'h', letters: /[\u0068\u24D7\uFF48\u0125\u1E23\u1E27\u021F\u1E25\u1E29\u1E2B\u1E96\u0127\u2C68\u2C76\u0265]/g
  }, {
    base: 'i',
    letters: /[\u0069\u24D8\uFF49\u00EC\u00ED\u00EE\u0129\u012B\u012D\u00EF\u1E2F\u1EC9\u01D0\u0209\u020B\u1ECB\u012F\u1E2D\u0268\u0131]/g
  }, {
    base: 'j', letters: /[\u006A\u24D9\uFF4A\u0135\u01F0\u0249]/g
  }, {
    base: 'k', letters: /[\u006B\u24DA\uFF4B\u1E31\u01E9\u1E33\u0137\u1E35\u0199\u2C6A\uA741\uA743\uA745\uA7A3]/g
  }, {
    base: 'l',
    letters: /[\u006C\u24DB\uFF4C\u0140\u013A\u013E\u1E37\u1E39\u013C\u1E3D\u1E3B\u017F\u0142\u019A\u026B\u2C61\uA749\uA781\uA747]/g
  }, {
    base: 'm', letters: /[\u006D\u24DC\uFF4D\u1E3F\u1E41\u1E43\u0271\u026F]/g
  }, {
    base: 'n',
    letters: /[\u006E\u24DD\uFF4E\u01F9\u0144\u00F1\u1E45\u0148\u1E47\u0146\u1E4B\u1E49\u019E\u0272\u0149\uA791\uA7A5]/g
  }, {
    base: 'o',
    letters: /[\u006F\u24DE\uFF4F\u00F2\u00F3\u00F4\u1ED3\u1ED1\u1ED7\u1ED5\u00F5\u1E4D\u022D\u1E4F\u014D\u1E51\u1E53\u014F\u022F\u0231\u00F6\u022B\u1ECF\u0151\u01D2\u020D\u020F\u01A1\u1EDD\u1EDB\u1EE1\u1EDF\u1EE3\u1ECD\u1ED9\u01EB\u01ED\u00F8\u01FF\u0254\uA74B\uA74D\u0275]/g
  }, {
    base: 'p', letters: /[\u0070\u24DF\uFF50\u1E55\u1E57\u01A5\u1D7D\uA751\uA753\uA755]/g
  }, {
    base: 'q', letters: /[\u0071\u24E0\uFF51\u024B\uA757\uA759]/g
  }, {
    base: 'r',
    letters: /[\u0072\u24E1\uFF52\u0155\u1E59\u0159\u0211\u0213\u1E5B\u1E5D\u0157\u1E5F\u024D\u027D\uA75B\uA7A7\uA783]/g
  }, {
    base: 's',
    letters: /[\u0073\u24E2\uFF53\u00DF\u015B\u1E65\u015D\u1E61\u0161\u1E67\u1E63\u1E69\u0219\u015F\u023F\uA7A9\uA785\u1E9B]/g
  }, {
    base: 't',
    letters: /[\u0074\u24E3\uFF54\u1E6B\u1E97\u0165\u1E6D\u021B\u0163\u1E71\u1E6F\u0167\u01AD\u0288\u2C66\uA787]/g
  }, {
    base: 'u',
    letters: /[\u0075\u24E4\uFF55\u00F9\u00FA\u00FB\u0169\u1E79\u016B\u1E7B\u016D\u00FC\u01DC\u01D8\u01D6\u01DA\u1EE7\u016F\u0171\u01D4\u0215\u0217\u01B0\u1EEB\u1EE9\u1EEF\u1EED\u1EF1\u1EE5\u1E73\u0173\u1E77\u1E75\u0289]/g
  }, {
    base: 'v', letters: /[\u0076\u24E5\uFF56\u1E7D\u1E7F\u028B\uA75F\u028C]/g
  }, {
    base: 'w', letters: /[\u0077\u24E6\uFF57\u1E81\u1E83\u0175\u1E87\u1E85\u1E98\u1E89\u2C73]/g
  }, {base: 'x', letters: /[\u0078\u24E7\uFF58\u1E8B\u1E8D]/g}, {
    base: 'y',
    letters: /[\u0079\u24E8\uFF59\u1EF3\u00FD\u0177\u1EF9\u0233\u1E8F\u00FF\u1EF7\u1E99\u1EF5\u01B4\u024F\u1EFF]/g
  }, {
    base: 'z', letters: /[\u007A\u24E9\uFF5A\u017A\u1E91\u017C\u017E\u1E93\u1E95\u01B6\u0225\u0240\u2C6C\uA763]/g
  }];

  if (preserveStringLength === undefined || preserveStringLength === true) {
    diacritics.push({base: 'AA', letters: /\uA732/g}, {base: 'AE', letters: /[\u00C6\u01FC\u01E2]/g},
      {base: 'AO', letters: /\uA734/g}, {base: 'AU', letters: /\uA736/g}, {base: 'AV', letters: /[\uA738\uA73A]/g},
      {base: 'AY', letters: /\uA73C/g}, {base: 'DZ', letters: /[\u01F1\u01C4]/g},
      {base: 'Dz', letters: /[\u01F2\u01C5]/g}, {base: 'LJ', letters: /\u01C7/g}, {base: 'Lj', letters: /\u01C8/g},
      {base: 'NJ', letters: /\u01CA/g}, {base: 'Nj', letters: /\u01CB/g}, {base: 'OI', letters: /\u01A2/g},
      {base: 'OO', letters: /\uA74E/g}, {base: 'OU', letters: /\u0222/g}, {base: 'TZ', letters: /\uA728/g},
      {base: 'VY', letters: /\uA760/g}, {base: 'aa', letters: /\uA733/g},
      {base: 'ae', letters: /[\u00E6\u01FD\u01E3]/g}, {base: 'ao', letters: /\uA735/g},
      {base: 'au', letters: /\uA737/g}, {base: 'av', letters: /[\uA739\uA73B]/g}, {base: 'ay', letters: /\uA73D/g},
      {base: 'dz', letters: /[\u01F3\u01C6]/g}, {base: 'hv', letters: /\u0195/g}, {base: 'lj', letters: /\u01C9/g},
      {base: 'nj', letters: /\u01CC/g}, {base: 'oi', letters: /\u01A3/g}, {base: 'ou', letters: /\u0223/g},
      {base: 'oo', letters: /\uA74F/g}, {base: 'tz', letters: /\uA729/g}, {base: 'vy', letters: /\uA761/g});
  }

  for (let i = 0; i < diacritics.length; i++) {
    str = str.replace(diacritics[i].letters, diacritics[i].base);
  }
  return str;
};

/**
 * The Pattern type.
 *
 * @param {RegExp} regexp the pattern to match.
 * @param {string} color the highlight color as a hexadecimal string.
 * @memberOf module:strings
 * @constructor
 * @struct
 * @final
 */
strings.Pattern = function (regexp, color) {
  this.regexp = regexp;
  this.color = color;
};

/**
 * The Highlight type.
 *
 * @param {string} matchedText the matched text fragment.
 * @param {number} matchedPage the page number (1-based) from which the snippet was extracted.
 * @param {string} rawSnippet a snippet of text surrounding the matched text fragment (about 300 characters).
 * @param {string} highlightedSnippet a snippet of text surrounding the matched and highlighted text fragment (about 300 characters).
 * @memberOf module:strings
 * @constructor
 * @struct
 * @final
 */
strings.Highlight = function (matchedText, matchedPage, rawSnippet, highlightedSnippet) {
  this.matchedText = matchedText;
  this.matchedPage = matchedPage;
  this.rawSnippet = rawSnippet;
  this.highlightedSnippet = highlightedSnippet;
};

/**
 * The HighlightedText type.
 *
 * @param {string} text the whole text, highlighted.
 * @param {Array<strings.Highlight>} snippets the snippet associated with each highlighted text fragment.
 * @memberOf module:strings
 * @constructor
 * @struct
 * @final
 */
strings.HighlightedText = function (text, snippets) {
  this.text = text;
  this.snippets = snippets;
};

/**
 * Highlights all occurrences of a given set of patterns in a string.
 *
 * @param {string} text the text to highlight.
 * @param {Array<strings.Pattern>} patterns a set of patterns to match and highlight.
 * @return {strings.HighlightedText} the highlighted text.
 * @memberOf module:strings
 */
strings.highlight = function (text, patterns) {

  text = text ? '' + text : '';

  if (!patterns || patterns.length <= 0 || strings.isNullOrBlank(text)) {
    return new strings.HighlightedText(text, []);
  }

  let highlightedText = text;
  text = strings.removeDiacritics(text, true);

  if (text.length !== highlightedText.length) {
    highlightedText = text;
  }
  const highlights = patterns.flatMap(pattern => {

    const matcher = pattern.regexp;
    const matches = [];
    let match = null;

    while (match = matcher.exec(text)) {
      matches.push({
        start: match.index, end: match.index + match[0].length, color: pattern.color
      });
    }
    return matches;
  }).sort((a, b) => {
    if (a.start < b.start) {
      return 1;
    }
    if (a.start > b.start) {
      return -1;
    }
    return 0;
  }).map(position => {

    // TODO : deal with overlaps?
    const prefix = highlightedText.substring(0, position.start);
    const infix = highlightedText.substring(position.start, position.end);
    const suffix = highlightedText.substring(position.end);

    highlightedText = `${prefix}<mark style="border-radius:3px;background:${position.color}">${infix}</mark>${suffix}`;

    const begin = Math.max(0, prefix.length - 50);
    const end = Math.min(150, suffix.length);
    const rawSnippet = `${prefix.substring(begin)}${infix}${suffix.substring(0, end)}`;
    let highlightedSnippet = `${prefix.substring(
      begin)}<mark style="border-radius:3px;background:${position.color}">${infix}</mark>${suffix.substring(0, end)}`;
    const pages = prefix.split('\f' /* page separator */).map((page, index) => index);
    const beginMark = highlightedSnippet.lastIndexOf('<m');
    const endMark = highlightedSnippet.lastIndexOf('</mark>');

    if (beginMark && (!endMark || beginMark > endMark)) {
      highlightedSnippet = highlightedSnippet.substring(0, beginMark);
    }
    return new strings.Highlight(infix, pages.length, rawSnippet, highlightedSnippet);
  });
  return new strings.HighlightedText(highlightedText, highlights);
};

/**
 * Returns true iif a string is either null or blank, false otherwise.
 *
 * @param {string} str the string to check.
 * @return {boolean} true if the string is null or blank, false otherwise.
 * @memberOf module:strings
 */
strings.isNullOrBlank = function (str) {
  return !(typeof str === 'string' && str.trim() !== '');
};

/**
 * Prepends '0' to a string or number until a given length is reached.
 *
 * @param {string|number} str a string or number.
 * @param {number} targetLength the string target length.
 * @return {string} a padded string.
 * @memberOf module:strings
 */
strings.pad = function (str, targetLength) {
  return (str ? '' + str : '').padStart(targetLength, '0');
};

/**
 * Removes all '0' from the beginning of a string.
 *
 * @param {string} str a string.
 * @return {string} an un-padded string.
 * @memberOf module:strings
 */
strings.unpad = function (str) {
  str = str ? '' + str : '';
  let i = 0;
  for (; i < str.length && str[i] === '0'; i++) {
  }
  return str.substring(i);
};

/**
 * Checks if a string represents a numeric value.
 *
 * @param {string} str the string to check.
 * @return {boolean} true if the string is a number, false otherwise.
 * @memberOf module:strings
 * @preserve The code is extracted from https://stackoverflow.com/a/175787.
 */
strings.isNumeric = function (str) {
  return typeof str === 'string' ? !isNaN(str) && !isNaN(parseFloat(str)) : false;
};

/**
 * Formats null or blank values.
 *
 * @param {string|number} str the value to format.
 * @param {string} defaultValue the string to return if the value is either null or empty.
 * @return {string} the formatted value.
 */
strings.formatNullOrBlank = function (str, defaultValue) {
  str = str ? '' + str : '';
  return strings.isNullOrBlank(str) ? defaultValue : str;
};

/**
 * Returns true iif a string starts with 'MASKED_', false otherwise.
 *
 * @param {string} str the string to check.
 * @return {boolean} true iif the string starts with 'MASKED_', false otherwise.
 */
strings.isMasked = function (str) {
  return typeof str === 'string' ? str.trim().toUpperCase().startsWith('MASKED_') : false;
};

/**
 * Convert a string from camel case to snake case.
 *
 * @param {string} str the string in camel case.
 * @returns {string} the string in snake case.
 */
strings.camelToSnakeCase = function (str) {
  return typeof str === 'string' ? str.replace(/[A-Z]/g,
    (letter, idx) => idx === 0 ? letter.toLowerCase() : `_${letter.toLowerCase()}`) : null;
};

/**
 * Convert a string from snake case to camel case.
 *
 * @param {string} str the string in snake case.
 * @returns {string} the string in camel case.
 */
strings.snakeCaseToCamelCase = function (str) {
  return typeof str === 'string' ? str.replace(/_[a-z]/g,
    (group, idx) => idx === 0 ? group.replace('_', '') : group.toUpperCase().replace('_', '')) : null;
};

/**
 * @module blueprintjs
 */
const blueprintjs = {};

/**
 * Base class that deals with injecting the common styles and scripts.
 *
 * @memberOf module:blueprintjs
 * @type {blueprintjs.Blueprintjs}
 */
blueprintjs.Blueprintjs = class extends widgets.Widget {

  /**
   * @param {Element} container the parent element.
   * @constructor
   */
  constructor(container) {
    super(container);
  }

  /**
   * Populate a DOM element with Blueprintjs components.
   *
   * @param template the DOM element.
   * @param objs the components.
   */
  static populate(template, objs) {
    objs.forEach(obj => blueprintjs.Blueprintjs.component(template, obj));
  }

  /**
   * Create a Blueprintjs component from a JSON object.
   *
   * @param template the DOM element where the component will be added.
   * @param obj the component properties.
   */
  static component(template, obj) {

    // {
    //    type: '<string>',
    //    container: '<string>',
    //    el: null,
    //    ...
    // }
    const container = template.querySelector(`#${obj.container}`);
    if (!container) {
      return obj;
    }
    switch (obj.type) {
      case 'Table': {
        obj.el = new blueprintjs.MinimalTable(container);
        break;
      }
      case 'Select': {
        const itemToText = obj.item_to_text;
        const itemToLabel = obj.item_to_label;
        const itemPredicate = obj.item_predicate;
        const itemCreate = obj.item_create;
        obj.el = new blueprintjs.MinimalSelect(container, itemToText, itemToLabel, itemPredicate, itemCreate);
        break;
      }
      case 'Slider': {
        const min = obj.min;
        const max = obj.max;
        const increment = obj.increment;
        const displayIncrement = obj.display_increment;
        obj.el = new blueprintjs.MinimalSlider(container, min, max, increment, displayIncrement);
        break;
      }
      case 'RangeSlider': {
        const min = obj.min;
        const max = obj.max;
        const increment = obj.increment;
        const displayIncrement = obj.display_increment;
        const defaultMinValue = obj.default_min_value;
        const defaultMaxValue = obj.default_max_value;
        obj.el = new blueprintjs.MinimalRangeSlider(container, min, max, increment, displayIncrement, defaultMinValue,
          defaultMaxValue);
        break;
      }
      case 'Drawer': {
        const width = obj.width;
        obj.el = new blueprintjs.MinimalDrawer(container, width);
        break;
      }
      case 'Tabs': {
        obj.el = new blueprintjs.MinimalTabs(container);
        break;
      }
      case 'Spinner': {
        const size = obj.size;
        obj.el = new blueprintjs.MinimalSpinner(container, size);
        break;
      }
      case 'Switch': {
        const checked = obj.checked;
        const label = obj.label;
        const labelPosition = obj.label_position;
        const labelChecked = obj.label_checked;
        const labelUnchecked = obj.label_unchecked;
        obj.el = new blueprintjs.MinimalSwitch(container, checked, label, labelPosition, labelChecked, labelUnchecked);
        break;
      }
      case 'Toaster': {
        obj.el = new blueprintjs.MinimalToaster(container);
        break;
      }
      case 'Card': {
        const body = obj.body;
        obj.el = new blueprintjs.MinimalCard(container, body);
        break;
      }
      case 'Icon': {
        const icon = obj.icon;
        const intent = obj.intent;
        obj.el = new blueprintjs.MinimalIcon(container, icon, intent);
        break;
      }
      case 'Checkbox': {
        const checked = obj.checked;
        const label = obj.label;
        const labelPosition = obj.label_position;
        obj.el = new blueprintjs.MinimalCheckbox(container, checked, label, labelPosition);
        break;
      }
      case 'Date': {
        const format = obj.format;
        const minDate = obj.min_date;
        const maxDate = obj.max_date;
        obj.el = new blueprintjs.MinimalDate(container, format, minDate, maxDate);
        break;
      }
      case 'Datetime': {
        const format = obj.format;
        const minDate = obj.min_date;
        const maxDate = obj.max_date;
        const timePrecision = obj.default_precision;
        const defaultTimezone = obj.default_timezone;
        obj.el = new blueprintjs.MinimalDatetime(container, format, minDate, maxDate, timePrecision, defaultTimezone);
        break;
      }
      case 'DateRange': {
        const format = obj.format;
        const minDate = obj.min_date;
        const maxDate = obj.max_date;
        obj.el = new blueprintjs.MinimalDateRange(container, format, minDate, maxDate);
        break;
      }
      case 'MultiSelect': {
        const itemToText = obj.item_to_text;
        const itemToLabel = obj.item_to_label;
        const itemToTag = obj.item_to_tag;
        const itemPredicate = obj.item_predicate;
        const itemCreate = obj.item_create;
        obj.el = new blueprintjs.MinimalMultiSelect(container, itemToText, itemToLabel, itemToTag, itemPredicate,
          itemCreate);
        break;
      }
      case 'Suggest': {
        const itemToText = obj.item_to_text;
        const itemToLabel = obj.item_to_label;
        const itemPredicate = obj.item_predicate;
        obj.el = new blueprintjs.MinimalSuggest(container, itemToText, itemToLabel, itemPredicate);
        break;
      }
      case 'FileInput': {
        const multiple = obj.multiple;
        obj.el = new blueprintjs.MinimalFileInput(container, multiple);
        break;
      }
      case 'RadioGroup': {
        const label = obj.label;
        const inline = obj.inline;
        obj.el = new blueprintjs.MinimalRadioGroup(container, label, inline);
        break;
      }
      case 'TextInput': {
        const defaultValue = obj.default_value;
        const icon = obj.icon;
        const intent = obj.intent;
        obj.el = new blueprintjs.MinimalTextInput(container, defaultValue, icon, intent);
        break;
      }
      case 'NumericInput': {
        const min = obj.min;
        const max = obj.max;
        const increment = obj.increment;
        const defaultValue = obj.default_value;
        const icon = obj.icon;
        const intent = obj.intent;
        obj.el = new blueprintjs.MinimalNumericInput(container, min, max, increment, defaultValue, icon, intent);
        break;
      }
      case 'Button': {
        const label = obj.label;
        const labelPosition = obj.label_position;
        const leftIcon = obj.left_icon;
        const rightIcon = obj.right_icon;
        const intent = obj.intent;
        obj.el = new blueprintjs.MinimalButton(container, label, labelPosition, leftIcon, rightIcon, intent);
        break;
      }
      default:
        obj.el = null;
        break;
    }
    if (obj.el) {
      for (let key in obj) {
        if (key === 'type' || key === 'container' || key === 'el') {
          continue;
        }
        const prop = strings.snakeCaseToCamelCase(key);
        const desc = Object.getOwnPropertyDescriptor(Object.getPrototypeOf(obj.el), prop);
        // console.log(prop, desc);
        if (desc) {
          if (desc.set) { // setter
            obj.el[prop] = obj[key];
          } else if (desc.writable) { // function
            obj.el[prop](obj[key]);
          }
        }
      }
    }
    return obj;
  }

  /**
   * In order to avoid a memory leak, properly remove the component from the DOM.
   *
   * @name destroy
   * @function
   * @protected
   */
  destroy() {

    if (this.widgets_) {

      // Remove registered widgets
      for (let i = 0; i < this.widgets_.length; i++) {
        this.widgets_[i].destroy();
      }
      this.widgets_ = [];
    }

    ReactDOM.unmountComponentAtNode(this.container);
  }

  /**
   * Renders the component.
   *
   * @name render
   * @function
   * @protected
   */
  render() {
    const element = this._newElement();
    if (element) {
      ReactDOM.render(element, this.container);
    }
  }

  /**
   * Initializes the component.
   *
   * @return {ReactElement|null}
   * @name _newElement
   * @function
   * @protected
   */
  _newElement() {
    return null;
  }
};

/**
 * A skeleton to ease the creation of a minimal Blueprintjs table component.
 *
 * @memberOf module:blueprintjs
 * @extends {blueprintjs.Blueprintjs}
 * @type {blueprintjs.MinimalTable}
 */
blueprintjs.MinimalTable = class extends blueprintjs.Blueprintjs {

  /**
   * @param {Element} container the parent element.
   * @param {function(number, number, *): ReactElement} cellRenderer a function in charge of rendering a single cell (optional).
   * @constructor
   */
  constructor(container, cellRenderer) {
    super(container);
    this.cellRenderer_ = cellRenderer;
    this.observers_ = new observers.Subject();
    this.columns_ = [];
    this.columnWidths_ = [];
    this.columnTypes_ = [];
    this.rows_ = [];
    this.loadingOptions_ = [];
    this.render();
  }

  get columns() {
    return this.columns_;
  }

  set columns(value) {
    this.columns_ = value;
    this.render();
  }

  get columnTypes() {
    return this.columnTypes_;
  }

  set columnTypes(values) {
    this.columnTypes_ = values;
    this.render();
  }

  get columnWidths() {
    return this.columnWidths_;
  }

  set columnWidths(values) {
    this.columnWidths_ = values;
    this.render();
  }

  get rows() {
    return this.rows_;
  }

  set rows(values) {
    this.rows_ = values;
    this.render();
  }

  get loadingOptions() {
    return this.loadingOptions_;
  }

  set loadingOptions(values) {
    this.loadingOptions_ = values;
    this.render();
  }

  /**
   * Listen to the `sort` event.
   *
   * @param {function(string, string): void} callback the callback to call when the event is triggered.
   * @name onSortColumn
   * @function
   * @public
   */
  onSortColumn(callback) {
    this.observers_.register('sort', (column, order) => {
      // console.log('Sort ' + order + ' is ' + column);
      if (callback) {
        callback(column, order);
      }
    });
  }

  /**
   * Listen to the `fetch-next-rows` event.
   *
   * @param {function(number): void} callback the callback to call when the event is triggered.
   * @name onFetchNextRows
   * @function
   * @public
   */
  onFetchNextRows(callback) {
    this.observers_.register('fetch-next-rows', (nextRow) => {
      // console.log('Next row is ' + nextRow);
      if (callback) {
        callback(nextRow);
      }
    });
  }

  /**
   * Listen to the `selection-change` event.
   *
   * @param {function(Array<Object>): void} callback the callback to call when the event is triggered.
   * @name onSelectionChange
   * @function
   * @public
   */
  onSelectionChange(callback) {
    this.observers_.register('selection-change', (regions) => {
      // console.log('Selected regions are ', regions);
      if (callback) {

        const cells = [];

        for (let i = 0; i < regions.length; i++) {

          if (!regions[i].hasOwnProperty('rows')) {
            continue; // ignore the region if a whole column has been selected
          }
          if (!regions[i].hasOwnProperty('cols')) {
            continue; // ignore the region if a whole row has been selected
          }

          const rows = regions[i].rows;
          const columns = regions[i].cols;

          for (let j = rows[0]; j <= rows[1]; j++) {
            for (let k = columns[0]; k <= columns[1]; k++) {
              cells.push({row_idx: j, col_idx: k, value: this.rows[j][k]});
            }
          }
        }
        callback(cells);
      }
    });
  }

  _newCell(self, rowIdx, colIdx) {
    return self.cellRenderer_ ? self.cellRenderer_(rowIdx, colIdx, self.rows[rowIdx][colIdx]) : React.createElement(
      Cell, {
        rowIndex: rowIdx, columnIndex: colIdx, style: {
          'text-align': self.columnTypes[colIdx] === 'number' ? 'right' : 'left'
        }, children: React.createElement('div', {}, self.rows[rowIdx][colIdx]),
      });
  }

  _newColumnHeader(self, column) {
    return React.createElement(ColumnHeaderCell, {
      name: column, menuRenderer: () => {

        // Menu item for sorting the column in ascending order
        const menuItemSortAsc = React.createElement(MenuItem, {
          icon: 'sort-asc', text: 'Sort Asc', onClick: () => self.observers_.notify('sort', column, 'ASC'),
        });

        // Menu item for sorting the column in descending order
        const menuItemSortDesc = React.createElement(MenuItem, {
          icon: 'sort-desc', text: 'Sort Desc', onClick: () => self.observers_.notify('sort', column, 'DESC'),
        });

        return React.createElement(Menu, {
          children: [menuItemSortAsc, menuItemSortDesc]
        });
      }
    });
  }

  _newColumn(self, column) {
    return React.createElement(Column, {
      name: column,
      cellRenderer: (rowIdx, colIdx) => self._newCell(self, rowIdx, colIdx),
      columnHeaderCellRenderer: () => self._newColumnHeader(self, column),
    });
  }

  _newElement() {
    return React.createElement(Table2, {
      numRows: this.rows.length,
      children: this.columns.map(column => this._newColumn(this, column)),
      enableColumnReordering: true,
      loadingOptions: this.loadingOptions,
      columnWidths: this.columnWidths.length <= 0 ? null : this.columnWidths,
      onSelection: (regions) => {
        this.observers_.notify('selection-change', regions);
      },
      onVisibleCellsChange: (rowIndex, columnIndex) => {
        if (rowIndex.rowIndexEnd + 1 >= this.rows.length) {
          this.observers_.notify('fetch-next-rows', this.rows.length);
        }
      },
      onColumnsReordered: (oldIndex, newIndex, length) => {

        this.loadingOptions = [TableLoadingOption.CELLS];

        // First, reorder the rows header
        const oldColumnsOrder = this.columns;
        const newColumnsOrder = [];

        const oldColumnTypes = this.columnTypes;
        const newColumnTypes = [];

        for (let i = 0; i < oldColumnsOrder.length; i++) {
          if (!(oldIndex <= i && i < oldIndex + length)) {
            newColumnsOrder.push(oldColumnsOrder[i]);
            newColumnTypes.push(oldColumnTypes[i]);
          }
        }
        for (let k = oldIndex; k < oldIndex + length; k++) {
          newColumnsOrder.splice(newIndex + k - oldIndex, 0, oldColumnsOrder[k]);
          newColumnTypes.splice(newIndex + k - oldIndex, 0, oldColumnTypes[k]);
        }

        // console.log('Previous column order was [' + oldColumnsOrder.join(', ') + ']');
        // console.log('New column order is [' + newColumnsOrder.join(', ') + ']');

        // console.log('Previous column types were [' + oldColumnTypes.join(', ') + ']');
        // console.log('New column types is [' + newColumnTypes.join(', ') + ']');

        // Then, reorder the rows data
        const oldColumnsIndex = {};
        const newColumnsIndex = {};

        for (let i = 0; i < oldColumnsOrder.length; i++) {
          oldColumnsIndex[oldColumnsOrder[i]] = i;
        }
        for (let i = 0; i < newColumnsOrder.length; i++) {
          newColumnsIndex[i] = newColumnsOrder[i];
        }

        const oldRows = this.rows;
        const newRows = [];

        for (let i = 0; i < oldRows.length; i++) {

          const newRow = [];

          for (let j = 0; j < oldRows[i].length; j++) {
            newRow.push(oldRows[i][oldColumnsIndex[newColumnsIndex[j]]]);
          }
          newRows.push(newRow);
        }

        // Next, redraw the table
        this.columns = newColumnsOrder;
        this.columnTypes = newColumnTypes;
        this.rows = newRows;
        this.loadingOptions = [];
      },
    });
  }
};

/**
 * A skeleton to ease the creation of a minimal Blueprintjs select element.
 *
 * @memberOf module:blueprintjs
 * @extends {blueprintjs.Blueprintjs}
 * @type {blueprintjs.MinimalSelect}
 */
blueprintjs.MinimalSelect = class extends blueprintjs.Blueprintjs {

  /**
   * @param {Element} container the parent element.
   * @param {function(*): string} itemToText a function that maps an item to the text to be displayed (optional).
   * @param {function(*): string} itemToLabel a function that maps an item to the label to be displayed (optional).
   * @param {function(string, *): boolean} itemPredicate a function that filters the internal list of items when user enters something in the input (optional).
   * @param {function(string): *} itemCreate a function that creates an item from a string (optional).
   * @constructor
   */
  constructor(container, itemToText, itemToLabel, itemPredicate, itemCreate) {
    super(container);
    this.itemToText_ = itemToText;
    this.itemToLabel_ = itemToLabel;
    this.itemPredicate_ = (query, item) => {
      if (itemPredicate) {
        return itemPredicate(query, item);
      }
      if (query && query !== '') {
        const txt = this.itemToText_ ? this.itemToText_(item) : item;
        return txt.trim().toLowerCase().indexOf(query.trim().toLowerCase()) >= 0;
      }
      return true;
    };
    this.itemCreate_ = itemCreate;
    this.observers_ = new observers.Subject();
    this.selectedItem_ = null;
    this.fillContainer_ = true;
    this.disabled_ = false;
    this.filterable_ = true;
    this.items_ = [];
    this.defaultText_ = 'Sélectionnez un élément...';
    this.noResults_ = 'Il n\'y a aucun résultat pour cette recherche.';
    this.render();
  }

  get fillContainer() {
    return this.fillContainer_;
  }

  set fillContainer(value) {
    this.fillContainer_ = value;
    this.render();
  }

  get disabled() {
    return this.disabled_;
  }

  set disabled(value) {
    this.disabled_ = value;
    this.render();
  }

  get filterable() {
    return this.filterable_;
  }

  set filterable(value) {
    this.filterable_ = value;
    this.render();
  }

  get items() {
    return this.items_;
  }

  set items(values) {
    this.items_ = values;
    this.render();
  }

  get selectedItem() {
    return this.selectedItem_;
  }

  set selectedItem(value) {
    this.selectedItem_ = value;
    this.render();
  }

  get defaultText() {
    return this.defaultText_;
  }

  set defaultText(value) {
    this.defaultText_ = value;
    this.render();
  }

  get noResults() {
    return this.noResults_;
  }

  set noResults(value) {
    this.noResults_ = value;
    this.render();
  }

  /**
   * Listen to the `selection-change` event.
   *
   * @param {function(*): void} callback the callback to call when the event is triggered.
   * @name onSelectionChange
   * @function
   * @public
   */
  onSelectionChange(callback) {
    this.observers_.register('selection-change', (item) => {
      // console.log('Selected item is ', item);
      if (callback) {
        callback(item);
      }
    });
  }

  /**
   * Listen to the `filter-change` event.
   *
   * @param {function(*): void} callback the callback to call when the event is triggered.
   * @name onFilterChange
   * @function
   * @public
   */
  onFilterChange(callback) {
    this.observers_.register('filter-change', (filter) => {
      // console.log('Filter is ', filter);
      if (callback) {
        callback(filter);
      }
    });
  }

  _newButton() {
    return React.createElement(Button, {
      text: this.selectedItem ? this.itemToText_ ? this.itemToText_(this.selectedItem) : this.selectedItem
        : this.defaultText,
      alignText: 'left',
      rightIcon: 'double-caret-vertical',
      fill: this.fillContainer,
      disabled: this.disabled,
    });
  }

  _newElement() {
    return React.createElement(Select2, {
      fill: this.fillContainer,
      disabled: this.disabled,
      children: [this._newButton()],
      items: this.items,
      filterable: this.filterable,
      itemPredicate: this.itemPredicate_,
      onItemSelect: (item) => {
        // If the user selects twice the same item, removes the selection
        const selection = item === this.selectedItem ? null : item;
        this.selectedItem_ = selection;
        this.render();
        this.observers_.notify('selection-change', selection);
      },
      onQueryChange: (query) => {
        this.observers_.notify('filter-change', query);
      },
      itemRenderer: (item, props) => {
        if (!props.modifiers.matchesPredicate) {
          return null;
        }
        let active = props.modifiers.active;
        if (this.selectedItem) {
          active = (this.itemToText_ ? this.itemToText_(this.selectedItem) : this.selectedItem) === (this.itemToText_
            ? this.itemToText_(item) : item);
        }
        return React.createElement(MenuItem, {
          key: props.index,
          selected: active,
          text: this.itemToText_ ? this.itemToText_(item) : item,
          label: this.itemToLabel_ ? this.itemToLabel_(item) : '',
          onFocus: props.handleFocus,
          onClick: props.handleClick,
        });
      },
      noResults: React.createElement(MenuItem, {
        text: this.noResults, disabled: true,
      }),
      popoverProps: {
        matchTargetWidth: true,
      },
      createNewItemFromQuery: this.itemCreate_,
      createNewItemRenderer: (query, active, handleClick) => {
        return React.createElement(MenuItem, {
          icon: 'add', selected: active, text: query, onClick: handleClick,
        });
      },
    });
  }
};

/**
 * A skeleton to ease the creation of a minimal Blueprintjs slider element.
 *
 * @memberOf module:blueprintjs
 * @extends {blueprintjs.Blueprintjs}
 * @type {blueprintjs.MinimalSlider}
 */
blueprintjs.MinimalSlider = class extends blueprintjs.Blueprintjs {

  /**
   * @param {Element} container the parent element.
   * @param {number} min the minimum value.
   * @param {number} max the maximum value.
   * @param {number} increment the internal increment.
   * @param {number} displayIncrement the display increment.
   * @constructor
   */
  constructor(container, min, max, increment, displayIncrement) {
    super(container);
    this.min_ = min;
    this.max_ = max;
    this.increment_ = increment;
    this.displayIncrement_ = displayIncrement;
    this.value_ = min;
    this.observers_ = new observers.Subject();
    this.disabled_ = false;
    this.render();
  }

  get disabled() {
    return this.disabled_;
  }

  set disabled(value) {
    this.disabled_ = value;
    this.render();
  }

  get value() {
    return this.value_;
  }

  set value(value) {
    this.value_ = value;
    this.render();
  }

  /**
   * Listen to the `selection-change` event.
   *
   * @param {function(number): void} callback the callback to call when the event is triggered.
   * @name onSelectionChange
   * @function
   * @public
   */
  onSelectionChange(callback) {
    this.observers_.register('selection-change', (value) => {
      // console.log('Selected value is ', item);
      if (callback) {
        callback(value);
      }
    });
  }

  _newElement() {
    return React.createElement(Slider, {
      min: this.min_,
      max: this.max_,
      stepSize: this.increment_,
      labelStepSize: this.displayIncrement_,
      value: this.value,
      disabled: this.disabled,
      onChange: (value) => {
        this.value = value;
        this.observers_.notify('selection-change', value);
      },
    });
  }
};

/**
 * A skeleton to ease the creation of a minimal Blueprintjs range slider element.
 *
 * @memberOf module:blueprintjs
 * @extends {blueprintjs.Blueprintjs}
 * @type {blueprintjs.MinimalRangeSlider}
 */
blueprintjs.MinimalRangeSlider = class extends blueprintjs.Blueprintjs {

  /**
   * @param {Element} container the parent element.
   * @param {number} min the minimum value.
   * @param {number} max the maximum value.
   * @param {number} increment the internal increment.
   * @param {number} displayIncrement the display increment.
   * @param {number} defaultMinValue the minimum value selected when the component is rendered the first time.
   * @param {number} defaultMaxValue the maximum value selected when the component is rendered the first time.
   * @constructor
   */
  constructor(container, min, max, increment, displayIncrement, defaultMinValue, defaultMaxValue) {
    super(container);
    this.min_ = min;
    this.max_ = max;
    this.increment_ = increment;
    this.displayIncrement_ = displayIncrement;
    this.minValue_ = defaultMinValue;
    this.maxValue_ = defaultMaxValue;
    this.observers_ = new observers.Subject();
    this.disabled_ = false;
    this.render();
  }

  get disabled() {
    return this.disabled_;
  }

  set disabled(value) {
    this.disabled_ = value;
    this.render();
  }

  get minValue() {
    return this.minValue_;
  }

  set minValue(value) {
    this.minValue_ = value;
    this.render();
  }

  get maxValue() {
    return this.maxValue_;
  }

  set maxValue(value) {
    this.maxValue_ = value;
    this.render();
  }

  /**
   * Listen to the `selection-change` event.
   *
   * @param {function(number, number): void} callback the callback to call when the event is triggered.
   * @name onSelectionChange
   * @function
   * @public
   */
  onSelectionChange(callback) {
    this.observers_.register('selection-change', (value) => {
      // console.log('Selected value is ', value);
      if (callback) {
        callback(value[0], value[1]);
      }
    });
  }

  _newElement() {
    return React.createElement(RangeSlider, {
      min: this.min_,
      max: this.max_,
      stepSize: this.increment_,
      labelStepSize: this.displayIncrement_,
      value: [this.minValue, this.maxValue],
      disabled: this.disabled,
      onChange: (value) => {
        this.minValue = value[0];
        this.maxValue = value[1];
        this.observers_.notify('selection-change', value);
      },
    });
  }
};

/**
 * A skeleton to ease the creation of a minimal Blueprintjs drawer element.
 *
 * @memberOf module:blueprintjs
 * @extends {blueprintjs.Blueprintjs}
 * @type {blueprintjs.MinimalDrawer}
 */
blueprintjs.MinimalDrawer = class extends blueprintjs.Blueprintjs {

  /**
   * @param {Element} container the parent element.
   * @param {string} width the drawer width in pixels or percents (optional).
   * @constructor
   */
  constructor(container, width) {
    super(container);
    this.observers_ = new observers.Subject();
    this.show_ = false;
    this.width_ = width ? width : '75%';
    this.render();
  }

  get show() {
    return this.show_;
  }

  set show(value) {
    this.show_ = value;
    this.render();
  }

  /**
   * Listen to the `opening` event.
   *
   * @param {function(Element): void} callback the callback to call when the event is triggered.
   * @name onOpen
   * @function
   * @public
   */
  onOpen(callback) {
    this.observers_.register('opening', (el) => {
      if (callback) {
        callback(el);
      }
    });
  }

  /**
   * Listen to the `opened` event.
   *
   * @param {function(Element): void} callback the callback to call when the event is triggered.
   * @name onOpened
   * @function
   * @public
   */
  onOpened(callback) {
    this.observers_.register('opened', (el) => {
      if (callback) {
        callback(el);
      }
    });
  }

  /**
   * Listen to the `closing` event.
   *
   * @param {function(Element): void} callback the callback to call when the event is triggered.
   * @name onClose
   * @function
   * @public
   */
  onClose(callback) {
    this.observers_.register('closing', (el) => {
      if (callback) {
        callback(el);
      }
    });
  }

  _newElement() {
    return React.createElement(Drawer, {
      isOpen: this.show,
      size: this.width_,
      position: Position.RIGHT,
      onOpening: (el) => this.observers_.notify('opening', el),
      onOpened: (el) => this.observers_.notify('opened', el),
      onClose: () => this.show = false,
      onClosed: (el) => this.observers_.notify('closing', el),
    });
  }
};

/**
 * A skeleton to ease the creation of a minimal Blueprintjs tabs element.
 *
 * @memberOf module:blueprintjs
 * @extends {blueprintjs.Blueprintjs}
 * @type {blueprintjs.MinimalTabs}
 */
blueprintjs.MinimalTabs = class extends blueprintjs.Blueprintjs {

  /**
   * @param {Element} container the parent element.
   * @constructor
   */
  constructor(container) {
    super(container);
    this.observers_ = new observers.Subject();
    this.tabs_ = [];
    this.render();
  }

  /**
   * Add a single tab to the nav bar.
   *
   * @param {string} name the tab name.
   * @param {Element} panel the tab content.
   * @name addTab
   * @function
   * @public
   */
  addTab(name, panel) {
    this.tabs_.push({
      name: name, panel: panel, disabled: false, is_selected: false,
    });
    this.render();
  }

  /**
   * Remove a single tab from the nav bar.
   *
   * @param {string} name the tab name.
   * @name removeTab
   * @function
   * @public
   */
  removeTab(name) {
    this.tabs_ = this.tabs_.filter(tab => tab.name !== name);
    this.render();
  }

  /**
   * Select the tab to display.
   *
   * @param {string} name the tab name.
   * @name selectTab
   * @function
   * @public
   */
  selectTab(name) {
    let selectedTab = null;
    this.tabs_.forEach(tab => {
      if (tab.name !== name) {
        tab.is_selected = false;
      } else {
        tab.is_selected = true;
        selectedTab = tab;
      }
    });
    this.render();
    if (selectedTab) {
      this.observers_.notify('selection-change', selectedTab.name, selectedTab.panel);
    }
  }

  /**
   * Listen to the `selection-change` event.
   *
   * @param {function(string, Element): void} callback the callback to call when the event is triggered.
   * @name onSelectionChange
   * @function
   * @public
   */
  onSelectionChange(callback) {
    this.observers_.register('selection-change', (tabName, tabBody) => {
      // console.log('Selected tab is ' + tabName);
      if (callback) {
        callback(tabName, tabBody);
      }
    });
  }

  _newTab(tab) {
    return React.createElement(Tab, {
      id: tab.name, title: tab.name, panel: null, disabled: tab.disabled,
    });
  }

  _newElement() {
    const selectedTab = this.tabs_.find(tab => tab.is_selected);
    return React.createElement(Tabs, {
      id: 'tabs',
      children: this.tabs_.map(tab => this._newTab(tab)),
      selectedTabId: selectedTab ? selectedTab.name : null,
      onChange: (newTabId, oldTabId) => this.selectTab(newTabId)
    });
  }
};

/**
 * A skeleton to ease the creation of a minimal Blueprintjs spinner element.
 *
 * @memberOf module:blueprintjs
 * @extends {blueprintjs.Blueprintjs}
 * @type {blueprintjs.MinimalSpinner}
 */
blueprintjs.MinimalSpinner = class extends blueprintjs.Blueprintjs {

  /**
   * @param {Element} container the parent element.
   * @param {string} size the spinner size in {'small', 'standard', 'large'}
   * @constructor
   */
  constructor(container, size) {
    super(container);
    this.value_ = null;
    if (size === 'small') {
      this.size_ = SpinnerSize.SMALL;
    } else if (size === 'large') {
      this.size_ = SpinnerSize.LARGE;
    } else {
      this.size_ = SpinnerSize.STANDARD;
    }
    this.render();
  }

  /**
   * Represents how far along an operation is.
   *
   * @param {number} value a value between 0 and 1 (inclusive) representing how far along an operation is.
   * @name advance
   * @function
   * @public
   */
  advance(value) {
    this.value_ = value;
    this.render();
  }

  _newElement() {
    return React.createElement(Spinner, {
      value: this.value_, size: this.size_,
    });
  }
};

/**
 * A skeleton to ease the creation of a minimal Blueprintjs switch element.
 *
 * @memberOf module:blueprintjs
 * @extends {blueprintjs.Blueprintjs}
 * @type {blueprintjs.MinimalSwitch}
 */
blueprintjs.MinimalSwitch = class extends blueprintjs.Blueprintjs {

  /**
   * @param {Element} container the parent element.
   * @param {boolean} checked true iif the control should initially be checked, false otherwise (optional).
   * @param {string} label the switch label (optional).
   * @param {string} labelPosition the switch label position (in {left, right}) in respect to the element (optional).
   * @param {string} labelChecked the text to display inside the switch indicator when checked (optional).
   * @param {string} labelUnchecked the text to display inside the switch indicator when unchecked (optional).
   * @constructor
   */
  constructor(container, checked, label, labelPosition, labelChecked, labelUnchecked) {
    super(container);
    this.checked_ = checked;
    this.label_ = label;
    this.switchPosition_ = labelPosition === 'left' ? Alignment.RIGHT : Alignment.LEFT;
    this.labelChecked_ = labelChecked;
    this.labelUnchecked_ = labelUnchecked;
    this.observers_ = new observers.Subject();
    this.disabled_ = false;
    this.render();
  }

  get disabled() {
    return this.disabled_;
  }

  set disabled(value) {
    this.disabled_ = value;
    this.render();
  }

  get checked() {
    return this.checked_;
  }

  set checked(value) {
    this.checked_ = value;
    this.render();
  }

  /**
   * Listen to the `selection-change` event.
   *
   * @param {function(boolean): void} callback the callback to call when the event is triggered.
   * @name onSelectionChange
   * @function
   * @public
   */
  onSelectionChange(callback) {
    this.observers_.register('selection-change', (value) => {
      // console.log('Selected option is ' + (value ? 'checked' : 'unchecked'));
      if (callback) {
        callback(value ? 'checked' : 'unchecked');
      }
    });
  }

  _newElement() {
    return React.createElement(Switch, {
      disabled: this.disabled_,
      checked: this.checked_,
      label: this.label_,
      alignIndicator: this.switchPosition_,
      innerLabel: this.labelUnchecked_,
      innerLabelChecked: this.labelChecked_,
      onChange: () => {
        this.checked = !this.checked;
        this.observers_.notify('selection-change', this.checked);
      },
    });
  }
};

/**
 * A skeleton to ease the creation of a minimal Blueprintjs toast element.
 *
 * @memberOf module:blueprintjs
 * @extends {blueprintjs.Blueprintjs}
 * @type {blueprintjs.MinimalToast}
 */
blueprintjs.MinimalToast = class extends blueprintjs.Blueprintjs {

  /**
   * @param {Element} container the parent element.
   * @param {string} message the message to display.
   * @param {string} intent the message intent in {none, primary, success, warning, danger} (optional).
   * @param {number} timeout the number of milliseconds to wait before automatically dismissing the toast (optional).
   * @constructor
   */
  constructor(container, message, intent, timeout) {
    super(container);
    this.timeout_ = timeout;
    this.message_ = message;
    if (intent === 'primary') {
      this.intent_ = Intent.PRIMARY;
      this.icon_ = null;
    } else if (intent === 'success') {
      this.intent_ = Intent.SUCCESS;
      this.icon_ = 'tick';
    } else if (intent === 'warning') {
      this.intent_ = Intent.WARNING;
      this.icon_ = 'warning-sign';
    } else if (intent === 'danger') {
      this.intent_ = Intent.DANGER;
      this.icon_ = 'warning-sign';
    } else {
      this.intent_ = Intent.NONE;
      this.icon_ = null;
    }
    this.observers_ = new observers.Subject();
    this.render();
  }

  /**
   * Listen to the `dismiss` event.
   *
   * @param {function(void): void} callback the callback to call when the event is triggered.
   * @name onDismiss
   * @function
   * @public
   */
  onDismiss(callback) {
    this.observers_.register('dismiss', (self) => {
      // console.log('Toast dismissed!');
      if (callback) {
        callback();
      }
    });
  }

  _newElement() {
    return React.createElement(Toast, {
      intent: this.intent_,
      icon: this.icon_,
      message: React.createElement('div', {}, this.message_),
      timeout: this.timeout_,
      onDismiss: () => this.observers_.notify('dismiss', this),
    });
  }
};

/**
 * A skeleton to ease the creation of a minimal Blueprintjs toaster element.
 *
 * @memberOf module:blueprintjs
 * @extends {blueprintjs.Blueprintjs}
 * @type {blueprintjs.MinimalToaster}
 */
blueprintjs.MinimalToaster = class extends blueprintjs.Blueprintjs {

  /**
   * @param {Element} container the parent element.
   * @constructor
   */
  constructor(container) {
    super(container);
    this.toasts_ = [];
    this.render();
  }

  /**
   * Create and display a new toast.
   *
   * @param {string} message the message to display.
   * @param {string} intent the message intent in {none, primary, success, warning, danger} (optional).
   * @param {number} timeout the number of milliseconds to wait before automatically dismissing the toast (optional).
   * @name toast
   * @function
   * @public
   */
  toast(message, intent, timeout) {
    const toast = new blueprintjs.MinimalToast(this.container, message, intent, timeout);
    toast.el_ = toast._newElement();
    toast.onDismiss(() => {
      this.toasts_ = this.toasts_.filter(t => t !== toast);
      this.render();
    });
    this.toasts_.push(toast);
    this.render();
  }

  _newElement() {
    return React.createElement(Toaster, {
      children: this.toasts_.map(toast => toast.el_), position: Position.TOP,
    });
  }
};

/**
 * A skeleton to ease the creation of a minimal Blueprintjs card element.
 *
 * @memberOf module:blueprintjs
 * @extends {blueprintjs.Blueprintjs}
 * @type {blueprintjs.MinimalCard}
 */
blueprintjs.MinimalCard = class extends blueprintjs.Blueprintjs {

  /**
   * @param {Element} container the parent element.
   * @param {Element} body the card body.
   * @constructor
   */
  constructor(container, body) {
    super(container);
    this.elevation_ = 0;
    this.interactive_ = false;
    this.observers_ = new observers.Subject();
    this.body_ = React.createElement('div', {
      ref: React.createRef(),
    });
    this.render(); // this.body_ must be rendered first!
    this.body_.ref.current.appendChild(body);
    this.render();
  }

  get elevation() {
    return this.elevation_;
  }

  set elevation(value) {
    this.elevation_ = !value ? 0 : value > 4 ? 4 : value;
    this.render();
  }

  get interactive() {
    return this.interactive_;
  }

  set interactive(value) {
    this.interactive_ = value;
    this.render();
  }

  /**
   * Listen to the `click` event.
   *
   * @param {function(void): void} callback the callback to call when the event is triggered.
   * @name onClick
   * @function
   * @public
   */
  onClick(callback) {
    this.observers_.register('click', (self) => {
      // console.log('Card clicked!');
      if (callback) {
        callback();
      }
    });
  }

  _newElement() {
    return React.createElement(Card, {
      children: [this.body_],
      elevation: this.elevation_,
      interactive: this.interactive_,
      onClick: () => this.observers_.notify('click', this),
    });
  }
};

/**
 * A skeleton to ease the creation of a minimal Blueprintjs icon element.
 *
 * @memberOf module:blueprintjs
 * @extends {blueprintjs.Blueprintjs}
 * @type {blueprintjs.MinimalIcon}
 */
blueprintjs.MinimalIcon = class extends blueprintjs.Blueprintjs {

  /**
   * @param {Element} container the parent element.
   * @param {string} icon the icon name.
   * @param {string} intent the icon intent in {none, primary, success, warning, danger} (optional).
   * @constructor
   */
  constructor(container, icon, intent) {
    super(container);
    this.observers_ = new observers.Subject();
    this.icon_ = icon;
    this.size_ = 20;
    if (intent === 'primary') {
      this.intent_ = Intent.PRIMARY;
    } else if (intent === 'success') {
      this.intent_ = Intent.SUCCESS;
    } else if (intent === 'warning') {
      this.intent_ = Intent.WARNING;
    } else if (intent === 'danger') {
      this.intent_ = Intent.DANGER;
    } else {
      this.intent_ = Intent.NONE;
    }
    this.render();
  }

  get icon() {
    return this.icon_;
  }

  set icon(value) {
    this.icon_ = value;
    this.render();
  }

  get size() {
    return this.size_;
  }

  set size(value) {
    this.size_ = value;
    this.render();
  }

  get intent() {
    return this.intent_;
  }

  set intent(value) {
    this.intent_ = value;
    this.render();
  }

  /**
   * Listen to the `click` event.
   *
   * @param {function(void): void} callback the callback to call when the event is triggered.
   * @name onClick
   * @function
   * @public
   */
  onClick(callback) {
    this.observers_.register('click', (self) => {
      // console.log('Icon clicked!');
      if (callback) {
        callback();
      }
    });
  }

  _newElement() {
    return React.createElement(Icon, {
      icon: this.icon_, size: this.size_, intent: this.intent_, onClick: () => this.observers_.notify('click', this),
    });
  }
};

/**
 * A skeleton to ease the creation of a minimal Blueprintjs checkbox element.
 *
 * @memberOf module:blueprintjs
 * @extends {blueprintjs.Blueprintjs}
 * @type {blueprintjs.MinimalCheckbox}
 */
blueprintjs.MinimalCheckbox = class extends blueprintjs.Blueprintjs {

  /**
   * @param {Element} container the parent element.
   * @param {boolean} checked true iif the control should initially be checked, false otherwise (optional).
   * @param {string} label the switch label (optional).
   * @param {string} labelPosition the switch label position (in {left, right}) in respect to the element (optional).
   * @constructor
   */
  constructor(container, checked, label, labelPosition) {
    super(container);
    this.observers_ = new observers.Subject();
    this.checked_ = checked;
    this.label_ = label;
    this.boxPosition_ = labelPosition === 'left' ? Alignment.RIGHT : Alignment.LEFT;
    this.disabled_ = false;
    this.render();
  }

  get checked() {
    return this.checked_;
  }

  set checked(value) {
    this.checked_ = value;
    this.render();
  }

  get disabled() {
    return this.disabled_;
  }

  set disabled(value) {
    this.disabled_ = value;
    this.render();
  }

  /**
   * Listen to the `selection-change` event.
   *
   * @param {function(string): void} callback the callback to call when the event is triggered.
   * @name onSelectionChange
   * @function
   * @public
   */
  onSelectionChange(callback) {
    this.observers_.register('selection-change', (value) => {
      // console.log('Selected option is ' + (value ? 'checked' : 'unchecked'));
      if (callback) {
        callback(value ? 'checked' : 'unchecked');
      }
    });
  }

  _newElement() {
    return React.createElement(Checkbox, {
      checked: this.checked_,
      disabled: this.disabled_,
      label: this.label_,
      alignIndicator: this.boxPosition_,
      onChange: () => {
        this.checked = !this.checked;
        this.observers_.notify('selection-change', this.checked);
      },
    });
  }
};

/**
 * A skeleton to ease the creation of a minimal Blueprintjs date element.
 *
 * @memberOf module:blueprintjs
 * @extends {blueprintjs.Blueprintjs}
 * @type {blueprintjs.MinimalDate}
 */
blueprintjs.MinimalDate = class extends blueprintjs.Blueprintjs {

  /**
   * @param {Element} container the parent element.
   * @param {string} format the date format (optional). Default is 'yyyy-MM-dd'.
   * @param {Date} minDate the earliest date the user can select (optional).
   * @param {Date} maxDate the latest date the user can select (optional).
   * @constructor
   */
  constructor(container, format, minDate, maxDate) {
    super(container);
    this.observers_ = new observers.Subject();
    this.value_ = null;
    this.disabled_ = false;
    this.format_ = format ? format : 'yyyy-MM-dd';
    this.fillContainer_ = true;
    this.shortcuts_ = false;
    this.showActionsBar_ = false;
    this.minDate_ = minDate ? minDate : sub(new Date(), {years: 10});
    this.maxDate_ = maxDate ? maxDate : add(new Date(), {years: 10});
    this.render();
  }

  get date() {
    return this.value_;
  }

  set date(value) {
    this.value_ = value;
    this.render();
  }

  get disabled() {
    return this.disabled_;
  }

  set disabled(value) {
    this.disabled_ = value;
    this.render();
  }

  get shortcuts() {
    return this.shortcuts_;
  }

  set shortcuts(value) {
    this.shortcuts_ = value;
    this.render();
  }

  get showActionsBar() {
    return this.showActionsBar_;
  }

  set showActionsBar(value) {
    this.showActionsBar_ = value;
    this.render();
  }

  get fillContainer() {
    return this.fillContainer_;
  }

  set fillContainer(value) {
    this.fillContainer_ = value;
    this.render();
  }

  get minDate() {
    return this.minDate_;
  }

  set minDate(value) {
    this.minDate_ = value;
    this.render();
  }

  get maxDate() {
    return this.maxDate_;
  }

  set maxDate(value) {
    this.maxDate_ = value;
    this.render();
  }

  /**
   * Listen to the `selection-change` event.
   *
   * @param {function(string): void} callback the callback to call when the event is triggered.
   * @name onSelectionChange
   * @function
   * @public
   */
  onSelectionChange(callback) {
    this.observers_.register('selection-change', (value) => {
      // console.log('Selected date is ' + value);
      if (callback) {
        callback(value);
      }
    });
  }

  _newElement() {
    return React.createElement(DateInput2, {
      formatDate: (date) => format(date, this.format_),
      parseDate: (str) => parse(str, this.format_, new Date()),
      value: this.date,
      disabled: this.disabled,
      placeholder: this.format_,
      fill: this.fillContainer,
      minDate: this.minDate,
      maxDate: this.maxDate,
      shortcuts: this.shortcuts,
      showActionsBar: this.showActionsBar,
      showTimezoneSelect: this._showTimezone(),
      disableTimezoneSelect: this._disableTimezone(),
      timePrecision: this._timePrecision(),
      defaultTimezone: this._defaultTimezone(),
      onChange: (value) => {
        this.date = value;
        this.observers_.notify('selection-change', this.date);
      }
    });
  }

  /* Time-specific functions */

  _showTimezone() {
    return false;
  }

  _timePrecision() {
    return null;
  }

  _defaultTimezone() {
    return null;
  }

  _disableTimezone() {
    return true;
  }
};

/**
 * A skeleton to ease the creation of a minimal Blueprintjs datetime element.
 *
 * @memberOf module:blueprintjs
 * @extends {blueprintjs.Blueprintjs}
 * @type {blueprintjs.MinimalDatetime}
 */
blueprintjs.MinimalDatetime = class extends blueprintjs.MinimalDate {

  /**
   * @param {Element} container the parent element.
   * @param {string} format the date format (optional). Default is 'yyyy-MM-dd HH:mm'.
   * @param {Date} minDate the earliest date the user can select (optional).
   * @param {Date} maxDate the latest date the user can select (optional).
   * @param {string} timePrecision the time precision in {'hours', 'minutes', 'seconds'} (optional). Default is 'minutes'.
   * @param {string} defaultTimezone the default time zone (optional). Default is 'UTC'.
   * @constructor
   */
  constructor(container, format, minDate, maxDate, timePrecision, defaultTimezone) {
    super(container, format ? format : 'yyyy-MM-dd HH:mm', minDate, maxDate);
    this.timePrecision_ = timePrecision === 'hours' ? TimePrecision.HOUR_24 : timePrecision === 'seconds'
      ? TimePrecision.SECOND : TimePrecision.MINUTE;
    this.defaultTimezone_ = defaultTimezone ? defaultTimezone : 'Etc/UTC';
    this.disableTimezone_ = false;
    this.render();
  }

  get disableTimezone() {
    return this.disableTimezone_;
  }

  set disableTimezone(value) {
    this.disableTimezone_ = value;
    this.render();
  }

  _showTimezone() {
    return true;
  }

  _timePrecision() {
    return this.timePrecision_;
  }

  _defaultTimezone() {
    return this.defaultTimezone_;
  }

  _disableTimezone() {
    return this.disableTimezone;
  }
};

/**
 * A skeleton to ease the creation of a minimal Blueprintjs date range element.
 *
 * @memberOf module:blueprintjs
 * @extends {blueprintjs.Blueprintjs}
 * @type {blueprintjs.MinimalDateRange}
 */
blueprintjs.MinimalDateRange = class extends blueprintjs.Blueprintjs {

  /**
   * @param {Element} container the parent element.
   * @param {string} format the date format (optional). Default is 'yyyy-MM-dd'.
   * @param {Date} minDate the earliest date the user can select (optional).
   * @param {Date} maxDate the latest date the user can select (optional).
   * @constructor
   */
  constructor(container, format, minDate, maxDate) {
    super(container);
    this.observers_ = new observers.Subject();
    this.disabled_ = false;
    this.shortcuts_ = true;
    this.dateMin_ = null;
    this.dateMax_ = null;
    this.format_ = format ? format : 'yyyy-MM-dd';
    this.minDate_ = minDate ? minDate : sub(new Date(), {years: 10});
    this.maxDate_ = maxDate ? maxDate : add(new Date(), {years: 10});
    this.render();
  }

  get disabled() {
    return this.disabled_;
  }

  set disabled(value) {
    this.disabled_ = value;
    this.render();
  }

  get shortcuts() {
    return this.shortcuts_;
  }

  set shortcuts(value) {
    this.shortcuts_ = value;
    this.render();
  }

  get dateMin() {
    return this.dateMin_;
  }

  set dateMin(value) {
    this.dateMin_ = value;
    this.render();
  }

  get dateMax() {
    return this.dateMax_;
  }

  set dateMax(value) {
    this.dateMax_ = value;
    this.render();
  }

  get minDate() {
    return this.minDate_;
  }

  set minDate(value) {
    this.minDate_ = value;
    this.render();
  }

  get maxDate() {
    return this.maxDate_;
  }

  set maxDate(value) {
    this.maxDate_ = value;
    this.render();
  }

  /**
   * Listen to the `selection-change` event.
   *
   * @param {function(Date, Date): void} callback the callback to call when the event is triggered.
   * @name onSelectionChange
   * @function
   * @public
   */
  onSelectionChange(callback) {
    this.observers_.register('selection-change', (range) => {
      // console.log('Selected range is ' + range);
      if (callback) {
        callback(range[0], range[1]);
      }
    });
  }

  _newElement() {
    return React.createElement(DateRangeInput2, {
      formatDate: (date) => format(date, this.format_),
      parseDate: (str) => parse(str, this.format_, new Date()),
      value: [this.dateMin, this.dateMax],
      disabled: this.disabled,
      placeholder: this.format_,
      shortcuts: this.shortcuts,
      minDate: this.minDate,
      maxDate: this.maxDate,
      onChange: (range) => {
        this.dateMin = range[0];
        this.dateMax = range[1];
        this.observers_.notify('selection-change', range);
      }
    });
  }
};

/**
 * A skeleton to ease the creation of a minimal Blueprintjs multiselect element.
 *
 * @memberOf module:blueprintjs
 * @extends {blueprintjs.Blueprintjs}
 * @type {blueprintjs.MinimalMultiSelect}
 */
blueprintjs.MinimalMultiSelect = class extends blueprintjs.Blueprintjs {

  /**
   * @param {Element} container the parent element.
   * @param {function(*): string} itemToText a function that maps an item to the text to be displayed (optional).
   * @param {function(*): string} itemToLabel a function that maps an item to the label to be displayed (optional).
   * @param {function(*): string} itemToTag a function that maps an item to the tag to be displayed (optional).
   * @param {function(string, *): boolean} itemPredicate a function that filters the internal list of items when user enters something in the input (optional).
   * @param {function(string): *} itemCreate a function that creates an item from a string (optional).
   * @constructor
   */
  constructor(container, itemToText, itemToLabel, itemToTag, itemPredicate, itemCreate) {
    super(container);
    this.itemToText_ = itemToText;
    this.itemToLabel_ = itemToLabel;
    this.itemToTag_ = itemToTag;
    this.itemPredicate_ = (query, item) => {
      if (itemPredicate) {
        return itemPredicate(query, item);
      }
      if (query && query !== '') {
        const txt = this.itemToText_ ? this.itemToText_(item) : item;
        return txt.trim().toLowerCase().indexOf(query.trim().toLowerCase()) >= 0;
      }
      return true;
    };
    this.itemCreate_ = itemCreate;
    this.observers_ = new observers.Subject();
    this.fillContainer_ = true;
    this.disabled_ = false;
    this.items_ = [];
    this.selectedItems_ = [];
    this.defaultText_ = 'Sélectionnez un élément...';
    this.noResults_ = 'Il n\'y a aucun résultat pour cette recherche.';
    this.render();
  }

  get fillContainer() {
    return this.fillContainer_;
  }

  set fillContainer(value) {
    this.fillContainer_ = value;
    this.render();
  }

  get disabled() {
    return this.disabled_;
  }

  set disabled(value) {
    this.disabled_ = value;
    this.render();
  }

  get items() {
    return this.items_;
  }

  set items(values) {
    this.items_ = values;
    this.render();
  }

  get selectedItems() {
    return this.selectedItems_;
  }

  set selectedItems(value) {
    this.selectedItems_ = value ? value : [];
    this.render();
  }

  get defaultText() {
    return this.defaultText_;
  }

  set defaultText(value) {
    this.defaultText_ = value;
    this.render();
  }

  get noResults() {
    return this.noResults_;
  }

  set noResults(value) {
    this.noResults_ = value;
    this.render();
  }

  /**
   * Listen to the `selection-change` event.
   *
   * @param {function(*): void} callback the callback to call when the event is triggered.
   * @name onSelectionChange
   * @function
   * @public
   */
  onSelectionChange(callback) {
    this.observers_.register('selection-change', (items) => {
      // console.log('Selected items are ', items);
      if (callback) {
        callback(items);
      }
    });
  }

  /**
   * Listen to the `filter-change` event.
   *
   * @param {function(*): void} callback the callback to call when the event is triggered.
   * @name onFilterChange
   * @function
   * @public
   */
  onFilterChange(callback) {
    this.observers_.register('filter-change', (query) => {
      // console.log('Query is ', query);
      if (callback) {
        callback(query);
      }
    });
  }

  _newElement() {
    return React.createElement(MultiSelect2, {
      fill: this.fillContainer,
      disabled: this.disabled,
      items: this.items,
      selectedItems: this.selectedItems,
      placeholder: this.defaultText,
      onQueryChange: (query) => {
        this.observers_.notify('filter-change', query);
      },
      onClear: () => {
        this.selectedItems_ = [];
        this.render();
        this.observers_.notify('selection-change', this.selectedItems);
      },
      itemPredicate: this.itemPredicate_,
      onItemSelect: (item) => {
        // If the user selects twice the same item, do not add it twice to the selection
        const pos = this.selectedItems.map(i => this.itemToText_ ? this.itemToText_(i) : i).indexOf(
          this.itemToText_ ? this.itemToText_(item) : item);
        if (pos !== 0 && pos <= -1) {
          this.selectedItems_.push(item);
          this.render();
          this.observers_.notify('selection-change', this.selectedItems);
        }
      },
      itemRenderer: (item, props) => {
        if (!props.modifiers.matchesPredicate) {
          return null;
        }
        return React.createElement(MenuItem, {
          key: props.index,
          selected: props.modifiers.active,
          text: this.itemToText_ ? this.itemToText_(item) : item,
          label: this.itemToLabel_ ? this.itemToLabel_(item) : '',
          onFocus: props.handleFocus,
          onClick: props.handleClick,
        });
      },
      tagRenderer: (item) => {
        return this.itemToTag_ ? this.itemToTag_(item) : item;
      },
      onRemove: (tag, index) => {
        this.selectedItems_.splice(index, 1);
        this.render();
        this.observers_.notify('selection-change', this.selectedItems);
      },
      noResults: React.createElement(MenuItem, {
        text: this.noResults, disabled: true,
      }),
      popoverProps: {
        matchTargetWidth: true,
      },
      resetOnSelect: !!this.itemCreate_,
      createNewItemFromQuery: this.itemCreate_,
      createNewItemRenderer: (query, active, handleClick) => {
        return React.createElement(MenuItem, {
          icon: 'add', selected: active, text: query, onClick: handleClick,
        });
      },
    });
  }
};

/**
 * A skeleton to ease the creation of a minimal Blueprintjs suggest element.
 *
 * @memberOf module:blueprintjs
 * @extends {blueprintjs.Blueprintjs}
 * @type {blueprintjs.MinimalSuggest}
 */
blueprintjs.MinimalSuggest = class extends blueprintjs.Blueprintjs {

  /**
   * @param {Element} container the parent element.
   * @param {function(*): string} itemToText a function that maps an item to the text to be displayed (optional).
   * @param {function(*): string} itemToLabel a function that maps an item to the label to be displayed (optional).
   * @param {function(*): boolean} itemPredicate a function that filters the internal list of items when user enters something in the input (optional).
   * @constructor
   */
  constructor(container, itemToText, itemToLabel, itemPredicate) {
    super(container);
    this.itemToText_ = itemToText;
    this.itemToLabel_ = itemToLabel;
    this.itemPredicate_ = (query, item) => {
      if (itemPredicate) {
        return itemPredicate(query, item);
      }
      if (query && query !== '') {
        const txt = this.itemToText_ ? this.itemToText_(item) : item;
        return txt.trim().toLowerCase().indexOf(query.trim().toLowerCase()) >= 0;
      }
      return true;
    };
    this.observers_ = new observers.Subject();
    this.fillContainer_ = true;
    this.disabled_ = false;
    this.items_ = [];
    this.selectedItem_ = null;
    this.defaultText_ = 'Saisissez un caractère...';
    this.noResults_ = 'Il n\'y a aucun résultat pour cette recherche.';
    this.render();
  }

  get fillContainer() {
    return this.fillContainer_;
  }

  set fillContainer(value) {
    this.fillContainer_ = value;
    this.render();
  }

  get disabled() {
    return this.disabled_;
  }

  set disabled(value) {
    this.disabled_ = value;
    this.render();
  }

  get items() {
    return this.items_;
  }

  set items(values) {
    this.items_ = values;
    this.render();
  }

  get selectedItem() {
    return this.selectedItem_;
  }

  set selectedItem(value) {
    this.selectedItem_ = value ? value : null;
    this.render();
  }

  get defaultText() {
    return this.defaultText_;
  }

  set defaultText(value) {
    this.defaultText_ = value;
    const input = this.container.querySelector('input');
    if (input) {
      input.placeholder = this.defaultText_;
    }
  }

  get noResults() {
    return this.noResults_;
  }

  set noResults(value) {
    this.noResults_ = value;
    this.render();
  }

  /**
   * Listen to the `selection-change` event.
   *
   * @param {function(*): void} callback the callback to call when the event is triggered.
   * @name onSelectionChange
   * @function
   * @public
   */
  onSelectionChange(callback) {
    this.observers_.register('selection-change', (item) => {
      // console.log('Selected item is ', item);
      if (callback) {
        callback(item);
      }
    });
  }

  /**
   * Listen to the `filter-change` event.
   *
   * @param {function(*): void} callback the callback to call when the event is triggered.
   * @name onFilterChange
   * @function
   * @public
   */
  onFilterChange(callback) {
    this.observers_.register('filter-change', (query) => {
      // console.log('Query is ', query);
      if (callback) {
        callback(query);
      }
    });
  }

  _newElement() {
    return React.createElement(Suggest2, {
      fill: this.fillContainer,
      disabled: this.disabled,
      items: this.items,
      selectedItem: this.selectedItem,
      onQueryChange: (query) => {
        this.observers_.notify('filter-change', query);
      },
      inputValueRenderer: item => this.itemToText_ ? this.itemToText_(item) : item,
      onItemSelect: (item) => {
        this.selectedItem_ = item;
        this.render();
        this.observers_.notify('selection-change', this.selectedItem);
      },
      itemPredicate: this.itemPredicate_,
      itemRenderer: (item, props) => {
        if (!props.modifiers.matchesPredicate) {
          return null;
        }
        return React.createElement(MenuItem, {
          key: props.index,
          selected: props.modifiers.active,
          text: this.itemToText_ ? this.itemToText_(item) : item,
          label: this.itemToLabel_ ? this.itemToLabel_(item) : '',
          onFocus: props.handleFocus,
          onClick: props.handleClick,
        });
      },
      noResults: React.createElement(MenuItem, {
        text: this.noResults, disabled: true,
      }),
      popoverProps: {
        matchTargetWidth: true,
      }
    });
  }
};

/**
 * A skeleton to ease the creation of a minimal Blueprintjs file input element.
 *
 * @memberOf module:blueprintjs
 * @extends {blueprintjs.Blueprintjs}
 * @type {blueprintjs.MinimalFileInput}
 */
blueprintjs.MinimalFileInput = class extends blueprintjs.Blueprintjs {

  /**
   * @param {Element} container the parent element.
   * @param {boolean} multiple true iif the user must be able to select one or more files.
   * @constructor
   */
  constructor(container, multiple) {
    super(container);
    this.observers_ = new observers.Subject();
    this.disabled_ = false;
    this.text_ = null;
    this.buttonText_ = null;
    this.fill_ = true;
    this.multiple_ = multiple === true;
    this.render();
  }

  get disabled() {
    return this.disabled_;
  }

  set disabled(value) {
    this.disabled_ = value;
    this.render();
  }

  get fill() {
    return this.fill_;
  }

  set fill(value) {
    this.fill_ = value;
    this.render();
  }

  get text() {
    return this.text_;
  }

  set text(value) {
    this.text_ = value;
    this.render();
  }

  get buttonText() {
    return this.buttonText_;
  }

  set buttonText(value) {
    this.buttonText_ = value;
    this.render();
  }

  get multiple() {
    return this.multiple_;
  }

  set multiple(value) {
    this.multiple_ = value;
    this.render();
  }

  /**
   * Listen to the `selection-change` event.
   *
   * @param {function(*): void} callback the callback to call when the event is triggered.
   * @name onSelectionChange
   * @function
   * @public
   */
  onSelectionChange(callback) {
    this.observers_.register('selection-change', (file) => {
      // console.log('Selected file is ', file);
      if (callback) {
        callback(file);
      }
    });
  }

  _newElement() {
    const props = {};
    if (this.multiple) {
      props.multiple = 'multiple';
    }
    return React.createElement(FileInput, {
      inputProps: props,
      disabled: this.disabled,
      text: this.text,
      buttonText: this.buttonText,
      fill: this.fill,
      onInputChange: (el) => {
        this.text = el.target.files[0].name;
        this.render();
        this.observers_.notify('selection-change', this.multiple ? el.target.files : el.target.files[0]);
      },
    });
  }
};

/**
 * A skeleton to ease the creation of a minimal Blueprintjs radio group element.
 *
 * @memberOf module:blueprintjs
 * @extends {blueprintjs.Blueprintjs}
 * @type {blueprintjs.MinimalRadioGroup}
 */
blueprintjs.MinimalRadioGroup = class extends blueprintjs.Blueprintjs {

  /**
   * @param {Element} container the parent element.
   * @param {string} label the group label (optional).
   * @param {boolean} inline true iif the radio buttons are to be displayed inline horizontally, false otherwise. (optional).
   * @constructor
   */
  constructor(container, label, inline) {
    super(container);
    this.label_ = label;
    this.inline_ = inline;
    this.observers_ = new observers.Subject();
    this.disabled_ = false;
    this.items_ = [];
    this.selectedItem_ = null;
    this.render();
  }

  get disabled() {
    return this.disabled_;
  }

  set disabled(value) {
    this.disabled_ = value;
    this.render();
  }

  get items() {
    return this.items_;
  }

  set items(values) {
    this.items_ = values;
    this.render();
  }

  get selectedItem() {
    return this.selectedItem_;
  }

  set selectedItem(value) {
    this.selectedItem_ = value;
    this.render();
  }

  /**
   * Listen to the `selection-change` event.
   *
   * @param {function(string): void} callback the callback to call when the event is triggered.
   * @name onSelectionChange
   * @function
   * @public
   */
  onSelectionChange(callback) {
    this.observers_.register('selection-change', (value) => {
      // console.log('Selected option is ', value);
      if (callback) {
        callback(value);
      }
    });
  }

  _newElement() {
    return React.createElement(RadioGroup, {
      label: this.label_,
      inline: this.inline_,
      disabled: this.disabled,
      options: this.items,
      selectedValue: this.selectedItem,
      onChange: (event) => {
        const selection = this.items.find(item => item.value === event.currentTarget.value);
        if (selection) {
          this.selectedItem = selection.value;
          this.observers_.notify('selection-change', selection);
        }
      },
    });
  }
};

/**
 * A skeleton to ease the creation of a minimal Blueprintjs text input element.
 *
 * @memberOf module:blueprintjs
 * @extends {blueprintjs.Blueprintjs}
 * @type {blueprintjs.MinimalTextInput}
 */
blueprintjs.MinimalTextInput = class extends blueprintjs.Blueprintjs {

  /**
   * @param {Element} container the parent element.
   * @param {string} defaultValue the input default value (optional).
   * @param {string} icon the icon name (optional).
   * @param {string} intent the input intent in {none, primary, success, warning, danger} (optional).
   *
   * @constructor
   */
  constructor(container, defaultValue, icon, intent) {
    super(container);
    this.defaultValue_ = defaultValue;
    this.icon_ = icon;
    this.intent_ = intent;
    this.observers_ = new observers.Subject();
    this.id_ = 'i' + Math.random().toString(36).substring(2, 12);
    this.disabled_ = false;
    this.fillContainer_ = true;
    this.placeholder_ = null;
    this.render();
  }

  get icon() {
    return this.icon_;
  }

  set icon(value) {
    this.icon_ = value;
    this.render();
  }

  get intent() {
    return this.intent_;
  }

  set intent(value) {
    this.intent_ = value;
    this.render();
  }

  get fillContainer() {
    return this.fillContainer_;
  }

  set fillContainer(value) {
    this.fillContainer_ = value;
    this.render();
  }

  get disabled() {
    return this.disabled_;
  }

  set disabled(value) {
    this.disabled_ = value;
    this.render();
  }

  get placeholder() {
    return this.placeholder_;
  }

  set placeholder(value) {
    this.placeholder_ = value;
    this.render();
  }

  get value() {
    return document.getElementById(this.id_).value;
  }

  _newElement() {
    return React.createElement(InputGroup, {
      id: this.id_,
      disabled: this.disabled,
      placeholder: this.placeholder,
      defaultValue: this.defaultValue_,
      fill: this.fillContainer,
      leftIcon: this.icon,
      intent: this.intent,
    });
  }
};

/**
 * A skeleton to ease the creation of a minimal Blueprintjs text input element.
 *
 * @memberOf module:blueprintjs
 * @extends {blueprintjs.Blueprintjs}
 * @type {blueprintjs.MinimalNumericInput}
 */
blueprintjs.MinimalNumericInput = class extends blueprintjs.Blueprintjs {

  /**
   * @param {Element} container the parent element.
   * @param {number} min the minimum value.
   * @param {number} max the maximum value.
   * @param {number} increment the internal increment.
   * @param {string} defaultValue the input default value (optional).
   * @param {string} icon the icon name (optional).
   * @param {string} intent the input intent in {none, primary, success, warning, danger} (optional).
   *
   * @constructor
   */
  constructor(container, min, max, increment, defaultValue, icon, intent) {
    super(container);
    this.min_ = min;
    this.max_ = max;
    this.increment_ = increment;
    this.defaultValue_ = defaultValue;
    this.icon_ = icon;
    this.intent_ = intent;
    this.id_ = 'i' + Math.random().toString(36).substring(2, 12);
    this.observers_ = new observers.Subject();
    this.disabled_ = false;
    this.fillContainer_ = true;
    this.placeholder_ = null;
    this.render();
  }

  get icon() {
    return this.icon_;
  }

  set icon(value) {
    this.icon_ = value;
    this.render();
  }

  get intent() {
    return this.intent_;
  }

  set intent(value) {
    this.intent_ = value;
    this.render();
  }

  get fillContainer() {
    return this.fillContainer_;
  }

  set fillContainer(value) {
    this.fillContainer_ = value;
    this.render();
  }

  get disabled() {
    return this.disabled_;
  }

  set disabled(value) {
    this.disabled_ = value;
    this.render();
  }

  get placeholder() {
    return this.placeholder_;
  }

  set placeholder(value) {
    this.placeholder_ = value;
    this.render();
  }

  get value() {
    return document.getElementById(this.id_).value;
  }

  /**
   * Listen to the `value-change` event.
   *
   * @param {function(number): void} callback the callback to call when the event is triggered.
   * @name onValueChange
   * @function
   * @public
   */
  onValueChange(callback) {
    this.observers_.register('value-change', (value) => {
      // console.log('Selected value is ' + value);
      if (callback) {
        callback(value);
      }
    });
  }

  _newElement() {
    return React.createElement(NumericInput, {
      id: this.id_,
      min: this.min_,
      max: this.max_,
      stepSize: this.increment_,
      disabled: this.disabled,
      placeholder: this.placeholder,
      defaultValue: this.defaultValue_,
      fill: this.fillContainer,
      leftIcon: this.icon,
      intent: this.intent,
      onValueChange: (value) => {
        this.observers_.notify('value-change', value);
      }
    });
  }
};

/**
 * A skeleton to ease the creation of a minimal Blueprintjs button element.
 *
 * @memberOf module:blueprintjs
 * @extends {blueprintjs.Blueprintjs}
 * @type {blueprintjs.MinimalButton}
 */
blueprintjs.MinimalButton = class extends blueprintjs.Blueprintjs {

  /**
   * @param {Element} container the parent element.
   * @param {string} label the switch label.
   * @param {string} labelPosition the switch label position (in {left, center, right}) in respect to the element (optional).
   * @param {string} leftIcon the left icon name (optional).
   * @param {string} rightIcon the right icon name (optional).
   * @param {string} intent the input intent in {none, primary, success, warning, danger} (optional).
   *
   * @constructor
   */
  constructor(container, label, labelPosition, leftIcon, rightIcon, intent) {
    super(container);
    this.label_ = label;
    this.labelPosition_ = labelPosition === 'left' ? Alignment.LEFT : labelPosition === 'right' ? Alignment.RIGHT
      : Alignment.CENTER;
    this.leftIcon_ = leftIcon;
    this.rightIcon_ = rightIcon;
    this.intent_ = intent;
    this.observers_ = new observers.Subject();
    this.disabled_ = false;
    this.loading_ = false;
    this.fillContainer_ = true;
    this.render();
  }

  get leftIcon() {
    return this.leftIcon_;
  }

  set leftIcon(value) {
    this.leftIcon_ = value;
    this.render();
  }

  get rightIcon() {
    return this.rightIcon_;
  }

  set rightIcon(value) {
    this.rightIcon_ = value;
    this.render();
  }

  get intent() {
    return this.intent_;
  }

  set intent(value) {
    this.intent_ = value;
    this.render();
  }

  get fillContainer() {
    return this.fillContainer_;
  }

  set fillContainer(value) {
    this.fillContainer_ = value;
    this.render();
  }

  get disabled() {
    return this.disabled_;
  }

  set disabled(value) {
    this.disabled_ = value;
    this.render();
  }

  get loading() {
    return this.loading_;
  }

  set loading(value) {
    this.loading_ = value;
    this.render();
  }

  /**
   * Listen to the `click` event.
   *
   * @param {function(void): void} callback the callback to call when the event is triggered.
   * @name onClick
   * @function
   * @public
   */
  onClick(callback) {
    this.observers_.register('click', () => {
      // console.log('Clicked!');
      if (callback) {
        callback();
      }
    });
  }

  _newElement() {
    return React.createElement(Button, {
      text: this.label_,
      alignText: this.labelPosition_,
      disabled: this.disabled,
      fill: this.fillContainer,
      loading: this.loading,
      icon: this.leftIcon,
      rightIcon: this.rightIcon,
      intent: this.intent,
      onClick: () => {
        this.observers_.notify('click');
      }
    });
  }
};

/**
 * @module caches
 */
const caches = {};

/**
 * A very minimal cache. When the maximum size is reached, the oldest entry is removed from the cache.
 *
 * @param {number} maxSize the maximum number of entries to keep.
 * @memberOf module:caches
 * @constructor
 * @struct
 * @final
 */
caches.Cache = function (maxSize) {

  const maxSize_ = maxSize ? maxSize : 100;
  let queue_ = [];
  let map_ = {};

  /**
   * Returns the number of cached objects.
   *
   * @return {number} the number of cached objects.
   */
  this.size = function () {
    return Object.keys(map_).length;
  };

  /**
   * Check if a key has already been added to the cache.
   *
   * @param {string} key the key to check.
   * @return {boolean} true iif the key already exists, false otherwise.
   */
  this.contains = function (key) {
    return map_.hasOwnProperty(key);
  };

  /**
   * Adds a single cache entry.
   *
   * @param {string} key the entry key.
   * @param {*} value the entry value.
   * @return {*|null} the values previously associated with the given key.
   */
  this.put = function (key, value) {
    const prev = this.get(key);
    if (queue_.length >= maxSize_) {
      this.remove(queue_[0].key);
    }
    map_[key] = value;
    queue_.push({key: key, value: value});
    return prev;
  };

  /**
   * Returns a single cache entry.
   *
   * @param {string} key the key to get.
   * @return {*|null} the value associated with the given key.
   */
  this.get = function (key) {
    return this.contains(key) ? map_[key] : null;
  };

  /**
   * Returns a single cache entry or a default value if the cache key does not belong to the cache.
   *
   * @param {string} key the key to get.
   * @param {*|null} defaultValue the default value to return.
   */
  this.getOrDefault = function (key, defaultValue) {
    return this.contains(key) ? this.get(key) : defaultValue;
  };

  /**
   * Returns a single cache entry or add a new one if the cache key does not belong to the cache.
   *
   * @param key the key to get.
   * @param defaultValue the default to add to the cache.
   * @return {*|null}
   */
  this.getOrPut = function (key, defaultValue) {
    if (!this.contains(key)) {
      this.put(key, defaultValue);
    }
    return this.get(key);
  };

  /**
   * Removes a single cache entry.
   *
   * @param {string} key the key to evict.
   * @return {*|null} the value previously associated with the given key.
   */
  this.remove = function (key) {
    if (this.contains(key)) {
      const prev = this.get(key);
      queue_ = queue_.filter(entry => entry.key !== key);
      delete map_[key];
      return prev;
    }
    return null;
  };

  /**
   * Removes all cache entries.
   */
  this.invalidate = function () {
    map_ = {};
    queue_ = [];
  };
};

/**
 * @module dates
 */
const dates = {};

/**
 * Initializes a javascript {@link Date} from a string or number formatted as YYYYMMDD.
 *
 * @param {string|number} str a string or number formatted as YYYYMMDD.
 * @return {?Date} a javascript {@link Date}.
 * @memberOf module:dates
 */
dates.yyyyMmDdToDate = function (str) {
  str = str ? ('' + str).trim() : '';
  if (str.length === 8) {
    const year = parseInt(str.substring(0, 4), 10);
    const month = parseInt(str.substring(4, 6), 10);
    const day = parseInt(str.substring(6, 8), 10);
    return new Date(year, month - 1, day);
  }
  return null;
};

/**
 * Initializes a javascript {@link Date} from a string or number formatted as DDMMYYYY.
 *
 * @param {string|number} str a string or number formatted as DDMMYYYY.
 * @return {?Date} a javascript {@link Date}.
 * @memberOf module:dates
 */
dates.ddMmYyyyToDate = function (str) {
  str = str ? ('' + str).trim() : '';
  if (str.length === 8) {
    const day = parseInt(str.substring(0, 2), 10);
    const month = parseInt(str.substring(2, 4), 10);
    const year = parseInt(str.substring(4, 8), 10);
    return new Date(year, month - 1, day);
  }
  return null;
};

/**
 * Formats a javascript {@link Date} to a string formatted as YYYY-MM-DD.
 *
 * @param {Date} date a javascript {@link Date}.
 * @param {?string} separator a separator that will be inserted between the date parts.
 * @return {?string} a string formatted as YYYY-MM-DD.
 * @memberOf module:dates
 */
dates.dateToYyyyMmDd = function (date, separator) {
  separator = separator || separator === '' ? separator : '-';
  return date instanceof Date ? date.getFullYear() + separator + (date.getMonth() < 9 ? '0' : '') + (date.getMonth()
      + 1) + separator + (date.getDate() < 10 ? '0' : '') + date.getDate() : null;
};

/**
 * Formats a javascript {@link Date} to a string formatted as DD-MM-YYYY.
 *
 * @param {Date} date a javascript {@link Date}.
 * @param {?string} separator a separator that will be inserted between the date parts.
 * @return {?string} a string formatted as DD-MM-YYYY.
 * @memberOf module:dates
 */
dates.dateToDdMmYyyy = function (date, separator) {
  separator = separator || separator === '' ? separator : '-';
  return date instanceof Date ? (date.getDate() < 10 ? '0' : '') + date.getDate() + separator + (date.getMonth() < 9
      ? '0' : '') + (date.getMonth() + 1) + separator + date.getFullYear() : null;
};

/**
 * @module helpers
 */
const helpers = {};

/**
 * Converts a Javascript value to a base-64 encoded string.
 *
 * @param {*} obj a Javascript value, usually an object or array, to be converted.
 * @return {string} a base-64 encoded string.
 * @memberOf module:helpers
 */
helpers.toBase64 = function (obj) {
  return btoa(JSON.stringify(obj));
};

/**
 * Converts a base-64 encoded string to a Javascript value.
 *
 * @param {string} str a base-64 encoded string.
 * @return {*} a Javascript value.
 * @memberOf module:helpers
 */
helpers.fromBase64 = function (str) {
  return JSON.parse(atob(str));
};

/**
 * A version of {@link JSON.stringify} that returns a canonical JSON format.
 *
 * 'Canonical JSON' means that the same object should always be stringified to the exact same string.
 * JavaScripts native {@link JSON.stringify} does not guarantee any order for object keys when serializing.
 *
 * @param value the value to stringify.
 * @returns {string} the stringified value.
 * @memberOf module:helpers
 * @preserve The code is extracted from https://github.com/mirkokiefer/canonical-json.
 */
helpers.stringify = function (value) {

  function isObject(object) {
    return Object.prototype.toString.call(object) === '[object Object]'
  }

  function copyObjectWithSortedKeys(object) {
    if (isObject(object)) {
      const newObj = {};
      const keysSorted = Object.keys(object).sort();
      let key;
      for (let i = 0, len = keysSorted.length; i < len; i++) {
        key = keysSorted[i];
        newObj[key] = copyObjectWithSortedKeys(object[key]);
      }
      return newObj
    } else if (Array.isArray(object)) {
      return object.map(copyObjectWithSortedKeys)
    } else {
      return object
    }
  }

  return JSON.stringify(copyObjectWithSortedKeys(value))
};

/**
 * A simple 53-bits hashing algorithm with good enough distribution.
 *
 * @param {*} obj the value to hash.
 * @param {number} seed a seed.
 * @return {number} the hashed value.
 * @memberOf module:helpers
 * @preserve The code is extracted from https://stackoverflow.com/a/52171480.
 */
helpers.goodFastHash = function (obj, seed) {

  const newStr = obj ? helpers.stringify(obj) : '';
  const newSeed = seed ? seed : 0;
  let h1 = 0xdeadbeef ^ newSeed;
  let h2 = 0x41c6ce57 ^ newSeed;

  for (let i = 0, ch; i < newStr.length; i++) {
    ch = newStr.charCodeAt(i);
    h1 = Math.imul(h1 ^ ch, 2654435761);
    h2 = Math.imul(h2 ^ ch, 1597334677);
  }

  h1 = Math.imul(h1 ^ (h1 >>> 16), 2246822507) ^ Math.imul(h2 ^ (h2 >>> 13), 3266489909);
  h2 = Math.imul(h2 ^ (h2 >>> 16), 2246822507) ^ Math.imul(h1 ^ (h1 >>> 13), 3266489909);

  return 4294967296 * (2097151 & h2) + (h1 >>> 0);
};

/**
 * Inject multiple scripts.
 *
 * @param {Element} el the root node where the scripts will be injected.
 * @param {Array<string>} urls the scripts URL.
 * @return a {Promise<*>}.
 */
helpers.injectScripts = function (el, urls) {

  let promise = null;

  for (let i = 0; i < urls.length; i++) {
    if (promise) {
      promise = promise.then(() => this.injectScript(el, urls[i]));
    } else {
      promise = this.injectScript(el, urls[i]);
    }
  }
  return promise;
};

/**
 * Inject a single script.
 *
 * @param {Element} el the root node where the script will be injected.
 * @param {string} url the script URL.
 * @return a {Promise<*>}.
 * @preserve The code is extracted from https://gist.github.com/james2doyle/28a59f8692cec6f334773007b31a1523.
 */
helpers.injectScript = function (el, url) {
  return el ? new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.src = url;
    script.async = true;
    script.onerror = function (err) {
      console.log('Script failed : ' + url, err);
      reject(url, script, err);
    };
    script.onload = function () {
      console.log('Script loaded : ' + url);
      resolve(url, script);
    };
    el.appendChild(script);
  }) : Promise.reject('invalid node');
};

/**
 * Inject multiple stylesheets.
 *
 * @param {Element} el the root node where the scripts will be injected.
 * @param {Array<String>} urls the stylesheets URL.
 * @return a {Promise<*>}.
 */
helpers.injectStyles = function (el, urls) {

  let promise = null;

  for (let i = 0; i < urls.length; i++) {
    if (promise) {
      promise = promise.then(() => this.injectStyle(el, urls[i]));
    } else {
      promise = this.injectStyle(el, urls[i]);
    }
  }
  return promise;
};

/**
 * Inject a single stylesheet.
 *
 * @param {Element} el the root node where the script will be injected.
 * @param {string} url the stylesheet URL.
 * @return a {Promise<*>}.
 * @preserve The code is extracted from https://gist.github.com/james2doyle/28a59f8692cec6f334773007b31a1523.
 */
helpers.injectStyle = function (el, url) {
  return el ? new Promise((resolve, reject) => {
    const link = document.createElement('link');
    link.href = url;
    link.rel = 'stylesheet';
    el.appendChild(link);
    console.log('Stylesheet loaded : ' + url);
    resolve(url, link);
  }) : Promise.reject('invalid node');
};

/**
 * Chunk a list and gives the UI thread a chance to process any pending UI events between each chunk (keeps the UI active).
 *
 * @param {Array<Object>} array the array to chunk and process.
 * @param {function(Object, Object): void} callback the callback to call for each array element.
 * @param {Object} context misc. contextual information (optional).
 * @param {number} maxTimePerChunk the maximum time to spend (guidance) in the callback for each chunk (optional).
 *
 * @preserve The code is extracted from https://stackoverflow.com/a/10344560.
 */
helpers.forEach = function (array, callback, context, maxTimePerChunk) {

  array = array || [];
  context = context || {};
  callback = callback || function (item, context) {
  };
  maxTimePerChunk = maxTimePerChunk || 200;
  let index = 0;

  function now() {
    return new Date().getTime();
  }

  function doChunk() {

    const startTime = now();

    while (index < array.length && (now() - startTime) <= maxTimePerChunk) {
      callback(array[index], context);
      ++index;
    }
    if (index < array.length) {
      setTimeout(doChunk, 1);
    }
  }

  doChunk();
};

/**
 * Delay a javascript function call. Executes only the last call.
 *
 * @param func the function to execute.
 * @param timeout the delay before the function can be called.
 * @returns {function}
 */
helpers.debounceLast = function (func, timeout = 300) {
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => {
      func.apply(this, args);
    }, timeout);
  };
};

/**
 * Delay a javascript function call. Executes only the first call.
 *
 * @param func the function to execute.
 * @param timeout the delay before the function can be called again.
 * @returns {function}
 */
helpers.debounceFirst = function (func, timeout = 300) {
  let timer;
  return (...args) => {
    if (!timer) {
      func.apply(this, args);
    }
    clearTimeout(timer);
    timer = setTimeout(() => {
      timer = undefined;
    }, timeout);
  };
};

/**
 * Download a JSON object or an array of JSON objects.
 *
 * @param filename the name of the downloaded file.
 * @param data the data to download.
 */
helpers.download = function (filename, data) {

  const blob = new Blob([JSON.stringify(data)], {type: "application/json;charset=utf-8"});
  const isIE = !!document.documentMode;

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
};

/**
 * @module platform
 */
const platform = {};

/**
 * The HttpClient type.
 *
 * @memberOf module:platform
 * @constructor
 * @struct
 * @final
 */
platform.HttpClient = function () {

  let baseUrl_ = '';
  let baseUrlAutodetect_ = false;
  let token_ = '';
  let tokenAutodetect_ = false;

  const reset = () => {
    baseUrl_ = '';
    baseUrlAutodetect_ = false;
    token_ = '';
    tokenAutodetect_ = false;
  };

  const findTokenFromQueryString = () => {
    const urlParams = new URLSearchParams(window?.location?.search);
    const token = urlParams.get('token');
    return token ? token : '';
  };

  const findBaseUrlFromReferrer = () => {
    let origin = '';
    if (window && window.document && window.document.referrer) {
      const url = new URL(window.document.referrer);
      origin = url.origin;
    }
    return origin;
  };

  /**
   * Execute a Http request to a given platform endpoint.
   *
   * @param {string} endpoint the platform endpoint.
   * @param {Object} body the request payload.
   * @param {...*} customConfig the Http request configuration.
   * @return {Promise<Object>} the platform response.
   */
  const fetch = (endpoint, {body, ...customConfig} = {}) => {
    const headers = {'Content-Type': 'application/json'};
    const config = {
      method: 'GET', ...customConfig, headers: {
        ...headers, ...customConfig.headers,
      },
    };
    if (body) {
      if (config.method === 'GET') {
        endpoint += '?' + new URLSearchParams(body);
      } else {
        config.body = JSON.stringify(body);
      }
    }
    return window.fetch(endpoint, config).then(async response => {
      if (response.ok) {
        return await response.json();
      } else {
        const errorMessage = await response.json();
        return Promise.reject(new Error(errorMessage.error));
      }
    });
  };

  /**
   * Returns the API token.
   *
   * @return {string} the API token.
   */
  this.getToken = function () {
    return token_;
  };

  /**
   * Set the API token.
   *
   * @param {string} token The API token.
   */
  this.setToken = function (token) {
    tokenAutodetect_ = false;
    token_ = token;
  };

  /**
   * Checks if the API token is set.
   *
   * @returns {boolean} returns true iif the API token is set, false otherwise.
   */
  this.hasToken = function () {
    return token_ !== '';
  };

  /**
   * Returns the API base URL.
   *
   * @return {string} the API base URL.
   */
  this.getBaseUrl = function () {
    return baseUrl_;
  };

  /**
   * Set the API base URL.
   *
   * @param {string} url the API base URL.
   */
  this.setBaseUrl = function (url) {
    baseUrlAutodetect_ = false;
    baseUrl_ = url;
  };

  /**
   * Checks if the API base URL is set.
   *
   * @returns {boolean} true iif the API base URL is set, false otherwise.
   */
  this.hasBaseUrl = function () {
    return baseUrl_ !== '';
  };

  /**
   * Initializes the Http client.
   *
   * If you omit a parameter, we will try to autodetect it.
   * For `token`, we try to find it on the query string. Ex: `?token=your_api_token`.
   * For `baseUrl`, we try to find it from the referrer.
   *
   * @param {string} baseUrl the base URL eg. https://www.company.computablefacts.com
   * @param {string} token the token.
   */
  this.init = function (baseUrl, token) {

    reset();

    if (typeof token === 'undefined') {
      token_ = findTokenFromQueryString();
      tokenAutodetect_ = this.hasToken();
      // console.log('init-autodetect-token token=', token, '_tokenAutodetect=', _tokenAutodetect)
    } else {
      this.setToken(token);
    }

    if (typeof baseUrl === 'undefined') {
      baseUrl_ = findBaseUrlFromReferrer();
      baseUrlAutodetect_ = this.hasBaseUrl();
      // console.log('init-autodetect-baseUrl baseUrl=', baseUrl, 'baseUrlAutodetect_=', baseUrlAutodetect_)
    } else {
      this.setBaseUrl(baseUrl);
    }
  };

  /**
   * Checks if the API token and base URL have been automatically set.
   *
   * @return `true` if the API token and base URL have been automatically set during [[`init`]].
   */
  this.hasAutodetect = function () {
    return tokenAutodetect_ && baseUrlAutodetect_;
  };

  /**
   * Returns the user information based on the API token.
   *
   * @return {Promise<Object>} the user permissions and authorizations.
   */
  this.whoAmI = function () {
    return fetch(`${baseUrl_}/api/v2/public/whoami`, {
      headers: {
        Authorization: `Bearer ${token_}`
      }
    });
  };

  /**
   * Call the platform JSON-RPC endpoint.
   *
   * @param {Object} payload the request payload.
   * @return {Promise<Object>} the platform response.
   * @preserve The specification can be found at https://www.jsonrpc.org/specification.
   */
  this.fetch = function (payload) {
    return fetch(`${baseUrl_}/api/v2/public/json-rpc?api_token=${token_}`, {body: payload, method: 'POST'}).then(
        response => {
          if ('error' in response) {
            const error = response['error'];
            const message = '(' + error.code + ') ' + error.message + '\n' + JSON.stringify(error.data);
            return Promise.reject(new Error(message));
          }
          return response['result'];
        });
  };

  /**
   * Call the `execute-problog-query` platform endpoint.
   *
   * @param {Object} params the request payload.
   * @return {Promise<Object>} the platform response.
   */
  this.executeProblogQuery = function (params) {
    return this.fetch({
      jsonrpc: '2.0', id: Date.now(), method: 'execute-problog-query', params: params
    });
  };

  /**
   * Call the `execute-sql-query` platform endpoint.
   *
   * @param {Object} params the request payload.
   * @return {Promise<Object>} the platform response.
   */
  this.executeSqlQuery = function (params) {
    return this.fetch({
      jsonrpc: '2.0', id: Date.now(), method: 'execute-sql-query', params: params
    });
  };

  /**
   * Call the `find-objects` platform endpoint.
   *
   * @param params the request payload.
   * @return {Promise<Object>} the platform response.
   */
  this.findObjects = function (params) {
    return this.fetch({
      jsonrpc: '2.0', id: Date.now(), method: 'find-objects', params: params
    });
  };

  /**
   * Call the `get-objects` platform endpoint.
   *
   * @param params the request payload.
   * @return {Promise<Array<Object>>} the platform response.
   */
  this.getObjects = function (params) {
    return this.fetch({
      jsonrpc: '2.0', id: Date.now(), method: 'get-objects', params: params
    });
  };

  /**
   * Call the `get-flattened-objects` platform endpoint.
   *
   * @param params the request payload.
   * @return {Promise<Array<Object>>} the platform response.
   */
  this.getFlattenedObjects = function (params) {
    return this.fetch({
      jsonrpc: '2.0', id: Date.now(), method: 'get-flattened-objects', params: params
    });
  };

  /**
   * Call the `find-terms` platform endpoint.
   *
   * @param params the request payload.
   * @return {Promise<Object>} the platform response.
   */
  this.findTerms = function (params) {
    return this.fetch({
      jsonrpc: '2.0', id: Date.now(), method: 'find-terms', params: params
    });
  };

  /**
   * Sink a single event.
   *
   * @param {string} type the event type.
   * @param {Array<string>} propNames the event property names.
   * @param {Array<string>} propValues the event property values.
   * @return {Promise<Object>} the created fact.
   */
  this.sinkEvent = function (type, propNames, propValues) {

    if (propNames.length !== propValues.length) {
      throw "Mismatch between the number of names and values"
    }

    const typeNormalized = 'event_' + type.replace(/-/g, '_').toLowerCase();
    const startDate = new Date();

    return fetch(`${baseUrl_}/api/v2/facts`, {
      body: {
        data: [{
          type: typeNormalized,
          values: propValues.map(prop => '' + prop),
          is_valid: true,
          start_date: startDate.toISOString(),
        }]
      }, method: 'POST', headers: {
        Authorization: `Bearer ${token_}`
      }
    });
  };

  /**
   * Source one or more events.
   *
   * @param {string} type the event type.
   * @param {Array<string>} propNames the event property names.
   * @param {Array<Object>} propPatterns the list of patterns to match.
   * @param {number} maxNbResults the maximum number of events to return.
   * @return {Promise<Array<Object>>} an array of events.
   */
  this.sourceEventsAsObjects = function (type, propNames, propPatterns, maxNbResults) {
    return this.sourceEvents(type, propNames, propPatterns, maxNbResults, 'objects');
  };

  /**
   * Source one or more events.
   *
   * @param {string} type the event type.
   * @param {Array<string>} propNames the event property names.
   * @param {Array<Object>} propPatterns the list of patterns to match.
   * @param {number} maxNbResults the maximum number of events to return.
   * @return {Promise<Array<Array<string>>>} an array of events.
   */
  this.sourceEventsAsArrays = function (type, propNames, propPatterns, maxNbResults) {
    return this.sourceEvents(type, propNames, propPatterns, maxNbResults, 'arrays_with_header');
  };

  /**
   * Source one or more events.
   *
   * @param {string} type the event type.
   * @param {Array<string>} propNames the event property names.
   * @param {Object} propPatterns the list of patterns to match.
   * @param {number} maxNbResults the maximum number of events to return.
   * @param {string} format the returned events format. 'objects' returns an `Array<Object>`. Both 'arrays' and 'arrays_with_header' return an `Array<Array<string>>`.
   * @return {Promise<Array<Object>|Array<Array<string>>>} an array of events.
   */
  this.sourceEvents = function (type, propNames, propPatterns, maxNbResults, format) {

    const newRule = (eventType, eventPropertyNames, patterns) => {

      let result = eventType + '(';
      result += eventPropertyNames.map(prop => prop.toUpperCase()).join(', ');
      result += ') :- ';
      result += 'fn_mysql_materialize_facts("{{ app_url }}/api/v3/facts/no_namespace/';
      result += eventType;
      result += '?alea=' + Math.random().toString(36).substring(2, 12);
      const filtersQuery = Object.entries(patterns).map(entry => entry[0] + '=' + entry[1]).join('&');
      result += filtersQuery ? '&' + filtersQuery : '';
      result += '", "{{ client }}", "{{ env }}", "{{ sftp_host }}", "{{ sftp_username }}", "{{ sftp_password }}", ';
      result += eventPropertyNames.map((prop, i) => '"value_' + i + '", _, ' + prop.toUpperCase()).join(', ');
      result += ').';

      // console.log('newRule = ', result);
      return result.trim();
    };

    const pattern = {};

    for (let i = 0; i < propNames.length; i++) {
      if (propPatterns[propNames[i]]) {
        pattern['value_' + i] = propPatterns[propNames[i]];
      }
    }

    const typeNormalized = 'event_' + type.replace(/-/g, '_').toLowerCase();
    const rule = newRule(typeNormalized, propNames, pattern);
    const alea = Math.random().toString(36).substring(2, 8);

    return this.executeProblogQuery({
      problog_rules: [alea + '_' + rule],
      problog_query: alea + '_' + (rule.substring(0, rule.indexOf(':-')).trim()) + '?',
      format: format ? format : 'objects',
      sample_size: maxNbResults ? maxNbResults : 15,
    });
  };
};

/**
 * @module promises
 */
const promises = {};

/**
 * An object that has the ability to memoize promises returned by a given user-defined function.
 *
 * @param {number} maxCacheSize the maximum number of distinct calls to cache.
 * @param {Function} fn a user-defined function that returns a promise.
 * @memberOf module:promises
 * @constructor
 * @struct
 * @final
 */
promises.Memoize = function (maxCacheSize, fn) {

  // Stats
  let hit_ = 0;
  let miss_ = 0;

  // Cache
  const cache_ = new caches.Cache(maxCacheSize);
  const function_ = fn;

  /**
   * Either read the cache or call the user-defined function and get a new promise.
   *
   * @param {...*} args a list of arguments to pass to the user-defined function.
   * @return {Promise} a promise to be resolved at a later stage.
   * @suppress {checkTypes}
   */
  this.promise = function (...args) {

    const cacheKey = helpers.goodFastHash(Array.from(args), 123).toString(10);

    if (cache_.contains(cacheKey)) {
      hit_++;
      return cache_.get(cacheKey);
    }

    cache_.put(cacheKey, function_(...args).catch(err => {
      cache_.remove(cacheKey);
      throw err;
    }));

    miss_++;
    return cache_.get(cacheKey);
  };

  /**
   * Return the number of cache hits.
   *
   * @return {number} the number of hits.
   */
  this.hits = function () {
    return hit_;
  };

  /**
   * Return the number of cache misses.
   *
   * @return {number} the number of misses.
   */
  this.misses = function () {
    return miss_;
  };

  /**
   * Return the cache hit rate.
   *
   * @return {number} the hit rate.
   */
  this.hitRate = function () {
    return hit_ / (hit_ + miss_);
  };

  /**
   * Return the cache miss rate.
   *
   * @return {number} the miss rate.
   */
  this.missRate = function () {
    return miss_ / (hit_ + miss_);
  };
};

/**
 * @module webcomponents
 * @deprecated
 */
const webcomponents = {};

/**
 * A skeleton to ease the creation of web components.
 *
 * @memberOf module:webcomponents
 * @type {webcomponents.WebComponent}
 * @extends {HTMLElement}
 */
webcomponents.WebComponent = class extends HTMLElement {

  /**
   * @constructor
   */
  constructor() {
    super();
    this.attachShadow({mode: 'open'});
  }

  /**
   * Called every time the element is inserted into the DOM.
   *
   * @name connectedCallback
   * @function
   * @protected
   * @override
   */
  connectedCallback() {

    const styles = this.externalStyles();
    const scripts = this.externalScripts();
    const template = this.template();

    const wrapper = document.createElement('div');
    wrapper.id = 'wcw'; // wcw = Web Component Wrapper
    this.shadowRoot.appendChild(wrapper);

    if (styles && styles.length > 0 && scripts && scripts.length > 0) {
      helpers.injectStyles(wrapper, styles).then(() => {
        helpers.injectScripts(wrapper, scripts).then(() => {
          if (template !== '') {
            wrapper.insertAdjacentHTML('beforeend', template);
          }
          this.renderedCallback();
        });
      });
    } else if ((!styles || styles.length === 0) && scripts && scripts.length > 0) {
      helpers.injectScripts(wrapper, scripts).then(() => {
        if (template !== '') {
          wrapper.insertAdjacentHTML('beforeend', template);
        }
        this.renderedCallback();
      });
    } else if (styles && styles.length > 0 && (!scripts || scripts.length === 0)) {
      helpers.injectStyles(wrapper, styles).then(() => {
        if (template !== '') {
          wrapper.insertAdjacentHTML('beforeend', template);
        }
        this.renderedCallback();
      });
    } else {
      if (template !== '') {
        wrapper.insertAdjacentHTML('beforeend', template);
      }
      this.renderedCallback();
    }
  }

  /**
   * Called every time the element is removed from the DOM.
   *
   * @name disconnectedCallback
   * @function
   * @protected
   * @override
   */
  disconnectedCallback() {
  }

  /**
   * Called after the `template` has been added to the DOM.
   *
   * @name renderedCallback
   * @function
   * @protected
   * @override
   */
  renderedCallback() {
  }

  /**
   * A list of stylesheets URL.
   *
   * @return {Array<string>} an array of URL.
   * @name externalStyles
   * @function
   * @protected
   * @override
   */
  externalStyles() {
    return [];
  }

  /**
   * A list of scripts URL.
   *
   * @return {Array<string>} an array of URL.
   * @name externalScripts
   * @function
   * @protected
   * @override
   */
  externalScripts() {
    return [];
  }

  /**
   * Returns the component HTML template.
   *
   * @return {string} the HTML.
   * @name template
   * @function
   * @protected
   */
  template() {
    return ``;
  }

  /**
   * Emit a custom event.
   *
   * @param {string} type the event type.
   * @param {Object|null} data any data structure to pass along with the event.
   * @param {Node|null} elem the element to attach the event to.
   * @return {boolean} returns true if either event's cancelable attribute value is false or its preventDefault() method was not invoked, and false otherwise.
   * @name emit
   * @function
   * @protected
   */
  emit(type, data = {}, elem = document) {
    const event = new CustomEvent(type, {
      bubbles: true, cancelable: true, detail: {
        component: this, data: data
      }
    });
    return (elem ? elem : document).dispatchEvent(event);
  }

  /**
   * Returns the component attribute value or a default value if none was found.
   *
   * @param {string} attr the attribute to get.
   * @param {string|null} defaultValue the default value.
   * @return {string|null} the attribute value if any, defaultValue otherwise.
   * @name getAttributeOrDefault
   * @function
   * @protected
   */
  getAttributeOrDefault(attr, defaultValue) {
    return this.hasAttribute(attr) ? this.getAttribute(attr) : defaultValue;
  }

  /**
   * Returns the first element with a given identifier or class name.
   *
   * @param {string} idOrClassName the identifier or class name to match.
   * @return {Element|null} an HTML element.
   * @name getElement
   * @function
   * @protected
   */
  getElement(idOrClassName) {
    return this.shadowRoot.querySelector(idOrClassName);
  }

  /**
   * Get the first page element with a given identifier or class name.
   *
   * @param {string} idOrClassName the identifier or class name to match.
   * @return {Element|null} an HTML element.
   * @name getPageElement
   * @function
   * @protected
   */
  getPageElement(idOrClassName) {
    return document.querySelector(idOrClassName);
  }

  /**
   * Add a given class to a given element.
   *
   * @param {string} idOrClassName the identifier or class name to match.
   * @param {string} className the class name to add.
   * @name addCssClass
   * @function
   * @protected
   */
  addCssClass(idOrClassName, className) {
    const el = this.getElement(idOrClassName);
    if (el) {
      el.classList.add(className);
    }
  }

  /**
   * Remove a given class from a given element.
   *
   * @param {string} idOrClassName the identifier or class name to match.
   * @param {string} className the class name to remove.
   * @name removeCssClass
   * @function
   * @protected
   */
  removeCssClass(idOrClassName, className) {
    const el = this.getElement(idOrClassName);
    if (el) {
      el.classList.remove(className);
    }
  }

  /**
   * Add a class if it does not already exist on a given element, remove it otherwise.
   *
   * @param {string} idOrClassName the identifier or class name to match.
   * @param {string} className the class name to toggle.
   * @name toggleCssClass
   * @function
   * @protected
   */
  toggleCssClass(idOrClassName, className) {
    const el = this.getElement(idOrClassName);
    if (el) {
      el.classList.toggle(className);
    }
  }

  /**
   * Check if a given element has a given class.
   *
   * @param {string} idOrClassName the identifier or class name to match.
   * @param {string} className the class name to search.
   * @return true if the element contains the given class, false otherwise.
   * @name includesCssClass
   * @function
   * @protected
   */
  includesCssClass(idOrClassName, className) {
    const el = this.getElement(idOrClassName);
    if (el) {
      return el.classList.contains(className);
    }
    return false;
  }

  /**
   * Get all classes associated to a given element.
   *
   * @param {string} idOrClassName the identifier or class name to match.
   * @return {Array<string>} the class names.
   * @name getAllCssClasses
   * @function
   * @protected
   */
  getAllCssClasses(idOrClassName) {
    const el = this.getElement(idOrClassName);
    if (el) {
      return el.className.split(' ').map(clazz => clazz.trim());
    }
    return [];
  }

  /**
   * Replace all classes associated to a given element.
   *
   * @param {string} idOrClassName the identifier or class name to match.
   * @param {Array<string>|string} classes the class names.
   * @name replaceAllCssClasses
   * @function
   * @protected
   */
  replaceAllCssClasses(idOrClassName, classes) {
    const el = this.getElement(idOrClassName);
    if (el) {
      if (Array.isArray(classes)) {
        el.className = classes.join(' ');
      } else {
        el.className = classes;
      }
    }
  }

  /**
   * Get the style associated to a given element.
   *
   * @param {string} idOrClassName the identifier or class name to match.
   * @return {CSSStyleDeclaration|null} the element style.
   * @name getStyle
   * @function
   * @protected
   */
  getStyle(idOrClassName) {
    const el = this.getElement(idOrClassName);
    if (el) {
      return el.style;
    }
    return null;
  }

  /**
   * Get the actual computed style associated to a given element.
   *
   * @param {string} idOrClassName the identifier or class name to match.
   * @return {CSSStyleDeclaration|null} the element computed style.
   * @name getComputedStyle
   * @function
   * @protected
   */
  getComputedStyle(idOrClassName) {
    const el = this.getElement(idOrClassName);
    if (el) {
      return window.getComputedStyle(el);
    }
    return null;
  }

  /**
   * Set the text associated to a given element.
   *
   * @param {string} idOrClassName the identifier or class name to match.
   * @param {string} text the text to set.
   * @name setText
   * @function
   * @protected
   */
  setText(idOrClassName, text) {
    const el = this.getElement(idOrClassName);
    if (el) {
      el.textContent = text;
    }
  }

  /**
   * Set the HTML associated to a given element.
   *
   * @param {string} idOrClassName the identifier or class name to match.
   * @param {string} html the html to set.
   * @name setHtml
   * @function
   * @protected
   */
  setHtml(idOrClassName, html) {
    const el = this.getElement(idOrClassName);
    if (el) {
      el.innerHTML = html;
    }
  }

  /**
   * Append an element to a given element.
   *
   * @param {string} idOrClassName the identifier or class name to match.
   * @param {Element} element the element to append.
   * @name replaceContent
   * @function
   * @protected
   */
  replaceContent(idOrClassName, element) {
    const el = this.getElement(idOrClassName);
    if (el) {
      el.innerHTML = '';
      el.appendChild(element);
    }
  }
};

export { arrays, blueprintjs, caches, dates, helpers, observers, platform, promises, strings, webcomponents, widgets };
//# sourceMappingURL=main.js.map
