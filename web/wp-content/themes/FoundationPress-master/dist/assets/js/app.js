/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 1);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports) {

module.exports = jQuery;

/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(2);


/***/ }),
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


__webpack_require__(23);

$(document).foundation(); // import $ from 'jquery';
// import whatInput from 'what-input';
//
// window.$ = $;
//
// import Foundation from 'foundation-sites';
// If you want to pick and choose which modules to include, comment out the above and uncomment
// the line below

/***/ }),
/* 3 */,
/* 4 */,
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.ignoreMousedisappear = exports.onLoad = exports.transitionend = exports.RegExpEscape = exports.GetYoDigits = exports.rtl = undefined;

var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

// Core Foundation Utilities, utilized in a number of places.

/**
 * Returns a boolean for RTL support
 */
function rtl() {
  return (0, _jquery2.default)('html').attr('dir') === 'rtl';
}

/**
 * returns a random base-36 uid with namespacing
 * @function
 * @param {Number} length - number of random base-36 digits desired. Increase for more random strings.
 * @param {String} namespace - name of plugin to be incorporated in uid, optional.
 * @default {String} '' - if no plugin name is provided, nothing is appended to the uid.
 * @returns {String} - unique id
 */
function GetYoDigits(length, namespace) {
  length = length || 6;
  return Math.round(Math.pow(36, length + 1) - Math.random() * Math.pow(36, length)).toString(36).slice(1) + (namespace ? '-' + namespace : '');
}

/**
 * Escape a string so it can be used as a regexp pattern
 * @function
 * @see https://stackoverflow.com/a/9310752/4317384
 *
 * @param {String} str - string to escape.
 * @returns {String} - escaped string
 */
function RegExpEscape(str) {
  return str.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&');
}

function transitionend($elem) {
  var transitions = {
    'transition': 'transitionend',
    'WebkitTransition': 'webkitTransitionEnd',
    'MozTransition': 'transitionend',
    'OTransition': 'otransitionend'
  };
  var elem = document.createElement('div'),
      end;

  for (var t in transitions) {
    if (typeof elem.style[t] !== 'undefined') {
      end = transitions[t];
    }
  }
  if (end) {
    return end;
  } else {
    end = setTimeout(function () {
      $elem.triggerHandler('transitionend', [$elem]);
    }, 1);
    return 'transitionend';
  }
}

/**
 * Return an event type to listen for window load.
 *
 * If `$elem` is passed, an event will be triggered on `$elem`. If window is already loaded, the event will still be triggered.
 * If `handler` is passed, attach it to the event on `$elem`.
 * Calling `onLoad` without handler allows you to get the event type that will be triggered before attaching the handler by yourself.
 * @function
 *
 * @param {Object} [] $elem - jQuery element on which the event will be triggered if passed.
 * @param {Function} [] handler - function to attach to the event.
 * @returns {String} - event type that should or will be triggered.
 */
function onLoad($elem, handler) {
  var didLoad = document.readyState === 'complete';
  var eventType = (didLoad ? '_didLoad' : 'load') + '.zf.util.onLoad';
  var cb = function cb() {
    return $elem.triggerHandler(eventType);
  };

  if ($elem) {
    if (handler) $elem.one(eventType, handler);

    if (didLoad) setTimeout(cb);else (0, _jquery2.default)(window).one('load', cb);
  }

  return eventType;
}

/**
 * Retuns an handler for the `mouseleave` that ignore disappeared mouses.
 *
 * If the mouse "disappeared" from the document (like when going on a browser UI element, See https://git.io/zf-11410),
 * the event is ignored.
 * - If the `ignoreLeaveWindow` is `true`, the event is ignored when the user actually left the window
 *   (like by switching to an other window with [Alt]+[Tab]).
 * - If the `ignoreReappear` is `true`, the event will be ignored when the mouse will reappear later on the document
 *   outside of the element it left.
 *
 * @function
 *
 * @param {Function} [] handler - handler for the filtered `mouseleave` event to watch.
 * @param {Object} [] options - object of options:
 * - {Boolean} [false] ignoreLeaveWindow - also ignore when the user switched windows.
 * - {Boolean} [false] ignoreReappear - also ignore when the mouse reappeared outside of the element it left.
 * @returns {Function} - filtered handler to use to listen on the `mouseleave` event.
 */
function ignoreMousedisappear(handler) {
  var _ref = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {},
      _ref$ignoreLeaveWindo = _ref.ignoreLeaveWindow,
      ignoreLeaveWindow = _ref$ignoreLeaveWindo === undefined ? false : _ref$ignoreLeaveWindo,
      _ref$ignoreReappear = _ref.ignoreReappear,
      ignoreReappear = _ref$ignoreReappear === undefined ? false : _ref$ignoreReappear;

  return function leaveEventHandler(eLeave) {
    for (var _len = arguments.length, rest = Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
      rest[_key - 1] = arguments[_key];
    }

    var callback = handler.bind.apply(handler, [this, eLeave].concat(rest));

    // The mouse left: call the given callback if the mouse entered elsewhere
    if (eLeave.relatedTarget !== null) {
      return callback();
    }

    // Otherwise, check if the mouse actually left the window.
    // In firefox if the user switched between windows, the window sill have the focus by the time
    // the event is triggered. We have to debounce the event to test this case.
    setTimeout(function leaveEventDebouncer() {
      if (!ignoreLeaveWindow && document.hasFocus && !document.hasFocus()) {
        return callback();
      }

      // Otherwise, wait for the mouse to reeapear outside of the element,
      if (!ignoreReappear) {
        (0, _jquery2.default)(document).one('mouseenter', function reenterEventHandler(eReenter) {
          if (!(0, _jquery2.default)(eLeave.currentTarget).has(eReenter.target).length) {
            // Fill where the mouse finally entered.
            eLeave.relatedTarget = eReenter.target;
            callback();
          }
        });
      }
    }, 0);
  };
}

exports.rtl = rtl;
exports.GetYoDigits = GetYoDigits;
exports.RegExpEscape = RegExpEscape;
exports.transitionend = transitionend;
exports.onLoad = onLoad;
exports.ignoreMousedisappear = ignoreMousedisappear;

/***/ }),
/* 6 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Plugin = undefined;

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

var _foundationCore = __webpack_require__(5);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

// Abstract class for providing lifecycle hooks. Expect plugins to define AT LEAST
// {function} _setup (replaces previous constructor),
// {function} _destroy (replaces previous destroy)
var Plugin = function () {
  function Plugin(element, options) {
    _classCallCheck(this, Plugin);

    this._setup(element, options);
    var pluginName = getPluginName(this);
    this.uuid = (0, _foundationCore.GetYoDigits)(6, pluginName);

    if (!this.$element.attr('data-' + pluginName)) {
      this.$element.attr('data-' + pluginName, this.uuid);
    }
    if (!this.$element.data('zfPlugin')) {
      this.$element.data('zfPlugin', this);
    }
    /**
     * Fires when the plugin has initialized.
     * @event Plugin#init
     */
    this.$element.trigger('init.zf.' + pluginName);
  }

  _createClass(Plugin, [{
    key: 'destroy',
    value: function destroy() {
      this._destroy();
      var pluginName = getPluginName(this);
      this.$element.removeAttr('data-' + pluginName).removeData('zfPlugin')
      /**
       * Fires when the plugin has been destroyed.
       * @event Plugin#destroyed
       */
      .trigger('destroyed.zf.' + pluginName);
      for (var prop in this) {
        this[prop] = null; //clean up script to prep for garbage collection.
      }
    }
  }]);

  return Plugin;
}();

// Convert PascalCase to kebab-case
// Thank you: http://stackoverflow.com/a/8955580


function hyphenate(str) {
  return str.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase();
}

function getPluginName(obj) {
  if (typeof obj.constructor.name !== 'undefined') {
    return hyphenate(obj.constructor.name);
  } else {
    return hyphenate(obj.className);
  }
}

exports.Plugin = Plugin;

/***/ }),
/* 7 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.MediaQuery = undefined;

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

// Default set of media queries
var defaultQueries = {
  'default': 'only screen',
  landscape: 'only screen and (orientation: landscape)',
  portrait: 'only screen and (orientation: portrait)',
  retina: 'only screen and (-webkit-min-device-pixel-ratio: 2),' + 'only screen and (min--moz-device-pixel-ratio: 2),' + 'only screen and (-o-min-device-pixel-ratio: 2/1),' + 'only screen and (min-device-pixel-ratio: 2),' + 'only screen and (min-resolution: 192dpi),' + 'only screen and (min-resolution: 2dppx)'
};

// matchMedia() polyfill - Test a CSS media type/query in JS.
// Authors & copyright(c) 2012: Scott Jehl, Paul Irish, Nicholas Zakas, David Knight. MIT license
/* eslint-disable */
window.matchMedia || (window.matchMedia = function () {
  "use strict";

  // For browsers that support matchMedium api such as IE 9 and webkit

  var styleMedia = window.styleMedia || window.media;

  // For those that don't support matchMedium
  if (!styleMedia) {
    var style = document.createElement('style'),
        script = document.getElementsByTagName('script')[0],
        info = null;

    style.type = 'text/css';
    style.id = 'matchmediajs-test';

    if (!script) {
      document.head.appendChild(style);
    } else {
      script.parentNode.insertBefore(style, script);
    }

    // 'style.currentStyle' is used by IE <= 8 and 'window.getComputedStyle' for all other browsers
    info = 'getComputedStyle' in window && window.getComputedStyle(style, null) || style.currentStyle;

    styleMedia = {
      matchMedium: function matchMedium(media) {
        var text = '@media ' + media + '{ #matchmediajs-test { width: 1px; } }';

        // 'style.styleSheet' is used by IE <= 8 and 'style.textContent' for all other browsers
        if (style.styleSheet) {
          style.styleSheet.cssText = text;
        } else {
          style.textContent = text;
        }

        // Test if media query is true or false
        return info.width === '1px';
      }
    };
  }

  return function (media) {
    return {
      matches: styleMedia.matchMedium(media || 'all'),
      media: media || 'all'
    };
  };
}());
/* eslint-enable */

var MediaQuery = {
  queries: [],

  current: '',

  /**
   * Initializes the media query helper, by extracting the breakpoint list from the CSS and activating the breakpoint watcher.
   * @function
   * @private
   */
  _init: function _init() {
    var self = this;
    var $meta = (0, _jquery2.default)('meta.foundation-mq');
    if (!$meta.length) {
      (0, _jquery2.default)('<meta class="foundation-mq">').appendTo(document.head);
    }

    var extractedStyles = (0, _jquery2.default)('.foundation-mq').css('font-family');
    var namedQueries;

    namedQueries = parseStyleToObject(extractedStyles);

    for (var key in namedQueries) {
      if (namedQueries.hasOwnProperty(key)) {
        self.queries.push({
          name: key,
          value: 'only screen and (min-width: ' + namedQueries[key] + ')'
        });
      }
    }

    this.current = this._getCurrentSize();

    this._watcher();
  },


  /**
   * Checks if the screen is at least as wide as a breakpoint.
   * @function
   * @param {String} size - Name of the breakpoint to check.
   * @returns {Boolean} `true` if the breakpoint matches, `false` if it's smaller.
   */
  atLeast: function atLeast(size) {
    var query = this.get(size);

    if (query) {
      return window.matchMedia(query).matches;
    }

    return false;
  },


  /**
   * Checks if the screen matches to a breakpoint.
   * @function
   * @param {String} size - Name of the breakpoint to check, either 'small only' or 'small'. Omitting 'only' falls back to using atLeast() method.
   * @returns {Boolean} `true` if the breakpoint matches, `false` if it does not.
   */
  is: function is(size) {
    size = size.trim().split(' ');
    if (size.length > 1 && size[1] === 'only') {
      if (size[0] === this._getCurrentSize()) return true;
    } else {
      return this.atLeast(size[0]);
    }
    return false;
  },


  /**
   * Gets the media query of a breakpoint.
   * @function
   * @param {String} size - Name of the breakpoint to get.
   * @returns {String|null} - The media query of the breakpoint, or `null` if the breakpoint doesn't exist.
   */
  get: function get(size) {
    for (var i in this.queries) {
      if (this.queries.hasOwnProperty(i)) {
        var query = this.queries[i];
        if (size === query.name) return query.value;
      }
    }

    return null;
  },


  /**
   * Gets the current breakpoint name by testing every breakpoint and returning the last one to match (the biggest one).
   * @function
   * @private
   * @returns {String} Name of the current breakpoint.
   */
  _getCurrentSize: function _getCurrentSize() {
    var matched;

    for (var i = 0; i < this.queries.length; i++) {
      var query = this.queries[i];

      if (window.matchMedia(query.value).matches) {
        matched = query;
      }
    }

    if ((typeof matched === 'undefined' ? 'undefined' : _typeof(matched)) === 'object') {
      return matched.name;
    } else {
      return matched;
    }
  },


  /**
   * Activates the breakpoint watcher, which fires an event on the window whenever the breakpoint changes.
   * @function
   * @private
   */
  _watcher: function _watcher() {
    var _this = this;

    (0, _jquery2.default)(window).off('resize.zf.mediaquery').on('resize.zf.mediaquery', function () {
      var newSize = _this._getCurrentSize(),
          currentSize = _this.current;

      if (newSize !== currentSize) {
        // Change the current media query
        _this.current = newSize;

        // Broadcast the media query change on the window
        (0, _jquery2.default)(window).trigger('changed.zf.mediaquery', [newSize, currentSize]);
      }
    });
  }
};

// Thank you: https://github.com/sindresorhus/query-string
function parseStyleToObject(str) {
  var styleObject = {};

  if (typeof str !== 'string') {
    return styleObject;
  }

  str = str.trim().slice(1, -1); // browsers re-quote string style values

  if (!str) {
    return styleObject;
  }

  styleObject = str.split('&').reduce(function (ret, param) {
    var parts = param.replace(/\+/g, ' ').split('=');
    var key = parts[0];
    var val = parts[1];
    key = decodeURIComponent(key);

    // missing `=` should be `null`:
    // http://w3.org/TR/2012/WD-url-20120524/#collect-url-parameters
    val = typeof val === 'undefined' ? null : decodeURIComponent(val);

    if (!ret.hasOwnProperty(key)) {
      ret[key] = val;
    } else if (Array.isArray(ret[key])) {
      ret[key].push(val);
    } else {
      ret[key] = [ret[key], val];
    }
    return ret;
  }, {});

  return styleObject;
}

exports.MediaQuery = MediaQuery;

/***/ }),
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/*******************************************
 *                                         *
 * This util was created by Marius Olbertz *
 * Please thank Marius on GitHub /owlbertz *
 * or the web http://www.mariusolbertz.de/ *
 *                                         *
 ******************************************/



Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Keyboard = undefined;

var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

var _foundationCore = __webpack_require__(5);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var keyCodes = {
  9: 'TAB',
  13: 'ENTER',
  27: 'ESCAPE',
  32: 'SPACE',
  35: 'END',
  36: 'HOME',
  37: 'ARROW_LEFT',
  38: 'ARROW_UP',
  39: 'ARROW_RIGHT',
  40: 'ARROW_DOWN'
};

var commands = {};

// Functions pulled out to be referenceable from internals
function findFocusable($element) {
  if (!$element) {
    return false;
  }
  return $element.find('a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, object, embed, *[tabindex], *[contenteditable]').filter(function () {
    if (!(0, _jquery2.default)(this).is(':visible') || (0, _jquery2.default)(this).attr('tabindex') < 0) {
      return false;
    } //only have visible elements and those that have a tabindex greater or equal 0
    return true;
  });
}

function parseKey(event) {
  var key = keyCodes[event.which || event.keyCode] || String.fromCharCode(event.which).toUpperCase();

  // Remove un-printable characters, e.g. for `fromCharCode` calls for CTRL only events
  key = key.replace(/\W+/, '');

  if (event.shiftKey) key = 'SHIFT_' + key;
  if (event.ctrlKey) key = 'CTRL_' + key;
  if (event.altKey) key = 'ALT_' + key;

  // Remove trailing underscore, in case only modifiers were used (e.g. only `CTRL_ALT`)
  key = key.replace(/_$/, '');

  return key;
}

var Keyboard = {
  keys: getKeyCodes(keyCodes),

  /**
   * Parses the (keyboard) event and returns a String that represents its key
   * Can be used like Foundation.parseKey(event) === Foundation.keys.SPACE
   * @param {Event} event - the event generated by the event handler
   * @return String key - String that represents the key pressed
   */
  parseKey: parseKey,

  /**
   * Handles the given (keyboard) event
   * @param {Event} event - the event generated by the event handler
   * @param {String} component - Foundation component's name, e.g. Slider or Reveal
   * @param {Objects} functions - collection of functions that are to be executed
   */
  handleKey: function handleKey(event, component, functions) {
    var commandList = commands[component],
        keyCode = this.parseKey(event),
        cmds,
        command,
        fn;

    if (!commandList) return console.warn('Component not defined!');

    if (typeof commandList.ltr === 'undefined') {
      // this component does not differentiate between ltr and rtl
      cmds = commandList; // use plain list
    } else {
      // merge ltr and rtl: if document is rtl, rtl overwrites ltr and vice versa
      if ((0, _foundationCore.rtl)()) cmds = _jquery2.default.extend({}, commandList.ltr, commandList.rtl);else cmds = _jquery2.default.extend({}, commandList.rtl, commandList.ltr);
    }
    command = cmds[keyCode];

    fn = functions[command];
    if (fn && typeof fn === 'function') {
      // execute function  if exists
      var returnValue = fn.apply();
      if (functions.handled || typeof functions.handled === 'function') {
        // execute function when event was handled
        functions.handled(returnValue);
      }
    } else {
      if (functions.unhandled || typeof functions.unhandled === 'function') {
        // execute function when event was not handled
        functions.unhandled();
      }
    }
  },


  /**
   * Finds all focusable elements within the given `$element`
   * @param {jQuery} $element - jQuery object to search within
   * @return {jQuery} $focusable - all focusable elements within `$element`
   */

  findFocusable: findFocusable,

  /**
   * Returns the component name name
   * @param {Object} component - Foundation component, e.g. Slider or Reveal
   * @return String componentName
   */

  register: function register(componentName, cmds) {
    commands[componentName] = cmds;
  },


  // TODO9438: These references to Keyboard need to not require global. Will 'this' work in this context?
  //
  /**
   * Traps the focus in the given element.
   * @param  {jQuery} $element  jQuery object to trap the foucs into.
   */
  trapFocus: function trapFocus($element) {
    var $focusable = findFocusable($element),
        $firstFocusable = $focusable.eq(0),
        $lastFocusable = $focusable.eq(-1);

    $element.on('keydown.zf.trapfocus', function (event) {
      if (event.target === $lastFocusable[0] && parseKey(event) === 'TAB') {
        event.preventDefault();
        $firstFocusable.focus();
      } else if (event.target === $firstFocusable[0] && parseKey(event) === 'SHIFT_TAB') {
        event.preventDefault();
        $lastFocusable.focus();
      }
    });
  },

  /**
   * Releases the trapped focus from the given element.
   * @param  {jQuery} $element  jQuery object to release the focus for.
   */
  releaseFocus: function releaseFocus($element) {
    $element.off('keydown.zf.trapfocus');
  }
};

/*
 * Constants for easier comparing.
 * Can be used like Foundation.parseKey(event) === Foundation.keys.SPACE
 */
function getKeyCodes(kcs) {
  var k = {};
  for (var kc in kcs) {
    k[kcs[kc]] = kcs[kc];
  }return k;
}

exports.Keyboard = Keyboard;

/***/ }),
/* 9 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Triggers = undefined;

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

var _foundationCore = __webpack_require__(5);

var _foundationUtil = __webpack_require__(10);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var MutationObserver = function () {
  var prefixes = ['WebKit', 'Moz', 'O', 'Ms', ''];
  for (var i = 0; i < prefixes.length; i++) {
    if (prefixes[i] + 'MutationObserver' in window) {
      return window[prefixes[i] + 'MutationObserver'];
    }
  }
  return false;
}();

var triggers = function triggers(el, type) {
  el.data(type).split(' ').forEach(function (id) {
    (0, _jquery2.default)('#' + id)[type === 'close' ? 'trigger' : 'triggerHandler'](type + '.zf.trigger', [el]);
  });
};

var Triggers = {
  Listeners: {
    Basic: {},
    Global: {}
  },
  Initializers: {}
};

Triggers.Listeners.Basic = {
  openListener: function openListener() {
    triggers((0, _jquery2.default)(this), 'open');
  },
  closeListener: function closeListener() {
    var id = (0, _jquery2.default)(this).data('close');
    if (id) {
      triggers((0, _jquery2.default)(this), 'close');
    } else {
      (0, _jquery2.default)(this).trigger('close.zf.trigger');
    }
  },
  toggleListener: function toggleListener() {
    var id = (0, _jquery2.default)(this).data('toggle');
    if (id) {
      triggers((0, _jquery2.default)(this), 'toggle');
    } else {
      (0, _jquery2.default)(this).trigger('toggle.zf.trigger');
    }
  },
  closeableListener: function closeableListener(e) {
    e.stopPropagation();
    var animation = (0, _jquery2.default)(this).data('closable');

    if (animation !== '') {
      _foundationUtil.Motion.animateOut((0, _jquery2.default)(this), animation, function () {
        (0, _jquery2.default)(this).trigger('closed.zf');
      });
    } else {
      (0, _jquery2.default)(this).fadeOut().trigger('closed.zf');
    }
  },
  toggleFocusListener: function toggleFocusListener() {
    var id = (0, _jquery2.default)(this).data('toggle-focus');
    (0, _jquery2.default)('#' + id).triggerHandler('toggle.zf.trigger', [(0, _jquery2.default)(this)]);
  }
};

// Elements with [data-open] will reveal a plugin that supports it when clicked.
Triggers.Initializers.addOpenListener = function ($elem) {
  $elem.off('click.zf.trigger', Triggers.Listeners.Basic.openListener);
  $elem.on('click.zf.trigger', '[data-open]', Triggers.Listeners.Basic.openListener);
};

// Elements with [data-close] will close a plugin that supports it when clicked.
// If used without a value on [data-close], the event will bubble, allowing it to close a parent component.
Triggers.Initializers.addCloseListener = function ($elem) {
  $elem.off('click.zf.trigger', Triggers.Listeners.Basic.closeListener);
  $elem.on('click.zf.trigger', '[data-close]', Triggers.Listeners.Basic.closeListener);
};

// Elements with [data-toggle] will toggle a plugin that supports it when clicked.
Triggers.Initializers.addToggleListener = function ($elem) {
  $elem.off('click.zf.trigger', Triggers.Listeners.Basic.toggleListener);
  $elem.on('click.zf.trigger', '[data-toggle]', Triggers.Listeners.Basic.toggleListener);
};

// Elements with [data-closable] will respond to close.zf.trigger events.
Triggers.Initializers.addCloseableListener = function ($elem) {
  $elem.off('close.zf.trigger', Triggers.Listeners.Basic.closeableListener);
  $elem.on('close.zf.trigger', '[data-closeable], [data-closable]', Triggers.Listeners.Basic.closeableListener);
};

// Elements with [data-toggle-focus] will respond to coming in and out of focus
Triggers.Initializers.addToggleFocusListener = function ($elem) {
  $elem.off('focus.zf.trigger blur.zf.trigger', Triggers.Listeners.Basic.toggleFocusListener);
  $elem.on('focus.zf.trigger blur.zf.trigger', '[data-toggle-focus]', Triggers.Listeners.Basic.toggleFocusListener);
};

// More Global/complex listeners and triggers
Triggers.Listeners.Global = {
  resizeListener: function resizeListener($nodes) {
    if (!MutationObserver) {
      //fallback for IE 9
      $nodes.each(function () {
        (0, _jquery2.default)(this).triggerHandler('resizeme.zf.trigger');
      });
    }
    //trigger all listening elements and signal a resize event
    $nodes.attr('data-events', "resize");
  },
  scrollListener: function scrollListener($nodes) {
    if (!MutationObserver) {
      //fallback for IE 9
      $nodes.each(function () {
        (0, _jquery2.default)(this).triggerHandler('scrollme.zf.trigger');
      });
    }
    //trigger all listening elements and signal a scroll event
    $nodes.attr('data-events', "scroll");
  },
  closeMeListener: function closeMeListener(e, pluginId) {
    var plugin = e.namespace.split('.')[0];
    var plugins = (0, _jquery2.default)('[data-' + plugin + ']').not('[data-yeti-box="' + pluginId + '"]');

    plugins.each(function () {
      var _this = (0, _jquery2.default)(this);
      _this.triggerHandler('close.zf.trigger', [_this]);
    });
  }

  // Global, parses whole document.
};Triggers.Initializers.addClosemeListener = function (pluginName) {
  var yetiBoxes = (0, _jquery2.default)('[data-yeti-box]'),
      plugNames = ['dropdown', 'tooltip', 'reveal'];

  if (pluginName) {
    if (typeof pluginName === 'string') {
      plugNames.push(pluginName);
    } else if ((typeof pluginName === 'undefined' ? 'undefined' : _typeof(pluginName)) === 'object' && typeof pluginName[0] === 'string') {
      plugNames.concat(pluginName);
    } else {
      console.error('Plugin names must be strings');
    }
  }
  if (yetiBoxes.length) {
    var listeners = plugNames.map(function (name) {
      return 'closeme.zf.' + name;
    }).join(' ');

    (0, _jquery2.default)(window).off(listeners).on(listeners, Triggers.Listeners.Global.closeMeListener);
  }
};

function debounceGlobalListener(debounce, trigger, listener) {
  var timer = void 0,
      args = Array.prototype.slice.call(arguments, 3);
  (0, _jquery2.default)(window).off(trigger).on(trigger, function (e) {
    if (timer) {
      clearTimeout(timer);
    }
    timer = setTimeout(function () {
      listener.apply(null, args);
    }, debounce || 10); //default time to emit scroll event
  });
}

Triggers.Initializers.addResizeListener = function (debounce) {
  var $nodes = (0, _jquery2.default)('[data-resize]');
  if ($nodes.length) {
    debounceGlobalListener(debounce, 'resize.zf.trigger', Triggers.Listeners.Global.resizeListener, $nodes);
  }
};

Triggers.Initializers.addScrollListener = function (debounce) {
  var $nodes = (0, _jquery2.default)('[data-scroll]');
  if ($nodes.length) {
    debounceGlobalListener(debounce, 'scroll.zf.trigger', Triggers.Listeners.Global.scrollListener, $nodes);
  }
};

Triggers.Initializers.addMutationEventsListener = function ($elem) {
  if (!MutationObserver) {
    return false;
  }
  var $nodes = $elem.find('[data-resize], [data-scroll], [data-mutate]');

  //element callback
  var listeningElementsMutation = function listeningElementsMutation(mutationRecordsList) {
    var $target = (0, _jquery2.default)(mutationRecordsList[0].target);

    //trigger the event handler for the element depending on type
    switch (mutationRecordsList[0].type) {
      case "attributes":
        if ($target.attr("data-events") === "scroll" && mutationRecordsList[0].attributeName === "data-events") {
          $target.triggerHandler('scrollme.zf.trigger', [$target, window.pageYOffset]);
        }
        if ($target.attr("data-events") === "resize" && mutationRecordsList[0].attributeName === "data-events") {
          $target.triggerHandler('resizeme.zf.trigger', [$target]);
        }
        if (mutationRecordsList[0].attributeName === "style") {
          $target.closest("[data-mutate]").attr("data-events", "mutate");
          $target.closest("[data-mutate]").triggerHandler('mutateme.zf.trigger', [$target.closest("[data-mutate]")]);
        }
        break;

      case "childList":
        $target.closest("[data-mutate]").attr("data-events", "mutate");
        $target.closest("[data-mutate]").triggerHandler('mutateme.zf.trigger', [$target.closest("[data-mutate]")]);
        break;

      default:
        return false;
      //nothing
    }
  };

  if ($nodes.length) {
    //for each element that needs to listen for resizing, scrolling, or mutation add a single observer
    for (var i = 0; i <= $nodes.length - 1; i++) {
      var elementObserver = new MutationObserver(listeningElementsMutation);
      elementObserver.observe($nodes[i], { attributes: true, childList: true, characterData: false, subtree: true, attributeFilter: ["data-events", "style"] });
    }
  }
};

Triggers.Initializers.addSimpleListeners = function () {
  var $document = (0, _jquery2.default)(document);

  Triggers.Initializers.addOpenListener($document);
  Triggers.Initializers.addCloseListener($document);
  Triggers.Initializers.addToggleListener($document);
  Triggers.Initializers.addCloseableListener($document);
  Triggers.Initializers.addToggleFocusListener($document);
};

Triggers.Initializers.addGlobalListeners = function () {
  var $document = (0, _jquery2.default)(document);
  Triggers.Initializers.addMutationEventsListener($document);
  Triggers.Initializers.addResizeListener();
  Triggers.Initializers.addScrollListener();
  Triggers.Initializers.addClosemeListener();
};

Triggers.init = function ($, Foundation) {
  (0, _foundationCore.onLoad)($(window), function () {
    if ($.triggersInitialized !== true) {
      Triggers.Initializers.addSimpleListeners();
      Triggers.Initializers.addGlobalListeners();
      $.triggersInitialized = true;
    }
  });

  if (Foundation) {
    Foundation.Triggers = Triggers;
    // Legacy included to be backwards compatible for now.
    Foundation.IHearYou = Triggers.Initializers.addGlobalListeners;
  }
};

exports.Triggers = Triggers;

/***/ }),
/* 10 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Motion = exports.Move = undefined;

var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

var _foundationCore = __webpack_require__(5);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

/**
 * Motion module.
 * @module foundation.motion
 */

var initClasses = ['mui-enter', 'mui-leave'];
var activeClasses = ['mui-enter-active', 'mui-leave-active'];

var Motion = {
  animateIn: function animateIn(element, animation, cb) {
    animate(true, element, animation, cb);
  },

  animateOut: function animateOut(element, animation, cb) {
    animate(false, element, animation, cb);
  }
};

function Move(duration, elem, fn) {
  var anim,
      prog,
      start = null;
  // console.log('called');

  if (duration === 0) {
    fn.apply(elem);
    elem.trigger('finished.zf.animate', [elem]).triggerHandler('finished.zf.animate', [elem]);
    return;
  }

  function move(ts) {
    if (!start) start = ts;
    // console.log(start, ts);
    prog = ts - start;
    fn.apply(elem);

    if (prog < duration) {
      anim = window.requestAnimationFrame(move, elem);
    } else {
      window.cancelAnimationFrame(anim);
      elem.trigger('finished.zf.animate', [elem]).triggerHandler('finished.zf.animate', [elem]);
    }
  }
  anim = window.requestAnimationFrame(move);
}

/**
 * Animates an element in or out using a CSS transition class.
 * @function
 * @private
 * @param {Boolean} isIn - Defines if the animation is in or out.
 * @param {Object} element - jQuery or HTML object to animate.
 * @param {String} animation - CSS class to use.
 * @param {Function} cb - Callback to run when animation is finished.
 */
function animate(isIn, element, animation, cb) {
  element = (0, _jquery2.default)(element).eq(0);

  if (!element.length) return;

  var initClass = isIn ? initClasses[0] : initClasses[1];
  var activeClass = isIn ? activeClasses[0] : activeClasses[1];

  // Set up the animation
  reset();

  element.addClass(animation).css('transition', 'none');

  requestAnimationFrame(function () {
    element.addClass(initClass);
    if (isIn) element.show();
  });

  // Start the animation
  requestAnimationFrame(function () {
    element[0].offsetWidth;
    element.css('transition', '').addClass(activeClass);
  });

  // Clean up the animation when it finishes
  element.one((0, _foundationCore.transitionend)(element), finish);

  // Hides the element (for out animations), resets the element, and runs a callback
  function finish() {
    if (!isIn) element.hide();
    reset();
    if (cb) cb.apply(element);
  }

  // Resets transitions and removes motion-specific classes
  function reset() {
    element[0].style.transitionDuration = 0;
    element.removeClass(initClass + ' ' + activeClass + ' ' + animation);
  }
}

exports.Move = Move;
exports.Motion = Motion;

/***/ }),
/* 11 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Box = undefined;

var _foundationCore = __webpack_require__(5);

var Box = {
  ImNotTouchingYou: ImNotTouchingYou,
  OverlapArea: OverlapArea,
  GetDimensions: GetDimensions,
  GetOffsets: GetOffsets,
  GetExplicitOffsets: GetExplicitOffsets

  /**
   * Compares the dimensions of an element to a container and determines collision events with container.
   * @function
   * @param {jQuery} element - jQuery object to test for collisions.
   * @param {jQuery} parent - jQuery object to use as bounding container.
   * @param {Boolean} lrOnly - set to true to check left and right values only.
   * @param {Boolean} tbOnly - set to true to check top and bottom values only.
   * @default if no parent object passed, detects collisions with `window`.
   * @returns {Boolean} - true if collision free, false if a collision in any direction.
   */
};function ImNotTouchingYou(element, parent, lrOnly, tbOnly, ignoreBottom) {
  return OverlapArea(element, parent, lrOnly, tbOnly, ignoreBottom) === 0;
};

function OverlapArea(element, parent, lrOnly, tbOnly, ignoreBottom) {
  var eleDims = GetDimensions(element),
      topOver,
      bottomOver,
      leftOver,
      rightOver;
  if (parent) {
    var parDims = GetDimensions(parent);

    bottomOver = parDims.height + parDims.offset.top - (eleDims.offset.top + eleDims.height);
    topOver = eleDims.offset.top - parDims.offset.top;
    leftOver = eleDims.offset.left - parDims.offset.left;
    rightOver = parDims.width + parDims.offset.left - (eleDims.offset.left + eleDims.width);
  } else {
    bottomOver = eleDims.windowDims.height + eleDims.windowDims.offset.top - (eleDims.offset.top + eleDims.height);
    topOver = eleDims.offset.top - eleDims.windowDims.offset.top;
    leftOver = eleDims.offset.left - eleDims.windowDims.offset.left;
    rightOver = eleDims.windowDims.width - (eleDims.offset.left + eleDims.width);
  }

  bottomOver = ignoreBottom ? 0 : Math.min(bottomOver, 0);
  topOver = Math.min(topOver, 0);
  leftOver = Math.min(leftOver, 0);
  rightOver = Math.min(rightOver, 0);

  if (lrOnly) {
    return leftOver + rightOver;
  }
  if (tbOnly) {
    return topOver + bottomOver;
  }

  // use sum of squares b/c we care about overlap area.
  return Math.sqrt(topOver * topOver + bottomOver * bottomOver + leftOver * leftOver + rightOver * rightOver);
}

/**
 * Uses native methods to return an object of dimension values.
 * @function
 * @param {jQuery || HTML} element - jQuery object or DOM element for which to get the dimensions. Can be any element other that document or window.
 * @returns {Object} - nested object of integer pixel values
 * TODO - if element is window, return only those values.
 */
function GetDimensions(elem) {
  elem = elem.length ? elem[0] : elem;

  if (elem === window || elem === document) {
    throw new Error("I'm sorry, Dave. I'm afraid I can't do that.");
  }

  var rect = elem.getBoundingClientRect(),
      parRect = elem.parentNode.getBoundingClientRect(),
      winRect = document.body.getBoundingClientRect(),
      winY = window.pageYOffset,
      winX = window.pageXOffset;

  return {
    width: rect.width,
    height: rect.height,
    offset: {
      top: rect.top + winY,
      left: rect.left + winX
    },
    parentDims: {
      width: parRect.width,
      height: parRect.height,
      offset: {
        top: parRect.top + winY,
        left: parRect.left + winX
      }
    },
    windowDims: {
      width: winRect.width,
      height: winRect.height,
      offset: {
        top: winY,
        left: winX
      }
    }
  };
}

/**
 * Returns an object of top and left integer pixel values for dynamically rendered elements,
 * such as: Tooltip, Reveal, and Dropdown. Maintained for backwards compatibility, and where
 * you don't know alignment, but generally from
 * 6.4 forward you should use GetExplicitOffsets, as GetOffsets conflates position and alignment.
 * @function
 * @param {jQuery} element - jQuery object for the element being positioned.
 * @param {jQuery} anchor - jQuery object for the element's anchor point.
 * @param {String} position - a string relating to the desired position of the element, relative to it's anchor
 * @param {Number} vOffset - integer pixel value of desired vertical separation between anchor and element.
 * @param {Number} hOffset - integer pixel value of desired horizontal separation between anchor and element.
 * @param {Boolean} isOverflow - if a collision event is detected, sets to true to default the element to full width - any desired offset.
 * TODO alter/rewrite to work with `em` values as well/instead of pixels
 */
function GetOffsets(element, anchor, position, vOffset, hOffset, isOverflow) {
  console.log("NOTE: GetOffsets is deprecated in favor of GetExplicitOffsets and will be removed in 6.5");
  switch (position) {
    case 'top':
      return (0, _foundationCore.rtl)() ? GetExplicitOffsets(element, anchor, 'top', 'left', vOffset, hOffset, isOverflow) : GetExplicitOffsets(element, anchor, 'top', 'right', vOffset, hOffset, isOverflow);
    case 'bottom':
      return (0, _foundationCore.rtl)() ? GetExplicitOffsets(element, anchor, 'bottom', 'left', vOffset, hOffset, isOverflow) : GetExplicitOffsets(element, anchor, 'bottom', 'right', vOffset, hOffset, isOverflow);
    case 'center top':
      return GetExplicitOffsets(element, anchor, 'top', 'center', vOffset, hOffset, isOverflow);
    case 'center bottom':
      return GetExplicitOffsets(element, anchor, 'bottom', 'center', vOffset, hOffset, isOverflow);
    case 'center left':
      return GetExplicitOffsets(element, anchor, 'left', 'center', vOffset, hOffset, isOverflow);
    case 'center right':
      return GetExplicitOffsets(element, anchor, 'right', 'center', vOffset, hOffset, isOverflow);
    case 'left bottom':
      return GetExplicitOffsets(element, anchor, 'bottom', 'left', vOffset, hOffset, isOverflow);
    case 'right bottom':
      return GetExplicitOffsets(element, anchor, 'bottom', 'right', vOffset, hOffset, isOverflow);
    // Backwards compatibility... this along with the reveal and reveal full
    // classes are the only ones that didn't reference anchor
    case 'center':
      return {
        left: $eleDims.windowDims.offset.left + $eleDims.windowDims.width / 2 - $eleDims.width / 2 + hOffset,
        top: $eleDims.windowDims.offset.top + $eleDims.windowDims.height / 2 - ($eleDims.height / 2 + vOffset)
      };
    case 'reveal':
      return {
        left: ($eleDims.windowDims.width - $eleDims.width) / 2 + hOffset,
        top: $eleDims.windowDims.offset.top + vOffset
      };
    case 'reveal full':
      return {
        left: $eleDims.windowDims.offset.left,
        top: $eleDims.windowDims.offset.top
      };
      break;
    default:
      return {
        left: (0, _foundationCore.rtl)() ? $anchorDims.offset.left - $eleDims.width + $anchorDims.width - hOffset : $anchorDims.offset.left + hOffset,
        top: $anchorDims.offset.top + $anchorDims.height + vOffset
      };

  }
}

function GetExplicitOffsets(element, anchor, position, alignment, vOffset, hOffset, isOverflow) {
  var $eleDims = GetDimensions(element),
      $anchorDims = anchor ? GetDimensions(anchor) : null;

  var topVal, leftVal;

  // set position related attribute

  switch (position) {
    case 'top':
      topVal = $anchorDims.offset.top - ($eleDims.height + vOffset);
      break;
    case 'bottom':
      topVal = $anchorDims.offset.top + $anchorDims.height + vOffset;
      break;
    case 'left':
      leftVal = $anchorDims.offset.left - ($eleDims.width + hOffset);
      break;
    case 'right':
      leftVal = $anchorDims.offset.left + $anchorDims.width + hOffset;
      break;
  }

  // set alignment related attribute
  switch (position) {
    case 'top':
    case 'bottom':
      switch (alignment) {
        case 'left':
          leftVal = $anchorDims.offset.left + hOffset;
          break;
        case 'right':
          leftVal = $anchorDims.offset.left - $eleDims.width + $anchorDims.width - hOffset;
          break;
        case 'center':
          leftVal = isOverflow ? hOffset : $anchorDims.offset.left + $anchorDims.width / 2 - $eleDims.width / 2 + hOffset;
          break;
      }
      break;
    case 'right':
    case 'left':
      switch (alignment) {
        case 'bottom':
          topVal = $anchorDims.offset.top - vOffset + $anchorDims.height - $eleDims.height;
          break;
        case 'top':
          topVal = $anchorDims.offset.top + vOffset;
          break;
        case 'center':
          topVal = $anchorDims.offset.top + vOffset + $anchorDims.height / 2 - $eleDims.height / 2;
          break;
      }
      break;
  }
  return { top: topVal, left: leftVal };
}

exports.Box = Box;

/***/ }),
/* 12 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.onImagesLoaded = undefined;

var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

/**
 * Runs a callback function when images are fully loaded.
 * @param {Object} images - Image(s) to check if loaded.
 * @param {Func} callback - Function to execute when image is fully loaded.
 */
function onImagesLoaded(images, callback) {
  var self = this,
      unloaded = images.length;

  if (unloaded === 0) {
    callback();
  }

  images.each(function () {
    // Check if image is loaded
    if (this.complete && typeof this.naturalWidth !== 'undefined') {
      singleImageLoaded();
    } else {
      // If the above check failed, simulate loading on detached element.
      var image = new Image();
      // Still count image as loaded if it finalizes with an error.
      var events = "load.zf.images error.zf.images";
      (0, _jquery2.default)(image).one(events, function me(event) {
        // Unbind the event listeners. We're using 'one' but only one of the two events will have fired.
        (0, _jquery2.default)(this).off(events, me);
        singleImageLoaded();
      });
      image.src = (0, _jquery2.default)(this).attr('src');
    }
  });

  function singleImageLoaded() {
    unloaded--;
    if (unloaded === 0) {
      callback();
    }
  }
}

exports.onImagesLoaded = onImagesLoaded;

/***/ }),
/* 13 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Nest = undefined;

var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var Nest = {
  Feather: function Feather(menu) {
    var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'zf';

    menu.attr('role', 'menubar');

    var items = menu.find('li').attr({ 'role': 'menuitem' }),
        subMenuClass = 'is-' + type + '-submenu',
        subItemClass = subMenuClass + '-item',
        hasSubClass = 'is-' + type + '-submenu-parent',
        applyAria = type !== 'accordion'; // Accordions handle their own ARIA attriutes.

    items.each(function () {
      var $item = (0, _jquery2.default)(this),
          $sub = $item.children('ul');

      if ($sub.length) {
        $item.addClass(hasSubClass);
        $sub.addClass('submenu ' + subMenuClass).attr({ 'data-submenu': '' });
        if (applyAria) {
          $item.attr({
            'aria-haspopup': true,
            'aria-label': $item.children('a:first').text()
          });
          // Note:  Drilldowns behave differently in how they hide, and so need
          // additional attributes.  We should look if this possibly over-generalized
          // utility (Nest) is appropriate when we rework menus in 6.4
          if (type === 'drilldown') {
            $item.attr({ 'aria-expanded': false });
          }
        }
        $sub.addClass('submenu ' + subMenuClass).attr({
          'data-submenu': '',
          'role': 'menubar'
        });
        if (type === 'drilldown') {
          $sub.attr({ 'aria-hidden': true });
        }
      }

      if ($item.parent('[data-submenu]').length) {
        $item.addClass('is-submenu-item ' + subItemClass);
      }
    });

    return;
  },
  Burn: function Burn(menu, type) {
    var //items = menu.find('li'),
    subMenuClass = 'is-' + type + '-submenu',
        subItemClass = subMenuClass + '-item',
        hasSubClass = 'is-' + type + '-submenu-parent';

    menu.find('>li, > li > ul, .menu, .menu > li, [data-submenu] > li').removeClass(subMenuClass + ' ' + subItemClass + ' ' + hasSubClass + ' is-submenu-item submenu is-active').removeAttr('data-submenu').css('display', '');
  }
};

exports.Nest = Nest;

/***/ }),
/* 14 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Touch = undefined;

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }(); //**************************************************
//**Work inspired by multiple jquery swipe plugins**
//**Done by Yohai Ararat ***************************
//**************************************************

var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var Touch = {};

var startPosX,
    startPosY,
    startTime,
    elapsedTime,
    startEvent,
    isMoving = false,
    didMoved = false;

function onTouchEnd(e) {
  this.removeEventListener('touchmove', onTouchMove);
  this.removeEventListener('touchend', onTouchEnd);

  // If the touch did not move, consider it as a "tap"
  if (!didMoved) {
    var tapEvent = _jquery2.default.Event('tap', startEvent || e);
    (0, _jquery2.default)(this).trigger(tapEvent);
  }

  startEvent = null;
  isMoving = false;
  didMoved = false;
}

function onTouchMove(e) {
  if (_jquery2.default.spotSwipe.preventDefault) {
    e.preventDefault();
  }

  if (isMoving) {
    var x = e.touches[0].pageX;
    var y = e.touches[0].pageY;
    var dx = startPosX - x;
    var dy = startPosY - y;
    var dir;
    didMoved = true;
    elapsedTime = new Date().getTime() - startTime;
    if (Math.abs(dx) >= _jquery2.default.spotSwipe.moveThreshold && elapsedTime <= _jquery2.default.spotSwipe.timeThreshold) {
      dir = dx > 0 ? 'left' : 'right';
    }
    // else if(Math.abs(dy) >= $.spotSwipe.moveThreshold && elapsedTime <= $.spotSwipe.timeThreshold) {
    //   dir = dy > 0 ? 'down' : 'up';
    // }
    if (dir) {
      e.preventDefault();
      onTouchEnd.apply(this, arguments);
      (0, _jquery2.default)(this).trigger(_jquery2.default.Event('swipe', e), dir).trigger(_jquery2.default.Event('swipe' + dir, e));
    }
  }
}

function onTouchStart(e) {

  if (e.touches.length == 1) {
    startPosX = e.touches[0].pageX;
    startPosY = e.touches[0].pageY;
    startEvent = e;
    isMoving = true;
    didMoved = false;
    startTime = new Date().getTime();
    this.addEventListener('touchmove', onTouchMove, false);
    this.addEventListener('touchend', onTouchEnd, false);
  }
}

function init() {
  this.addEventListener && this.addEventListener('touchstart', onTouchStart, false);
}

function teardown() {
  this.removeEventListener('touchstart', onTouchStart);
}

var SpotSwipe = function () {
  function SpotSwipe($) {
    _classCallCheck(this, SpotSwipe);

    this.version = '1.0.0';
    this.enabled = 'ontouchstart' in document.documentElement;
    this.preventDefault = false;
    this.moveThreshold = 75;
    this.timeThreshold = 200;
    this.$ = $;
    this._init();
  }

  _createClass(SpotSwipe, [{
    key: '_init',
    value: function _init() {
      var $ = this.$;
      $.event.special.swipe = { setup: init };
      $.event.special.tap = { setup: init };

      $.each(['left', 'up', 'down', 'right'], function () {
        $.event.special['swipe' + this] = { setup: function setup() {
            $(this).on('swipe', $.noop);
          } };
      });
    }
  }]);

  return SpotSwipe;
}();

/****************************************************
 * As far as I can tell, both setupSpotSwipe and    *
 * setupTouchHandler should be idempotent,          *
 * because they directly replace functions &        *
 * values, and do not add event handlers directly.  *
 ****************************************************/

Touch.setupSpotSwipe = function ($) {
  $.spotSwipe = new SpotSwipe($);
};

/****************************************************
 * Method for adding pseudo drag events to elements *
 ***************************************************/
Touch.setupTouchHandler = function ($) {
  $.fn.addTouch = function () {
    this.each(function (i, el) {
      $(el).bind('touchstart touchmove touchend touchcancel', function (event) {
        //we pass the original event object because the jQuery event
        //object is normalized to w3c specs and does not provide the TouchList
        handleTouch(event);
      });
    });

    var handleTouch = function handleTouch(event) {
      var touches = event.changedTouches,
          first = touches[0],
          eventTypes = {
        touchstart: 'mousedown',
        touchmove: 'mousemove',
        touchend: 'mouseup'
      },
          type = eventTypes[event.type],
          simulatedEvent;

      if ('MouseEvent' in window && typeof window.MouseEvent === 'function') {
        simulatedEvent = new window.MouseEvent(type, {
          'bubbles': true,
          'cancelable': true,
          'screenX': first.screenX,
          'screenY': first.screenY,
          'clientX': first.clientX,
          'clientY': first.clientY
        });
      } else {
        simulatedEvent = document.createEvent('MouseEvent');
        simulatedEvent.initMouseEvent(type, true, true, window, 1, first.screenX, first.screenY, first.clientX, first.clientY, false, false, false, false, 0 /*left*/, null);
      }
      first.target.dispatchEvent(simulatedEvent);
    };
  };
};

Touch.init = function ($) {

  if (typeof $.spotSwipe === 'undefined') {
    Touch.setupSpotSwipe($);
    Touch.setupTouchHandler($);
  }
};

exports.Touch = Touch;

/***/ }),
/* 15 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Timer = undefined;

var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function Timer(elem, options, cb) {
  var _this = this,
      duration = options.duration,
      //options is an object for easily adding features later.
  nameSpace = Object.keys(elem.data())[0] || 'timer',
      remain = -1,
      start,
      timer;

  this.isPaused = false;

  this.restart = function () {
    remain = -1;
    clearTimeout(timer);
    this.start();
  };

  this.start = function () {
    this.isPaused = false;
    // if(!elem.data('paused')){ return false; }//maybe implement this sanity check if used for other things.
    clearTimeout(timer);
    remain = remain <= 0 ? duration : remain;
    elem.data('paused', false);
    start = Date.now();
    timer = setTimeout(function () {
      if (options.infinite) {
        _this.restart(); //rerun the timer.
      }
      if (cb && typeof cb === 'function') {
        cb();
      }
    }, remain);
    elem.trigger('timerstart.zf.' + nameSpace);
  };

  this.pause = function () {
    this.isPaused = true;
    //if(elem.data('paused')){ return false; }//maybe implement this sanity check if used for other things.
    clearTimeout(timer);
    elem.data('paused', true);
    var end = Date.now();
    remain = remain - (end - start);
    elem.trigger('timerpaused.zf.' + nameSpace);
  };
}

exports.Timer = Timer;

/***/ }),
/* 16 */,
/* 17 */,
/* 18 */,
/* 19 */,
/* 20 */,
/* 21 */,
/* 22 */,
/* 23 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

var _foundation = __webpack_require__(24);

var _foundationCore = __webpack_require__(5);

var _foundationUtil = __webpack_require__(11);

var _foundationUtil2 = __webpack_require__(12);

var _foundationUtil3 = __webpack_require__(8);

var _foundationUtil4 = __webpack_require__(7);

var _foundationUtil5 = __webpack_require__(10);

var _foundationUtil6 = __webpack_require__(13);

var _foundationUtil7 = __webpack_require__(15);

var _foundationUtil8 = __webpack_require__(14);

var _foundationUtil9 = __webpack_require__(9);

var _foundation2 = __webpack_require__(36);

var _foundation3 = __webpack_require__(37);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

// import { Tooltip } from 'foundation-sites/js/foundation.tooltip';
// import { ResponsiveAccordionTabs } from 'foundation-sites/js/foundation.responsiveAccordionTabs';


// import { Abide } from 'foundation-sites/js/foundation.abide';
// import { Accordion } from 'foundation-sites/js/foundation.accordion';
// import { AccordionMenu } from 'foundation-sites/js/foundation.accordionMenu';
// import { Drilldown } from 'foundation-sites/js/foundation.drilldown';
// import { Dropdown } from 'foundation-sites/js/foundation.dropdown';
// import { DropdownMenu } from 'foundation-sites/js/foundation.dropdownMenu';
// import { Equalizer } from 'foundation-sites/js/foundation.equalizer';
// import { Interchange } from 'foundation-sites/js/foundation.interchange';
// import { Magellan } from 'foundation-sites/js/foundation.magellan';
// import { OffCanvas } from 'foundation-sites/js/foundation.offcanvas';
// import { Orbit } from 'foundation-sites/js/foundation.orbit';
// import { ResponsiveMenu } from 'foundation-sites/js/foundation.responsiveMenu';
// import { ResponsiveToggle } from 'foundation-sites/js/foundation.responsiveToggle';
// import { Reveal } from 'foundation-sites/js/foundation.reveal';
// import { Slider } from 'foundation-sites/js/foundation.slider';
// import { SmoothScroll } from 'foundation-sites/js/foundation.smoothScroll';
_foundation.Foundation.addToJquery(_jquery2.default);

// Add Foundation Utils to Foundation global namespace for backwards
// compatibility.

// import { Tabs } from 'foundation-sites/js/foundation.tabs';
_foundation.Foundation.rtl = _foundationCore.rtl;
_foundation.Foundation.GetYoDigits = _foundationCore.GetYoDigits;
_foundation.Foundation.transitionend = _foundationCore.transitionend;

_foundation.Foundation.Box = _foundationUtil.Box;
_foundation.Foundation.onImagesLoaded = _foundationUtil2.onImagesLoaded;
_foundation.Foundation.Keyboard = _foundationUtil3.Keyboard;
_foundation.Foundation.MediaQuery = _foundationUtil4.MediaQuery;
_foundation.Foundation.Motion = _foundationUtil5.Motion;
_foundation.Foundation.Move = _foundationUtil5.Move;
_foundation.Foundation.Nest = _foundationUtil6.Nest;
_foundation.Foundation.Timer = _foundationUtil7.Timer;

// Touch and Triggers previously were almost purely sede effect driven,
// so no // need to add it to Foundation, just init them.
_foundationUtil8.Touch.init(_jquery2.default);

_foundationUtil9.Triggers.init(_jquery2.default, _foundation.Foundation);

// Foundation.plugin( Abide, 'Abide' );
// Foundation.plugin( Accordion, 'Accordion' );
// Foundation.plugin( AccordionMenu, 'AccordionMenu' );
// Foundation.plugin( Drilldown, 'Drilldown' );
// Foundation.plugin( Dropdown, 'Dropdown' );
// Foundation.plugin( DropdownMenu, 'DropdownMenu' );
// Foundation.plugin( Equalizer, 'Equalizer' );
// Foundation.plugin( Interchange, 'Interchange' );
// Foundation.plugin( Magellan, 'Magellan' );
// Foundation.plugin( OffCanvas, 'OffCanvas' );
// Foundation.plugin( Orbit, 'Orbit' );
// Foundation.plugin( ResponsiveMenu, 'ResponsiveMenu' );
// Foundation.plugin( ResponsiveToggle, 'ResponsiveToggle' );
// Foundation.plugin( Reveal, 'Reveal' );
// Foundation.plugin( Slider, 'Slider' );
// Foundation.plugin( SmoothScroll, 'SmoothScroll' );
_foundation.Foundation.plugin(_foundation2.Sticky, 'Sticky');
// Foundation.plugin( Tabs, 'Tabs' );
_foundation.Foundation.plugin(_foundation3.Toggler, 'Toggler');
// Foundation.plugin( Tooltip, 'Tooltip' );
// Foundation.plugin( ResponsiveAccordionTabs, 'ResponsiveAccordionTabs' );

module.exports = _foundation.Foundation;

/***/ }),
/* 24 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Foundation = undefined;

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

var _foundationCore = __webpack_require__(5);

var _foundationUtil = __webpack_require__(7);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var FOUNDATION_VERSION = '6.5.1';

// Global Foundation object
// This is attached to the window, or used as a module for AMD/Browserify
var Foundation = {
  version: FOUNDATION_VERSION,

  /**
   * Stores initialized plugins.
   */
  _plugins: {},

  /**
   * Stores generated unique ids for plugin instances
   */
  _uuids: [],

  /**
   * Defines a Foundation plugin, adding it to the `Foundation` namespace and the list of plugins to initialize when reflowing.
   * @param {Object} plugin - The constructor of the plugin.
   */
  plugin: function plugin(_plugin, name) {
    // Object key to use when adding to global Foundation object
    // Examples: Foundation.Reveal, Foundation.OffCanvas
    var className = name || functionName(_plugin);
    // Object key to use when storing the plugin, also used to create the identifying data attribute for the plugin
    // Examples: data-reveal, data-off-canvas
    var attrName = hyphenate(className);

    // Add to the Foundation object and the plugins list (for reflowing)
    this._plugins[attrName] = this[className] = _plugin;
  },
  /**
   * @function
   * Populates the _uuids array with pointers to each individual plugin instance.
   * Adds the `zfPlugin` data-attribute to programmatically created plugins to allow use of $(selector).foundation(method) calls.
   * Also fires the initialization event for each plugin, consolidating repetitive code.
   * @param {Object} plugin - an instance of a plugin, usually `this` in context.
   * @param {String} name - the name of the plugin, passed as a camelCased string.
   * @fires Plugin#init
   */
  registerPlugin: function registerPlugin(plugin, name) {
    var pluginName = name ? hyphenate(name) : functionName(plugin.constructor).toLowerCase();
    plugin.uuid = (0, _foundationCore.GetYoDigits)(6, pluginName);

    if (!plugin.$element.attr('data-' + pluginName)) {
      plugin.$element.attr('data-' + pluginName, plugin.uuid);
    }
    if (!plugin.$element.data('zfPlugin')) {
      plugin.$element.data('zfPlugin', plugin);
    }
    /**
     * Fires when the plugin has initialized.
     * @event Plugin#init
     */
    plugin.$element.trigger('init.zf.' + pluginName);

    this._uuids.push(plugin.uuid);

    return;
  },
  /**
   * @function
   * Removes the plugins uuid from the _uuids array.
   * Removes the zfPlugin data attribute, as well as the data-plugin-name attribute.
   * Also fires the destroyed event for the plugin, consolidating repetitive code.
   * @param {Object} plugin - an instance of a plugin, usually `this` in context.
   * @fires Plugin#destroyed
   */
  unregisterPlugin: function unregisterPlugin(plugin) {
    var pluginName = hyphenate(functionName(plugin.$element.data('zfPlugin').constructor));

    this._uuids.splice(this._uuids.indexOf(plugin.uuid), 1);
    plugin.$element.removeAttr('data-' + pluginName).removeData('zfPlugin')
    /**
     * Fires when the plugin has been destroyed.
     * @event Plugin#destroyed
     */
    .trigger('destroyed.zf.' + pluginName);
    for (var prop in plugin) {
      plugin[prop] = null; //clean up script to prep for garbage collection.
    }
    return;
  },

  /**
   * @function
   * Causes one or more active plugins to re-initialize, resetting event listeners, recalculating positions, etc.
   * @param {String} plugins - optional string of an individual plugin key, attained by calling `$(element).data('pluginName')`, or string of a plugin class i.e. `'dropdown'`
   * @default If no argument is passed, reflow all currently active plugins.
   */
  reInit: function reInit(plugins) {
    var isJQ = plugins instanceof _jquery2.default;
    try {
      if (isJQ) {
        plugins.each(function () {
          (0, _jquery2.default)(this).data('zfPlugin')._init();
        });
      } else {
        var type = typeof plugins === 'undefined' ? 'undefined' : _typeof(plugins),
            _this = this,
            fns = {
          'object': function object(plgs) {
            plgs.forEach(function (p) {
              p = hyphenate(p);
              (0, _jquery2.default)('[data-' + p + ']').foundation('_init');
            });
          },
          'string': function string() {
            plugins = hyphenate(plugins);
            (0, _jquery2.default)('[data-' + plugins + ']').foundation('_init');
          },
          'undefined': function undefined() {
            this['object'](Object.keys(_this._plugins));
          }
        };
        fns[type](plugins);
      }
    } catch (err) {
      console.error(err);
    } finally {
      return plugins;
    }
  },

  /**
   * Initialize plugins on any elements within `elem` (and `elem` itself) that aren't already initialized.
   * @param {Object} elem - jQuery object containing the element to check inside. Also checks the element itself, unless it's the `document` object.
   * @param {String|Array} plugins - A list of plugins to initialize. Leave this out to initialize everything.
   */
  reflow: function reflow(elem, plugins) {

    // If plugins is undefined, just grab everything
    if (typeof plugins === 'undefined') {
      plugins = Object.keys(this._plugins);
    }
    // If plugins is a string, convert it to an array with one item
    else if (typeof plugins === 'string') {
        plugins = [plugins];
      }

    var _this = this;

    // Iterate through each plugin
    _jquery2.default.each(plugins, function (i, name) {
      // Get the current plugin
      var plugin = _this._plugins[name];

      // Localize the search to all elements inside elem, as well as elem itself, unless elem === document
      var $elem = (0, _jquery2.default)(elem).find('[data-' + name + ']').addBack('[data-' + name + ']');

      // For each plugin found, initialize it
      $elem.each(function () {
        var $el = (0, _jquery2.default)(this),
            opts = {};
        // Don't double-dip on plugins
        if ($el.data('zfPlugin')) {
          console.warn("Tried to initialize " + name + " on an element that already has a Foundation plugin.");
          return;
        }

        if ($el.attr('data-options')) {
          var thing = $el.attr('data-options').split(';').forEach(function (e, i) {
            var opt = e.split(':').map(function (el) {
              return el.trim();
            });
            if (opt[0]) opts[opt[0]] = parseValue(opt[1]);
          });
        }
        try {
          $el.data('zfPlugin', new plugin((0, _jquery2.default)(this), opts));
        } catch (er) {
          console.error(er);
        } finally {
          return;
        }
      });
    });
  },
  getFnName: functionName,

  addToJquery: function addToJquery($) {
    // TODO: consider not making this a jQuery function
    // TODO: need way to reflow vs. re-initialize
    /**
     * The Foundation jQuery method.
     * @param {String|Array} method - An action to perform on the current jQuery object.
     */
    var foundation = function foundation(method) {
      var type = typeof method === 'undefined' ? 'undefined' : _typeof(method),
          $noJS = $('.no-js');

      if ($noJS.length) {
        $noJS.removeClass('no-js');
      }

      if (type === 'undefined') {
        //needs to initialize the Foundation object, or an individual plugin.
        _foundationUtil.MediaQuery._init();
        Foundation.reflow(this);
      } else if (type === 'string') {
        //an individual method to invoke on a plugin or group of plugins
        var args = Array.prototype.slice.call(arguments, 1); //collect all the arguments, if necessary
        var plugClass = this.data('zfPlugin'); //determine the class of plugin

        if (typeof plugClass !== 'undefined' && typeof plugClass[method] !== 'undefined') {
          //make sure both the class and method exist
          if (this.length === 1) {
            //if there's only one, call it directly.
            plugClass[method].apply(plugClass, args);
          } else {
            this.each(function (i, el) {
              //otherwise loop through the jQuery collection and invoke the method on each
              plugClass[method].apply($(el).data('zfPlugin'), args);
            });
          }
        } else {
          //error for no class or no method
          throw new ReferenceError("We're sorry, '" + method + "' is not an available method for " + (plugClass ? functionName(plugClass) : 'this element') + '.');
        }
      } else {
        //error for invalid argument type
        throw new TypeError('We\'re sorry, ' + type + ' is not a valid parameter. You must use a string representing the method you wish to invoke.');
      }
      return this;
    };
    $.fn.foundation = foundation;
    return $;
  }
};

Foundation.util = {
  /**
   * Function for applying a debounce effect to a function call.
   * @function
   * @param {Function} func - Function to be called at end of timeout.
   * @param {Number} delay - Time in ms to delay the call of `func`.
   * @returns function
   */
  throttle: function throttle(func, delay) {
    var timer = null;

    return function () {
      var context = this,
          args = arguments;

      if (timer === null) {
        timer = setTimeout(function () {
          func.apply(context, args);
          timer = null;
        }, delay);
      }
    };
  }
};

window.Foundation = Foundation;

// Polyfill for requestAnimationFrame
(function () {
  if (!Date.now || !window.Date.now) window.Date.now = Date.now = function () {
    return new Date().getTime();
  };

  var vendors = ['webkit', 'moz'];
  for (var i = 0; i < vendors.length && !window.requestAnimationFrame; ++i) {
    var vp = vendors[i];
    window.requestAnimationFrame = window[vp + 'RequestAnimationFrame'];
    window.cancelAnimationFrame = window[vp + 'CancelAnimationFrame'] || window[vp + 'CancelRequestAnimationFrame'];
  }
  if (/iP(ad|hone|od).*OS 6/.test(window.navigator.userAgent) || !window.requestAnimationFrame || !window.cancelAnimationFrame) {
    var lastTime = 0;
    window.requestAnimationFrame = function (callback) {
      var now = Date.now();
      var nextTime = Math.max(lastTime + 16, now);
      return setTimeout(function () {
        callback(lastTime = nextTime);
      }, nextTime - now);
    };
    window.cancelAnimationFrame = clearTimeout;
  }
  /**
   * Polyfill for performance.now, required by rAF
   */
  if (!window.performance || !window.performance.now) {
    window.performance = {
      start: Date.now(),
      now: function now() {
        return Date.now() - this.start;
      }
    };
  }
})();
if (!Function.prototype.bind) {
  Function.prototype.bind = function (oThis) {
    if (typeof this !== 'function') {
      // closest thing possible to the ECMAScript 5
      // internal IsCallable function
      throw new TypeError('Function.prototype.bind - what is trying to be bound is not callable');
    }

    var aArgs = Array.prototype.slice.call(arguments, 1),
        fToBind = this,
        fNOP = function fNOP() {},
        fBound = function fBound() {
      return fToBind.apply(this instanceof fNOP ? this : oThis, aArgs.concat(Array.prototype.slice.call(arguments)));
    };

    if (this.prototype) {
      // native functions don't have a prototype
      fNOP.prototype = this.prototype;
    }
    fBound.prototype = new fNOP();

    return fBound;
  };
}
// Polyfill to get the name of a function in IE9
function functionName(fn) {
  if (typeof Function.prototype.name === 'undefined') {
    var funcNameRegex = /function\s([^(]{1,})\(/;
    var results = funcNameRegex.exec(fn.toString());
    return results && results.length > 1 ? results[1].trim() : "";
  } else if (typeof fn.prototype === 'undefined') {
    return fn.constructor.name;
  } else {
    return fn.prototype.constructor.name;
  }
}
function parseValue(str) {
  if ('true' === str) return true;else if ('false' === str) return false;else if (!isNaN(str * 1)) return parseFloat(str);
  return str;
}
// Convert PascalCase to kebab-case
// Thank you: http://stackoverflow.com/a/8955580
function hyphenate(str) {
  return str.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase();
}

exports.Foundation = Foundation;

/***/ }),
/* 25 */,
/* 26 */,
/* 27 */,
/* 28 */,
/* 29 */,
/* 30 */,
/* 31 */,
/* 32 */,
/* 33 */,
/* 34 */,
/* 35 */,
/* 36 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Sticky = undefined;

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

var _foundationCore = __webpack_require__(5);

var _foundationUtil = __webpack_require__(7);

var _foundationCore2 = __webpack_require__(6);

var _foundationUtil2 = __webpack_require__(9);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * Sticky module.
 * @module foundation.sticky
 * @requires foundation.util.triggers
 * @requires foundation.util.mediaQuery
 */

var Sticky = function (_Plugin) {
  _inherits(Sticky, _Plugin);

  function Sticky() {
    _classCallCheck(this, Sticky);

    return _possibleConstructorReturn(this, (Sticky.__proto__ || Object.getPrototypeOf(Sticky)).apply(this, arguments));
  }

  _createClass(Sticky, [{
    key: '_setup',

    /**
     * Creates a new instance of a sticky thing.
     * @class
     * @name Sticky
     * @param {jQuery} element - jQuery object to make sticky.
     * @param {Object} options - options object passed when creating the element programmatically.
     */
    value: function _setup(element, options) {
      this.$element = element;
      this.options = _jquery2.default.extend({}, Sticky.defaults, this.$element.data(), options);
      this.className = 'Sticky'; // ie9 back compat

      // Triggers init is idempotent, just need to make sure it is initialized
      _foundationUtil2.Triggers.init(_jquery2.default);

      this._init();
    }

    /**
     * Initializes the sticky element by adding classes, getting/setting dimensions, breakpoints and attributes
     * @function
     * @private
     */

  }, {
    key: '_init',
    value: function _init() {
      _foundationUtil.MediaQuery._init();

      var $parent = this.$element.parent('[data-sticky-container]'),
          id = this.$element[0].id || (0, _foundationCore.GetYoDigits)(6, 'sticky'),
          _this = this;

      if ($parent.length) {
        this.$container = $parent;
      } else {
        this.wasWrapped = true;
        this.$element.wrap(this.options.container);
        this.$container = this.$element.parent();
      }
      this.$container.addClass(this.options.containerClass);

      this.$element.addClass(this.options.stickyClass).attr({ 'data-resize': id, 'data-mutate': id });
      if (this.options.anchor !== '') {
        (0, _jquery2.default)('#' + _this.options.anchor).attr({ 'data-mutate': id });
      }

      this.scrollCount = this.options.checkEvery;
      this.isStuck = false;
      this.onLoadListener = (0, _foundationCore.onLoad)((0, _jquery2.default)(window), function () {
        //We calculate the container height to have correct values for anchor points offset calculation.
        _this.containerHeight = _this.$element.css("display") == "none" ? 0 : _this.$element[0].getBoundingClientRect().height;
        _this.$container.css('height', _this.containerHeight);
        _this.elemHeight = _this.containerHeight;
        if (_this.options.anchor !== '') {
          _this.$anchor = (0, _jquery2.default)('#' + _this.options.anchor);
        } else {
          _this._parsePoints();
        }

        _this._setSizes(function () {
          var scroll = window.pageYOffset;
          _this._calc(false, scroll);
          //Unstick the element will ensure that proper classes are set.
          if (!_this.isStuck) {
            _this._removeSticky(scroll >= _this.topPoint ? false : true);
          }
        });
        _this._events(id.split('-').reverse().join('-'));
      });
    }

    /**
     * If using multiple elements as anchors, calculates the top and bottom pixel values the sticky thing should stick and unstick on.
     * @function
     * @private
     */

  }, {
    key: '_parsePoints',
    value: function _parsePoints() {
      var top = this.options.topAnchor == "" ? 1 : this.options.topAnchor,
          btm = this.options.btmAnchor == "" ? document.documentElement.scrollHeight : this.options.btmAnchor,
          pts = [top, btm],
          breaks = {};
      for (var i = 0, len = pts.length; i < len && pts[i]; i++) {
        var pt;
        if (typeof pts[i] === 'number') {
          pt = pts[i];
        } else {
          var place = pts[i].split(':'),
              anchor = (0, _jquery2.default)('#' + place[0]);

          pt = anchor.offset().top;
          if (place[1] && place[1].toLowerCase() === 'bottom') {
            pt += anchor[0].getBoundingClientRect().height;
          }
        }
        breaks[i] = pt;
      }

      this.points = breaks;
      return;
    }

    /**
     * Adds event handlers for the scrolling element.
     * @private
     * @param {String} id - pseudo-random id for unique scroll event listener.
     */

  }, {
    key: '_events',
    value: function _events(id) {
      var _this = this,
          scrollListener = this.scrollListener = 'scroll.zf.' + id;
      if (this.isOn) {
        return;
      }
      if (this.canStick) {
        this.isOn = true;
        (0, _jquery2.default)(window).off(scrollListener).on(scrollListener, function (e) {
          if (_this.scrollCount === 0) {
            _this.scrollCount = _this.options.checkEvery;
            _this._setSizes(function () {
              _this._calc(false, window.pageYOffset);
            });
          } else {
            _this.scrollCount--;
            _this._calc(false, window.pageYOffset);
          }
        });
      }

      this.$element.off('resizeme.zf.trigger').on('resizeme.zf.trigger', function (e, el) {
        _this._eventsHandler(id);
      });

      this.$element.on('mutateme.zf.trigger', function (e, el) {
        _this._eventsHandler(id);
      });

      if (this.$anchor) {
        this.$anchor.on('mutateme.zf.trigger', function (e, el) {
          _this._eventsHandler(id);
        });
      }
    }

    /**
     * Handler for events.
     * @private
     * @param {String} id - pseudo-random id for unique scroll event listener.
     */

  }, {
    key: '_eventsHandler',
    value: function _eventsHandler(id) {
      var _this = this,
          scrollListener = this.scrollListener = 'scroll.zf.' + id;

      _this._setSizes(function () {
        _this._calc(false);
        if (_this.canStick) {
          if (!_this.isOn) {
            _this._events(id);
          }
        } else if (_this.isOn) {
          _this._pauseListeners(scrollListener);
        }
      });
    }

    /**
     * Removes event handlers for scroll and change events on anchor.
     * @fires Sticky#pause
     * @param {String} scrollListener - unique, namespaced scroll listener attached to `window`
     */

  }, {
    key: '_pauseListeners',
    value: function _pauseListeners(scrollListener) {
      this.isOn = false;
      (0, _jquery2.default)(window).off(scrollListener);

      /**
       * Fires when the plugin is paused due to resize event shrinking the view.
       * @event Sticky#pause
       * @private
       */
      this.$element.trigger('pause.zf.sticky');
    }

    /**
     * Called on every `scroll` event and on `_init`
     * fires functions based on booleans and cached values
     * @param {Boolean} checkSizes - true if plugin should recalculate sizes and breakpoints.
     * @param {Number} scroll - current scroll position passed from scroll event cb function. If not passed, defaults to `window.pageYOffset`.
     */

  }, {
    key: '_calc',
    value: function _calc(checkSizes, scroll) {
      if (checkSizes) {
        this._setSizes();
      }

      if (!this.canStick) {
        if (this.isStuck) {
          this._removeSticky(true);
        }
        return false;
      }

      if (!scroll) {
        scroll = window.pageYOffset;
      }

      if (scroll >= this.topPoint) {
        if (scroll <= this.bottomPoint) {
          if (!this.isStuck) {
            this._setSticky();
          }
        } else {
          if (this.isStuck) {
            this._removeSticky(false);
          }
        }
      } else {
        if (this.isStuck) {
          this._removeSticky(true);
        }
      }
    }

    /**
     * Causes the $element to become stuck.
     * Adds `position: fixed;`, and helper classes.
     * @fires Sticky#stuckto
     * @function
     * @private
     */

  }, {
    key: '_setSticky',
    value: function _setSticky() {
      var _this = this,
          stickTo = this.options.stickTo,
          mrgn = stickTo === 'top' ? 'marginTop' : 'marginBottom',
          notStuckTo = stickTo === 'top' ? 'bottom' : 'top',
          css = {};

      css[mrgn] = this.options[mrgn] + 'em';
      css[stickTo] = 0;
      css[notStuckTo] = 'auto';
      this.isStuck = true;
      this.$element.removeClass('is-anchored is-at-' + notStuckTo).addClass('is-stuck is-at-' + stickTo).css(css)
      /**
       * Fires when the $element has become `position: fixed;`
       * Namespaced to `top` or `bottom`, e.g. `sticky.zf.stuckto:top`
       * @event Sticky#stuckto
       */
      .trigger('sticky.zf.stuckto:' + stickTo);
      this.$element.on("transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd", function () {
        _this._setSizes();
      });
    }

    /**
     * Causes the $element to become unstuck.
     * Removes `position: fixed;`, and helper classes.
     * Adds other helper classes.
     * @param {Boolean} isTop - tells the function if the $element should anchor to the top or bottom of its $anchor element.
     * @fires Sticky#unstuckfrom
     * @private
     */

  }, {
    key: '_removeSticky',
    value: function _removeSticky(isTop) {
      var stickTo = this.options.stickTo,
          stickToTop = stickTo === 'top',
          css = {},
          anchorPt = (this.points ? this.points[1] - this.points[0] : this.anchorHeight) - this.elemHeight,
          mrgn = stickToTop ? 'marginTop' : 'marginBottom',
          notStuckTo = stickToTop ? 'bottom' : 'top',
          topOrBottom = isTop ? 'top' : 'bottom';

      css[mrgn] = 0;

      css['bottom'] = 'auto';
      if (isTop) {
        css['top'] = 0;
      } else {
        css['top'] = anchorPt;
      }

      this.isStuck = false;
      this.$element.removeClass('is-stuck is-at-' + stickTo).addClass('is-anchored is-at-' + topOrBottom).css(css)
      /**
       * Fires when the $element has become anchored.
       * Namespaced to `top` or `bottom`, e.g. `sticky.zf.unstuckfrom:bottom`
       * @event Sticky#unstuckfrom
       */
      .trigger('sticky.zf.unstuckfrom:' + topOrBottom);
    }

    /**
     * Sets the $element and $container sizes for plugin.
     * Calls `_setBreakPoints`.
     * @param {Function} cb - optional callback function to fire on completion of `_setBreakPoints`.
     * @private
     */

  }, {
    key: '_setSizes',
    value: function _setSizes(cb) {
      this.canStick = _foundationUtil.MediaQuery.is(this.options.stickyOn);
      if (!this.canStick) {
        if (cb && typeof cb === 'function') {
          cb();
        }
      }
      var _this = this,
          newElemWidth = this.$container[0].getBoundingClientRect().width,
          comp = window.getComputedStyle(this.$container[0]),
          pdngl = parseInt(comp['padding-left'], 10),
          pdngr = parseInt(comp['padding-right'], 10);

      if (this.$anchor && this.$anchor.length) {
        this.anchorHeight = this.$anchor[0].getBoundingClientRect().height;
      } else {
        this._parsePoints();
      }

      this.$element.css({
        'max-width': newElemWidth - pdngl - pdngr + 'px'
      });

      var newContainerHeight = this.$element[0].getBoundingClientRect().height || this.containerHeight;
      if (this.$element.css("display") == "none") {
        newContainerHeight = 0;
      }
      this.containerHeight = newContainerHeight;
      this.$container.css({
        height: newContainerHeight
      });
      this.elemHeight = newContainerHeight;

      if (!this.isStuck) {
        if (this.$element.hasClass('is-at-bottom')) {
          var anchorPt = (this.points ? this.points[1] - this.$container.offset().top : this.anchorHeight) - this.elemHeight;
          this.$element.css('top', anchorPt);
        }
      }

      this._setBreakPoints(newContainerHeight, function () {
        if (cb && typeof cb === 'function') {
          cb();
        }
      });
    }

    /**
     * Sets the upper and lower breakpoints for the element to become sticky/unsticky.
     * @param {Number} elemHeight - px value for sticky.$element height, calculated by `_setSizes`.
     * @param {Function} cb - optional callback function to be called on completion.
     * @private
     */

  }, {
    key: '_setBreakPoints',
    value: function _setBreakPoints(elemHeight, cb) {
      if (!this.canStick) {
        if (cb && typeof cb === 'function') {
          cb();
        } else {
          return false;
        }
      }
      var mTop = emCalc(this.options.marginTop),
          mBtm = emCalc(this.options.marginBottom),
          topPoint = this.points ? this.points[0] : this.$anchor.offset().top,
          bottomPoint = this.points ? this.points[1] : topPoint + this.anchorHeight,

      // topPoint = this.$anchor.offset().top || this.points[0],
      // bottomPoint = topPoint + this.anchorHeight || this.points[1],
      winHeight = window.innerHeight;

      if (this.options.stickTo === 'top') {
        topPoint -= mTop;
        bottomPoint -= elemHeight + mTop;
      } else if (this.options.stickTo === 'bottom') {
        topPoint -= winHeight - (elemHeight + mBtm);
        bottomPoint -= winHeight - mBtm;
      } else {
        //this would be the stickTo: both option... tricky
      }

      this.topPoint = topPoint;
      this.bottomPoint = bottomPoint;

      if (cb && typeof cb === 'function') {
        cb();
      }
    }

    /**
     * Destroys the current sticky element.
     * Resets the element to the top position first.
     * Removes event listeners, JS-added css properties and classes, and unwraps the $element if the JS added the $container.
     * @function
     */

  }, {
    key: '_destroy',
    value: function _destroy() {
      this._removeSticky(true);

      this.$element.removeClass(this.options.stickyClass + ' is-anchored is-at-top').css({
        height: '',
        top: '',
        bottom: '',
        'max-width': ''
      }).off('resizeme.zf.trigger').off('mutateme.zf.trigger');
      if (this.$anchor && this.$anchor.length) {
        this.$anchor.off('change.zf.sticky');
      }
      if (this.scrollListener) (0, _jquery2.default)(window).off(this.scrollListener);
      if (this.onLoadListener) (0, _jquery2.default)(window).off(this.onLoadListener);

      if (this.wasWrapped) {
        this.$element.unwrap();
      } else {
        this.$container.removeClass(this.options.containerClass).css({
          height: ''
        });
      }
    }
  }]);

  return Sticky;
}(_foundationCore2.Plugin);

Sticky.defaults = {
  /**
   * Customizable container template. Add your own classes for styling and sizing.
   * @option
   * @type {string}
   * @default '&lt;div data-sticky-container&gt;&lt;/div&gt;'
   */
  container: '<div data-sticky-container></div>',
  /**
   * Location in the view the element sticks to. Can be `'top'` or `'bottom'`.
   * @option
   * @type {string}
   * @default 'top'
   */
  stickTo: 'top',
  /**
   * If anchored to a single element, the id of that element.
   * @option
   * @type {string}
   * @default ''
   */
  anchor: '',
  /**
   * If using more than one element as anchor points, the id of the top anchor.
   * @option
   * @type {string}
   * @default ''
   */
  topAnchor: '',
  /**
   * If using more than one element as anchor points, the id of the bottom anchor.
   * @option
   * @type {string}
   * @default ''
   */
  btmAnchor: '',
  /**
   * Margin, in `em`'s to apply to the top of the element when it becomes sticky.
   * @option
   * @type {number}
   * @default 1
   */
  marginTop: 1,
  /**
   * Margin, in `em`'s to apply to the bottom of the element when it becomes sticky.
   * @option
   * @type {number}
   * @default 1
   */
  marginBottom: 1,
  /**
   * Breakpoint string that is the minimum screen size an element should become sticky.
   * @option
   * @type {string}
   * @default 'medium'
   */
  stickyOn: 'medium',
  /**
   * Class applied to sticky element, and removed on destruction. Foundation defaults to `sticky`.
   * @option
   * @type {string}
   * @default 'sticky'
   */
  stickyClass: 'sticky',
  /**
   * Class applied to sticky container. Foundation defaults to `sticky-container`.
   * @option
   * @type {string}
   * @default 'sticky-container'
   */
  containerClass: 'sticky-container',
  /**
   * Number of scroll events between the plugin's recalculating sticky points. Setting it to `0` will cause it to recalc every scroll event, setting it to `-1` will prevent recalc on scroll.
   * @option
   * @type {number}
   * @default -1
   */
  checkEvery: -1
};

/**
 * Helper function to calculate em values
 * @param Number {em} - number of em's to calculate into pixels
 */
function emCalc(em) {
  return parseInt(window.getComputedStyle(document.body, null).fontSize, 10) * em;
}

exports.Sticky = Sticky;

/***/ }),
/* 37 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Toggler = undefined;

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

var _foundationUtil = __webpack_require__(10);

var _foundationCore = __webpack_require__(6);

var _foundationCore2 = __webpack_require__(5);

var _foundationUtil2 = __webpack_require__(9);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * Toggler module.
 * @module foundation.toggler
 * @requires foundation.util.motion
 * @requires foundation.util.triggers
 */

var Toggler = function (_Plugin) {
  _inherits(Toggler, _Plugin);

  function Toggler() {
    _classCallCheck(this, Toggler);

    return _possibleConstructorReturn(this, (Toggler.__proto__ || Object.getPrototypeOf(Toggler)).apply(this, arguments));
  }

  _createClass(Toggler, [{
    key: '_setup',

    /**
     * Creates a new instance of Toggler.
     * @class
     * @name Toggler
     * @fires Toggler#init
     * @param {Object} element - jQuery object to add the trigger to.
     * @param {Object} options - Overrides to the default plugin settings.
     */
    value: function _setup(element, options) {
      this.$element = element;
      this.options = _jquery2.default.extend({}, Toggler.defaults, element.data(), options);
      this.className = '';
      this.className = 'Toggler'; // ie9 back compat

      // Triggers init is idempotent, just need to make sure it is initialized
      _foundationUtil2.Triggers.init(_jquery2.default);

      this._init();
      this._events();
    }

    /**
     * Initializes the Toggler plugin by parsing the toggle class from data-toggler, or animation classes from data-animate.
     * @function
     * @private
     */

  }, {
    key: '_init',
    value: function _init() {
      var input;
      // Parse animation classes if they were set
      if (this.options.animate) {
        input = this.options.animate.split(' ');

        this.animationIn = input[0];
        this.animationOut = input[1] || null;
      }
      // Otherwise, parse toggle class
      else {
          input = this.$element.data('toggler');
          // Allow for a . at the beginning of the string
          this.className = input[0] === '.' ? input.slice(1) : input;
        }

      // Add ARIA attributes to triggers:
      var id = this.$element[0].id,
          $triggers = (0, _jquery2.default)('[data-open~="' + id + '"], [data-close~="' + id + '"], [data-toggle~="' + id + '"]');

      // - aria-expanded: according to the element visibility.
      $triggers.attr('aria-expanded', !this.$element.is(':hidden'));
      // - aria-controls: adding the element id to it if not already in it.
      $triggers.each(function (index, trigger) {
        var $trigger = (0, _jquery2.default)(trigger);
        var controls = $trigger.attr('aria-controls') || '';

        var containsId = new RegExp('\\b' + (0, _foundationCore2.RegExpEscape)(id) + '\\b').test(controls);
        if (!containsId) $trigger.attr('aria-controls', controls ? controls + ' ' + id : id);
      });
    }

    /**
     * Initializes events for the toggle trigger.
     * @function
     * @private
     */

  }, {
    key: '_events',
    value: function _events() {
      this.$element.off('toggle.zf.trigger').on('toggle.zf.trigger', this.toggle.bind(this));
    }

    /**
     * Toggles the target class on the target element. An event is fired from the original trigger depending on if the resultant state was "on" or "off".
     * @function
     * @fires Toggler#on
     * @fires Toggler#off
     */

  }, {
    key: 'toggle',
    value: function toggle() {
      this[this.options.animate ? '_toggleAnimate' : '_toggleClass']();
    }
  }, {
    key: '_toggleClass',
    value: function _toggleClass() {
      this.$element.toggleClass(this.className);

      var isOn = this.$element.hasClass(this.className);
      if (isOn) {
        /**
         * Fires if the target element has the class after a toggle.
         * @event Toggler#on
         */
        this.$element.trigger('on.zf.toggler');
      } else {
        /**
         * Fires if the target element does not have the class after a toggle.
         * @event Toggler#off
         */
        this.$element.trigger('off.zf.toggler');
      }

      this._updateARIA(isOn);
      this.$element.find('[data-mutate]').trigger('mutateme.zf.trigger');
    }
  }, {
    key: '_toggleAnimate',
    value: function _toggleAnimate() {
      var _this = this;

      if (this.$element.is(':hidden')) {
        _foundationUtil.Motion.animateIn(this.$element, this.animationIn, function () {
          _this._updateARIA(true);
          this.trigger('on.zf.toggler');
          this.find('[data-mutate]').trigger('mutateme.zf.trigger');
        });
      } else {
        _foundationUtil.Motion.animateOut(this.$element, this.animationOut, function () {
          _this._updateARIA(false);
          this.trigger('off.zf.toggler');
          this.find('[data-mutate]').trigger('mutateme.zf.trigger');
        });
      }
    }
  }, {
    key: '_updateARIA',
    value: function _updateARIA(isOn) {
      var id = this.$element[0].id;
      (0, _jquery2.default)('[data-open="' + id + '"], [data-close="' + id + '"], [data-toggle="' + id + '"]').attr({
        'aria-expanded': isOn ? true : false
      });
    }

    /**
     * Destroys the instance of Toggler on the element.
     * @function
     */

  }, {
    key: '_destroy',
    value: function _destroy() {
      this.$element.off('.zf.toggler');
    }
  }]);

  return Toggler;
}(_foundationCore.Plugin);

Toggler.defaults = {
  /**
   * Tells the plugin if the element should animated when toggled.
   * @option
   * @type {boolean}
   * @default false
   */
  animate: false
};

exports.Toggler = Toggler;

/***/ })
/******/ ]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vd2VicGFjay9ib290c3RyYXAgM2JmMWI1YzU0M2E0ZGNhZWQzYTYiLCJ3ZWJwYWNrOi8vL2V4dGVybmFsIFwialF1ZXJ5XCIiLCJ3ZWJwYWNrOi8vLy4vc3JjL2Fzc2V0cy9qcy9hcHAuanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi5jb3JlLnV0aWxzLmpzIiwid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9mb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24uY29yZS5wbHVnaW4uanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi51dGlsLm1lZGlhUXVlcnkuanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi51dGlsLmtleWJvYXJkLmpzIiwid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9mb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24udXRpbC50cmlnZ2Vycy5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLnV0aWwubW90aW9uLmpzIiwid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9mb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24udXRpbC5ib3guanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi51dGlsLmltYWdlTG9hZGVyLmpzIiwid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9mb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24udXRpbC5uZXN0LmpzIiwid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9mb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24udXRpbC50b3VjaC5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLnV0aWwudGltZXIuanMiLCJ3ZWJwYWNrOi8vLy4vc3JjL2Fzc2V0cy9qcy9saWIvZm91bmRhdGlvbi1leHBsaWNpdC1waWVjZXMuanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi5jb3JlLmpzIiwid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9mb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24uc3RpY2t5LmpzIiwid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9mb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24udG9nZ2xlci5qcyJdLCJuYW1lcyI6WyIkIiwiZG9jdW1lbnQiLCJmb3VuZGF0aW9uIiwicnRsIiwiYXR0ciIsIkdldFlvRGlnaXRzIiwibGVuZ3RoIiwibmFtZXNwYWNlIiwiTWF0aCIsInJvdW5kIiwicG93IiwicmFuZG9tIiwidG9TdHJpbmciLCJzbGljZSIsIlJlZ0V4cEVzY2FwZSIsInN0ciIsInJlcGxhY2UiLCJ0cmFuc2l0aW9uZW5kIiwiJGVsZW0iLCJ0cmFuc2l0aW9ucyIsImVsZW0iLCJjcmVhdGVFbGVtZW50IiwiZW5kIiwidCIsInN0eWxlIiwic2V0VGltZW91dCIsInRyaWdnZXJIYW5kbGVyIiwib25Mb2FkIiwiaGFuZGxlciIsImRpZExvYWQiLCJyZWFkeVN0YXRlIiwiZXZlbnRUeXBlIiwiY2IiLCJvbmUiLCJ3aW5kb3ciLCJpZ25vcmVNb3VzZWRpc2FwcGVhciIsImlnbm9yZUxlYXZlV2luZG93IiwiaWdub3JlUmVhcHBlYXIiLCJsZWF2ZUV2ZW50SGFuZGxlciIsImVMZWF2ZSIsInJlc3QiLCJjYWxsYmFjayIsImJpbmQiLCJyZWxhdGVkVGFyZ2V0IiwibGVhdmVFdmVudERlYm91bmNlciIsImhhc0ZvY3VzIiwicmVlbnRlckV2ZW50SGFuZGxlciIsImVSZWVudGVyIiwiY3VycmVudFRhcmdldCIsImhhcyIsInRhcmdldCIsIlBsdWdpbiIsImVsZW1lbnQiLCJvcHRpb25zIiwiX3NldHVwIiwicGx1Z2luTmFtZSIsImdldFBsdWdpbk5hbWUiLCJ1dWlkIiwiJGVsZW1lbnQiLCJkYXRhIiwidHJpZ2dlciIsIl9kZXN0cm95IiwicmVtb3ZlQXR0ciIsInJlbW92ZURhdGEiLCJwcm9wIiwiaHlwaGVuYXRlIiwidG9Mb3dlckNhc2UiLCJvYmoiLCJjb25zdHJ1Y3RvciIsIm5hbWUiLCJjbGFzc05hbWUiLCJkZWZhdWx0UXVlcmllcyIsImxhbmRzY2FwZSIsInBvcnRyYWl0IiwicmV0aW5hIiwibWF0Y2hNZWRpYSIsInN0eWxlTWVkaWEiLCJtZWRpYSIsInNjcmlwdCIsImdldEVsZW1lbnRzQnlUYWdOYW1lIiwiaW5mbyIsInR5cGUiLCJpZCIsImhlYWQiLCJhcHBlbmRDaGlsZCIsInBhcmVudE5vZGUiLCJpbnNlcnRCZWZvcmUiLCJnZXRDb21wdXRlZFN0eWxlIiwiY3VycmVudFN0eWxlIiwibWF0Y2hNZWRpdW0iLCJ0ZXh0Iiwic3R5bGVTaGVldCIsImNzc1RleHQiLCJ0ZXh0Q29udGVudCIsIndpZHRoIiwibWF0Y2hlcyIsIk1lZGlhUXVlcnkiLCJxdWVyaWVzIiwiY3VycmVudCIsIl9pbml0Iiwic2VsZiIsIiRtZXRhIiwiYXBwZW5kVG8iLCJleHRyYWN0ZWRTdHlsZXMiLCJjc3MiLCJuYW1lZFF1ZXJpZXMiLCJwYXJzZVN0eWxlVG9PYmplY3QiLCJrZXkiLCJoYXNPd25Qcm9wZXJ0eSIsInB1c2giLCJ2YWx1ZSIsIl9nZXRDdXJyZW50U2l6ZSIsIl93YXRjaGVyIiwiYXRMZWFzdCIsInNpemUiLCJxdWVyeSIsImdldCIsImlzIiwidHJpbSIsInNwbGl0IiwiaSIsIm1hdGNoZWQiLCJvZmYiLCJvbiIsIm5ld1NpemUiLCJjdXJyZW50U2l6ZSIsInN0eWxlT2JqZWN0IiwicmVkdWNlIiwicmV0IiwicGFyYW0iLCJwYXJ0cyIsInZhbCIsImRlY29kZVVSSUNvbXBvbmVudCIsIkFycmF5IiwiaXNBcnJheSIsImtleUNvZGVzIiwiY29tbWFuZHMiLCJmaW5kRm9jdXNhYmxlIiwiZmluZCIsImZpbHRlciIsInBhcnNlS2V5IiwiZXZlbnQiLCJ3aGljaCIsImtleUNvZGUiLCJTdHJpbmciLCJmcm9tQ2hhckNvZGUiLCJ0b1VwcGVyQ2FzZSIsInNoaWZ0S2V5IiwiY3RybEtleSIsImFsdEtleSIsIktleWJvYXJkIiwia2V5cyIsImdldEtleUNvZGVzIiwiaGFuZGxlS2V5IiwiY29tcG9uZW50IiwiZnVuY3Rpb25zIiwiY29tbWFuZExpc3QiLCJjbWRzIiwiY29tbWFuZCIsImZuIiwiY29uc29sZSIsIndhcm4iLCJsdHIiLCJleHRlbmQiLCJyZXR1cm5WYWx1ZSIsImFwcGx5IiwiaGFuZGxlZCIsInVuaGFuZGxlZCIsInJlZ2lzdGVyIiwiY29tcG9uZW50TmFtZSIsInRyYXBGb2N1cyIsIiRmb2N1c2FibGUiLCIkZmlyc3RGb2N1c2FibGUiLCJlcSIsIiRsYXN0Rm9jdXNhYmxlIiwicHJldmVudERlZmF1bHQiLCJmb2N1cyIsInJlbGVhc2VGb2N1cyIsImtjcyIsImsiLCJrYyIsIk11dGF0aW9uT2JzZXJ2ZXIiLCJwcmVmaXhlcyIsInRyaWdnZXJzIiwiZWwiLCJmb3JFYWNoIiwiVHJpZ2dlcnMiLCJMaXN0ZW5lcnMiLCJCYXNpYyIsIkdsb2JhbCIsIkluaXRpYWxpemVycyIsIm9wZW5MaXN0ZW5lciIsImNsb3NlTGlzdGVuZXIiLCJ0b2dnbGVMaXN0ZW5lciIsImNsb3NlYWJsZUxpc3RlbmVyIiwiZSIsInN0b3BQcm9wYWdhdGlvbiIsImFuaW1hdGlvbiIsIk1vdGlvbiIsImFuaW1hdGVPdXQiLCJmYWRlT3V0IiwidG9nZ2xlRm9jdXNMaXN0ZW5lciIsImFkZE9wZW5MaXN0ZW5lciIsImFkZENsb3NlTGlzdGVuZXIiLCJhZGRUb2dnbGVMaXN0ZW5lciIsImFkZENsb3NlYWJsZUxpc3RlbmVyIiwiYWRkVG9nZ2xlRm9jdXNMaXN0ZW5lciIsInJlc2l6ZUxpc3RlbmVyIiwiJG5vZGVzIiwiZWFjaCIsInNjcm9sbExpc3RlbmVyIiwiY2xvc2VNZUxpc3RlbmVyIiwicGx1Z2luSWQiLCJwbHVnaW4iLCJwbHVnaW5zIiwibm90IiwiX3RoaXMiLCJhZGRDbG9zZW1lTGlzdGVuZXIiLCJ5ZXRpQm94ZXMiLCJwbHVnTmFtZXMiLCJjb25jYXQiLCJlcnJvciIsImxpc3RlbmVycyIsIm1hcCIsImpvaW4iLCJkZWJvdW5jZUdsb2JhbExpc3RlbmVyIiwiZGVib3VuY2UiLCJsaXN0ZW5lciIsInRpbWVyIiwiYXJncyIsInByb3RvdHlwZSIsImNhbGwiLCJhcmd1bWVudHMiLCJjbGVhclRpbWVvdXQiLCJhZGRSZXNpemVMaXN0ZW5lciIsImFkZFNjcm9sbExpc3RlbmVyIiwiYWRkTXV0YXRpb25FdmVudHNMaXN0ZW5lciIsImxpc3RlbmluZ0VsZW1lbnRzTXV0YXRpb24iLCJtdXRhdGlvblJlY29yZHNMaXN0IiwiJHRhcmdldCIsImF0dHJpYnV0ZU5hbWUiLCJwYWdlWU9mZnNldCIsImNsb3Nlc3QiLCJlbGVtZW50T2JzZXJ2ZXIiLCJvYnNlcnZlIiwiYXR0cmlidXRlcyIsImNoaWxkTGlzdCIsImNoYXJhY3RlckRhdGEiLCJzdWJ0cmVlIiwiYXR0cmlidXRlRmlsdGVyIiwiYWRkU2ltcGxlTGlzdGVuZXJzIiwiJGRvY3VtZW50IiwiYWRkR2xvYmFsTGlzdGVuZXJzIiwiaW5pdCIsIkZvdW5kYXRpb24iLCJ0cmlnZ2Vyc0luaXRpYWxpemVkIiwiSUhlYXJZb3UiLCJpbml0Q2xhc3NlcyIsImFjdGl2ZUNsYXNzZXMiLCJhbmltYXRlSW4iLCJhbmltYXRlIiwiTW92ZSIsImR1cmF0aW9uIiwiYW5pbSIsInByb2ciLCJzdGFydCIsIm1vdmUiLCJ0cyIsInJlcXVlc3RBbmltYXRpb25GcmFtZSIsImNhbmNlbEFuaW1hdGlvbkZyYW1lIiwiaXNJbiIsImluaXRDbGFzcyIsImFjdGl2ZUNsYXNzIiwicmVzZXQiLCJhZGRDbGFzcyIsInNob3ciLCJvZmZzZXRXaWR0aCIsImZpbmlzaCIsImhpZGUiLCJ0cmFuc2l0aW9uRHVyYXRpb24iLCJyZW1vdmVDbGFzcyIsIkJveCIsIkltTm90VG91Y2hpbmdZb3UiLCJPdmVybGFwQXJlYSIsIkdldERpbWVuc2lvbnMiLCJHZXRPZmZzZXRzIiwiR2V0RXhwbGljaXRPZmZzZXRzIiwicGFyZW50IiwibHJPbmx5IiwidGJPbmx5IiwiaWdub3JlQm90dG9tIiwiZWxlRGltcyIsInRvcE92ZXIiLCJib3R0b21PdmVyIiwibGVmdE92ZXIiLCJyaWdodE92ZXIiLCJwYXJEaW1zIiwiaGVpZ2h0Iiwib2Zmc2V0IiwidG9wIiwibGVmdCIsIndpbmRvd0RpbXMiLCJtaW4iLCJzcXJ0IiwiRXJyb3IiLCJyZWN0IiwiZ2V0Qm91bmRpbmdDbGllbnRSZWN0IiwicGFyUmVjdCIsIndpblJlY3QiLCJib2R5Iiwid2luWSIsIndpblgiLCJwYWdlWE9mZnNldCIsInBhcmVudERpbXMiLCJhbmNob3IiLCJwb3NpdGlvbiIsInZPZmZzZXQiLCJoT2Zmc2V0IiwiaXNPdmVyZmxvdyIsImxvZyIsIiRlbGVEaW1zIiwiJGFuY2hvckRpbXMiLCJhbGlnbm1lbnQiLCJ0b3BWYWwiLCJsZWZ0VmFsIiwib25JbWFnZXNMb2FkZWQiLCJpbWFnZXMiLCJ1bmxvYWRlZCIsImNvbXBsZXRlIiwibmF0dXJhbFdpZHRoIiwic2luZ2xlSW1hZ2VMb2FkZWQiLCJpbWFnZSIsIkltYWdlIiwiZXZlbnRzIiwibWUiLCJzcmMiLCJOZXN0IiwiRmVhdGhlciIsIm1lbnUiLCJpdGVtcyIsInN1Yk1lbnVDbGFzcyIsInN1Ykl0ZW1DbGFzcyIsImhhc1N1YkNsYXNzIiwiYXBwbHlBcmlhIiwiJGl0ZW0iLCIkc3ViIiwiY2hpbGRyZW4iLCJCdXJuIiwiVG91Y2giLCJzdGFydFBvc1giLCJzdGFydFBvc1kiLCJzdGFydFRpbWUiLCJlbGFwc2VkVGltZSIsInN0YXJ0RXZlbnQiLCJpc01vdmluZyIsImRpZE1vdmVkIiwib25Ub3VjaEVuZCIsInJlbW92ZUV2ZW50TGlzdGVuZXIiLCJvblRvdWNoTW92ZSIsInRhcEV2ZW50IiwiRXZlbnQiLCJzcG90U3dpcGUiLCJ4IiwidG91Y2hlcyIsInBhZ2VYIiwieSIsInBhZ2VZIiwiZHgiLCJkeSIsImRpciIsIkRhdGUiLCJnZXRUaW1lIiwiYWJzIiwibW92ZVRocmVzaG9sZCIsInRpbWVUaHJlc2hvbGQiLCJvblRvdWNoU3RhcnQiLCJhZGRFdmVudExpc3RlbmVyIiwidGVhcmRvd24iLCJTcG90U3dpcGUiLCJ2ZXJzaW9uIiwiZW5hYmxlZCIsImRvY3VtZW50RWxlbWVudCIsInNwZWNpYWwiLCJzd2lwZSIsInNldHVwIiwidGFwIiwibm9vcCIsInNldHVwU3BvdFN3aXBlIiwic2V0dXBUb3VjaEhhbmRsZXIiLCJhZGRUb3VjaCIsImhhbmRsZVRvdWNoIiwiY2hhbmdlZFRvdWNoZXMiLCJmaXJzdCIsImV2ZW50VHlwZXMiLCJ0b3VjaHN0YXJ0IiwidG91Y2htb3ZlIiwidG91Y2hlbmQiLCJzaW11bGF0ZWRFdmVudCIsIk1vdXNlRXZlbnQiLCJzY3JlZW5YIiwic2NyZWVuWSIsImNsaWVudFgiLCJjbGllbnRZIiwiY3JlYXRlRXZlbnQiLCJpbml0TW91c2VFdmVudCIsImRpc3BhdGNoRXZlbnQiLCJUaW1lciIsIm5hbWVTcGFjZSIsIk9iamVjdCIsInJlbWFpbiIsImlzUGF1c2VkIiwicmVzdGFydCIsIm5vdyIsImluZmluaXRlIiwicGF1c2UiLCJhZGRUb0pxdWVyeSIsIlN0aWNreSIsIlRvZ2dsZXIiLCJtb2R1bGUiLCJleHBvcnRzIiwiRk9VTkRBVElPTl9WRVJTSU9OIiwiX3BsdWdpbnMiLCJfdXVpZHMiLCJmdW5jdGlvbk5hbWUiLCJhdHRyTmFtZSIsInJlZ2lzdGVyUGx1Z2luIiwidW5yZWdpc3RlclBsdWdpbiIsInNwbGljZSIsImluZGV4T2YiLCJyZUluaXQiLCJpc0pRIiwiZm5zIiwicGxncyIsInAiLCJlcnIiLCJyZWZsb3ciLCJhZGRCYWNrIiwiJGVsIiwib3B0cyIsInRoaW5nIiwib3B0IiwicGFyc2VWYWx1ZSIsImVyIiwiZ2V0Rm5OYW1lIiwibWV0aG9kIiwiJG5vSlMiLCJwbHVnQ2xhc3MiLCJSZWZlcmVuY2VFcnJvciIsIlR5cGVFcnJvciIsInV0aWwiLCJ0aHJvdHRsZSIsImZ1bmMiLCJkZWxheSIsImNvbnRleHQiLCJ2ZW5kb3JzIiwidnAiLCJ0ZXN0IiwibmF2aWdhdG9yIiwidXNlckFnZW50IiwibGFzdFRpbWUiLCJuZXh0VGltZSIsIm1heCIsInBlcmZvcm1hbmNlIiwiRnVuY3Rpb24iLCJvVGhpcyIsImFBcmdzIiwiZlRvQmluZCIsImZOT1AiLCJmQm91bmQiLCJmdW5jTmFtZVJlZ2V4IiwicmVzdWx0cyIsImV4ZWMiLCJpc05hTiIsInBhcnNlRmxvYXQiLCJkZWZhdWx0cyIsIiRwYXJlbnQiLCIkY29udGFpbmVyIiwid2FzV3JhcHBlZCIsIndyYXAiLCJjb250YWluZXIiLCJjb250YWluZXJDbGFzcyIsInN0aWNreUNsYXNzIiwic2Nyb2xsQ291bnQiLCJjaGVja0V2ZXJ5IiwiaXNTdHVjayIsIm9uTG9hZExpc3RlbmVyIiwiY29udGFpbmVySGVpZ2h0IiwiZWxlbUhlaWdodCIsIiRhbmNob3IiLCJfcGFyc2VQb2ludHMiLCJfc2V0U2l6ZXMiLCJzY3JvbGwiLCJfY2FsYyIsIl9yZW1vdmVTdGlja3kiLCJ0b3BQb2ludCIsIl9ldmVudHMiLCJyZXZlcnNlIiwidG9wQW5jaG9yIiwiYnRtIiwiYnRtQW5jaG9yIiwic2Nyb2xsSGVpZ2h0IiwicHRzIiwiYnJlYWtzIiwibGVuIiwicHQiLCJwbGFjZSIsInBvaW50cyIsImlzT24iLCJjYW5TdGljayIsIl9ldmVudHNIYW5kbGVyIiwiX3BhdXNlTGlzdGVuZXJzIiwiY2hlY2tTaXplcyIsImJvdHRvbVBvaW50IiwiX3NldFN0aWNreSIsInN0aWNrVG8iLCJtcmduIiwibm90U3R1Y2tUbyIsImlzVG9wIiwic3RpY2tUb1RvcCIsImFuY2hvclB0IiwiYW5jaG9ySGVpZ2h0IiwidG9wT3JCb3R0b20iLCJzdGlja3lPbiIsIm5ld0VsZW1XaWR0aCIsImNvbXAiLCJwZG5nbCIsInBhcnNlSW50IiwicGRuZ3IiLCJuZXdDb250YWluZXJIZWlnaHQiLCJoYXNDbGFzcyIsIl9zZXRCcmVha1BvaW50cyIsIm1Ub3AiLCJlbUNhbGMiLCJtYXJnaW5Ub3AiLCJtQnRtIiwibWFyZ2luQm90dG9tIiwid2luSGVpZ2h0IiwiaW5uZXJIZWlnaHQiLCJib3R0b20iLCJ1bndyYXAiLCJlbSIsImZvbnRTaXplIiwiaW5wdXQiLCJhbmltYXRpb25JbiIsImFuaW1hdGlvbk91dCIsIiR0cmlnZ2VycyIsImluZGV4IiwiJHRyaWdnZXIiLCJjb250cm9scyIsImNvbnRhaW5zSWQiLCJSZWdFeHAiLCJ0b2dnbGUiLCJ0b2dnbGVDbGFzcyIsIl91cGRhdGVBUklBIl0sIm1hcHBpbmdzIjoiO0FBQUE7QUFDQTs7QUFFQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7OztBQUdBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLGFBQUs7QUFDTDtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBLG1DQUEyQiwwQkFBMEIsRUFBRTtBQUN2RCx5Q0FBaUMsZUFBZTtBQUNoRDtBQUNBO0FBQ0E7O0FBRUE7QUFDQSw4REFBc0QsK0RBQStEOztBQUVySDtBQUNBOztBQUVBO0FBQ0E7Ozs7Ozs7QUM3REEsd0I7Ozs7Ozs7Ozs7Ozs7Ozs7QUNRQTs7QUFDQUEsRUFBR0MsUUFBSCxFQUFjQyxVQUFkLEcsQ0FUQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLGlCOzs7Ozs7Ozs7QUNQQTs7Ozs7OztBQUVBOzs7Ozs7QUFFQTs7QUFFRTs7O0FBR0YsU0FBU0MsR0FBVCxHQUFlO0FBQ2IsU0FBTyxzQkFBRSxNQUFGLEVBQVVDLElBQVYsQ0FBZSxLQUFmLE1BQTBCLEtBQWpDO0FBQ0Q7O0FBRUQ7Ozs7Ozs7O0FBUUEsU0FBU0MsV0FBVCxDQUFxQkMsTUFBckIsRUFBNkJDLFNBQTdCLEVBQXVDO0FBQ3JDRCxXQUFT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dWxlIGNhY2hlXG4gXHR2YXIgaW5zdGFsbGVkTW9kdWxlcyA9IHt9O1xuXG4gXHQvLyBUaGUgcmVxdWlyZSBmdW5jdGlvblxuIFx0ZnVuY3Rpb24gX193ZWJwYWNrX3JlcXVpcmVfXyhtb2R1bGVJZCkge1xuXG4gXHRcdC8vIENoZWNrIGlmIG1vZHVsZSBpcyBpbiBjYWNoZVxuIFx0XHRpZihpbnN0YWxsZWRNb2R1bGVzW21vZHVsZUlkXSkge1xuIFx0XHRcdHJldHVybiBpbnN0YWxsZWRNb2R1bGVzW21vZHVsZUlkXS5leHBvcnRzO1xuIFx0XHR9XG4gXHRcdC8vIENyZWF0ZSBhIG5ldyBtb2R1bGUgKGFuZCBwdXQgaXQgaW50byB0aGUgY2FjaGUpXG4gXHRcdHZhciBtb2R1bGUgPSBpbnN0YWxsZWRNb2R1bGVzW21vZHVsZUlkXSA9IHtcbiBcdFx0XHRpOiBtb2R1bGVJZCxcbiBcdFx0XHRsOiBmYWxzZSxcbiBcdFx0XHRleHBvcnRzOiB7fVxuIFx0XHR9O1xuXG4gXHRcdC8vIEV4ZWN1dGUgdGhlIG1vZHVsZSBmdW5jdGlvblxuIFx0XHRtb2R1bGVzW21vZHVsZUlkXS5jYWxsKG1vZHVsZS5leHBvcnRzLCBtb2R1bGUsIG1vZHVsZS5leHBvcnRzLCBfX3dlYnBhY2tfcmVxdWlyZV9fKTtcblxuIFx0XHQvLyBGbGFnIHRoZSBtb2R1bGUgYXMgbG9hZGVkXG4gXHRcdG1vZHVsZS5sID0gdHJ1ZTtcblxuIFx0XHQvLyBSZXR1cm4gdGhlIGV4cG9ydHMgb2YgdGhlIG1vZHVsZVxuIFx0XHRyZXR1cm4gbW9kdWxlLmV4cG9ydHM7XG4gXHR9XG5cblxuIFx0Ly8gZXhwb3NlIHRoZSBtb2R1bGVzIG9iamVjdCAoX193ZWJwYWNrX21vZHVsZXNfXylcbiBcdF9fd2VicGFja19yZXF1aXJlX18ubSA9IG1vZHVsZXM7XG5cbiBcdC8vIGV4cG9zZSB0aGUgbW9kdWxlIGNhY2hlXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLmMgPSBpbnN0YWxsZWRNb2R1bGVzO1xuXG4gXHQvLyBkZWZpbmUgZ2V0dGVyIGZ1bmN0aW9uIGZvciBoYXJtb255IGV4cG9ydHNcbiBcdF9fd2VicGFja19yZXF1aXJlX18uZCA9IGZ1bmN0aW9uKGV4cG9ydHMsIG5hbWUsIGdldHRlcikge1xuIFx0XHRpZighX193ZWJwYWNrX3JlcXVpcmVfXy5vKGV4cG9ydHMsIG5hbWUpKSB7XG4gXHRcdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsIG5hbWUsIHtcbiBcdFx0XHRcdGNvbmZpZ3VyYWJsZTogZmFsc2UsXG4gXHRcdFx0XHRlbnVtZXJhYmxlOiB0cnVlLFxuIFx0XHRcdFx0Z2V0OiBnZXR0ZXJcbiBcdFx0XHR9KTtcbiBcdFx0fVxuIFx0fTtcblxuIFx0Ly8gZ2V0RGVmYXVsdEV4cG9ydCBmdW5jdGlvbiBmb3IgY29tcGF0aWJpbGl0eSB3aXRoIG5vbi1oYXJtb255IG1vZHVsZXNcbiBcdF9fd2VicGFja19yZXF1aXJlX18ubiA9IGZ1bmN0aW9uKG1vZHVsZSkge1xuIFx0XHR2YXIgZ2V0dGVyID0gbW9kdWxlICYmIG1vZHVsZS5fX2VzTW9kdWxlID9cbiBcdFx0XHRmdW5jdGlvbiBnZXREZWZhdWx0KCkgeyByZXR1cm4gbW9kdWxlWydkZWZhdWx0J107IH0gOlxuIFx0XHRcdGZ1bmN0aW9uIGdldE1vZHVsZUV4cG9ydHMoKSB7IHJldHVybiBtb2R1bGU7IH07XG4gXHRcdF9fd2VicGFja19yZXF1aXJlX18uZChnZXR0ZXIsICdhJywgZ2V0dGVyKTtcbiBcdFx0cmV0dXJuIGdldHRlcjtcbiBcdH07XG5cbiBcdC8vIE9iamVjdC5wcm90b3R5cGUuaGFzT3duUHJvcGVydHkuY2FsbFxuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5vID0gZnVuY3Rpb24ob2JqZWN0LCBwcm9wZXJ0eSkgeyByZXR1cm4gT2JqZWN0LnByb3RvdHlwZS5oYXNPd25Qcm9wZXJ0eS5jYWxsKG9iamVjdCwgcHJvcGVydHkpOyB9O1xuXG4gXHQvLyBfX3dlYnBhY2tfcHVibGljX3BhdGhfX1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5wID0gXCJcIjtcblxuIFx0Ly8gTG9hZCBlbnRyeSBtb2R1bGUgYW5kIHJldHVybiBleHBvcnRzXG4gXHRyZXR1cm4gX193ZWJwYWNrX3JlcXVpcmVfXyhfX3dlYnBhY2tfcmVxdWlyZV9fLnMgPSAxKTtcblxuXG5cbi8vIFdFQlBBQ0sgRk9PVEVSIC8vXG4vLyB3ZWJwYWNrL2Jvb3RzdHJhcCAzYmYxYjVjNTQzYTRkY2FlZDNhNiIsIm1vZHVsZS5leHBvcnRzID0galF1ZXJ5O1xuXG5cbi8vLy8vLy8vLy8vLy8vLy8vL1xuLy8gV0VCUEFDSyBGT09URVJcbi8vIGV4dGVybmFsIFwialF1ZXJ5XCJcbi8vIG1vZHVsZSBpZCA9IDBcbi8vIG1vZHVsZSBjaHVua3MgPSAwIiwiLy8gaW1wb3J0ICQgZnJvbSAnanF1ZXJ5Jztcbi8vIGltcG9ydCB3aGF0SW5wdXQgZnJvbSAnd2hhdC1pbnB1dCc7XG4vL1xuLy8gd2luZG93LiQgPSAkO1xuLy9cbi8vIGltcG9ydCBGb3VuZGF0aW9uIGZyb20gJ2ZvdW5kYXRpb24tc2l0ZXMnO1xuLy8gSWYgeW91IHdhbnQgdG8gcGljayBhbmQgY2hvb3NlIHdoaWNoIG1vZHVsZXMgdG8gaW5jbHVkZSwgY29tbWVudCBvdXQgdGhlIGFib3ZlIGFuZCB1bmNvbW1lbnRcbi8vIHRoZSBsaW5lIGJlbG93XG5pbXBvcnQgJy4vbGliL2ZvdW5kYXRpb24tZXhwbGljaXQtcGllY2VzJztcbiQoIGRvY3VtZW50ICkuZm91bmRhdGlvbigpO1xuXG5cblxuLy8gV0VCUEFDSyBGT09URVIgLy9cbi8vIC4vc3JjL2Fzc2V0cy9qcy9hcHAuanMiLCJcInVzZSBzdHJpY3RcIjtcblxuaW1wb3J0ICQgZnJvbSAnanF1ZXJ5JztcblxuLy8gQ29yZSBGb3VuZGF0aW9uIFV0aWxpdGllcywgdXRpbGl6ZWQgaW4gYSBudW1iZXIgb2YgcGxhY2VzLlxuXG4gIC8qKlxuICAgKiBSZXR1cm5zIGEgYm9vbGVhbiBmb3IgUlRMIHN1cHBvcnRcbiAgICovXG5mdW5jdGlvbiBydGwoKSB7XG4gIHJldHVybiAkKCdodG1sJykuYXR0cignZGlyJykgPT09ICdydGwnO1xufVxuXG4vKipcbiAqIHJldHVybnMgYSByYW5kb20gYmFzZS0zNiB1aWQgd2l0aCBuYW1lc3BhY2luZ1xuICogQGZ1bmN0aW9uXG4gKiBAcGFyYW0ge051bWJlcn0gbGVuZ3RoIC0gbnVtYmVyIG9mIHJhbmRvbSBiYXNlLTM2IGRpZ2l0cyBkZXNpcmVkLiBJbmNyZWFzZSBmb3IgbW9yZSByYW5kb20gc3RyaW5ncy5cbiAqIEBwYXJhbSB7U3RyaW5nfSBuYW1lc3BhY2UgLSBuYW1lIG9mIHBsdWdpbiB0byBiZSBpbmNvcnBvcmF0ZWQgaW4gdWlkLCBvcHRpb25hbC5cbiAqIEBkZWZhdWx0IHtTdHJpbmd9ICcnIC0gaWYgbm8gcGx1Z2luIG5hbWUgaXMgcHJvdmlkZWQsIG5vdGhpbmcgaXMgYXBwZW5kZWQgdG8gdGhlIHVpZC5cbiAqIEByZXR1cm5zIHtTdHJpbmd9IC0gdW5pcXVlIGlkXG4gKi9cbmZ1bmN0aW9uIEdldFlvRGlnaXRzKGxlbmd0aCwgbmFtZXNwYWNlKXtcbiAgbGVuZ3RoID0gbGVuZ3RoIHx8IDY7XG4gIHJldHVybiBNYXRoLnJvdW5kKChNYXRoLnBvdygzNiwgbGVuZ3RoICsgMSkgLSBNYXRoLnJhbmRvbSgpICogTWF0aC5wb3coMzYsIGxlbmd0aCkpKS50b1N0cmluZygzNikuc2xpY2UoMSkgKyAobmFtZXNwYWNlID8gYC0ke25hbWVzcGFjZX1gIDogJycpO1xufVxuXG4vKipcbiAqIEVzY2FwZSBhIHN0cmluZyBzbyBpdCBjYW4gYmUgdXNlZCBhcyBhIHJlZ2V4cCBwYXR0ZXJuXG4gKiBAZnVuY3Rpb25cbiAqIEBzZWUgaHR0cHM6Ly9zdGFja292ZXJmbG93LmNvbS9hLzkzMTA3NTIvNDMxNzM4NFxuICpcbiAqIEBwYXJhbSB7U3RyaW5nfSBzdHIgLSBzdHJpbmcgdG8gZXNjYXBlLlxuICogQHJldHVybnMge1N0cmluZ30gLSBlc2NhcGVkIHN0cmluZ1xuICovXG5mdW5jdGlvbiBSZWdFeHBFc2NhcGUoc3RyKXtcbiAgcmV0dXJuIHN0ci5yZXBsYWNlKC9bLVtcXF17fSgpKis/LixcXFxcXiR8I1xcc10vZywgJ1xcXFwkJicpO1xufVxuXG5mdW5jdGlvbiB0cmFuc2l0aW9uZW5kKCRlbGVtKXtcbiAgdmFyIHRyYW5zaXRpb25zID0ge1xuICAgICd0cmFuc2l0aW9uJzogJ3RyYW5zaXRpb25lbmQnLFxuICAgICdXZWJraXRUcmFuc2l0aW9uJzogJ3dlYmtpdFRyYW5zaXRpb25FbmQnLFxuICAgICdNb3pUcmFuc2l0aW9uJzogJ3RyYW5zaXRpb25lbmQnLFxuICAgICdPVHJhbnNpdGlvbic6ICdvdHJhbnNpdGlvbmVuZCdcbiAgfTtcbiAgdmFyIGVsZW0gPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCdkaXYnKSxcbiAgICAgIGVuZDtcblxuICBmb3IgKHZhciB0IGluIHRyYW5zaXRpb25zKXtcbiAgICBpZiAodHlwZW9mIGVsZW0uc3R5bGVbdF0gIT09ICd1bmRlZmluZWQnKXtcbiAgICAgIGVuZCA9IHRyYW5zaXRpb25zW3RdO1xuICAgIH1cbiAgfVxuICBpZihlbmQpe1xuICAgIHJldHVybiBlbmQ7XG4gIH1lbHNle1xuICAgIGVuZCA9IHNldFRpbWVvdXQoZnVuY3Rpb24oKXtcbiAgICAgICRlbGVtLnRyaWdnZXJIYW5kbGVyKCd0cmFuc2l0aW9uZW5kJywgWyRlbGVtXSk7XG4gICAgfSwgMSk7XG4gICAgcmV0dXJuICd0cmFuc2l0aW9uZW5kJztcbiAgfVxufVxuXG4vKipcbiAqIFJldHVybiBhbiBldmVudCB0eXBlIHRvIGxpc3RlbiBmb3Igd2luZG93IGxvYWQuXG4gKlxuICogSWYgYCRlbGVtYCBpcyBwYXNzZWQsIGFuIGV2ZW50IHdpbGwgYmUgdHJpZ2dlcmVkIG9uIGAkZWxlbWAuIElmIHdpbmRvdyBpcyBhbHJlYWR5IGxvYWRlZCwgdGhlIGV2ZW50IHdpbGwgc3RpbGwgYmUgdHJpZ2dlcmVkLlxuICogSWYgYGhhbmRsZXJgIGlzIHBhc3NlZCwgYXR0YWNoIGl0IHRvIHRoZSBldmVudCBvbiBgJGVsZW1gLlxuICogQ2FsbGluZyBgb25Mb2FkYCB3aXRob3V0IGhhbmRsZXIgYWxsb3dzIHlvdSB0byBnZXQgdGhlIGV2ZW50IHR5cGUgdGhhdCB3aWxsIGJlIHRyaWdnZXJlZCBiZWZvcmUgYXR0YWNoaW5nIHRoZSBoYW5kbGVyIGJ5IHlvdXJzZWxmLlxuICogQGZ1bmN0aW9uXG4gKlxuICogQHBhcmFtIHtPYmplY3R9IFtdICRlbGVtIC0galF1ZXJ5IGVsZW1lbnQgb24gd2hpY2ggdGhlIGV2ZW50IHdpbGwgYmUgdHJpZ2dlcmVkIGlmIHBhc3NlZC5cbiAqIEBwYXJhbSB7RnVuY3Rpb259IFtdIGhhbmRsZXIgLSBmdW5jdGlvbiB0byBhdHRhY2ggdG8gdGhlIGV2ZW50LlxuICogQHJldHVybnMge1N0cmluZ30gLSBldmVudCB0eXBlIHRoYXQgc2hvdWxkIG9yIHdpbGwgYmUgdHJpZ2dlcmVkLlxuICovXG5mdW5jdGlvbiBvbkxvYWQoJGVsZW0sIGhhbmRsZXIpIHtcbiAgY29uc3QgZGlkTG9hZCA9IGRvY3VtZW50LnJlYWR5U3RhdGUgPT09ICdjb21wbGV0ZSc7XG4gIGNvbnN0IGV2ZW50VHlwZSA9IChkaWRMb2FkID8gJ19kaWRMb2FkJyA6ICdsb2FkJykgKyAnLnpmLnV0aWwub25Mb2FkJztcbiAgY29uc3QgY2IgPSAoKSA9PiAkZWxlbS50cmlnZ2VySGFuZGxlcihldmVudFR5cGUpO1xuXG4gIGlmICgkZWxlbSkge1xuICAgIGlmIChoYW5kbGVyKSAkZWxlbS5vbmUoZXZlbnRUeXBlLCBoYW5kbGVyKTtcblxuICAgIGlmIChkaWRMb2FkKVxuICAgICAgc2V0VGltZW91dChjYik7XG4gICAgZWxzZVxuICAgICAgJCh3aW5kb3cpLm9uZSgnbG9hZCcsIGNiKTtcbiAgfVxuXG4gIHJldHVybiBldmVudFR5cGU7XG59XG5cbi8qKlxuICogUmV0dW5zIGFuIGhhbmRsZXIgZm9yIHRoZSBgbW91c2VsZWF2ZWAgdGhhdCBpZ25vcmUgZGlzYXBwZWFyZWQgbW91c2VzLlxuICpcbiAqIElmIHRoZSBtb3VzZSBcImRpc2FwcGVhcmVkXCIgZnJvbSB0aGUgZG9jdW1lbnQgKGxpa2Ugd2hlbiBnb2luZyBvbiBhIGJyb3dzZXIgVUkgZWxlbWVudCwgU2VlIGh0dHBzOi8vZ2l0LmlvL3pmLTExNDEwKSxcbiAqIHRoZSBldmVudCBpcyBpZ25vcmVkLlxuICogLSBJZiB0aGUgYGlnbm9yZUxlYXZlV2luZG93YCBpcyBgdHJ1ZWAsIHRoZSBldmVudCBpcyBpZ25vcmVkIHdoZW4gdGhlIHVzZXIgYWN0dWFsbHkgbGVmdCB0aGUgd2luZG93XG4gKiAgIChsaWtlIGJ5IHN3aXRjaGluZyB0byBhbiBvdGhlciB3aW5kb3cgd2l0aCBbQWx0XStbVGFiXSkuXG4gKiAtIElmIHRoZSBgaWdub3JlUmVhcHBlYXJgIGlzIGB0cnVlYCwgdGhlIGV2ZW50IHdpbGwgYmUgaWdub3JlZCB3aGVuIHRoZSBtb3VzZSB3aWxsIHJlYXBwZWFyIGxhdGVyIG9uIHRoZSBkb2N1bWVudFxuICogICBvdXRzaWRlIG9mIHRoZSBlbGVtZW50IGl0IGxlZnQuXG4gKlxuICogQGZ1bmN0aW9uXG4gKlxuICogQHBhcmFtIHtGdW5jdGlvbn0gW10gaGFuZGxlciAtIGhhbmRsZXIgZm9yIHRoZSBmaWx0ZXJlZCBgbW91c2VsZWF2ZWAgZXZlbnQgdG8gd2F0Y2guXG4gKiBAcGFyYW0ge09iamVjdH0gW10gb3B0aW9ucyAtIG9iamVjdCBvZiBvcHRpb25zOlxuICogLSB7Qm9vbGVhbn0gW2ZhbHNlXSBpZ25vcmVMZWF2ZVdpbmRvdyAtIGFsc28gaWdub3JlIHdoZW4gdGhlIHVzZXIgc3dpdGNoZWQgd2luZG93cy5cbiAqIC0ge0Jvb2xlYW59IFtmYWxzZV0gaWdub3JlUmVhcHBlYXIgLSBhbHNvIGlnbm9yZSB3aGVuIHRoZSBtb3VzZSByZWFwcGVhcmVkIG91dHNpZGUgb2YgdGhlIGVsZW1lbnQgaXQgbGVmdC5cbiAqIEByZXR1cm5zIHtGdW5jdGlvbn0gLSBmaWx0ZXJlZCBoYW5kbGVyIHRvIHVzZSB0byBsaXN0ZW4gb24gdGhlIGBtb3VzZWxlYXZlYCBldmVudC5cbiAqL1xuZnVuY3Rpb24gaWdub3JlTW91c2VkaXNhcHBlYXIoaGFuZGxlciwgeyBpZ25vcmVMZWF2ZVdpbmRvdyA9IGZhbHNlLCBpZ25vcmVSZWFwcGVhciA9IGZhbHNlIH0gPSB7fSkge1xuICByZXR1cm4gZnVuY3Rpb24gbGVhdmVFdmVudEhhbmRsZXIoZUxlYXZlLCAuLi5yZXN0KSB7XG4gICAgY29uc3QgY2FsbGJhY2sgPSBoYW5kbGVyLmJpbmQodGhpcywgZUxlYXZlLCAuLi5yZXN0KTtcblxuICAgIC8vIFRoZSBtb3VzZSBsZWZ0OiBjYWxsIHRoZSBnaXZlbiBjYWxsYmFjayBpZiB0aGUgbW91c2UgZW50ZXJlZCBlbHNld2hlcmVcbiAgICBpZiAoZUxlYXZlLnJlbGF0ZWRUYXJnZXQgIT09IG51bGwpIHtcbiAgICAgIHJldHVybiBjYWxsYmFjaygpO1xuICAgIH1cblxuICAgIC8vIE90aGVyd2lzZSwgY2hlY2sgaWYgdGhlIG1vdXNlIGFjdHVhbGx5IGxlZnQgdGhlIHdpbmRvdy5cbiAgICAvLyBJbiBmaXJlZm94IGlmIHRoZSB1c2VyIHN3aXRjaGVkIGJldHdlZW4gd2luZG93cywgdGhlIHdpbmRvdyBzaWxsIGhhdmUgdGhlIGZvY3VzIGJ5IHRoZSB0aW1lXG4gICAgLy8gdGhlIGV2ZW50IGlzIHRyaWdnZXJlZC4gV2UgaGF2ZSB0byBkZWJvdW5jZSB0aGUgZXZlbnQgdG8gdGVzdCB0aGlzIGNhc2UuXG4gICAgc2V0VGltZW91dChmdW5jdGlvbiBsZWF2ZUV2ZW50RGVib3VuY2VyKCkge1xuICAgICAgaWYgKCFpZ25vcmVMZWF2ZVdpbmRvdyAmJiBkb2N1bWVudC5oYXNGb2N1cyAmJiAhZG9jdW1lbnQuaGFzRm9jdXMoKSkge1xuICAgICAgICByZXR1cm4gY2FsbGJhY2soKTtcbiAgICAgIH1cblxuICAgICAgLy8gT3RoZXJ3aXNlLCB3YWl0IGZvciB0aGUgbW91c2UgdG8gcmVlYXBlYXIgb3V0c2lkZSBvZiB0aGUgZWxlbWVudCxcbiAgICAgIGlmICghaWdub3JlUmVhcHBlYXIpIHtcbiAgICAgICAgJChkb2N1bWVudCkub25lKCdtb3VzZWVudGVyJywgZnVuY3Rpb24gcmVlbnRlckV2ZW50SGFuZGxlcihlUmVlbnRlcikge1xuICAgICAgICAgIGlmICghJChlTGVhdmUuY3VycmVudFRhcmdldCkuaGFzKGVSZWVudGVyLnRhcmdldCkubGVuZ3RoKSB7XG4gICAgICAgICAgICAvLyBGaWxsIHdoZXJlIHRoZSBtb3VzZSBmaW5hbGx5IGVudGVyZWQuXG4gICAgICAgICAgICBlTGVhdmUucmVsYXRlZFRhcmdldCA9IGVSZWVudGVyLnRhcmdldDtcbiAgICAgICAgICAgIGNhbGxiYWNrKCk7XG4gICAgICAgICAgfVxuICAgICAgICB9KTtcbiAgICAgIH1cblxuICAgIH0sIDApO1xuICB9O1xufVxuXG5leHBvcnQgeyBydGwsIEdldFlvRGlnaXRzLCBSZWdFeHBFc2NhcGUsIHRyYW5zaXRpb25lbmQsIG9uTG9hZCwgaWdub3JlTW91c2VkaXNhcHBlYXIgfTtcblxuXG5cbi8vIFdFQlBBQ0sgRk9PVEVSIC8vXG4vLyAuL25vZGVfbW9kdWxlcy9mb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24uY29yZS51dGlscy5qcyIsIid1c2Ugc3RyaWN0JztcblxuaW1wb3J0ICQgZnJvbSAnanF1ZXJ5JztcbmltcG9ydCB7IEdldFlvRGlnaXRzIH0gZnJvbSAnLi9mb3VuZGF0aW9uLmNvcmUudXRpbHMnO1xuXG4vLyBBYnN0cmFjdCBjbGFzcyBmb3IgcHJvdmlkaW5nIGxpZmVjeWNsZSBob29rcy4gRXhwZWN0IHBsdWdpbnMgdG8gZGVmaW5lIEFUIExFQVNUXG4vLyB7ZnVuY3Rpb259IF9zZXR1cCAocmVwbGFjZXMgcHJldmlvdXMgY29uc3RydWN0b3IpLFxuLy8ge2Z1bmN0aW9ufSBfZGVzdHJveSAocmVwbGFjZXMgcHJldmlvdXMgZGVzdHJveSlcbmNsYXNzIFBsdWdpbiB7XG5cbiAgY29uc3RydWN0b3IoZWxlbWVudCwgb3B0aW9ucykge1xuICAgIHRoaXMuX3NldHVwKGVsZW1lbnQsIG9wdGlvbnMpO1xuICAgIHZhciBwbHVnaW5OYW1lID0gZ2V0UGx1Z2luTmFtZSh0aGlzKTtcbiAgICB0aGlzLnV1aWQgPSBHZXRZb0RpZ2l0cyg2LCBwbHVnaW5OYW1lKTtcblxuICAgIGlmKCF0aGlzLiRlbGVtZW50LmF0dHIoYGRhdGEtJHtwbHVnaW5OYW1lfWApKXsgdGhpcy4kZWxlbWVudC5hdHRyKGBkYXRhLSR7cGx1Z2luTmFtZX1gLCB0aGlzLnV1aWQpOyB9XG4gICAgaWYoIXRoaXMuJGVsZW1lbnQuZGF0YSgnemZQbHVnaW4nKSl7IHRoaXMuJGVsZW1lbnQuZGF0YSgnemZQbHVnaW4nLCB0aGlzKTsgfVxuICAgIC8qKlxuICAgICAqIEZpcmVzIHdoZW4gdGhlIHBsdWdpbiBoYXMgaW5pdGlhbGl6ZWQuXG4gICAgICogQGV2ZW50IFBsdWdpbiNpbml0XG4gICAgICovXG4gICAgdGhpcy4kZWxlbWVudC50cmlnZ2VyKGBpbml0LnpmLiR7cGx1Z2luTmFtZX1gKTtcbiAgfVxuXG4gIGRlc3Ryb3koKSB7XG4gICAgdGhpcy5fZGVzdHJveSgpO1xuICAgIHZhciBwbHVnaW5OYW1lID0gZ2V0UGx1Z2luTmFtZSh0aGlzKTtcbiAgICB0aGlzLiRlbGVtZW50LnJlbW92ZUF0dHIoYGRhdGEtJHtwbHVnaW5OYW1lfWApLnJlbW92ZURhdGEoJ3pmUGx1Z2luJylcbiAgICAgICAgLyoqXG4gICAgICAgICAqIEZpcmVzIHdoZW4gdGhlIHBsdWdpbiBoYXMgYmVlbiBkZXN0cm95ZWQuXG4gICAgICAgICAqIEBldmVudCBQbHVnaW4jZGVzdHJveWVkXG4gICAgICAgICAqL1xuICAgICAgICAudHJpZ2dlcihgZGVzdHJveWVkLnpmLiR7cGx1Z2luTmFtZX1gKTtcbiAgICBmb3IodmFyIHByb3AgaW4gdGhpcyl7XG4gICAgICB0aGlzW3Byb3BdID0gbnVsbDsvL2NsZWFuIHVwIHNjcmlwdCB0byBwcmVwIGZvciBnYXJiYWdlIGNvbGxlY3Rpb24uXG4gICAgfVxuICB9XG59XG5cbi8vIENvbnZlcnQgUGFzY2FsQ2FzZSB0byBrZWJhYi1jYXNlXG4vLyBUaGFuayB5b3U6IGh0dHA6Ly9zdGFja292ZXJmbG93LmNvbS9hLzg5NTU1ODBcbmZ1bmN0aW9uIGh5cGhlbmF0ZShzdHIpIHtcbiAgcmV0dXJuIHN0ci5yZXBsYWNlKC8oW2Etel0pKFtBLVpdKS9nLCAnJDEtJDInKS50b0xvd2VyQ2FzZSgpO1xufVxuXG5mdW5jdGlvbiBnZXRQbHVnaW5OYW1lKG9iaikge1xuICBpZih0eXBlb2Yob2JqLmNvbnN0cnVjdG9yLm5hbWUpICE9PSAndW5kZWZpbmVkJykge1xuICAgIHJldHVybiBoeXBoZW5hdGUob2JqLmNvbnN0cnVjdG9yLm5hbWUpO1xuICB9IGVsc2Uge1xuICAgIHJldHVybiBoeXBoZW5hdGUob2JqLmNsYXNzTmFtZSk7XG4gIH1cbn1cblxuZXhwb3J0IHtQbHVnaW59O1xuXG5cblxuLy8gV0VCUEFDSyBGT09URVIgLy9cbi8vIC4vbm9kZV9tb2R1bGVzL2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi5jb3JlLnBsdWdpbi5qcyIsIid1c2Ugc3RyaWN0JztcblxuaW1wb3J0ICQgZnJvbSAnanF1ZXJ5JztcblxuLy8gRGVmYXVsdCBzZXQgb2YgbWVkaWEgcXVlcmllc1xuY29uc3QgZGVmYXVsdFF1ZXJpZXMgPSB7XG4gICdkZWZhdWx0JyA6ICdvbmx5IHNjcmVlbicsXG4gIGxhbmRzY2FwZSA6ICdvbmx5IHNjcmVlbiBhbmQgKG9yaWVudGF0aW9uOiBsYW5kc2NhcGUpJyxcbiAgcG9ydHJhaXQgOiAnb25seSBzY3JlZW4gYW5kIChvcmllbnRhdGlvbjogcG9ydHJhaXQpJyxcbiAgcmV0aW5hIDogJ29ubHkgc2NyZWVuIGFuZCAoLXdlYmtpdC1taW4tZGV2aWNlLXBpeGVsLXJhdGlvOiAyKSwnICtcbiAgICAnb25seSBzY3JlZW4gYW5kIChtaW4tLW1vei1kZXZpY2UtcGl4ZWwtcmF0aW86IDIpLCcgK1xuICAgICdvbmx5IHNjcmVlbiBhbmQgKC1vLW1pbi1kZXZpY2UtcGl4ZWwtcmF0aW86IDIvMSksJyArXG4gICAgJ29ubHkgc2NyZWVuIGFuZCAobWluLWRldmljZS1waXhlbC1yYXRpbzogMiksJyArXG4gICAgJ29ubHkgc2NyZWVuIGFuZCAobWluLXJlc29sdXRpb246IDE5MmRwaSksJyArXG4gICAgJ29ubHkgc2NyZWVuIGFuZCAobWluLXJlc29sdXRpb246IDJkcHB4KSdcbiAgfTtcblxuXG4vLyBtYXRjaE1lZGlhKCkgcG9seWZpbGwgLSBUZXN0IGEgQ1NTIG1lZGlhIHR5cGUvcXVlcnkgaW4gSlMuXG4vLyBBdXRob3JzICYgY29weXJpZ2h0KGMpIDIwMTI6IFNjb3R0IEplaGwsIFBhdWwgSXJpc2gsIE5pY2hvbGFzIFpha2FzLCBEYXZpZCBLbmlnaHQuIE1JVCBsaWNlbnNlXG4vKiBlc2xpbnQtZGlzYWJsZSAqL1xud2luZG93Lm1hdGNoTWVkaWEgfHwgKHdpbmRvdy5tYXRjaE1lZGlhID0gKGZ1bmN0aW9uICgpIHtcbiAgXCJ1c2Ugc3RyaWN0XCI7XG5cbiAgLy8gRm9yIGJyb3dzZXJzIHRoYXQgc3VwcG9ydCBtYXRjaE1lZGl1bSBhcGkgc3VjaCBhcyBJRSA5IGFuZCB3ZWJraXRcbiAgdmFyIHN0eWxlTWVkaWEgPSAod2luZG93LnN0eWxlTWVkaWEgfHwgd2luZG93Lm1lZGlhKTtcblxuICAvLyBGb3IgdGhvc2UgdGhhdCBkb24ndCBzdXBwb3J0IG1hdGNoTWVkaXVtXG4gIGlmICghc3R5bGVNZWRpYSkge1xuICAgIHZhciBzdHlsZSAgID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnc3R5bGUnKSxcbiAgICBzY3JpcHQgICAgICA9IGRvY3VtZW50LmdldEVsZW1lbnRzQnlUYWdOYW1lKCdzY3JpcHQnKVswXSxcbiAgICBpbmZvICAgICAgICA9IG51bGw7XG5cbiAgICBzdHlsZS50eXBlICA9ICd0ZXh0L2Nzcyc7XG4gICAgc3R5bGUuaWQgICAgPSAnbWF0Y2htZWRpYWpzLXRlc3QnO1xuXG4gICAgaWYgKCFzY3JpcHQpIHtcbiAgICAgIGRvY3VtZW50LmhlYWQuYXBwZW5kQ2hpbGQoc3R5bGUpO1xuICAgIH0gZWxzZSB7XG4gICAgICBzY3JpcHQucGFyZW50Tm9kZS5pbnNlcnRCZWZvcmUoc3R5bGUsIHNjcmlwdCk7XG4gICAgfVxuXG4gICAgLy8gJ3N0eWxlLmN1cnJlbnRTdHlsZScgaXMgdXNlZCBieSBJRSA8PSA4IGFuZCAnd2luZG93LmdldENvbXB1dGVkU3R5bGUnIGZvciBhbGwgb3RoZXIgYnJvd3NlcnNcbiAgICBpbmZvID0gKCdnZXRDb21wdXRlZFN0eWxlJyBpbiB3aW5kb3cpICYmIHdpbmRvdy5nZXRDb21wdXRlZFN0eWxlKHN0eWxlLCBudWxsKSB8fCBzdHlsZS5jdXJyZW50U3R5bGU7XG5cbiAgICBzdHlsZU1lZGlhID0ge1xuICAgICAgbWF0Y2hNZWRpdW06IGZ1bmN0aW9uIChtZWRpYSkge1xuICAgICAgICB2YXIgdGV4dCA9ICdAbWVkaWEgJyArIG1lZGlhICsgJ3sgI21hdGNobWVkaWFqcy10ZXN0IHsgd2lkdGg6IDFweDsgfSB9JztcblxuICAgICAgICAvLyAnc3R5bGUuc3R5bGVTaGVldCcgaXMgdXNlZCBieSBJRSA8PSA4IGFuZCAnc3R5bGUudGV4dENvbnRlbnQnIGZvciBhbGwgb3RoZXIgYnJvd3NlcnNcbiAgICAgICAgaWYgKHN0eWxlLnN0eWxlU2hlZXQpIHtcbiAgICAgICAgICBzdHlsZS5zdHlsZVNoZWV0LmNzc1RleHQgPSB0ZXh0O1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgIHN0eWxlLnRleHRDb250ZW50ID0gdGV4dDtcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIFRlc3QgaWYgbWVkaWEgcXVlcnkgaXMgdHJ1ZSBvciBmYWxzZVxuICAgICAgICByZXR1cm4gaW5mby53aWR0aCA9PT0gJzFweCc7XG4gICAgICB9XG4gICAgfTtcbiAgfVxuXG4gIHJldHVybiBmdW5jdGlvbihtZWRpYSkge1xuICAgIHJldHVybiB7XG4gICAgICBtYXRjaGVzOiBzdHlsZU1lZGlhLm1hdGNoTWVkaXVtKG1lZGlhIHx8ICdhbGwnKSxcbiAgICAgIG1lZGlhOiBtZWRpYSB8fCAnYWxsJ1xuICAgIH07XG4gIH07XG59KSgpKTtcbi8qIGVzbGludC1lbmFibGUgKi9cblxudmFyIE1lZGlhUXVlcnkgPSB7XG4gIHF1ZXJpZXM6IFtdLFxuXG4gIGN1cnJlbnQ6ICcnLFxuXG4gIC8qKlxuICAgKiBJbml0aWFsaXplcyB0aGUgbWVkaWEgcXVlcnkgaGVscGVyLCBieSBleHRyYWN0aW5nIHRoZSBicmVha3BvaW50IGxpc3QgZnJvbSB0aGUgQ1NTIGFuZCBhY3RpdmF0aW5nIHRoZSBicmVha3BvaW50IHdhdGNoZXIuXG4gICAqIEBmdW5jdGlvblxuICAgKiBAcHJpdmF0ZVxuICAgKi9cbiAgX2luaXQoKSB7XG4gICAgdmFyIHNlbGYgPSB0aGlzO1xuICAgIHZhciAkbWV0YSA9ICQoJ21ldGEuZm91bmRhdGlvbi1tcScpO1xuICAgIGlmKCEkbWV0YS5sZW5ndGgpe1xuICAgICAgJCgnPG1ldGEgY2xhc3M9XCJmb3VuZGF0aW9uLW1xXCI+JykuYXBwZW5kVG8oZG9jdW1lbnQuaGVhZCk7XG4gICAgfVxuXG4gICAgdmFyIGV4dHJhY3RlZFN0eWxlcyA9ICQoJy5mb3VuZGF0aW9uLW1xJykuY3NzKCdmb250LWZhbWlseScpO1xuICAgIHZhciBuYW1lZFF1ZXJpZXM7XG5cbiAgICBuYW1lZFF1ZXJpZXMgPSBwYXJzZVN0eWxlVG9PYmplY3QoZXh0cmFjdGVkU3R5bGVzKTtcblxuICAgIGZvciAodmFyIGtleSBpbiBuYW1lZFF1ZXJpZXMpIHtcbiAgICAgIGlmKG5hbWVkUXVlcmllcy5oYXNPd25Qcm9wZXJ0eShrZXkpKSB7XG4gICAgICAgIHNlbGYucXVlcmllcy5wdXNoKHtcbiAgICAgICAgICBuYW1lOiBrZXksXG4gICAgICAgICAgdmFsdWU6IGBvbmx5IHNjcmVlbiBhbmQgKG1pbi13aWR0aDogJHtuYW1lZFF1ZXJpZXNba2V5XX0pYFxuICAgICAgICB9KTtcbiAgICAgIH1cbiAgICB9XG5cbiAgICB0aGlzLmN1cnJlbnQgPSB0aGlzLl9nZXRDdXJyZW50U2l6ZSgpO1xuXG4gICAgdGhpcy5fd2F0Y2hlcigpO1xuICB9LFxuXG4gIC8qKlxuICAgKiBDaGVja3MgaWYgdGhlIHNjcmVlbiBpcyBhdCBsZWFzdCBhcyB3aWRlIGFzIGEgYnJlYWtwb2ludC5cbiAgICogQGZ1bmN0aW9uXG4gICAqIEBwYXJhbSB7U3RyaW5nfSBzaXplIC0gTmFtZSBvZiB0aGUgYnJlYWtwb2ludCB0byBjaGVjay5cbiAgICogQHJldHVybnMge0Jvb2xlYW59IGB0cnVlYCBpZiB0aGUgYnJlYWtwb2ludCBtYXRjaGVzLCBgZmFsc2VgIGlmIGl0J3Mgc21hbGxlci5cbiAgICovXG4gIGF0TGVhc3Qoc2l6ZSkge1xuICAgIHZhciBxdWVyeSA9IHRoaXMuZ2V0KHNpemUpO1xuXG4gICAgaWYgKHF1ZXJ5KSB7XG4gICAgICByZXR1cm4gd2luZG93Lm1hdGNoTWVkaWEocXVlcnkpLm1hdGNoZXM7XG4gICAgfVxuXG4gICAgcmV0dXJuIGZhbHNlO1xuICB9LFxuXG4gIC8qKlxuICAgKiBDaGVja3MgaWYgdGhlIHNjcmVlbiBtYXRjaGVzIHRvIGEgYnJlYWtwb2ludC5cbiAgICogQGZ1bmN0aW9uXG4gICAqIEBwYXJhbSB7U3RyaW5nfSBzaXplIC0gTmFtZSBvZiB0aGUgYnJlYWtwb2ludCB0byBjaGVjaywgZWl0aGVyICdzbWFsbCBvbmx5JyBvciAnc21hbGwnLiBPbWl0dGluZyAnb25seScgZmFsbHMgYmFjayB0byB1c2luZyBhdExlYXN0KCkgbWV0aG9kLlxuICAgKiBAcmV0dXJucyB7Qm9vbGVhbn0gYHRydWVgIGlmIHRoZSBicmVha3BvaW50IG1hdGNoZXMsIGBmYWxzZWAgaWYgaXQgZG9lcyBub3QuXG4gICAqL1xuICBpcyhzaXplKSB7XG4gICAgc2l6ZSA9IHNpemUudHJpbSgpLnNwbGl0KCcgJyk7XG4gICAgaWYoc2l6ZS5sZW5ndGggPiAxICYmIHNpemVbMV0gPT09ICdvbmx5Jykge1xuICAgICAgaWYoc2l6ZVswXSA9PT0gdGhpcy5fZ2V0Q3VycmVudFNpemUoKSkgcmV0dXJuIHRydWU7XG4gICAgfSBlbHNlIHtcbiAgICAgIHJldHVybiB0aGlzLmF0TGVhc3Qoc2l6ZVswXSk7XG4gICAgfVxuICAgIHJldHVybiBmYWxzZTtcbiAgfSxcblxuICAvKipcbiAgICogR2V0cyB0aGUgbWVkaWEgcXVlcnkgb2YgYSBicmVha3BvaW50LlxuICAgKiBAZnVuY3Rpb25cbiAgICogQHBhcmFtIHtTdHJpbmd9IHNpemUgLSBOYW1lIG9mIHRoZSBicmVha3BvaW50IHRvIGdldC5cbiAgICogQHJldHVybnMge1N0cmluZ3xudWxsfSAtIFRoZSBtZWRpYSBxdWVyeSBvZiB0aGUgYnJlYWtwb2ludCwgb3IgYG51bGxgIGlmIHRoZSBicmVha3BvaW50IGRvZXNuJ3QgZXhpc3QuXG4gICAqL1xuICBnZXQoc2l6ZSkge1xuICAgIGZvciAodmFyIGkgaW4gdGhpcy5xdWVyaWVzKSB7XG4gICAgICBpZih0aGlzLnF1ZXJpZXMuaGFzT3duUHJvcGVydHkoaSkpIHtcbiAgICAgICAgdmFyIHF1ZXJ5ID0gdGhpcy5xdWVyaWVzW2ldO1xuICAgICAgICBpZiAoc2l6ZSA9PT0gcXVlcnkubmFtZSkgcmV0dXJuIHF1ZXJ5LnZhbHVlO1xuICAgICAgfVxuICAgIH1cblxuICAgIHJldHVybiBudWxsO1xuICB9LFxuXG4gIC8qKlxuICAgKiBHZXRzIHRoZSBjdXJyZW50IGJyZWFrcG9pbnQgbmFtZSBieSB0ZXN0aW5nIGV2ZXJ5IGJyZWFrcG9pbnQgYW5kIHJldHVybmluZyB0aGUgbGFzdCBvbmUgdG8gbWF0Y2ggKHRoZSBiaWdnZXN0IG9uZSkuXG4gICAqIEBmdW5jdGlvblxuICAgKiBAcHJpdmF0ZVxuICAgKiBAcmV0dXJucyB7U3RyaW5nfSBOYW1lIG9mIHRoZSBjdXJyZW50IGJyZWFrcG9pbnQuXG4gICAqL1xuICBfZ2V0Q3VycmVudFNpemUoKSB7XG4gICAgdmFyIG1hdGNoZWQ7XG5cbiAgICBmb3IgKHZhciBpID0gMDsgaSA8IHRoaXMucXVlcmllcy5sZW5ndGg7IGkrKykge1xuICAgICAgdmFyIHF1ZXJ5ID0gdGhpcy5xdWVyaWVzW2ldO1xuXG4gICAgICBpZiAod2luZG93Lm1hdGNoTWVkaWEocXVlcnkudmFsdWUpLm1hdGNoZXMpIHtcbiAgICAgICAgbWF0Y2hlZCA9IHF1ZXJ5O1xuICAgICAgfVxuICAgIH1cblxuICAgIGlmICh0eXBlb2YgbWF0Y2hlZCA9PT0gJ29iamVjdCcpIHtcbiAgICAgIHJldHVybiBtYXRjaGVkLm5hbWU7XG4gICAgfSBlbHNlIHtcbiAgICAgIHJldHVybiBtYXRjaGVkO1xuICAgIH1cbiAgfSxcblxuICAvKipcbiAgICogQWN0aXZhdGVzIHRoZSBicmVha3BvaW50IHdhdGNoZXIsIHdoaWNoIGZpcmVzIGFuIGV2ZW50IG9uIHRoZSB3aW5kb3cgd2hlbmV2ZXIgdGhlIGJyZWFrcG9pbnQgY2hhbmdlcy5cbiAgICogQGZ1bmN0aW9uXG4gICAqIEBwcml2YXRlXG4gICAqL1xuICBfd2F0Y2hlcigpIHtcbiAgICAkKHdpbmRvdykub2ZmKCdyZXNpemUuemYubWVkaWFxdWVyeScpLm9uKCdyZXNpemUuemYubWVkaWFxdWVyeScsICgpID0+IHtcbiAgICAgIHZhciBuZXdTaXplID0gdGhpcy5fZ2V0Q3VycmVudFNpemUoKSwgY3VycmVudFNpemUgPSB0aGlzLmN1cnJlbnQ7XG5cbiAgICAgIGlmIChuZXdTaXplICE9PSBjdXJyZW50U2l6ZSkge1xuICAgICAgICAvLyBDaGFuZ2UgdGhlIGN1cnJlbnQgbWVkaWEgcXVlcnlcbiAgICAgICAgdGhpcy5jdXJyZW50ID0gbmV3U2l6ZTtcblxuICAgICAgICAvLyBCcm9hZGNhc3QgdGhlIG1lZGlhIHF1ZXJ5IGNoYW5nZSBvbiB0aGUgd2luZG93XG4gICAgICAgICQod2luZG93KS50cmlnZ2VyKCdjaGFuZ2VkLnpmLm1lZGlhcXVlcnknLCBbbmV3U2l6ZSwgY3VycmVudFNpemVdKTtcbiAgICAgIH1cbiAgICB9KTtcbiAgfVxufTtcblxuXG5cbi8vIFRoYW5rIHlvdTogaHR0cHM6Ly9naXRodWIuY29tL3NpbmRyZXNvcmh1cy9xdWVyeS1zdHJpbmdcbmZ1bmN0aW9uIHBhcnNlU3R5bGVUb09iamVjdChzdHIpIHtcbiAgdmFyIHN0eWxlT2JqZWN0ID0ge307XG5cbiAgaWYgKHR5cGVvZiBzdHIgIT09ICdzdHJpbmcnKSB7XG4gICAgcmV0dXJuIHN0eWxlT2JqZWN0O1xuICB9XG5cbiAgc3RyID0gc3RyLnRyaW0oKS5zbGljZSgxLCAtMSk7IC8vIGJyb3dzZXJzIHJlLXF1b3RlIHN0cmluZyBzdHlsZSB2YWx1ZXNcblxuICBpZiAoIXN0cikge1xuICAgIHJldHVybiBzdHlsZU9iamVjdDtcbiAgfVxuXG4gIHN0eWxlT2JqZWN0ID0gc3RyLnNwbGl0KCcmJykucmVkdWNlKGZ1bmN0aW9uKHJldCwgcGFyYW0pIHtcbiAgICB2YXIgcGFydHMgPSBwYXJhbS5yZXBsYWNlKC9cXCsvZywgJyAnKS5zcGxpdCgnPScpO1xuICAgIHZhciBrZXkgPSBwYXJ0c1swXTtcbiAgICB2YXIgdmFsID0gcGFydHNbMV07XG4gICAga2V5ID0gZGVjb2RlVVJJQ29tcG9uZW50KGtleSk7XG5cbiAgICAvLyBtaXNzaW5nIGA9YCBzaG91bGQgYmUgYG51bGxgOlxuICAgIC8vIGh0dHA6Ly93My5vcmcvVFIvMjAxMi9XRC11cmwtMjAxMjA1MjQvI2NvbGxlY3QtdXJsLXBhcmFtZXRlcnNcbiAgICB2YWwgPSB0eXBlb2YgdmFsID09PSAndW5kZWZpbmVkJyA/IG51bGwgOiBkZWNvZGVVUklDb21wb25lbnQodmFsKTtcblxuICAgIGlmICghcmV0Lmhhc093blByb3BlcnR5KGtleSkpIHtcbiAgICAgIHJldFtrZXldID0gdmFsO1xuICAgIH0gZWxzZSBpZiAoQXJyYXkuaXNBcnJheShyZXRba2V5XSkpIHtcbiAgICAgIHJldFtrZXldLnB1c2godmFsKTtcbiAgICB9IGVsc2Uge1xuICAgICAgcmV0W2tleV0gPSBbcmV0W2tleV0sIHZhbF07XG4gICAgfVxuICAgIHJldHVybiByZXQ7XG4gIH0sIHt9KTtcblxuICByZXR1cm4gc3R5bGVPYmplY3Q7XG59XG5cbmV4cG9ydCB7TWVkaWFRdWVyeX07XG5cblxuXG4vLyBXRUJQQUNLIEZPT1RFUiAvL1xuLy8gLi9ub2RlX21vZHVsZXMvZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLnV0aWwubWVkaWFRdWVyeS5qcyIsIi8qKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqXG4gKiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgKlxuICogVGhpcyB1dGlsIHdhcyBjcmVhdGVkIGJ5IE1hcml1cyBPbGJlcnR6ICpcbiAqIFBsZWFzZSB0aGFuayBNYXJpdXMgb24gR2l0SHViIC9vd2xiZXJ0eiAqXG4gKiBvciB0aGUgd2ViIGh0dHA6Ly93d3cubWFyaXVzb2xiZXJ0ei5kZS8gKlxuICogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICpcbiAqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKiovXG5cbid1c2Ugc3RyaWN0JztcblxuaW1wb3J0ICQgZnJvbSAnanF1ZXJ5JztcbmltcG9ydCB7IHJ0bCBhcyBSdGwgfSBmcm9tICcuL2ZvdW5kYXRpb24uY29yZS51dGlscyc7XG5cbmNvbnN0IGtleUNvZGVzID0ge1xuICA5OiAnVEFCJyxcbiAgMTM6ICdFTlRFUicsXG4gIDI3OiAnRVNDQVBFJyxcbiAgMzI6ICdTUEFDRScsXG4gIDM1OiAnRU5EJyxcbiAgMzY6ICdIT01FJyxcbiAgMzc6ICdBUlJPV19MRUZUJyxcbiAgMzg6ICdBUlJPV19VUCcsXG4gIDM5OiAnQVJST1dfUklHSFQnLFxuICA0MDogJ0FSUk9XX0RPV04nXG59XG5cbnZhciBjb21tYW5kcyA9IHt9XG5cbi8vIEZ1bmN0aW9ucyBwdWxsZWQgb3V0IHRvIGJlIHJlZmVyZW5jZWFibGUgZnJvbSBpbnRlcm5hbHNcbmZ1bmN0aW9uIGZpbmRGb2N1c2FibGUoJGVsZW1lbnQpIHtcbiAgaWYoISRlbGVtZW50KSB7cmV0dXJuIGZhbHNlOyB9XG4gIHJldHVybiAkZWxlbWVudC5maW5kKCdhW2hyZWZdLCBhcmVhW2hyZWZdLCBpbnB1dDpub3QoW2Rpc2FibGVkXSksIHNlbGVjdDpub3QoW2Rpc2FibGVkXSksIHRleHRhcmVhOm5vdChbZGlzYWJsZWRdKSwgYnV0dG9uOm5vdChbZGlzYWJsZWRdKSwgaWZyYW1lLCBvYmplY3QsIGVtYmVkLCAqW3RhYmluZGV4XSwgKltjb250ZW50ZWRpdGFibGVdJykuZmlsdGVyKGZ1bmN0aW9uKCkge1xuICAgIGlmICghJCh0aGlzKS5pcygnOnZpc2libGUnKSB8fCAkKHRoaXMpLmF0dHIoJ3RhYmluZGV4JykgPCAwKSB7IHJldHVybiBmYWxzZTsgfSAvL29ubHkgaGF2ZSB2aXNpYmxlIGVsZW1lbnRzIGFuZCB0aG9zZSB0aGF0IGhhdmUgYSB0YWJpbmRleCBncmVhdGVyIG9yIGVxdWFsIDBcbiAgICByZXR1cm4gdHJ1ZTtcbiAgfSk7XG59XG5cbmZ1bmN0aW9uIHBhcnNlS2V5KGV2ZW50KSB7XG4gIHZhciBrZXkgPSBrZXlDb2Rlc1tldmVudC53aGljaCB8fCBldmVudC5rZXlDb2RlXSB8fCBTdHJpbmcuZnJvbUNoYXJDb2RlKGV2ZW50LndoaWNoKS50b1VwcGVyQ2FzZSgpO1xuXG4gIC8vIFJlbW92ZSB1bi1wcmludGFibGUgY2hhcmFjdGVycywgZS5nLiBmb3IgYGZyb21DaGFyQ29kZWAgY2FsbHMgZm9yIENUUkwgb25seSBldmVudHNcbiAga2V5ID0ga2V5LnJlcGxhY2UoL1xcVysvLCAnJyk7XG5cbiAgaWYgKGV2ZW50LnNoaWZ0S2V5KSBrZXkgPSBgU0hJRlRfJHtrZXl9YDtcbiAgaWYgKGV2ZW50LmN0cmxLZXkpIGtleSA9IGBDVFJMXyR7a2V5fWA7XG4gIGlmIChldmVudC5hbHRLZXkpIGtleSA9IGBBTFRfJHtrZXl9YDtcblxuICAvLyBSZW1vdmUgdHJhaWxpbmcgdW5kZXJzY29yZSwgaW4gY2FzZSBvbmx5IG1vZGlmaWVycyB3ZXJlIHVzZWQgKGUuZy4gb25seSBgQ1RSTF9BTFRgKVxuICBrZXkgPSBrZXkucmVwbGFjZSgvXyQvLCAnJyk7XG5cbiAgcmV0dXJuIGtleTtcbn1cblxudmFyIEtleWJvYXJkID0ge1xuICBrZXlzOiBnZXRLZXlDb2RlcyhrZXlDb2RlcyksXG5cbiAgLyoqXG4gICAqIFBhcnNlcyB0aGUgKGtleWJvYXJkKSBldmVudCBhbmQgcmV0dXJucyBhIFN0cmluZyB0aGF0IHJlcHJlc2VudHMgaXRzIGtleVxuICAgKiBDYW4gYmUgdXNlZCBsaWtlIEZvdW5kYXRpb24ucGFyc2VLZXkoZXZlbnQpID09PSBGb3VuZGF0aW9uLmtleXMuU1BBQ0VcbiAgICogQHBhcmFtIHtFdmVudH0gZXZlbnQgLSB0aGUgZXZlbnQgZ2VuZXJhdGVkIGJ5IHRoZSBldmVudCBoYW5kbGVyXG4gICAqIEByZXR1cm4gU3RyaW5nIGtleSAtIFN0cmluZyB0aGF0IHJlcHJlc2VudHMgdGhlIGtleSBwcmVzc2VkXG4gICAqL1xuICBwYXJzZUtleTogcGFyc2VLZXksXG5cbiAgLyoqXG4gICAqIEhhbmRsZXMgdGhlIGdpdmVuIChrZXlib2FyZCkgZXZlbnRcbiAgICogQHBhcmFtIHtFdmVudH0gZXZlbnQgLSB0aGUgZXZlbnQgZ2VuZXJhdGVkIGJ5IHRoZSBldmVudCBoYW5kbGVyXG4gICAqIEBwYXJhbSB7U3RyaW5nfSBjb21wb25lbnQgLSBGb3VuZGF0aW9uIGNvbXBvbmVudCdzIG5hbWUsIGUuZy4gU2xpZGVyIG9yIFJldmVhbFxuICAgKiBAcGFyYW0ge09iamVjdHN9IGZ1bmN0aW9ucyAtIGNvbGxlY3Rpb24gb2YgZnVuY3Rpb25zIHRoYXQgYXJlIHRvIGJlIGV4ZWN1dGVkXG4gICAqL1xuICBoYW5kbGVLZXkoZXZlbnQsIGNvbXBvbmVudCwgZnVuY3Rpb25zKSB7XG4gICAgdmFyIGNvbW1hbmRMaXN0ID0gY29tbWFuZHNbY29tcG9uZW50XSxcbiAgICAgIGtleUNvZGUgPSB0aGlzLnBhcnNlS2V5KGV2ZW50KSxcbiAgICAgIGNtZHMsXG4gICAgICBjb21tYW5kLFxuICAgICAgZm47XG5cbiAgICBpZiAoIWNvbW1hbmRMaXN0KSByZXR1cm4gY29uc29sZS53YXJuKCdDb21wb25lbnQgbm90IGRlZmluZWQhJyk7XG5cbiAgICBpZiAodHlwZW9mIGNvbW1hbmRMaXN0Lmx0ciA9PT0gJ3VuZGVmaW5lZCcpIHsgLy8gdGhpcyBjb21wb25lbnQgZG9lcyBub3QgZGlmZmVyZW50aWF0ZSBiZXR3ZWVuIGx0ciBhbmQgcnRsXG4gICAgICAgIGNtZHMgPSBjb21tYW5kTGlzdDsgLy8gdXNlIHBsYWluIGxpc3RcbiAgICB9IGVsc2UgeyAvLyBtZXJnZSBsdHIgYW5kIHJ0bDogaWYgZG9jdW1lbnQgaXMgcnRsLCBydGwgb3ZlcndyaXRlcyBsdHIgYW5kIHZpY2UgdmVyc2FcbiAgICAgICAgaWYgKFJ0bCgpKSBjbWRzID0gJC5leHRlbmQoe30sIGNvbW1hbmRMaXN0Lmx0ciwgY29tbWFuZExpc3QucnRsKTtcblxuICAgICAgICBlbHNlIGNtZHMgPSAkLmV4dGVuZCh7fSwgY29tbWFuZExpc3QucnRsLCBjb21tYW5kTGlzdC5sdHIpO1xuICAgIH1cbiAgICBjb21tYW5kID0gY21kc1trZXlDb2RlXTtcblxuICAgIGZuID0gZnVuY3Rpb25zW2NvbW1hbmRdO1xuICAgIGlmIChmbiAmJiB0eXBlb2YgZm4gPT09ICdmdW5jdGlvbicpIHsgLy8gZXhlY3V0ZSBmdW5jdGlvbiAgaWYgZXhpc3RzXG4gICAgICB2YXIgcmV0dXJuVmFsdWUgPSBmbi5hcHBseSgpO1xuICAgICAgaWYgKGZ1bmN0aW9ucy5oYW5kbGVkIHx8IHR5cGVvZiBmdW5jdGlvbnMuaGFuZGxlZCA9PT0gJ2Z1bmN0aW9uJykgeyAvLyBleGVjdXRlIGZ1bmN0aW9uIHdoZW4gZXZlbnQgd2FzIGhhbmRsZWRcbiAgICAgICAgICBmdW5jdGlvbnMuaGFuZGxlZChyZXR1cm5WYWx1ZSk7XG4gICAgICB9XG4gICAgfSBlbHNlIHtcbiAgICAgIGlmIChmdW5jdGlvbnMudW5oYW5kbGVkIHx8IHR5cGVvZiBmdW5jdGlvbnMudW5oYW5kbGVkID09PSAnZnVuY3Rpb24nKSB7IC8vIGV4ZWN1dGUgZnVuY3Rpb24gd2hlbiBldmVudCB3YXMgbm90IGhhbmRsZWRcbiAgICAgICAgICBmdW5jdGlvbnMudW5oYW5kbGVkKCk7XG4gICAgICB9XG4gICAgfVxuICB9LFxuXG4gIC8qKlxuICAgKiBGaW5kcyBhbGwgZm9jdXNhYmxlIGVsZW1lbnRzIHdpdGhpbiB0aGUgZ2l2ZW4gYCRlbGVtZW50YFxuICAgKiBAcGFyYW0ge2pRdWVyeX0gJGVsZW1lbnQgLSBqUXVlcnkgb2JqZWN0IHRvIHNlYXJjaCB3aXRoaW5cbiAgICogQHJldHVybiB7alF1ZXJ5fSAkZm9jdXNhYmxlIC0gYWxsIGZvY3VzYWJsZSBlbGVtZW50cyB3aXRoaW4gYCRlbGVtZW50YFxuICAgKi9cblxuICBmaW5kRm9jdXNhYmxlOiBmaW5kRm9jdXNhYmxlLFxuXG4gIC8qKlxuICAgKiBSZXR1cm5zIHRoZSBjb21wb25lbnQgbmFtZSBuYW1lXG4gICAqIEBwYXJhbSB7T2JqZWN0fSBjb21wb25lbnQgLSBGb3VuZGF0aW9uIGNvbXBvbmVudCwgZS5nLiBTbGlkZXIgb3IgUmV2ZWFsXG4gICAqIEByZXR1cm4gU3RyaW5nIGNvbXBvbmVudE5hbWVcbiAgICovXG5cbiAgcmVnaXN0ZXIoY29tcG9uZW50TmFtZSwgY21kcykge1xuICAgIGNvbW1hbmRzW2NvbXBvbmVudE5hbWVdID0gY21kcztcbiAgfSxcblxuXG4gIC8vIFRPRE85NDM4OiBUaGVzZSByZWZlcmVuY2VzIHRvIEtleWJvYXJkIG5lZWQgdG8gbm90IHJlcXVpcmUgZ2xvYmFsLiBXaWxsICd0aGlzJyB3b3JrIGluIHRoaXMgY29udGV4dD9cbiAgLy9cbiAgLyoqXG4gICAqIFRyYXBzIHRoZSBmb2N1cyBpbiB0aGUgZ2l2ZW4gZWxlbWVudC5cbiAgICogQHBhcmFtICB7alF1ZXJ5fSAkZWxlbWVudCAgalF1ZXJ5IG9iamVjdCB0byB0cmFwIHRoZSBmb3VjcyBpbnRvLlxuICAgKi9cbiAgdHJhcEZvY3VzKCRlbGVtZW50KSB7XG4gICAgdmFyICRmb2N1c2FibGUgPSBmaW5kRm9jdXNhYmxlKCRlbGVtZW50KSxcbiAgICAgICAgJGZpcnN0Rm9jdXNhYmxlID0gJGZvY3VzYWJsZS5lcSgwKSxcbiAgICAgICAgJGxhc3RGb2N1c2FibGUgPSAkZm9jdXNhYmxlLmVxKC0xKTtcblxuICAgICRlbGVtZW50Lm9uKCdrZXlkb3duLnpmLnRyYXBmb2N1cycsIGZ1bmN0aW9uKGV2ZW50KSB7XG4gICAgICBpZiAoZXZlbnQudGFyZ2V0ID09PSAkbGFzdEZvY3VzYWJsZVswXSAmJiBwYXJzZUtleShldmVudCkgPT09ICdUQUInKSB7XG4gICAgICAgIGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgICRmaXJzdEZvY3VzYWJsZS5mb2N1cygpO1xuICAgICAgfVxuICAgICAgZWxzZSBpZiAoZXZlbnQudGFyZ2V0ID09PSAkZmlyc3RGb2N1c2FibGVbMF0gJiYgcGFyc2VLZXkoZXZlbnQpID09PSAnU0hJRlRfVEFCJykge1xuICAgICAgICBldmVudC5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICAkbGFzdEZvY3VzYWJsZS5mb2N1cygpO1xuICAgICAgfVxuICAgIH0pO1xuICB9LFxuICAvKipcbiAgICogUmVsZWFzZXMgdGhlIHRyYXBwZWQgZm9jdXMgZnJvbSB0aGUgZ2l2ZW4gZWxlbWVudC5cbiAgICogQHBhcmFtICB7alF1ZXJ5fSAkZWxlbWVudCAgalF1ZXJ5IG9iamVjdCB0byByZWxlYXNlIHRoZSBmb2N1cyBmb3IuXG4gICAqL1xuICByZWxlYXNlRm9jdXMoJGVsZW1lbnQpIHtcbiAgICAkZWxlbWVudC5vZmYoJ2tleWRvd24uemYudHJhcGZvY3VzJyk7XG4gIH1cbn1cblxuLypcbiAqIENvbnN0YW50cyBmb3IgZWFzaWVyIGNvbXBhcmluZy5cbiAqIENhbiBiZSB1c2VkIGxpa2UgRm91bmRhdGlvbi5wYXJzZUtleShldmVudCkgPT09IEZvdW5kYXRpb24ua2V5cy5TUEFDRVxuICovXG5mdW5jdGlvbiBnZXRLZXlDb2RlcyhrY3MpIHtcbiAgdmFyIGsgPSB7fTtcbiAgZm9yICh2YXIga2MgaW4ga2NzKSBrW2tjc1trY11dID0ga2NzW2tjXTtcbiAgcmV0dXJuIGs7XG59XG5cbmV4cG9ydCB7S2V5Ym9hcmR9O1xuXG5cblxuLy8gV0VCUEFDSyBGT09URVIgLy9cbi8vIC4vbm9kZV9tb2R1bGVzL2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi51dGlsLmtleWJvYXJkLmpzIiwiJ3VzZSBzdHJpY3QnO1xuXG5pbXBvcnQgJCBmcm9tICdqcXVlcnknO1xuaW1wb3J0IHsgb25Mb2FkIH0gZnJvbSAnLi9mb3VuZGF0aW9uLmNvcmUudXRpbHMnO1xuaW1wb3J0IHsgTW90aW9uIH0gZnJvbSAnLi9mb3VuZGF0aW9uLnV0aWwubW90aW9uJztcblxuY29uc3QgTXV0YXRpb25PYnNlcnZlciA9IChmdW5jdGlvbiAoKSB7XG4gIHZhciBwcmVmaXhlcyA9IFsnV2ViS2l0JywgJ01veicsICdPJywgJ01zJywgJyddO1xuICBmb3IgKHZhciBpPTA7IGkgPCBwcmVmaXhlcy5sZW5ndGg7IGkrKykge1xuICAgIGlmIChgJHtwcmVmaXhlc1tpXX1NdXRhdGlvbk9ic2VydmVyYCBpbiB3aW5kb3cpIHtcbiAgICAgIHJldHVybiB3aW5kb3dbYCR7cHJlZml4ZXNbaV19TXV0YXRpb25PYnNlcnZlcmBdO1xuICAgIH1cbiAgfVxuICByZXR1cm4gZmFsc2U7XG59KCkpO1xuXG5jb25zdCB0cmlnZ2VycyA9IChlbCwgdHlwZSkgPT4ge1xuICBlbC5kYXRhKHR5cGUpLnNwbGl0KCcgJykuZm9yRWFjaChpZCA9PiB7XG4gICAgJChgIyR7aWR9YClbIHR5cGUgPT09ICdjbG9zZScgPyAndHJpZ2dlcicgOiAndHJpZ2dlckhhbmRsZXInXShgJHt0eXBlfS56Zi50cmlnZ2VyYCwgW2VsXSk7XG4gIH0pO1xufTtcblxudmFyIFRyaWdnZXJzID0ge1xuICBMaXN0ZW5lcnM6IHtcbiAgICBCYXNpYzoge30sXG4gICAgR2xvYmFsOiB7fVxuICB9LFxuICBJbml0aWFsaXplcnM6IHt9XG59XG5cblRyaWdnZXJzLkxpc3RlbmVycy5CYXNpYyAgPSB7XG4gIG9wZW5MaXN0ZW5lcjogZnVuY3Rpb24oKSB7XG4gICAgdHJpZ2dlcnMoJCh0aGlzKSwgJ29wZW4nKTtcbiAgfSxcbiAgY2xvc2VMaXN0ZW5lcjogZnVuY3Rpb24oKSB7XG4gICAgbGV0IGlkID0gJCh0aGlzKS5kYXRhKCdjbG9zZScpO1xuICAgIGlmIChpZCkge1xuICAgICAgdHJpZ2dlcnMoJCh0aGlzKSwgJ2Nsb3NlJyk7XG4gICAgfVxuICAgIGVsc2Uge1xuICAgICAgJCh0aGlzKS50cmlnZ2VyKCdjbG9zZS56Zi50cmlnZ2VyJyk7XG4gICAgfVxuICB9LFxuICB0b2dnbGVMaXN0ZW5lcjogZnVuY3Rpb24oKSB7XG4gICAgbGV0IGlkID0gJCh0aGlzKS5kYXRhKCd0b2dnbGUnKTtcbiAgICBpZiAoaWQpIHtcbiAgICAgIHRyaWdnZXJzKCQodGhpcyksICd0b2dnbGUnKTtcbiAgICB9IGVsc2Uge1xuICAgICAgJCh0aGlzKS50cmlnZ2VyKCd0b2dnbGUuemYudHJpZ2dlcicpO1xuICAgIH1cbiAgfSxcbiAgY2xvc2VhYmxlTGlzdGVuZXI6IGZ1bmN0aW9uKGUpIHtcbiAgICBlLnN0b3BQcm9wYWdhdGlvbigpO1xuICAgIGxldCBhbmltYXRpb24gPSAkKHRoaXMpLmRhdGEoJ2Nsb3NhYmxlJyk7XG5cbiAgICBpZihhbmltYXRpb24gIT09ICcnKXtcbiAgICAgIE1vdGlvbi5hbmltYXRlT3V0KCQodGhpcyksIGFuaW1hdGlvbiwgZnVuY3Rpb24oKSB7XG4gICAgICAgICQodGhpcykudHJpZ2dlcignY2xvc2VkLnpmJyk7XG4gICAgICB9KTtcbiAgICB9ZWxzZXtcbiAgICAgICQodGhpcykuZmFkZU91dCgpLnRyaWdnZXIoJ2Nsb3NlZC56ZicpO1xuICAgIH1cbiAgfSxcbiAgdG9nZ2xlRm9jdXNMaXN0ZW5lcjogZnVuY3Rpb24oKSB7XG4gICAgbGV0IGlkID0gJCh0aGlzKS5kYXRhKCd0b2dnbGUtZm9jdXMnKTtcbiAgICAkKGAjJHtpZH1gKS50cmlnZ2VySGFuZGxlcigndG9nZ2xlLnpmLnRyaWdnZXInLCBbJCh0aGlzKV0pO1xuICB9XG59O1xuXG4vLyBFbGVtZW50cyB3aXRoIFtkYXRhLW9wZW5dIHdpbGwgcmV2ZWFsIGEgcGx1Z2luIHRoYXQgc3VwcG9ydHMgaXQgd2hlbiBjbGlja2VkLlxuVHJpZ2dlcnMuSW5pdGlhbGl6ZXJzLmFkZE9wZW5MaXN0ZW5lciA9ICgkZWxlbSkgPT4ge1xuICAkZWxlbS5vZmYoJ2NsaWNrLnpmLnRyaWdnZXInLCBUcmlnZ2Vycy5MaXN0ZW5lcnMuQmFzaWMub3Blbkxpc3RlbmVyKTtcbiAgJGVsZW0ub24oJ2NsaWNrLnpmLnRyaWdnZXInLCAnW2RhdGEtb3Blbl0nLCBUcmlnZ2Vycy5MaXN0ZW5lcnMuQmFzaWMub3Blbkxpc3RlbmVyKTtcbn1cblxuLy8gRWxlbWVudHMgd2l0aCBbZGF0YS1jbG9zZV0gd2lsbCBjbG9zZSBhIHBsdWdpbiB0aGF0IHN1cHBvcnRzIGl0IHdoZW4gY2xpY2tlZC5cbi8vIElmIHVzZWQgd2l0aG91dCBhIHZhbHVlIG9uIFtkYXRhLWNsb3NlXSwgdGhlIGV2ZW50IHdpbGwgYnViYmxlLCBhbGxvd2luZyBpdCB0byBjbG9zZSBhIHBhcmVudCBjb21wb25lbnQuXG5UcmlnZ2Vycy5Jbml0aWFsaXplcnMuYWRkQ2xvc2VMaXN0ZW5lciA9ICgkZWxlbSkgPT4ge1xuICAkZWxlbS5vZmYoJ2NsaWNrLnpmLnRyaWdnZXInLCBUcmlnZ2Vycy5MaXN0ZW5lcnMuQmFzaWMuY2xvc2VMaXN0ZW5lcik7XG4gICRlbGVtLm9uKCdjbGljay56Zi50cmlnZ2VyJywgJ1tkYXRhLWNsb3NlXScsIFRyaWdnZXJzLkxpc3RlbmVycy5CYXNpYy5jbG9zZUxpc3RlbmVyKTtcbn1cblxuLy8gRWxlbWVudHMgd2l0aCBbZGF0YS10b2dnbGVdIHdpbGwgdG9nZ2xlIGEgcGx1Z2luIHRoYXQgc3VwcG9ydHMgaXQgd2hlbiBjbGlja2VkLlxuVHJpZ2dlcnMuSW5pdGlhbGl6ZXJzLmFkZFRvZ2dsZUxpc3RlbmVyID0gKCRlbGVtKSA9PiB7XG4gICRlbGVtLm9mZignY2xpY2suemYudHJpZ2dlcicsIFRyaWdnZXJzLkxpc3RlbmVycy5CYXNpYy50b2dnbGVMaXN0ZW5lcik7XG4gICRlbGVtLm9uKCdjbGljay56Zi50cmlnZ2VyJywgJ1tkYXRhLXRvZ2dsZV0nLCBUcmlnZ2Vycy5MaXN0ZW5lcnMuQmFzaWMudG9nZ2xlTGlzdGVuZXIpO1xufVxuXG4vLyBFbGVtZW50cyB3aXRoIFtkYXRhLWNsb3NhYmxlXSB3aWxsIHJlc3BvbmQgdG8gY2xvc2UuemYudHJpZ2dlciBldmVudHMuXG5UcmlnZ2Vycy5Jbml0aWFsaXplcnMuYWRkQ2xvc2VhYmxlTGlzdGVuZXIgPSAoJGVsZW0pID0+IHtcbiAgJGVsZW0ub2ZmKCdjbG9zZS56Zi50cmlnZ2VyJywgVHJpZ2dlcnMuTGlzdGVuZXJzLkJhc2ljLmNsb3NlYWJsZUxpc3RlbmVyKTtcbiAgJGVsZW0ub24oJ2Nsb3NlLnpmLnRyaWdnZXInLCAnW2RhdGEtY2xvc2VhYmxlXSwgW2RhdGEtY2xvc2FibGVdJywgVHJpZ2dlcnMuTGlzdGVuZXJzLkJhc2ljLmNsb3NlYWJsZUxpc3RlbmVyKTtcbn1cblxuLy8gRWxlbWVudHMgd2l0aCBbZGF0YS10b2dnbGUtZm9jdXNdIHdpbGwgcmVzcG9uZCB0byBjb21pbmcgaW4gYW5kIG91dCBvZiBmb2N1c1xuVHJpZ2dlcnMuSW5pdGlhbGl6ZXJzLmFkZFRvZ2dsZUZvY3VzTGlzdGVuZXIgPSAoJGVsZW0pID0+IHtcbiAgJGVsZW0ub2ZmKCdmb2N1cy56Zi50cmlnZ2VyIGJsdXIuemYudHJpZ2dlcicsIFRyaWdnZXJzLkxpc3RlbmVycy5CYXNpYy50b2dnbGVGb2N1c0xpc3RlbmVyKTtcbiAgJGVsZW0ub24oJ2ZvY3VzLnpmLnRyaWdnZXIgYmx1ci56Zi50cmlnZ2VyJywgJ1tkYXRhLXRvZ2dsZS1mb2N1c10nLCBUcmlnZ2Vycy5MaXN0ZW5lcnMuQmFzaWMudG9nZ2xlRm9jdXNMaXN0ZW5lcik7XG59XG5cblxuXG4vLyBNb3JlIEdsb2JhbC9jb21wbGV4IGxpc3RlbmVycyBhbmQgdHJpZ2dlcnNcblRyaWdnZXJzLkxpc3RlbmVycy5HbG9iYWwgID0ge1xuICByZXNpemVMaXN0ZW5lcjogZnVuY3Rpb24oJG5vZGVzKSB7XG4gICAgaWYoIU11dGF0aW9uT2JzZXJ2ZXIpey8vZmFsbGJhY2sgZm9yIElFIDlcbiAgICAgICRub2Rlcy5lYWNoKGZ1bmN0aW9uKCl7XG4gICAgICAgICQodGhpcykudHJpZ2dlckhhbmRsZXIoJ3Jlc2l6ZW1lLnpmLnRyaWdnZXInKTtcbiAgICAgIH0pO1xuICAgIH1cbiAgICAvL3RyaWdnZXIgYWxsIGxpc3RlbmluZyBlbGVtZW50cyBhbmQgc2lnbmFsIGEgcmVzaXplIGV2ZW50XG4gICAgJG5vZGVzLmF0dHIoJ2RhdGEtZXZlbnRzJywgXCJyZXNpemVcIik7XG4gIH0sXG4gIHNjcm9sbExpc3RlbmVyOiBmdW5jdGlvbigkbm9kZXMpIHtcbiAgICBpZighTXV0YXRpb25PYnNlcnZlcil7Ly9mYWxsYmFjayBmb3IgSUUgOVxuICAgICAgJG5vZGVzLmVhY2goZnVuY3Rpb24oKXtcbiAgICAgICAgJCh0aGlzKS50cmlnZ2VySGFuZGxlcignc2Nyb2xsbWUuemYudHJpZ2dlcicpO1xuICAgICAgfSk7XG4gICAgfVxuICAgIC8vdHJpZ2dlciBhbGwgbGlzdGVuaW5nIGVsZW1lbnRzIGFuZCBzaWduYWwgYSBzY3JvbGwgZXZlbnRcbiAgICAkbm9kZXMuYXR0cignZGF0YS1ldmVudHMnLCBcInNjcm9sbFwiKTtcbiAgfSxcbiAgY2xvc2VNZUxpc3RlbmVyOiBmdW5jdGlvbihlLCBwbHVnaW5JZCl7XG4gICAgbGV0IHBsdWdpbiA9IGUubmFtZXNwYWNlLnNwbGl0KCcuJylbMF07XG4gICAgbGV0IHBsdWdpbnMgPSAkKGBbZGF0YS0ke3BsdWdpbn1dYCkubm90KGBbZGF0YS15ZXRpLWJveD1cIiR7cGx1Z2luSWR9XCJdYCk7XG5cbiAgICBwbHVnaW5zLmVhY2goZnVuY3Rpb24oKXtcbiAgICAgIGxldCBfdGhpcyA9ICQodGhpcyk7XG4gICAgICBfdGhpcy50cmlnZ2VySGFuZGxlcignY2xvc2UuemYudHJpZ2dlcicsIFtfdGhpc10pO1xuICAgIH0pO1xuICB9XG59XG5cbi8vIEdsb2JhbCwgcGFyc2VzIHdob2xlIGRvY3VtZW50LlxuVHJpZ2dlcnMuSW5pdGlhbGl6ZXJzLmFkZENsb3NlbWVMaXN0ZW5lciA9IGZ1bmN0aW9uKHBsdWdpbk5hbWUpIHtcbiAgdmFyIHlldGlCb3hlcyA9ICQoJ1tkYXRhLXlldGktYm94XScpLFxuICAgICAgcGx1Z05hbWVzID0gWydkcm9wZG93bicsICd0b29sdGlwJywgJ3JldmVhbCddO1xuXG4gIGlmKHBsdWdpbk5hbWUpe1xuICAgIGlmKHR5cGVvZiBwbHVnaW5OYW1lID09PSAnc3RyaW5nJyl7XG4gICAgICBwbHVnTmFtZXMucHVzaChwbHVnaW5OYW1lKTtcbiAgICB9ZWxzZSBpZih0eXBlb2YgcGx1Z2luTmFtZSA9PT0gJ29iamVjdCcgJiYgdHlwZW9mIHBsdWdpbk5hbWVbMF0gPT09ICdzdHJpbmcnKXtcbiAgICAgIHBsdWdOYW1lcy5jb25jYXQocGx1Z2luTmFtZSk7XG4gICAgfWVsc2V7XG4gICAgICBjb25zb2xlLmVycm9yKCdQbHVnaW4gbmFtZXMgbXVzdCBiZSBzdHJpbmdzJyk7XG4gICAgfVxuICB9XG4gIGlmKHlldGlCb3hlcy5sZW5ndGgpe1xuICAgIGxldCBsaXN0ZW5lcnMgPSBwbHVnTmFtZXMubWFwKChuYW1lKSA9PiB7XG4gICAgICByZXR1cm4gYGNsb3NlbWUuemYuJHtuYW1lfWA7XG4gICAgfSkuam9pbignICcpO1xuXG4gICAgJCh3aW5kb3cpLm9mZihsaXN0ZW5lcnMpLm9uKGxpc3RlbmVycywgVHJpZ2dlcnMuTGlzdGVuZXJzLkdsb2JhbC5jbG9zZU1lTGlzdGVuZXIpO1xuICB9XG59XG5cbmZ1bmN0aW9uIGRlYm91bmNlR2xvYmFsTGlzdGVuZXIoZGVib3VuY2UsIHRyaWdnZXIsIGxpc3RlbmVyKSB7XG4gIGxldCB0aW1lciwgYXJncyA9IEFycmF5LnByb3RvdHlwZS5zbGljZS5jYWxsKGFyZ3VtZW50cywgMyk7XG4gICQod2luZG93KS5vZmYodHJpZ2dlcikub24odHJpZ2dlciwgZnVuY3Rpb24oZSkge1xuICAgIGlmICh0aW1lcikgeyBjbGVhclRpbWVvdXQodGltZXIpOyB9XG4gICAgdGltZXIgPSBzZXRUaW1lb3V0KGZ1bmN0aW9uKCl7XG4gICAgICBsaXN0ZW5lci5hcHBseShudWxsLCBhcmdzKTtcbiAgICB9LCBkZWJvdW5jZSB8fCAxMCk7Ly9kZWZhdWx0IHRpbWUgdG8gZW1pdCBzY3JvbGwgZXZlbnRcbiAgfSk7XG59XG5cblRyaWdnZXJzLkluaXRpYWxpemVycy5hZGRSZXNpemVMaXN0ZW5lciA9IGZ1bmN0aW9uKGRlYm91bmNlKXtcbiAgbGV0ICRub2RlcyA9ICQoJ1tkYXRhLXJlc2l6ZV0nKTtcbiAgaWYoJG5vZGVzLmxlbmd0aCl7XG4gICAgZGVib3VuY2VHbG9iYWxMaXN0ZW5lcihkZWJvdW5jZSwgJ3Jlc2l6ZS56Zi50cmlnZ2VyJywgVHJpZ2dlcnMuTGlzdGVuZXJzLkdsb2JhbC5yZXNpemVMaXN0ZW5lciwgJG5vZGVzKTtcbiAgfVxufVxuXG5UcmlnZ2Vycy5Jbml0aWFsaXplcnMuYWRkU2Nyb2xsTGlzdGVuZXIgPSBmdW5jdGlvbihkZWJvdW5jZSl7XG4gIGxldCAkbm9kZXMgPSAkKCdbZGF0YS1zY3JvbGxdJyk7XG4gIGlmKCRub2Rlcy5sZW5ndGgpe1xuICAgIGRlYm91bmNlR2xvYmFsTGlzdGVuZXIoZGVib3VuY2UsICdzY3JvbGwuemYudHJpZ2dlcicsIFRyaWdnZXJzLkxpc3RlbmVycy5HbG9iYWwuc2Nyb2xsTGlzdGVuZXIsICRub2Rlcyk7XG4gIH1cbn1cblxuVHJpZ2dlcnMuSW5pdGlhbGl6ZXJzLmFkZE11dGF0aW9uRXZlbnRzTGlzdGVuZXIgPSBmdW5jdGlvbigkZWxlbSkge1xuICBpZighTXV0YXRpb25PYnNlcnZlcil7IHJldHVybiBmYWxzZTsgfVxuICBsZXQgJG5vZGVzID0gJGVsZW0uZmluZCgnW2RhdGEtcmVzaXplXSwgW2RhdGEtc2Nyb2xsXSwgW2RhdGEtbXV0YXRlXScpO1xuXG4gIC8vZWxlbWVudCBjYWxsYmFja1xuICB2YXIgbGlzdGVuaW5nRWxlbWVudHNNdXRhdGlvbiA9IGZ1bmN0aW9uIChtdXRhdGlvblJlY29yZHNMaXN0KSB7XG4gICAgdmFyICR0YXJnZXQgPSAkKG11dGF0aW9uUmVjb3Jkc0xpc3RbMF0udGFyZ2V0KTtcblxuICAgIC8vdHJpZ2dlciB0aGUgZXZlbnQgaGFuZGxlciBmb3IgdGhlIGVsZW1lbnQgZGVwZW5kaW5nIG9uIHR5cGVcbiAgICBzd2l0Y2ggKG11dGF0aW9uUmVjb3Jkc0xpc3RbMF0udHlwZSkge1xuICAgICAgY2FzZSBcImF0dHJpYnV0ZXNcIjpcbiAgICAgICAgaWYgKCR0YXJnZXQuYXR0cihcImRhdGEtZXZlbnRzXCIpID09PSBcInNjcm9sbFwiICYmIG11dGF0aW9uUmVjb3Jkc0xpc3RbMF0uYXR0cmlidXRlTmFtZSA9PT0gXCJkYXRhLWV2ZW50c1wiKSB7XG4gICAgICAgICAgJHRhcmdldC50cmlnZ2VySGFuZGxlcignc2Nyb2xsbWUuemYudHJpZ2dlcicsIFskdGFyZ2V0LCB3aW5kb3cucGFnZVlPZmZzZXRdKTtcbiAgICAgICAgfVxuICAgICAgICBpZiAoJHRhcmdldC5hdHRyKFwiZGF0YS1ldmVudHNcIikgPT09IFwicmVzaXplXCIgJiYgbXV0YXRpb25SZWNvcmRzTGlzdFswXS5hdHRyaWJ1dGVOYW1lID09PSBcImRhdGEtZXZlbnRzXCIpIHtcbiAgICAgICAgICAkdGFyZ2V0LnRyaWdnZXJIYW5kbGVyKCdyZXNpemVtZS56Zi50cmlnZ2VyJywgWyR0YXJnZXRdKTtcbiAgICAgICAgIH1cbiAgICAgICAgaWYgKG11dGF0aW9uUmVjb3Jkc0xpc3RbMF0uYXR0cmlidXRlTmFtZSA9PT0gXCJzdHlsZVwiKSB7XG4gICAgICAgICAgJHRhcmdldC5jbG9zZXN0KFwiW2RhdGEtbXV0YXRlXVwiKS5hdHRyKFwiZGF0YS1ldmVudHNcIixcIm11dGF0ZVwiKTtcbiAgICAgICAgICAkdGFyZ2V0LmNsb3Nlc3QoXCJbZGF0YS1tdXRhdGVdXCIpLnRyaWdnZXJIYW5kbGVyKCdtdXRhdGVtZS56Zi50cmlnZ2VyJywgWyR0YXJnZXQuY2xvc2VzdChcIltkYXRhLW11dGF0ZV1cIildKTtcbiAgICAgICAgfVxuICAgICAgICBicmVhaztcblxuICAgICAgY2FzZSBcImNoaWxkTGlzdFwiOlxuICAgICAgICAkdGFyZ2V0LmNsb3Nlc3QoXCJbZGF0YS1tdXRhdGVdXCIpLmF0dHIoXCJkYXRhLWV2ZW50c1wiLFwibXV0YXRlXCIpO1xuICAgICAgICAkdGFyZ2V0LmNsb3Nlc3QoXCJbZGF0YS1tdXRhdGVdXCIpLnRyaWdnZXJIYW5kbGVyKCdtdXRhdGVtZS56Zi50cmlnZ2VyJywgWyR0YXJnZXQuY2xvc2VzdChcIltkYXRhLW11dGF0ZV1cIildKTtcbiAgICAgICAgYnJlYWs7XG5cbiAgICAgIGRlZmF1bHQ6XG4gICAgICAgIHJldHVybiBmYWxzZTtcbiAgICAgIC8vbm90aGluZ1xuICAgIH1cbiAgfTtcblxuICBpZiAoJG5vZGVzLmxlbmd0aCkge1xuICAgIC8vZm9yIGVhY2ggZWxlbWVudCB0aGF0IG5lZWRzIHRvIGxpc3RlbiBmb3IgcmVzaXppbmcsIHNjcm9sbGluZywgb3IgbXV0YXRpb24gYWRkIGEgc2luZ2xlIG9ic2VydmVyXG4gICAgZm9yICh2YXIgaSA9IDA7IGkgPD0gJG5vZGVzLmxlbmd0aCAtIDE7IGkrKykge1xuICAgICAgdmFyIGVsZW1lbnRPYnNlcnZlciA9IG5ldyBNdXRhdGlvbk9ic2VydmVyKGxpc3RlbmluZ0VsZW1lbnRzTXV0YXRpb24pO1xuICAgICAgZWxlbWVudE9ic2VydmVyLm9ic2VydmUoJG5vZGVzW2ldLCB7IGF0dHJpYnV0ZXM6IHRydWUsIGNoaWxkTGlzdDogdHJ1ZSwgY2hhcmFjdGVyRGF0YTogZmFsc2UsIHN1YnRyZWU6IHRydWUsIGF0dHJpYnV0ZUZpbHRlcjogW1wiZGF0YS1ldmVudHNcIiwgXCJzdHlsZVwiXSB9KTtcbiAgICB9XG4gIH1cbn1cblxuVHJpZ2dlcnMuSW5pdGlhbGl6ZXJzLmFkZFNpbXBsZUxpc3RlbmVycyA9IGZ1bmN0aW9uKCkge1xuICBsZXQgJGRvY3VtZW50ID0gJChkb2N1bWVudCk7XG5cbiAgVHJpZ2dlcnMuSW5pdGlhbGl6ZXJzLmFkZE9wZW5MaXN0ZW5lcigkZG9jdW1lbnQpO1xuICBUcmlnZ2Vycy5Jbml0aWFsaXplcnMuYWRkQ2xvc2VMaXN0ZW5lcigkZG9jdW1lbnQpO1xuICBUcmlnZ2Vycy5Jbml0aWFsaXplcnMuYWRkVG9nZ2xlTGlzdGVuZXIoJGRvY3VtZW50KTtcbiAgVHJpZ2dlcnMuSW5pdGlhbGl6ZXJzLmFkZENsb3NlYWJsZUxpc3RlbmVyKCRkb2N1bWVudCk7XG4gIFRyaWdnZXJzLkluaXRpYWxpemVycy5hZGRUb2dnbGVGb2N1c0xpc3RlbmVyKCRkb2N1bWVudCk7XG5cbn1cblxuVHJpZ2dlcnMuSW5pdGlhbGl6ZXJzLmFkZEdsb2JhbExpc3RlbmVycyA9IGZ1bmN0aW9uKCkge1xuICBsZXQgJGRvY3VtZW50ID0gJChkb2N1bWVudCk7XG4gIFRyaWdnZXJzLkluaXRpYWxpemVycy5hZGRNdXRhdGlvbkV2ZW50c0xpc3RlbmVyKCRkb2N1bWVudCk7XG4gIFRyaWdnZXJzLkluaXRpYWxpemVycy5hZGRSZXNpemVMaXN0ZW5lcigpO1xuICBUcmlnZ2Vycy5Jbml0aWFsaXplcnMuYWRkU2Nyb2xsTGlzdGVuZXIoKTtcbiAgVHJpZ2dlcnMuSW5pdGlhbGl6ZXJzLmFkZENsb3NlbWVMaXN0ZW5lcigpO1xufVxuXG5cblRyaWdnZXJzLmluaXQgPSBmdW5jdGlvbiAoJCwgRm91bmRhdGlvbikge1xuICBvbkxvYWQoJCh3aW5kb3cpLCBmdW5jdGlvbiAoKSB7XG4gICAgaWYgKCQudHJpZ2dlcnNJbml0aWFsaXplZCAhPT0gdHJ1ZSkge1xuICAgICAgVHJpZ2dlcnMuSW5pdGlhbGl6ZXJzLmFkZFNpbXBsZUxpc3RlbmVycygpO1xuICAgICAgVHJpZ2dlcnMuSW5pdGlhbGl6ZXJzLmFkZEdsb2JhbExpc3RlbmVycygpO1xuICAgICAgJC50cmlnZ2Vyc0luaXRpYWxpemVkID0gdHJ1ZTtcbiAgICB9XG4gIH0pO1xuXG4gIGlmKEZvdW5kYXRpb24pIHtcbiAgICBGb3VuZGF0aW9uLlRyaWdnZXJzID0gVHJpZ2dlcnM7XG4gICAgLy8gTGVnYWN5IGluY2x1ZGVkIHRvIGJlIGJhY2t3YXJkcyBjb21wYXRpYmxlIGZvciBub3cuXG4gICAgRm91bmRhdGlvbi5JSGVhcllvdSA9IFRyaWdnZXJzLkluaXRpYWxpemVycy5hZGRHbG9iYWxMaXN0ZW5lcnNcbiAgfVxufVxuXG5leHBvcnQge1RyaWdnZXJzfTtcblxuXG5cbi8vIFdFQlBBQ0sgRk9PVEVSIC8vXG4vLyAuL25vZGVfbW9kdWxlcy9mb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24udXRpbC50cmlnZ2Vycy5qcyIsIid1c2Ugc3RyaWN0JztcblxuaW1wb3J0ICQgZnJvbSAnanF1ZXJ5JztcbmltcG9ydCB7IHRyYW5zaXRpb25lbmQgfSBmcm9tICcuL2ZvdW5kYXRpb24uY29yZS51dGlscyc7XG5cbi8qKlxuICogTW90aW9uIG1vZHVsZS5cbiAqIEBtb2R1bGUgZm91bmRhdGlvbi5tb3Rpb25cbiAqL1xuXG5jb25zdCBpbml0Q2xhc3NlcyAgID0gWydtdWktZW50ZXInLCAnbXVpLWxlYXZlJ107XG5jb25zdCBhY3RpdmVDbGFzc2VzID0gWydtdWktZW50ZXItYWN0aXZlJywgJ211aS1sZWF2ZS1hY3RpdmUnXTtcblxuY29uc3QgTW90aW9uID0ge1xuICBhbmltYXRlSW46IGZ1bmN0aW9uKGVsZW1lbnQsIGFuaW1hdGlvbiwgY2IpIHtcbiAgICBhbmltYXRlKHRydWUsIGVsZW1lbnQsIGFuaW1hdGlvbiwgY2IpO1xuICB9LFxuXG4gIGFuaW1hdGVPdXQ6IGZ1bmN0aW9uKGVsZW1lbnQsIGFuaW1hdGlvbiwgY2IpIHtcbiAgICBhbmltYXRlKGZhbHNlLCBlbGVtZW50LCBhbmltYXRpb24sIGNiKTtcbiAgfVxufVxuXG5mdW5jdGlvbiBNb3ZlKGR1cmF0aW9uLCBlbGVtLCBmbil7XG4gIHZhciBhbmltLCBwcm9nLCBzdGFydCA9IG51bGw7XG4gIC8vIGNvbnNvbGUubG9nKCdjYWxsZWQnKTtcblxuICBpZiAoZHVyYXRpb24gPT09IDApIHtcbiAgICBmbi5hcHBseShlbGVtKTtcbiAgICBlbGVtLnRyaWdnZXIoJ2ZpbmlzaGVkLnpmLmFuaW1hdGUnLCBbZWxlbV0pLnRyaWdnZXJIYW5kbGVyKCdmaW5pc2hlZC56Zi5hbmltYXRlJywgW2VsZW1dKTtcbiAgICByZXR1cm47XG4gIH1cblxuICBmdW5jdGlvbiBtb3ZlKHRzKXtcbiAgICBpZighc3RhcnQpIHN0YXJ0ID0gdHM7XG4gICAgLy8gY29uc29sZS5sb2coc3RhcnQsIHRzKTtcbiAgICBwcm9nID0gdHMgLSBzdGFydDtcbiAgICBmbi5hcHBseShlbGVtKTtcblxuICAgIGlmKHByb2cgPCBkdXJhdGlvbil7IGFuaW0gPSB3aW5kb3cucmVxdWVzdEFuaW1hdGlvbkZyYW1lKG1vdmUsIGVsZW0pOyB9XG4gICAgZWxzZXtcbiAgICAgIHdpbmRvdy5jYW5jZWxBbmltYXRpb25GcmFtZShhbmltKTtcbiAgICAgIGVsZW0udHJpZ2dlcignZmluaXNoZWQuemYuYW5pbWF0ZScsIFtlbGVtXSkudHJpZ2dlckhhbmRsZXIoJ2ZpbmlzaGVkLnpmLmFuaW1hdGUnLCBbZWxlbV0pO1xuICAgIH1cbiAgfVxuICBhbmltID0gd2luZG93LnJlcXVlc3RBbmltYXRpb25GcmFtZShtb3ZlKTtcbn1cblxuLyoqXG4gKiBBbmltYXRlcyBhbiBlbGVtZW50IGluIG9yIG91dCB1c2luZyBhIENTUyB0cmFuc2l0aW9uIGNsYXNzLlxuICogQGZ1bmN0aW9uXG4gKiBAcHJpdmF0ZVxuICogQHBhcmFtIHtCb29sZWFufSBpc0luIC0gRGVmaW5lcyBpZiB0aGUgYW5pbWF0aW9uIGlzIGluIG9yIG91dC5cbiAqIEBwYXJhbSB7T2JqZWN0fSBlbGVtZW50IC0galF1ZXJ5IG9yIEhUTUwgb2JqZWN0IHRvIGFuaW1hdGUuXG4gKiBAcGFyYW0ge1N0cmluZ30gYW5pbWF0aW9uIC0gQ1NTIGNsYXNzIHRvIHVzZS5cbiAqIEBwYXJhbSB7RnVuY3Rpb259IGNiIC0gQ2FsbGJhY2sgdG8gcnVuIHdoZW4gYW5pbWF0aW9uIGlzIGZpbmlzaGVkLlxuICovXG5mdW5jdGlvbiBhbmltYXRlKGlzSW4sIGVsZW1lbnQsIGFuaW1hdGlvbiwgY2IpIHtcbiAgZWxlbWVudCA9ICQoZWxlbWVudCkuZXEoMCk7XG5cbiAgaWYgKCFlbGVtZW50Lmxlbmd0aCkgcmV0dXJuO1xuXG4gIHZhciBpbml0Q2xhc3MgPSBpc0luID8gaW5pdENsYXNzZXNbMF0gOiBpbml0Q2xhc3Nlc1sxXTtcbiAgdmFyIGFjdGl2ZUNsYXNzID0gaXNJbiA/IGFjdGl2ZUNsYXNzZXNbMF0gOiBhY3RpdmVDbGFzc2VzWzFdO1xuXG4gIC8vIFNldCB1cCB0aGUgYW5pbWF0aW9uXG4gIHJlc2V0KCk7XG5cbiAgZWxlbWVudFxuICAgIC5hZGRDbGFzcyhhbmltYXRpb24pXG4gICAgLmNzcygndHJhbnNpdGlvbicsICdub25lJyk7XG5cbiAgcmVxdWVzdEFuaW1hdGlvbkZyYW1lKCgpID0+IHtcbiAgICBlbGVtZW50LmFkZENsYXNzKGluaXRDbGFzcyk7XG4gICAgaWYgKGlzSW4pIGVsZW1lbnQuc2hvdygpO1xuICB9KTtcblxuICAvLyBTdGFydCB0aGUgYW5pbWF0aW9uXG4gIHJlcXVlc3RBbmltYXRpb25GcmFtZSgoKSA9PiB7XG4gICAgZWxlbWVudFswXS5vZmZzZXRXaWR0aDtcbiAgICBlbGVtZW50XG4gICAgICAuY3NzKCd0cmFuc2l0aW9uJywgJycpXG4gICAgICAuYWRkQ2xhc3MoYWN0aXZlQ2xhc3MpO1xuICB9KTtcblxuICAvLyBDbGVhbiB1cCB0aGUgYW5pbWF0aW9uIHdoZW4gaXQgZmluaXNoZXNcbiAgZWxlbWVudC5vbmUodHJhbnNpdGlvbmVuZChlbGVtZW50KSwgZmluaXNoKTtcblxuICAvLyBIaWRlcyB0aGUgZWxlbWVudCAoZm9yIG91dCBhbmltYXRpb25zKSwgcmVzZXRzIHRoZSBlbGVtZW50LCBhbmQgcnVucyBhIGNhbGxiYWNrXG4gIGZ1bmN0aW9uIGZpbmlzaCgpIHtcbiAgICBpZiAoIWlzSW4pIGVsZW1lbnQuaGlkZSgpO1xuICAgIHJlc2V0KCk7XG4gICAgaWYgKGNiKSBjYi5hcHBseShlbGVtZW50KTtcbiAgfVxuXG4gIC8vIFJlc2V0cyB0cmFuc2l0aW9ucyBhbmQgcmVtb3ZlcyBtb3Rpb24tc3BlY2lmaWMgY2xhc3Nlc1xuICBmdW5jdGlvbiByZXNldCgpIHtcbiAgICBlbGVtZW50WzBdLnN0eWxlLnRyYW5zaXRpb25EdXJhdGlvbiA9IDA7XG4gICAgZWxlbWVudC5yZW1vdmVDbGFzcyhgJHtpbml0Q2xhc3N9ICR7YWN0aXZlQ2xhc3N9ICR7YW5pbWF0aW9ufWApO1xuICB9XG59XG5cbmV4cG9ydCB7IE1vdmUsIE1vdGlvbiB9O1xuXG5cblxuXG4vLyBXRUJQQUNLIEZPT1RFUiAvL1xuLy8gLi9ub2RlX21vZHVsZXMvZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLnV0aWwubW90aW9uLmpzIiwiJ3VzZSBzdHJpY3QnO1xuXG5cbmltcG9ydCB7IHJ0bCBhcyBSdGwgfSBmcm9tIFwiLi9mb3VuZGF0aW9uLmNvcmUudXRpbHNcIjtcblxudmFyIEJveCA9IHtcbiAgSW1Ob3RUb3VjaGluZ1lvdTogSW1Ob3RUb3VjaGluZ1lvdSxcbiAgT3ZlcmxhcEFyZWE6IE92ZXJsYXBBcmVhLFxuICBHZXREaW1lbnNpb25zOiBHZXREaW1lbnNpb25zLFxuICBHZXRPZmZzZXRzOiBHZXRPZmZzZXRzLFxuICBHZXRFeHBsaWNpdE9mZnNldHM6IEdldEV4cGxpY2l0T2Zmc2V0c1xufVxuXG4vKipcbiAqIENvbXBhcmVzIHRoZSBkaW1lbnNpb25zIG9mIGFuIGVsZW1lbnQgdG8gYSBjb250YWluZXIgYW5kIGRldGVybWluZXMgY29sbGlzaW9uIGV2ZW50cyB3aXRoIGNvbnRhaW5lci5cbiAqIEBmdW5jdGlvblxuICogQHBhcmFtIHtqUXVlcnl9IGVsZW1lbnQgLSBqUXVlcnkgb2JqZWN0IHRvIHRlc3QgZm9yIGNvbGxpc2lvbnMuXG4gKiBAcGFyYW0ge2pRdWVyeX0gcGFyZW50IC0galF1ZXJ5IG9iamVjdCB0byB1c2UgYXMgYm91bmRpbmcgY29udGFpbmVyLlxuICogQHBhcmFtIHtCb29sZWFufSBsck9ubHkgLSBzZXQgdG8gdHJ1ZSB0byBjaGVjayBsZWZ0IGFuZCByaWdodCB2YWx1ZXMgb25seS5cbiAqIEBwYXJhbSB7Qm9vbGVhbn0gdGJPbmx5IC0gc2V0IHRvIHRydWUgdG8gY2hlY2sgdG9wIGFuZCBib3R0b20gdmFsdWVzIG9ubHkuXG4gKiBAZGVmYXVsdCBpZiBubyBwYXJlbnQgb2JqZWN0IHBhc3NlZCwgZGV0ZWN0cyBjb2xsaXNpb25zIHdpdGggYHdpbmRvd2AuXG4gKiBAcmV0dXJucyB7Qm9vbGVhbn0gLSB0cnVlIGlmIGNvbGxpc2lvbiBmcmVlLCBmYWxzZSBpZiBhIGNvbGxpc2lvbiBpbiBhbnkgZGlyZWN0aW9uLlxuICovXG5mdW5jdGlvbiBJbU5vdFRvdWNoaW5nWW91KGVsZW1lbnQsIHBhcmVudCwgbHJPbmx5LCB0Yk9ubHksIGlnbm9yZUJvdHRvbSkge1xuICByZXR1cm4gT3ZlcmxhcEFyZWEoZWxlbWVudCwgcGFyZW50LCBsck9ubHksIHRiT25seSwgaWdub3JlQm90dG9tKSA9PT0gMDtcbn07XG5cbmZ1bmN0aW9uIE92ZXJsYXBBcmVhKGVsZW1lbnQsIHBhcmVudCwgbHJPbmx5LCB0Yk9ubHksIGlnbm9yZUJvdHRvbSkge1xuICB2YXIgZWxlRGltcyA9IEdldERpbWVuc2lvbnMoZWxlbWVudCksXG4gIHRvcE92ZXIsIGJvdHRvbU92ZXIsIGxlZnRPdmVyLCByaWdodE92ZXI7XG4gIGlmIChwYXJlbnQpIHtcbiAgICB2YXIgcGFyRGltcyA9IEdldERpbWVuc2lvbnMocGFyZW50KTtcblxuICAgIGJvdHRvbU92ZXIgPSAocGFyRGltcy5oZWlnaHQgKyBwYXJEaW1zLm9mZnNldC50b3ApIC0gKGVsZURpbXMub2Zmc2V0LnRvcCArIGVsZURpbXMuaGVpZ2h0KTtcbiAgICB0b3BPdmVyICAgID0gZWxlRGltcy5vZmZzZXQudG9wIC0gcGFyRGltcy5vZmZzZXQudG9wO1xuICAgIGxlZnRPdmVyICAgPSBlbGVEaW1zLm9mZnNldC5sZWZ0IC0gcGFyRGltcy5vZmZzZXQubGVmdDtcbiAgICByaWdodE92ZXIgID0gKHBhckRpbXMud2lkdGggKyBwYXJEaW1zLm9mZnNldC5sZWZ0KSAtIChlbGVEaW1zLm9mZnNldC5sZWZ0ICsgZWxlRGltcy53aWR0aCk7XG4gIH1cbiAgZWxzZSB7XG4gICAgYm90dG9tT3ZlciA9IChlbGVEaW1zLndpbmRvd0RpbXMuaGVpZ2h0ICsgZWxlRGltcy53aW5kb3dEaW1zLm9mZnNldC50b3ApIC0gKGVsZURpbXMub2Zmc2V0LnRvcCArIGVsZURpbXMuaGVpZ2h0KTtcbiAgICB0b3BPdmVyICAgID0gZWxlRGltcy5vZmZzZXQudG9wIC0gZWxlRGltcy53aW5kb3dEaW1zLm9mZnNldC50b3A7XG4gICAgbGVmdE92ZXIgICA9IGVsZURpbXMub2Zmc2V0LmxlZnQgLSBlbGVEaW1zLndpbmRvd0RpbXMub2Zmc2V0LmxlZnQ7XG4gICAgcmlnaHRPdmVyICA9IGVsZURpbXMud2luZG93RGltcy53aWR0aCAtIChlbGVEaW1zLm9mZnNldC5sZWZ0ICsgZWxlRGltcy53aWR0aCk7XG4gIH1cblxuICBib3R0b21PdmVyID0gaWdub3JlQm90dG9tID8gMCA6IE1hdGgubWluKGJvdHRvbU92ZXIsIDApO1xuICB0b3BPdmVyICAgID0gTWF0aC5taW4odG9wT3ZlciwgMCk7XG4gIGxlZnRPdmVyICAgPSBNYXRoLm1pbihsZWZ0T3ZlciwgMCk7XG4gIHJpZ2h0T3ZlciAgPSBNYXRoLm1pbihyaWdodE92ZXIsIDApO1xuXG4gIGlmIChsck9ubHkpIHtcbiAgICByZXR1cm4gbGVmdE92ZXIgKyByaWdodE92ZXI7XG4gIH1cbiAgaWYgKHRiT25seSkge1xuICAgIHJldHVybiB0b3BPdmVyICsgYm90dG9tT3ZlcjtcbiAgfVxuXG4gIC8vIHVzZSBzdW0gb2Ygc3F1YXJlcyBiL2Mgd2UgY2FyZSBhYm91dCBvdmVybGFwIGFyZWEuXG4gIHJldHVybiBNYXRoLnNxcnQoKHRvcE92ZXIgKiB0b3BPdmVyKSArIChib3R0b21PdmVyICogYm90dG9tT3ZlcikgKyAobGVmdE92ZXIgKiBsZWZ0T3ZlcikgKyAocmlnaHRPdmVyICogcmlnaHRPdmVyKSk7XG59XG5cbi8qKlxuICogVXNlcyBuYXRpdmUgbWV0aG9kcyB0byByZXR1cm4gYW4gb2JqZWN0IG9mIGRpbWVuc2lvbiB2YWx1ZXMuXG4gKiBAZnVuY3Rpb25cbiAqIEBwYXJhbSB7alF1ZXJ5IHx8IEhUTUx9IGVsZW1lbnQgLSBqUXVlcnkgb2JqZWN0IG9yIERPTSBlbGVtZW50IGZvciB3aGljaCB0byBnZXQgdGhlIGRpbWVuc2lvbnMuIENhbiBiZSBhbnkgZWxlbWVudCBvdGhlciB0aGF0IGRvY3VtZW50IG9yIHdpbmRvdy5cbiAqIEByZXR1cm5zIHtPYmplY3R9IC0gbmVzdGVkIG9iamVjdCBvZiBpbnRlZ2VyIHBpeGVsIHZhbHVlc1xuICogVE9ETyAtIGlmIGVsZW1lbnQgaXMgd2luZG93LCByZXR1cm4gb25seSB0aG9zZSB2YWx1ZXMuXG4gKi9cbmZ1bmN0aW9uIEdldERpbWVuc2lvbnMoZWxlbSl7XG4gIGVsZW0gPSBlbGVtLmxlbmd0aCA/IGVsZW1bMF0gOiBlbGVtO1xuXG4gIGlmIChlbGVtID09PSB3aW5kb3cgfHwgZWxlbSA9PT0gZG9jdW1lbnQpIHtcbiAgICB0aHJvdyBuZXcgRXJyb3IoXCJJJ20gc29ycnksIERhdmUuIEknbSBhZnJhaWQgSSBjYW4ndCBkbyB0aGF0LlwiKTtcbiAgfVxuXG4gIHZhciByZWN0ID0gZWxlbS5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKSxcbiAgICAgIHBhclJlY3QgPSBlbGVtLnBhcmVudE5vZGUuZ2V0Qm91bmRpbmdDbGllbnRSZWN0KCksXG4gICAgICB3aW5SZWN0ID0gZG9jdW1lbnQuYm9keS5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKSxcbiAgICAgIHdpblkgPSB3aW5kb3cucGFnZVlPZmZzZXQsXG4gICAgICB3aW5YID0gd2luZG93LnBhZ2VYT2Zmc2V0O1xuXG4gIHJldHVybiB7XG4gICAgd2lkdGg6IHJlY3Qud2lkdGgsXG4gICAgaGVpZ2h0OiByZWN0LmhlaWdodCxcbiAgICBvZmZzZXQ6IHtcbiAgICAgIHRvcDogcmVjdC50b3AgKyB3aW5ZLFxuICAgICAgbGVmdDogcmVjdC5sZWZ0ICsgd2luWFxuICAgIH0sXG4gICAgcGFyZW50RGltczoge1xuICAgICAgd2lkdGg6IHBhclJlY3Qud2lkdGgsXG4gICAgICBoZWlnaHQ6IHBhclJlY3QuaGVpZ2h0LFxuICAgICAgb2Zmc2V0OiB7XG4gICAgICAgIHRvcDogcGFyUmVjdC50b3AgKyB3aW5ZLFxuICAgICAgICBsZWZ0OiBwYXJSZWN0LmxlZnQgKyB3aW5YXG4gICAgICB9XG4gICAgfSxcbiAgICB3aW5kb3dEaW1zOiB7XG4gICAgICB3aWR0aDogd2luUmVjdC53aWR0aCxcbiAgICAgIGhlaWdodDogd2luUmVjdC5oZWlnaHQsXG4gICAgICBvZmZzZXQ6IHtcbiAgICAgICAgdG9wOiB3aW5ZLFxuICAgICAgICBsZWZ0OiB3aW5YXG4gICAgICB9XG4gICAgfVxuICB9XG59XG5cbi8qKlxuICogUmV0dXJucyBhbiBvYmplY3Qgb2YgdG9wIGFuZCBsZWZ0IGludGVnZXIgcGl4ZWwgdmFsdWVzIGZvciBkeW5hbWljYWxseSByZW5kZXJlZCBlbGVtZW50cyxcbiAqIHN1Y2ggYXM6IFRvb2x0aXAsIFJldmVhbCwgYW5kIERyb3Bkb3duLiBNYWludGFpbmVkIGZvciBiYWNrd2FyZHMgY29tcGF0aWJpbGl0eSwgYW5kIHdoZXJlXG4gKiB5b3UgZG9uJ3Qga25vdyBhbGlnbm1lbnQsIGJ1dCBnZW5lcmFsbHkgZnJvbVxuICogNi40IGZvcndhcmQgeW91IHNob3VsZCB1c2UgR2V0RXhwbGljaXRPZmZzZXRzLCBhcyBHZXRPZmZzZXRzIGNvbmZsYXRlcyBwb3NpdGlvbiBhbmQgYWxpZ25tZW50LlxuICogQGZ1bmN0aW9uXG4gKiBAcGFyYW0ge2pRdWVyeX0gZWxlbWVudCAtIGpRdWVyeSBvYmplY3QgZm9yIHRoZSBlbGVtZW50IGJlaW5nIHBvc2l0aW9uZWQuXG4gKiBAcGFyYW0ge2pRdWVyeX0gYW5jaG9yIC0galF1ZXJ5IG9iamVjdCBmb3IgdGhlIGVsZW1lbnQncyBhbmNob3IgcG9pbnQuXG4gKiBAcGFyYW0ge1N0cmluZ30gcG9zaXRpb24gLSBhIHN0cmluZyByZWxhdGluZyB0byB0aGUgZGVzaXJlZCBwb3NpdGlvbiBvZiB0aGUgZWxlbWVudCwgcmVsYXRpdmUgdG8gaXQncyBhbmNob3JcbiAqIEBwYXJhbSB7TnVtYmVyfSB2T2Zmc2V0IC0gaW50ZWdlciBwaXhlbCB2YWx1ZSBvZiBkZXNpcmVkIHZlcnRpY2FsIHNlcGFyYXRpb24gYmV0d2VlbiBhbmNob3IgYW5kIGVsZW1lbnQuXG4gKiBAcGFyYW0ge051bWJlcn0gaE9mZnNldCAtIGludGVnZXIgcGl4ZWwgdmFsdWUgb2YgZGVzaXJlZCBob3Jpem9udGFsIHNlcGFyYXRpb24gYmV0d2VlbiBhbmNob3IgYW5kIGVsZW1lbnQuXG4gKiBAcGFyYW0ge0Jvb2xlYW59IGlzT3ZlcmZsb3cgLSBpZiBhIGNvbGxpc2lvbiBldmVudCBpcyBkZXRlY3RlZCwgc2V0cyB0byB0cnVlIHRvIGRlZmF1bHQgdGhlIGVsZW1lbnQgdG8gZnVsbCB3aWR0aCAtIGFueSBkZXNpcmVkIG9mZnNldC5cbiAqIFRPRE8gYWx0ZXIvcmV3cml0ZSB0byB3b3JrIHdpdGggYGVtYCB2YWx1ZXMgYXMgd2VsbC9pbnN0ZWFkIG9mIHBpeGVsc1xuICovXG5mdW5jdGlvbiBHZXRPZmZzZXRzKGVsZW1lbnQsIGFuY2hvciwgcG9zaXRpb24sIHZPZmZzZXQsIGhPZmZzZXQsIGlzT3ZlcmZsb3cpIHtcbiAgY29uc29sZS5sb2coXCJOT1RFOiBHZXRPZmZzZXRzIGlzIGRlcHJlY2F0ZWQgaW4gZmF2b3Igb2YgR2V0RXhwbGljaXRPZmZzZXRzIGFuZCB3aWxsIGJlIHJlbW92ZWQgaW4gNi41XCIpO1xuICBzd2l0Y2ggKHBvc2l0aW9uKSB7XG4gICAgY2FzZSAndG9wJzpcbiAgICAgIHJldHVybiBSdGwoKSA/XG4gICAgICAgIEdldEV4cGxpY2l0T2Zmc2V0cyhlbGVtZW50LCBhbmNob3IsICd0b3AnLCAnbGVmdCcsIHZPZmZzZXQsIGhPZmZzZXQsIGlzT3ZlcmZsb3cpIDpcbiAgICAgICAgR2V0RXhwbGljaXRPZmZzZXRzKGVsZW1lbnQsIGFuY2hvciwgJ3RvcCcsICdyaWdodCcsIHZPZmZzZXQsIGhPZmZzZXQsIGlzT3ZlcmZsb3cpO1xuICAgIGNhc2UgJ2JvdHRvbSc6XG4gICAgICByZXR1cm4gUnRsKCkgP1xuICAgICAgICBHZXRFeHBsaWNpdE9mZnNldHMoZWxlbWVudCwgYW5jaG9yLCAnYm90dG9tJywgJ2xlZnQnLCB2T2Zmc2V0LCBoT2Zmc2V0LCBpc092ZXJmbG93KSA6XG4gICAgICAgIEdldEV4cGxpY2l0T2Zmc2V0cyhlbGVtZW50LCBhbmNob3IsICdib3R0b20nLCAncmlnaHQnLCB2T2Zmc2V0LCBoT2Zmc2V0LCBpc092ZXJmbG93KTtcbiAgICBjYXNlICdjZW50ZXIgdG9wJzpcbiAgICAgIHJldHVybiBHZXRFeHBsaWNpdE9mZnNldHMoZWxlbWVudCwgYW5jaG9yLCAndG9wJywgJ2NlbnRlcicsIHZPZmZzZXQsIGhPZmZzZXQsIGlzT3ZlcmZsb3cpO1xuICAgIGNhc2UgJ2NlbnRlciBib3R0b20nOlxuICAgICAgcmV0dXJuIEdldEV4cGxpY2l0T2Zmc2V0cyhlbGVtZW50LCBhbmNob3IsICdib3R0b20nLCAnY2VudGVyJywgdk9mZnNldCwgaE9mZnNldCwgaXNPdmVyZmxvdyk7XG4gICAgY2FzZSAnY2VudGVyIGxlZnQnOlxuICAgICAgcmV0dXJuIEdldEV4cGxpY2l0T2Zmc2V0cyhlbGVtZW50LCBhbmNob3IsICdsZWZ0JywgJ2NlbnRlcicsIHZPZmZzZXQsIGhPZmZzZXQsIGlzT3ZlcmZsb3cpO1xuICAgIGNhc2UgJ2NlbnRlciByaWdodCc6XG4gICAgICByZXR1cm4gR2V0RXhwbGljaXRPZmZzZXRzKGVsZW1lbnQsIGFuY2hvciwgJ3JpZ2h0JywgJ2NlbnRlcicsIHZPZmZzZXQsIGhPZmZzZXQsIGlzT3ZlcmZsb3cpO1xuICAgIGNhc2UgJ2xlZnQgYm90dG9tJzpcbiAgICAgIHJldHVybiBHZXRFeHBsaWNpdE9mZnNldHMoZWxlbWVudCwgYW5jaG9yLCAnYm90dG9tJywgJ2xlZnQnLCB2T2Zmc2V0LCBoT2Zmc2V0LCBpc092ZXJmbG93KTtcbiAgICBjYXNlICdyaWdodCBib3R0b20nOlxuICAgICAgcmV0dXJuIEdldEV4cGxpY2l0T2Zmc2V0cyhlbGVtZW50LCBhbmNob3IsICdib3R0b20nLCAncmlnaHQnLCB2T2Zmc2V0LCBoT2Zmc2V0LCBpc092ZXJmbG93KTtcbiAgICAvLyBCYWNrd2FyZHMgY29tcGF0aWJpbGl0eS4uLiB0aGlzIGFsb25nIHdpdGggdGhlIHJldmVhbCBhbmQgcmV2ZWFsIGZ1bGxcbiAgICAvLyBjbGFzc2VzIGFyZSB0aGUgb25seSBvbmVzIHRoYXQgZGlkbid0IHJlZmVyZW5jZSBhbmNob3JcbiAgICBjYXNlICdjZW50ZXInOlxuICAgICAgcmV0dXJuIHtcbiAgICAgICAgbGVmdDogKCRlbGVEaW1zLndpbmRvd0RpbXMub2Zmc2V0LmxlZnQgKyAoJGVsZURpbXMud2luZG93RGltcy53aWR0aCAvIDIpKSAtICgkZWxlRGltcy53aWR0aCAvIDIpICsgaE9mZnNldCxcbiAgICAgICAgdG9wOiAoJGVsZURpbXMud2luZG93RGltcy5vZmZzZXQudG9wICsgKCRlbGVEaW1zLndpbmRvd0RpbXMuaGVpZ2h0IC8gMikpIC0gKCRlbGVEaW1zLmhlaWdodCAvIDIgKyB2T2Zmc2V0KVxuICAgICAgfVxuICAgIGNhc2UgJ3JldmVhbCc6XG4gICAgICByZXR1cm4ge1xuICAgICAgICBsZWZ0OiAoJGVsZURpbXMud2luZG93RGltcy53aWR0aCAtICRlbGVEaW1zLndpZHRoKSAvIDIgKyBoT2Zmc2V0LFxuICAgICAgICB0b3A6ICRlbGVEaW1zLndpbmRvd0RpbXMub2Zmc2V0LnRvcCArIHZPZmZzZXRcbiAgICAgIH1cbiAgICBjYXNlICdyZXZlYWwgZnVsbCc6XG4gICAgICByZXR1cm4ge1xuICAgICAgICBsZWZ0OiAkZWxlRGltcy53aW5kb3dEaW1zLm9mZnNldC5sZWZ0LFxuICAgICAgICB0b3A6ICRlbGVEaW1zLndpbmRvd0RpbXMub2Zmc2V0LnRvcFxuICAgICAgfVxuICAgICAgYnJlYWs7XG4gICAgZGVmYXVsdDpcbiAgICAgIHJldHVybiB7XG4gICAgICAgIGxlZnQ6IChSdGwoKSA/ICRhbmNob3JEaW1zLm9mZnNldC5sZWZ0IC0gJGVsZURpbXMud2lkdGggKyAkYW5jaG9yRGltcy53aWR0aCAtIGhPZmZzZXQ6ICRhbmNob3JEaW1zLm9mZnNldC5sZWZ0ICsgaE9mZnNldCksXG4gICAgICAgIHRvcDogJGFuY2hvckRpbXMub2Zmc2V0LnRvcCArICRhbmNob3JEaW1zLmhlaWdodCArIHZPZmZzZXRcbiAgICAgIH1cblxuICB9XG5cbn1cblxuZnVuY3Rpb24gR2V0RXhwbGljaXRPZmZzZXRzKGVsZW1lbnQsIGFuY2hvciwgcG9zaXRpb24sIGFsaWdubWVudCwgdk9mZnNldCwgaE9mZnNldCwgaXNPdmVyZmxvdykge1xuICB2YXIgJGVsZURpbXMgPSBHZXREaW1lbnNpb25zKGVsZW1lbnQpLFxuICAgICAgJGFuY2hvckRpbXMgPSBhbmNob3IgPyBHZXREaW1lbnNpb25zKGFuY2hvcikgOiBudWxsO1xuXG4gICAgICB2YXIgdG9wVmFsLCBsZWZ0VmFsO1xuXG4gIC8vIHNldCBwb3NpdGlvbiByZWxhdGVkIGF0dHJpYnV0ZVxuXG4gIHN3aXRjaCAocG9zaXRpb24pIHtcbiAgICBjYXNlICd0b3AnOlxuICAgICAgdG9wVmFsID0gJGFuY2hvckRpbXMub2Zmc2V0LnRvcCAtICgkZWxlRGltcy5oZWlnaHQgKyB2T2Zmc2V0KTtcbiAgICAgIGJyZWFrO1xuICAgIGNhc2UgJ2JvdHRvbSc6XG4gICAgICB0b3BWYWwgPSAkYW5jaG9yRGltcy5vZmZzZXQudG9wICsgJGFuY2hvckRpbXMuaGVpZ2h0ICsgdk9mZnNldDtcbiAgICAgIGJyZWFrO1xuICAgIGNhc2UgJ2xlZnQnOlxuICAgICAgbGVmdFZhbCA9ICRhbmNob3JEaW1zLm9mZnNldC5sZWZ0IC0gKCRlbGVEaW1zLndpZHRoICsgaE9mZnNldCk7XG4gICAgICBicmVhaztcbiAgICBjYXNlICdyaWdodCc6XG4gICAgICBsZWZ0VmFsID0gJGFuY2hvckRpbXMub2Zmc2V0LmxlZnQgKyAkYW5jaG9yRGltcy53aWR0aCArIGhPZmZzZXQ7XG4gICAgICBicmVhaztcbiAgfVxuXG5cbiAgLy8gc2V0IGFsaWdubWVudCByZWxhdGVkIGF0dHJpYnV0ZVxuICBzd2l0Y2ggKHBvc2l0aW9uKSB7XG4gICAgY2FzZSAndG9wJzpcbiAgICBjYXNlICdib3R0b20nOlxuICAgICAgc3dpdGNoIChhbGlnbm1lbnQpIHtcbiAgICAgICAgY2FzZSAnbGVmdCc6XG4gICAgICAgICAgbGVmdFZhbCA9ICRhbmNob3JEaW1zLm9mZnNldC5sZWZ0ICsgaE9mZnNldDtcbiAgICAgICAgICBicmVhaztcbiAgICAgICAgY2FzZSAncmlnaHQnOlxuICAgICAgICAgIGxlZnRWYWwgPSAkYW5jaG9yRGltcy5vZmZzZXQubGVmdCAtICRlbGVEaW1zLndpZHRoICsgJGFuY2hvckRpbXMud2lkdGggLSBoT2Zmc2V0O1xuICAgICAgICAgIGJyZWFrO1xuICAgICAgICBjYXNlICdjZW50ZXInOlxuICAgICAgICAgIGxlZnRWYWwgPSBpc092ZXJmbG93ID8gaE9mZnNldCA6ICgoJGFuY2hvckRpbXMub2Zmc2V0LmxlZnQgKyAoJGFuY2hvckRpbXMud2lkdGggLyAyKSkgLSAoJGVsZURpbXMud2lkdGggLyAyKSkgKyBoT2Zmc2V0O1xuICAgICAgICAgIGJyZWFrO1xuICAgICAgfVxuICAgICAgYnJlYWs7XG4gICAgY2FzZSAncmlnaHQnOlxuICAgIGNhc2UgJ2xlZnQnOlxuICAgICAgc3dpdGNoIChhbGlnbm1lbnQpIHtcbiAgICAgICAgY2FzZSAnYm90dG9tJzpcbiAgICAgICAgICB0b3BWYWwgPSAkYW5jaG9yRGltcy5vZmZzZXQudG9wIC0gdk9mZnNldCArICRhbmNob3JEaW1zLmhlaWdodCAtICRlbGVEaW1zLmhlaWdodDtcbiAgICAgICAgICBicmVhaztcbiAgICAgICAgY2FzZSAndG9wJzpcbiAgICAgICAgICB0b3BWYWwgPSAkYW5jaG9yRGltcy5vZmZzZXQudG9wICsgdk9mZnNldFxuICAgICAgICAgIGJyZWFrO1xuICAgICAgICBjYXNlICdjZW50ZXInOlxuICAgICAgICAgIHRvcFZhbCA9ICgkYW5jaG9yRGltcy5vZmZzZXQudG9wICsgdk9mZnNldCArICgkYW5jaG9yRGltcy5oZWlnaHQgLyAyKSkgLSAoJGVsZURpbXMuaGVpZ2h0IC8gMilcbiAgICAgICAgICBicmVhaztcbiAgICAgIH1cbiAgICAgIGJyZWFrO1xuICB9XG4gIHJldHVybiB7dG9wOiB0b3BWYWwsIGxlZnQ6IGxlZnRWYWx9O1xufVxuXG5leHBvcnQge0JveH07XG5cblxuXG4vLyBXRUJQQUNLIEZPT1RFUiAvL1xuLy8gLi9ub2RlX21vZHVsZXMvZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLnV0aWwuYm94LmpzIiwiJ3VzZSBzdHJpY3QnO1xuXG5pbXBvcnQgJCBmcm9tICdqcXVlcnknO1xuXG4vKipcbiAqIFJ1bnMgYSBjYWxsYmFjayBmdW5jdGlvbiB3aGVuIGltYWdlcyBhcmUgZnVsbHkgbG9hZGVkLlxuICogQHBhcmFtIHtPYmplY3R9IGltYWdlcyAtIEltYWdlKHMpIHRvIGNoZWNrIGlmIGxvYWRlZC5cbiAqIEBwYXJhbSB7RnVuY30gY2FsbGJhY2sgLSBGdW5jdGlvbiB0byBleGVjdXRlIHdoZW4gaW1hZ2UgaXMgZnVsbHkgbG9hZGVkLlxuICovXG5mdW5jdGlvbiBvbkltYWdlc0xvYWRlZChpbWFnZXMsIGNhbGxiYWNrKXtcbiAgdmFyIHNlbGYgPSB0aGlzLFxuICAgICAgdW5sb2FkZWQgPSBpbWFnZXMubGVuZ3RoO1xuXG4gIGlmICh1bmxvYWRlZCA9PT0gMCkge1xuICAgIGNhbGxiYWNrKCk7XG4gIH1cblxuICBpbWFnZXMuZWFjaChmdW5jdGlvbigpe1xuICAgIC8vIENoZWNrIGlmIGltYWdlIGlzIGxvYWRlZFxuICAgIGlmICh0aGlzLmNvbXBsZXRlICYmIHR5cGVvZiB0aGlzLm5hdHVyYWxXaWR0aCAhPT0gJ3VuZGVmaW5lZCcpIHtcbiAgICAgIHNpbmdsZUltYWdlTG9hZGVkKCk7XG4gICAgfVxuICAgIGVsc2Uge1xuICAgICAgLy8gSWYgdGhlIGFib3ZlIGNoZWNrIGZhaWxlZCwgc2ltdWxhdGUgbG9hZGluZyBvbiBkZXRhY2hlZCBlbGVtZW50LlxuICAgICAgdmFyIGltYWdlID0gbmV3IEltYWdlKCk7XG4gICAgICAvLyBTdGlsbCBjb3VudCBpbWFnZSBhcyBsb2FkZWQgaWYgaXQgZmluYWxpemVzIHdpdGggYW4gZXJyb3IuXG4gICAgICB2YXIgZXZlbnRzID0gXCJsb2FkLnpmLmltYWdlcyBlcnJvci56Zi5pbWFnZXNcIjtcbiAgICAgICQoaW1hZ2UpLm9uZShldmVudHMsIGZ1bmN0aW9uIG1lKGV2ZW50KXtcbiAgICAgICAgLy8gVW5iaW5kIHRoZSBldmVudCBsaXN0ZW5lcnMuIFdlJ3JlIHVzaW5nICdvbmUnIGJ1dCBvbmx5IG9uZSBvZiB0aGUgdHdvIGV2ZW50cyB3aWxsIGhhdmUgZmlyZWQuXG4gICAgICAgICQodGhpcykub2ZmKGV2ZW50cywgbWUpO1xuICAgICAgICBzaW5nbGVJbWFnZUxvYWRlZCgpO1xuICAgICAgfSk7XG4gICAgICBpbWFnZS5zcmMgPSAkKHRoaXMpLmF0dHIoJ3NyYycpO1xuICAgIH1cbiAgfSk7XG5cbiAgZnVuY3Rpb24gc2luZ2xlSW1hZ2VMb2FkZWQoKSB7XG4gICAgdW5sb2FkZWQtLTtcbiAgICBpZiAodW5sb2FkZWQgPT09IDApIHtcbiAgICAgIGNhbGxiYWNrKCk7XG4gICAgfVxuICB9XG59XG5cbmV4cG9ydCB7IG9uSW1hZ2VzTG9hZGVkIH07XG5cblxuXG4vLyBXRUJQQUNLIEZPT1RFUiAvL1xuLy8gLi9ub2RlX21vZHVsZXMvZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLnV0aWwuaW1hZ2VMb2FkZXIuanMiLCIndXNlIHN0cmljdCc7XG5cbmltcG9ydCAkIGZyb20gJ2pxdWVyeSc7XG5cbmNvbnN0IE5lc3QgPSB7XG4gIEZlYXRoZXIobWVudSwgdHlwZSA9ICd6ZicpIHtcbiAgICBtZW51LmF0dHIoJ3JvbGUnLCAnbWVudWJhcicpO1xuXG4gICAgdmFyIGl0ZW1zID0gbWVudS5maW5kKCdsaScpLmF0dHIoeydyb2xlJzogJ21lbnVpdGVtJ30pLFxuICAgICAgICBzdWJNZW51Q2xhc3MgPSBgaXMtJHt0eXBlfS1zdWJtZW51YCxcbiAgICAgICAgc3ViSXRlbUNsYXNzID0gYCR7c3ViTWVudUNsYXNzfS1pdGVtYCxcbiAgICAgICAgaGFzU3ViQ2xhc3MgPSBgaXMtJHt0eXBlfS1zdWJtZW51LXBhcmVudGAsXG4gICAgICAgIGFwcGx5QXJpYSA9ICh0eXBlICE9PSAnYWNjb3JkaW9uJyk7IC8vIEFjY29yZGlvbnMgaGFuZGxlIHRoZWlyIG93biBBUklBIGF0dHJpdXRlcy5cblxuICAgIGl0ZW1zLmVhY2goZnVuY3Rpb24oKSB7XG4gICAgICB2YXIgJGl0ZW0gPSAkKHRoaXMpLFxuICAgICAgICAgICRzdWIgPSAkaXRlbS5jaGlsZHJlbigndWwnKTtcblxuICAgICAgaWYgKCRzdWIubGVuZ3RoKSB7XG4gICAgICAgICRpdGVtLmFkZENsYXNzKGhhc1N1YkNsYXNzKTtcbiAgICAgICAgJHN1Yi5hZGRDbGFzcyhgc3VibWVudSAke3N1Yk1lbnVDbGFzc31gKS5hdHRyKHsnZGF0YS1zdWJtZW51JzogJyd9KTtcbiAgICAgICAgaWYoYXBwbHlBcmlhKSB7XG4gICAgICAgICAgJGl0ZW0uYXR0cih7XG4gICAgICAgICAgICAnYXJpYS1oYXNwb3B1cCc6IHRydWUsXG4gICAgICAgICAgICAnYXJpYS1sYWJlbCc6ICRpdGVtLmNoaWxkcmVuKCdhOmZpcnN0JykudGV4dCgpXG4gICAgICAgICAgfSk7XG4gICAgICAgICAgLy8gTm90ZTogIERyaWxsZG93bnMgYmVoYXZlIGRpZmZlcmVudGx5IGluIGhvdyB0aGV5IGhpZGUsIGFuZCBzbyBuZWVkXG4gICAgICAgICAgLy8gYWRkaXRpb25hbCBhdHRyaWJ1dGVzLiAgV2Ugc2hvdWxkIGxvb2sgaWYgdGhpcyBwb3NzaWJseSBvdmVyLWdlbmVyYWxpemVkXG4gICAgICAgICAgLy8gdXRpbGl0eSAoTmVzdCkgaXMgYXBwcm9wcmlhdGUgd2hlbiB3ZSByZXdvcmsgbWVudXMgaW4gNi40XG4gICAgICAgICAgaWYodHlwZSA9PT0gJ2RyaWxsZG93bicpIHtcbiAgICAgICAgICAgICRpdGVtLmF0dHIoeydhcmlhLWV4cGFuZGVkJzogZmFsc2V9KTtcbiAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICAgICAgJHN1YlxuICAgICAgICAgIC5hZGRDbGFzcyhgc3VibWVudSAke3N1Yk1lbnVDbGFzc31gKVxuICAgICAgICAgIC5hdHRyKHtcbiAgICAgICAgICAgICdkYXRhLXN1Ym1lbnUnOiAnJyxcbiAgICAgICAgICAgICdyb2xlJzogJ21lbnViYXInXG4gICAgICAgICAgfSk7XG4gICAgICAgIGlmKHR5cGUgPT09ICdkcmlsbGRvd24nKSB7XG4gICAgICAgICAgJHN1Yi5hdHRyKHsnYXJpYS1oaWRkZW4nOiB0cnVlfSk7XG4gICAgICAgIH1cbiAgICAgIH1cblxuICAgICAgaWYgKCRpdGVtLnBhcmVudCgnW2RhdGEtc3VibWVudV0nKS5sZW5ndGgpIHtcbiAgICAgICAgJGl0ZW0uYWRkQ2xhc3MoYGlzLXN1Ym1lbnUtaXRlbSAke3N1Ykl0ZW1DbGFzc31gKTtcbiAgICAgIH1cbiAgICB9KTtcblxuICAgIHJldHVybjtcbiAgfSxcblxuICBCdXJuKG1lbnUsIHR5cGUpIHtcbiAgICB2YXIgLy9pdGVtcyA9IG1lbnUuZmluZCgnbGknKSxcbiAgICAgICAgc3ViTWVudUNsYXNzID0gYGlzLSR7dHlwZX0tc3VibWVudWAsXG4gICAgICAgIHN1Ykl0ZW1DbGFzcyA9IGAke3N1Yk1lbnVDbGFzc30taXRlbWAsXG4gICAgICAgIGhhc1N1YkNsYXNzID0gYGlzLSR7dHlwZX0tc3VibWVudS1wYXJlbnRgO1xuXG4gICAgbWVudVxuICAgICAgLmZpbmQoJz5saSwgPiBsaSA+IHVsLCAubWVudSwgLm1lbnUgPiBsaSwgW2RhdGEtc3VibWVudV0gPiBsaScpXG4gICAgICAucmVtb3ZlQ2xhc3MoYCR7c3ViTWVudUNsYXNzfSAke3N1Ykl0ZW1DbGFzc30gJHtoYXNTdWJDbGFzc30gaXMtc3VibWVudS1pdGVtIHN1Ym1lbnUgaXMtYWN0aXZlYClcbiAgICAgIC5yZW1vdmVBdHRyKCdkYXRhLXN1Ym1lbnUnKS5jc3MoJ2Rpc3BsYXknLCAnJyk7XG5cbiAgfVxufVxuXG5leHBvcnQge05lc3R9O1xuXG5cblxuLy8gV0VCUEFDSyBGT09URVIgLy9cbi8vIC4vbm9kZV9tb2R1bGVzL2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi51dGlsLm5lc3QuanMiLCIvLyoqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqXG4vLyoqV29yayBpbnNwaXJlZCBieSBtdWx0aXBsZSBqcXVlcnkgc3dpcGUgcGx1Z2lucyoqXG4vLyoqRG9uZSBieSBZb2hhaSBBcmFyYXQgKioqKioqKioqKioqKioqKioqKioqKioqKioqXG4vLyoqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqXG5cbmltcG9ydCAkIGZyb20gJ2pxdWVyeSc7XG5cbnZhciBUb3VjaCA9IHt9O1xuXG52YXIgc3RhcnRQb3NYLFxuICAgIHN0YXJ0UG9zWSxcbiAgICBzdGFydFRpbWUsXG4gICAgZWxhcHNlZFRpbWUsXG4gICAgc3RhcnRFdmVudCxcbiAgICBpc01vdmluZyA9IGZhbHNlLFxuICAgIGRpZE1vdmVkID0gZmFsc2U7XG5cbmZ1bmN0aW9uIG9uVG91Y2hFbmQoZSkge1xuICB0aGlzLnJlbW92ZUV2ZW50TGlzdGVuZXIoJ3RvdWNobW92ZScsIG9uVG91Y2hNb3ZlKTtcbiAgdGhpcy5yZW1vdmVFdmVudExpc3RlbmVyKCd0b3VjaGVuZCcsIG9uVG91Y2hFbmQpO1xuXG4gIC8vIElmIHRoZSB0b3VjaCBkaWQgbm90IG1vdmUsIGNvbnNpZGVyIGl0IGFzIGEgXCJ0YXBcIlxuICBpZiAoIWRpZE1vdmVkKSB7XG4gICAgdmFyIHRhcEV2ZW50ID0gJC5FdmVudCgndGFwJywgc3RhcnRFdmVudCB8fCBlKTtcbiAgICAkKHRoaXMpLnRyaWdnZXIodGFwRXZlbnQpO1xuICB9XG5cbiAgc3RhcnRFdmVudCA9IG51bGw7XG4gIGlzTW92aW5nID0gZmFsc2U7XG4gIGRpZE1vdmVkID0gZmFsc2U7XG59XG5cbmZ1bmN0aW9uIG9uVG91Y2hNb3ZlKGUpIHtcbiAgaWYgKCQuc3BvdFN3aXBlLnByZXZlbnREZWZhdWx0KSB7IGUucHJldmVudERlZmF1bHQoKTsgfVxuXG4gIGlmKGlzTW92aW5nKSB7XG4gICAgdmFyIHggPSBlLnRvdWNoZXNbMF0ucGFnZVg7XG4gICAgdmFyIHkgPSBlLnRvdWNoZXNbMF0ucGFnZVk7XG4gICAgdmFyIGR4ID0gc3RhcnRQb3NYIC0geDtcbiAgICB2YXIgZHkgPSBzdGFydFBvc1kgLSB5O1xuICAgIHZhciBkaXI7XG4gICAgZGlkTW92ZWQgPSB0cnVlO1xuICAgIGVsYXBzZWRUaW1lID0gbmV3IERhdGUoKS5nZXRUaW1lKCkgLSBzdGFydFRpbWU7XG4gICAgaWYoTWF0aC5hYnMoZHgpID49ICQuc3BvdFN3aXBlLm1vdmVUaHJlc2hvbGQgJiYgZWxhcHNlZFRpbWUgPD0gJC5zcG90U3dpcGUudGltZVRocmVzaG9sZCkge1xuICAgICAgZGlyID0gZHggPiAwID8gJ2xlZnQnIDogJ3JpZ2h0JztcbiAgICB9XG4gICAgLy8gZWxzZSBpZihNYXRoLmFicyhkeSkgPj0gJC5zcG90U3dpcGUubW92ZVRocmVzaG9sZCAmJiBlbGFwc2VkVGltZSA8PSAkLnNwb3RTd2lwZS50aW1lVGhyZXNob2xkKSB7XG4gICAgLy8gICBkaXIgPSBkeSA+IDAgPyAnZG93bicgOiAndXAnO1xuICAgIC8vIH1cbiAgICBpZihkaXIpIHtcbiAgICAgIGUucHJldmVudERlZmF1bHQoKTtcbiAgICAgIG9uVG91Y2hFbmQuYXBwbHkodGhpcywgYXJndW1lbnRzKTtcbiAgICAgICQodGhpcylcbiAgICAgICAgLnRyaWdnZXIoJC5FdmVudCgnc3dpcGUnLCBlKSwgZGlyKVxuICAgICAgICAudHJpZ2dlcigkLkV2ZW50KGBzd2lwZSR7ZGlyfWAsIGUpKTtcbiAgICB9XG4gIH1cblxufVxuXG5mdW5jdGlvbiBvblRvdWNoU3RhcnQoZSkge1xuXG4gIGlmIChlLnRvdWNoZXMubGVuZ3RoID09IDEpIHtcbiAgICBzdGFydFBvc1ggPSBlLnRvdWNoZXNbMF0ucGFnZVg7XG4gICAgc3RhcnRQb3NZID0gZS50b3VjaGVzWzBdLnBhZ2VZO1xuICAgIHN0YXJ0RXZlbnQgPSBlO1xuICAgIGlzTW92aW5nID0gdHJ1ZTtcbiAgICBkaWRNb3ZlZCA9IGZhbHNlO1xuICAgIHN0YXJ0VGltZSA9IG5ldyBEYXRlKCkuZ2V0VGltZSgpO1xuICAgIHRoaXMuYWRkRXZlbnRMaXN0ZW5lcigndG91Y2htb3ZlJywgb25Ub3VjaE1vdmUsIGZhbHNlKTtcbiAgICB0aGlzLmFkZEV2ZW50TGlzdGVuZXIoJ3RvdWNoZW5kJywgb25Ub3VjaEVuZCwgZmFsc2UpO1xuICB9XG59XG5cbmZ1bmN0aW9uIGluaXQoKSB7XG4gIHRoaXMuYWRkRXZlbnRMaXN0ZW5lciAmJiB0aGlzLmFkZEV2ZW50TGlzdGVuZXIoJ3RvdWNoc3RhcnQnLCBvblRvdWNoU3RhcnQsIGZhbHNlKTtcbn1cblxuZnVuY3Rpb24gdGVhcmRvd24oKSB7XG4gIHRoaXMucmVtb3ZlRXZlbnRMaXN0ZW5lcigndG91Y2hzdGFydCcsIG9uVG91Y2hTdGFydCk7XG59XG5cbmNsYXNzIFNwb3RTd2lwZSB7XG4gIGNvbnN0cnVjdG9yKCQpIHtcbiAgICB0aGlzLnZlcnNpb24gPSAnMS4wLjAnO1xuICAgIHRoaXMuZW5hYmxlZCA9ICdvbnRvdWNoc3RhcnQnIGluIGRvY3VtZW50LmRvY3VtZW50RWxlbWVudDtcbiAgICB0aGlzLnByZXZlbnREZWZhdWx0ID0gZmFsc2U7XG4gICAgdGhpcy5tb3ZlVGhyZXNob2xkID0gNzU7XG4gICAgdGhpcy50aW1lVGhyZXNob2xkID0gMjAwO1xuICAgIHRoaXMuJCA9ICQ7XG4gICAgdGhpcy5faW5pdCgpO1xuICB9XG5cbiAgX2luaXQoKSB7XG4gICAgdmFyICQgPSB0aGlzLiQ7XG4gICAgJC5ldmVudC5zcGVjaWFsLnN3aXBlID0geyBzZXR1cDogaW5pdCB9O1xuICAgICQuZXZlbnQuc3BlY2lhbC50YXAgPSB7IHNldHVwOiBpbml0IH07XG5cbiAgICAkLmVhY2goWydsZWZ0JywgJ3VwJywgJ2Rvd24nLCAncmlnaHQnXSwgZnVuY3Rpb24gKCkge1xuICAgICAgJC5ldmVudC5zcGVjaWFsW2Bzd2lwZSR7dGhpc31gXSA9IHsgc2V0dXA6IGZ1bmN0aW9uKCl7XG4gICAgICAgICQodGhpcykub24oJ3N3aXBlJywgJC5ub29wKTtcbiAgICAgIH0gfTtcbiAgICB9KTtcbiAgfVxufVxuXG4vKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKlxuICogQXMgZmFyIGFzIEkgY2FuIHRlbGwsIGJvdGggc2V0dXBTcG90U3dpcGUgYW5kICAgICpcbiAqIHNldHVwVG91Y2hIYW5kbGVyIHNob3VsZCBiZSBpZGVtcG90ZW50LCAgICAgICAgICAqXG4gKiBiZWNhdXNlIHRoZXkgZGlyZWN0bHkgcmVwbGFjZSBmdW5jdGlvbnMgJiAgICAgICAgKlxuICogdmFsdWVzLCBhbmQgZG8gbm90IGFkZCBldmVudCBoYW5kbGVycyBkaXJlY3RseS4gICpcbiAqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqL1xuXG5Ub3VjaC5zZXR1cFNwb3RTd2lwZSA9IGZ1bmN0aW9uKCQpIHtcbiAgJC5zcG90U3dpcGUgPSBuZXcgU3BvdFN3aXBlKCQpO1xufTtcblxuLyoqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKipcbiAqIE1ldGhvZCBmb3IgYWRkaW5nIHBzZXVkbyBkcmFnIGV2ZW50cyB0byBlbGVtZW50cyAqXG4gKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqL1xuVG91Y2guc2V0dXBUb3VjaEhhbmRsZXIgPSBmdW5jdGlvbigkKSB7XG4gICQuZm4uYWRkVG91Y2ggPSBmdW5jdGlvbigpe1xuICAgIHRoaXMuZWFjaChmdW5jdGlvbihpLGVsKXtcbiAgICAgICQoZWwpLmJpbmQoJ3RvdWNoc3RhcnQgdG91Y2htb3ZlIHRvdWNoZW5kIHRvdWNoY2FuY2VsJywgZnVuY3Rpb24oZXZlbnQpICB7XG4gICAgICAgIC8vd2UgcGFzcyB0aGUgb3JpZ2luYWwgZXZlbnQgb2JqZWN0IGJlY2F1c2UgdGhlIGpRdWVyeSBldmVudFxuICAgICAgICAvL29iamVjdCBpcyBub3JtYWxpemVkIHRvIHczYyBzcGVjcyBhbmQgZG9lcyBub3QgcHJvdmlkZSB0aGUgVG91Y2hMaXN0XG4gICAgICAgIGhhbmRsZVRvdWNoKGV2ZW50KTtcbiAgICAgIH0pO1xuICAgIH0pO1xuXG4gICAgdmFyIGhhbmRsZVRvdWNoID0gZnVuY3Rpb24oZXZlbnQpe1xuICAgICAgdmFyIHRvdWNoZXMgPSBldmVudC5jaGFuZ2VkVG91Y2hlcyxcbiAgICAgICAgICBmaXJzdCA9IHRvdWNoZXNbMF0sXG4gICAgICAgICAgZXZlbnRUeXBlcyA9IHtcbiAgICAgICAgICAgIHRvdWNoc3RhcnQ6ICdtb3VzZWRvd24nLFxuICAgICAgICAgICAgdG91Y2htb3ZlOiAnbW91c2Vtb3ZlJyxcbiAgICAgICAgICAgIHRvdWNoZW5kOiAnbW91c2V1cCdcbiAgICAgICAgICB9LFxuICAgICAgICAgIHR5cGUgPSBldmVudFR5cGVzW2V2ZW50LnR5cGVdLFxuICAgICAgICAgIHNpbXVsYXRlZEV2ZW50XG4gICAgICAgIDtcblxuICAgICAgaWYoJ01vdXNlRXZlbnQnIGluIHdpbmRvdyAmJiB0eXBlb2Ygd2luZG93Lk1vdXNlRXZlbnQgPT09ICdmdW5jdGlvbicpIHtcbiAgICAgICAgc2ltdWxhdGVkRXZlbnQgPSBuZXcgd2luZG93Lk1vdXNlRXZlbnQodHlwZSwge1xuICAgICAgICAgICdidWJibGVzJzogdHJ1ZSxcbiAgICAgICAgICAnY2FuY2VsYWJsZSc6IHRydWUsXG4gICAgICAgICAgJ3NjcmVlblgnOiBmaXJzdC5zY3JlZW5YLFxuICAgICAgICAgICdzY3JlZW5ZJzogZmlyc3Quc2NyZWVuWSxcbiAgICAgICAgICAnY2xpZW50WCc6IGZpcnN0LmNsaWVudFgsXG4gICAgICAgICAgJ2NsaWVudFknOiBmaXJzdC5jbGllbnRZXG4gICAgICAgIH0pO1xuICAgICAgfSBlbHNlIHtcbiAgICAgICAgc2ltdWxhdGVkRXZlbnQgPSBkb2N1bWVudC5jcmVhdGVFdmVudCgnTW91c2VFdmVudCcpO1xuICAgICAgICBzaW11bGF0ZWRFdmVudC5pbml0TW91c2VFdmVudCh0eXBlLCB0cnVlLCB0cnVlLCB3aW5kb3csIDEsIGZpcnN0LnNjcmVlblgsIGZpcnN0LnNjcmVlblksIGZpcnN0LmNsaWVudFgsIGZpcnN0LmNsaWVudFksIGZhbHNlLCBmYWxzZSwgZmFsc2UsIGZhbHNlLCAwLypsZWZ0Ki8sIG51bGwpO1xuICAgICAgfVxuICAgICAgZmlyc3QudGFyZ2V0LmRpc3BhdGNoRXZlbnQoc2ltdWxhdGVkRXZlbnQpO1xuICAgIH07XG4gIH07XG59O1xuXG5Ub3VjaC5pbml0ID0gZnVuY3Rpb24gKCQpIHtcblxuICBpZih0eXBlb2YoJC5zcG90U3dpcGUpID09PSAndW5kZWZpbmVkJykge1xuICAgIFRvdWNoLnNldHVwU3BvdFN3aXBlKCQpO1xuICAgIFRvdWNoLnNldHVwVG91Y2hIYW5kbGVyKCQpO1xuICB9XG59O1xuXG5leHBvcnQge1RvdWNofTtcblxuXG5cbi8vIFdFQlBBQ0sgRk9PVEVSIC8vXG4vLyAuL25vZGVfbW9kdWxlcy9mb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24udXRpbC50b3VjaC5qcyIsIid1c2Ugc3RyaWN0JztcblxuaW1wb3J0ICQgZnJvbSAnanF1ZXJ5JztcblxuZnVuY3Rpb24gVGltZXIoZWxlbSwgb3B0aW9ucywgY2IpIHtcbiAgdmFyIF90aGlzID0gdGhpcyxcbiAgICAgIGR1cmF0aW9uID0gb3B0aW9ucy5kdXJhdGlvbiwvL29wdGlvbnMgaXMgYW4gb2JqZWN0IGZvciBlYXNpbHkgYWRkaW5nIGZlYXR1cmVzIGxhdGVyLlxuICAgICAgbmFtZVNwYWNlID0gT2JqZWN0LmtleXMoZWxlbS5kYXRhKCkpWzBdIHx8ICd0aW1lcicsXG4gICAgICByZW1haW4gPSAtMSxcbiAgICAgIHN0YXJ0LFxuICAgICAgdGltZXI7XG5cbiAgdGhpcy5pc1BhdXNlZCA9IGZhbHNlO1xuXG4gIHRoaXMucmVzdGFydCA9IGZ1bmN0aW9uKCkge1xuICAgIHJlbWFpbiA9IC0xO1xuICAgIGNsZWFyVGltZW91dCh0aW1lcik7XG4gICAgdGhpcy5zdGFydCgpO1xuICB9XG5cbiAgdGhpcy5zdGFydCA9IGZ1bmN0aW9uKCkge1xuICAgIHRoaXMuaXNQYXVzZWQgPSBmYWxzZTtcbiAgICAvLyBpZighZWxlbS5kYXRhKCdwYXVzZWQnKSl7IHJldHVybiBmYWxzZTsgfS8vbWF5YmUgaW1wbGVtZW50IHRoaXMgc2FuaXR5IGNoZWNrIGlmIHVzZWQgZm9yIG90aGVyIHRoaW5ncy5cbiAgICBjbGVhclRpbWVvdXQodGltZXIpO1xuICAgIHJlbWFpbiA9IHJlbWFpbiA8PSAwID8gZHVyYXRpb24gOiByZW1haW47XG4gICAgZWxlbS5kYXRhKCdwYXVzZWQnLCBmYWxzZSk7XG4gICAgc3RhcnQgPSBEYXRlLm5vdygpO1xuICAgIHRpbWVyID0gc2V0VGltZW91dChmdW5jdGlvbigpe1xuICAgICAgaWYob3B0aW9ucy5pbmZpbml0ZSl7XG4gICAgICAgIF90aGlzLnJlc3RhcnQoKTsvL3JlcnVuIHRoZSB0aW1lci5cbiAgICAgIH1cbiAgICAgIGlmIChjYiAmJiB0eXBlb2YgY2IgPT09ICdmdW5jdGlvbicpIHsgY2IoKTsgfVxuICAgIH0sIHJlbWFpbik7XG4gICAgZWxlbS50cmlnZ2VyKGB0aW1lcnN0YXJ0LnpmLiR7bmFtZVNwYWNlfWApO1xuICB9XG5cbiAgdGhpcy5wYXVzZSA9IGZ1bmN0aW9uKCkge1xuICAgIHRoaXMuaXNQYXVzZWQgPSB0cnVlO1xuICAgIC8vaWYoZWxlbS5kYXRhKCdwYXVzZWQnKSl7IHJldHVybiBmYWxzZTsgfS8vbWF5YmUgaW1wbGVtZW50IHRoaXMgc2FuaXR5IGNoZWNrIGlmIHVzZWQgZm9yIG90aGVyIHRoaW5ncy5cbiAgICBjbGVhclRpbWVvdXQodGltZXIpO1xuICAgIGVsZW0uZGF0YSgncGF1c2VkJywgdHJ1ZSk7XG4gICAgdmFyIGVuZCA9IERhdGUubm93KCk7XG4gICAgcmVtYWluID0gcmVtYWluIC0gKGVuZCAtIHN0YXJ0KTtcbiAgICBlbGVtLnRyaWdnZXIoYHRpbWVycGF1c2VkLnpmLiR7bmFtZVNwYWNlfWApO1xuICB9XG59XG5cbmV4cG9ydCB7VGltZXJ9O1xuXG5cblxuLy8gV0VCUEFDSyBGT09URVIgLy9cbi8vIC4vbm9kZV9tb2R1bGVzL2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi51dGlsLnRpbWVyLmpzIiwiaW1wb3J0ICQgZnJvbSAnanF1ZXJ5JztcbmltcG9ydCB7IEZvdW5kYXRpb24gfSBmcm9tICdmb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24uY29yZSc7XG5pbXBvcnQgeyBydGwsIEdldFlvRGlnaXRzLCB0cmFuc2l0aW9uZW5kIH0gZnJvbSAnZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLmNvcmUudXRpbHMnO1xuaW1wb3J0IHsgQm94IH0gZnJvbSAnZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLnV0aWwuYm94J1xuaW1wb3J0IHsgb25JbWFnZXNMb2FkZWQgfSBmcm9tICdmb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24udXRpbC5pbWFnZUxvYWRlcic7XG5pbXBvcnQgeyBLZXlib2FyZCB9IGZyb20gJ2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi51dGlsLmtleWJvYXJkJztcbmltcG9ydCB7IE1lZGlhUXVlcnkgfSBmcm9tICdmb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24udXRpbC5tZWRpYVF1ZXJ5JztcbmltcG9ydCB7IE1vdGlvbiwgTW92ZSB9IGZyb20gJ2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi51dGlsLm1vdGlvbic7XG5pbXBvcnQgeyBOZXN0IH0gZnJvbSAnZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLnV0aWwubmVzdCc7XG5pbXBvcnQgeyBUaW1lciB9IGZyb20gJ2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi51dGlsLnRpbWVyJztcbmltcG9ydCB7IFRvdWNoIH0gZnJvbSAnZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLnV0aWwudG91Y2gnO1xuaW1wb3J0IHsgVHJpZ2dlcnMgfSBmcm9tICdmb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24udXRpbC50cmlnZ2Vycyc7XG4vLyBpbXBvcnQgeyBBYmlkZSB9IGZyb20gJ2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi5hYmlkZSc7XG4vLyBpbXBvcnQgeyBBY2NvcmRpb24gfSBmcm9tICdmb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24uYWNjb3JkaW9uJztcbi8vIGltcG9ydCB7IEFjY29yZGlvbk1lbnUgfSBmcm9tICdmb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24uYWNjb3JkaW9uTWVudSc7XG4vLyBpbXBvcnQgeyBEcmlsbGRvd24gfSBmcm9tICdmb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24uZHJpbGxkb3duJztcbi8vIGltcG9ydCB7IERyb3Bkb3duIH0gZnJvbSAnZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLmRyb3Bkb3duJztcbi8vIGltcG9ydCB7IERyb3Bkb3duTWVudSB9IGZyb20gJ2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi5kcm9wZG93bk1lbnUnO1xuLy8gaW1wb3J0IHsgRXF1YWxpemVyIH0gZnJvbSAnZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLmVxdWFsaXplcic7XG4vLyBpbXBvcnQgeyBJbnRlcmNoYW5nZSB9IGZyb20gJ2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi5pbnRlcmNoYW5nZSc7XG4vLyBpbXBvcnQgeyBNYWdlbGxhbiB9IGZyb20gJ2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi5tYWdlbGxhbic7XG4vLyBpbXBvcnQgeyBPZmZDYW52YXMgfSBmcm9tICdmb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24ub2ZmY2FudmFzJztcbi8vIGltcG9ydCB7IE9yYml0IH0gZnJvbSAnZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLm9yYml0Jztcbi8vIGltcG9ydCB7IFJlc3BvbnNpdmVNZW51IH0gZnJvbSAnZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLnJlc3BvbnNpdmVNZW51Jztcbi8vIGltcG9ydCB7IFJlc3BvbnNpdmVUb2dnbGUgfSBmcm9tICdmb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24ucmVzcG9uc2l2ZVRvZ2dsZSc7XG4vLyBpbXBvcnQgeyBSZXZlYWwgfSBmcm9tICdmb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24ucmV2ZWFsJztcbi8vIGltcG9ydCB7IFNsaWRlciB9IGZyb20gJ2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi5zbGlkZXInO1xuLy8gaW1wb3J0IHsgU21vb3RoU2Nyb2xsIH0gZnJvbSAnZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLnNtb290aFNjcm9sbCc7XG5pbXBvcnQgeyBTdGlja3kgfSBmcm9tICdmb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24uc3RpY2t5Jztcbi8vIGltcG9ydCB7IFRhYnMgfSBmcm9tICdmb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24udGFicyc7XG5pbXBvcnQgeyBUb2dnbGVyIH0gZnJvbSAnZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLnRvZ2dsZXInO1xuLy8gaW1wb3J0IHsgVG9vbHRpcCB9IGZyb20gJ2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi50b29sdGlwJztcbi8vIGltcG9ydCB7IFJlc3BvbnNpdmVBY2NvcmRpb25UYWJzIH0gZnJvbSAnZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLnJlc3BvbnNpdmVBY2NvcmRpb25UYWJzJztcblxuXG5Gb3VuZGF0aW9uLmFkZFRvSnF1ZXJ5KCAkICk7XG5cbi8vIEFkZCBGb3VuZGF0aW9uIFV0aWxzIHRvIEZvdW5kYXRpb24gZ2xvYmFsIG5hbWVzcGFjZSBmb3IgYmFja3dhcmRzXG4vLyBjb21wYXRpYmlsaXR5LlxuRm91bmRhdGlvbi5ydGwgICAgICAgICAgID0gcnRsO1xuRm91bmRhdGlvbi5HZXRZb0RpZ2l0cyAgID0gR2V0WW9EaWdpdHM7XG5Gb3VuZGF0aW9uLnRyYW5zaXRpb25lbmQgPSB0cmFuc2l0aW9uZW5kO1xuXG5Gb3VuZGF0aW9uLkJveCAgICAgICAgICAgID0gQm94O1xuRm91bmRhdGlvbi5vbkltYWdlc0xvYWRlZCA9IG9uSW1hZ2VzTG9hZGVkO1xuRm91bmRhdGlvbi5LZXlib2FyZCAgICAgICA9IEtleWJvYXJkO1xuRm91bmRhdGlvbi5NZWRpYVF1ZXJ5ICAgICA9IE1lZGlhUXVlcnk7XG5Gb3VuZGF0aW9uLk1vdGlvbiAgICAgICAgID0gTW90aW9uO1xuRm91bmRhdGlvbi5Nb3ZlICAgICAgICAgICA9IE1vdmU7XG5Gb3VuZGF0aW9uLk5lc3QgICAgICAgICAgID0gTmVzdDtcbkZvdW5kYXRpb24uVGltZXIgICAgICAgICAgPSBUaW1lcjtcblxuLy8gVG91Y2ggYW5kIFRyaWdnZXJzIHByZXZpb3VzbHkgd2VyZSBhbG1vc3QgcHVyZWx5IHNlZGUgZWZmZWN0IGRyaXZlbixcbi8vIHNvIG5vIC8vIG5lZWQgdG8gYWRkIGl0IHRvIEZvdW5kYXRpb24sIGp1c3QgaW5pdCB0aGVtLlxuVG91Y2guaW5pdCggJCApO1xuXG5UcmlnZ2Vycy5pbml0KCAkLCBGb3VuZGF0aW9uICk7XG5cbi8vIEZvdW5kYXRpb24ucGx1Z2luKCBBYmlkZSwgJ0FiaWRlJyApO1xuLy8gRm91bmRhdGlvbi5wbHVnaW4oIEFjY29yZGlvbiwgJ0FjY29yZGlvbicgKTtcbi8vIEZvdW5kYXRpb24ucGx1Z2luKCBBY2NvcmRpb25NZW51LCAnQWNjb3JkaW9uTWVudScgKTtcbi8vIEZvdW5kYXRpb24ucGx1Z2luKCBEcmlsbGRvd24sICdEcmlsbGRvd24nICk7XG4vLyBGb3VuZGF0aW9uLnBsdWdpbiggRHJvcGRvd24sICdEcm9wZG93bicgKTtcbi8vIEZvdW5kYXRpb24ucGx1Z2luKCBEcm9wZG93bk1lbnUsICdEcm9wZG93bk1lbnUnICk7XG4vLyBGb3VuZGF0aW9uLnBsdWdpbiggRXF1YWxpemVyLCAnRXF1YWxpemVyJyApO1xuLy8gRm91bmRhdGlvbi5wbHVnaW4oIEludGVyY2hhbmdlLCAnSW50ZXJjaGFuZ2UnICk7XG4vLyBGb3VuZGF0aW9uLnBsdWdpbiggTWFnZWxsYW4sICdNYWdlbGxhbicgKTtcbi8vIEZvdW5kYXRpb24ucGx1Z2luKCBPZmZDYW52YXMsICdPZmZDYW52YXMnICk7XG4vLyBGb3VuZGF0aW9uLnBsdWdpbiggT3JiaXQsICdPcmJpdCcgKTtcbi8vIEZvdW5kYXRpb24ucGx1Z2luKCBSZXNwb25zaXZlTWVudSwgJ1Jlc3BvbnNpdmVNZW51JyApO1xuLy8gRm91bmRhdGlvbi5wbHVnaW4oIFJlc3BvbnNpdmVUb2dnbGUsICdSZXNwb25zaXZlVG9nZ2xlJyApO1xuLy8gRm91bmRhdGlvbi5wbHVnaW4oIFJldmVhbCwgJ1JldmVhbCcgKTtcbi8vIEZvdW5kYXRpb24ucGx1Z2luKCBTbGlkZXIsICdTbGlkZXInICk7XG4vLyBGb3VuZGF0aW9uLnBsdWdpbiggU21vb3RoU2Nyb2xsLCAnU21vb3RoU2Nyb2xsJyApO1xuRm91bmRhdGlvbi5wbHVnaW4oIFN0aWNreSwgJ1N0aWNreScgKTtcbi8vIEZvdW5kYXRpb24ucGx1Z2luKCBUYWJzLCAnVGFicycgKTtcbkZvdW5kYXRpb24ucGx1Z2luKCBUb2dnbGVyLCAnVG9nZ2xlcicgKTtcbi8vIEZvdW5kYXRpb24ucGx1Z2luKCBUb29sdGlwLCAnVG9vbHRpcCcgKTtcbi8vIEZvdW5kYXRpb24ucGx1Z2luKCBSZXNwb25zaXZlQWNjb3JkaW9uVGFicywgJ1Jlc3BvbnNpdmVBY2NvcmRpb25UYWJzJyApO1xuXG5tb2R1bGUuZXhwb3J0cyA9IEZvdW5kYXRpb247XG5cblxuXG4vLyBXRUJQQUNLIEZPT1RFUiAvL1xuLy8gLi9zcmMvYXNzZXRzL2pzL2xpYi9mb3VuZGF0aW9uLWV4cGxpY2l0LXBpZWNlcy5qcyIsIlwidXNlIHN0cmljdFwiO1xuXG5pbXBvcnQgJCBmcm9tICdqcXVlcnknO1xuaW1wb3J0IHsgR2V0WW9EaWdpdHMgfSBmcm9tICcuL2ZvdW5kYXRpb24uY29yZS51dGlscyc7XG5pbXBvcnQgeyBNZWRpYVF1ZXJ5IH0gZnJvbSAnLi9mb3VuZGF0aW9uLnV0aWwubWVkaWFRdWVyeSc7XG5cbnZhciBGT1VOREFUSU9OX1ZFUlNJT04gPSAnNi41LjEnO1xuXG4vLyBHbG9iYWwgRm91bmRhdGlvbiBvYmplY3Rcbi8vIFRoaXMgaXMgYXR0YWNoZWQgdG8gdGhlIHdpbmRvdywgb3IgdXNlZCBhcyBhIG1vZHVsZSBmb3IgQU1EL0Jyb3dzZXJpZnlcbnZhciBGb3VuZGF0aW9uID0ge1xuICB2ZXJzaW9uOiBGT1VOREFUSU9OX1ZFUlNJT04sXG5cbiAgLyoqXG4gICAqIFN0b3JlcyBpbml0aWFsaXplZCBwbHVnaW5zLlxuICAgKi9cbiAgX3BsdWdpbnM6IHt9LFxuXG4gIC8qKlxuICAgKiBTdG9yZXMgZ2VuZXJhdGVkIHVuaXF1ZSBpZHMgZm9yIHBsdWdpbiBpbnN0YW5jZXNcbiAgICovXG4gIF91dWlkczogW10sXG5cbiAgLyoqXG4gICAqIERlZmluZXMgYSBGb3VuZGF0aW9uIHBsdWdpbiwgYWRkaW5nIGl0IHRvIHRoZSBgRm91bmRhdGlvbmAgbmFtZXNwYWNlIGFuZCB0aGUgbGlzdCBvZiBwbHVnaW5zIHRvIGluaXRpYWxpemUgd2hlbiByZWZsb3dpbmcuXG4gICAqIEBwYXJhbSB7T2JqZWN0fSBwbHVnaW4gLSBUaGUgY29uc3RydWN0b3Igb2YgdGhlIHBsdWdpbi5cbiAgICovXG4gIHBsdWdpbjogZnVuY3Rpb24ocGx1Z2luLCBuYW1lKSB7XG4gICAgLy8gT2JqZWN0IGtleSB0byB1c2Ugd2hlbiBhZGRpbmcgdG8gZ2xvYmFsIEZvdW5kYXRpb24gb2JqZWN0XG4gICAgLy8gRXhhbXBsZXM6IEZvdW5kYXRpb24uUmV2ZWFsLCBGb3VuZGF0aW9uLk9mZkNhbnZhc1xuICAgIHZhciBjbGFzc05hbWUgPSAobmFtZSB8fCBmdW5jdGlvbk5hbWUocGx1Z2luKSk7XG4gICAgLy8gT2JqZWN0IGtleSB0byB1c2Ugd2hlbiBzdG9yaW5nIHRoZSBwbHVnaW4sIGFsc28gdXNlZCB0byBjcmVhdGUgdGhlIGlkZW50aWZ5aW5nIGRhdGEgYXR0cmlidXRlIGZvciB0aGUgcGx1Z2luXG4gICAgLy8gRXhhbXBsZXM6IGRhdGEtcmV2ZWFsLCBkYXRhLW9mZi1jYW52YXNcbiAgICB2YXIgYXR0ck5hbWUgID0gaHlwaGVuYXRlKGNsYXNzTmFtZSk7XG5cbiAgICAvLyBBZGQgdG8gdGhlIEZvdW5kYXRpb24gb2JqZWN0IGFuZCB0aGUgcGx1Z2lucyBsaXN0IChmb3IgcmVmbG93aW5nKVxuICAgIHRoaXMuX3BsdWdpbnNbYXR0ck5hbWVdID0gdGhpc1tjbGFzc05hbWVdID0gcGx1Z2luO1xuICB9LFxuICAvKipcbiAgICogQGZ1bmN0aW9uXG4gICAqIFBvcHVsYXRlcyB0aGUgX3V1aWRzIGFycmF5IHdpdGggcG9pbnRlcnMgdG8gZWFjaCBpbmRpdmlkdWFsIHBsdWdpbiBpbnN0YW5jZS5cbiAgICogQWRkcyB0aGUgYHpmUGx1Z2luYCBkYXRhLWF0dHJpYnV0ZSB0byBwcm9ncmFtbWF0aWNhbGx5IGNyZWF0ZWQgcGx1Z2lucyB0byBhbGxvdyB1c2Ugb2YgJChzZWxlY3RvcikuZm91bmRhdGlvbihtZXRob2QpIGNhbGxzLlxuICAgKiBBbHNvIGZpcmVzIHRoZSBpbml0aWFsaXphdGlvbiBldmVudCBmb3IgZWFjaCBwbHVnaW4sIGNvbnNvbGlkYXRpbmcgcmVwZXRpdGl2ZSBjb2RlLlxuICAgKiBAcGFyYW0ge09iamVjdH0gcGx1Z2luIC0gYW4gaW5zdGFuY2Ugb2YgYSBwbHVnaW4sIHVzdWFsbHkgYHRoaXNgIGluIGNvbnRleHQuXG4gICAqIEBwYXJhbSB7U3RyaW5nfSBuYW1lIC0gdGhlIG5hbWUgb2YgdGhlIHBsdWdpbiwgcGFzc2VkIGFzIGEgY2FtZWxDYXNlZCBzdHJpbmcuXG4gICAqIEBmaXJlcyBQbHVnaW4jaW5pdFxuICAgKi9cbiAgcmVnaXN0ZXJQbHVnaW46IGZ1bmN0aW9uKHBsdWdpbiwgbmFtZSl7XG4gICAgdmFyIHBsdWdpbk5hbWUgPSBuYW1lID8gaHlwaGVuYXRlKG5hbWUpIDogZnVuY3Rpb25OYW1lKHBsdWdpbi5jb25zdHJ1Y3RvcikudG9Mb3dlckNhc2UoKTtcbiAgICBwbHVnaW4udXVpZCA9IEdldFlvRGlnaXRzKDYsIHBsdWdpbk5hbWUpO1xuXG4gICAgaWYoIXBsdWdpbi4kZWxlbWVudC5hdHRyKGBkYXRhLSR7cGx1Z2luTmFtZX1gKSl7IHBsdWdpbi4kZWxlbWVudC5hdHRyKGBkYXRhLSR7cGx1Z2luTmFtZX1gLCBwbHVnaW4udXVpZCk7IH1cbiAgICBpZighcGx1Z2luLiRlbGVtZW50LmRhdGEoJ3pmUGx1Z2luJykpeyBwbHVnaW4uJGVsZW1lbnQuZGF0YSgnemZQbHVnaW4nLCBwbHVnaW4pOyB9XG4gICAgICAgICAgLyoqXG4gICAgICAgICAgICogRmlyZXMgd2hlbiB0aGUgcGx1Z2luIGhhcyBpbml0aWFsaXplZC5cbiAgICAgICAgICAgKiBAZXZlbnQgUGx1Z2luI2luaXRcbiAgICAgICAgICAgKi9cbiAgICBwbHVnaW4uJGVsZW1lbnQudHJpZ2dlcihgaW5pdC56Zi4ke3BsdWdpbk5hbWV9YCk7XG5cbiAgICB0aGlzLl91dWlkcy5wdXNoKHBsdWdpbi51dWlkKTtcblxuICAgIHJldHVybjtcbiAgfSxcbiAgLyoqXG4gICAqIEBmdW5jdGlvblxuICAgKiBSZW1vdmVzIHRoZSBwbHVnaW5zIHV1aWQgZnJvbSB0aGUgX3V1aWRzIGFycmF5LlxuICAgKiBSZW1vdmVzIHRoZSB6ZlBsdWdpbiBkYXRhIGF0dHJpYnV0ZSwgYXMgd2VsbCBhcyB0aGUgZGF0YS1wbHVnaW4tbmFtZSBhdHRyaWJ1dGUuXG4gICAqIEFsc28gZmlyZXMgdGhlIGRlc3Ryb3llZCBldmVudCBmb3IgdGhlIHBsdWdpbiwgY29uc29saWRhdGluZyByZXBldGl0aXZlIGNvZGUuXG4gICAqIEBwYXJhbSB7T2JqZWN0fSBwbHVnaW4gLSBhbiBpbnN0YW5jZSBvZiBhIHBsdWdpbiwgdXN1YWxseSBgdGhpc2AgaW4gY29udGV4dC5cbiAgICogQGZpcmVzIFBsdWdpbiNkZXN0cm95ZWRcbiAgICovXG4gIHVucmVnaXN0ZXJQbHVnaW46IGZ1bmN0aW9uKHBsdWdpbil7XG4gICAgdmFyIHBsdWdpbk5hbWUgPSBoeXBoZW5hdGUoZnVuY3Rpb25OYW1lKHBsdWdpbi4kZWxlbWVudC5kYXRhKCd6ZlBsdWdpbicpLmNvbnN0cnVjdG9yKSk7XG5cbiAgICB0aGlzLl91dWlkcy5zcGxpY2UodGhpcy5fdXVpZHMuaW5kZXhPZihwbHVnaW4udXVpZCksIDEpO1xuICAgIHBsdWdpbi4kZWxlbWVudC5yZW1vdmVBdHRyKGBkYXRhLSR7cGx1Z2luTmFtZX1gKS5yZW1vdmVEYXRhKCd6ZlBsdWdpbicpXG4gICAgICAgICAgLyoqXG4gICAgICAgICAgICogRmlyZXMgd2hlbiB0aGUgcGx1Z2luIGhhcyBiZWVuIGRlc3Ryb3llZC5cbiAgICAgICAgICAgKiBAZXZlbnQgUGx1Z2luI2Rlc3Ryb3llZFxuICAgICAgICAgICAqL1xuICAgICAgICAgIC50cmlnZ2VyKGBkZXN0cm95ZWQuemYuJHtwbHVnaW5OYW1lfWApO1xuICAgIGZvcih2YXIgcHJvcCBpbiBwbHVnaW4pe1xuICAgICAgcGx1Z2luW3Byb3BdID0gbnVsbDsvL2NsZWFuIHVwIHNjcmlwdCB0byBwcmVwIGZvciBnYXJiYWdlIGNvbGxlY3Rpb24uXG4gICAgfVxuICAgIHJldHVybjtcbiAgfSxcblxuICAvKipcbiAgICogQGZ1bmN0aW9uXG4gICAqIENhdXNlcyBvbmUgb3IgbW9yZSBhY3RpdmUgcGx1Z2lucyB0byByZS1pbml0aWFsaXplLCByZXNldHRpbmcgZXZlbnQgbGlzdGVuZXJzLCByZWNhbGN1bGF0aW5nIHBvc2l0aW9ucywgZXRjLlxuICAgKiBAcGFyYW0ge1N0cmluZ30gcGx1Z2lucyAtIG9wdGlvbmFsIHN0cmluZyBvZiBhbiBpbmRpdmlkdWFsIHBsdWdpbiBrZXksIGF0dGFpbmVkIGJ5IGNhbGxpbmcgYCQoZWxlbWVudCkuZGF0YSgncGx1Z2luTmFtZScpYCwgb3Igc3RyaW5nIG9mIGEgcGx1Z2luIGNsYXNzIGkuZS4gYCdkcm9wZG93bidgXG4gICAqIEBkZWZhdWx0IElmIG5vIGFyZ3VtZW50IGlzIHBhc3NlZCwgcmVmbG93IGFsbCBjdXJyZW50bHkgYWN0aXZlIHBsdWdpbnMuXG4gICAqL1xuICAgcmVJbml0OiBmdW5jdGlvbihwbHVnaW5zKXtcbiAgICAgdmFyIGlzSlEgPSBwbHVnaW5zIGluc3RhbmNlb2YgJDtcbiAgICAgdHJ5e1xuICAgICAgIGlmKGlzSlEpe1xuICAgICAgICAgcGx1Z2lucy5lYWNoKGZ1bmN0aW9uKCl7XG4gICAgICAgICAgICQodGhpcykuZGF0YSgnemZQbHVnaW4nKS5faW5pdCgpO1xuICAgICAgICAgfSk7XG4gICAgICAgfWVsc2V7XG4gICAgICAgICB2YXIgdHlwZSA9IHR5cGVvZiBwbHVnaW5zLFxuICAgICAgICAgX3RoaXMgPSB0aGlzLFxuICAgICAgICAgZm5zID0ge1xuICAgICAgICAgICAnb2JqZWN0JzogZnVuY3Rpb24ocGxncyl7XG4gICAgICAgICAgICAgcGxncy5mb3JFYWNoKGZ1bmN0aW9uKHApe1xuICAgICAgICAgICAgICAgcCA9IGh5cGhlbmF0ZShwKTtcbiAgICAgICAgICAgICAgICQoJ1tkYXRhLScrIHAgKyddJykuZm91bmRhdGlvbignX2luaXQnKTtcbiAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgfSxcbiAgICAgICAgICAgJ3N0cmluZyc6IGZ1bmN0aW9uKCl7XG4gICAgICAgICAgICAgcGx1Z2lucyA9IGh5cGhlbmF0ZShwbHVnaW5zKTtcbiAgICAgICAgICAgICAkKCdbZGF0YS0nKyBwbHVnaW5zICsnXScpLmZvdW5kYXRpb24oJ19pbml0Jyk7XG4gICAgICAgICAgIH0sXG4gICAgICAgICAgICd1bmRlZmluZWQnOiBmdW5jdGlvbigpe1xuICAgICAgICAgICAgIHRoaXNbJ29iamVjdCddKE9iamVjdC5rZXlzKF90aGlzLl9wbHVnaW5zKSk7XG4gICAgICAgICAgIH1cbiAgICAgICAgIH07XG4gICAgICAgICBmbnNbdHlwZV0ocGx1Z2lucyk7XG4gICAgICAgfVxuICAgICB9Y2F0Y2goZXJyKXtcbiAgICAgICBjb25zb2xlLmVycm9yKGVycik7XG4gICAgIH1maW5hbGx5e1xuICAgICAgIHJldHVybiBwbHVnaW5zO1xuICAgICB9XG4gICB9LFxuXG4gIC8qKlxuICAgKiBJbml0aWFsaXplIHBsdWdpbnMgb24gYW55IGVsZW1lbnRzIHdpdGhpbiBgZWxlbWAgKGFuZCBgZWxlbWAgaXRzZWxmKSB0aGF0IGFyZW4ndCBhbHJlYWR5IGluaXRpYWxpemVkLlxuICAgKiBAcGFyYW0ge09iamVjdH0gZWxlbSAtIGpRdWVyeSBvYmplY3QgY29udGFpbmluZyB0aGUgZWxlbWVudCB0byBjaGVjayBpbnNpZGUuIEFsc28gY2hlY2tzIHRoZSBlbGVtZW50IGl0c2VsZiwgdW5sZXNzIGl0J3MgdGhlIGBkb2N1bWVudGAgb2JqZWN0LlxuICAgKiBAcGFyYW0ge1N0cmluZ3xBcnJheX0gcGx1Z2lucyAtIEEgbGlzdCBvZiBwbHVnaW5zIHRvIGluaXRpYWxpemUuIExlYXZlIHRoaXMgb3V0IHRvIGluaXRpYWxpemUgZXZlcnl0aGluZy5cbiAgICovXG4gIHJlZmxvdzogZnVuY3Rpb24oZWxlbSwgcGx1Z2lucykge1xuXG4gICAgLy8gSWYgcGx1Z2lucyBpcyB1bmRlZmluZWQsIGp1c3QgZ3JhYiBldmVyeXRoaW5nXG4gICAgaWYgKHR5cGVvZiBwbHVnaW5zID09PSAndW5kZWZpbmVkJykge1xuICAgICAgcGx1Z2lucyA9IE9iamVjdC5rZXlzKHRoaXMuX3BsdWdpbnMpO1xuICAgIH1cbiAgICAvLyBJZiBwbHVnaW5zIGlzIGEgc3RyaW5nLCBjb252ZXJ0IGl0IHRvIGFuIGFycmF5IHdpdGggb25lIGl0ZW1cbiAgICBlbHNlIGlmICh0eXBlb2YgcGx1Z2lucyA9PT0gJ3N0cmluZycpIHtcbiAgICAgIHBsdWdpbnMgPSBbcGx1Z2luc107XG4gICAgfVxuXG4gICAgdmFyIF90aGlzID0gdGhpcztcblxuICAgIC8vIEl0ZXJhdGUgdGhyb3VnaCBlYWNoIHBsdWdpblxuICAgICQuZWFjaChwbHVnaW5zLCBmdW5jdGlvbihpLCBuYW1lKSB7XG4gICAgICAvLyBHZXQgdGhlIGN1cnJlbnQgcGx1Z2luXG4gICAgICB2YXIgcGx1Z2luID0gX3RoaXMuX3BsdWdpbnNbbmFtZV07XG5cbiAgICAgIC8vIExvY2FsaXplIHRoZSBzZWFyY2ggdG8gYWxsIGVsZW1lbnRzIGluc2lkZSBlbGVtLCBhcyB3ZWxsIGFzIGVsZW0gaXRzZWxmLCB1bmxlc3MgZWxlbSA9PT0gZG9jdW1lbnRcbiAgICAgIHZhciAkZWxlbSA9ICQoZWxlbSkuZmluZCgnW2RhdGEtJytuYW1lKyddJykuYWRkQmFjaygnW2RhdGEtJytuYW1lKyddJyk7XG5cbiAgICAgIC8vIEZvciBlYWNoIHBsdWdpbiBmb3VuZCwgaW5pdGlhbGl6ZSBpdFxuICAgICAgJGVsZW0uZWFjaChmdW5jdGlvbigpIHtcbiAgICAgICAgdmFyICRlbCA9ICQodGhpcyksXG4gICAgICAgICAgICBvcHRzID0ge307XG4gICAgICAgIC8vIERvbid0IGRvdWJsZS1kaXAgb24gcGx1Z2luc1xuICAgICAgICBpZiAoJGVsLmRhdGEoJ3pmUGx1Z2luJykpIHtcbiAgICAgICAgICBjb25zb2xlLndhcm4oXCJUcmllZCB0byBpbml0aWFsaXplIFwiK25hbWUrXCIgb24gYW4gZWxlbWVudCB0aGF0IGFscmVhZHkgaGFzIGEgRm91bmRhdGlvbiBwbHVnaW4uXCIpO1xuICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuXG4gICAgICAgIGlmKCRlbC5hdHRyKCdkYXRhLW9wdGlvbnMnKSl7XG4gICAgICAgICAgdmFyIHRoaW5nID0gJGVsLmF0dHIoJ2RhdGEtb3B0aW9ucycpLnNwbGl0KCc7JykuZm9yRWFjaChmdW5jdGlvbihlLCBpKXtcbiAgICAgICAgICAgIHZhciBvcHQgPSBlLnNwbGl0KCc6JykubWFwKGZ1bmN0aW9uKGVsKXsgcmV0dXJuIGVsLnRyaW0oKTsgfSk7XG4gICAgICAgICAgICBpZihvcHRbMF0pIG9wdHNbb3B0WzBdXSA9IHBhcnNlVmFsdWUob3B0WzFdKTtcbiAgICAgICAgICB9KTtcbiAgICAgICAgfVxuICAgICAgICB0cnl7XG4gICAgICAgICAgJGVsLmRhdGEoJ3pmUGx1Z2luJywgbmV3IHBsdWdpbigkKHRoaXMpLCBvcHRzKSk7XG4gICAgICAgIH1jYXRjaChlcil7XG4gICAgICAgICAgY29uc29sZS5lcnJvcihlcik7XG4gICAgICAgIH1maW5hbGx5e1xuICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuICAgICAgfSk7XG4gICAgfSk7XG4gIH0sXG4gIGdldEZuTmFtZTogZnVuY3Rpb25OYW1lLFxuXG4gIGFkZFRvSnF1ZXJ5OiBmdW5jdGlvbigkKSB7XG4gICAgLy8gVE9ETzogY29uc2lkZXIgbm90IG1ha2luZyB0aGlzIGEgalF1ZXJ5IGZ1bmN0aW9uXG4gICAgLy8gVE9ETzogbmVlZCB3YXkgdG8gcmVmbG93IHZzLiByZS1pbml0aWFsaXplXG4gICAgLyoqXG4gICAgICogVGhlIEZvdW5kYXRpb24galF1ZXJ5IG1ldGhvZC5cbiAgICAgKiBAcGFyYW0ge1N0cmluZ3xBcnJheX0gbWV0aG9kIC0gQW4gYWN0aW9uIHRvIHBlcmZvcm0gb24gdGhlIGN1cnJlbnQgalF1ZXJ5IG9iamVjdC5cbiAgICAgKi9cbiAgICB2YXIgZm91bmRhdGlvbiA9IGZ1bmN0aW9uKG1ldGhvZCkge1xuICAgICAgdmFyIHR5cGUgPSB0eXBlb2YgbWV0aG9kLFxuICAgICAgICAgICRub0pTID0gJCgnLm5vLWpzJyk7XG5cbiAgICAgIGlmKCRub0pTLmxlbmd0aCl7XG4gICAgICAgICRub0pTLnJlbW92ZUNsYXNzKCduby1qcycpO1xuICAgICAgfVxuXG4gICAgICBpZih0eXBlID09PSAndW5kZWZpbmVkJyl7Ly9uZWVkcyB0byBpbml0aWFsaXplIHRoZSBGb3VuZGF0aW9uIG9iamVjdCwgb3IgYW4gaW5kaXZpZHVhbCBwbHVnaW4uXG4gICAgICAgIE1lZGlhUXVlcnkuX2luaXQoKTtcbiAgICAgICAgRm91bmRhdGlvbi5yZWZsb3codGhpcyk7XG4gICAgICB9ZWxzZSBpZih0eXBlID09PSAnc3RyaW5nJyl7Ly9hbiBpbmRpdmlkdWFsIG1ldGhvZCB0byBpbnZva2Ugb24gYSBwbHVnaW4gb3IgZ3JvdXAgb2YgcGx1Z2luc1xuICAgICAgICB2YXIgYXJncyA9IEFycmF5LnByb3RvdHlwZS5zbGljZS5jYWxsKGFyZ3VtZW50cywgMSk7Ly9jb2xsZWN0IGFsbCB0aGUgYXJndW1lbnRzLCBpZiBuZWNlc3NhcnlcbiAgICAgICAgdmFyIHBsdWdDbGFzcyA9IHRoaXMuZGF0YSgnemZQbHVnaW4nKTsvL2RldGVybWluZSB0aGUgY2xhc3Mgb2YgcGx1Z2luXG5cbiAgICAgICAgaWYodHlwZW9mIHBsdWdDbGFzcyAhPT0gJ3VuZGVmaW5lZCcgJiYgdHlwZW9mIHBsdWdDbGFzc1ttZXRob2RdICE9PSAndW5kZWZpbmVkJyl7Ly9tYWtlIHN1cmUgYm90aCB0aGUgY2xhc3MgYW5kIG1ldGhvZCBleGlzdFxuICAgICAgICAgIGlmKHRoaXMubGVuZ3RoID09PSAxKXsvL2lmIHRoZXJlJ3Mgb25seSBvbmUsIGNhbGwgaXQgZGlyZWN0bHkuXG4gICAgICAgICAgICAgIHBsdWdDbGFzc1ttZXRob2RdLmFwcGx5KHBsdWdDbGFzcywgYXJncyk7XG4gICAgICAgICAgfWVsc2V7XG4gICAgICAgICAgICB0aGlzLmVhY2goZnVuY3Rpb24oaSwgZWwpey8vb3RoZXJ3aXNlIGxvb3AgdGhyb3VnaCB0aGUgalF1ZXJ5IGNvbGxlY3Rpb24gYW5kIGludm9rZSB0aGUgbWV0aG9kIG9uIGVhY2hcbiAgICAgICAgICAgICAgcGx1Z0NsYXNzW21ldGhvZF0uYXBwbHkoJChlbCkuZGF0YSgnemZQbHVnaW4nKSwgYXJncyk7XG4gICAgICAgICAgICB9KTtcbiAgICAgICAgICB9XG4gICAgICAgIH1lbHNley8vZXJyb3IgZm9yIG5vIGNsYXNzIG9yIG5vIG1ldGhvZFxuICAgICAgICAgIHRocm93IG5ldyBSZWZlcmVuY2VFcnJvcihcIldlJ3JlIHNvcnJ5LCAnXCIgKyBtZXRob2QgKyBcIicgaXMgbm90IGFuIGF2YWlsYWJsZSBtZXRob2QgZm9yIFwiICsgKHBsdWdDbGFzcyA/IGZ1bmN0aW9uTmFtZShwbHVnQ2xhc3MpIDogJ3RoaXMgZWxlbWVudCcpICsgJy4nKTtcbiAgICAgICAgfVxuICAgICAgfWVsc2V7Ly9lcnJvciBmb3IgaW52YWxpZCBhcmd1bWVudCB0eXBlXG4gICAgICAgIHRocm93IG5ldyBUeXBlRXJyb3IoYFdlJ3JlIHNvcnJ5LCAke3R5cGV9IGlzIG5vdCBhIHZhbGlkIHBhcmFtZXRlci4gWW91IG11c3QgdXNlIGEgc3RyaW5nIHJlcHJlc2VudGluZyB0aGUgbWV0aG9kIHlvdSB3aXNoIHRvIGludm9rZS5gKTtcbiAgICAgIH1cbiAgICAgIHJldHVybiB0aGlzO1xuICAgIH07XG4gICAgJC5mbi5mb3VuZGF0aW9uID0gZm91bmRhdGlvbjtcbiAgICByZXR1cm4gJDtcbiAgfVxufTtcblxuRm91bmRhdGlvbi51dGlsID0ge1xuICAvKipcbiAgICogRnVuY3Rpb24gZm9yIGFwcGx5aW5nIGEgZGVib3VuY2UgZWZmZWN0IHRvIGEgZnVuY3Rpb24gY2FsbC5cbiAgICogQGZ1bmN0aW9uXG4gICAqIEBwYXJhbSB7RnVuY3Rpb259IGZ1bmMgLSBGdW5jdGlvbiB0byBiZSBjYWxsZWQgYXQgZW5kIG9mIHRpbWVvdXQuXG4gICAqIEBwYXJhbSB7TnVtYmVyfSBkZWxheSAtIFRpbWUgaW4gbXMgdG8gZGVsYXkgdGhlIGNhbGwgb2YgYGZ1bmNgLlxuICAgKiBAcmV0dXJucyBmdW5jdGlvblxuICAgKi9cbiAgdGhyb3R0bGU6IGZ1bmN0aW9uIChmdW5jLCBkZWxheSkge1xuICAgIHZhciB0aW1lciA9IG51bGw7XG5cbiAgICByZXR1cm4gZnVuY3Rpb24gKCkge1xuICAgICAgdmFyIGNvbnRleHQgPSB0aGlzLCBhcmdzID0gYXJndW1lbnRzO1xuXG4gICAgICBpZiAodGltZXIgPT09IG51bGwpIHtcbiAgICAgICAgdGltZXIgPSBzZXRUaW1lb3V0KGZ1bmN0aW9uICgpIHtcbiAgICAgICAgICBmdW5jLmFwcGx5KGNvbnRleHQsIGFyZ3MpO1xuICAgICAgICAgIHRpbWVyID0gbnVsbDtcbiAgICAgICAgfSwgZGVsYXkpO1xuICAgICAgfVxuICAgIH07XG4gIH1cbn07XG5cbndpbmRvdy5Gb3VuZGF0aW9uID0gRm91bmRhdGlvbjtcblxuLy8gUG9seWZpbGwgZm9yIHJlcXVlc3RBbmltYXRpb25GcmFtZVxuKGZ1bmN0aW9uKCkge1xuICBpZiAoIURhdGUubm93IHx8ICF3aW5kb3cuRGF0ZS5ub3cpXG4gICAgd2luZG93LkRhdGUubm93ID0gRGF0ZS5ub3cgPSBmdW5jdGlvbigpIHsgcmV0dXJuIG5ldyBEYXRlKCkuZ2V0VGltZSgpOyB9O1xuXG4gIHZhciB2ZW5kb3JzID0gWyd3ZWJraXQnLCAnbW96J107XG4gIGZvciAodmFyIGkgPSAwOyBpIDwgdmVuZG9ycy5sZW5ndGggJiYgIXdpbmRvdy5yZXF1ZXN0QW5pbWF0aW9uRnJhbWU7ICsraSkge1xuICAgICAgdmFyIHZwID0gdmVuZG9yc1tpXTtcbiAgICAgIHdpbmRvdy5yZXF1ZXN0QW5pbWF0aW9uRnJhbWUgPSB3aW5kb3dbdnArJ1JlcXVlc3RBbmltYXRpb25GcmFtZSddO1xuICAgICAgd2luZG93LmNhbmNlbEFuaW1hdGlvbkZyYW1lID0gKHdpbmRvd1t2cCsnQ2FuY2VsQW5pbWF0aW9uRnJhbWUnXVxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfHwgd2luZG93W3ZwKydDYW5jZWxSZXF1ZXN0QW5pbWF0aW9uRnJhbWUnXSk7XG4gIH1cbiAgaWYgKC9pUChhZHxob25lfG9kKS4qT1MgNi8udGVzdCh3aW5kb3cubmF2aWdhdG9yLnVzZXJBZ2VudClcbiAgICB8fCAhd2luZG93LnJlcXVlc3RBbmltYXRpb25GcmFtZSB8fCAhd2luZG93LmNhbmNlbEFuaW1hdGlvbkZyYW1lKSB7XG4gICAgdmFyIGxhc3RUaW1lID0gMDtcbiAgICB3aW5kb3cucmVxdWVzdEFuaW1hdGlvbkZyYW1lID0gZnVuY3Rpb24oY2FsbGJhY2spIHtcbiAgICAgICAgdmFyIG5vdyA9IERhdGUubm93KCk7XG4gICAgICAgIHZhciBuZXh0VGltZSA9IE1hdGgubWF4KGxhc3RUaW1lICsgMTYsIG5vdyk7XG4gICAgICAgIHJldHVybiBzZXRUaW1lb3V0KGZ1bmN0aW9uKCkgeyBjYWxsYmFjayhsYXN0VGltZSA9IG5leHRUaW1lKTsgfSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgbmV4dFRpbWUgLSBub3cpO1xuICAgIH07XG4gICAgd2luZG93LmNhbmNlbEFuaW1hdGlvbkZyYW1lID0gY2xlYXJUaW1lb3V0O1xuICB9XG4gIC8qKlxuICAgKiBQb2x5ZmlsbCBmb3IgcGVyZm9ybWFuY2Uubm93LCByZXF1aXJlZCBieSByQUZcbiAgICovXG4gIGlmKCF3aW5kb3cucGVyZm9ybWFuY2UgfHwgIXdpbmRvdy5wZXJmb3JtYW5jZS5ub3cpe1xuICAgIHdpbmRvdy5wZXJmb3JtYW5jZSA9IHtcbiAgICAgIHN0YXJ0OiBEYXRlLm5vdygpLFxuICAgICAgbm93OiBmdW5jdGlvbigpeyByZXR1cm4gRGF0ZS5ub3coKSAtIHRoaXMuc3RhcnQ7IH1cbiAgICB9O1xuICB9XG59KSgpO1xuaWYgKCFGdW5jdGlvbi5wcm90b3R5cGUuYmluZCkge1xuICBGdW5jdGlvbi5wcm90b3R5cGUuYmluZCA9IGZ1bmN0aW9uKG9UaGlzKSB7XG4gICAgaWYgKHR5cGVvZiB0aGlzICE9PSAnZnVuY3Rpb24nKSB7XG4gICAgICAvLyBjbG9zZXN0IHRoaW5nIHBvc3NpYmxlIHRvIHRoZSBFQ01BU2NyaXB0IDVcbiAgICAgIC8vIGludGVybmFsIElzQ2FsbGFibGUgZnVuY3Rpb25cbiAgICAgIHRocm93IG5ldyBUeXBlRXJyb3IoJ0Z1bmN0aW9uLnByb3RvdHlwZS5iaW5kIC0gd2hhdCBpcyB0cnlpbmcgdG8gYmUgYm91bmQgaXMgbm90IGNhbGxhYmxlJyk7XG4gICAgfVxuXG4gICAgdmFyIGFBcmdzICAgPSBBcnJheS5wcm90b3R5cGUuc2xpY2UuY2FsbChhcmd1bWVudHMsIDEpLFxuICAgICAgICBmVG9CaW5kID0gdGhpcyxcbiAgICAgICAgZk5PUCAgICA9IGZ1bmN0aW9uKCkge30sXG4gICAgICAgIGZCb3VuZCAgPSBmdW5jdGlvbigpIHtcbiAgICAgICAgICByZXR1cm4gZlRvQmluZC5hcHBseSh0aGlzIGluc3RhbmNlb2YgZk5PUFxuICAgICAgICAgICAgICAgICA/IHRoaXNcbiAgICAgICAgICAgICAgICAgOiBvVGhpcyxcbiAgICAgICAgICAgICAgICAgYUFyZ3MuY29uY2F0KEFycmF5LnByb3RvdHlwZS5zbGljZS5jYWxsKGFyZ3VtZW50cykpKTtcbiAgICAgICAgfTtcblxuICAgIGlmICh0aGlzLnByb3RvdHlwZSkge1xuICAgICAgLy8gbmF0aXZlIGZ1bmN0aW9ucyBkb24ndCBoYXZlIGEgcHJvdG90eXBlXG4gICAgICBmTk9QLnByb3RvdHlwZSA9IHRoaXMucHJvdG90eXBlO1xuICAgIH1cbiAgICBmQm91bmQucHJvdG90eXBlID0gbmV3IGZOT1AoKTtcblxuICAgIHJldHVybiBmQm91bmQ7XG4gIH07XG59XG4vLyBQb2x5ZmlsbCB0byBnZXQgdGhlIG5hbWUgb2YgYSBmdW5jdGlvbiBpbiBJRTlcbmZ1bmN0aW9uIGZ1bmN0aW9uTmFtZShmbikge1xuICBpZiAodHlwZW9mIEZ1bmN0aW9uLnByb3RvdHlwZS5uYW1lID09PSAndW5kZWZpbmVkJykge1xuICAgIHZhciBmdW5jTmFtZVJlZ2V4ID0gL2Z1bmN0aW9uXFxzKFteKF17MSx9KVxcKC87XG4gICAgdmFyIHJlc3VsdHMgPSAoZnVuY05hbWVSZWdleCkuZXhlYygoZm4pLnRvU3RyaW5nKCkpO1xuICAgIHJldHVybiAocmVzdWx0cyAmJiByZXN1bHRzLmxlbmd0aCA+IDEpID8gcmVzdWx0c1sxXS50cmltKCkgOiBcIlwiO1xuICB9XG4gIGVsc2UgaWYgKHR5cGVvZiBmbi5wcm90b3R5cGUgPT09ICd1bmRlZmluZWQnKSB7XG4gICAgcmV0dXJuIGZuLmNvbnN0cnVjdG9yLm5hbWU7XG4gIH1cbiAgZWxzZSB7XG4gICAgcmV0dXJuIGZuLnByb3RvdHlwZS5jb25zdHJ1Y3Rvci5uYW1lO1xuICB9XG59XG5mdW5jdGlvbiBwYXJzZVZhbHVlKHN0cil7XG4gIGlmICgndHJ1ZScgPT09IHN0cikgcmV0dXJuIHRydWU7XG4gIGVsc2UgaWYgKCdmYWxzZScgPT09IHN0cikgcmV0dXJuIGZhbHNlO1xuICBlbHNlIGlmICghaXNOYU4oc3RyICogMSkpIHJldHVybiBwYXJzZUZsb2F0KHN0cik7XG4gIHJldHVybiBzdHI7XG59XG4vLyBDb252ZXJ0IFBhc2NhbENhc2UgdG8ga2ViYWItY2FzZVxuLy8gVGhhbmsgeW91OiBodHRwOi8vc3RhY2tvdmVyZmxvdy5jb20vYS84OTU1NTgwXG5mdW5jdGlvbiBoeXBoZW5hdGUoc3RyKSB7XG4gIHJldHVybiBzdHIucmVwbGFjZSgvKFthLXpdKShbQS1aXSkvZywgJyQxLSQyJykudG9Mb3dlckNhc2UoKTtcbn1cblxuZXhwb3J0IHtGb3VuZGF0aW9ufTtcblxuXG5cbi8vIFdFQlBBQ0sgRk9PVEVSIC8vXG4vLyAuL25vZGVfbW9kdWxlcy9mb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24uY29yZS5qcyIsIid1c2Ugc3RyaWN0JztcblxuaW1wb3J0ICQgZnJvbSAnanF1ZXJ5JztcbmltcG9ydCB7IG9uTG9hZCwgR2V0WW9EaWdpdHMgfSBmcm9tICcuL2ZvdW5kYXRpb24uY29yZS51dGlscyc7XG5pbXBvcnQgeyBNZWRpYVF1ZXJ5IH0gZnJvbSAnLi9mb3VuZGF0aW9uLnV0aWwubWVkaWFRdWVyeSc7XG5pbXBvcnQgeyBQbHVnaW4gfSBmcm9tICcuL2ZvdW5kYXRpb24uY29yZS5wbHVnaW4nO1xuaW1wb3J0IHsgVHJpZ2dlcnMgfSBmcm9tICcuL2ZvdW5kYXRpb24udXRpbC50cmlnZ2Vycyc7XG5cbi8qKlxuICogU3RpY2t5IG1vZHVsZS5cbiAqIEBtb2R1bGUgZm91bmRhdGlvbi5zdGlja3lcbiAqIEByZXF1aXJlcyBmb3VuZGF0aW9uLnV0aWwudHJpZ2dlcnNcbiAqIEByZXF1aXJlcyBmb3VuZGF0aW9uLnV0aWwubWVkaWFRdWVyeVxuICovXG5cbmNsYXNzIFN0aWNreSBleHRlbmRzIFBsdWdpbiB7XG4gIC8qKlxuICAgKiBDcmVhdGVzIGEgbmV3IGluc3RhbmNlIG9mIGEgc3RpY2t5IHRoaW5nLlxuICAgKiBAY2xhc3NcbiAgICogQG5hbWUgU3RpY2t5XG4gICAqIEBwYXJhbSB7alF1ZXJ5fSBlbGVtZW50IC0galF1ZXJ5IG9iamVjdCB0byBtYWtlIHN0aWNreS5cbiAgICogQHBhcmFtIHtPYmplY3R9IG9wdGlvbnMgLSBvcHRpb25zIG9iamVjdCBwYXNzZWQgd2hlbiBjcmVhdGluZyB0aGUgZWxlbWVudCBwcm9ncmFtbWF0aWNhbGx5LlxuICAgKi9cbiAgX3NldHVwKGVsZW1lbnQsIG9wdGlvbnMpIHtcbiAgICB0aGlzLiRlbGVtZW50ID0gZWxlbWVudDtcbiAgICB0aGlzLm9wdGlvbnMgPSAkLmV4dGVuZCh7fSwgU3RpY2t5LmRlZmF1bHRzLCB0aGlzLiRlbGVtZW50LmRhdGEoKSwgb3B0aW9ucyk7XG4gICAgdGhpcy5jbGFzc05hbWUgPSAnU3RpY2t5JzsgLy8gaWU5IGJhY2sgY29tcGF0XG5cbiAgICAvLyBUcmlnZ2VycyBpbml0IGlzIGlkZW1wb3RlbnQsIGp1c3QgbmVlZCB0byBtYWtlIHN1cmUgaXQgaXMgaW5pdGlhbGl6ZWRcbiAgICBUcmlnZ2Vycy5pbml0KCQpO1xuXG4gICAgdGhpcy5faW5pdCgpO1xuICB9XG5cbiAgLyoqXG4gICAqIEluaXRpYWxpemVzIHRoZSBzdGlja3kgZWxlbWVudCBieSBhZGRpbmcgY2xhc3NlcywgZ2V0dGluZy9zZXR0aW5nIGRpbWVuc2lvbnMsIGJyZWFrcG9pbnRzIGFuZCBhdHRyaWJ1dGVzXG4gICAqIEBmdW5jdGlvblxuICAgKiBAcHJpdmF0ZVxuICAgKi9cbiAgX2luaXQoKSB7XG4gICAgTWVkaWFRdWVyeS5faW5pdCgpO1xuXG4gICAgdmFyICRwYXJlbnQgPSB0aGlzLiRlbGVtZW50LnBhcmVudCgnW2RhdGEtc3RpY2t5LWNvbnRhaW5lcl0nKSxcbiAgICAgICAgaWQgPSB0aGlzLiRlbGVtZW50WzBdLmlkIHx8IEdldFlvRGlnaXRzKDYsICdzdGlja3knKSxcbiAgICAgICAgX3RoaXMgPSB0aGlzO1xuXG4gICAgaWYoJHBhcmVudC5sZW5ndGgpe1xuICAgICAgdGhpcy4kY29udGFpbmVyID0gJHBhcmVudDtcbiAgICB9IGVsc2Uge1xuICAgICAgdGhpcy53YXNXcmFwcGVkID0gdHJ1ZTtcbiAgICAgIHRoaXMuJGVsZW1lbnQud3JhcCh0aGlzLm9wdGlvbnMuY29udGFpbmVyKTtcbiAgICAgIHRoaXMuJGNvbnRhaW5lciA9IHRoaXMuJGVsZW1lbnQucGFyZW50KCk7XG4gICAgfVxuICAgIHRoaXMuJGNvbnRhaW5lci5hZGRDbGFzcyh0aGlzLm9wdGlvbnMuY29udGFpbmVyQ2xhc3MpO1xuXG4gICAgdGhpcy4kZWxlbWVudC5hZGRDbGFzcyh0aGlzLm9wdGlvbnMuc3RpY2t5Q2xhc3MpLmF0dHIoeyAnZGF0YS1yZXNpemUnOiBpZCwgJ2RhdGEtbXV0YXRlJzogaWQgfSk7XG4gICAgaWYgKHRoaXMub3B0aW9ucy5hbmNob3IgIT09ICcnKSB7XG4gICAgICAgICQoJyMnICsgX3RoaXMub3B0aW9ucy5hbmNob3IpLmF0dHIoeyAnZGF0YS1tdXRhdGUnOiBpZCB9KTtcbiAgICB9XG5cbiAgICB0aGlzLnNjcm9sbENvdW50ID0gdGhpcy5vcHRpb25zLmNoZWNrRXZlcnk7XG4gICAgdGhpcy5pc1N0dWNrID0gZmFsc2U7XG4gICAgdGhpcy5vbkxvYWRMaXN0ZW5lciA9IG9uTG9hZCgkKHdpbmRvdyksIGZ1bmN0aW9uICgpIHtcbiAgICAgIC8vV2UgY2FsY3VsYXRlIHRoZSBjb250YWluZXIgaGVpZ2h0IHRvIGhhdmUgY29ycmVjdCB2YWx1ZXMgZm9yIGFuY2hvciBwb2ludHMgb2Zmc2V0IGNhbGN1bGF0aW9uLlxuICAgICAgX3RoaXMuY29udGFpbmVySGVpZ2h0ID0gX3RoaXMuJGVsZW1lbnQuY3NzKFwiZGlzcGxheVwiKSA9PSBcIm5vbmVcIiA/IDAgOiBfdGhpcy4kZWxlbWVudFswXS5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKS5oZWlnaHQ7XG4gICAgICBfdGhpcy4kY29udGFpbmVyLmNzcygnaGVpZ2h0JywgX3RoaXMuY29udGFpbmVySGVpZ2h0KTtcbiAgICAgIF90aGlzLmVsZW1IZWlnaHQgPSBfdGhpcy5jb250YWluZXJIZWlnaHQ7XG4gICAgICBpZiAoX3RoaXMub3B0aW9ucy5hbmNob3IgIT09ICcnKSB7XG4gICAgICAgIF90aGlzLiRhbmNob3IgPSAkKCcjJyArIF90aGlzLm9wdGlvbnMuYW5jaG9yKTtcbiAgICAgIH0gZWxzZSB7XG4gICAgICAgIF90aGlzLl9wYXJzZVBvaW50cygpO1xuICAgICAgfVxuXG4gICAgICBfdGhpcy5fc2V0U2l6ZXMoZnVuY3Rpb24gKCkge1xuICAgICAgICB2YXIgc2Nyb2xsID0gd2luZG93LnBhZ2VZT2Zmc2V0O1xuICAgICAgICBfdGhpcy5fY2FsYyhmYWxzZSwgc2Nyb2xsKTtcbiAgICAgICAgLy9VbnN0aWNrIHRoZSBlbGVtZW50IHdpbGwgZW5zdXJlIHRoYXQgcHJvcGVyIGNsYXNzZXMgYXJlIHNldC5cbiAgICAgICAgaWYgKCFfdGhpcy5pc1N0dWNrKSB7XG4gICAgICAgICAgX3RoaXMuX3JlbW92ZVN0aWNreSgoc2Nyb2xsID49IF90aGlzLnRvcFBvaW50KSA/IGZhbHNlIDogdHJ1ZSk7XG4gICAgICAgIH1cbiAgICAgIH0pO1xuICAgICAgX3RoaXMuX2V2ZW50cyhpZC5zcGxpdCgnLScpLnJldmVyc2UoKS5qb2luKCctJykpO1xuICAgIH0pO1xuICB9XG5cbiAgLyoqXG4gICAqIElmIHVzaW5nIG11bHRpcGxlIGVsZW1lbnRzIGFzIGFuY2hvcnMsIGNhbGN1bGF0ZXMgdGhlIHRvcCBhbmQgYm90dG9tIHBpeGVsIHZhbHVlcyB0aGUgc3RpY2t5IHRoaW5nIHNob3VsZCBzdGljayBhbmQgdW5zdGljayBvbi5cbiAgICogQGZ1bmN0aW9uXG4gICAqIEBwcml2YXRlXG4gICAqL1xuICBfcGFyc2VQb2ludHMoKSB7XG4gICAgdmFyIHRvcCA9IHRoaXMub3B0aW9ucy50b3BBbmNob3IgPT0gXCJcIiA/IDEgOiB0aGlzLm9wdGlvbnMudG9wQW5jaG9yLFxuICAgICAgICBidG0gPSB0aGlzLm9wdGlvbnMuYnRtQW5jaG9yPT0gXCJcIiA/IGRvY3VtZW50LmRvY3VtZW50RWxlbWVudC5zY3JvbGxIZWlnaHQgOiB0aGlzLm9wdGlvbnMuYnRtQW5jaG9yLFxuICAgICAgICBwdHMgPSBbdG9wLCBidG1dLFxuICAgICAgICBicmVha3MgPSB7fTtcbiAgICBmb3IgKHZhciBpID0gMCwgbGVuID0gcHRzLmxlbmd0aDsgaSA8IGxlbiAmJiBwdHNbaV07IGkrKykge1xuICAgICAgdmFyIHB0O1xuICAgICAgaWYgKHR5cGVvZiBwdHNbaV0gPT09ICdudW1iZXInKSB7XG4gICAgICAgIHB0ID0gcHRzW2ldO1xuICAgICAgfSBlbHNlIHtcbiAgICAgICAgdmFyIHBsYWNlID0gcHRzW2ldLnNwbGl0KCc6JyksXG4gICAgICAgICAgICBhbmNob3IgPSAkKGAjJHtwbGFjZVswXX1gKTtcblxuICAgICAgICBwdCA9IGFuY2hvci5vZmZzZXQoKS50b3A7XG4gICAgICAgIGlmIChwbGFjZVsxXSAmJiBwbGFjZVsxXS50b0xvd2VyQ2FzZSgpID09PSAnYm90dG9tJykge1xuICAgICAgICAgIHB0ICs9IGFuY2hvclswXS5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKS5oZWlnaHQ7XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICAgIGJyZWFrc1tpXSA9IHB0O1xuICAgIH1cblxuXG4gICAgdGhpcy5wb2ludHMgPSBicmVha3M7XG4gICAgcmV0dXJuO1xuICB9XG5cbiAgLyoqXG4gICAqIEFkZHMgZXZlbnQgaGFuZGxlcnMgZm9yIHRoZSBzY3JvbGxpbmcgZWxlbWVudC5cbiAgICogQHByaXZhdGVcbiAgICogQHBhcmFtIHtTdHJpbmd9IGlkIC0gcHNldWRvLXJhbmRvbSBpZCBmb3IgdW5pcXVlIHNjcm9sbCBldmVudCBsaXN0ZW5lci5cbiAgICovXG4gIF9ldmVudHMoaWQpIHtcbiAgICB2YXIgX3RoaXMgPSB0aGlzLFxuICAgICAgICBzY3JvbGxMaXN0ZW5lciA9IHRoaXMuc2Nyb2xsTGlzdGVuZXIgPSBgc2Nyb2xsLnpmLiR7aWR9YDtcbiAgICBpZiAodGhpcy5pc09uKSB7IHJldHVybjsgfVxuICAgIGlmICh0aGlzLmNhblN0aWNrKSB7XG4gICAgICB0aGlzLmlzT24gPSB0cnVlO1xuICAgICAgJCh3aW5kb3cpLm9mZihzY3JvbGxMaXN0ZW5lcilcbiAgICAgICAgICAgICAgIC5vbihzY3JvbGxMaXN0ZW5lciwgZnVuY3Rpb24oZSkge1xuICAgICAgICAgICAgICAgICBpZiAoX3RoaXMuc2Nyb2xsQ291bnQgPT09IDApIHtcbiAgICAgICAgICAgICAgICAgICBfdGhpcy5zY3JvbGxDb3VudCA9IF90aGlzLm9wdGlvbnMuY2hlY2tFdmVyeTtcbiAgICAgICAgICAgICAgICAgICBfdGhpcy5fc2V0U2l6ZXMoZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICAgICAgICAgICBfdGhpcy5fY2FsYyhmYWxzZSwgd2luZG93LnBhZ2VZT2Zmc2V0KTtcbiAgICAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICAgICBfdGhpcy5zY3JvbGxDb3VudC0tO1xuICAgICAgICAgICAgICAgICAgIF90aGlzLl9jYWxjKGZhbHNlLCB3aW5kb3cucGFnZVlPZmZzZXQpO1xuICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgIH0pO1xuICAgIH1cblxuICAgIHRoaXMuJGVsZW1lbnQub2ZmKCdyZXNpemVtZS56Zi50cmlnZ2VyJylcbiAgICAgICAgICAgICAgICAgLm9uKCdyZXNpemVtZS56Zi50cmlnZ2VyJywgZnVuY3Rpb24oZSwgZWwpIHtcbiAgICAgICAgICAgICAgICAgICAgX3RoaXMuX2V2ZW50c0hhbmRsZXIoaWQpO1xuICAgIH0pO1xuXG4gICAgdGhpcy4kZWxlbWVudC5vbignbXV0YXRlbWUuemYudHJpZ2dlcicsIGZ1bmN0aW9uIChlLCBlbCkge1xuICAgICAgICBfdGhpcy5fZXZlbnRzSGFuZGxlcihpZCk7XG4gICAgfSk7XG5cbiAgICBpZih0aGlzLiRhbmNob3IpIHtcbiAgICAgIHRoaXMuJGFuY2hvci5vbignbXV0YXRlbWUuemYudHJpZ2dlcicsIGZ1bmN0aW9uIChlLCBlbCkge1xuICAgICAgICAgIF90aGlzLl9ldmVudHNIYW5kbGVyKGlkKTtcbiAgICAgIH0pO1xuICAgIH1cbiAgfVxuXG4gIC8qKlxuICAgKiBIYW5kbGVyIGZvciBldmVudHMuXG4gICAqIEBwcml2YXRlXG4gICAqIEBwYXJhbSB7U3RyaW5nfSBpZCAtIHBzZXVkby1yYW5kb20gaWQgZm9yIHVuaXF1ZSBzY3JvbGwgZXZlbnQgbGlzdGVuZXIuXG4gICAqL1xuICBfZXZlbnRzSGFuZGxlcihpZCkge1xuICAgICAgIHZhciBfdGhpcyA9IHRoaXMsXG4gICAgICAgIHNjcm9sbExpc3RlbmVyID0gdGhpcy5zY3JvbGxMaXN0ZW5lciA9IGBzY3JvbGwuemYuJHtpZH1gO1xuXG4gICAgICAgX3RoaXMuX3NldFNpemVzKGZ1bmN0aW9uKCkge1xuICAgICAgIF90aGlzLl9jYWxjKGZhbHNlKTtcbiAgICAgICBpZiAoX3RoaXMuY2FuU3RpY2spIHtcbiAgICAgICAgIGlmICghX3RoaXMuaXNPbikge1xuICAgICAgICAgICBfdGhpcy5fZXZlbnRzKGlkKTtcbiAgICAgICAgIH1cbiAgICAgICB9IGVsc2UgaWYgKF90aGlzLmlzT24pIHtcbiAgICAgICAgIF90aGlzLl9wYXVzZUxpc3RlbmVycyhzY3JvbGxMaXN0ZW5lcik7XG4gICAgICAgfVxuICAgICB9KTtcbiAgfVxuXG4gIC8qKlxuICAgKiBSZW1vdmVzIGV2ZW50IGhhbmRsZXJzIGZvciBzY3JvbGwgYW5kIGNoYW5nZSBldmVudHMgb24gYW5jaG9yLlxuICAgKiBAZmlyZXMgU3RpY2t5I3BhdXNlXG4gICAqIEBwYXJhbSB7U3RyaW5nfSBzY3JvbGxMaXN0ZW5lciAtIHVuaXF1ZSwgbmFtZXNwYWNlZCBzY3JvbGwgbGlzdGVuZXIgYXR0YWNoZWQgdG8gYHdpbmRvd2BcbiAgICovXG4gIF9wYXVzZUxpc3RlbmVycyhzY3JvbGxMaXN0ZW5lcikge1xuICAgIHRoaXMuaXNPbiA9IGZhbHNlO1xuICAgICQod2luZG93KS5vZmYoc2Nyb2xsTGlzdGVuZXIpO1xuXG4gICAgLyoqXG4gICAgICogRmlyZXMgd2hlbiB0aGUgcGx1Z2luIGlzIHBhdXNlZCBkdWUgdG8gcmVzaXplIGV2ZW50IHNocmlua2luZyB0aGUgdmlldy5cbiAgICAgKiBAZXZlbnQgU3RpY2t5I3BhdXNlXG4gICAgICogQHByaXZhdGVcbiAgICAgKi9cbiAgICAgdGhpcy4kZWxlbWVudC50cmlnZ2VyKCdwYXVzZS56Zi5zdGlja3knKTtcbiAgfVxuXG4gIC8qKlxuICAgKiBDYWxsZWQgb24gZXZlcnkgYHNjcm9sbGAgZXZlbnQgYW5kIG9uIGBfaW5pdGBcbiAgICogZmlyZXMgZnVuY3Rpb25zIGJhc2VkIG9uIGJvb2xlYW5zIGFuZCBjYWNoZWQgdmFsdWVzXG4gICAqIEBwYXJhbSB7Qm9vbGVhbn0gY2hlY2tTaXplcyAtIHRydWUgaWYgcGx1Z2luIHNob3VsZCByZWNhbGN1bGF0ZSBzaXplcyBhbmQgYnJlYWtwb2ludHMuXG4gICAqIEBwYXJhbSB7TnVtYmVyfSBzY3JvbGwgLSBjdXJyZW50IHNjcm9sbCBwb3NpdGlvbiBwYXNzZWQgZnJvbSBzY3JvbGwgZXZlbnQgY2IgZnVuY3Rpb24uIElmIG5vdCBwYXNzZWQsIGRlZmF1bHRzIHRvIGB3aW5kb3cucGFnZVlPZmZzZXRgLlxuICAgKi9cbiAgX2NhbGMoY2hlY2tTaXplcywgc2Nyb2xsKSB7XG4gICAgaWYgKGNoZWNrU2l6ZXMpIHsgdGhpcy5fc2V0U2l6ZXMoKTsgfVxuXG4gICAgaWYgKCF0aGlzLmNhblN0aWNrKSB7XG4gICAgICBpZiAodGhpcy5pc1N0dWNrKSB7XG4gICAgICAgIHRoaXMuX3JlbW92ZVN0aWNreSh0cnVlKTtcbiAgICAgIH1cbiAgICAgIHJldHVybiBmYWxzZTtcbiAgICB9XG5cbiAgICBpZiAoIXNjcm9sbCkgeyBzY3JvbGwgPSB3aW5kb3cucGFnZVlPZmZzZXQ7IH1cblxuICAgIGlmIChzY3JvbGwgPj0gdGhpcy50b3BQb2ludCkge1xuICAgICAgaWYgKHNjcm9sbCA8PSB0aGlzLmJvdHRvbVBvaW50KSB7XG4gICAgICAgIGlmICghdGhpcy5pc1N0dWNrKSB7XG4gICAgICAgICAgdGhpcy5fc2V0U3RpY2t5KCk7XG4gICAgICAgIH1cbiAgICAgIH0gZWxzZSB7XG4gICAgICAgIGlmICh0aGlzLmlzU3R1Y2spIHtcbiAgICAgICAgICB0aGlzLl9yZW1vdmVTdGlja3koZmFsc2UpO1xuICAgICAgICB9XG4gICAgICB9XG4gICAgfSBlbHNlIHtcbiAgICAgIGlmICh0aGlzLmlzU3R1Y2spIHtcbiAgICAgICAgdGhpcy5fcmVtb3ZlU3RpY2t5KHRydWUpO1xuICAgICAgfVxuICAgIH1cbiAgfVxuXG4gIC8qKlxuICAgKiBDYXVzZXMgdGhlICRlbGVtZW50IHRvIGJlY29tZSBzdHVjay5cbiAgICogQWRkcyBgcG9zaXRpb246IGZpeGVkO2AsIGFuZCBoZWxwZXIgY2xhc3Nlcy5cbiAgICogQGZpcmVzIFN0aWNreSNzdHVja3RvXG4gICAqIEBmdW5jdGlvblxuICAgKiBAcHJpdmF0ZVxuICAgKi9cbiAgX3NldFN0aWNreSgpIHtcbiAgICB2YXIgX3RoaXMgPSB0aGlzLFxuICAgICAgICBzdGlja1RvID0gdGhpcy5vcHRpb25zLnN0aWNrVG8sXG4gICAgICAgIG1yZ24gPSBzdGlja1RvID09PSAndG9wJyA/ICdtYXJnaW5Ub3AnIDogJ21hcmdpbkJvdHRvbScsXG4gICAgICAgIG5vdFN0dWNrVG8gPSBzdGlja1RvID09PSAndG9wJyA/ICdib3R0b20nIDogJ3RvcCcsXG4gICAgICAgIGNzcyA9IHt9O1xuXG4gICAgY3NzW21yZ25dID0gYCR7dGhpcy5vcHRpb25zW21yZ25dfWVtYDtcbiAgICBjc3Nbc3RpY2tUb10gPSAwO1xuICAgIGNzc1tub3RTdHVja1RvXSA9ICdhdXRvJztcbiAgICB0aGlzLmlzU3R1Y2sgPSB0cnVlO1xuICAgIHRoaXMuJGVsZW1lbnQucmVtb3ZlQ2xhc3MoYGlzLWFuY2hvcmVkIGlzLWF0LSR7bm90U3R1Y2tUb31gKVxuICAgICAgICAgICAgICAgICAuYWRkQ2xhc3MoYGlzLXN0dWNrIGlzLWF0LSR7c3RpY2tUb31gKVxuICAgICAgICAgICAgICAgICAuY3NzKGNzcylcbiAgICAgICAgICAgICAgICAgLyoqXG4gICAgICAgICAgICAgICAgICAqIEZpcmVzIHdoZW4gdGhlICRlbGVtZW50IGhhcyBiZWNvbWUgYHBvc2l0aW9uOiBmaXhlZDtgXG4gICAgICAgICAgICAgICAgICAqIE5hbWVzcGFjZWQgdG8gYHRvcGAgb3IgYGJvdHRvbWAsIGUuZy4gYHN0aWNreS56Zi5zdHVja3RvOnRvcGBcbiAgICAgICAgICAgICAgICAgICogQGV2ZW50IFN0aWNreSNzdHVja3RvXG4gICAgICAgICAgICAgICAgICAqL1xuICAgICAgICAgICAgICAgICAudHJpZ2dlcihgc3RpY2t5LnpmLnN0dWNrdG86JHtzdGlja1RvfWApO1xuICAgIHRoaXMuJGVsZW1lbnQub24oXCJ0cmFuc2l0aW9uZW5kIHdlYmtpdFRyYW5zaXRpb25FbmQgb1RyYW5zaXRpb25FbmQgb3RyYW5zaXRpb25lbmQgTVNUcmFuc2l0aW9uRW5kXCIsIGZ1bmN0aW9uKCkge1xuICAgICAgX3RoaXMuX3NldFNpemVzKCk7XG4gICAgfSk7XG4gIH1cblxuICAvKipcbiAgICogQ2F1c2VzIHRoZSAkZWxlbWVudCB0byBiZWNvbWUgdW5zdHVjay5cbiAgICogUmVtb3ZlcyBgcG9zaXRpb246IGZpeGVkO2AsIGFuZCBoZWxwZXIgY2xhc3Nlcy5cbiAgICogQWRkcyBvdGhlciBoZWxwZXIgY2xhc3Nlcy5cbiAgICogQHBhcmFtIHtCb29sZWFufSBpc1RvcCAtIHRlbGxzIHRoZSBmdW5jdGlvbiBpZiB0aGUgJGVsZW1lbnQgc2hvdWxkIGFuY2hvciB0byB0aGUgdG9wIG9yIGJvdHRvbSBvZiBpdHMgJGFuY2hvciBlbGVtZW50LlxuICAgKiBAZmlyZXMgU3RpY2t5I3Vuc3R1Y2tmcm9tXG4gICAqIEBwcml2YXRlXG4gICAqL1xuICBfcmVtb3ZlU3RpY2t5KGlzVG9wKSB7XG4gICAgdmFyIHN0aWNrVG8gPSB0aGlzLm9wdGlvbnMuc3RpY2tUbyxcbiAgICAgICAgc3RpY2tUb1RvcCA9IHN0aWNrVG8gPT09ICd0b3AnLFxuICAgICAgICBjc3MgPSB7fSxcbiAgICAgICAgYW5jaG9yUHQgPSAodGhpcy5wb2ludHMgPyB0aGlzLnBvaW50c1sxXSAtIHRoaXMucG9pbnRzWzBdIDogdGhpcy5hbmNob3JIZWlnaHQpIC0gdGhpcy5lbGVtSGVpZ2h0LFxuICAgICAgICBtcmduID0gc3RpY2tUb1RvcCA/ICdtYXJnaW5Ub3AnIDogJ21hcmdpbkJvdHRvbScsXG4gICAgICAgIG5vdFN0dWNrVG8gPSBzdGlja1RvVG9wID8gJ2JvdHRvbScgOiAndG9wJyxcbiAgICAgICAgdG9wT3JCb3R0b20gPSBpc1RvcCA/ICd0b3AnIDogJ2JvdHRvbSc7XG5cbiAgICBjc3NbbXJnbl0gPSAwO1xuXG4gICAgY3NzWydib3R0b20nXSA9ICdhdXRvJztcbiAgICBpZihpc1RvcCkge1xuICAgICAgY3NzWyd0b3AnXSA9IDA7XG4gICAgfSBlbHNlIHtcbiAgICAgIGNzc1sndG9wJ10gPSBhbmNob3JQdDtcbiAgICB9XG5cbiAgICB0aGlzLmlzU3R1Y2sgPSBmYWxzZTtcbiAgICB0aGlzLiRlbGVtZW50LnJlbW92ZUNsYXNzKGBpcy1zdHVjayBpcy1hdC0ke3N0aWNrVG99YClcbiAgICAgICAgICAgICAgICAgLmFkZENsYXNzKGBpcy1hbmNob3JlZCBpcy1hdC0ke3RvcE9yQm90dG9tfWApXG4gICAgICAgICAgICAgICAgIC5jc3MoY3NzKVxuICAgICAgICAgICAgICAgICAvKipcbiAgICAgICAgICAgICAgICAgICogRmlyZXMgd2hlbiB0aGUgJGVsZW1lbnQgaGFzIGJlY29tZSBhbmNob3JlZC5cbiAgICAgICAgICAgICAgICAgICogTmFtZXNwYWNlZCB0byBgdG9wYCBvciBgYm90dG9tYCwgZS5nLiBgc3RpY2t5LnpmLnVuc3R1Y2tmcm9tOmJvdHRvbWBcbiAgICAgICAgICAgICAgICAgICogQGV2ZW50IFN0aWNreSN1bnN0dWNrZnJvbVxuICAgICAgICAgICAgICAgICAgKi9cbiAgICAgICAgICAgICAgICAgLnRyaWdnZXIoYHN0aWNreS56Zi51bnN0dWNrZnJvbToke3RvcE9yQm90dG9tfWApO1xuICB9XG5cbiAgLyoqXG4gICAqIFNldHMgdGhlICRlbGVtZW50IGFuZCAkY29udGFpbmVyIHNpemVzIGZvciBwbHVnaW4uXG4gICAqIENhbGxzIGBfc2V0QnJlYWtQb2ludHNgLlxuICAgKiBAcGFyYW0ge0Z1bmN0aW9ufSBjYiAtIG9wdGlvbmFsIGNhbGxiYWNrIGZ1bmN0aW9uIHRvIGZpcmUgb24gY29tcGxldGlvbiBvZiBgX3NldEJyZWFrUG9pbnRzYC5cbiAgICogQHByaXZhdGVcbiAgICovXG4gIF9zZXRTaXplcyhjYikge1xuICAgIHRoaXMuY2FuU3RpY2sgPSBNZWRpYVF1ZXJ5LmlzKHRoaXMub3B0aW9ucy5zdGlja3lPbik7XG4gICAgaWYgKCF0aGlzLmNhblN0aWNrKSB7XG4gICAgICBpZiAoY2IgJiYgdHlwZW9mIGNiID09PSAnZnVuY3Rpb24nKSB7IGNiKCk7IH1cbiAgICB9XG4gICAgdmFyIF90aGlzID0gdGhpcyxcbiAgICAgICAgbmV3RWxlbVdpZHRoID0gdGhpcy4kY29udGFpbmVyWzBdLmdldEJvdW5kaW5nQ2xpZW50UmVjdCgpLndpZHRoLFxuICAgICAgICBjb21wID0gd2luZG93LmdldENvbXB1dGVkU3R5bGUodGhpcy4kY29udGFpbmVyWzBdKSxcbiAgICAgICAgcGRuZ2wgPSBwYXJzZUludChjb21wWydwYWRkaW5nLWxlZnQnXSwgMTApLFxuICAgICAgICBwZG5nciA9IHBhcnNlSW50KGNvbXBbJ3BhZGRpbmctcmlnaHQnXSwgMTApO1xuXG4gICAgaWYgKHRoaXMuJGFuY2hvciAmJiB0aGlzLiRhbmNob3IubGVuZ3RoKSB7XG4gICAgICB0aGlzLmFuY2hvckhlaWdodCA9IHRoaXMuJGFuY2hvclswXS5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKS5oZWlnaHQ7XG4gICAgfSBlbHNlIHtcbiAgICAgIHRoaXMuX3BhcnNlUG9pbnRzKCk7XG4gICAgfVxuXG4gICAgdGhpcy4kZWxlbWVudC5jc3Moe1xuICAgICAgJ21heC13aWR0aCc6IGAke25ld0VsZW1XaWR0aCAtIHBkbmdsIC0gcGRuZ3J9cHhgXG4gICAgfSk7XG5cbiAgICB2YXIgbmV3Q29udGFpbmVySGVpZ2h0ID0gdGhpcy4kZWxlbWVudFswXS5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKS5oZWlnaHQgfHwgdGhpcy5jb250YWluZXJIZWlnaHQ7XG4gICAgaWYgKHRoaXMuJGVsZW1lbnQuY3NzKFwiZGlzcGxheVwiKSA9PSBcIm5vbmVcIikge1xuICAgICAgbmV3Q29udGFpbmVySGVpZ2h0ID0gMDtcbiAgICB9XG4gICAgdGhpcy5jb250YWluZXJIZWlnaHQgPSBuZXdDb250YWluZXJIZWlnaHQ7XG4gICAgdGhpcy4kY29udGFpbmVyLmNzcyh7XG4gICAgICBoZWlnaHQ6IG5ld0NvbnRhaW5lckhlaWdodFxuICAgIH0pO1xuICAgIHRoaXMuZWxlbUhlaWdodCA9IG5ld0NvbnRhaW5lckhlaWdodDtcblxuICAgIGlmICghdGhpcy5pc1N0dWNrKSB7XG4gICAgICBpZiAodGhpcy4kZWxlbWVudC5oYXNDbGFzcygnaXMtYXQtYm90dG9tJykpIHtcbiAgICAgICAgdmFyIGFuY2hvclB0ID0gKHRoaXMucG9pbnRzID8gdGhpcy5wb2ludHNbMV0gLSB0aGlzLiRjb250YWluZXIub2Zmc2V0KCkudG9wIDogdGhpcy5hbmNob3JIZWlnaHQpIC0gdGhpcy5lbGVtSGVpZ2h0O1xuICAgICAgICB0aGlzLiRlbGVtZW50LmNzcygndG9wJywgYW5jaG9yUHQpO1xuICAgICAgfVxuICAgIH1cblxuICAgIHRoaXMuX3NldEJyZWFrUG9pbnRzKG5ld0NvbnRhaW5lckhlaWdodCwgZnVuY3Rpb24oKSB7XG4gICAgICBpZiAoY2IgJiYgdHlwZW9mIGNiID09PSAnZnVuY3Rpb24nKSB7IGNiKCk7IH1cbiAgICB9KTtcbiAgfVxuXG4gIC8qKlxuICAgKiBTZXRzIHRoZSB1cHBlciBhbmQgbG93ZXIgYnJlYWtwb2ludHMgZm9yIHRoZSBlbGVtZW50IHRvIGJlY29tZSBzdGlja3kvdW5zdGlja3kuXG4gICAqIEBwYXJhbSB7TnVtYmVyfSBlbGVtSGVpZ2h0IC0gcHggdmFsdWUgZm9yIHN0aWNreS4kZWxlbWVudCBoZWlnaHQsIGNhbGN1bGF0ZWQgYnkgYF9zZXRTaXplc2AuXG4gICAqIEBwYXJhbSB7RnVuY3Rpb259IGNiIC0gb3B0aW9uYWwgY2FsbGJhY2sgZnVuY3Rpb24gdG8gYmUgY2FsbGVkIG9uIGNvbXBsZXRpb24uXG4gICAqIEBwcml2YXRlXG4gICAqL1xuICBfc2V0QnJlYWtQb2ludHMoZWxlbUhlaWdodCwgY2IpIHtcbiAgICBpZiAoIXRoaXMuY2FuU3RpY2spIHtcbiAgICAgIGlmIChjYiAmJiB0eXBlb2YgY2IgPT09ICdmdW5jdGlvbicpIHsgY2IoKTsgfVxuICAgICAgZWxzZSB7IHJldHVybiBmYWxzZTsgfVxuICAgIH1cbiAgICB2YXIgbVRvcCA9IGVtQ2FsYyh0aGlzLm9wdGlvbnMubWFyZ2luVG9wKSxcbiAgICAgICAgbUJ0bSA9IGVtQ2FsYyh0aGlzLm9wdGlvbnMubWFyZ2luQm90dG9tKSxcbiAgICAgICAgdG9wUG9pbnQgPSB0aGlzLnBvaW50cyA/IHRoaXMucG9pbnRzWzBdIDogdGhpcy4kYW5jaG9yLm9mZnNldCgpLnRvcCxcbiAgICAgICAgYm90dG9tUG9pbnQgPSB0aGlzLnBvaW50cyA/IHRoaXMucG9pbnRzWzFdIDogdG9wUG9pbnQgKyB0aGlzLmFuY2hvckhlaWdodCxcbiAgICAgICAgLy8gdG9wUG9pbnQgPSB0aGlzLiRhbmNob3Iub2Zmc2V0KCkudG9wIHx8IHRoaXMucG9pbnRzWzBdLFxuICAgICAgICAvLyBib3R0b21Qb2ludCA9IHRvcFBvaW50ICsgdGhpcy5hbmNob3JIZWlnaHQgfHwgdGhpcy5wb2ludHNbMV0sXG4gICAgICAgIHdpbkhlaWdodCA9IHdpbmRvdy5pbm5lckhlaWdodDtcblxuICAgIGlmICh0aGlzLm9wdGlvbnMuc3RpY2tUbyA9PT0gJ3RvcCcpIHtcbiAgICAgIHRvcFBvaW50IC09IG1Ub3A7XG4gICAgICBib3R0b21Qb2ludCAtPSAoZWxlbUhlaWdodCArIG1Ub3ApO1xuICAgIH0gZWxzZSBpZiAodGhpcy5vcHRpb25zLnN0aWNrVG8gPT09ICdib3R0b20nKSB7XG4gICAgICB0b3BQb2ludCAtPSAod2luSGVpZ2h0IC0gKGVsZW1IZWlnaHQgKyBtQnRtKSk7XG4gICAgICBib3R0b21Qb2ludCAtPSAod2luSGVpZ2h0IC0gbUJ0bSk7XG4gICAgfSBlbHNlIHtcbiAgICAgIC8vdGhpcyB3b3VsZCBiZSB0aGUgc3RpY2tUbzogYm90aCBvcHRpb24uLi4gdHJpY2t5XG4gICAgfVxuXG4gICAgdGhpcy50b3BQb2ludCA9IHRvcFBvaW50O1xuICAgIHRoaXMuYm90dG9tUG9pbnQgPSBib3R0b21Qb2ludDtcblxuICAgIGlmIChjYiAmJiB0eXBlb2YgY2IgPT09ICdmdW5jdGlvbicpIHsgY2IoKTsgfVxuICB9XG5cbiAgLyoqXG4gICAqIERlc3Ryb3lzIHRoZSBjdXJyZW50IHN0aWNreSBlbGVtZW50LlxuICAgKiBSZXNldHMgdGhlIGVsZW1lbnQgdG8gdGhlIHRvcCBwb3NpdGlvbiBmaXJzdC5cbiAgICogUmVtb3ZlcyBldmVudCBsaXN0ZW5lcnMsIEpTLWFkZGVkIGNzcyBwcm9wZXJ0aWVzIGFuZCBjbGFzc2VzLCBhbmQgdW53cmFwcyB0aGUgJGVsZW1lbnQgaWYgdGhlIEpTIGFkZGVkIHRoZSAkY29udGFpbmVyLlxuICAgKiBAZnVuY3Rpb25cbiAgICovXG4gIF9kZXN0cm95KCkge1xuICAgIHRoaXMuX3JlbW92ZVN0aWNreSh0cnVlKTtcblxuICAgIHRoaXMuJGVsZW1lbnQucmVtb3ZlQ2xhc3MoYCR7dGhpcy5vcHRpb25zLnN0aWNreUNsYXNzfSBpcy1hbmNob3JlZCBpcy1hdC10b3BgKVxuICAgICAgICAgICAgICAgICAuY3NzKHtcbiAgICAgICAgICAgICAgICAgICBoZWlnaHQ6ICcnLFxuICAgICAgICAgICAgICAgICAgIHRvcDogJycsXG4gICAgICAgICAgICAgICAgICAgYm90dG9tOiAnJyxcbiAgICAgICAgICAgICAgICAgICAnbWF4LXdpZHRoJzogJydcbiAgICAgICAgICAgICAgICAgfSlcbiAgICAgICAgICAgICAgICAgLm9mZigncmVzaXplbWUuemYudHJpZ2dlcicpXG4gICAgICAgICAgICAgICAgIC5vZmYoJ211dGF0ZW1lLnpmLnRyaWdnZXInKTtcbiAgICBpZiAodGhpcy4kYW5jaG9yICYmIHRoaXMuJGFuY2hvci5sZW5ndGgpIHtcbiAgICAgIHRoaXMuJGFuY2hvci5vZmYoJ2NoYW5nZS56Zi5zdGlja3knKTtcbiAgICB9XG4gICAgaWYgKHRoaXMuc2Nyb2xsTGlzdGVuZXIpICQod2luZG93KS5vZmYodGhpcy5zY3JvbGxMaXN0ZW5lcilcbiAgICBpZiAodGhpcy5vbkxvYWRMaXN0ZW5lcikgJCh3aW5kb3cpLm9mZih0aGlzLm9uTG9hZExpc3RlbmVyKVxuXG4gICAgaWYgKHRoaXMud2FzV3JhcHBlZCkge1xuICAgICAgdGhpcy4kZWxlbWVudC51bndyYXAoKTtcbiAgICB9IGVsc2Uge1xuICAgICAgdGhpcy4kY29udGFpbmVyLnJlbW92ZUNsYXNzKHRoaXMub3B0aW9ucy5jb250YWluZXJDbGFzcylcbiAgICAgICAgICAgICAgICAgICAgIC5jc3Moe1xuICAgICAgICAgICAgICAgICAgICAgICBoZWlnaHQ6ICcnXG4gICAgICAgICAgICAgICAgICAgICB9KTtcbiAgICB9XG4gIH1cbn1cblxuU3RpY2t5LmRlZmF1bHRzID0ge1xuICAvKipcbiAgICogQ3VzdG9taXphYmxlIGNvbnRhaW5lciB0ZW1wbGF0ZS4gQWRkIHlvdXIgb3duIGNsYXNzZXMgZm9yIHN0eWxpbmcgYW5kIHNpemluZy5cbiAgICogQG9wdGlvblxuICAgKiBAdHlwZSB7c3RyaW5nfVxuICAgKiBAZGVmYXVsdCAnJmx0O2RpdiBkYXRhLXN0aWNreS1jb250YWluZXImZ3Q7Jmx0Oy9kaXYmZ3Q7J1xuICAgKi9cbiAgY29udGFpbmVyOiAnPGRpdiBkYXRhLXN0aWNreS1jb250YWluZXI+PC9kaXY+JyxcbiAgLyoqXG4gICAqIExvY2F0aW9uIGluIHRoZSB2aWV3IHRoZSBlbGVtZW50IHN0aWNrcyB0by4gQ2FuIGJlIGAndG9wJ2Agb3IgYCdib3R0b20nYC5cbiAgICogQG9wdGlvblxuICAgKiBAdHlwZSB7c3RyaW5nfVxuICAgKiBAZGVmYXVsdCAndG9wJ1xuICAgKi9cbiAgc3RpY2tUbzogJ3RvcCcsXG4gIC8qKlxuICAgKiBJZiBhbmNob3JlZCB0byBhIHNpbmdsZSBlbGVtZW50LCB0aGUgaWQgb2YgdGhhdCBlbGVtZW50LlxuICAgKiBAb3B0aW9uXG4gICAqIEB0eXBlIHtzdHJpbmd9XG4gICAqIEBkZWZhdWx0ICcnXG4gICAqL1xuICBhbmNob3I6ICcnLFxuICAvKipcbiAgICogSWYgdXNpbmcgbW9yZSB0aGFuIG9uZSBlbGVtZW50IGFzIGFuY2hvciBwb2ludHMsIHRoZSBpZCBvZiB0aGUgdG9wIGFuY2hvci5cbiAgICogQG9wdGlvblxuICAgKiBAdHlwZSB7c3RyaW5nfVxuICAgKiBAZGVmYXVsdCAnJ1xuICAgKi9cbiAgdG9wQW5jaG9yOiAnJyxcbiAgLyoqXG4gICAqIElmIHVzaW5nIG1vcmUgdGhhbiBvbmUgZWxlbWVudCBhcyBhbmNob3IgcG9pbnRzLCB0aGUgaWQgb2YgdGhlIGJvdHRvbSBhbmNob3IuXG4gICAqIEBvcHRpb25cbiAgICogQHR5cGUge3N0cmluZ31cbiAgICogQGRlZmF1bHQgJydcbiAgICovXG4gIGJ0bUFuY2hvcjogJycsXG4gIC8qKlxuICAgKiBNYXJnaW4sIGluIGBlbWAncyB0byBhcHBseSB0byB0aGUgdG9wIG9mIHRoZSBlbGVtZW50IHdoZW4gaXQgYmVjb21lcyBzdGlja3kuXG4gICAqIEBvcHRpb25cbiAgICogQHR5cGUge251bWJlcn1cbiAgICogQGRlZmF1bHQgMVxuICAgKi9cbiAgbWFyZ2luVG9wOiAxLFxuICAvKipcbiAgICogTWFyZ2luLCBpbiBgZW1gJ3MgdG8gYXBwbHkgdG8gdGhlIGJvdHRvbSBvZiB0aGUgZWxlbWVudCB3aGVuIGl0IGJlY29tZXMgc3RpY2t5LlxuICAgKiBAb3B0aW9uXG4gICAqIEB0eXBlIHtudW1iZXJ9XG4gICAqIEBkZWZhdWx0IDFcbiAgICovXG4gIG1hcmdpbkJvdHRvbTogMSxcbiAgLyoqXG4gICAqIEJyZWFrcG9pbnQgc3RyaW5nIHRoYXQgaXMgdGhlIG1pbmltdW0gc2NyZWVuIHNpemUgYW4gZWxlbWVudCBzaG91bGQgYmVjb21lIHN0aWNreS5cbiAgICogQG9wdGlvblxuICAgKiBAdHlwZSB7c3RyaW5nfVxuICAgKiBAZGVmYXVsdCAnbWVkaXVtJ1xuICAgKi9cbiAgc3RpY2t5T246ICdtZWRpdW0nLFxuICAvKipcbiAgICogQ2xhc3MgYXBwbGllZCB0byBzdGlja3kgZWxlbWVudCwgYW5kIHJlbW92ZWQgb24gZGVzdHJ1Y3Rpb24uIEZvdW5kYXRpb24gZGVmYXVsdHMgdG8gYHN0aWNreWAuXG4gICAqIEBvcHRpb25cbiAgICogQHR5cGUge3N0cmluZ31cbiAgICogQGRlZmF1bHQgJ3N0aWNreSdcbiAgICovXG4gIHN0aWNreUNsYXNzOiAnc3RpY2t5JyxcbiAgLyoqXG4gICAqIENsYXNzIGFwcGxpZWQgdG8gc3RpY2t5IGNvbnRhaW5lci4gRm91bmRhdGlvbiBkZWZhdWx0cyB0byBgc3RpY2t5LWNvbnRhaW5lcmAuXG4gICAqIEBvcHRpb25cbiAgICogQHR5cGUge3N0cmluZ31cbiAgICogQGRlZmF1bHQgJ3N0aWNreS1jb250YWluZXInXG4gICAqL1xuICBjb250YWluZXJDbGFzczogJ3N0aWNreS1jb250YWluZXInLFxuICAvKipcbiAgICogTnVtYmVyIG9mIHNjcm9sbCBldmVudHMgYmV0d2VlbiB0aGUgcGx1Z2luJ3MgcmVjYWxjdWxhdGluZyBzdGlja3kgcG9pbnRzLiBTZXR0aW5nIGl0IHRvIGAwYCB3aWxsIGNhdXNlIGl0IHRvIHJlY2FsYyBldmVyeSBzY3JvbGwgZXZlbnQsIHNldHRpbmcgaXQgdG8gYC0xYCB3aWxsIHByZXZlbnQgcmVjYWxjIG9uIHNjcm9sbC5cbiAgICogQG9wdGlvblxuICAgKiBAdHlwZSB7bnVtYmVyfVxuICAgKiBAZGVmYXVsdCAtMVxuICAgKi9cbiAgY2hlY2tFdmVyeTogLTFcbn07XG5cbi8qKlxuICogSGVscGVyIGZ1bmN0aW9uIHRvIGNhbGN1bGF0ZSBlbSB2YWx1ZXNcbiAqIEBwYXJhbSBOdW1iZXIge2VtfSAtIG51bWJlciBvZiBlbSdzIHRvIGNhbGN1bGF0ZSBpbnRvIHBpeGVsc1xuICovXG5mdW5jdGlvbiBlbUNhbGMoZW0pIHtcbiAgcmV0dXJuIHBhcnNlSW50KHdpbmRvdy5nZXRDb21wdXRlZFN0eWxlKGRvY3VtZW50LmJvZHksIG51bGwpLmZvbnRTaXplLCAxMCkgKiBlbTtcbn1cblxuZXhwb3J0IHtTdGlja3l9O1xuXG5cblxuLy8gV0VCUEFDSyBGT09URVIgLy9cbi8vIC4vbm9kZV9tb2R1bGVzL2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi5zdGlja3kuanMiLCIndXNlIHN0cmljdCc7XG5cbmltcG9ydCAkIGZyb20gJ2pxdWVyeSc7XG5pbXBvcnQgeyBNb3Rpb24gfSBmcm9tICcuL2ZvdW5kYXRpb24udXRpbC5tb3Rpb24nO1xuaW1wb3J0IHsgUGx1Z2luIH0gZnJvbSAnLi9mb3VuZGF0aW9uLmNvcmUucGx1Z2luJztcbmltcG9ydCB7IFJlZ0V4cEVzY2FwZSB9IGZyb20gJy4vZm91bmRhdGlvbi5jb3JlLnV0aWxzJztcbmltcG9ydCB7IFRyaWdnZXJzIH0gZnJvbSAnLi9mb3VuZGF0aW9uLnV0aWwudHJpZ2dlcnMnO1xuXG4vKipcbiAqIFRvZ2dsZXIgbW9kdWxlLlxuICogQG1vZHVsZSBmb3VuZGF0aW9uLnRvZ2dsZXJcbiAqIEByZXF1aXJlcyBmb3VuZGF0aW9uLnV0aWwubW90aW9uXG4gKiBAcmVxdWlyZXMgZm91bmRhdGlvbi51dGlsLnRyaWdnZXJzXG4gKi9cblxuY2xhc3MgVG9nZ2xlciBleHRlbmRzIFBsdWdpbiB7XG4gIC8qKlxuICAgKiBDcmVhdGVzIGEgbmV3IGluc3RhbmNlIG9mIFRvZ2dsZXIuXG4gICAqIEBjbGFzc1xuICAgKiBAbmFtZSBUb2dnbGVyXG4gICAqIEBmaXJlcyBUb2dnbGVyI2luaXRcbiAgICogQHBhcmFtIHtPYmplY3R9IGVsZW1lbnQgLSBqUXVlcnkgb2JqZWN0IHRvIGFkZCB0aGUgdHJpZ2dlciB0by5cbiAgICogQHBhcmFtIHtPYmplY3R9IG9wdGlvbnMgLSBPdmVycmlkZXMgdG8gdGhlIGRlZmF1bHQgcGx1Z2luIHNldHRpbmdzLlxuICAgKi9cbiAgX3NldHVwKGVsZW1lbnQsIG9wdGlvbnMpIHtcbiAgICB0aGlzLiRlbGVtZW50ID0gZWxlbWVudDtcbiAgICB0aGlzLm9wdGlvbnMgPSAkLmV4dGVuZCh7fSwgVG9nZ2xlci5kZWZhdWx0cywgZWxlbWVudC5kYXRhKCksIG9wdGlvbnMpO1xuICAgIHRoaXMuY2xhc3NOYW1lID0gJyc7XG4gICAgdGhpcy5jbGFzc05hbWUgPSAnVG9nZ2xlcic7IC8vIGllOSBiYWNrIGNvbXBhdFxuXG4gICAgLy8gVHJpZ2dlcnMgaW5pdCBpcyBpZGVtcG90ZW50LCBqdXN0IG5lZWQgdG8gbWFrZSBzdXJlIGl0IGlzIGluaXRpYWxpemVkXG4gICAgVHJpZ2dlcnMuaW5pdCgkKTtcblxuICAgIHRoaXMuX2luaXQoKTtcbiAgICB0aGlzLl9ldmVudHMoKTtcbiAgfVxuXG4gIC8qKlxuICAgKiBJbml0aWFsaXplcyB0aGUgVG9nZ2xlciBwbHVnaW4gYnkgcGFyc2luZyB0aGUgdG9nZ2xlIGNsYXNzIGZyb20gZGF0YS10b2dnbGVyLCBvciBhbmltYXRpb24gY2xhc3NlcyBmcm9tIGRhdGEtYW5pbWF0ZS5cbiAgICogQGZ1bmN0aW9uXG4gICAqIEBwcml2YXRlXG4gICAqL1xuICBfaW5pdCgpIHtcbiAgICB2YXIgaW5wdXQ7XG4gICAgLy8gUGFyc2UgYW5pbWF0aW9uIGNsYXNzZXMgaWYgdGhleSB3ZXJlIHNldFxuICAgIGlmICh0aGlzLm9wdGlvbnMuYW5pbWF0ZSkge1xuICAgICAgaW5wdXQgPSB0aGlzLm9wdGlvbnMuYW5pbWF0ZS5zcGxpdCgnICcpO1xuXG4gICAgICB0aGlzLmFuaW1hdGlvbkluID0gaW5wdXRbMF07XG4gICAgICB0aGlzLmFuaW1hdGlvbk91dCA9IGlucHV0WzFdIHx8IG51bGw7XG4gICAgfVxuICAgIC8vIE90aGVyd2lzZSwgcGFyc2UgdG9nZ2xlIGNsYXNzXG4gICAgZWxzZSB7XG4gICAgICBpbnB1dCA9IHRoaXMuJGVsZW1lbnQuZGF0YSgndG9nZ2xlcicpO1xuICAgICAgLy8gQWxsb3cgZm9yIGEgLiBhdCB0aGUgYmVnaW5uaW5nIG9mIHRoZSBzdHJpbmdcbiAgICAgIHRoaXMuY2xhc3NOYW1lID0gaW5wdXRbMF0gPT09ICcuJyA/IGlucHV0LnNsaWNlKDEpIDogaW5wdXQ7XG4gICAgfVxuXG4gICAgLy8gQWRkIEFSSUEgYXR0cmlidXRlcyB0byB0cmlnZ2VyczpcbiAgICB2YXIgaWQgPSB0aGlzLiRlbGVtZW50WzBdLmlkLFxuICAgICAgJHRyaWdnZXJzID0gJChgW2RhdGEtb3Blbn49XCIke2lkfVwiXSwgW2RhdGEtY2xvc2V+PVwiJHtpZH1cIl0sIFtkYXRhLXRvZ2dsZX49XCIke2lkfVwiXWApO1xuXG4gICAgLy8gLSBhcmlhLWV4cGFuZGVkOiBhY2NvcmRpbmcgdG8gdGhlIGVsZW1lbnQgdmlzaWJpbGl0eS5cbiAgICAkdHJpZ2dlcnMuYXR0cignYXJpYS1leHBhbmRlZCcsICF0aGlzLiRlbGVtZW50LmlzKCc6aGlkZGVuJykpO1xuICAgIC8vIC0gYXJpYS1jb250cm9sczogYWRkaW5nIHRoZSBlbGVtZW50IGlkIHRvIGl0IGlmIG5vdCBhbHJlYWR5IGluIGl0LlxuICAgICR0cmlnZ2Vycy5lYWNoKChpbmRleCwgdHJpZ2dlcikgPT4ge1xuICAgICAgY29uc3QgJHRyaWdnZXIgPSAkKHRyaWdnZXIpO1xuICAgICAgY29uc3QgY29udHJvbHMgPSAkdHJpZ2dlci5hdHRyKCdhcmlhLWNvbnRyb2xzJykgfHwgJyc7XG5cbiAgICAgIGNvbnN0IGNvbnRhaW5zSWQgPSBuZXcgUmVnRXhwKGBcXFxcYiR7UmVnRXhwRXNjYXBlKGlkKX1cXFxcYmApLnRlc3QoY29udHJvbHMpO1xuICAgICAgaWYgKCFjb250YWluc0lkKSAkdHJpZ2dlci5hdHRyKCdhcmlhLWNvbnRyb2xzJywgY29udHJvbHMgPyBgJHtjb250cm9sc30gJHtpZH1gIDogaWQpO1xuICAgIH0pO1xuICB9XG5cbiAgLyoqXG4gICAqIEluaXRpYWxpemVzIGV2ZW50cyBmb3IgdGhlIHRvZ2dsZSB0cmlnZ2VyLlxuICAgKiBAZnVuY3Rpb25cbiAgICogQHByaXZhdGVcbiAgICovXG4gIF9ldmVudHMoKSB7XG4gICAgdGhpcy4kZWxlbWVudC5vZmYoJ3RvZ2dsZS56Zi50cmlnZ2VyJykub24oJ3RvZ2dsZS56Zi50cmlnZ2VyJywgdGhpcy50b2dnbGUuYmluZCh0aGlzKSk7XG4gIH1cblxuICAvKipcbiAgICogVG9nZ2xlcyB0aGUgdGFyZ2V0IGNsYXNzIG9uIHRoZSB0YXJnZXQgZWxlbWVudC4gQW4gZXZlbnQgaXMgZmlyZWQgZnJvbSB0aGUgb3JpZ2luYWwgdHJpZ2dlciBkZXBlbmRpbmcgb24gaWYgdGhlIHJlc3VsdGFudCBzdGF0ZSB3YXMgXCJvblwiIG9yIFwib2ZmXCIuXG4gICAqIEBmdW5jdGlvblxuICAgKiBAZmlyZXMgVG9nZ2xlciNvblxuICAgKiBAZmlyZXMgVG9nZ2xlciNvZmZcbiAgICovXG4gIHRvZ2dsZSgpIHtcbiAgICB0aGlzWyB0aGlzLm9wdGlvbnMuYW5pbWF0ZSA/ICdfdG9nZ2xlQW5pbWF0ZScgOiAnX3RvZ2dsZUNsYXNzJ10oKTtcbiAgfVxuXG4gIF90b2dnbGVDbGFzcygpIHtcbiAgICB0aGlzLiRlbGVtZW50LnRvZ2dsZUNsYXNzKHRoaXMuY2xhc3NOYW1lKTtcblxuICAgIHZhciBpc09uID0gdGhpcy4kZWxlbWVudC5oYXNDbGFzcyh0aGlzLmNsYXNzTmFtZSk7XG4gICAgaWYgKGlzT24pIHtcbiAgICAgIC8qKlxuICAgICAgICogRmlyZXMgaWYgdGhlIHRhcmdldCBlbGVtZW50IGhhcyB0aGUgY2xhc3MgYWZ0ZXIgYSB0b2dnbGUuXG4gICAgICAgKiBAZXZlbnQgVG9nZ2xlciNvblxuICAgICAgICovXG4gICAgICB0aGlzLiRlbGVtZW50LnRyaWdnZXIoJ29uLnpmLnRvZ2dsZXInKTtcbiAgICB9XG4gICAgZWxzZSB7XG4gICAgICAvKipcbiAgICAgICAqIEZpcmVzIGlmIHRoZSB0YXJnZXQgZWxlbWVudCBkb2VzIG5vdCBoYXZlIHRoZSBjbGFzcyBhZnRlciBhIHRvZ2dsZS5cbiAgICAgICAqIEBldmVudCBUb2dnbGVyI29mZlxuICAgICAgICovXG4gICAgICB0aGlzLiRlbGVtZW50LnRyaWdnZXIoJ29mZi56Zi50b2dnbGVyJyk7XG4gICAgfVxuXG4gICAgdGhpcy5fdXBkYXRlQVJJQShpc09uKTtcbiAgICB0aGlzLiRlbGVtZW50LmZpbmQoJ1tkYXRhLW11dGF0ZV0nKS50cmlnZ2VyKCdtdXRhdGVtZS56Zi50cmlnZ2VyJyk7XG4gIH1cblxuICBfdG9nZ2xlQW5pbWF0ZSgpIHtcbiAgICB2YXIgX3RoaXMgPSB0aGlzO1xuXG4gICAgaWYgKHRoaXMuJGVsZW1lbnQuaXMoJzpoaWRkZW4nKSkge1xuICAgICAgTW90aW9uLmFuaW1hdGVJbih0aGlzLiRlbGVtZW50LCB0aGlzLmFuaW1hdGlvbkluLCBmdW5jdGlvbigpIHtcbiAgICAgICAgX3RoaXMuX3VwZGF0ZUFSSUEodHJ1ZSk7XG4gICAgICAgIHRoaXMudHJpZ2dlcignb24uemYudG9nZ2xlcicpO1xuICAgICAgICB0aGlzLmZpbmQoJ1tkYXRhLW11dGF0ZV0nKS50cmlnZ2VyKCdtdXRhdGVtZS56Zi50cmlnZ2VyJyk7XG4gICAgICB9KTtcbiAgICB9XG4gICAgZWxzZSB7XG4gICAgICBNb3Rpb24uYW5pbWF0ZU91dCh0aGlzLiRlbGVtZW50LCB0aGlzLmFuaW1hdGlvbk91dCwgZnVuY3Rpb24oKSB7XG4gICAgICAgIF90aGlzLl91cGRhdGVBUklBKGZhbHNlKTtcbiAgICAgICAgdGhpcy50cmlnZ2VyKCdvZmYuemYudG9nZ2xlcicpO1xuICAgICAgICB0aGlzLmZpbmQoJ1tkYXRhLW11dGF0ZV0nKS50cmlnZ2VyKCdtdXRhdGVtZS56Zi50cmlnZ2VyJyk7XG4gICAgICB9KTtcbiAgICB9XG4gIH1cblxuICBfdXBkYXRlQVJJQShpc09uKSB7XG4gICAgdmFyIGlkID0gdGhpcy4kZWxlbWVudFswXS5pZDtcbiAgICAkKGBbZGF0YS1vcGVuPVwiJHtpZH1cIl0sIFtkYXRhLWNsb3NlPVwiJHtpZH1cIl0sIFtkYXRhLXRvZ2dsZT1cIiR7aWR9XCJdYClcbiAgICAgIC5hdHRyKHtcbiAgICAgICAgJ2FyaWEtZXhwYW5kZWQnOiBpc09uID8gdHJ1ZSA6IGZhbHNlXG4gICAgICB9KTtcbiAgfVxuXG4gIC8qKlxuICAgKiBEZXN0cm95cyB0aGUgaW5zdGFuY2Ugb2YgVG9nZ2xlciBvbiB0aGUgZWxlbWVudC5cbiAgICogQGZ1bmN0aW9uXG4gICAqL1xuICBfZGVzdHJveSgpIHtcbiAgICB0aGlzLiRlbGVtZW50Lm9mZignLnpmLnRvZ2dsZXInKTtcbiAgfVxufVxuXG5Ub2dnbGVyLmRlZmF1bHRzID0ge1xuICAvKipcbiAgICogVGVsbHMgdGhlIHBsdWdpbiBpZiB0aGUgZWxlbWVudCBzaG91bGQgYW5pbWF0ZWQgd2hlbiB0b2dnbGVkLlxuICAgKiBAb3B0aW9uXG4gICAqIEB0eXBlIHtib29sZWFufVxuICAgKiBAZGVmYXVsdCBmYWxzZVxuICAgKi9cbiAgYW5pbWF0ZTogZmFsc2Vcbn07XG5cbmV4cG9ydCB7VG9nZ2xlcn07XG5cblxuXG4vLyBXRUJQQUNLIEZPT1RFUiAvL1xuLy8gLi9ub2RlX21vZHVsZXMvZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLnRvZ2dsZXIuanMiXSwic291cmNlUm9vdCI6IiJ9