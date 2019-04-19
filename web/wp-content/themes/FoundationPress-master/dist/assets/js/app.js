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
/******/ 	return __webpack_require__(__webpack_require__.s = 6);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports) {

module.exports = jQuery;

/***/ }),
/* 1 */
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
/* 2 */
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
/* 3 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Motion = exports.Move = undefined;

var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

var _foundationCore = __webpack_require__(1);

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
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Triggers = undefined;

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

var _foundationCore = __webpack_require__(1);

var _foundationUtil = __webpack_require__(3);

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
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Plugin = undefined;

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

var _foundationCore = __webpack_require__(1);

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
/* 6 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(7);


/***/ }),
/* 7 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


__webpack_require__(8);

$(document).foundation(); // import $ from 'jquery';
// import whatInput from 'what-input';
//
// window.$ = $;
//
// import Foundation from 'foundation-sites';
// If you want to pick and choose which modules to include, comment out the above and uncomment
// the line below

/***/ }),
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

var _foundation = __webpack_require__(9);

var _foundationCore = __webpack_require__(1);

var _foundationUtil = __webpack_require__(10);

var _foundationUtil2 = __webpack_require__(11);

var _foundationUtil3 = __webpack_require__(12);

var _foundationUtil4 = __webpack_require__(2);

var _foundationUtil5 = __webpack_require__(3);

var _foundationUtil6 = __webpack_require__(13);

var _foundationUtil7 = __webpack_require__(14);

var _foundationUtil8 = __webpack_require__(15);

var _foundationUtil9 = __webpack_require__(4);

var _foundation2 = __webpack_require__(16);

var _foundation3 = __webpack_require__(17);

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
/* 9 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Foundation = undefined;

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

var _foundationCore = __webpack_require__(1);

var _foundationUtil = __webpack_require__(2);

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
/* 10 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Box = undefined;

var _foundationCore = __webpack_require__(1);

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
/* 11 */
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
/* 12 */
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

var _foundationCore = __webpack_require__(1);

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
/* 15 */
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
/* 16 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Sticky = undefined;

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

var _foundationCore = __webpack_require__(1);

var _foundationUtil = __webpack_require__(2);

var _foundationCore2 = __webpack_require__(5);

var _foundationUtil2 = __webpack_require__(4);

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
/* 17 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Toggler = undefined;

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

var _foundationUtil = __webpack_require__(3);

var _foundationCore = __webpack_require__(5);

var _foundationCore2 = __webpack_require__(1);

var _foundationUtil2 = __webpack_require__(4);

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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vd2VicGFjay9ib290c3RyYXAgZTMwNDI1NjlhYzBhZTFiYmYzYzciLCJ3ZWJwYWNrOi8vL2V4dGVybmFsIFwialF1ZXJ5XCIiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi5jb3JlLnV0aWxzLmpzIiwid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9mb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24udXRpbC5tZWRpYVF1ZXJ5LmpzIiwid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9mb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24udXRpbC5tb3Rpb24uanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi51dGlsLnRyaWdnZXJzLmpzIiwid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9mb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24uY29yZS5wbHVnaW4uanMiLCJ3ZWJwYWNrOi8vLy4vc3JjL2Fzc2V0cy9qcy9hcHAuanMiLCJ3ZWJwYWNrOi8vLy4vc3JjL2Fzc2V0cy9qcy9saWIvZm91bmRhdGlvbi1leHBsaWNpdC1waWVjZXMuanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi5jb3JlLmpzIiwid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9mb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24udXRpbC5ib3guanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi51dGlsLmltYWdlTG9hZGVyLmpzIiwid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9mb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24udXRpbC5rZXlib2FyZC5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLnV0aWwubmVzdC5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLnV0aWwudGltZXIuanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi51dGlsLnRvdWNoLmpzIiwid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9mb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24uc3RpY2t5LmpzIiwid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9mb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24udG9nZ2xlci5qcyJdLCJuYW1lcyI6WyJydGwiLCJhdHRyIiwiR2V0WW9EaWdpdHMiLCJsZW5ndGgiLCJuYW1lc3BhY2UiLCJNYXRoIiwicm91bmQiLCJwb3ciLCJyYW5kb20iLCJ0b1N0cmluZyIsInNsaWNlIiwiUmVnRXhwRXNjYXBlIiwic3RyIiwicmVwbGFjZSIsInRyYW5zaXRpb25lbmQiLCIkZWxlbSIsInRyYW5zaXRpb25zIiwiZWxlbSIsImRvY3VtZW50IiwiY3JlYXRlRWxlbWVudCIsImVuZCIsInQiLCJzdHlsZSIsInNldFRpbWVvdXQiLCJ0cmlnZ2VySGFuZGxlciIsIm9uTG9hZCIsImhhbmRsZXIiLCJkaWRMb2FkIiwicmVhZHlTdGF0ZSIsImV2ZW50VHlwZSIsImNiIiwib25lIiwid2luZG93IiwiaWdub3JlTW91c2VkaXNhcHBlYXIiLCJpZ25vcmVMZWF2ZVdpbmRvdyIsImlnbm9yZVJlYXBwZWFyIiwibGVhdmVFdmVudEhhbmRsZXIiLCJlTGVhdmUiLCJyZXN0IiwiY2FsbGJhY2siLCJiaW5kIiwicmVsYXRlZFRhcmdldCIsImxlYXZlRXZlbnREZWJvdW5jZXIiLCJoYXNGb2N1cyIsInJlZW50ZXJFdmVudEhhbmRsZXIiLCJlUmVlbnRlciIsImN1cnJlbnRUYXJnZXQiLCJoYXMiLCJ0YXJnZXQiLCJkZWZhdWx0UXVlcmllcyIsImxhbmRzY2FwZSIsInBvcnRyYWl0IiwicmV0aW5hIiwibWF0Y2hNZWRpYSIsInN0eWxlTWVkaWEiLCJtZWRpYSIsInNjcmlwdCIsImdldEVsZW1lbnRzQnlUYWdOYW1lIiwiaW5mbyIsInR5cGUiLCJpZCIsImhlYWQiLCJhcHBlbmRDaGlsZCIsInBhcmVudE5vZGUiLCJpbnNlcnRCZWZvcmUiLCJnZXRDb21wdXRlZFN0eWxlIiwiY3VycmVudFN0eWxlIiwibWF0Y2hNZWRpdW0iLCJ0ZXh0Iiwic3R5bGVTaGVldCIsImNzc1RleHQiLCJ0ZXh0Q29udGVudCIsIndpZHRoIiwibWF0Y2hlcyIsIk1lZGlhUXVlcnkiLCJxdWVyaWVzIiwiY3VycmVudCIsIl9pbml0Iiwic2VsZiIsIiRtZXRhIiwiYXBwZW5kVG8iLCJleHRyYWN0ZWRTdHlsZXMiLCJjc3MiLCJuYW1lZFF1ZXJpZXMiLCJwYXJzZVN0eWxlVG9PYmplY3QiLCJrZXkiLCJoYXNPd25Qcm9wZXJ0eSIsInB1c2giLCJuYW1lIiwidmFsdWUiLCJfZ2V0Q3VycmVudFNpemUiLCJfd2F0Y2hlciIsImF0TGVhc3QiLCJzaXplIiwicXVlcnkiLCJnZXQiLCJpcyIsInRyaW0iLCJzcGxpdCIsImkiLCJtYXRjaGVkIiwib2ZmIiwib24iLCJuZXdTaXplIiwiY3VycmVudFNpemUiLCJ0cmlnZ2VyIiwic3R5bGVPYmplY3QiLCJyZWR1Y2UiLCJyZXQiLCJwYXJhbSIsInBhcnRzIiwidmFsIiwiZGVjb2RlVVJJQ29tcG9uZW50IiwiQXJyYXkiLCJpc0FycmF5IiwiaW5pdENsYXNzZXMiLCJhY3RpdmVDbGFzc2VzIiwiTW90aW9uIiwiYW5pbWF0ZUluIiwiZWxlbWVudCIsImFuaW1hdGlvbiIsImFuaW1hdGUiLCJhbmltYXRlT3V0IiwiTW92ZSIsImR1cmF0aW9uIiwiZm4iLCJhbmltIiwicHJvZyIsInN0YXJ0IiwiYXBwbHkiLCJtb3ZlIiwidHMiLCJyZXF1ZXN0QW5pbWF0aW9uRnJhbWUiLCJjYW5jZWxBbmltYXRpb25GcmFtZSIsImlzSW4iLCJlcSIsImluaXRDbGFzcyIsImFjdGl2ZUNsYXNzIiwicmVzZXQiLCJhZGRDbGFzcyIsInNob3ciLCJvZmZzZXRXaWR0aCIsImZpbmlzaCIsImhpZGUiLCJ0cmFuc2l0aW9uRHVyYXRpb24iLCJyZW1vdmVDbGFzcyIsIk11dGF0aW9uT2JzZXJ2ZXIiLCJwcmVmaXhlcyIsInRyaWdnZXJzIiwiZWwiLCJkYXRhIiwiZm9yRWFjaCIsIlRyaWdnZXJzIiwiTGlzdGVuZXJzIiwiQmFzaWMiLCJHbG9iYWwiLCJJbml0aWFsaXplcnMiLCJvcGVuTGlzdGVuZXIiLCJjbG9zZUxpc3RlbmVyIiwidG9nZ2xlTGlzdGVuZXIiLCJjbG9zZWFibGVMaXN0ZW5lciIsImUiLCJzdG9wUHJvcGFnYXRpb24iLCJmYWRlT3V0IiwidG9nZ2xlRm9jdXNMaXN0ZW5lciIsImFkZE9wZW5MaXN0ZW5lciIsImFkZENsb3NlTGlzdGVuZXIiLCJhZGRUb2dnbGVMaXN0ZW5lciIsImFkZENsb3NlYWJsZUxpc3RlbmVyIiwiYWRkVG9nZ2xlRm9jdXNMaXN0ZW5lciIsInJlc2l6ZUxpc3RlbmVyIiwiJG5vZGVzIiwiZWFjaCIsInNjcm9sbExpc3RlbmVyIiwiY2xvc2VNZUxpc3RlbmVyIiwicGx1Z2luSWQiLCJwbHVnaW4iLCJwbHVnaW5zIiwibm90IiwiX3RoaXMiLCJhZGRDbG9zZW1lTGlzdGVuZXIiLCJwbHVnaW5OYW1lIiwieWV0aUJveGVzIiwicGx1Z05hbWVzIiwiY29uY2F0IiwiY29uc29sZSIsImVycm9yIiwibGlzdGVuZXJzIiwibWFwIiwiam9pbiIsImRlYm91bmNlR2xvYmFsTGlzdGVuZXIiLCJkZWJvdW5jZSIsImxpc3RlbmVyIiwidGltZXIiLCJhcmdzIiwicHJvdG90eXBlIiwiY2FsbCIsImFyZ3VtZW50cyIsImNsZWFyVGltZW91dCIsImFkZFJlc2l6ZUxpc3RlbmVyIiwiYWRkU2Nyb2xsTGlzdGVuZXIiLCJhZGRNdXRhdGlvbkV2ZW50c0xpc3RlbmVyIiwiZmluZCIsImxpc3RlbmluZ0VsZW1lbnRzTXV0YXRpb24iLCJtdXRhdGlvblJlY29yZHNMaXN0IiwiJHRhcmdldCIsImF0dHJpYnV0ZU5hbWUiLCJwYWdlWU9mZnNldCIsImNsb3Nlc3QiLCJlbGVtZW50T2JzZXJ2ZXIiLCJvYnNlcnZlIiwiYXR0cmlidXRlcyIsImNoaWxkTGlzdCIsImNoYXJhY3RlckRhdGEiLCJzdWJ0cmVlIiwiYXR0cmlidXRlRmlsdGVyIiwiYWRkU2ltcGxlTGlzdGVuZXJzIiwiJGRvY3VtZW50IiwiYWRkR2xvYmFsTGlzdGVuZXJzIiwiaW5pdCIsIiQiLCJGb3VuZGF0aW9uIiwidHJpZ2dlcnNJbml0aWFsaXplZCIsIklIZWFyWW91IiwiUGx1Z2luIiwib3B0aW9ucyIsIl9zZXR1cCIsImdldFBsdWdpbk5hbWUiLCJ1dWlkIiwiJGVsZW1lbnQiLCJfZGVzdHJveSIsInJlbW92ZUF0dHIiLCJyZW1vdmVEYXRhIiwicHJvcCIsImh5cGhlbmF0ZSIsInRvTG93ZXJDYXNlIiwib2JqIiwiY29uc3RydWN0b3IiLCJjbGFzc05hbWUiLCJmb3VuZGF0aW9uIiwiYWRkVG9KcXVlcnkiLCJCb3giLCJvbkltYWdlc0xvYWRlZCIsIktleWJvYXJkIiwiTmVzdCIsIlRpbWVyIiwiVG91Y2giLCJTdGlja3kiLCJUb2dnbGVyIiwibW9kdWxlIiwiZXhwb3J0cyIsIkZPVU5EQVRJT05fVkVSU0lPTiIsInZlcnNpb24iLCJfcGx1Z2lucyIsIl91dWlkcyIsImZ1bmN0aW9uTmFtZSIsImF0dHJOYW1lIiwicmVnaXN0ZXJQbHVnaW4iLCJ1bnJlZ2lzdGVyUGx1Z2luIiwic3BsaWNlIiwiaW5kZXhPZiIsInJlSW5pdCIsImlzSlEiLCJmbnMiLCJwbGdzIiwicCIsIk9iamVjdCIsImtleXMiLCJlcnIiLCJyZWZsb3ciLCJhZGRCYWNrIiwiJGVsIiwib3B0cyIsIndhcm4iLCJ0aGluZyIsIm9wdCIsInBhcnNlVmFsdWUiLCJlciIsImdldEZuTmFtZSIsIm1ldGhvZCIsIiRub0pTIiwicGx1Z0NsYXNzIiwiUmVmZXJlbmNlRXJyb3IiLCJUeXBlRXJyb3IiLCJ1dGlsIiwidGhyb3R0bGUiLCJmdW5jIiwiZGVsYXkiLCJjb250ZXh0IiwiRGF0ZSIsIm5vdyIsImdldFRpbWUiLCJ2ZW5kb3JzIiwidnAiLCJ0ZXN0IiwibmF2aWdhdG9yIiwidXNlckFnZW50IiwibGFzdFRpbWUiLCJuZXh0VGltZSIsIm1heCIsInBlcmZvcm1hbmNlIiwiRnVuY3Rpb24iLCJvVGhpcyIsImFBcmdzIiwiZlRvQmluZCIsImZOT1AiLCJmQm91bmQiLCJmdW5jTmFtZVJlZ2V4IiwicmVzdWx0cyIsImV4ZWMiLCJpc05hTiIsInBhcnNlRmxvYXQiLCJJbU5vdFRvdWNoaW5nWW91IiwiT3ZlcmxhcEFyZWEiLCJHZXREaW1lbnNpb25zIiwiR2V0T2Zmc2V0cyIsIkdldEV4cGxpY2l0T2Zmc2V0cyIsInBhcmVudCIsImxyT25seSIsInRiT25seSIsImlnbm9yZUJvdHRvbSIsImVsZURpbXMiLCJ0b3BPdmVyIiwiYm90dG9tT3ZlciIsImxlZnRPdmVyIiwicmlnaHRPdmVyIiwicGFyRGltcyIsImhlaWdodCIsIm9mZnNldCIsInRvcCIsImxlZnQiLCJ3aW5kb3dEaW1zIiwibWluIiwic3FydCIsIkVycm9yIiwicmVjdCIsImdldEJvdW5kaW5nQ2xpZW50UmVjdCIsInBhclJlY3QiLCJ3aW5SZWN0IiwiYm9keSIsIndpblkiLCJ3aW5YIiwicGFnZVhPZmZzZXQiLCJwYXJlbnREaW1zIiwiYW5jaG9yIiwicG9zaXRpb24iLCJ2T2Zmc2V0IiwiaE9mZnNldCIsImlzT3ZlcmZsb3ciLCJsb2ciLCIkZWxlRGltcyIsIiRhbmNob3JEaW1zIiwiYWxpZ25tZW50IiwidG9wVmFsIiwibGVmdFZhbCIsImltYWdlcyIsInVubG9hZGVkIiwiY29tcGxldGUiLCJuYXR1cmFsV2lkdGgiLCJzaW5nbGVJbWFnZUxvYWRlZCIsImltYWdlIiwiSW1hZ2UiLCJldmVudHMiLCJtZSIsImV2ZW50Iiwic3JjIiwia2V5Q29kZXMiLCJjb21tYW5kcyIsImZpbmRGb2N1c2FibGUiLCJmaWx0ZXIiLCJwYXJzZUtleSIsIndoaWNoIiwia2V5Q29kZSIsIlN0cmluZyIsImZyb21DaGFyQ29kZSIsInRvVXBwZXJDYXNlIiwic2hpZnRLZXkiLCJjdHJsS2V5IiwiYWx0S2V5IiwiZ2V0S2V5Q29kZXMiLCJoYW5kbGVLZXkiLCJjb21wb25lbnQiLCJmdW5jdGlvbnMiLCJjb21tYW5kTGlzdCIsImNtZHMiLCJjb21tYW5kIiwibHRyIiwiZXh0ZW5kIiwicmV0dXJuVmFsdWUiLCJoYW5kbGVkIiwidW5oYW5kbGVkIiwicmVnaXN0ZXIiLCJjb21wb25lbnROYW1lIiwidHJhcEZvY3VzIiwiJGZvY3VzYWJsZSIsIiRmaXJzdEZvY3VzYWJsZSIsIiRsYXN0Rm9jdXNhYmxlIiwicHJldmVudERlZmF1bHQiLCJmb2N1cyIsInJlbGVhc2VGb2N1cyIsImtjcyIsImsiLCJrYyIsIkZlYXRoZXIiLCJtZW51IiwiaXRlbXMiLCJzdWJNZW51Q2xhc3MiLCJzdWJJdGVtQ2xhc3MiLCJoYXNTdWJDbGFzcyIsImFwcGx5QXJpYSIsIiRpdGVtIiwiJHN1YiIsImNoaWxkcmVuIiwiQnVybiIsIm5hbWVTcGFjZSIsInJlbWFpbiIsImlzUGF1c2VkIiwicmVzdGFydCIsImluZmluaXRlIiwicGF1c2UiLCJzdGFydFBvc1giLCJzdGFydFBvc1kiLCJzdGFydFRpbWUiLCJlbGFwc2VkVGltZSIsInN0YXJ0RXZlbnQiLCJpc01vdmluZyIsImRpZE1vdmVkIiwib25Ub3VjaEVuZCIsInJlbW92ZUV2ZW50TGlzdGVuZXIiLCJvblRvdWNoTW92ZSIsInRhcEV2ZW50IiwiRXZlbnQiLCJzcG90U3dpcGUiLCJ4IiwidG91Y2hlcyIsInBhZ2VYIiwieSIsInBhZ2VZIiwiZHgiLCJkeSIsImRpciIsImFicyIsIm1vdmVUaHJlc2hvbGQiLCJ0aW1lVGhyZXNob2xkIiwib25Ub3VjaFN0YXJ0IiwiYWRkRXZlbnRMaXN0ZW5lciIsInRlYXJkb3duIiwiU3BvdFN3aXBlIiwiZW5hYmxlZCIsImRvY3VtZW50RWxlbWVudCIsInNwZWNpYWwiLCJzd2lwZSIsInNldHVwIiwidGFwIiwibm9vcCIsInNldHVwU3BvdFN3aXBlIiwic2V0dXBUb3VjaEhhbmRsZXIiLCJhZGRUb3VjaCIsImhhbmRsZVRvdWNoIiwiY2hhbmdlZFRvdWNoZXMiLCJmaXJzdCIsImV2ZW50VHlwZXMiLCJ0b3VjaHN0YXJ0IiwidG91Y2htb3ZlIiwidG91Y2hlbmQiLCJzaW11bGF0ZWRFdmVudCIsIk1vdXNlRXZlbnQiLCJzY3JlZW5YIiwic2NyZWVuWSIsImNsaWVudFgiLCJjbGllbnRZIiwiY3JlYXRlRXZlbnQiLCJpbml0TW91c2VFdmVudCIsImRpc3BhdGNoRXZlbnQiLCJkZWZhdWx0cyIsIiRwYXJlbnQiLCIkY29udGFpbmVyIiwid2FzV3JhcHBlZCIsIndyYXAiLCJjb250YWluZXIiLCJjb250YWluZXJDbGFzcyIsInN0aWNreUNsYXNzIiwic2Nyb2xsQ291bnQiLCJjaGVja0V2ZXJ5IiwiaXNTdHVjayIsIm9uTG9hZExpc3RlbmVyIiwiY29udGFpbmVySGVpZ2h0IiwiZWxlbUhlaWdodCIsIiRhbmNob3IiLCJfcGFyc2VQb2ludHMiLCJfc2V0U2l6ZXMiLCJzY3JvbGwiLCJfY2FsYyIsIl9yZW1vdmVTdGlja3kiLCJ0b3BQb2ludCIsIl9ldmVudHMiLCJyZXZlcnNlIiwidG9wQW5jaG9yIiwiYnRtIiwiYnRtQW5jaG9yIiwic2Nyb2xsSGVpZ2h0IiwicHRzIiwiYnJlYWtzIiwibGVuIiwicHQiLCJwbGFjZSIsInBvaW50cyIsImlzT24iLCJjYW5TdGljayIsIl9ldmVudHNIYW5kbGVyIiwiX3BhdXNlTGlzdGVuZXJzIiwiY2hlY2tTaXplcyIsImJvdHRvbVBvaW50IiwiX3NldFN0aWNreSIsInN0aWNrVG8iLCJtcmduIiwibm90U3R1Y2tUbyIsImlzVG9wIiwic3RpY2tUb1RvcCIsImFuY2hvclB0IiwiYW5jaG9ySGVpZ2h0IiwidG9wT3JCb3R0b20iLCJzdGlja3lPbiIsIm5ld0VsZW1XaWR0aCIsImNvbXAiLCJwZG5nbCIsInBhcnNlSW50IiwicGRuZ3IiLCJuZXdDb250YWluZXJIZWlnaHQiLCJoYXNDbGFzcyIsIl9zZXRCcmVha1BvaW50cyIsIm1Ub3AiLCJlbUNhbGMiLCJtYXJnaW5Ub3AiLCJtQnRtIiwibWFyZ2luQm90dG9tIiwid2luSGVpZ2h0IiwiaW5uZXJIZWlnaHQiLCJib3R0b20iLCJ1bndyYXAiLCJlbSIsImZvbnRTaXplIiwiaW5wdXQiLCJhbmltYXRpb25JbiIsImFuaW1hdGlvbk91dCIsIiR0cmlnZ2VycyIsImluZGV4IiwiJHRyaWdnZXIiLCJjb250cm9scyIsImNvbnRhaW5zSWQiLCJSZWdFeHAiLCJ0b2dnbGUiLCJ0b2dnbGVDbGFzcyIsIl91cGRhdGVBUklBIl0sIm1hcHBpbmdzIjoiO0FBQUE7QUFDQTs7QUFFQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7OztBQUdBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLGFBQUs7QUFDTDtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBLG1DQUEyQiwwQkFBMEIsRUFBRTtBQUN2RCx5Q0FBaUMsZUFBZTtBQUNoRDtBQUNBO0FBQ0E7O0FBRUE7QUFDQSw4REFBc0QsK0RBQStEOztBQUVySDtBQUNBOztBQUVBO0FBQ0E7Ozs7Ozs7QUM3REEsd0I7Ozs7Ozs7QUNBQTs7Ozs7OztBQUVBOzs7Ozs7QUFFQTs7QUFFRTs7O0FBR0YsU0FBU0EsR0FBVCxHQUFlO0FBQ2IsU0FBTyxzQkFBRSxNQUFGLEVBQVVDLElBQVYsQ0FBZSxLQUFmLE1BQTBCLEtBQWpDO0FBQ0Q7O0FBRUQ7Ozs7Ozs7O0FBUUEsU0FBU0MsV0FBVCxDQUFxQkMsTUFBckIsRUFBNkJDLFNBQTdCLEVBQXVDO0FBQ3JDRCxXQUFTQSxVQUFVLENBQW5CO0FBQ0EsU0FBT0UsS0FBS0MsS0FBTCxDQUFZRCxLQUFLRSxHQUFMLENBQVMsRUFBVCxFQUFhSixTQUFTLENBQXRCLElBQTJCRSxLQUFLRyxNQUFM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RSxRQUFRLElBQVo7O0FBRUEsVUFBSSxLQUFLa0QsUUFBTCxDQUFjckksRUFBZCxDQUFpQixTQUFqQixDQUFKLEVBQWlDO0FBQy9CcUIsK0JBQU9DLFNBQVAsQ0FBaUIsS0FBSytHLFFBQXRCLEVBQWdDLEtBQUswVCxXQUFyQyxFQUFrRCxZQUFXO0FBQzNENVcsZ0JBQU1zWCxXQUFOLENBQWtCLElBQWxCO0FBQ0EsZUFBS2hjLE9BQUwsQ0FBYSxlQUFiO0FBQ0EsZUFBS2lHLElBQUwsQ0FBVSxlQUFWLEVBQTJCakcsT0FBM0IsQ0FBbUMscUJBQW5DO0FBQ0QsU0FKRDtBQUtELE9BTkQsTUFPSztBQUNIWSwrQkFBT0ssVUFBUCxDQUFrQixLQUFLMkcsUUFBdkIsRUFBaUMsS0FBSzJULFlBQXRDLEVBQW9ELFlBQVc7QUFDN0Q3VyxnQkFBTXNYLFdBQU4sQ0FBa0IsS0FBbEI7QUFDQSxlQUFLaGMsT0FBTCxDQUFhLGdCQUFiO0FBQ0EsZUFBS2lHLElBQUwsQ0FBVSxlQUFWLEVBQTJCakcsT0FBM0IsQ0FBbUMscUJBQW5DO0FBQ0QsU0FKRDtBQUtEO0FBQ0Y7OztnQ0FFV2taLEksRUFBTTtBQUNoQixVQUFJL2IsS0FBSyxLQUFLeUssUUFBTCxDQUFjLENBQWQsRUFBaUJ6SyxFQUExQjtBQUNBLDZDQUFpQkEsRUFBakIseUJBQXVDQSxFQUF2QywwQkFBOERBLEVBQTlELFNBQ0czRCxJQURILENBQ1E7QUFDSix5QkFBaUIwZixPQUFPLElBQVAsR0FBYztBQUQzQixPQURSO0FBSUQ7O0FBRUQ7Ozs7Ozs7K0JBSVc7QUFDVCxXQUFLdFIsUUFBTCxDQUFjaEksR0FBZCxDQUFrQixhQUFsQjtBQUNEOzs7O0VBdEltQjJILHNCOztBQXlJdEJ3QixRQUFRa08sUUFBUixHQUFtQjtBQUNqQjs7Ozs7O0FBTUFqVyxXQUFTO0FBUFEsQ0FBbkI7O1FBVVErSCxPLEdBQUFBLE8iLCJmaWxlIjoiYXBwLmpzIiwic291cmNlc0NvbnRlbnQiOlsiIFx0Ly8gVGhlIG1vZHVsZSBjYWNoZVxuIFx0dmFyIGluc3RhbGxlZE1vZHVsZXMgPSB7fTtcblxuIFx0Ly8gVGhlIHJlcXVpcmUgZnVuY3Rpb25cbiBcdGZ1bmN0aW9uIF9fd2VicGFja19yZXF1aXJlX18obW9kdWxlSWQpIHtcblxuIFx0XHQvLyBDaGVjayBpZiBtb2R1bGUgaXMgaW4gY2FjaGVcbiBcdFx0aWYoaW5zdGFsbGVkTW9kdWxlc1ttb2R1bGVJZF0pIHtcbiBcdFx0XHRyZXR1cm4gaW5zdGFsbGVkTW9kdWxlc1ttb2R1bGVJZF0uZXhwb3J0cztcbiBcdFx0fVxuIFx0XHQvLyBDcmVhdGUgYSBuZXcgbW9kdWxlIChhbmQgcHV0IGl0IGludG8gdGhlIGNhY2hlKVxuIFx0XHR2YXIgbW9kdWxlID0gaW5zdGFsbGVkTW9kdWxlc1ttb2R1bGVJZF0gPSB7XG4gXHRcdFx0aTogbW9kdWxlSWQsXG4gXHRcdFx0bDogZmFsc2UsXG4gXHRcdFx0ZXhwb3J0czoge31cbiBcdFx0fTtcblxuIFx0XHQvLyBFeGVjdXRlIHRoZSBtb2R1bGUgZnVuY3Rpb25cbiBcdFx0bW9kdWxlc1ttb2R1bGVJZF0uY2FsbChtb2R1bGUuZXhwb3J0cywgbW9kdWxlLCBtb2R1bGUuZXhwb3J0cywgX193ZWJwYWNrX3JlcXVpcmVfXyk7XG5cbiBcdFx0Ly8gRmxhZyB0aGUgbW9kdWxlIGFzIGxvYWRlZFxuIFx0XHRtb2R1bGUubCA9IHRydWU7XG5cbiBcdFx0Ly8gUmV0dXJuIHRoZSBleHBvcnRzIG9mIHRoZSBtb2R1bGVcbiBcdFx0cmV0dXJuIG1vZHVsZS5leHBvcnRzO1xuIFx0fVxuXG5cbiBcdC8vIGV4cG9zZSB0aGUgbW9kdWxlcyBvYmplY3QgKF9fd2VicGFja19tb2R1bGVzX18pXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLm0gPSBtb2R1bGVzO1xuXG4gXHQvLyBleHBvc2UgdGhlIG1vZHVsZSBjYWNoZVxuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5jID0gaW5zdGFsbGVkTW9kdWxlcztcblxuIFx0Ly8gZGVmaW5lIGdldHRlciBmdW5jdGlvbiBmb3IgaGFybW9ueSBleHBvcnRzXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLmQgPSBmdW5jdGlvbihleHBvcnRzLCBuYW1lLCBnZXR0ZXIpIHtcbiBcdFx0aWYoIV9fd2VicGFja19yZXF1aXJlX18ubyhleHBvcnRzLCBuYW1lKSkge1xuIFx0XHRcdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShleHBvcnRzLCBuYW1lLCB7XG4gXHRcdFx0XHRjb25maWd1cmFibGU6IGZhbHNlLFxuIFx0XHRcdFx0ZW51bWVyYWJsZTogdHJ1ZSxcbiBcdFx0XHRcdGdldDogZ2V0dGVyXG4gXHRcdFx0fSk7XG4gXHRcdH1cbiBcdH07XG5cbiBcdC8vIGdldERlZmF1bHRFeHBvcnQgZnVuY3Rpb24gZm9yIGNvbXBhdGliaWxpdHkgd2l0aCBub24taGFybW9ueSBtb2R1bGVzXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLm4gPSBmdW5jdGlvbihtb2R1bGUpIHtcbiBcdFx0dmFyIGdldHRlciA9IG1vZHVsZSAmJiBtb2R1bGUuX19lc01vZHVsZSA/XG4gXHRcdFx0ZnVuY3Rpb24gZ2V0RGVmYXVsdCgpIHsgcmV0dXJuIG1vZHVsZVsnZGVmYXVsdCddOyB9IDpcbiBcdFx0XHRmdW5jdGlvbiBnZXRNb2R1bGVFeHBvcnRzKCkgeyByZXR1cm4gbW9kdWxlOyB9O1xuIFx0XHRfX3dlYnBhY2tfcmVxdWlyZV9fLmQoZ2V0dGVyLCAnYScsIGdldHRlcik7XG4gXHRcdHJldHVybiBnZXR0ZXI7XG4gXHR9O1xuXG4gXHQvLyBPYmplY3QucHJvdG90eXBlLmhhc093blByb3BlcnR5LmNhbGxcbiBcdF9fd2VicGFja19yZXF1aXJlX18ubyA9IGZ1bmN0aW9uKG9iamVjdCwgcHJvcGVydHkpIHsgcmV0dXJuIE9iamVjdC5wcm90b3R5cGUuaGFzT3duUHJvcGVydHkuY2FsbChvYmplY3QsIHByb3BlcnR5KTsgfTtcblxuIFx0Ly8gX193ZWJwYWNrX3B1YmxpY19wYXRoX19cbiBcdF9fd2VicGFja19yZXF1aXJlX18ucCA9IFwiXCI7XG5cbiBcdC8vIExvYWQgZW50cnkgbW9kdWxlIGFuZCByZXR1cm4gZXhwb3J0c1xuIFx0cmV0dXJuIF9fd2VicGFja19yZXF1aXJlX18oX193ZWJwYWNrX3JlcXVpcmVfXy5zID0gNik7XG5cblxuXG4vLyBXRUJQQUNLIEZPT1RFUiAvL1xuLy8gd2VicGFjay9ib290c3RyYXAgZTMwNDI1NjlhYzBhZTFiYmYzYzciLCJtb2R1bGUuZXhwb3J0cyA9IGpRdWVyeTtcblxuXG4vLy8vLy8vLy8vLy8vLy8vLy9cbi8vIFdFQlBBQ0sgRk9PVEVSXG4vLyBleHRlcm5hbCBcImpRdWVyeVwiXG4vLyBtb2R1bGUgaWQgPSAwXG4vLyBtb2R1bGUgY2h1bmtzID0gMCIsIlwidXNlIHN0cmljdFwiO1xuXG5pbXBvcnQgJCBmcm9tICdqcXVlcnknO1xuXG4vLyBDb3JlIEZvdW5kYXRpb24gVXRpbGl0aWVzLCB1dGlsaXplZCBpbiBhIG51bWJlciBvZiBwbGFjZXMuXG5cbiAgLyoqXG4gICAqIFJldHVybnMgYSBib29sZWFuIGZvciBSVEwgc3VwcG9ydFxuICAgKi9cbmZ1bmN0aW9uIHJ0bCgpIHtcbiAgcmV0dXJuICQoJ2h0bWwnKS5hdHRyKCdkaXInKSA9PT0gJ3J0bCc7XG59XG5cbi8qKlxuICogcmV0dXJucyBhIHJhbmRvbSBiYXNlLTM2IHVpZCB3aXRoIG5hbWVzcGFjaW5nXG4gKiBAZnVuY3Rpb25cbiAqIEBwYXJhbSB7TnVtYmVyfSBsZW5ndGggLSBudW1iZXIgb2YgcmFuZG9tIGJhc2UtMzYgZGlnaXRzIGRlc2lyZWQuIEluY3JlYXNlIGZvciBtb3JlIHJhbmRvbSBzdHJpbmdzLlxuICogQHBhcmFtIHtTdHJpbmd9IG5hbWVzcGFjZSAtIG5hbWUgb2YgcGx1Z2luIHRvIGJlIGluY29ycG9yYXRlZCBpbiB1aWQsIG9wdGlvbmFsLlxuICogQGRlZmF1bHQge1N0cmluZ30gJycgLSBpZiBubyBwbHVnaW4gbmFtZSBpcyBwcm92aWRlZCwgbm90aGluZyBpcyBhcHBlbmRlZCB0byB0aGUgdWlkLlxuICogQHJldHVybnMge1N0cmluZ30gLSB1bmlxdWUgaWRcbiAqL1xuZnVuY3Rpb24gR2V0WW9EaWdpdHMobGVuZ3RoLCBuYW1lc3BhY2Upe1xuICBsZW5ndGggPSBsZW5ndGggfHwgNjtcbiAgcmV0dXJuIE1hdGgucm91bmQoKE1hdGgucG93KDM2LCBsZW5ndGggKyAxKSAtIE1hdGgucmFuZG9tKCkgKiBNYXRoLnBvdygzNiwgbGVuZ3RoKSkpLnRvU3RyaW5nKDM2KS5zbGljZSgxKSArIChuYW1lc3BhY2UgPyBgLSR7bmFtZXNwYWNlfWAgOiAnJyk7XG59XG5cbi8qKlxuICogRXNjYXBlIGEgc3RyaW5nIHNvIGl0IGNhbiBiZSB1c2VkIGFzIGEgcmVnZXhwIHBhdHRlcm5cbiAqIEBmdW5jdGlvblxuICogQHNlZSBodHRwczovL3N0YWNrb3ZlcmZsb3cuY29tL2EvOTMxMDc1Mi80MzE3Mzg0XG4gKlxuICogQHBhcmFtIHtTdHJpbmd9IHN0ciAtIHN0cmluZyB0byBlc2NhcGUuXG4gKiBAcmV0dXJucyB7U3RyaW5nfSAtIGVzY2FwZWQgc3RyaW5nXG4gKi9cbmZ1bmN0aW9uIFJlZ0V4cEVzY2FwZShzdHIpe1xuICByZXR1cm4gc3RyLnJlcGxhY2UoL1stW1xcXXt9KCkqKz8uLFxcXFxeJHwjXFxzXS9nLCAnXFxcXCQmJyk7XG59XG5cbmZ1bmN0aW9uIHRyYW5zaXRpb25lbmQoJGVsZW0pe1xuICB2YXIgdHJhbnNpdGlvbnMgPSB7XG4gICAgJ3RyYW5zaXRpb24nOiAndHJhbnNpdGlvbmVuZCcsXG4gICAgJ1dlYmtpdFRyYW5zaXRpb24nOiAnd2Via2l0VHJhbnNpdGlvbkVuZCcsXG4gICAgJ01velRyYW5zaXRpb24nOiAndHJhbnNpdGlvbmVuZCcsXG4gICAgJ09UcmFuc2l0aW9uJzogJ290cmFuc2l0aW9uZW5kJ1xuICB9O1xuICB2YXIgZWxlbSA9IGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoJ2RpdicpLFxuICAgICAgZW5kO1xuXG4gIGZvciAodmFyIHQgaW4gdHJhbnNpdGlvbnMpe1xuICAgIGlmICh0eXBlb2YgZWxlbS5zdHlsZVt0XSAhPT0gJ3VuZGVmaW5lZCcpe1xuICAgICAgZW5kID0gdHJhbnNpdGlvbnNbdF07XG4gICAgfVxuICB9XG4gIGlmKGVuZCl7XG4gICAgcmV0dXJuIGVuZDtcbiAgfWVsc2V7XG4gICAgZW5kID0gc2V0VGltZW91dChmdW5jdGlvbigpe1xuICAgICAgJGVsZW0udHJpZ2dlckhhbmRsZXIoJ3RyYW5zaXRpb25lbmQnLCBbJGVsZW1dKTtcbiAgICB9LCAxKTtcbiAgICByZXR1cm4gJ3RyYW5zaXRpb25lbmQnO1xuICB9XG59XG5cbi8qKlxuICogUmV0dXJuIGFuIGV2ZW50IHR5cGUgdG8gbGlzdGVuIGZvciB3aW5kb3cgbG9hZC5cbiAqXG4gKiBJZiBgJGVsZW1gIGlzIHBhc3NlZCwgYW4gZXZlbnQgd2lsbCBiZSB0cmlnZ2VyZWQgb24gYCRlbGVtYC4gSWYgd2luZG93IGlzIGFscmVhZHkgbG9hZGVkLCB0aGUgZXZlbnQgd2lsbCBzdGlsbCBiZSB0cmlnZ2VyZWQuXG4gKiBJZiBgaGFuZGxlcmAgaXMgcGFzc2VkLCBhdHRhY2ggaXQgdG8gdGhlIGV2ZW50IG9uIGAkZWxlbWAuXG4gKiBDYWxsaW5nIGBvbkxvYWRgIHdpdGhvdXQgaGFuZGxlciBhbGxvd3MgeW91IHRvIGdldCB0aGUgZXZlbnQgdHlwZSB0aGF0IHdpbGwgYmUgdHJpZ2dlcmVkIGJlZm9yZSBhdHRhY2hpbmcgdGhlIGhhbmRsZXIgYnkgeW91cnNlbGYuXG4gKiBAZnVuY3Rpb25cbiAqXG4gKiBAcGFyYW0ge09iamVjdH0gW10gJGVsZW0gLSBqUXVlcnkgZWxlbWVudCBvbiB3aGljaCB0aGUgZXZlbnQgd2lsbCBiZSB0cmlnZ2VyZWQgaWYgcGFzc2VkLlxuICogQHBhcmFtIHtGdW5jdGlvbn0gW10gaGFuZGxlciAtIGZ1bmN0aW9uIHRvIGF0dGFjaCB0byB0aGUgZXZlbnQuXG4gKiBAcmV0dXJucyB7U3RyaW5nfSAtIGV2ZW50IHR5cGUgdGhhdCBzaG91bGQgb3Igd2lsbCBiZSB0cmlnZ2VyZWQuXG4gKi9cbmZ1bmN0aW9uIG9uTG9hZCgkZWxlbSwgaGFuZGxlcikge1xuICBjb25zdCBkaWRMb2FkID0gZG9jdW1lbnQucmVhZHlTdGF0ZSA9PT0gJ2NvbXBsZXRlJztcbiAgY29uc3QgZXZlbnRUeXBlID0gKGRpZExvYWQgPyAnX2RpZExvYWQnIDogJ2xvYWQnKSArICcuemYudXRpbC5vbkxvYWQnO1xuICBjb25zdCBjYiA9ICgpID0+ICRlbGVtLnRyaWdnZXJIYW5kbGVyKGV2ZW50VHlwZSk7XG5cbiAgaWYgKCRlbGVtKSB7XG4gICAgaWYgKGhhbmRsZXIpICRlbGVtLm9uZShldmVudFR5cGUsIGhhbmRsZXIpO1xuXG4gICAgaWYgKGRpZExvYWQpXG4gICAgICBzZXRUaW1lb3V0KGNiKTtcbiAgICBlbHNlXG4gICAgICAkKHdpbmRvdykub25lKCdsb2FkJywgY2IpO1xuICB9XG5cbiAgcmV0dXJuIGV2ZW50VHlwZTtcbn1cblxuLyoqXG4gKiBSZXR1bnMgYW4gaGFuZGxlciBmb3IgdGhlIGBtb3VzZWxlYXZlYCB0aGF0IGlnbm9yZSBkaXNhcHBlYXJlZCBtb3VzZXMuXG4gKlxuICogSWYgdGhlIG1vdXNlIFwiZGlzYXBwZWFyZWRcIiBmcm9tIHRoZSBkb2N1bWVudCAobGlrZSB3aGVuIGdvaW5nIG9uIGEgYnJvd3NlciBVSSBlbGVtZW50LCBTZWUgaHR0cHM6Ly9naXQuaW8vemYtMTE0MTApLFxuICogdGhlIGV2ZW50IGlzIGlnbm9yZWQuXG4gKiAtIElmIHRoZSBgaWdub3JlTGVhdmVXaW5kb3dgIGlzIGB0cnVlYCwgdGhlIGV2ZW50IGlzIGlnbm9yZWQgd2hlbiB0aGUgdXNlciBhY3R1YWxseSBsZWZ0IHRoZSB3aW5kb3dcbiAqICAgKGxpa2UgYnkgc3dpdGNoaW5nIHRvIGFuIG90aGVyIHdpbmRvdyB3aXRoIFtBbHRdK1tUYWJdKS5cbiAqIC0gSWYgdGhlIGBpZ25vcmVSZWFwcGVhcmAgaXMgYHRydWVgLCB0aGUgZXZlbnQgd2lsbCBiZSBpZ25vcmVkIHdoZW4gdGhlIG1vdXNlIHdpbGwgcmVhcHBlYXIgbGF0ZXIgb24gdGhlIGRvY3VtZW50XG4gKiAgIG91dHNpZGUgb2YgdGhlIGVsZW1lbnQgaXQgbGVmdC5cbiAqXG4gKiBAZnVuY3Rpb25cbiAqXG4gKiBAcGFyYW0ge0Z1bmN0aW9ufSBbXSBoYW5kbGVyIC0gaGFuZGxlciBmb3IgdGhlIGZpbHRlcmVkIGBtb3VzZWxlYXZlYCBldmVudCB0byB3YXRjaC5cbiAqIEBwYXJhbSB7T2JqZWN0fSBbXSBvcHRpb25zIC0gb2JqZWN0IG9mIG9wdGlvbnM6XG4gKiAtIHtCb29sZWFufSBbZmFsc2VdIGlnbm9yZUxlYXZlV2luZG93IC0gYWxzbyBpZ25vcmUgd2hlbiB0aGUgdXNlciBzd2l0Y2hlZCB3aW5kb3dzLlxuICogLSB7Qm9vbGVhbn0gW2ZhbHNlXSBpZ25vcmVSZWFwcGVhciAtIGFsc28gaWdub3JlIHdoZW4gdGhlIG1vdXNlIHJlYXBwZWFyZWQgb3V0c2lkZSBvZiB0aGUgZWxlbWVudCBpdCBsZWZ0LlxuICogQHJldHVybnMge0Z1bmN0aW9ufSAtIGZpbHRlcmVkIGhhbmRsZXIgdG8gdXNlIHRvIGxpc3RlbiBvbiB0aGUgYG1vdXNlbGVhdmVgIGV2ZW50LlxuICovXG5mdW5jdGlvbiBpZ25vcmVNb3VzZWRpc2FwcGVhcihoYW5kbGVyLCB7IGlnbm9yZUxlYXZlV2luZG93ID0gZmFsc2UsIGlnbm9yZVJlYXBwZWFyID0gZmFsc2UgfSA9IHt9KSB7XG4gIHJldHVybiBmdW5jdGlvbiBsZWF2ZUV2ZW50SGFuZGxlcihlTGVhdmUsIC4uLnJlc3QpIHtcbiAgICBjb25zdCBjYWxsYmFjayA9IGhhbmRsZXIuYmluZCh0aGlzLCBlTGVhdmUsIC4uLnJlc3QpO1xuXG4gICAgLy8gVGhlIG1vdXNlIGxlZnQ6IGNhbGwgdGhlIGdpdmVuIGNhbGxiYWNrIGlmIHRoZSBtb3VzZSBlbnRlcmVkIGVsc2V3aGVyZVxuICAgIGlmIChlTGVhdmUucmVsYXRlZFRhcmdldCAhPT0gbnVsbCkge1xuICAgICAgcmV0dXJuIGNhbGxiYWNrKCk7XG4gICAgfVxuXG4gICAgLy8gT3RoZXJ3aXNlLCBjaGVjayBpZiB0aGUgbW91c2UgYWN0dWFsbHkgbGVmdCB0aGUgd2luZG93LlxuICAgIC8vIEluIGZpcmVmb3ggaWYgdGhlIHVzZXIgc3dpdGNoZWQgYmV0d2VlbiB3aW5kb3dzLCB0aGUgd2luZG93IHNpbGwgaGF2ZSB0aGUgZm9jdXMgYnkgdGhlIHRpbWVcbiAgICAvLyB0aGUgZXZlbnQgaXMgdHJpZ2dlcmVkLiBXZSBoYXZlIHRvIGRlYm91bmNlIHRoZSBldmVudCB0byB0ZXN0IHRoaXMgY2FzZS5cbiAgICBzZXRUaW1lb3V0KGZ1bmN0aW9uIGxlYXZlRXZlbnREZWJvdW5jZXIoKSB7XG4gICAgICBpZiAoIWlnbm9yZUxlYXZlV2luZG93ICYmIGRvY3VtZW50Lmhhc0ZvY3VzICYmICFkb2N1bWVudC5oYXNGb2N1cygpKSB7XG4gICAgICAgIHJldHVybiBjYWxsYmFjaygpO1xuICAgICAgfVxuXG4gICAgICAvLyBPdGhlcndpc2UsIHdhaXQgZm9yIHRoZSBtb3VzZSB0byByZWVhcGVhciBvdXRzaWRlIG9mIHRoZSBlbGVtZW50LFxuICAgICAgaWYgKCFpZ25vcmVSZWFwcGVhcikge1xuICAgICAgICAkKGRvY3VtZW50KS5vbmUoJ21vdXNlZW50ZXInLCBmdW5jdGlvbiByZWVudGVyRXZlbnRIYW5kbGVyKGVSZWVudGVyKSB7XG4gICAgICAgICAgaWYgKCEkKGVMZWF2ZS5jdXJyZW50VGFyZ2V0KS5oYXMoZVJlZW50ZXIudGFyZ2V0KS5sZW5ndGgpIHtcbiAgICAgICAgICAgIC8vIEZpbGwgd2hlcmUgdGhlIG1vdXNlIGZpbmFsbHkgZW50ZXJlZC5cbiAgICAgICAgICAgIGVMZWF2ZS5yZWxhdGVkVGFyZ2V0ID0gZVJlZW50ZXIudGFyZ2V0O1xuICAgICAgICAgICAgY2FsbGJhY2soKTtcbiAgICAgICAgICB9XG4gICAgICAgIH0pO1xuICAgICAgfVxuXG4gICAgfSwgMCk7XG4gIH07XG59XG5cbmV4cG9ydCB7IHJ0bCwgR2V0WW9EaWdpdHMsIFJlZ0V4cEVzY2FwZSwgdHJhbnNpdGlvbmVuZCwgb25Mb2FkLCBpZ25vcmVNb3VzZWRpc2FwcGVhciB9O1xuXG5cblxuLy8gV0VCUEFDSyBGT09URVIgLy9cbi8vIC4vbm9kZV9tb2R1bGVzL2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi5jb3JlLnV0aWxzLmpzIiwiJ3VzZSBzdHJpY3QnO1xuXG5pbXBvcnQgJCBmcm9tICdqcXVlcnknO1xuXG4vLyBEZWZhdWx0IHNldCBvZiBtZWRpYSBxdWVyaWVzXG5jb25zdCBkZWZhdWx0UXVlcmllcyA9IHtcbiAgJ2RlZmF1bHQnIDogJ29ubHkgc2NyZWVuJyxcbiAgbGFuZHNjYXBlIDogJ29ubHkgc2NyZWVuIGFuZCAob3JpZW50YXRpb246IGxhbmRzY2FwZSknLFxuICBwb3J0cmFpdCA6ICdvbmx5IHNjcmVlbiBhbmQgKG9yaWVudGF0aW9uOiBwb3J0cmFpdCknLFxuICByZXRpbmEgOiAnb25seSBzY3JlZW4gYW5kICgtd2Via2l0LW1pbi1kZXZpY2UtcGl4ZWwtcmF0aW86IDIpLCcgK1xuICAgICdvbmx5IHNjcmVlbiBhbmQgKG1pbi0tbW96LWRldmljZS1waXhlbC1yYXRpbzogMiksJyArXG4gICAgJ29ubHkgc2NyZWVuIGFuZCAoLW8tbWluLWRldmljZS1waXhlbC1yYXRpbzogMi8xKSwnICtcbiAgICAnb25seSBzY3JlZW4gYW5kIChtaW4tZGV2aWNlLXBpeGVsLXJhdGlvOiAyKSwnICtcbiAgICAnb25seSBzY3JlZW4gYW5kIChtaW4tcmVzb2x1dGlvbjogMTkyZHBpKSwnICtcbiAgICAnb25seSBzY3JlZW4gYW5kIChtaW4tcmVzb2x1dGlvbjogMmRwcHgpJ1xuICB9O1xuXG5cbi8vIG1hdGNoTWVkaWEoKSBwb2x5ZmlsbCAtIFRlc3QgYSBDU1MgbWVkaWEgdHlwZS9xdWVyeSBpbiBKUy5cbi8vIEF1dGhvcnMgJiBjb3B5cmlnaHQoYykgMjAxMjogU2NvdHQgSmVobCwgUGF1bCBJcmlzaCwgTmljaG9sYXMgWmFrYXMsIERhdmlkIEtuaWdodC4gTUlUIGxpY2Vuc2Vcbi8qIGVzbGludC1kaXNhYmxlICovXG53aW5kb3cubWF0Y2hNZWRpYSB8fCAod2luZG93Lm1hdGNoTWVkaWEgPSAoZnVuY3Rpb24gKCkge1xuICBcInVzZSBzdHJpY3RcIjtcblxuICAvLyBGb3IgYnJvd3NlcnMgdGhhdCBzdXBwb3J0IG1hdGNoTWVkaXVtIGFwaSBzdWNoIGFzIElFIDkgYW5kIHdlYmtpdFxuICB2YXIgc3R5bGVNZWRpYSA9ICh3aW5kb3cuc3R5bGVNZWRpYSB8fCB3aW5kb3cubWVkaWEpO1xuXG4gIC8vIEZvciB0aG9zZSB0aGF0IGRvbid0IHN1cHBvcnQgbWF0Y2hNZWRpdW1cbiAgaWYgKCFzdHlsZU1lZGlhKSB7XG4gICAgdmFyIHN0eWxlICAgPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCdzdHlsZScpLFxuICAgIHNjcmlwdCAgICAgID0gZG9jdW1lbnQuZ2V0RWxlbWVudHNCeVRhZ05hbWUoJ3NjcmlwdCcpWzBdLFxuICAgIGluZm8gICAgICAgID0gbnVsbDtcblxuICAgIHN0eWxlLnR5cGUgID0gJ3RleHQvY3NzJztcbiAgICBzdHlsZS5pZCAgICA9ICdtYXRjaG1lZGlhanMtdGVzdCc7XG5cbiAgICBpZiAoIXNjcmlwdCkge1xuICAgICAgZG9jdW1lbnQuaGVhZC5hcHBlbmRDaGlsZChzdHlsZSk7XG4gICAgfSBlbHNlIHtcbiAgICAgIHNjcmlwdC5wYXJlbnROb2RlLmluc2VydEJlZm9yZShzdHlsZSwgc2NyaXB0KTtcbiAgICB9XG5cbiAgICAvLyAnc3R5bGUuY3VycmVudFN0eWxlJyBpcyB1c2VkIGJ5IElFIDw9IDggYW5kICd3aW5kb3cuZ2V0Q29tcHV0ZWRTdHlsZScgZm9yIGFsbCBvdGhlciBicm93c2Vyc1xuICAgIGluZm8gPSAoJ2dldENvbXB1dGVkU3R5bGUnIGluIHdpbmRvdykgJiYgd2luZG93LmdldENvbXB1dGVkU3R5bGUoc3R5bGUsIG51bGwpIHx8IHN0eWxlLmN1cnJlbnRTdHlsZTtcblxuICAgIHN0eWxlTWVkaWEgPSB7XG4gICAgICBtYXRjaE1lZGl1bTogZnVuY3Rpb24gKG1lZGlhKSB7XG4gICAgICAgIHZhciB0ZXh0ID0gJ0BtZWRpYSAnICsgbWVkaWEgKyAneyAjbWF0Y2htZWRpYWpzLXRlc3QgeyB3aWR0aDogMXB4OyB9IH0nO1xuXG4gICAgICAgIC8vICdzdHlsZS5zdHlsZVNoZWV0JyBpcyB1c2VkIGJ5IElFIDw9IDggYW5kICdzdHlsZS50ZXh0Q29udGVudCcgZm9yIGFsbCBvdGhlciBicm93c2Vyc1xuICAgICAgICBpZiAoc3R5bGUuc3R5bGVTaGVldCkge1xuICAgICAgICAgIHN0eWxlLnN0eWxlU2hlZXQuY3NzVGV4dCA9IHRleHQ7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgc3R5bGUudGV4dENvbnRlbnQgPSB0ZXh0O1xuICAgICAgICB9XG5cbiAgICAgICAgLy8gVGVzdCBpZiBtZWRpYSBxdWVyeSBpcyB0cnVlIG9yIGZhbHNlXG4gICAgICAgIHJldHVybiBpbmZvLndpZHRoID09PSAnMXB4JztcbiAgICAgIH1cbiAgICB9O1xuICB9XG5cbiAgcmV0dXJuIGZ1bmN0aW9uKG1lZGlhKSB7XG4gICAgcmV0dXJuIHtcbiAgICAgIG1hdGNoZXM6IHN0eWxlTWVkaWEubWF0Y2hNZWRpdW0obWVkaWEgfHwgJ2FsbCcpLFxuICAgICAgbWVkaWE6IG1lZGlhIHx8ICdhbGwnXG4gICAgfTtcbiAgfTtcbn0pKCkpO1xuLyogZXNsaW50LWVuYWJsZSAqL1xuXG52YXIgTWVkaWFRdWVyeSA9IHtcbiAgcXVlcmllczogW10sXG5cbiAgY3VycmVudDogJycsXG5cbiAgLyoqXG4gICAqIEluaXRpYWxpemVzIHRoZSBtZWRpYSBxdWVyeSBoZWxwZXIsIGJ5IGV4dHJhY3RpbmcgdGhlIGJyZWFrcG9pbnQgbGlzdCBmcm9tIHRoZSBDU1MgYW5kIGFjdGl2YXRpbmcgdGhlIGJyZWFrcG9pbnQgd2F0Y2hlci5cbiAgICogQGZ1bmN0aW9uXG4gICAqIEBwcml2YXRlXG4gICAqL1xuICBfaW5pdCgpIHtcbiAgICB2YXIgc2VsZiA9IHRoaXM7XG4gICAgdmFyICRtZXRhID0gJCgnbWV0YS5mb3VuZGF0aW9uLW1xJyk7XG4gICAgaWYoISRtZXRhLmxlbmd0aCl7XG4gICAgICAkKCc8bWV0YSBjbGFzcz1cImZvdW5kYXRpb24tbXFcIj4nKS5hcHBlbmRUbyhkb2N1bWVudC5oZWFkKTtcbiAgICB9XG5cbiAgICB2YXIgZXh0cmFjdGVkU3R5bGVzID0gJCgnLmZvdW5kYXRpb24tbXEnKS5jc3MoJ2ZvbnQtZmFtaWx5Jyk7XG4gICAgdmFyIG5hbWVkUXVlcmllcztcblxuICAgIG5hbWVkUXVlcmllcyA9IHBhcnNlU3R5bGVUb09iamVjdChleHRyYWN0ZWRTdHlsZXMpO1xuXG4gICAgZm9yICh2YXIga2V5IGluIG5hbWVkUXVlcmllcykge1xuICAgICAgaWYobmFtZWRRdWVyaWVzLmhhc093blByb3BlcnR5KGtleSkpIHtcbiAgICAgICAgc2VsZi5xdWVyaWVzLnB1c2goe1xuICAgICAgICAgIG5hbWU6IGtleSxcbiAgICAgICAgICB2YWx1ZTogYG9ubHkgc2NyZWVuIGFuZCAobWluLXdpZHRoOiAke25hbWVkUXVlcmllc1trZXldfSlgXG4gICAgICAgIH0pO1xuICAgICAgfVxuICAgIH1cblxuICAgIHRoaXMuY3VycmVudCA9IHRoaXMuX2dldEN1cnJlbnRTaXplKCk7XG5cbiAgICB0aGlzLl93YXRjaGVyKCk7XG4gIH0sXG5cbiAgLyoqXG4gICAqIENoZWNrcyBpZiB0aGUgc2NyZWVuIGlzIGF0IGxlYXN0IGFzIHdpZGUgYXMgYSBicmVha3BvaW50LlxuICAgKiBAZnVuY3Rpb25cbiAgICogQHBhcmFtIHtTdHJpbmd9IHNpemUgLSBOYW1lIG9mIHRoZSBicmVha3BvaW50IHRvIGNoZWNrLlxuICAgKiBAcmV0dXJucyB7Qm9vbGVhbn0gYHRydWVgIGlmIHRoZSBicmVha3BvaW50IG1hdGNoZXMsIGBmYWxzZWAgaWYgaXQncyBzbWFsbGVyLlxuICAgKi9cbiAgYXRMZWFzdChzaXplKSB7XG4gICAgdmFyIHF1ZXJ5ID0gdGhpcy5nZXQoc2l6ZSk7XG5cbiAgICBpZiAocXVlcnkpIHtcbiAgICAgIHJldHVybiB3aW5kb3cubWF0Y2hNZWRpYShxdWVyeSkubWF0Y2hlcztcbiAgICB9XG5cbiAgICByZXR1cm4gZmFsc2U7XG4gIH0sXG5cbiAgLyoqXG4gICAqIENoZWNrcyBpZiB0aGUgc2NyZWVuIG1hdGNoZXMgdG8gYSBicmVha3BvaW50LlxuICAgKiBAZnVuY3Rpb25cbiAgICogQHBhcmFtIHtTdHJpbmd9IHNpemUgLSBOYW1lIG9mIHRoZSBicmVha3BvaW50IHRvIGNoZWNrLCBlaXRoZXIgJ3NtYWxsIG9ubHknIG9yICdzbWFsbCcuIE9taXR0aW5nICdvbmx5JyBmYWxscyBiYWNrIHRvIHVzaW5nIGF0TGVhc3QoKSBtZXRob2QuXG4gICAqIEByZXR1cm5zIHtCb29sZWFufSBgdHJ1ZWAgaWYgdGhlIGJyZWFrcG9pbnQgbWF0Y2hlcywgYGZhbHNlYCBpZiBpdCBkb2VzIG5vdC5cbiAgICovXG4gIGlzKHNpemUpIHtcbiAgICBzaXplID0gc2l6ZS50cmltKCkuc3BsaXQoJyAnKTtcbiAgICBpZihzaXplLmxlbmd0aCA+IDEgJiYgc2l6ZVsxXSA9PT0gJ29ubHknKSB7XG4gICAgICBpZihzaXplWzBdID09PSB0aGlzLl9nZXRDdXJyZW50U2l6ZSgpKSByZXR1cm4gdHJ1ZTtcbiAgICB9IGVsc2Uge1xuICAgICAgcmV0dXJuIHRoaXMuYXRMZWFzdChzaXplWzBdKTtcbiAgICB9XG4gICAgcmV0dXJuIGZhbHNlO1xuICB9LFxuXG4gIC8qKlxuICAgKiBHZXRzIHRoZSBtZWRpYSBxdWVyeSBvZiBhIGJyZWFrcG9pbnQuXG4gICAqIEBmdW5jdGlvblxuICAgKiBAcGFyYW0ge1N0cmluZ30gc2l6ZSAtIE5hbWUgb2YgdGhlIGJyZWFrcG9pbnQgdG8gZ2V0LlxuICAgKiBAcmV0dXJucyB7U3RyaW5nfG51bGx9IC0gVGhlIG1lZGlhIHF1ZXJ5IG9mIHRoZSBicmVha3BvaW50LCBvciBgbnVsbGAgaWYgdGhlIGJyZWFrcG9pbnQgZG9lc24ndCBleGlzdC5cbiAgICovXG4gIGdldChzaXplKSB7XG4gICAgZm9yICh2YXIgaSBpbiB0aGlzLnF1ZXJpZXMpIHtcbiAgICAgIGlmKHRoaXMucXVlcmllcy5oYXNPd25Qcm9wZXJ0eShpKSkge1xuICAgICAgICB2YXIgcXVlcnkgPSB0aGlzLnF1ZXJpZXNbaV07XG4gICAgICAgIGlmIChzaXplID09PSBxdWVyeS5uYW1lKSByZXR1cm4gcXVlcnkudmFsdWU7XG4gICAgICB9XG4gICAgfVxuXG4gICAgcmV0dXJuIG51bGw7XG4gIH0sXG5cbiAgLyoqXG4gICAqIEdldHMgdGhlIGN1cnJlbnQgYnJlYWtwb2ludCBuYW1lIGJ5IHRlc3RpbmcgZXZlcnkgYnJlYWtwb2ludCBhbmQgcmV0dXJuaW5nIHRoZSBsYXN0IG9uZSB0byBtYXRjaCAodGhlIGJpZ2dlc3Qgb25lKS5cbiAgICogQGZ1bmN0aW9uXG4gICAqIEBwcml2YXRlXG4gICAqIEByZXR1cm5zIHtTdHJpbmd9IE5hbWUgb2YgdGhlIGN1cnJlbnQgYnJlYWtwb2ludC5cbiAgICovXG4gIF9nZXRDdXJyZW50U2l6ZSgpIHtcbiAgICB2YXIgbWF0Y2hlZDtcblxuICAgIGZvciAodmFyIGkgPSAwOyBpIDwgdGhpcy5xdWVyaWVzLmxlbmd0aDsgaSsrKSB7XG4gICAgICB2YXIgcXVlcnkgPSB0aGlzLnF1ZXJpZXNbaV07XG5cbiAgICAgIGlmICh3aW5kb3cubWF0Y2hNZWRpYShxdWVyeS52YWx1ZSkubWF0Y2hlcykge1xuICAgICAgICBtYXRjaGVkID0gcXVlcnk7XG4gICAgICB9XG4gICAgfVxuXG4gICAgaWYgKHR5cGVvZiBtYXRjaGVkID09PSAnb2JqZWN0Jykge1xuICAgICAgcmV0dXJuIG1hdGNoZWQubmFtZTtcbiAgICB9IGVsc2Uge1xuICAgICAgcmV0dXJuIG1hdGNoZWQ7XG4gICAgfVxuICB9LFxuXG4gIC8qKlxuICAgKiBBY3RpdmF0ZXMgdGhlIGJyZWFrcG9pbnQgd2F0Y2hlciwgd2hpY2ggZmlyZXMgYW4gZXZlbnQgb24gdGhlIHdpbmRvdyB3aGVuZXZlciB0aGUgYnJlYWtwb2ludCBjaGFuZ2VzLlxuICAgKiBAZnVuY3Rpb25cbiAgICogQHByaXZhdGVcbiAgICovXG4gIF93YXRjaGVyKCkge1xuICAgICQod2luZG93KS5vZmYoJ3Jlc2l6ZS56Zi5tZWRpYXF1ZXJ5Jykub24oJ3Jlc2l6ZS56Zi5tZWRpYXF1ZXJ5JywgKCkgPT4ge1xuICAgICAgdmFyIG5ld1NpemUgPSB0aGlzLl9nZXRDdXJyZW50U2l6ZSgpLCBjdXJyZW50U2l6ZSA9IHRoaXMuY3VycmVudDtcblxuICAgICAgaWYgKG5ld1NpemUgIT09IGN1cnJlbnRTaXplKSB7XG4gICAgICAgIC8vIENoYW5nZSB0aGUgY3VycmVudCBtZWRpYSBxdWVyeVxuICAgICAgICB0aGlzLmN1cnJlbnQgPSBuZXdTaXplO1xuXG4gICAgICAgIC8vIEJyb2FkY2FzdCB0aGUgbWVkaWEgcXVlcnkgY2hhbmdlIG9uIHRoZSB3aW5kb3dcbiAgICAgICAgJCh3aW5kb3cpLnRyaWdnZXIoJ2NoYW5nZWQuemYubWVkaWFxdWVyeScsIFtuZXdTaXplLCBjdXJyZW50U2l6ZV0pO1xuICAgICAgfVxuICAgIH0pO1xuICB9XG59O1xuXG5cblxuLy8gVGhhbmsgeW91OiBodHRwczovL2dpdGh1Yi5jb20vc2luZHJlc29yaHVzL3F1ZXJ5LXN0cmluZ1xuZnVuY3Rpb24gcGFyc2VTdHlsZVRvT2JqZWN0KHN0cikge1xuICB2YXIgc3R5bGVPYmplY3QgPSB7fTtcblxuICBpZiAodHlwZW9mIHN0ciAhPT0gJ3N0cmluZycpIHtcbiAgICByZXR1cm4gc3R5bGVPYmplY3Q7XG4gIH1cblxuICBzdHIgPSBzdHIudHJpbSgpLnNsaWNlKDEsIC0xKTsgLy8gYnJvd3NlcnMgcmUtcXVvdGUgc3RyaW5nIHN0eWxlIHZhbHVlc1xuXG4gIGlmICghc3RyKSB7XG4gICAgcmV0dXJuIHN0eWxlT2JqZWN0O1xuICB9XG5cbiAgc3R5bGVPYmplY3QgPSBzdHIuc3BsaXQoJyYnKS5yZWR1Y2UoZnVuY3Rpb24ocmV0LCBwYXJhbSkge1xuICAgIHZhciBwYXJ0cyA9IHBhcmFtLnJlcGxhY2UoL1xcKy9nLCAnICcpLnNwbGl0KCc9Jyk7XG4gICAgdmFyIGtleSA9IHBhcnRzWzBdO1xuICAgIHZhciB2YWwgPSBwYXJ0c1sxXTtcbiAgICBrZXkgPSBkZWNvZGVVUklDb21wb25lbnQoa2V5KTtcblxuICAgIC8vIG1pc3NpbmcgYD1gIHNob3VsZCBiZSBgbnVsbGA6XG4gICAgLy8gaHR0cDovL3czLm9yZy9UUi8yMDEyL1dELXVybC0yMDEyMDUyNC8jY29sbGVjdC11cmwtcGFyYW1ldGVyc1xuICAgIHZhbCA9IHR5cGVvZiB2YWwgPT09ICd1bmRlZmluZWQnID8gbnVsbCA6IGRlY29kZVVSSUNvbXBvbmVudCh2YWwpO1xuXG4gICAgaWYgKCFyZXQuaGFzT3duUHJvcGVydHkoa2V5KSkge1xuICAgICAgcmV0W2tleV0gPSB2YWw7XG4gICAgfSBlbHNlIGlmIChBcnJheS5pc0FycmF5KHJldFtrZXldKSkge1xuICAgICAgcmV0W2tleV0ucHVzaCh2YWwpO1xuICAgIH0gZWxzZSB7XG4gICAgICByZXRba2V5XSA9IFtyZXRba2V5XSwgdmFsXTtcbiAgICB9XG4gICAgcmV0dXJuIHJldDtcbiAgfSwge30pO1xuXG4gIHJldHVybiBzdHlsZU9iamVjdDtcbn1cblxuZXhwb3J0IHtNZWRpYVF1ZXJ5fTtcblxuXG5cbi8vIFdFQlBBQ0sgRk9PVEVSIC8vXG4vLyAuL25vZGVfbW9kdWxlcy9mb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24udXRpbC5tZWRpYVF1ZXJ5LmpzIiwiJ3VzZSBzdHJpY3QnO1xuXG5pbXBvcnQgJCBmcm9tICdqcXVlcnknO1xuaW1wb3J0IHsgdHJhbnNpdGlvbmVuZCB9IGZyb20gJy4vZm91bmRhdGlvbi5jb3JlLnV0aWxzJztcblxuLyoqXG4gKiBNb3Rpb24gbW9kdWxlLlxuICogQG1vZHVsZSBmb3VuZGF0aW9uLm1vdGlvblxuICovXG5cbmNvbnN0IGluaXRDbGFzc2VzICAgPSBbJ211aS1lbnRlcicsICdtdWktbGVhdmUnXTtcbmNvbnN0IGFjdGl2ZUNsYXNzZXMgPSBbJ211aS1lbnRlci1hY3RpdmUnLCAnbXVpLWxlYXZlLWFjdGl2ZSddO1xuXG5jb25zdCBNb3Rpb24gPSB7XG4gIGFuaW1hdGVJbjogZnVuY3Rpb24oZWxlbWVudCwgYW5pbWF0aW9uLCBjYikge1xuICAgIGFuaW1hdGUodHJ1ZSwgZWxlbWVudCwgYW5pbWF0aW9uLCBjYik7XG4gIH0sXG5cbiAgYW5pbWF0ZU91dDogZnVuY3Rpb24oZWxlbWVudCwgYW5pbWF0aW9uLCBjYikge1xuICAgIGFuaW1hdGUoZmFsc2UsIGVsZW1lbnQsIGFuaW1hdGlvbiwgY2IpO1xuICB9XG59XG5cbmZ1bmN0aW9uIE1vdmUoZHVyYXRpb24sIGVsZW0sIGZuKXtcbiAgdmFyIGFuaW0sIHByb2csIHN0YXJ0ID0gbnVsbDtcbiAgLy8gY29uc29sZS5sb2coJ2NhbGxlZCcpO1xuXG4gIGlmIChkdXJhdGlvbiA9PT0gMCkge1xuICAgIGZuLmFwcGx5KGVsZW0pO1xuICAgIGVsZW0udHJpZ2dlcignZmluaXNoZWQuemYuYW5pbWF0ZScsIFtlbGVtXSkudHJpZ2dlckhhbmRsZXIoJ2ZpbmlzaGVkLnpmLmFuaW1hdGUnLCBbZWxlbV0pO1xuICAgIHJldHVybjtcbiAgfVxuXG4gIGZ1bmN0aW9uIG1vdmUodHMpe1xuICAgIGlmKCFzdGFydCkgc3RhcnQgPSB0cztcbiAgICAvLyBjb25zb2xlLmxvZyhzdGFydCwgdHMpO1xuICAgIHByb2cgPSB0cyAtIHN0YXJ0O1xuICAgIGZuLmFwcGx5KGVsZW0pO1xuXG4gICAgaWYocHJvZyA8IGR1cmF0aW9uKXsgYW5pbSA9IHdpbmRvdy5yZXF1ZXN0QW5pbWF0aW9uRnJhbWUobW92ZSwgZWxlbSk7IH1cbiAgICBlbHNle1xuICAgICAgd2luZG93LmNhbmNlbEFuaW1hdGlvbkZyYW1lKGFuaW0pO1xuICAgICAgZWxlbS50cmlnZ2VyKCdmaW5pc2hlZC56Zi5hbmltYXRlJywgW2VsZW1dKS50cmlnZ2VySGFuZGxlcignZmluaXNoZWQuemYuYW5pbWF0ZScsIFtlbGVtXSk7XG4gICAgfVxuICB9XG4gIGFuaW0gPSB3aW5kb3cucmVxdWVzdEFuaW1hdGlvbkZyYW1lKG1vdmUpO1xufVxuXG4vKipcbiAqIEFuaW1hdGVzIGFuIGVsZW1lbnQgaW4gb3Igb3V0IHVzaW5nIGEgQ1NTIHRyYW5zaXRpb24gY2xhc3MuXG4gKiBAZnVuY3Rpb25cbiAqIEBwcml2YXRlXG4gKiBAcGFyYW0ge0Jvb2xlYW59IGlzSW4gLSBEZWZpbmVzIGlmIHRoZSBhbmltYXRpb24gaXMgaW4gb3Igb3V0LlxuICogQHBhcmFtIHtPYmplY3R9IGVsZW1lbnQgLSBqUXVlcnkgb3IgSFRNTCBvYmplY3QgdG8gYW5pbWF0ZS5cbiAqIEBwYXJhbSB7U3RyaW5nfSBhbmltYXRpb24gLSBDU1MgY2xhc3MgdG8gdXNlLlxuICogQHBhcmFtIHtGdW5jdGlvbn0gY2IgLSBDYWxsYmFjayB0byBydW4gd2hlbiBhbmltYXRpb24gaXMgZmluaXNoZWQuXG4gKi9cbmZ1bmN0aW9uIGFuaW1hdGUoaXNJbiwgZWxlbWVudCwgYW5pbWF0aW9uLCBjYikge1xuICBlbGVtZW50ID0gJChlbGVtZW50KS5lcSgwKTtcblxuICBpZiAoIWVsZW1lbnQubGVuZ3RoKSByZXR1cm47XG5cbiAgdmFyIGluaXRDbGFzcyA9IGlzSW4gPyBpbml0Q2xhc3Nlc1swXSA6IGluaXRDbGFzc2VzWzFdO1xuICB2YXIgYWN0aXZlQ2xhc3MgPSBpc0luID8gYWN0aXZlQ2xhc3Nlc1swXSA6IGFjdGl2ZUNsYXNzZXNbMV07XG5cbiAgLy8gU2V0IHVwIHRoZSBhbmltYXRpb25cbiAgcmVzZXQoKTtcblxuICBlbGVtZW50XG4gICAgLmFkZENsYXNzKGFuaW1hdGlvbilcbiAgICAuY3NzKCd0cmFuc2l0aW9uJywgJ25vbmUnKTtcblxuICByZXF1ZXN0QW5pbWF0aW9uRnJhbWUoKCkgPT4ge1xuICAgIGVsZW1lbnQuYWRkQ2xhc3MoaW5pdENsYXNzKTtcbiAgICBpZiAoaXNJbikgZWxlbWVudC5zaG93KCk7XG4gIH0pO1xuXG4gIC8vIFN0YXJ0IHRoZSBhbmltYXRpb25cbiAgcmVxdWVzdEFuaW1hdGlvbkZyYW1lKCgpID0+IHtcbiAgICBlbGVtZW50WzBdLm9mZnNldFdpZHRoO1xuICAgIGVsZW1lbnRcbiAgICAgIC5jc3MoJ3RyYW5zaXRpb24nLCAnJylcbiAgICAgIC5hZGRDbGFzcyhhY3RpdmVDbGFzcyk7XG4gIH0pO1xuXG4gIC8vIENsZWFuIHVwIHRoZSBhbmltYXRpb24gd2hlbiBpdCBmaW5pc2hlc1xuICBlbGVtZW50Lm9uZSh0cmFuc2l0aW9uZW5kKGVsZW1lbnQpLCBmaW5pc2gpO1xuXG4gIC8vIEhpZGVzIHRoZSBlbGVtZW50IChmb3Igb3V0IGFuaW1hdGlvbnMpLCByZXNldHMgdGhlIGVsZW1lbnQsIGFuZCBydW5zIGEgY2FsbGJhY2tcbiAgZnVuY3Rpb24gZmluaXNoKCkge1xuICAgIGlmICghaXNJbikgZWxlbWVudC5oaWRlKCk7XG4gICAgcmVzZXQoKTtcbiAgICBpZiAoY2IpIGNiLmFwcGx5KGVsZW1lbnQpO1xuICB9XG5cbiAgLy8gUmVzZXRzIHRyYW5zaXRpb25zIGFuZCByZW1vdmVzIG1vdGlvbi1zcGVjaWZpYyBjbGFzc2VzXG4gIGZ1bmN0aW9uIHJlc2V0KCkge1xuICAgIGVsZW1lbnRbMF0uc3R5bGUudHJhbnNpdGlvbkR1cmF0aW9uID0gMDtcbiAgICBlbGVtZW50LnJlbW92ZUNsYXNzKGAke2luaXRDbGFzc30gJHthY3RpdmVDbGFzc30gJHthbmltYXRpb259YCk7XG4gIH1cbn1cblxuZXhwb3J0IHsgTW92ZSwgTW90aW9uIH07XG5cblxuXG5cbi8vIFdFQlBBQ0sgRk9PVEVSIC8vXG4vLyAuL25vZGVfbW9kdWxlcy9mb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24udXRpbC5tb3Rpb24uanMiLCIndXNlIHN0cmljdCc7XG5cbmltcG9ydCAkIGZyb20gJ2pxdWVyeSc7XG5pbXBvcnQgeyBvbkxvYWQgfSBmcm9tICcuL2ZvdW5kYXRpb24uY29yZS51dGlscyc7XG5pbXBvcnQgeyBNb3Rpb24gfSBmcm9tICcuL2ZvdW5kYXRpb24udXRpbC5tb3Rpb24nO1xuXG5jb25zdCBNdXRhdGlvbk9ic2VydmVyID0gKGZ1bmN0aW9uICgpIHtcbiAgdmFyIHByZWZpeGVzID0gWydXZWJLaXQnLCAnTW96JywgJ08nLCAnTXMnLCAnJ107XG4gIGZvciAodmFyIGk9MDsgaSA8IHByZWZpeGVzLmxlbmd0aDsgaSsrKSB7XG4gICAgaWYgKGAke3ByZWZpeGVzW2ldfU11dGF0aW9uT2JzZXJ2ZXJgIGluIHdpbmRvdykge1xuICAgICAgcmV0dXJuIHdpbmRvd1tgJHtwcmVmaXhlc1tpXX1NdXRhdGlvbk9ic2VydmVyYF07XG4gICAgfVxuICB9XG4gIHJldHVybiBmYWxzZTtcbn0oKSk7XG5cbmNvbnN0IHRyaWdnZXJzID0gKGVsLCB0eXBlKSA9PiB7XG4gIGVsLmRhdGEodHlwZSkuc3BsaXQoJyAnKS5mb3JFYWNoKGlkID0+IHtcbiAgICAkKGAjJHtpZH1gKVsgdHlwZSA9PT0gJ2Nsb3NlJyA/ICd0cmlnZ2VyJyA6ICd0cmlnZ2VySGFuZGxlciddKGAke3R5cGV9LnpmLnRyaWdnZXJgLCBbZWxdKTtcbiAgfSk7XG59O1xuXG52YXIgVHJpZ2dlcnMgPSB7XG4gIExpc3RlbmVyczoge1xuICAgIEJhc2ljOiB7fSxcbiAgICBHbG9iYWw6IHt9XG4gIH0sXG4gIEluaXRpYWxpemVyczoge31cbn1cblxuVHJpZ2dlcnMuTGlzdGVuZXJzLkJhc2ljICA9IHtcbiAgb3Blbkxpc3RlbmVyOiBmdW5jdGlvbigpIHtcbiAgICB0cmlnZ2VycygkKHRoaXMpLCAnb3BlbicpO1xuICB9LFxuICBjbG9zZUxpc3RlbmVyOiBmdW5jdGlvbigpIHtcbiAgICBsZXQgaWQgPSAkKHRoaXMpLmRhdGEoJ2Nsb3NlJyk7XG4gICAgaWYgKGlkKSB7XG4gICAgICB0cmlnZ2VycygkKHRoaXMpLCAnY2xvc2UnKTtcbiAgICB9XG4gICAgZWxzZSB7XG4gICAgICAkKHRoaXMpLnRyaWdnZXIoJ2Nsb3NlLnpmLnRyaWdnZXInKTtcbiAgICB9XG4gIH0sXG4gIHRvZ2dsZUxpc3RlbmVyOiBmdW5jdGlvbigpIHtcbiAgICBsZXQgaWQgPSAkKHRoaXMpLmRhdGEoJ3RvZ2dsZScpO1xuICAgIGlmIChpZCkge1xuICAgICAgdHJpZ2dlcnMoJCh0aGlzKSwgJ3RvZ2dsZScpO1xuICAgIH0gZWxzZSB7XG4gICAgICAkKHRoaXMpLnRyaWdnZXIoJ3RvZ2dsZS56Zi50cmlnZ2VyJyk7XG4gICAgfVxuICB9LFxuICBjbG9zZWFibGVMaXN0ZW5lcjogZnVuY3Rpb24oZSkge1xuICAgIGUuc3RvcFByb3BhZ2F0aW9uKCk7XG4gICAgbGV0IGFuaW1hdGlvbiA9ICQodGhpcykuZGF0YSgnY2xvc2FibGUnKTtcblxuICAgIGlmKGFuaW1hdGlvbiAhPT0gJycpe1xuICAgICAgTW90aW9uLmFuaW1hdGVPdXQoJCh0aGlzKSwgYW5pbWF0aW9uLCBmdW5jdGlvbigpIHtcbiAgICAgICAgJCh0aGlzKS50cmlnZ2VyKCdjbG9zZWQuemYnKTtcbiAgICAgIH0pO1xuICAgIH1lbHNle1xuICAgICAgJCh0aGlzKS5mYWRlT3V0KCkudHJpZ2dlcignY2xvc2VkLnpmJyk7XG4gICAgfVxuICB9LFxuICB0b2dnbGVGb2N1c0xpc3RlbmVyOiBmdW5jdGlvbigpIHtcbiAgICBsZXQgaWQgPSAkKHRoaXMpLmRhdGEoJ3RvZ2dsZS1mb2N1cycpO1xuICAgICQoYCMke2lkfWApLnRyaWdnZXJIYW5kbGVyKCd0b2dnbGUuemYudHJpZ2dlcicsIFskKHRoaXMpXSk7XG4gIH1cbn07XG5cbi8vIEVsZW1lbnRzIHdpdGggW2RhdGEtb3Blbl0gd2lsbCByZXZlYWwgYSBwbHVnaW4gdGhhdCBzdXBwb3J0cyBpdCB3aGVuIGNsaWNrZWQuXG5UcmlnZ2Vycy5Jbml0aWFsaXplcnMuYWRkT3Blbkxpc3RlbmVyID0gKCRlbGVtKSA9PiB7XG4gICRlbGVtLm9mZignY2xpY2suemYudHJpZ2dlcicsIFRyaWdnZXJzLkxpc3RlbmVycy5CYXNpYy5vcGVuTGlzdGVuZXIpO1xuICAkZWxlbS5vbignY2xpY2suemYudHJpZ2dlcicsICdbZGF0YS1vcGVuXScsIFRyaWdnZXJzLkxpc3RlbmVycy5CYXNpYy5vcGVuTGlzdGVuZXIpO1xufVxuXG4vLyBFbGVtZW50cyB3aXRoIFtkYXRhLWNsb3NlXSB3aWxsIGNsb3NlIGEgcGx1Z2luIHRoYXQgc3VwcG9ydHMgaXQgd2hlbiBjbGlja2VkLlxuLy8gSWYgdXNlZCB3aXRob3V0IGEgdmFsdWUgb24gW2RhdGEtY2xvc2VdLCB0aGUgZXZlbnQgd2lsbCBidWJibGUsIGFsbG93aW5nIGl0IHRvIGNsb3NlIGEgcGFyZW50IGNvbXBvbmVudC5cblRyaWdnZXJzLkluaXRpYWxpemVycy5hZGRDbG9zZUxpc3RlbmVyID0gKCRlbGVtKSA9PiB7XG4gICRlbGVtLm9mZignY2xpY2suemYudHJpZ2dlcicsIFRyaWdnZXJzLkxpc3RlbmVycy5CYXNpYy5jbG9zZUxpc3RlbmVyKTtcbiAgJGVsZW0ub24oJ2NsaWNrLnpmLnRyaWdnZXInLCAnW2RhdGEtY2xvc2VdJywgVHJpZ2dlcnMuTGlzdGVuZXJzLkJhc2ljLmNsb3NlTGlzdGVuZXIpO1xufVxuXG4vLyBFbGVtZW50cyB3aXRoIFtkYXRhLXRvZ2dsZV0gd2lsbCB0b2dnbGUgYSBwbHVnaW4gdGhhdCBzdXBwb3J0cyBpdCB3aGVuIGNsaWNrZWQuXG5UcmlnZ2Vycy5Jbml0aWFsaXplcnMuYWRkVG9nZ2xlTGlzdGVuZXIgPSAoJGVsZW0pID0+IHtcbiAgJGVsZW0ub2ZmKCdjbGljay56Zi50cmlnZ2VyJywgVHJpZ2dlcnMuTGlzdGVuZXJzLkJhc2ljLnRvZ2dsZUxpc3RlbmVyKTtcbiAgJGVsZW0ub24oJ2NsaWNrLnpmLnRyaWdnZXInLCAnW2RhdGEtdG9nZ2xlXScsIFRyaWdnZXJzLkxpc3RlbmVycy5CYXNpYy50b2dnbGVMaXN0ZW5lcik7XG59XG5cbi8vIEVsZW1lbnRzIHdpdGggW2RhdGEtY2xvc2FibGVdIHdpbGwgcmVzcG9uZCB0byBjbG9zZS56Zi50cmlnZ2VyIGV2ZW50cy5cblRyaWdnZXJzLkluaXRpYWxpemVycy5hZGRDbG9zZWFibGVMaXN0ZW5lciA9ICgkZWxlbSkgPT4ge1xuICAkZWxlbS5vZmYoJ2Nsb3NlLnpmLnRyaWdnZXInLCBUcmlnZ2Vycy5MaXN0ZW5lcnMuQmFzaWMuY2xvc2VhYmxlTGlzdGVuZXIpO1xuICAkZWxlbS5vbignY2xvc2UuemYudHJpZ2dlcicsICdbZGF0YS1jbG9zZWFibGVdLCBbZGF0YS1jbG9zYWJsZV0nLCBUcmlnZ2Vycy5MaXN0ZW5lcnMuQmFzaWMuY2xvc2VhYmxlTGlzdGVuZXIpO1xufVxuXG4vLyBFbGVtZW50cyB3aXRoIFtkYXRhLXRvZ2dsZS1mb2N1c10gd2lsbCByZXNwb25kIHRvIGNvbWluZyBpbiBhbmQgb3V0IG9mIGZvY3VzXG5UcmlnZ2Vycy5Jbml0aWFsaXplcnMuYWRkVG9nZ2xlRm9jdXNMaXN0ZW5lciA9ICgkZWxlbSkgPT4ge1xuICAkZWxlbS5vZmYoJ2ZvY3VzLnpmLnRyaWdnZXIgYmx1ci56Zi50cmlnZ2VyJywgVHJpZ2dlcnMuTGlzdGVuZXJzLkJhc2ljLnRvZ2dsZUZvY3VzTGlzdGVuZXIpO1xuICAkZWxlbS5vbignZm9jdXMuemYudHJpZ2dlciBibHVyLnpmLnRyaWdnZXInLCAnW2RhdGEtdG9nZ2xlLWZvY3VzXScsIFRyaWdnZXJzLkxpc3RlbmVycy5CYXNpYy50b2dnbGVGb2N1c0xpc3RlbmVyKTtcbn1cblxuXG5cbi8vIE1vcmUgR2xvYmFsL2NvbXBsZXggbGlzdGVuZXJzIGFuZCB0cmlnZ2Vyc1xuVHJpZ2dlcnMuTGlzdGVuZXJzLkdsb2JhbCAgPSB7XG4gIHJlc2l6ZUxpc3RlbmVyOiBmdW5jdGlvbigkbm9kZXMpIHtcbiAgICBpZighTXV0YXRpb25PYnNlcnZlcil7Ly9mYWxsYmFjayBmb3IgSUUgOVxuICAgICAgJG5vZGVzLmVhY2goZnVuY3Rpb24oKXtcbiAgICAgICAgJCh0aGlzKS50cmlnZ2VySGFuZGxlcigncmVzaXplbWUuemYudHJpZ2dlcicpO1xuICAgICAgfSk7XG4gICAgfVxuICAgIC8vdHJpZ2dlciBhbGwgbGlzdGVuaW5nIGVsZW1lbnRzIGFuZCBzaWduYWwgYSByZXNpemUgZXZlbnRcbiAgICAkbm9kZXMuYXR0cignZGF0YS1ldmVudHMnLCBcInJlc2l6ZVwiKTtcbiAgfSxcbiAgc2Nyb2xsTGlzdGVuZXI6IGZ1bmN0aW9uKCRub2Rlcykge1xuICAgIGlmKCFNdXRhdGlvbk9ic2VydmVyKXsvL2ZhbGxiYWNrIGZvciBJRSA5XG4gICAgICAkbm9kZXMuZWFjaChmdW5jdGlvbigpe1xuICAgICAgICAkKHRoaXMpLnRyaWdnZXJIYW5kbGVyKCdzY3JvbGxtZS56Zi50cmlnZ2VyJyk7XG4gICAgICB9KTtcbiAgICB9XG4gICAgLy90cmlnZ2VyIGFsbCBsaXN0ZW5pbmcgZWxlbWVudHMgYW5kIHNpZ25hbCBhIHNjcm9sbCBldmVudFxuICAgICRub2Rlcy5hdHRyKCdkYXRhLWV2ZW50cycsIFwic2Nyb2xsXCIpO1xuICB9LFxuICBjbG9zZU1lTGlzdGVuZXI6IGZ1bmN0aW9uKGUsIHBsdWdpbklkKXtcbiAgICBsZXQgcGx1Z2luID0gZS5uYW1lc3BhY2Uuc3BsaXQoJy4nKVswXTtcbiAgICBsZXQgcGx1Z2lucyA9ICQoYFtkYXRhLSR7cGx1Z2lufV1gKS5ub3QoYFtkYXRhLXlldGktYm94PVwiJHtwbHVnaW5JZH1cIl1gKTtcblxuICAgIHBsdWdpbnMuZWFjaChmdW5jdGlvbigpe1xuICAgICAgbGV0IF90aGlzID0gJCh0aGlzKTtcbiAgICAgIF90aGlzLnRyaWdnZXJIYW5kbGVyKCdjbG9zZS56Zi50cmlnZ2VyJywgW190aGlzXSk7XG4gICAgfSk7XG4gIH1cbn1cblxuLy8gR2xvYmFsLCBwYXJzZXMgd2hvbGUgZG9jdW1lbnQuXG5UcmlnZ2Vycy5Jbml0aWFsaXplcnMuYWRkQ2xvc2VtZUxpc3RlbmVyID0gZnVuY3Rpb24ocGx1Z2luTmFtZSkge1xuICB2YXIgeWV0aUJveGVzID0gJCgnW2RhdGEteWV0aS1ib3hdJyksXG4gICAgICBwbHVnTmFtZXMgPSBbJ2Ryb3Bkb3duJywgJ3Rvb2x0aXAnLCAncmV2ZWFsJ107XG5cbiAgaWYocGx1Z2luTmFtZSl7XG4gICAgaWYodHlwZW9mIHBsdWdpbk5hbWUgPT09ICdzdHJpbmcnKXtcbiAgICAgIHBsdWdOYW1lcy5wdXNoKHBsdWdpbk5hbWUpO1xuICAgIH1lbHNlIGlmKHR5cGVvZiBwbHVnaW5OYW1lID09PSAnb2JqZWN0JyAmJiB0eXBlb2YgcGx1Z2luTmFtZVswXSA9PT0gJ3N0cmluZycpe1xuICAgICAgcGx1Z05hbWVzLmNvbmNhdChwbHVnaW5OYW1lKTtcbiAgICB9ZWxzZXtcbiAgICAgIGNvbnNvbGUuZXJyb3IoJ1BsdWdpbiBuYW1lcyBtdXN0IGJlIHN0cmluZ3MnKTtcbiAgICB9XG4gIH1cbiAgaWYoeWV0aUJveGVzLmxlbmd0aCl7XG4gICAgbGV0IGxpc3RlbmVycyA9IHBsdWdOYW1lcy5tYXAoKG5hbWUpID0+IHtcbiAgICAgIHJldHVybiBgY2xvc2VtZS56Zi4ke25hbWV9YDtcbiAgICB9KS5qb2luKCcgJyk7XG5cbiAgICAkKHdpbmRvdykub2ZmKGxpc3RlbmVycykub24obGlzdGVuZXJzLCBUcmlnZ2Vycy5MaXN0ZW5lcnMuR2xvYmFsLmNsb3NlTWVMaXN0ZW5lcik7XG4gIH1cbn1cblxuZnVuY3Rpb24gZGVib3VuY2VHbG9iYWxMaXN0ZW5lcihkZWJvdW5jZSwgdHJpZ2dlciwgbGlzdGVuZXIpIHtcbiAgbGV0IHRpbWVyLCBhcmdzID0gQXJyYXkucHJvdG90eXBlLnNsaWNlLmNhbGwoYXJndW1lbnRzLCAzKTtcbiAgJCh3aW5kb3cpLm9mZih0cmlnZ2VyKS5vbih0cmlnZ2VyLCBmdW5jdGlvbihlKSB7XG4gICAgaWYgKHRpbWVyKSB7IGNsZWFyVGltZW91dCh0aW1lcik7IH1cbiAgICB0aW1lciA9IHNldFRpbWVvdXQoZnVuY3Rpb24oKXtcbiAgICAgIGxpc3RlbmVyLmFwcGx5KG51bGwsIGFyZ3MpO1xuICAgIH0sIGRlYm91bmNlIHx8IDEwKTsvL2RlZmF1bHQgdGltZSB0byBlbWl0IHNjcm9sbCBldmVudFxuICB9KTtcbn1cblxuVHJpZ2dlcnMuSW5pdGlhbGl6ZXJzLmFkZFJlc2l6ZUxpc3RlbmVyID0gZnVuY3Rpb24oZGVib3VuY2Upe1xuICBsZXQgJG5vZGVzID0gJCgnW2RhdGEtcmVzaXplXScpO1xuICBpZigkbm9kZXMubGVuZ3RoKXtcbiAgICBkZWJvdW5jZUdsb2JhbExpc3RlbmVyKGRlYm91bmNlLCAncmVzaXplLnpmLnRyaWdnZXInLCBUcmlnZ2Vycy5MaXN0ZW5lcnMuR2xvYmFsLnJlc2l6ZUxpc3RlbmVyLCAkbm9kZXMpO1xuICB9XG59XG5cblRyaWdnZXJzLkluaXRpYWxpemVycy5hZGRTY3JvbGxMaXN0ZW5lciA9IGZ1bmN0aW9uKGRlYm91bmNlKXtcbiAgbGV0ICRub2RlcyA9ICQoJ1tkYXRhLXNjcm9sbF0nKTtcbiAgaWYoJG5vZGVzLmxlbmd0aCl7XG4gICAgZGVib3VuY2VHbG9iYWxMaXN0ZW5lcihkZWJvdW5jZSwgJ3Njcm9sbC56Zi50cmlnZ2VyJywgVHJpZ2dlcnMuTGlzdGVuZXJzLkdsb2JhbC5zY3JvbGxMaXN0ZW5lciwgJG5vZGVzKTtcbiAgfVxufVxuXG5UcmlnZ2Vycy5Jbml0aWFsaXplcnMuYWRkTXV0YXRpb25FdmVudHNMaXN0ZW5lciA9IGZ1bmN0aW9uKCRlbGVtKSB7XG4gIGlmKCFNdXRhdGlvbk9ic2VydmVyKXsgcmV0dXJuIGZhbHNlOyB9XG4gIGxldCAkbm9kZXMgPSAkZWxlbS5maW5kKCdbZGF0YS1yZXNpemVdLCBbZGF0YS1zY3JvbGxdLCBbZGF0YS1tdXRhdGVdJyk7XG5cbiAgLy9lbGVtZW50IGNhbGxiYWNrXG4gIHZhciBsaXN0ZW5pbmdFbGVtZW50c011dGF0aW9uID0gZnVuY3Rpb24gKG11dGF0aW9uUmVjb3Jkc0xpc3QpIHtcbiAgICB2YXIgJHRhcmdldCA9ICQobXV0YXRpb25SZWNvcmRzTGlzdFswXS50YXJnZXQpO1xuXG4gICAgLy90cmlnZ2VyIHRoZSBldmVudCBoYW5kbGVyIGZvciB0aGUgZWxlbWVudCBkZXBlbmRpbmcgb24gdHlwZVxuICAgIHN3aXRjaCAobXV0YXRpb25SZWNvcmRzTGlzdFswXS50eXBlKSB7XG4gICAgICBjYXNlIFwiYXR0cmlidXRlc1wiOlxuICAgICAgICBpZiAoJHRhcmdldC5hdHRyKFwiZGF0YS1ldmVudHNcIikgPT09IFwic2Nyb2xsXCIgJiYgbXV0YXRpb25SZWNvcmRzTGlzdFswXS5hdHRyaWJ1dGVOYW1lID09PSBcImRhdGEtZXZlbnRzXCIpIHtcbiAgICAgICAgICAkdGFyZ2V0LnRyaWdnZXJIYW5kbGVyKCdzY3JvbGxtZS56Zi50cmlnZ2VyJywgWyR0YXJnZXQsIHdpbmRvdy5wYWdlWU9mZnNldF0pO1xuICAgICAgICB9XG4gICAgICAgIGlmICgkdGFyZ2V0LmF0dHIoXCJkYXRhLWV2ZW50c1wiKSA9PT0gXCJyZXNpemVcIiAmJiBtdXRhdGlvblJlY29yZHNMaXN0WzBdLmF0dHJpYnV0ZU5hbWUgPT09IFwiZGF0YS1ldmVudHNcIikge1xuICAgICAgICAgICR0YXJnZXQudHJpZ2dlckhhbmRsZXIoJ3Jlc2l6ZW1lLnpmLnRyaWdnZXInLCBbJHRhcmdldF0pO1xuICAgICAgICAgfVxuICAgICAgICBpZiAobXV0YXRpb25SZWNvcmRzTGlzdFswXS5hdHRyaWJ1dGVOYW1lID09PSBcInN0eWxlXCIpIHtcbiAgICAgICAgICAkdGFyZ2V0LmNsb3Nlc3QoXCJbZGF0YS1tdXRhdGVdXCIpLmF0dHIoXCJkYXRhLWV2ZW50c1wiLFwibXV0YXRlXCIpO1xuICAgICAgICAgICR0YXJnZXQuY2xvc2VzdChcIltkYXRhLW11dGF0ZV1cIikudHJpZ2dlckhhbmRsZXIoJ211dGF0ZW1lLnpmLnRyaWdnZXInLCBbJHRhcmdldC5jbG9zZXN0KFwiW2RhdGEtbXV0YXRlXVwiKV0pO1xuICAgICAgICB9XG4gICAgICAgIGJyZWFrO1xuXG4gICAgICBjYXNlIFwiY2hpbGRMaXN0XCI6XG4gICAgICAgICR0YXJnZXQuY2xvc2VzdChcIltkYXRhLW11dGF0ZV1cIikuYXR0cihcImRhdGEtZXZlbnRzXCIsXCJtdXRhdGVcIik7XG4gICAgICAgICR0YXJnZXQuY2xvc2VzdChcIltkYXRhLW11dGF0ZV1cIikudHJpZ2dlckhhbmRsZXIoJ211dGF0ZW1lLnpmLnRyaWdnZXInLCBbJHRhcmdldC5jbG9zZXN0KFwiW2RhdGEtbXV0YXRlXVwiKV0pO1xuICAgICAgICBicmVhaztcblxuICAgICAgZGVmYXVsdDpcbiAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgLy9ub3RoaW5nXG4gICAgfVxuICB9O1xuXG4gIGlmICgkbm9kZXMubGVuZ3RoKSB7XG4gICAgLy9mb3IgZWFjaCBlbGVtZW50IHRoYXQgbmVlZHMgdG8gbGlzdGVuIGZvciByZXNpemluZywgc2Nyb2xsaW5nLCBvciBtdXRhdGlvbiBhZGQgYSBzaW5nbGUgb2JzZXJ2ZXJcbiAgICBmb3IgKHZhciBpID0gMDsgaSA8PSAkbm9kZXMubGVuZ3RoIC0gMTsgaSsrKSB7XG4gICAgICB2YXIgZWxlbWVudE9ic2VydmVyID0gbmV3IE11dGF0aW9uT2JzZXJ2ZXIobGlzdGVuaW5nRWxlbWVudHNNdXRhdGlvbik7XG4gICAgICBlbGVtZW50T2JzZXJ2ZXIub2JzZXJ2ZSgkbm9kZXNbaV0sIHsgYXR0cmlidXRlczogdHJ1ZSwgY2hpbGRMaXN0OiB0cnVlLCBjaGFyYWN0ZXJEYXRhOiBmYWxzZSwgc3VidHJlZTogdHJ1ZSwgYXR0cmlidXRlRmlsdGVyOiBbXCJkYXRhLWV2ZW50c1wiLCBcInN0eWxlXCJdIH0pO1xuICAgIH1cbiAgfVxufVxuXG5UcmlnZ2Vycy5Jbml0aWFsaXplcnMuYWRkU2ltcGxlTGlzdGVuZXJzID0gZnVuY3Rpb24oKSB7XG4gIGxldCAkZG9jdW1lbnQgPSAkKGRvY3VtZW50KTtcblxuICBUcmlnZ2Vycy5Jbml0aWFsaXplcnMuYWRkT3Blbkxpc3RlbmVyKCRkb2N1bWVudCk7XG4gIFRyaWdnZXJzLkluaXRpYWxpemVycy5hZGRDbG9zZUxpc3RlbmVyKCRkb2N1bWVudCk7XG4gIFRyaWdnZXJzLkluaXRpYWxpemVycy5hZGRUb2dnbGVMaXN0ZW5lcigkZG9jdW1lbnQpO1xuICBUcmlnZ2Vycy5Jbml0aWFsaXplcnMuYWRkQ2xvc2VhYmxlTGlzdGVuZXIoJGRvY3VtZW50KTtcbiAgVHJpZ2dlcnMuSW5pdGlhbGl6ZXJzLmFkZFRvZ2dsZUZvY3VzTGlzdGVuZXIoJGRvY3VtZW50KTtcblxufVxuXG5UcmlnZ2Vycy5Jbml0aWFsaXplcnMuYWRkR2xvYmFsTGlzdGVuZXJzID0gZnVuY3Rpb24oKSB7XG4gIGxldCAkZG9jdW1lbnQgPSAkKGRvY3VtZW50KTtcbiAgVHJpZ2dlcnMuSW5pdGlhbGl6ZXJzLmFkZE11dGF0aW9uRXZlbnRzTGlzdGVuZXIoJGRvY3VtZW50KTtcbiAgVHJpZ2dlcnMuSW5pdGlhbGl6ZXJzLmFkZFJlc2l6ZUxpc3RlbmVyKCk7XG4gIFRyaWdnZXJzLkluaXRpYWxpemVycy5hZGRTY3JvbGxMaXN0ZW5lcigpO1xuICBUcmlnZ2Vycy5Jbml0aWFsaXplcnMuYWRkQ2xvc2VtZUxpc3RlbmVyKCk7XG59XG5cblxuVHJpZ2dlcnMuaW5pdCA9IGZ1bmN0aW9uICgkLCBGb3VuZGF0aW9uKSB7XG4gIG9uTG9hZCgkKHdpbmRvdyksIGZ1bmN0aW9uICgpIHtcbiAgICBpZiAoJC50cmlnZ2Vyc0luaXRpYWxpemVkICE9PSB0cnVlKSB7XG4gICAgICBUcmlnZ2Vycy5Jbml0aWFsaXplcnMuYWRkU2ltcGxlTGlzdGVuZXJzKCk7XG4gICAgICBUcmlnZ2Vycy5Jbml0aWFsaXplcnMuYWRkR2xvYmFsTGlzdGVuZXJzKCk7XG4gICAgICAkLnRyaWdnZXJzSW5pdGlhbGl6ZWQgPSB0cnVlO1xuICAgIH1cbiAgfSk7XG5cbiAgaWYoRm91bmRhdGlvbikge1xuICAgIEZvdW5kYXRpb24uVHJpZ2dlcnMgPSBUcmlnZ2VycztcbiAgICAvLyBMZWdhY3kgaW5jbHVkZWQgdG8gYmUgYmFja3dhcmRzIGNvbXBhdGlibGUgZm9yIG5vdy5cbiAgICBGb3VuZGF0aW9uLklIZWFyWW91ID0gVHJpZ2dlcnMuSW5pdGlhbGl6ZXJzLmFkZEdsb2JhbExpc3RlbmVyc1xuICB9XG59XG5cbmV4cG9ydCB7VHJpZ2dlcnN9O1xuXG5cblxuLy8gV0VCUEFDSyBGT09URVIgLy9cbi8vIC4vbm9kZV9tb2R1bGVzL2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi51dGlsLnRyaWdnZXJzLmpzIiwiJ3VzZSBzdHJpY3QnO1xuXG5pbXBvcnQgJCBmcm9tICdqcXVlcnknO1xuaW1wb3J0IHsgR2V0WW9EaWdpdHMgfSBmcm9tICcuL2ZvdW5kYXRpb24uY29yZS51dGlscyc7XG5cbi8vIEFic3RyYWN0IGNsYXNzIGZvciBwcm92aWRpbmcgbGlmZWN5Y2xlIGhvb2tzLiBFeHBlY3QgcGx1Z2lucyB0byBkZWZpbmUgQVQgTEVBU1Rcbi8vIHtmdW5jdGlvbn0gX3NldHVwIChyZXBsYWNlcyBwcmV2aW91cyBjb25zdHJ1Y3RvciksXG4vLyB7ZnVuY3Rpb259IF9kZXN0cm95IChyZXBsYWNlcyBwcmV2aW91cyBkZXN0cm95KVxuY2xhc3MgUGx1Z2luIHtcblxuICBjb25zdHJ1Y3RvcihlbGVtZW50LCBvcHRpb25zKSB7XG4gICAgdGhpcy5fc2V0dXAoZWxlbWVudCwgb3B0aW9ucyk7XG4gICAgdmFyIHBsdWdpbk5hbWUgPSBnZXRQbHVnaW5OYW1lKHRoaXMpO1xuICAgIHRoaXMudXVpZCA9IEdldFlvRGlnaXRzKDYsIHBsdWdpbk5hbWUpO1xuXG4gICAgaWYoIXRoaXMuJGVsZW1lbnQuYXR0cihgZGF0YS0ke3BsdWdpbk5hbWV9YCkpeyB0aGlzLiRlbGVtZW50LmF0dHIoYGRhdGEtJHtwbHVnaW5OYW1lfWAsIHRoaXMudXVpZCk7IH1cbiAgICBpZighdGhpcy4kZWxlbWVudC5kYXRhKCd6ZlBsdWdpbicpKXsgdGhpcy4kZWxlbWVudC5kYXRhKCd6ZlBsdWdpbicsIHRoaXMpOyB9XG4gICAgLyoqXG4gICAgICogRmlyZXMgd2hlbiB0aGUgcGx1Z2luIGhhcyBpbml0aWFsaXplZC5cbiAgICAgKiBAZXZlbnQgUGx1Z2luI2luaXRcbiAgICAgKi9cbiAgICB0aGlzLiRlbGVtZW50LnRyaWdnZXIoYGluaXQuemYuJHtwbHVnaW5OYW1lfWApO1xuICB9XG5cbiAgZGVzdHJveSgpIHtcbiAgICB0aGlzLl9kZXN0cm95KCk7XG4gICAgdmFyIHBsdWdpbk5hbWUgPSBnZXRQbHVnaW5OYW1lKHRoaXMpO1xuICAgIHRoaXMuJGVsZW1lbnQucmVtb3ZlQXR0cihgZGF0YS0ke3BsdWdpbk5hbWV9YCkucmVtb3ZlRGF0YSgnemZQbHVnaW4nKVxuICAgICAgICAvKipcbiAgICAgICAgICogRmlyZXMgd2hlbiB0aGUgcGx1Z2luIGhhcyBiZWVuIGRlc3Ryb3llZC5cbiAgICAgICAgICogQGV2ZW50IFBsdWdpbiNkZXN0cm95ZWRcbiAgICAgICAgICovXG4gICAgICAgIC50cmlnZ2VyKGBkZXN0cm95ZWQuemYuJHtwbHVnaW5OYW1lfWApO1xuICAgIGZvcih2YXIgcHJvcCBpbiB0aGlzKXtcbiAgICAgIHRoaXNbcHJvcF0gPSBudWxsOy8vY2xlYW4gdXAgc2NyaXB0IHRvIHByZXAgZm9yIGdhcmJhZ2UgY29sbGVjdGlvbi5cbiAgICB9XG4gIH1cbn1cblxuLy8gQ29udmVydCBQYXNjYWxDYXNlIHRvIGtlYmFiLWNhc2Vcbi8vIFRoYW5rIHlvdTogaHR0cDovL3N0YWNrb3ZlcmZsb3cuY29tL2EvODk1NTU4MFxuZnVuY3Rpb24gaHlwaGVuYXRlKHN0cikge1xuICByZXR1cm4gc3RyLnJlcGxhY2UoLyhbYS16XSkoW0EtWl0pL2csICckMS0kMicpLnRvTG93ZXJDYXNlKCk7XG59XG5cbmZ1bmN0aW9uIGdldFBsdWdpbk5hbWUob2JqKSB7XG4gIGlmKHR5cGVvZihvYmouY29uc3RydWN0b3IubmFtZSkgIT09ICd1bmRlZmluZWQnKSB7XG4gICAgcmV0dXJuIGh5cGhlbmF0ZShvYmouY29uc3RydWN0b3IubmFtZSk7XG4gIH0gZWxzZSB7XG4gICAgcmV0dXJuIGh5cGhlbmF0ZShvYmouY2xhc3NOYW1lKTtcbiAgfVxufVxuXG5leHBvcnQge1BsdWdpbn07XG5cblxuXG4vLyBXRUJQQUNLIEZPT1RFUiAvL1xuLy8gLi9ub2RlX21vZHVsZXMvZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLmNvcmUucGx1Z2luLmpzIiwiLy8gaW1wb3J0ICQgZnJvbSAnanF1ZXJ5Jztcbi8vIGltcG9ydCB3aGF0SW5wdXQgZnJvbSAnd2hhdC1pbnB1dCc7XG4vL1xuLy8gd2luZG93LiQgPSAkO1xuLy9cbi8vIGltcG9ydCBGb3VuZGF0aW9uIGZyb20gJ2ZvdW5kYXRpb24tc2l0ZXMnO1xuLy8gSWYgeW91IHdhbnQgdG8gcGljayBhbmQgY2hvb3NlIHdoaWNoIG1vZHVsZXMgdG8gaW5jbHVkZSwgY29tbWVudCBvdXQgdGhlIGFib3ZlIGFuZCB1bmNvbW1lbnRcbi8vIHRoZSBsaW5lIGJlbG93XG5pbXBvcnQgJy4vbGliL2ZvdW5kYXRpb24tZXhwbGljaXQtcGllY2VzJztcbiQoIGRvY3VtZW50ICkuZm91bmRhdGlvbigpO1xuXG5cblxuLy8gV0VCUEFDSyBGT09URVIgLy9cbi8vIC4vc3JjL2Fzc2V0cy9qcy9hcHAuanMiLCJpbXBvcnQgJCBmcm9tICdqcXVlcnknO1xuaW1wb3J0IHsgRm91bmRhdGlvbiB9IGZyb20gJ2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi5jb3JlJztcbmltcG9ydCB7IHJ0bCwgR2V0WW9EaWdpdHMsIHRyYW5zaXRpb25lbmQgfSBmcm9tICdmb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24uY29yZS51dGlscyc7XG5pbXBvcnQgeyBCb3ggfSBmcm9tICdmb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24udXRpbC5ib3gnXG5pbXBvcnQgeyBvbkltYWdlc0xvYWRlZCB9IGZyb20gJ2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi51dGlsLmltYWdlTG9hZGVyJztcbmltcG9ydCB7IEtleWJvYXJkIH0gZnJvbSAnZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLnV0aWwua2V5Ym9hcmQnO1xuaW1wb3J0IHsgTWVkaWFRdWVyeSB9IGZyb20gJ2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi51dGlsLm1lZGlhUXVlcnknO1xuaW1wb3J0IHsgTW90aW9uLCBNb3ZlIH0gZnJvbSAnZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLnV0aWwubW90aW9uJztcbmltcG9ydCB7IE5lc3QgfSBmcm9tICdmb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24udXRpbC5uZXN0JztcbmltcG9ydCB7IFRpbWVyIH0gZnJvbSAnZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLnV0aWwudGltZXInO1xuaW1wb3J0IHsgVG91Y2ggfSBmcm9tICdmb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24udXRpbC50b3VjaCc7XG5pbXBvcnQgeyBUcmlnZ2VycyB9IGZyb20gJ2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi51dGlsLnRyaWdnZXJzJztcbi8vIGltcG9ydCB7IEFiaWRlIH0gZnJvbSAnZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLmFiaWRlJztcbi8vIGltcG9ydCB7IEFjY29yZGlvbiB9IGZyb20gJ2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi5hY2NvcmRpb24nO1xuLy8gaW1wb3J0IHsgQWNjb3JkaW9uTWVudSB9IGZyb20gJ2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi5hY2NvcmRpb25NZW51Jztcbi8vIGltcG9ydCB7IERyaWxsZG93biB9IGZyb20gJ2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi5kcmlsbGRvd24nO1xuLy8gaW1wb3J0IHsgRHJvcGRvd24gfSBmcm9tICdmb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24uZHJvcGRvd24nO1xuLy8gaW1wb3J0IHsgRHJvcGRvd25NZW51IH0gZnJvbSAnZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLmRyb3Bkb3duTWVudSc7XG4vLyBpbXBvcnQgeyBFcXVhbGl6ZXIgfSBmcm9tICdmb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24uZXF1YWxpemVyJztcbi8vIGltcG9ydCB7IEludGVyY2hhbmdlIH0gZnJvbSAnZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLmludGVyY2hhbmdlJztcbi8vIGltcG9ydCB7IE1hZ2VsbGFuIH0gZnJvbSAnZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLm1hZ2VsbGFuJztcbi8vIGltcG9ydCB7IE9mZkNhbnZhcyB9IGZyb20gJ2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi5vZmZjYW52YXMnO1xuLy8gaW1wb3J0IHsgT3JiaXQgfSBmcm9tICdmb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24ub3JiaXQnO1xuLy8gaW1wb3J0IHsgUmVzcG9uc2l2ZU1lbnUgfSBmcm9tICdmb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24ucmVzcG9uc2l2ZU1lbnUnO1xuLy8gaW1wb3J0IHsgUmVzcG9uc2l2ZVRvZ2dsZSB9IGZyb20gJ2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi5yZXNwb25zaXZlVG9nZ2xlJztcbi8vIGltcG9ydCB7IFJldmVhbCB9IGZyb20gJ2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi5yZXZlYWwnO1xuLy8gaW1wb3J0IHsgU2xpZGVyIH0gZnJvbSAnZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLnNsaWRlcic7XG4vLyBpbXBvcnQgeyBTbW9vdGhTY3JvbGwgfSBmcm9tICdmb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24uc21vb3RoU2Nyb2xsJztcbmltcG9ydCB7IFN0aWNreSB9IGZyb20gJ2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi5zdGlja3knO1xuLy8gaW1wb3J0IHsgVGFicyB9IGZyb20gJ2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi50YWJzJztcbmltcG9ydCB7IFRvZ2dsZXIgfSBmcm9tICdmb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24udG9nZ2xlcic7XG4vLyBpbXBvcnQgeyBUb29sdGlwIH0gZnJvbSAnZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLnRvb2x0aXAnO1xuLy8gaW1wb3J0IHsgUmVzcG9uc2l2ZUFjY29yZGlvblRhYnMgfSBmcm9tICdmb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24ucmVzcG9uc2l2ZUFjY29yZGlvblRhYnMnO1xuXG5cbkZvdW5kYXRpb24uYWRkVG9KcXVlcnkoICQgKTtcblxuLy8gQWRkIEZvdW5kYXRpb24gVXRpbHMgdG8gRm91bmRhdGlvbiBnbG9iYWwgbmFtZXNwYWNlIGZvciBiYWNrd2FyZHNcbi8vIGNvbXBhdGliaWxpdHkuXG5Gb3VuZGF0aW9uLnJ0bCAgICAgICAgICAgPSBydGw7XG5Gb3VuZGF0aW9uLkdldFlvRGlnaXRzICAgPSBHZXRZb0RpZ2l0cztcbkZvdW5kYXRpb24udHJhbnNpdGlvbmVuZCA9IHRyYW5zaXRpb25lbmQ7XG5cbkZvdW5kYXRpb24uQm94ICAgICAgICAgICAgPSBCb3g7XG5Gb3VuZGF0aW9uLm9uSW1hZ2VzTG9hZGVkID0gb25JbWFnZXNMb2FkZWQ7XG5Gb3VuZGF0aW9uLktleWJvYXJkICAgICAgID0gS2V5Ym9hcmQ7XG5Gb3VuZGF0aW9uLk1lZGlhUXVlcnkgICAgID0gTWVkaWFRdWVyeTtcbkZvdW5kYXRpb24uTW90aW9uICAgICAgICAgPSBNb3Rpb247XG5Gb3VuZGF0aW9uLk1vdmUgICAgICAgICAgID0gTW92ZTtcbkZvdW5kYXRpb24uTmVzdCAgICAgICAgICAgPSBOZXN0O1xuRm91bmRhdGlvbi5UaW1lciAgICAgICAgICA9IFRpbWVyO1xuXG4vLyBUb3VjaCBhbmQgVHJpZ2dlcnMgcHJldmlvdXNseSB3ZXJlIGFsbW9zdCBwdXJlbHkgc2VkZSBlZmZlY3QgZHJpdmVuLFxuLy8gc28gbm8gLy8gbmVlZCB0byBhZGQgaXQgdG8gRm91bmRhdGlvbiwganVzdCBpbml0IHRoZW0uXG5Ub3VjaC5pbml0KCAkICk7XG5cblRyaWdnZXJzLmluaXQoICQsIEZvdW5kYXRpb24gKTtcblxuLy8gRm91bmRhdGlvbi5wbHVnaW4oIEFiaWRlLCAnQWJpZGUnICk7XG4vLyBGb3VuZGF0aW9uLnBsdWdpbiggQWNjb3JkaW9uLCAnQWNjb3JkaW9uJyApO1xuLy8gRm91bmRhdGlvbi5wbHVnaW4oIEFjY29yZGlvbk1lbnUsICdBY2NvcmRpb25NZW51JyApO1xuLy8gRm91bmRhdGlvbi5wbHVnaW4oIERyaWxsZG93biwgJ0RyaWxsZG93bicgKTtcbi8vIEZvdW5kYXRpb24ucGx1Z2luKCBEcm9wZG93biwgJ0Ryb3Bkb3duJyApO1xuLy8gRm91bmRhdGlvbi5wbHVnaW4oIERyb3Bkb3duTWVudSwgJ0Ryb3Bkb3duTWVudScgKTtcbi8vIEZvdW5kYXRpb24ucGx1Z2luKCBFcXVhbGl6ZXIsICdFcXVhbGl6ZXInICk7XG4vLyBGb3VuZGF0aW9uLnBsdWdpbiggSW50ZXJjaGFuZ2UsICdJbnRlcmNoYW5nZScgKTtcbi8vIEZvdW5kYXRpb24ucGx1Z2luKCBNYWdlbGxhbiwgJ01hZ2VsbGFuJyApO1xuLy8gRm91bmRhdGlvbi5wbHVnaW4oIE9mZkNhbnZhcywgJ09mZkNhbnZhcycgKTtcbi8vIEZvdW5kYXRpb24ucGx1Z2luKCBPcmJpdCwgJ09yYml0JyApO1xuLy8gRm91bmRhdGlvbi5wbHVnaW4oIFJlc3BvbnNpdmVNZW51LCAnUmVzcG9uc2l2ZU1lbnUnICk7XG4vLyBGb3VuZGF0aW9uLnBsdWdpbiggUmVzcG9uc2l2ZVRvZ2dsZSwgJ1Jlc3BvbnNpdmVUb2dnbGUnICk7XG4vLyBGb3VuZGF0aW9uLnBsdWdpbiggUmV2ZWFsLCAnUmV2ZWFsJyApO1xuLy8gRm91bmRhdGlvbi5wbHVnaW4oIFNsaWRlciwgJ1NsaWRlcicgKTtcbi8vIEZvdW5kYXRpb24ucGx1Z2luKCBTbW9vdGhTY3JvbGwsICdTbW9vdGhTY3JvbGwnICk7XG5Gb3VuZGF0aW9uLnBsdWdpbiggU3RpY2t5LCAnU3RpY2t5JyApO1xuLy8gRm91bmRhdGlvbi5wbHVnaW4oIFRhYnMsICdUYWJzJyApO1xuRm91bmRhdGlvbi5wbHVnaW4oIFRvZ2dsZXIsICdUb2dnbGVyJyApO1xuLy8gRm91bmRhdGlvbi5wbHVnaW4oIFRvb2x0aXAsICdUb29sdGlwJyApO1xuLy8gRm91bmRhdGlvbi5wbHVnaW4oIFJlc3BvbnNpdmVBY2NvcmRpb25UYWJzLCAnUmVzcG9uc2l2ZUFjY29yZGlvblRhYnMnICk7XG5cbm1vZHVsZS5leHBvcnRzID0gRm91bmRhdGlvbjtcblxuXG5cbi8vIFdFQlBBQ0sgRk9PVEVSIC8vXG4vLyAuL3NyYy9hc3NldHMvanMvbGliL2ZvdW5kYXRpb24tZXhwbGljaXQtcGllY2VzLmpzIiwiXCJ1c2Ugc3RyaWN0XCI7XG5cbmltcG9ydCAkIGZyb20gJ2pxdWVyeSc7XG5pbXBvcnQgeyBHZXRZb0RpZ2l0cyB9IGZyb20gJy4vZm91bmRhdGlvbi5jb3JlLnV0aWxzJztcbmltcG9ydCB7IE1lZGlhUXVlcnkgfSBmcm9tICcuL2ZvdW5kYXRpb24udXRpbC5tZWRpYVF1ZXJ5JztcblxudmFyIEZPVU5EQVRJT05fVkVSU0lPTiA9ICc2LjUuMSc7XG5cbi8vIEdsb2JhbCBGb3VuZGF0aW9uIG9iamVjdFxuLy8gVGhpcyBpcyBhdHRhY2hlZCB0byB0aGUgd2luZG93LCBvciB1c2VkIGFzIGEgbW9kdWxlIGZvciBBTUQvQnJvd3NlcmlmeVxudmFyIEZvdW5kYXRpb24gPSB7XG4gIHZlcnNpb246IEZPVU5EQVRJT05fVkVSU0lPTixcblxuICAvKipcbiAgICogU3RvcmVzIGluaXRpYWxpemVkIHBsdWdpbnMuXG4gICAqL1xuICBfcGx1Z2luczoge30sXG5cbiAgLyoqXG4gICAqIFN0b3JlcyBnZW5lcmF0ZWQgdW5pcXVlIGlkcyBmb3IgcGx1Z2luIGluc3RhbmNlc1xuICAgKi9cbiAgX3V1aWRzOiBbXSxcblxuICAvKipcbiAgICogRGVmaW5lcyBhIEZvdW5kYXRpb24gcGx1Z2luLCBhZGRpbmcgaXQgdG8gdGhlIGBGb3VuZGF0aW9uYCBuYW1lc3BhY2UgYW5kIHRoZSBsaXN0IG9mIHBsdWdpbnMgdG8gaW5pdGlhbGl6ZSB3aGVuIHJlZmxvd2luZy5cbiAgICogQHBhcmFtIHtPYmplY3R9IHBsdWdpbiAtIFRoZSBjb25zdHJ1Y3RvciBvZiB0aGUgcGx1Z2luLlxuICAgKi9cbiAgcGx1Z2luOiBmdW5jdGlvbihwbHVnaW4sIG5hbWUpIHtcbiAgICAvLyBPYmplY3Qga2V5IHRvIHVzZSB3aGVuIGFkZGluZyB0byBnbG9iYWwgRm91bmRhdGlvbiBvYmplY3RcbiAgICAvLyBFeGFtcGxlczogRm91bmRhdGlvbi5SZXZlYWwsIEZvdW5kYXRpb24uT2ZmQ2FudmFzXG4gICAgdmFyIGNsYXNzTmFtZSA9IChuYW1lIHx8IGZ1bmN0aW9uTmFtZShwbHVnaW4pKTtcbiAgICAvLyBPYmplY3Qga2V5IHRvIHVzZSB3aGVuIHN0b3JpbmcgdGhlIHBsdWdpbiwgYWxzbyB1c2VkIHRvIGNyZWF0ZSB0aGUgaWRlbnRpZnlpbmcgZGF0YSBhdHRyaWJ1dGUgZm9yIHRoZSBwbHVnaW5cbiAgICAvLyBFeGFtcGxlczogZGF0YS1yZXZlYWwsIGRhdGEtb2ZmLWNhbnZhc1xuICAgIHZhciBhdHRyTmFtZSAgPSBoeXBoZW5hdGUoY2xhc3NOYW1lKTtcblxuICAgIC8vIEFkZCB0byB0aGUgRm91bmRhdGlvbiBvYmplY3QgYW5kIHRoZSBwbHVnaW5zIGxpc3QgKGZvciByZWZsb3dpbmcpXG4gICAgdGhpcy5fcGx1Z2luc1thdHRyTmFtZV0gPSB0aGlzW2NsYXNzTmFtZV0gPSBwbHVnaW47XG4gIH0sXG4gIC8qKlxuICAgKiBAZnVuY3Rpb25cbiAgICogUG9wdWxhdGVzIHRoZSBfdXVpZHMgYXJyYXkgd2l0aCBwb2ludGVycyB0byBlYWNoIGluZGl2aWR1YWwgcGx1Z2luIGluc3RhbmNlLlxuICAgKiBBZGRzIHRoZSBgemZQbHVnaW5gIGRhdGEtYXR0cmlidXRlIHRvIHByb2dyYW1tYXRpY2FsbHkgY3JlYXRlZCBwbHVnaW5zIHRvIGFsbG93IHVzZSBvZiAkKHNlbGVjdG9yKS5mb3VuZGF0aW9uKG1ldGhvZCkgY2FsbHMuXG4gICAqIEFsc28gZmlyZXMgdGhlIGluaXRpYWxpemF0aW9uIGV2ZW50IGZvciBlYWNoIHBsdWdpbiwgY29uc29saWRhdGluZyByZXBldGl0aXZlIGNvZGUuXG4gICAqIEBwYXJhbSB7T2JqZWN0fSBwbHVnaW4gLSBhbiBpbnN0YW5jZSBvZiBhIHBsdWdpbiwgdXN1YWxseSBgdGhpc2AgaW4gY29udGV4dC5cbiAgICogQHBhcmFtIHtTdHJpbmd9IG5hbWUgLSB0aGUgbmFtZSBvZiB0aGUgcGx1Z2luLCBwYXNzZWQgYXMgYSBjYW1lbENhc2VkIHN0cmluZy5cbiAgICogQGZpcmVzIFBsdWdpbiNpbml0XG4gICAqL1xuICByZWdpc3RlclBsdWdpbjogZnVuY3Rpb24ocGx1Z2luLCBuYW1lKXtcbiAgICB2YXIgcGx1Z2luTmFtZSA9IG5hbWUgPyBoeXBoZW5hdGUobmFtZSkgOiBmdW5jdGlvbk5hbWUocGx1Z2luLmNvbnN0cnVjdG9yKS50b0xvd2VyQ2FzZSgpO1xuICAgIHBsdWdpbi51dWlkID0gR2V0WW9EaWdpdHMoNiwgcGx1Z2luTmFtZSk7XG5cbiAgICBpZighcGx1Z2luLiRlbGVtZW50LmF0dHIoYGRhdGEtJHtwbHVnaW5OYW1lfWApKXsgcGx1Z2luLiRlbGVtZW50LmF0dHIoYGRhdGEtJHtwbHVnaW5OYW1lfWAsIHBsdWdpbi51dWlkKTsgfVxuICAgIGlmKCFwbHVnaW4uJGVsZW1lbnQuZGF0YSgnemZQbHVnaW4nKSl7IHBsdWdpbi4kZWxlbWVudC5kYXRhKCd6ZlBsdWdpbicsIHBsdWdpbik7IH1cbiAgICAgICAgICAvKipcbiAgICAgICAgICAgKiBGaXJlcyB3aGVuIHRoZSBwbHVnaW4gaGFzIGluaXRpYWxpemVkLlxuICAgICAgICAgICAqIEBldmVudCBQbHVnaW4jaW5pdFxuICAgICAgICAgICAqL1xuICAgIHBsdWdpbi4kZWxlbWVudC50cmlnZ2VyKGBpbml0LnpmLiR7cGx1Z2luTmFtZX1gKTtcblxuICAgIHRoaXMuX3V1aWRzLnB1c2gocGx1Z2luLnV1aWQpO1xuXG4gICAgcmV0dXJuO1xuICB9LFxuICAvKipcbiAgICogQGZ1bmN0aW9uXG4gICAqIFJlbW92ZXMgdGhlIHBsdWdpbnMgdXVpZCBmcm9tIHRoZSBfdXVpZHMgYXJyYXkuXG4gICAqIFJlbW92ZXMgdGhlIHpmUGx1Z2luIGRhdGEgYXR0cmlidXRlLCBhcyB3ZWxsIGFzIHRoZSBkYXRhLXBsdWdpbi1uYW1lIGF0dHJpYnV0ZS5cbiAgICogQWxzbyBmaXJlcyB0aGUgZGVzdHJveWVkIGV2ZW50IGZvciB0aGUgcGx1Z2luLCBjb25zb2xpZGF0aW5nIHJlcGV0aXRpdmUgY29kZS5cbiAgICogQHBhcmFtIHtPYmplY3R9IHBsdWdpbiAtIGFuIGluc3RhbmNlIG9mIGEgcGx1Z2luLCB1c3VhbGx5IGB0aGlzYCBpbiBjb250ZXh0LlxuICAgKiBAZmlyZXMgUGx1Z2luI2Rlc3Ryb3llZFxuICAgKi9cbiAgdW5yZWdpc3RlclBsdWdpbjogZnVuY3Rpb24ocGx1Z2luKXtcbiAgICB2YXIgcGx1Z2luTmFtZSA9IGh5cGhlbmF0ZShmdW5jdGlvbk5hbWUocGx1Z2luLiRlbGVtZW50LmRhdGEoJ3pmUGx1Z2luJykuY29uc3RydWN0b3IpKTtcblxuICAgIHRoaXMuX3V1aWRzLnNwbGljZSh0aGlzLl91dWlkcy5pbmRleE9mKHBsdWdpbi51dWlkKSwgMSk7XG4gICAgcGx1Z2luLiRlbGVtZW50LnJlbW92ZUF0dHIoYGRhdGEtJHtwbHVnaW5OYW1lfWApLnJlbW92ZURhdGEoJ3pmUGx1Z2luJylcbiAgICAgICAgICAvKipcbiAgICAgICAgICAgKiBGaXJlcyB3aGVuIHRoZSBwbHVnaW4gaGFzIGJlZW4gZGVzdHJveWVkLlxuICAgICAgICAgICAqIEBldmVudCBQbHVnaW4jZGVzdHJveWVkXG4gICAgICAgICAgICovXG4gICAgICAgICAgLnRyaWdnZXIoYGRlc3Ryb3llZC56Zi4ke3BsdWdpbk5hbWV9YCk7XG4gICAgZm9yKHZhciBwcm9wIGluIHBsdWdpbil7XG4gICAgICBwbHVnaW5bcHJvcF0gPSBudWxsOy8vY2xlYW4gdXAgc2NyaXB0IHRvIHByZXAgZm9yIGdhcmJhZ2UgY29sbGVjdGlvbi5cbiAgICB9XG4gICAgcmV0dXJuO1xuICB9LFxuXG4gIC8qKlxuICAgKiBAZnVuY3Rpb25cbiAgICogQ2F1c2VzIG9uZSBvciBtb3JlIGFjdGl2ZSBwbHVnaW5zIHRvIHJlLWluaXRpYWxpemUsIHJlc2V0dGluZyBldmVudCBsaXN0ZW5lcnMsIHJlY2FsY3VsYXRpbmcgcG9zaXRpb25zLCBldGMuXG4gICAqIEBwYXJhbSB7U3RyaW5nfSBwbHVnaW5zIC0gb3B0aW9uYWwgc3RyaW5nIG9mIGFuIGluZGl2aWR1YWwgcGx1Z2luIGtleSwgYXR0YWluZWQgYnkgY2FsbGluZyBgJChlbGVtZW50KS5kYXRhKCdwbHVnaW5OYW1lJylgLCBvciBzdHJpbmcgb2YgYSBwbHVnaW4gY2xhc3MgaS5lLiBgJ2Ryb3Bkb3duJ2BcbiAgICogQGRlZmF1bHQgSWYgbm8gYXJndW1lbnQgaXMgcGFzc2VkLCByZWZsb3cgYWxsIGN1cnJlbnRseSBhY3RpdmUgcGx1Z2lucy5cbiAgICovXG4gICByZUluaXQ6IGZ1bmN0aW9uKHBsdWdpbnMpe1xuICAgICB2YXIgaXNKUSA9IHBsdWdpbnMgaW5zdGFuY2VvZiAkO1xuICAgICB0cnl7XG4gICAgICAgaWYoaXNKUSl7XG4gICAgICAgICBwbHVnaW5zLmVhY2goZnVuY3Rpb24oKXtcbiAgICAgICAgICAgJCh0aGlzKS5kYXRhKCd6ZlBsdWdpbicpLl9pbml0KCk7XG4gICAgICAgICB9KTtcbiAgICAgICB9ZWxzZXtcbiAgICAgICAgIHZhciB0eXBlID0gdHlwZW9mIHBsdWdpbnMsXG4gICAgICAgICBfdGhpcyA9IHRoaXMsXG4gICAgICAgICBmbnMgPSB7XG4gICAgICAgICAgICdvYmplY3QnOiBmdW5jdGlvbihwbGdzKXtcbiAgICAgICAgICAgICBwbGdzLmZvckVhY2goZnVuY3Rpb24ocCl7XG4gICAgICAgICAgICAgICBwID0gaHlwaGVuYXRlKHApO1xuICAgICAgICAgICAgICAgJCgnW2RhdGEtJysgcCArJ10nKS5mb3VuZGF0aW9uKCdfaW5pdCcpO1xuICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICB9LFxuICAgICAgICAgICAnc3RyaW5nJzogZnVuY3Rpb24oKXtcbiAgICAgICAgICAgICBwbHVnaW5zID0gaHlwaGVuYXRlKHBsdWdpbnMpO1xuICAgICAgICAgICAgICQoJ1tkYXRhLScrIHBsdWdpbnMgKyddJykuZm91bmRhdGlvbignX2luaXQnKTtcbiAgICAgICAgICAgfSxcbiAgICAgICAgICAgJ3VuZGVmaW5lZCc6IGZ1bmN0aW9uKCl7XG4gICAgICAgICAgICAgdGhpc1snb2JqZWN0J10oT2JqZWN0LmtleXMoX3RoaXMuX3BsdWdpbnMpKTtcbiAgICAgICAgICAgfVxuICAgICAgICAgfTtcbiAgICAgICAgIGZuc1t0eXBlXShwbHVnaW5zKTtcbiAgICAgICB9XG4gICAgIH1jYXRjaChlcnIpe1xuICAgICAgIGNvbnNvbGUuZXJyb3IoZXJyKTtcbiAgICAgfWZpbmFsbHl7XG4gICAgICAgcmV0dXJuIHBsdWdpbnM7XG4gICAgIH1cbiAgIH0sXG5cbiAgLyoqXG4gICAqIEluaXRpYWxpemUgcGx1Z2lucyBvbiBhbnkgZWxlbWVudHMgd2l0aGluIGBlbGVtYCAoYW5kIGBlbGVtYCBpdHNlbGYpIHRoYXQgYXJlbid0IGFscmVhZHkgaW5pdGlhbGl6ZWQuXG4gICAqIEBwYXJhbSB7T2JqZWN0fSBlbGVtIC0galF1ZXJ5IG9iamVjdCBjb250YWluaW5nIHRoZSBlbGVtZW50IHRvIGNoZWNrIGluc2lkZS4gQWxzbyBjaGVja3MgdGhlIGVsZW1lbnQgaXRzZWxmLCB1bmxlc3MgaXQncyB0aGUgYGRvY3VtZW50YCBvYmplY3QuXG4gICAqIEBwYXJhbSB7U3RyaW5nfEFycmF5fSBwbHVnaW5zIC0gQSBsaXN0IG9mIHBsdWdpbnMgdG8gaW5pdGlhbGl6ZS4gTGVhdmUgdGhpcyBvdXQgdG8gaW5pdGlhbGl6ZSBldmVyeXRoaW5nLlxuICAgKi9cbiAgcmVmbG93OiBmdW5jdGlvbihlbGVtLCBwbHVnaW5zKSB7XG5cbiAgICAvLyBJZiBwbHVnaW5zIGlzIHVuZGVmaW5lZCwganVzdCBncmFiIGV2ZXJ5dGhpbmdcbiAgICBpZiAodHlwZW9mIHBsdWdpbnMgPT09ICd1bmRlZmluZWQnKSB7XG4gICAgICBwbHVnaW5zID0gT2JqZWN0LmtleXModGhpcy5fcGx1Z2lucyk7XG4gICAgfVxuICAgIC8vIElmIHBsdWdpbnMgaXMgYSBzdHJpbmcsIGNvbnZlcnQgaXQgdG8gYW4gYXJyYXkgd2l0aCBvbmUgaXRlbVxuICAgIGVsc2UgaWYgKHR5cGVvZiBwbHVnaW5zID09PSAnc3RyaW5nJykge1xuICAgICAgcGx1Z2lucyA9IFtwbHVnaW5zXTtcbiAgICB9XG5cbiAgICB2YXIgX3RoaXMgPSB0aGlzO1xuXG4gICAgLy8gSXRlcmF0ZSB0aHJvdWdoIGVhY2ggcGx1Z2luXG4gICAgJC5lYWNoKHBsdWdpbnMsIGZ1bmN0aW9uKGksIG5hbWUpIHtcbiAgICAgIC8vIEdldCB0aGUgY3VycmVudCBwbHVnaW5cbiAgICAgIHZhciBwbHVnaW4gPSBfdGhpcy5fcGx1Z2luc1tuYW1lXTtcblxuICAgICAgLy8gTG9jYWxpemUgdGhlIHNlYXJjaCB0byBhbGwgZWxlbWVudHMgaW5zaWRlIGVsZW0sIGFzIHdlbGwgYXMgZWxlbSBpdHNlbGYsIHVubGVzcyBlbGVtID09PSBkb2N1bWVudFxuICAgICAgdmFyICRlbGVtID0gJChlbGVtKS5maW5kKCdbZGF0YS0nK25hbWUrJ10nKS5hZGRCYWNrKCdbZGF0YS0nK25hbWUrJ10nKTtcblxuICAgICAgLy8gRm9yIGVhY2ggcGx1Z2luIGZvdW5kLCBpbml0aWFsaXplIGl0XG4gICAgICAkZWxlbS5lYWNoKGZ1bmN0aW9uKCkge1xuICAgICAgICB2YXIgJGVsID0gJCh0aGlzKSxcbiAgICAgICAgICAgIG9wdHMgPSB7fTtcbiAgICAgICAgLy8gRG9uJ3QgZG91YmxlLWRpcCBvbiBwbHVnaW5zXG4gICAgICAgIGlmICgkZWwuZGF0YSgnemZQbHVnaW4nKSkge1xuICAgICAgICAgIGNvbnNvbGUud2FybihcIlRyaWVkIHRvIGluaXRpYWxpemUgXCIrbmFtZStcIiBvbiBhbiBlbGVtZW50IHRoYXQgYWxyZWFkeSBoYXMgYSBGb3VuZGF0aW9uIHBsdWdpbi5cIik7XG4gICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYoJGVsLmF0dHIoJ2RhdGEtb3B0aW9ucycpKXtcbiAgICAgICAgICB2YXIgdGhpbmcgPSAkZWwuYXR0cignZGF0YS1vcHRpb25zJykuc3BsaXQoJzsnKS5mb3JFYWNoKGZ1bmN0aW9uKGUsIGkpe1xuICAgICAgICAgICAgdmFyIG9wdCA9IGUuc3BsaXQoJzonKS5tYXAoZnVuY3Rpb24oZWwpeyByZXR1cm4gZWwudHJpbSgpOyB9KTtcbiAgICAgICAgICAgIGlmKG9wdFswXSkgb3B0c1tvcHRbMF1dID0gcGFyc2VWYWx1ZShvcHRbMV0pO1xuICAgICAgICAgIH0pO1xuICAgICAgICB9XG4gICAgICAgIHRyeXtcbiAgICAgICAgICAkZWwuZGF0YSgnemZQbHVnaW4nLCBuZXcgcGx1Z2luKCQodGhpcyksIG9wdHMpKTtcbiAgICAgICAgfWNhdGNoKGVyKXtcbiAgICAgICAgICBjb25zb2xlLmVycm9yKGVyKTtcbiAgICAgICAgfWZpbmFsbHl7XG4gICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG4gICAgICB9KTtcbiAgICB9KTtcbiAgfSxcbiAgZ2V0Rm5OYW1lOiBmdW5jdGlvbk5hbWUsXG5cbiAgYWRkVG9KcXVlcnk6IGZ1bmN0aW9uKCQpIHtcbiAgICAvLyBUT0RPOiBjb25zaWRlciBub3QgbWFraW5nIHRoaXMgYSBqUXVlcnkgZnVuY3Rpb25cbiAgICAvLyBUT0RPOiBuZWVkIHdheSB0byByZWZsb3cgdnMuIHJlLWluaXRpYWxpemVcbiAgICAvKipcbiAgICAgKiBUaGUgRm91bmRhdGlvbiBqUXVlcnkgbWV0aG9kLlxuICAgICAqIEBwYXJhbSB7U3RyaW5nfEFycmF5fSBtZXRob2QgLSBBbiBhY3Rpb24gdG8gcGVyZm9ybSBvbiB0aGUgY3VycmVudCBqUXVlcnkgb2JqZWN0LlxuICAgICAqL1xuICAgIHZhciBmb3VuZGF0aW9uID0gZnVuY3Rpb24obWV0aG9kKSB7XG4gICAgICB2YXIgdHlwZSA9IHR5cGVvZiBtZXRob2QsXG4gICAgICAgICAgJG5vSlMgPSAkKCcubm8tanMnKTtcblxuICAgICAgaWYoJG5vSlMubGVuZ3RoKXtcbiAgICAgICAgJG5vSlMucmVtb3ZlQ2xhc3MoJ25vLWpzJyk7XG4gICAgICB9XG5cbiAgICAgIGlmKHR5cGUgPT09ICd1bmRlZmluZWQnKXsvL25lZWRzIHRvIGluaXRpYWxpemUgdGhlIEZvdW5kYXRpb24gb2JqZWN0LCBvciBhbiBpbmRpdmlkdWFsIHBsdWdpbi5cbiAgICAgICAgTWVkaWFRdWVyeS5faW5pdCgpO1xuICAgICAgICBGb3VuZGF0aW9uLnJlZmxvdyh0aGlzKTtcbiAgICAgIH1lbHNlIGlmKHR5cGUgPT09ICdzdHJpbmcnKXsvL2FuIGluZGl2aWR1YWwgbWV0aG9kIHRvIGludm9rZSBvbiBhIHBsdWdpbiBvciBncm91cCBvZiBwbHVnaW5zXG4gICAgICAgIHZhciBhcmdzID0gQXJyYXkucHJvdG90eXBlLnNsaWNlLmNhbGwoYXJndW1lbnRzLCAxKTsvL2NvbGxlY3QgYWxsIHRoZSBhcmd1bWVudHMsIGlmIG5lY2Vzc2FyeVxuICAgICAgICB2YXIgcGx1Z0NsYXNzID0gdGhpcy5kYXRhKCd6ZlBsdWdpbicpOy8vZGV0ZXJtaW5lIHRoZSBjbGFzcyBvZiBwbHVnaW5cblxuICAgICAgICBpZih0eXBlb2YgcGx1Z0NsYXNzICE9PSAndW5kZWZpbmVkJyAmJiB0eXBlb2YgcGx1Z0NsYXNzW21ldGhvZF0gIT09ICd1bmRlZmluZWQnKXsvL21ha2Ugc3VyZSBib3RoIHRoZSBjbGFzcyBhbmQgbWV0aG9kIGV4aXN0XG4gICAgICAgICAgaWYodGhpcy5sZW5ndGggPT09IDEpey8vaWYgdGhlcmUncyBvbmx5IG9uZSwgY2FsbCBpdCBkaXJlY3RseS5cbiAgICAgICAgICAgICAgcGx1Z0NsYXNzW21ldGhvZF0uYXBwbHkocGx1Z0NsYXNzLCBhcmdzKTtcbiAgICAgICAgICB9ZWxzZXtcbiAgICAgICAgICAgIHRoaXMuZWFjaChmdW5jdGlvbihpLCBlbCl7Ly9vdGhlcndpc2UgbG9vcCB0aHJvdWdoIHRoZSBqUXVlcnkgY29sbGVjdGlvbiBhbmQgaW52b2tlIHRoZSBtZXRob2Qgb24gZWFjaFxuICAgICAgICAgICAgICBwbHVnQ2xhc3NbbWV0aG9kXS5hcHBseSgkKGVsKS5kYXRhKCd6ZlBsdWdpbicpLCBhcmdzKTtcbiAgICAgICAgICAgIH0pO1xuICAgICAgICAgIH1cbiAgICAgICAgfWVsc2V7Ly9lcnJvciBmb3Igbm8gY2xhc3Mgb3Igbm8gbWV0aG9kXG4gICAgICAgICAgdGhyb3cgbmV3IFJlZmVyZW5jZUVycm9yKFwiV2UncmUgc29ycnksICdcIiArIG1ldGhvZCArIFwiJyBpcyBub3QgYW4gYXZhaWxhYmxlIG1ldGhvZCBmb3IgXCIgKyAocGx1Z0NsYXNzID8gZnVuY3Rpb25OYW1lKHBsdWdDbGFzcykgOiAndGhpcyBlbGVtZW50JykgKyAnLicpO1xuICAgICAgICB9XG4gICAgICB9ZWxzZXsvL2Vycm9yIGZvciBpbnZhbGlkIGFyZ3VtZW50IHR5cGVcbiAgICAgICAgdGhyb3cgbmV3IFR5cGVFcnJvcihgV2UncmUgc29ycnksICR7dHlwZX0gaXMgbm90IGEgdmFsaWQgcGFyYW1ldGVyLiBZb3UgbXVzdCB1c2UgYSBzdHJpbmcgcmVwcmVzZW50aW5nIHRoZSBtZXRob2QgeW91IHdpc2ggdG8gaW52b2tlLmApO1xuICAgICAgfVxuICAgICAgcmV0dXJuIHRoaXM7XG4gICAgfTtcbiAgICAkLmZuLmZvdW5kYXRpb24gPSBmb3VuZGF0aW9uO1xuICAgIHJldHVybiAkO1xuICB9XG59O1xuXG5Gb3VuZGF0aW9uLnV0aWwgPSB7XG4gIC8qKlxuICAgKiBGdW5jdGlvbiBmb3IgYXBwbHlpbmcgYSBkZWJvdW5jZSBlZmZlY3QgdG8gYSBmdW5jdGlvbiBjYWxsLlxuICAgKiBAZnVuY3Rpb25cbiAgICogQHBhcmFtIHtGdW5jdGlvbn0gZnVuYyAtIEZ1bmN0aW9uIHRvIGJlIGNhbGxlZCBhdCBlbmQgb2YgdGltZW91dC5cbiAgICogQHBhcmFtIHtOdW1iZXJ9IGRlbGF5IC0gVGltZSBpbiBtcyB0byBkZWxheSB0aGUgY2FsbCBvZiBgZnVuY2AuXG4gICAqIEByZXR1cm5zIGZ1bmN0aW9uXG4gICAqL1xuICB0aHJvdHRsZTogZnVuY3Rpb24gKGZ1bmMsIGRlbGF5KSB7XG4gICAgdmFyIHRpbWVyID0gbnVsbDtcblxuICAgIHJldHVybiBmdW5jdGlvbiAoKSB7XG4gICAgICB2YXIgY29udGV4dCA9IHRoaXMsIGFyZ3MgPSBhcmd1bWVudHM7XG5cbiAgICAgIGlmICh0aW1lciA9PT0gbnVsbCkge1xuICAgICAgICB0aW1lciA9IHNldFRpbWVvdXQoZnVuY3Rpb24gKCkge1xuICAgICAgICAgIGZ1bmMuYXBwbHkoY29udGV4dCwgYXJncyk7XG4gICAgICAgICAgdGltZXIgPSBudWxsO1xuICAgICAgICB9LCBkZWxheSk7XG4gICAgICB9XG4gICAgfTtcbiAgfVxufTtcblxud2luZG93LkZvdW5kYXRpb24gPSBGb3VuZGF0aW9uO1xuXG4vLyBQb2x5ZmlsbCBmb3IgcmVxdWVzdEFuaW1hdGlvbkZyYW1lXG4oZnVuY3Rpb24oKSB7XG4gIGlmICghRGF0ZS5ub3cgfHwgIXdpbmRvdy5EYXRlLm5vdylcbiAgICB3aW5kb3cuRGF0ZS5ub3cgPSBEYXRlLm5vdyA9IGZ1bmN0aW9uKCkgeyByZXR1cm4gbmV3IERhdGUoKS5nZXRUaW1lKCk7IH07XG5cbiAgdmFyIHZlbmRvcnMgPSBbJ3dlYmtpdCcsICdtb3onXTtcbiAgZm9yICh2YXIgaSA9IDA7IGkgPCB2ZW5kb3JzLmxlbmd0aCAmJiAhd2luZG93LnJlcXVlc3RBbmltYXRpb25GcmFtZTsgKytpKSB7XG4gICAgICB2YXIgdnAgPSB2ZW5kb3JzW2ldO1xuICAgICAgd2luZG93LnJlcXVlc3RBbmltYXRpb25GcmFtZSA9IHdpbmRvd1t2cCsnUmVxdWVzdEFuaW1hdGlvbkZyYW1lJ107XG4gICAgICB3aW5kb3cuY2FuY2VsQW5pbWF0aW9uRnJhbWUgPSAod2luZG93W3ZwKydDYW5jZWxBbmltYXRpb25GcmFtZSddXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB8fCB3aW5kb3dbdnArJ0NhbmNlbFJlcXVlc3RBbmltYXRpb25GcmFtZSddKTtcbiAgfVxuICBpZiAoL2lQKGFkfGhvbmV8b2QpLipPUyA2Ly50ZXN0KHdpbmRvdy5uYXZpZ2F0b3IudXNlckFnZW50KVxuICAgIHx8ICF3aW5kb3cucmVxdWVzdEFuaW1hdGlvbkZyYW1lIHx8ICF3aW5kb3cuY2FuY2VsQW5pbWF0aW9uRnJhbWUpIHtcbiAgICB2YXIgbGFzdFRpbWUgPSAwO1xuICAgIHdpbmRvdy5yZXF1ZXN0QW5pbWF0aW9uRnJhbWUgPSBmdW5jdGlvbihjYWxsYmFjaykge1xuICAgICAgICB2YXIgbm93ID0gRGF0ZS5ub3coKTtcbiAgICAgICAgdmFyIG5leHRUaW1lID0gTWF0aC5tYXgobGFzdFRpbWUgKyAxNiwgbm93KTtcbiAgICAgICAgcmV0dXJuIHNldFRpbWVvdXQoZnVuY3Rpb24oKSB7IGNhbGxiYWNrKGxhc3RUaW1lID0gbmV4dFRpbWUpOyB9LFxuICAgICAgICAgICAgICAgICAgICAgICAgICBuZXh0VGltZSAtIG5vdyk7XG4gICAgfTtcbiAgICB3aW5kb3cuY2FuY2VsQW5pbWF0aW9uRnJhbWUgPSBjbGVhclRpbWVvdXQ7XG4gIH1cbiAgLyoqXG4gICAqIFBvbHlmaWxsIGZvciBwZXJmb3JtYW5jZS5ub3csIHJlcXVpcmVkIGJ5IHJBRlxuICAgKi9cbiAgaWYoIXdpbmRvdy5wZXJmb3JtYW5jZSB8fCAhd2luZG93LnBlcmZvcm1hbmNlLm5vdyl7XG4gICAgd2luZG93LnBlcmZvcm1hbmNlID0ge1xuICAgICAgc3RhcnQ6IERhdGUubm93KCksXG4gICAgICBub3c6IGZ1bmN0aW9uKCl7IHJldHVybiBEYXRlLm5vdygpIC0gdGhpcy5zdGFydDsgfVxuICAgIH07XG4gIH1cbn0pKCk7XG5pZiAoIUZ1bmN0aW9uLnByb3RvdHlwZS5iaW5kKSB7XG4gIEZ1bmN0aW9uLnByb3RvdHlwZS5iaW5kID0gZnVuY3Rpb24ob1RoaXMpIHtcbiAgICBpZiAodHlwZW9mIHRoaXMgIT09ICdmdW5jdGlvbicpIHtcbiAgICAgIC8vIGNsb3Nlc3QgdGhpbmcgcG9zc2libGUgdG8gdGhlIEVDTUFTY3JpcHQgNVxuICAgICAgLy8gaW50ZXJuYWwgSXNDYWxsYWJsZSBmdW5jdGlvblxuICAgICAgdGhyb3cgbmV3IFR5cGVFcnJvcignRnVuY3Rpb24ucHJvdG90eXBlLmJpbmQgLSB3aGF0IGlzIHRyeWluZyB0byBiZSBib3VuZCBpcyBub3QgY2FsbGFibGUnKTtcbiAgICB9XG5cbiAgICB2YXIgYUFyZ3MgICA9IEFycmF5LnByb3RvdHlwZS5zbGljZS5jYWxsKGFyZ3VtZW50cywgMSksXG4gICAgICAgIGZUb0JpbmQgPSB0aGlzLFxuICAgICAgICBmTk9QICAgID0gZnVuY3Rpb24oKSB7fSxcbiAgICAgICAgZkJvdW5kICA9IGZ1bmN0aW9uKCkge1xuICAgICAgICAgIHJldHVybiBmVG9CaW5kLmFwcGx5KHRoaXMgaW5zdGFuY2VvZiBmTk9QXG4gICAgICAgICAgICAgICAgID8gdGhpc1xuICAgICAgICAgICAgICAgICA6IG9UaGlzLFxuICAgICAgICAgICAgICAgICBhQXJncy5jb25jYXQoQXJyYXkucHJvdG90eXBlLnNsaWNlLmNhbGwoYXJndW1lbnRzKSkpO1xuICAgICAgICB9O1xuXG4gICAgaWYgKHRoaXMucHJvdG90eXBlKSB7XG4gICAgICAvLyBuYXRpdmUgZnVuY3Rpb25zIGRvbid0IGhhdmUgYSBwcm90b3R5cGVcbiAgICAgIGZOT1AucHJvdG90eXBlID0gdGhpcy5wcm90b3R5cGU7XG4gICAgfVxuICAgIGZCb3VuZC5wcm90b3R5cGUgPSBuZXcgZk5PUCgpO1xuXG4gICAgcmV0dXJuIGZCb3VuZDtcbiAgfTtcbn1cbi8vIFBvbHlmaWxsIHRvIGdldCB0aGUgbmFtZSBvZiBhIGZ1bmN0aW9uIGluIElFOVxuZnVuY3Rpb24gZnVuY3Rpb25OYW1lKGZuKSB7XG4gIGlmICh0eXBlb2YgRnVuY3Rpb24ucHJvdG90eXBlLm5hbWUgPT09ICd1bmRlZmluZWQnKSB7XG4gICAgdmFyIGZ1bmNOYW1lUmVnZXggPSAvZnVuY3Rpb25cXHMoW14oXXsxLH0pXFwoLztcbiAgICB2YXIgcmVzdWx0cyA9IChmdW5jTmFtZVJlZ2V4KS5leGVjKChmbikudG9TdHJpbmcoKSk7XG4gICAgcmV0dXJuIChyZXN1bHRzICYmIHJlc3VsdHMubGVuZ3RoID4gMSkgPyByZXN1bHRzWzFdLnRyaW0oKSA6IFwiXCI7XG4gIH1cbiAgZWxzZSBpZiAodHlwZW9mIGZuLnByb3RvdHlwZSA9PT0gJ3VuZGVmaW5lZCcpIHtcbiAgICByZXR1cm4gZm4uY29uc3RydWN0b3IubmFtZTtcbiAgfVxuICBlbHNlIHtcbiAgICByZXR1cm4gZm4ucHJvdG90eXBlLmNvbnN0cnVjdG9yLm5hbWU7XG4gIH1cbn1cbmZ1bmN0aW9uIHBhcnNlVmFsdWUoc3RyKXtcbiAgaWYgKCd0cnVlJyA9PT0gc3RyKSByZXR1cm4gdHJ1ZTtcbiAgZWxzZSBpZiAoJ2ZhbHNlJyA9PT0gc3RyKSByZXR1cm4gZmFsc2U7XG4gIGVsc2UgaWYgKCFpc05hTihzdHIgKiAxKSkgcmV0dXJuIHBhcnNlRmxvYXQoc3RyKTtcbiAgcmV0dXJuIHN0cjtcbn1cbi8vIENvbnZlcnQgUGFzY2FsQ2FzZSB0byBrZWJhYi1jYXNlXG4vLyBUaGFuayB5b3U6IGh0dHA6Ly9zdGFja292ZXJmbG93LmNvbS9hLzg5NTU1ODBcbmZ1bmN0aW9uIGh5cGhlbmF0ZShzdHIpIHtcbiAgcmV0dXJuIHN0ci5yZXBsYWNlKC8oW2Etel0pKFtBLVpdKS9nLCAnJDEtJDInKS50b0xvd2VyQ2FzZSgpO1xufVxuXG5leHBvcnQge0ZvdW5kYXRpb259O1xuXG5cblxuLy8gV0VCUEFDSyBGT09URVIgLy9cbi8vIC4vbm9kZV9tb2R1bGVzL2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi5jb3JlLmpzIiwiJ3VzZSBzdHJpY3QnO1xuXG5cbmltcG9ydCB7IHJ0bCBhcyBSdGwgfSBmcm9tIFwiLi9mb3VuZGF0aW9uLmNvcmUudXRpbHNcIjtcblxudmFyIEJveCA9IHtcbiAgSW1Ob3RUb3VjaGluZ1lvdTogSW1Ob3RUb3VjaGluZ1lvdSxcbiAgT3ZlcmxhcEFyZWE6IE92ZXJsYXBBcmVhLFxuICBHZXREaW1lbnNpb25zOiBHZXREaW1lbnNpb25zLFxuICBHZXRPZmZzZXRzOiBHZXRPZmZzZXRzLFxuICBHZXRFeHBsaWNpdE9mZnNldHM6IEdldEV4cGxpY2l0T2Zmc2V0c1xufVxuXG4vKipcbiAqIENvbXBhcmVzIHRoZSBkaW1lbnNpb25zIG9mIGFuIGVsZW1lbnQgdG8gYSBjb250YWluZXIgYW5kIGRldGVybWluZXMgY29sbGlzaW9uIGV2ZW50cyB3aXRoIGNvbnRhaW5lci5cbiAqIEBmdW5jdGlvblxuICogQHBhcmFtIHtqUXVlcnl9IGVsZW1lbnQgLSBqUXVlcnkgb2JqZWN0IHRvIHRlc3QgZm9yIGNvbGxpc2lvbnMuXG4gKiBAcGFyYW0ge2pRdWVyeX0gcGFyZW50IC0galF1ZXJ5IG9iamVjdCB0byB1c2UgYXMgYm91bmRpbmcgY29udGFpbmVyLlxuICogQHBhcmFtIHtCb29sZWFufSBsck9ubHkgLSBzZXQgdG8gdHJ1ZSB0byBjaGVjayBsZWZ0IGFuZCByaWdodCB2YWx1ZXMgb25seS5cbiAqIEBwYXJhbSB7Qm9vbGVhbn0gdGJPbmx5IC0gc2V0IHRvIHRydWUgdG8gY2hlY2sgdG9wIGFuZCBib3R0b20gdmFsdWVzIG9ubHkuXG4gKiBAZGVmYXVsdCBpZiBubyBwYXJlbnQgb2JqZWN0IHBhc3NlZCwgZGV0ZWN0cyBjb2xsaXNpb25zIHdpdGggYHdpbmRvd2AuXG4gKiBAcmV0dXJucyB7Qm9vbGVhbn0gLSB0cnVlIGlmIGNvbGxpc2lvbiBmcmVlLCBmYWxzZSBpZiBhIGNvbGxpc2lvbiBpbiBhbnkgZGlyZWN0aW9uLlxuICovXG5mdW5jdGlvbiBJbU5vdFRvdWNoaW5nWW91KGVsZW1lbnQsIHBhcmVudCwgbHJPbmx5LCB0Yk9ubHksIGlnbm9yZUJvdHRvbSkge1xuICByZXR1cm4gT3ZlcmxhcEFyZWEoZWxlbWVudCwgcGFyZW50LCBsck9ubHksIHRiT25seSwgaWdub3JlQm90dG9tKSA9PT0gMDtcbn07XG5cbmZ1bmN0aW9uIE92ZXJsYXBBcmVhKGVsZW1lbnQsIHBhcmVudCwgbHJPbmx5LCB0Yk9ubHksIGlnbm9yZUJvdHRvbSkge1xuICB2YXIgZWxlRGltcyA9IEdldERpbWVuc2lvbnMoZWxlbWVudCksXG4gIHRvcE92ZXIsIGJvdHRvbU92ZXIsIGxlZnRPdmVyLCByaWdodE92ZXI7XG4gIGlmIChwYXJlbnQpIHtcbiAgICB2YXIgcGFyRGltcyA9IEdldERpbWVuc2lvbnMocGFyZW50KTtcblxuICAgIGJvdHRvbU92ZXIgPSAocGFyRGltcy5oZWlnaHQgKyBwYXJEaW1zLm9mZnNldC50b3ApIC0gKGVsZURpbXMub2Zmc2V0LnRvcCArIGVsZURpbXMuaGVpZ2h0KTtcbiAgICB0b3BPdmVyICAgID0gZWxlRGltcy5vZmZzZXQudG9wIC0gcGFyRGltcy5vZmZzZXQudG9wO1xuICAgIGxlZnRPdmVyICAgPSBlbGVEaW1zLm9mZnNldC5sZWZ0IC0gcGFyRGltcy5vZmZzZXQubGVmdDtcbiAgICByaWdodE92ZXIgID0gKHBhckRpbXMud2lkdGggKyBwYXJEaW1zLm9mZnNldC5sZWZ0KSAtIChlbGVEaW1zLm9mZnNldC5sZWZ0ICsgZWxlRGltcy53aWR0aCk7XG4gIH1cbiAgZWxzZSB7XG4gICAgYm90dG9tT3ZlciA9IChlbGVEaW1zLndpbmRvd0RpbXMuaGVpZ2h0ICsgZWxlRGltcy53aW5kb3dEaW1zLm9mZnNldC50b3ApIC0gKGVsZURpbXMub2Zmc2V0LnRvcCArIGVsZURpbXMuaGVpZ2h0KTtcbiAgICB0b3BPdmVyICAgID0gZWxlRGltcy5vZmZzZXQudG9wIC0gZWxlRGltcy53aW5kb3dEaW1zLm9mZnNldC50b3A7XG4gICAgbGVmdE92ZXIgICA9IGVsZURpbXMub2Zmc2V0LmxlZnQgLSBlbGVEaW1zLndpbmRvd0RpbXMub2Zmc2V0LmxlZnQ7XG4gICAgcmlnaHRPdmVyICA9IGVsZURpbXMud2luZG93RGltcy53aWR0aCAtIChlbGVEaW1zLm9mZnNldC5sZWZ0ICsgZWxlRGltcy53aWR0aCk7XG4gIH1cblxuICBib3R0b21PdmVyID0gaWdub3JlQm90dG9tID8gMCA6IE1hdGgubWluKGJvdHRvbU92ZXIsIDApO1xuICB0b3BPdmVyICAgID0gTWF0aC5taW4odG9wT3ZlciwgMCk7XG4gIGxlZnRPdmVyICAgPSBNYXRoLm1pbihsZWZ0T3ZlciwgMCk7XG4gIHJpZ2h0T3ZlciAgPSBNYXRoLm1pbihyaWdodE92ZXIsIDApO1xuXG4gIGlmIChsck9ubHkpIHtcbiAgICByZXR1cm4gbGVmdE92ZXIgKyByaWdodE92ZXI7XG4gIH1cbiAgaWYgKHRiT25seSkge1xuICAgIHJldHVybiB0b3BPdmVyICsgYm90dG9tT3ZlcjtcbiAgfVxuXG4gIC8vIHVzZSBzdW0gb2Ygc3F1YXJlcyBiL2Mgd2UgY2FyZSBhYm91dCBvdmVybGFwIGFyZWEuXG4gIHJldHVybiBNYXRoLnNxcnQoKHRvcE92ZXIgKiB0b3BPdmVyKSArIChib3R0b21PdmVyICogYm90dG9tT3ZlcikgKyAobGVmdE92ZXIgKiBsZWZ0T3ZlcikgKyAocmlnaHRPdmVyICogcmlnaHRPdmVyKSk7XG59XG5cbi8qKlxuICogVXNlcyBuYXRpdmUgbWV0aG9kcyB0byByZXR1cm4gYW4gb2JqZWN0IG9mIGRpbWVuc2lvbiB2YWx1ZXMuXG4gKiBAZnVuY3Rpb25cbiAqIEBwYXJhbSB7alF1ZXJ5IHx8IEhUTUx9IGVsZW1lbnQgLSBqUXVlcnkgb2JqZWN0IG9yIERPTSBlbGVtZW50IGZvciB3aGljaCB0byBnZXQgdGhlIGRpbWVuc2lvbnMuIENhbiBiZSBhbnkgZWxlbWVudCBvdGhlciB0aGF0IGRvY3VtZW50IG9yIHdpbmRvdy5cbiAqIEByZXR1cm5zIHtPYmplY3R9IC0gbmVzdGVkIG9iamVjdCBvZiBpbnRlZ2VyIHBpeGVsIHZhbHVlc1xuICogVE9ETyAtIGlmIGVsZW1lbnQgaXMgd2luZG93LCByZXR1cm4gb25seSB0aG9zZSB2YWx1ZXMuXG4gKi9cbmZ1bmN0aW9uIEdldERpbWVuc2lvbnMoZWxlbSl7XG4gIGVsZW0gPSBlbGVtLmxlbmd0aCA/IGVsZW1bMF0gOiBlbGVtO1xuXG4gIGlmIChlbGVtID09PSB3aW5kb3cgfHwgZWxlbSA9PT0gZG9jdW1lbnQpIHtcbiAgICB0aHJvdyBuZXcgRXJyb3IoXCJJJ20gc29ycnksIERhdmUuIEknbSBhZnJhaWQgSSBjYW4ndCBkbyB0aGF0LlwiKTtcbiAgfVxuXG4gIHZhciByZWN0ID0gZWxlbS5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKSxcbiAgICAgIHBhclJlY3QgPSBlbGVtLnBhcmVudE5vZGUuZ2V0Qm91bmRpbmdDbGllbnRSZWN0KCksXG4gICAgICB3aW5SZWN0ID0gZG9jdW1lbnQuYm9keS5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKSxcbiAgICAgIHdpblkgPSB3aW5kb3cucGFnZVlPZmZzZXQsXG4gICAgICB3aW5YID0gd2luZG93LnBhZ2VYT2Zmc2V0O1xuXG4gIHJldHVybiB7XG4gICAgd2lkdGg6IHJlY3Qud2lkdGgsXG4gICAgaGVpZ2h0OiByZWN0LmhlaWdodCxcbiAgICBvZmZzZXQ6IHtcbiAgICAgIHRvcDogcmVjdC50b3AgKyB3aW5ZLFxuICAgICAgbGVmdDogcmVjdC5sZWZ0ICsgd2luWFxuICAgIH0sXG4gICAgcGFyZW50RGltczoge1xuICAgICAgd2lkdGg6IHBhclJlY3Qud2lkdGgsXG4gICAgICBoZWlnaHQ6IHBhclJlY3QuaGVpZ2h0LFxuICAgICAgb2Zmc2V0OiB7XG4gICAgICAgIHRvcDogcGFyUmVjdC50b3AgKyB3aW5ZLFxuICAgICAgICBsZWZ0OiBwYXJSZWN0LmxlZnQgKyB3aW5YXG4gICAgICB9XG4gICAgfSxcbiAgICB3aW5kb3dEaW1zOiB7XG4gICAgICB3aWR0aDogd2luUmVjdC53aWR0aCxcbiAgICAgIGhlaWdodDogd2luUmVjdC5oZWlnaHQsXG4gICAgICBvZmZzZXQ6IHtcbiAgICAgICAgdG9wOiB3aW5ZLFxuICAgICAgICBsZWZ0OiB3aW5YXG4gICAgICB9XG4gICAgfVxuICB9XG59XG5cbi8qKlxuICogUmV0dXJucyBhbiBvYmplY3Qgb2YgdG9wIGFuZCBsZWZ0IGludGVnZXIgcGl4ZWwgdmFsdWVzIGZvciBkeW5hbWljYWxseSByZW5kZXJlZCBlbGVtZW50cyxcbiAqIHN1Y2ggYXM6IFRvb2x0aXAsIFJldmVhbCwgYW5kIERyb3Bkb3duLiBNYWludGFpbmVkIGZvciBiYWNrd2FyZHMgY29tcGF0aWJpbGl0eSwgYW5kIHdoZXJlXG4gKiB5b3UgZG9uJ3Qga25vdyBhbGlnbm1lbnQsIGJ1dCBnZW5lcmFsbHkgZnJvbVxuICogNi40IGZvcndhcmQgeW91IHNob3VsZCB1c2UgR2V0RXhwbGljaXRPZmZzZXRzLCBhcyBHZXRPZmZzZXRzIGNvbmZsYXRlcyBwb3NpdGlvbiBhbmQgYWxpZ25tZW50LlxuICogQGZ1bmN0aW9uXG4gKiBAcGFyYW0ge2pRdWVyeX0gZWxlbWVudCAtIGpRdWVyeSBvYmplY3QgZm9yIHRoZSBlbGVtZW50IGJlaW5nIHBvc2l0aW9uZWQuXG4gKiBAcGFyYW0ge2pRdWVyeX0gYW5jaG9yIC0galF1ZXJ5IG9iamVjdCBmb3IgdGhlIGVsZW1lbnQncyBhbmNob3IgcG9pbnQuXG4gKiBAcGFyYW0ge1N0cmluZ30gcG9zaXRpb24gLSBhIHN0cmluZyByZWxhdGluZyB0byB0aGUgZGVzaXJlZCBwb3NpdGlvbiBvZiB0aGUgZWxlbWVudCwgcmVsYXRpdmUgdG8gaXQncyBhbmNob3JcbiAqIEBwYXJhbSB7TnVtYmVyfSB2T2Zmc2V0IC0gaW50ZWdlciBwaXhlbCB2YWx1ZSBvZiBkZXNpcmVkIHZlcnRpY2FsIHNlcGFyYXRpb24gYmV0d2VlbiBhbmNob3IgYW5kIGVsZW1lbnQuXG4gKiBAcGFyYW0ge051bWJlcn0gaE9mZnNldCAtIGludGVnZXIgcGl4ZWwgdmFsdWUgb2YgZGVzaXJlZCBob3Jpem9udGFsIHNlcGFyYXRpb24gYmV0d2VlbiBhbmNob3IgYW5kIGVsZW1lbnQuXG4gKiBAcGFyYW0ge0Jvb2xlYW59IGlzT3ZlcmZsb3cgLSBpZiBhIGNvbGxpc2lvbiBldmVudCBpcyBkZXRlY3RlZCwgc2V0cyB0byB0cnVlIHRvIGRlZmF1bHQgdGhlIGVsZW1lbnQgdG8gZnVsbCB3aWR0aCAtIGFueSBkZXNpcmVkIG9mZnNldC5cbiAqIFRPRE8gYWx0ZXIvcmV3cml0ZSB0byB3b3JrIHdpdGggYGVtYCB2YWx1ZXMgYXMgd2VsbC9pbnN0ZWFkIG9mIHBpeGVsc1xuICovXG5mdW5jdGlvbiBHZXRPZmZzZXRzKGVsZW1lbnQsIGFuY2hvciwgcG9zaXRpb24sIHZPZmZzZXQsIGhPZmZzZXQsIGlzT3ZlcmZsb3cpIHtcbiAgY29uc29sZS5sb2coXCJOT1RFOiBHZXRPZmZzZXRzIGlzIGRlcHJlY2F0ZWQgaW4gZmF2b3Igb2YgR2V0RXhwbGljaXRPZmZzZXRzIGFuZCB3aWxsIGJlIHJlbW92ZWQgaW4gNi41XCIpO1xuICBzd2l0Y2ggKHBvc2l0aW9uKSB7XG4gICAgY2FzZSAndG9wJzpcbiAgICAgIHJldHVybiBSdGwoKSA/XG4gICAgICAgIEdldEV4cGxpY2l0T2Zmc2V0cyhlbGVtZW50LCBhbmNob3IsICd0b3AnLCAnbGVmdCcsIHZPZmZzZXQsIGhPZmZzZXQsIGlzT3ZlcmZsb3cpIDpcbiAgICAgICAgR2V0RXhwbGljaXRPZmZzZXRzKGVsZW1lbnQsIGFuY2hvciwgJ3RvcCcsICdyaWdodCcsIHZPZmZzZXQsIGhPZmZzZXQsIGlzT3ZlcmZsb3cpO1xuICAgIGNhc2UgJ2JvdHRvbSc6XG4gICAgICByZXR1cm4gUnRsKCkgP1xuICAgICAgICBHZXRFeHBsaWNpdE9mZnNldHMoZWxlbWVudCwgYW5jaG9yLCAnYm90dG9tJywgJ2xlZnQnLCB2T2Zmc2V0LCBoT2Zmc2V0LCBpc092ZXJmbG93KSA6XG4gICAgICAgIEdldEV4cGxpY2l0T2Zmc2V0cyhlbGVtZW50LCBhbmNob3IsICdib3R0b20nLCAncmlnaHQnLCB2T2Zmc2V0LCBoT2Zmc2V0LCBpc092ZXJmbG93KTtcbiAgICBjYXNlICdjZW50ZXIgdG9wJzpcbiAgICAgIHJldHVybiBHZXRFeHBsaWNpdE9mZnNldHMoZWxlbWVudCwgYW5jaG9yLCAndG9wJywgJ2NlbnRlcicsIHZPZmZzZXQsIGhPZmZzZXQsIGlzT3ZlcmZsb3cpO1xuICAgIGNhc2UgJ2NlbnRlciBib3R0b20nOlxuICAgICAgcmV0dXJuIEdldEV4cGxpY2l0T2Zmc2V0cyhlbGVtZW50LCBhbmNob3IsICdib3R0b20nLCAnY2VudGVyJywgdk9mZnNldCwgaE9mZnNldCwgaXNPdmVyZmxvdyk7XG4gICAgY2FzZSAnY2VudGVyIGxlZnQnOlxuICAgICAgcmV0dXJuIEdldEV4cGxpY2l0T2Zmc2V0cyhlbGVtZW50LCBhbmNob3IsICdsZWZ0JywgJ2NlbnRlcicsIHZPZmZzZXQsIGhPZmZzZXQsIGlzT3ZlcmZsb3cpO1xuICAgIGNhc2UgJ2NlbnRlciByaWdodCc6XG4gICAgICByZXR1cm4gR2V0RXhwbGljaXRPZmZzZXRzKGVsZW1lbnQsIGFuY2hvciwgJ3JpZ2h0JywgJ2NlbnRlcicsIHZPZmZzZXQsIGhPZmZzZXQsIGlzT3ZlcmZsb3cpO1xuICAgIGNhc2UgJ2xlZnQgYm90dG9tJzpcbiAgICAgIHJldHVybiBHZXRFeHBsaWNpdE9mZnNldHMoZWxlbWVudCwgYW5jaG9yLCAnYm90dG9tJywgJ2xlZnQnLCB2T2Zmc2V0LCBoT2Zmc2V0LCBpc092ZXJmbG93KTtcbiAgICBjYXNlICdyaWdodCBib3R0b20nOlxuICAgICAgcmV0dXJuIEdldEV4cGxpY2l0T2Zmc2V0cyhlbGVtZW50LCBhbmNob3IsICdib3R0b20nLCAncmlnaHQnLCB2T2Zmc2V0LCBoT2Zmc2V0LCBpc092ZXJmbG93KTtcbiAgICAvLyBCYWNrd2FyZHMgY29tcGF0aWJpbGl0eS4uLiB0aGlzIGFsb25nIHdpdGggdGhlIHJldmVhbCBhbmQgcmV2ZWFsIGZ1bGxcbiAgICAvLyBjbGFzc2VzIGFyZSB0aGUgb25seSBvbmVzIHRoYXQgZGlkbid0IHJlZmVyZW5jZSBhbmNob3JcbiAgICBjYXNlICdjZW50ZXInOlxuICAgICAgcmV0dXJuIHtcbiAgICAgICAgbGVmdDogKCRlbGVEaW1zLndpbmRvd0RpbXMub2Zmc2V0LmxlZnQgKyAoJGVsZURpbXMud2luZG93RGltcy53aWR0aCAvIDIpKSAtICgkZWxlRGltcy53aWR0aCAvIDIpICsgaE9mZnNldCxcbiAgICAgICAgdG9wOiAoJGVsZURpbXMud2luZG93RGltcy5vZmZzZXQudG9wICsgKCRlbGVEaW1zLndpbmRvd0RpbXMuaGVpZ2h0IC8gMikpIC0gKCRlbGVEaW1zLmhlaWdodCAvIDIgKyB2T2Zmc2V0KVxuICAgICAgfVxuICAgIGNhc2UgJ3JldmVhbCc6XG4gICAgICByZXR1cm4ge1xuICAgICAgICBsZWZ0OiAoJGVsZURpbXMud2luZG93RGltcy53aWR0aCAtICRlbGVEaW1zLndpZHRoKSAvIDIgKyBoT2Zmc2V0LFxuICAgICAgICB0b3A6ICRlbGVEaW1zLndpbmRvd0RpbXMub2Zmc2V0LnRvcCArIHZPZmZzZXRcbiAgICAgIH1cbiAgICBjYXNlICdyZXZlYWwgZnVsbCc6XG4gICAgICByZXR1cm4ge1xuICAgICAgICBsZWZ0OiAkZWxlRGltcy53aW5kb3dEaW1zLm9mZnNldC5sZWZ0LFxuICAgICAgICB0b3A6ICRlbGVEaW1zLndpbmRvd0RpbXMub2Zmc2V0LnRvcFxuICAgICAgfVxuICAgICAgYnJlYWs7XG4gICAgZGVmYXVsdDpcbiAgICAgIHJldHVybiB7XG4gICAgICAgIGxlZnQ6IChSdGwoKSA/ICRhbmNob3JEaW1zLm9mZnNldC5sZWZ0IC0gJGVsZURpbXMud2lkdGggKyAkYW5jaG9yRGltcy53aWR0aCAtIGhPZmZzZXQ6ICRhbmNob3JEaW1zLm9mZnNldC5sZWZ0ICsgaE9mZnNldCksXG4gICAgICAgIHRvcDogJGFuY2hvckRpbXMub2Zmc2V0LnRvcCArICRhbmNob3JEaW1zLmhlaWdodCArIHZPZmZzZXRcbiAgICAgIH1cblxuICB9XG5cbn1cblxuZnVuY3Rpb24gR2V0RXhwbGljaXRPZmZzZXRzKGVsZW1lbnQsIGFuY2hvciwgcG9zaXRpb24sIGFsaWdubWVudCwgdk9mZnNldCwgaE9mZnNldCwgaXNPdmVyZmxvdykge1xuICB2YXIgJGVsZURpbXMgPSBHZXREaW1lbnNpb25zKGVsZW1lbnQpLFxuICAgICAgJGFuY2hvckRpbXMgPSBhbmNob3IgPyBHZXREaW1lbnNpb25zKGFuY2hvcikgOiBudWxsO1xuXG4gICAgICB2YXIgdG9wVmFsLCBsZWZ0VmFsO1xuXG4gIC8vIHNldCBwb3NpdGlvbiByZWxhdGVkIGF0dHJpYnV0ZVxuXG4gIHN3aXRjaCAocG9zaXRpb24pIHtcbiAgICBjYXNlICd0b3AnOlxuICAgICAgdG9wVmFsID0gJGFuY2hvckRpbXMub2Zmc2V0LnRvcCAtICgkZWxlRGltcy5oZWlnaHQgKyB2T2Zmc2V0KTtcbiAgICAgIGJyZWFrO1xuICAgIGNhc2UgJ2JvdHRvbSc6XG4gICAgICB0b3BWYWwgPSAkYW5jaG9yRGltcy5vZmZzZXQudG9wICsgJGFuY2hvckRpbXMuaGVpZ2h0ICsgdk9mZnNldDtcbiAgICAgIGJyZWFrO1xuICAgIGNhc2UgJ2xlZnQnOlxuICAgICAgbGVmdFZhbCA9ICRhbmNob3JEaW1zLm9mZnNldC5sZWZ0IC0gKCRlbGVEaW1zLndpZHRoICsgaE9mZnNldCk7XG4gICAgICBicmVhaztcbiAgICBjYXNlICdyaWdodCc6XG4gICAgICBsZWZ0VmFsID0gJGFuY2hvckRpbXMub2Zmc2V0LmxlZnQgKyAkYW5jaG9yRGltcy53aWR0aCArIGhPZmZzZXQ7XG4gICAgICBicmVhaztcbiAgfVxuXG5cbiAgLy8gc2V0IGFsaWdubWVudCByZWxhdGVkIGF0dHJpYnV0ZVxuICBzd2l0Y2ggKHBvc2l0aW9uKSB7XG4gICAgY2FzZSAndG9wJzpcbiAgICBjYXNlICdib3R0b20nOlxuICAgICAgc3dpdGNoIChhbGlnbm1lbnQpIHtcbiAgICAgICAgY2FzZSAnbGVmdCc6XG4gICAgICAgICAgbGVmdFZhbCA9ICRhbmNob3JEaW1zLm9mZnNldC5sZWZ0ICsgaE9mZnNldDtcbiAgICAgICAgICBicmVhaztcbiAgICAgICAgY2FzZSAncmlnaHQnOlxuICAgICAgICAgIGxlZnRWYWwgPSAkYW5jaG9yRGltcy5vZmZzZXQubGVmdCAtICRlbGVEaW1zLndpZHRoICsgJGFuY2hvckRpbXMud2lkdGggLSBoT2Zmc2V0O1xuICAgICAgICAgIGJyZWFrO1xuICAgICAgICBjYXNlICdjZW50ZXInOlxuICAgICAgICAgIGxlZnRWYWwgPSBpc092ZXJmbG93ID8gaE9mZnNldCA6ICgoJGFuY2hvckRpbXMub2Zmc2V0LmxlZnQgKyAoJGFuY2hvckRpbXMud2lkdGggLyAyKSkgLSAoJGVsZURpbXMud2lkdGggLyAyKSkgKyBoT2Zmc2V0O1xuICAgICAgICAgIGJyZWFrO1xuICAgICAgfVxuICAgICAgYnJlYWs7XG4gICAgY2FzZSAncmlnaHQnOlxuICAgIGNhc2UgJ2xlZnQnOlxuICAgICAgc3dpdGNoIChhbGlnbm1lbnQpIHtcbiAgICAgICAgY2FzZSAnYm90dG9tJzpcbiAgICAgICAgICB0b3BWYWwgPSAkYW5jaG9yRGltcy5vZmZzZXQudG9wIC0gdk9mZnNldCArICRhbmNob3JEaW1zLmhlaWdodCAtICRlbGVEaW1zLmhlaWdodDtcbiAgICAgICAgICBicmVhaztcbiAgICAgICAgY2FzZSAndG9wJzpcbiAgICAgICAgICB0b3BWYWwgPSAkYW5jaG9yRGltcy5vZmZzZXQudG9wICsgdk9mZnNldFxuICAgICAgICAgIGJyZWFrO1xuICAgICAgICBjYXNlICdjZW50ZXInOlxuICAgICAgICAgIHRvcFZhbCA9ICgkYW5jaG9yRGltcy5vZmZzZXQudG9wICsgdk9mZnNldCArICgkYW5jaG9yRGltcy5oZWlnaHQgLyAyKSkgLSAoJGVsZURpbXMuaGVpZ2h0IC8gMilcbiAgICAgICAgICBicmVhaztcbiAgICAgIH1cbiAgICAgIGJyZWFrO1xuICB9XG4gIHJldHVybiB7dG9wOiB0b3BWYWwsIGxlZnQ6IGxlZnRWYWx9O1xufVxuXG5leHBvcnQge0JveH07XG5cblxuXG4vLyBXRUJQQUNLIEZPT1RFUiAvL1xuLy8gLi9ub2RlX21vZHVsZXMvZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLnV0aWwuYm94LmpzIiwiJ3VzZSBzdHJpY3QnO1xuXG5pbXBvcnQgJCBmcm9tICdqcXVlcnknO1xuXG4vKipcbiAqIFJ1bnMgYSBjYWxsYmFjayBmdW5jdGlvbiB3aGVuIGltYWdlcyBhcmUgZnVsbHkgbG9hZGVkLlxuICogQHBhcmFtIHtPYmplY3R9IGltYWdlcyAtIEltYWdlKHMpIHRvIGNoZWNrIGlmIGxvYWRlZC5cbiAqIEBwYXJhbSB7RnVuY30gY2FsbGJhY2sgLSBGdW5jdGlvbiB0byBleGVjdXRlIHdoZW4gaW1hZ2UgaXMgZnVsbHkgbG9hZGVkLlxuICovXG5mdW5jdGlvbiBvbkltYWdlc0xvYWRlZChpbWFnZXMsIGNhbGxiYWNrKXtcbiAgdmFyIHNlbGYgPSB0aGlzLFxuICAgICAgdW5sb2FkZWQgPSBpbWFnZXMubGVuZ3RoO1xuXG4gIGlmICh1bmxvYWRlZCA9PT0gMCkge1xuICAgIGNhbGxiYWNrKCk7XG4gIH1cblxuICBpbWFnZXMuZWFjaChmdW5jdGlvbigpe1xuICAgIC8vIENoZWNrIGlmIGltYWdlIGlzIGxvYWRlZFxuICAgIGlmICh0aGlzLmNvbXBsZXRlICYmIHR5cGVvZiB0aGlzLm5hdHVyYWxXaWR0aCAhPT0gJ3VuZGVmaW5lZCcpIHtcbiAgICAgIHNpbmdsZUltYWdlTG9hZGVkKCk7XG4gICAgfVxuICAgIGVsc2Uge1xuICAgICAgLy8gSWYgdGhlIGFib3ZlIGNoZWNrIGZhaWxlZCwgc2ltdWxhdGUgbG9hZGluZyBvbiBkZXRhY2hlZCBlbGVtZW50LlxuICAgICAgdmFyIGltYWdlID0gbmV3IEltYWdlKCk7XG4gICAgICAvLyBTdGlsbCBjb3VudCBpbWFnZSBhcyBsb2FkZWQgaWYgaXQgZmluYWxpemVzIHdpdGggYW4gZXJyb3IuXG4gICAgICB2YXIgZXZlbnRzID0gXCJsb2FkLnpmLmltYWdlcyBlcnJvci56Zi5pbWFnZXNcIjtcbiAgICAgICQoaW1hZ2UpLm9uZShldmVudHMsIGZ1bmN0aW9uIG1lKGV2ZW50KXtcbiAgICAgICAgLy8gVW5iaW5kIHRoZSBldmVudCBsaXN0ZW5lcnMuIFdlJ3JlIHVzaW5nICdvbmUnIGJ1dCBvbmx5IG9uZSBvZiB0aGUgdHdvIGV2ZW50cyB3aWxsIGhhdmUgZmlyZWQuXG4gICAgICAgICQodGhpcykub2ZmKGV2ZW50cywgbWUpO1xuICAgICAgICBzaW5nbGVJbWFnZUxvYWRlZCgpO1xuICAgICAgfSk7XG4gICAgICBpbWFnZS5zcmMgPSAkKHRoaXMpLmF0dHIoJ3NyYycpO1xuICAgIH1cbiAgfSk7XG5cbiAgZnVuY3Rpb24gc2luZ2xlSW1hZ2VMb2FkZWQoKSB7XG4gICAgdW5sb2FkZWQtLTtcbiAgICBpZiAodW5sb2FkZWQgPT09IDApIHtcbiAgICAgIGNhbGxiYWNrKCk7XG4gICAgfVxuICB9XG59XG5cbmV4cG9ydCB7IG9uSW1hZ2VzTG9hZGVkIH07XG5cblxuXG4vLyBXRUJQQUNLIEZPT1RFUiAvL1xuLy8gLi9ub2RlX21vZHVsZXMvZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLnV0aWwuaW1hZ2VMb2FkZXIuanMiLCIvKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKlxuICogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICpcbiAqIFRoaXMgdXRpbCB3YXMgY3JlYXRlZCBieSBNYXJpdXMgT2xiZXJ0eiAqXG4gKiBQbGVhc2UgdGhhbmsgTWFyaXVzIG9uIEdpdEh1YiAvb3dsYmVydHogKlxuICogb3IgdGhlIHdlYiBodHRwOi8vd3d3Lm1hcml1c29sYmVydHouZGUvICpcbiAqICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAqXG4gKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqL1xuXG4ndXNlIHN0cmljdCc7XG5cbmltcG9ydCAkIGZyb20gJ2pxdWVyeSc7XG5pbXBvcnQgeyBydGwgYXMgUnRsIH0gZnJvbSAnLi9mb3VuZGF0aW9uLmNvcmUudXRpbHMnO1xuXG5jb25zdCBrZXlDb2RlcyA9IHtcbiAgOTogJ1RBQicsXG4gIDEzOiAnRU5URVInLFxuICAyNzogJ0VTQ0FQRScsXG4gIDMyOiAnU1BBQ0UnLFxuICAzNTogJ0VORCcsXG4gIDM2OiAnSE9NRScsXG4gIDM3OiAnQVJST1dfTEVGVCcsXG4gIDM4OiAnQVJST1dfVVAnLFxuICAzOTogJ0FSUk9XX1JJR0hUJyxcbiAgNDA6ICdBUlJPV19ET1dOJ1xufVxuXG52YXIgY29tbWFuZHMgPSB7fVxuXG4vLyBGdW5jdGlvbnMgcHVsbGVkIG91dCB0byBiZSByZWZlcmVuY2VhYmxlIGZyb20gaW50ZXJuYWxzXG5mdW5jdGlvbiBmaW5kRm9jdXNhYmxlKCRlbGVtZW50KSB7XG4gIGlmKCEkZWxlbWVudCkge3JldHVybiBmYWxzZTsgfVxuICByZXR1cm4gJGVsZW1lbnQuZmluZCgnYVtocmVmXSwgYXJlYVtocmVmXSwgaW5wdXQ6bm90KFtkaXNhYmxlZF0pLCBzZWxlY3Q6bm90KFtkaXNhYmxlZF0pLCB0ZXh0YXJlYTpub3QoW2Rpc2FibGVkXSksIGJ1dHRvbjpub3QoW2Rpc2FibGVkXSksIGlmcmFtZSwgb2JqZWN0LCBlbWJlZCwgKlt0YWJpbmRleF0sICpbY29udGVudGVkaXRhYmxlXScpLmZpbHRlcihmdW5jdGlvbigpIHtcbiAgICBpZiAoISQodGhpcykuaXMoJzp2aXNpYmxlJykgfHwgJCh0aGlzKS5hdHRyKCd0YWJpbmRleCcpIDwgMCkgeyByZXR1cm4gZmFsc2U7IH0gLy9vbmx5IGhhdmUgdmlzaWJsZSBlbGVtZW50cyBhbmQgdGhvc2UgdGhhdCBoYXZlIGEgdGFiaW5kZXggZ3JlYXRlciBvciBlcXVhbCAwXG4gICAgcmV0dXJuIHRydWU7XG4gIH0pO1xufVxuXG5mdW5jdGlvbiBwYXJzZUtleShldmVudCkge1xuICB2YXIga2V5ID0ga2V5Q29kZXNbZXZlbnQud2hpY2ggfHwgZXZlbnQua2V5Q29kZV0gfHwgU3RyaW5nLmZyb21DaGFyQ29kZShldmVudC53aGljaCkudG9VcHBlckNhc2UoKTtcblxuICAvLyBSZW1vdmUgdW4tcHJpbnRhYmxlIGNoYXJhY3RlcnMsIGUuZy4gZm9yIGBmcm9tQ2hhckNvZGVgIGNhbGxzIGZvciBDVFJMIG9ubHkgZXZlbnRzXG4gIGtleSA9IGtleS5yZXBsYWNlKC9cXFcrLywgJycpO1xuXG4gIGlmIChldmVudC5zaGlmdEtleSkga2V5ID0gYFNISUZUXyR7a2V5fWA7XG4gIGlmIChldmVudC5jdHJsS2V5KSBrZXkgPSBgQ1RSTF8ke2tleX1gO1xuICBpZiAoZXZlbnQuYWx0S2V5KSBrZXkgPSBgQUxUXyR7a2V5fWA7XG5cbiAgLy8gUmVtb3ZlIHRyYWlsaW5nIHVuZGVyc2NvcmUsIGluIGNhc2Ugb25seSBtb2RpZmllcnMgd2VyZSB1c2VkIChlLmcuIG9ubHkgYENUUkxfQUxUYClcbiAga2V5ID0ga2V5LnJlcGxhY2UoL18kLywgJycpO1xuXG4gIHJldHVybiBrZXk7XG59XG5cbnZhciBLZXlib2FyZCA9IHtcbiAga2V5czogZ2V0S2V5Q29kZXMoa2V5Q29kZXMpLFxuXG4gIC8qKlxuICAgKiBQYXJzZXMgdGhlIChrZXlib2FyZCkgZXZlbnQgYW5kIHJldHVybnMgYSBTdHJpbmcgdGhhdCByZXByZXNlbnRzIGl0cyBrZXlcbiAgICogQ2FuIGJlIHVzZWQgbGlrZSBGb3VuZGF0aW9uLnBhcnNlS2V5KGV2ZW50KSA9PT0gRm91bmRhdGlvbi5rZXlzLlNQQUNFXG4gICAqIEBwYXJhbSB7RXZlbnR9IGV2ZW50IC0gdGhlIGV2ZW50IGdlbmVyYXRlZCBieSB0aGUgZXZlbnQgaGFuZGxlclxuICAgKiBAcmV0dXJuIFN0cmluZyBrZXkgLSBTdHJpbmcgdGhhdCByZXByZXNlbnRzIHRoZSBrZXkgcHJlc3NlZFxuICAgKi9cbiAgcGFyc2VLZXk6IHBhcnNlS2V5LFxuXG4gIC8qKlxuICAgKiBIYW5kbGVzIHRoZSBnaXZlbiAoa2V5Ym9hcmQpIGV2ZW50XG4gICAqIEBwYXJhbSB7RXZlbnR9IGV2ZW50IC0gdGhlIGV2ZW50IGdlbmVyYXRlZCBieSB0aGUgZXZlbnQgaGFuZGxlclxuICAgKiBAcGFyYW0ge1N0cmluZ30gY29tcG9uZW50IC0gRm91bmRhdGlvbiBjb21wb25lbnQncyBuYW1lLCBlLmcuIFNsaWRlciBvciBSZXZlYWxcbiAgICogQHBhcmFtIHtPYmplY3RzfSBmdW5jdGlvbnMgLSBjb2xsZWN0aW9uIG9mIGZ1bmN0aW9ucyB0aGF0IGFyZSB0byBiZSBleGVjdXRlZFxuICAgKi9cbiAgaGFuZGxlS2V5KGV2ZW50LCBjb21wb25lbnQsIGZ1bmN0aW9ucykge1xuICAgIHZhciBjb21tYW5kTGlzdCA9IGNvbW1hbmRzW2NvbXBvbmVudF0sXG4gICAgICBrZXlDb2RlID0gdGhpcy5wYXJzZUtleShldmVudCksXG4gICAgICBjbWRzLFxuICAgICAgY29tbWFuZCxcbiAgICAgIGZuO1xuXG4gICAgaWYgKCFjb21tYW5kTGlzdCkgcmV0dXJuIGNvbnNvbGUud2FybignQ29tcG9uZW50IG5vdCBkZWZpbmVkIScpO1xuXG4gICAgaWYgKHR5cGVvZiBjb21tYW5kTGlzdC5sdHIgPT09ICd1bmRlZmluZWQnKSB7IC8vIHRoaXMgY29tcG9uZW50IGRvZXMgbm90IGRpZmZlcmVudGlhdGUgYmV0d2VlbiBsdHIgYW5kIHJ0bFxuICAgICAgICBjbWRzID0gY29tbWFuZExpc3Q7IC8vIHVzZSBwbGFpbiBsaXN0XG4gICAgfSBlbHNlIHsgLy8gbWVyZ2UgbHRyIGFuZCBydGw6IGlmIGRvY3VtZW50IGlzIHJ0bCwgcnRsIG92ZXJ3cml0ZXMgbHRyIGFuZCB2aWNlIHZlcnNhXG4gICAgICAgIGlmIChSdGwoKSkgY21kcyA9ICQuZXh0ZW5kKHt9LCBjb21tYW5kTGlzdC5sdHIsIGNvbW1hbmRMaXN0LnJ0bCk7XG5cbiAgICAgICAgZWxzZSBjbWRzID0gJC5leHRlbmQoe30sIGNvbW1hbmRMaXN0LnJ0bCwgY29tbWFuZExpc3QubHRyKTtcbiAgICB9XG4gICAgY29tbWFuZCA9IGNtZHNba2V5Q29kZV07XG5cbiAgICBmbiA9IGZ1bmN0aW9uc1tjb21tYW5kXTtcbiAgICBpZiAoZm4gJiYgdHlwZW9mIGZuID09PSAnZnVuY3Rpb24nKSB7IC8vIGV4ZWN1dGUgZnVuY3Rpb24gIGlmIGV4aXN0c1xuICAgICAgdmFyIHJldHVyblZhbHVlID0gZm4uYXBwbHkoKTtcbiAgICAgIGlmIChmdW5jdGlvbnMuaGFuZGxlZCB8fCB0eXBlb2YgZnVuY3Rpb25zLmhhbmRsZWQgPT09ICdmdW5jdGlvbicpIHsgLy8gZXhlY3V0ZSBmdW5jdGlvbiB3aGVuIGV2ZW50IHdhcyBoYW5kbGVkXG4gICAgICAgICAgZnVuY3Rpb25zLmhhbmRsZWQocmV0dXJuVmFsdWUpO1xuICAgICAgfVxuICAgIH0gZWxzZSB7XG4gICAgICBpZiAoZnVuY3Rpb25zLnVuaGFuZGxlZCB8fCB0eXBlb2YgZnVuY3Rpb25zLnVuaGFuZGxlZCA9PT0gJ2Z1bmN0aW9uJykgeyAvLyBleGVjdXRlIGZ1bmN0aW9uIHdoZW4gZXZlbnQgd2FzIG5vdCBoYW5kbGVkXG4gICAgICAgICAgZnVuY3Rpb25zLnVuaGFuZGxlZCgpO1xuICAgICAgfVxuICAgIH1cbiAgfSxcblxuICAvKipcbiAgICogRmluZHMgYWxsIGZvY3VzYWJsZSBlbGVtZW50cyB3aXRoaW4gdGhlIGdpdmVuIGAkZWxlbWVudGBcbiAgICogQHBhcmFtIHtqUXVlcnl9ICRlbGVtZW50IC0galF1ZXJ5IG9iamVjdCB0byBzZWFyY2ggd2l0aGluXG4gICAqIEByZXR1cm4ge2pRdWVyeX0gJGZvY3VzYWJsZSAtIGFsbCBmb2N1c2FibGUgZWxlbWVudHMgd2l0aGluIGAkZWxlbWVudGBcbiAgICovXG5cbiAgZmluZEZvY3VzYWJsZTogZmluZEZvY3VzYWJsZSxcblxuICAvKipcbiAgICogUmV0dXJucyB0aGUgY29tcG9uZW50IG5hbWUgbmFtZVxuICAgKiBAcGFyYW0ge09iamVjdH0gY29tcG9uZW50IC0gRm91bmRhdGlvbiBjb21wb25lbnQsIGUuZy4gU2xpZGVyIG9yIFJldmVhbFxuICAgKiBAcmV0dXJuIFN0cmluZyBjb21wb25lbnROYW1lXG4gICAqL1xuXG4gIHJlZ2lzdGVyKGNvbXBvbmVudE5hbWUsIGNtZHMpIHtcbiAgICBjb21tYW5kc1tjb21wb25lbnROYW1lXSA9IGNtZHM7XG4gIH0sXG5cblxuICAvLyBUT0RPOTQzODogVGhlc2UgcmVmZXJlbmNlcyB0byBLZXlib2FyZCBuZWVkIHRvIG5vdCByZXF1aXJlIGdsb2JhbC4gV2lsbCAndGhpcycgd29yayBpbiB0aGlzIGNvbnRleHQ/XG4gIC8vXG4gIC8qKlxuICAgKiBUcmFwcyB0aGUgZm9jdXMgaW4gdGhlIGdpdmVuIGVsZW1lbnQuXG4gICAqIEBwYXJhbSAge2pRdWVyeX0gJGVsZW1lbnQgIGpRdWVyeSBvYmplY3QgdG8gdHJhcCB0aGUgZm91Y3MgaW50by5cbiAgICovXG4gIHRyYXBGb2N1cygkZWxlbWVudCkge1xuICAgIHZhciAkZm9jdXNhYmxlID0gZmluZEZvY3VzYWJsZSgkZWxlbWVudCksXG4gICAgICAgICRmaXJzdEZvY3VzYWJsZSA9ICRmb2N1c2FibGUuZXEoMCksXG4gICAgICAgICRsYXN0Rm9jdXNhYmxlID0gJGZvY3VzYWJsZS5lcSgtMSk7XG5cbiAgICAkZWxlbWVudC5vbigna2V5ZG93bi56Zi50cmFwZm9jdXMnLCBmdW5jdGlvbihldmVudCkge1xuICAgICAgaWYgKGV2ZW50LnRhcmdldCA9PT0gJGxhc3RGb2N1c2FibGVbMF0gJiYgcGFyc2VLZXkoZXZlbnQpID09PSAnVEFCJykge1xuICAgICAgICBldmVudC5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICAkZmlyc3RGb2N1c2FibGUuZm9jdXMoKTtcbiAgICAgIH1cbiAgICAgIGVsc2UgaWYgKGV2ZW50LnRhcmdldCA9PT0gJGZpcnN0Rm9jdXNhYmxlWzBdICYmIHBhcnNlS2V5KGV2ZW50KSA9PT0gJ1NISUZUX1RBQicpIHtcbiAgICAgICAgZXZlbnQucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgJGxhc3RGb2N1c2FibGUuZm9jdXMoKTtcbiAgICAgIH1cbiAgICB9KTtcbiAgfSxcbiAgLyoqXG4gICAqIFJlbGVhc2VzIHRoZSB0cmFwcGVkIGZvY3VzIGZyb20gdGhlIGdpdmVuIGVsZW1lbnQuXG4gICAqIEBwYXJhbSAge2pRdWVyeX0gJGVsZW1lbnQgIGpRdWVyeSBvYmplY3QgdG8gcmVsZWFzZSB0aGUgZm9jdXMgZm9yLlxuICAgKi9cbiAgcmVsZWFzZUZvY3VzKCRlbGVtZW50KSB7XG4gICAgJGVsZW1lbnQub2ZmKCdrZXlkb3duLnpmLnRyYXBmb2N1cycpO1xuICB9XG59XG5cbi8qXG4gKiBDb25zdGFudHMgZm9yIGVhc2llciBjb21wYXJpbmcuXG4gKiBDYW4gYmUgdXNlZCBsaWtlIEZvdW5kYXRpb24ucGFyc2VLZXkoZXZlbnQpID09PSBGb3VuZGF0aW9uLmtleXMuU1BBQ0VcbiAqL1xuZnVuY3Rpb24gZ2V0S2V5Q29kZXMoa2NzKSB7XG4gIHZhciBrID0ge307XG4gIGZvciAodmFyIGtjIGluIGtjcykga1trY3Nba2NdXSA9IGtjc1trY107XG4gIHJldHVybiBrO1xufVxuXG5leHBvcnQge0tleWJvYXJkfTtcblxuXG5cbi8vIFdFQlBBQ0sgRk9PVEVSIC8vXG4vLyAuL25vZGVfbW9kdWxlcy9mb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24udXRpbC5rZXlib2FyZC5qcyIsIid1c2Ugc3RyaWN0JztcblxuaW1wb3J0ICQgZnJvbSAnanF1ZXJ5JztcblxuY29uc3QgTmVzdCA9IHtcbiAgRmVhdGhlcihtZW51LCB0eXBlID0gJ3pmJykge1xuICAgIG1lbnUuYXR0cigncm9sZScsICdtZW51YmFyJyk7XG5cbiAgICB2YXIgaXRlbXMgPSBtZW51LmZpbmQoJ2xpJykuYXR0cih7J3JvbGUnOiAnbWVudWl0ZW0nfSksXG4gICAgICAgIHN1Yk1lbnVDbGFzcyA9IGBpcy0ke3R5cGV9LXN1Ym1lbnVgLFxuICAgICAgICBzdWJJdGVtQ2xhc3MgPSBgJHtzdWJNZW51Q2xhc3N9LWl0ZW1gLFxuICAgICAgICBoYXNTdWJDbGFzcyA9IGBpcy0ke3R5cGV9LXN1Ym1lbnUtcGFyZW50YCxcbiAgICAgICAgYXBwbHlBcmlhID0gKHR5cGUgIT09ICdhY2NvcmRpb24nKTsgLy8gQWNjb3JkaW9ucyBoYW5kbGUgdGhlaXIgb3duIEFSSUEgYXR0cml1dGVzLlxuXG4gICAgaXRlbXMuZWFjaChmdW5jdGlvbigpIHtcbiAgICAgIHZhciAkaXRlbSA9ICQodGhpcyksXG4gICAgICAgICAgJHN1YiA9ICRpdGVtLmNoaWxkcmVuKCd1bCcpO1xuXG4gICAgICBpZiAoJHN1Yi5sZW5ndGgpIHtcbiAgICAgICAgJGl0ZW0uYWRkQ2xhc3MoaGFzU3ViQ2xhc3MpO1xuICAgICAgICAkc3ViLmFkZENsYXNzKGBzdWJtZW51ICR7c3ViTWVudUNsYXNzfWApLmF0dHIoeydkYXRhLXN1Ym1lbnUnOiAnJ30pO1xuICAgICAgICBpZihhcHBseUFyaWEpIHtcbiAgICAgICAgICAkaXRlbS5hdHRyKHtcbiAgICAgICAgICAgICdhcmlhLWhhc3BvcHVwJzogdHJ1ZSxcbiAgICAgICAgICAgICdhcmlhLWxhYmVsJzogJGl0ZW0uY2hpbGRyZW4oJ2E6Zmlyc3QnKS50ZXh0KClcbiAgICAgICAgICB9KTtcbiAgICAgICAgICAvLyBOb3RlOiAgRHJpbGxkb3ducyBiZWhhdmUgZGlmZmVyZW50bHkgaW4gaG93IHRoZXkgaGlkZSwgYW5kIHNvIG5lZWRcbiAgICAgICAgICAvLyBhZGRpdGlvbmFsIGF0dHJpYnV0ZXMuICBXZSBzaG91bGQgbG9vayBpZiB0aGlzIHBvc3NpYmx5IG92ZXItZ2VuZXJhbGl6ZWRcbiAgICAgICAgICAvLyB1dGlsaXR5IChOZXN0KSBpcyBhcHByb3ByaWF0ZSB3aGVuIHdlIHJld29yayBtZW51cyBpbiA2LjRcbiAgICAgICAgICBpZih0eXBlID09PSAnZHJpbGxkb3duJykge1xuICAgICAgICAgICAgJGl0ZW0uYXR0cih7J2FyaWEtZXhwYW5kZWQnOiBmYWxzZX0pO1xuICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICAgICAkc3ViXG4gICAgICAgICAgLmFkZENsYXNzKGBzdWJtZW51ICR7c3ViTWVudUNsYXNzfWApXG4gICAgICAgICAgLmF0dHIoe1xuICAgICAgICAgICAgJ2RhdGEtc3VibWVudSc6ICcnLFxuICAgICAgICAgICAgJ3JvbGUnOiAnbWVudWJhcidcbiAgICAgICAgICB9KTtcbiAgICAgICAgaWYodHlwZSA9PT0gJ2RyaWxsZG93bicpIHtcbiAgICAgICAgICAkc3ViLmF0dHIoeydhcmlhLWhpZGRlbic6IHRydWV9KTtcbiAgICAgICAgfVxuICAgICAgfVxuXG4gICAgICBpZiAoJGl0ZW0ucGFyZW50KCdbZGF0YS1zdWJtZW51XScpLmxlbmd0aCkge1xuICAgICAgICAkaXRlbS5hZGRDbGFzcyhgaXMtc3VibWVudS1pdGVtICR7c3ViSXRlbUNsYXNzfWApO1xuICAgICAgfVxuICAgIH0pO1xuXG4gICAgcmV0dXJuO1xuICB9LFxuXG4gIEJ1cm4obWVudSwgdHlwZSkge1xuICAgIHZhciAvL2l0ZW1zID0gbWVudS5maW5kKCdsaScpLFxuICAgICAgICBzdWJNZW51Q2xhc3MgPSBgaXMtJHt0eXBlfS1zdWJtZW51YCxcbiAgICAgICAgc3ViSXRlbUNsYXNzID0gYCR7c3ViTWVudUNsYXNzfS1pdGVtYCxcbiAgICAgICAgaGFzU3ViQ2xhc3MgPSBgaXMtJHt0eXBlfS1zdWJtZW51LXBhcmVudGA7XG5cbiAgICBtZW51XG4gICAgICAuZmluZCgnPmxpLCA+IGxpID4gdWwsIC5tZW51LCAubWVudSA+IGxpLCBbZGF0YS1zdWJtZW51XSA+IGxpJylcbiAgICAgIC5yZW1vdmVDbGFzcyhgJHtzdWJNZW51Q2xhc3N9ICR7c3ViSXRlbUNsYXNzfSAke2hhc1N1YkNsYXNzfSBpcy1zdWJtZW51LWl0ZW0gc3VibWVudSBpcy1hY3RpdmVgKVxuICAgICAgLnJlbW92ZUF0dHIoJ2RhdGEtc3VibWVudScpLmNzcygnZGlzcGxheScsICcnKTtcblxuICB9XG59XG5cbmV4cG9ydCB7TmVzdH07XG5cblxuXG4vLyBXRUJQQUNLIEZPT1RFUiAvL1xuLy8gLi9ub2RlX21vZHVsZXMvZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLnV0aWwubmVzdC5qcyIsIid1c2Ugc3RyaWN0JztcblxuaW1wb3J0ICQgZnJvbSAnanF1ZXJ5JztcblxuZnVuY3Rpb24gVGltZXIoZWxlbSwgb3B0aW9ucywgY2IpIHtcbiAgdmFyIF90aGlzID0gdGhpcyxcbiAgICAgIGR1cmF0aW9uID0gb3B0aW9ucy5kdXJhdGlvbiwvL29wdGlvbnMgaXMgYW4gb2JqZWN0IGZvciBlYXNpbHkgYWRkaW5nIGZlYXR1cmVzIGxhdGVyLlxuICAgICAgbmFtZVNwYWNlID0gT2JqZWN0LmtleXMoZWxlbS5kYXRhKCkpWzBdIHx8ICd0aW1lcicsXG4gICAgICByZW1haW4gPSAtMSxcbiAgICAgIHN0YXJ0LFxuICAgICAgdGltZXI7XG5cbiAgdGhpcy5pc1BhdXNlZCA9IGZhbHNlO1xuXG4gIHRoaXMucmVzdGFydCA9IGZ1bmN0aW9uKCkge1xuICAgIHJlbWFpbiA9IC0xO1xuICAgIGNsZWFyVGltZW91dCh0aW1lcik7XG4gICAgdGhpcy5zdGFydCgpO1xuICB9XG5cbiAgdGhpcy5zdGFydCA9IGZ1bmN0aW9uKCkge1xuICAgIHRoaXMuaXNQYXVzZWQgPSBmYWxzZTtcbiAgICAvLyBpZighZWxlbS5kYXRhKCdwYXVzZWQnKSl7IHJldHVybiBmYWxzZTsgfS8vbWF5YmUgaW1wbGVtZW50IHRoaXMgc2FuaXR5IGNoZWNrIGlmIHVzZWQgZm9yIG90aGVyIHRoaW5ncy5cbiAgICBjbGVhclRpbWVvdXQodGltZXIpO1xuICAgIHJlbWFpbiA9IHJlbWFpbiA8PSAwID8gZHVyYXRpb24gOiByZW1haW47XG4gICAgZWxlbS5kYXRhKCdwYXVzZWQnLCBmYWxzZSk7XG4gICAgc3RhcnQgPSBEYXRlLm5vdygpO1xuICAgIHRpbWVyID0gc2V0VGltZW91dChmdW5jdGlvbigpe1xuICAgICAgaWYob3B0aW9ucy5pbmZpbml0ZSl7XG4gICAgICAgIF90aGlzLnJlc3RhcnQoKTsvL3JlcnVuIHRoZSB0aW1lci5cbiAgICAgIH1cbiAgICAgIGlmIChjYiAmJiB0eXBlb2YgY2IgPT09ICdmdW5jdGlvbicpIHsgY2IoKTsgfVxuICAgIH0sIHJlbWFpbik7XG4gICAgZWxlbS50cmlnZ2VyKGB0aW1lcnN0YXJ0LnpmLiR7bmFtZVNwYWNlfWApO1xuICB9XG5cbiAgdGhpcy5wYXVzZSA9IGZ1bmN0aW9uKCkge1xuICAgIHRoaXMuaXNQYXVzZWQgPSB0cnVlO1xuICAgIC8vaWYoZWxlbS5kYXRhKCdwYXVzZWQnKSl7IHJldHVybiBmYWxzZTsgfS8vbWF5YmUgaW1wbGVtZW50IHRoaXMgc2FuaXR5IGNoZWNrIGlmIHVzZWQgZm9yIG90aGVyIHRoaW5ncy5cbiAgICBjbGVhclRpbWVvdXQodGltZXIpO1xuICAgIGVsZW0uZGF0YSgncGF1c2VkJywgdHJ1ZSk7XG4gICAgdmFyIGVuZCA9IERhdGUubm93KCk7XG4gICAgcmVtYWluID0gcmVtYWluIC0gKGVuZCAtIHN0YXJ0KTtcbiAgICBlbGVtLnRyaWdnZXIoYHRpbWVycGF1c2VkLnpmLiR7bmFtZVNwYWNlfWApO1xuICB9XG59XG5cbmV4cG9ydCB7VGltZXJ9O1xuXG5cblxuLy8gV0VCUEFDSyBGT09URVIgLy9cbi8vIC4vbm9kZV9tb2R1bGVzL2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi51dGlsLnRpbWVyLmpzIiwiLy8qKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKlxuLy8qKldvcmsgaW5zcGlyZWQgYnkgbXVsdGlwbGUganF1ZXJ5IHN3aXBlIHBsdWdpbnMqKlxuLy8qKkRvbmUgYnkgWW9oYWkgQXJhcmF0ICoqKioqKioqKioqKioqKioqKioqKioqKioqKlxuLy8qKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKlxuXG5pbXBvcnQgJCBmcm9tICdqcXVlcnknO1xuXG52YXIgVG91Y2ggPSB7fTtcblxudmFyIHN0YXJ0UG9zWCxcbiAgICBzdGFydFBvc1ksXG4gICAgc3RhcnRUaW1lLFxuICAgIGVsYXBzZWRUaW1lLFxuICAgIHN0YXJ0RXZlbnQsXG4gICAgaXNNb3ZpbmcgPSBmYWxzZSxcbiAgICBkaWRNb3ZlZCA9IGZhbHNlO1xuXG5mdW5jdGlvbiBvblRvdWNoRW5kKGUpIHtcbiAgdGhpcy5yZW1vdmVFdmVudExpc3RlbmVyKCd0b3VjaG1vdmUnLCBvblRvdWNoTW92ZSk7XG4gIHRoaXMucmVtb3ZlRXZlbnRMaXN0ZW5lcigndG91Y2hlbmQnLCBvblRvdWNoRW5kKTtcblxuICAvLyBJZiB0aGUgdG91Y2ggZGlkIG5vdCBtb3ZlLCBjb25zaWRlciBpdCBhcyBhIFwidGFwXCJcbiAgaWYgKCFkaWRNb3ZlZCkge1xuICAgIHZhciB0YXBFdmVudCA9ICQuRXZlbnQoJ3RhcCcsIHN0YXJ0RXZlbnQgfHwgZSk7XG4gICAgJCh0aGlzKS50cmlnZ2VyKHRhcEV2ZW50KTtcbiAgfVxuXG4gIHN0YXJ0RXZlbnQgPSBudWxsO1xuICBpc01vdmluZyA9IGZhbHNlO1xuICBkaWRNb3ZlZCA9IGZhbHNlO1xufVxuXG5mdW5jdGlvbiBvblRvdWNoTW92ZShlKSB7XG4gIGlmICgkLnNwb3RTd2lwZS5wcmV2ZW50RGVmYXVsdCkgeyBlLnByZXZlbnREZWZhdWx0KCk7IH1cblxuICBpZihpc01vdmluZykge1xuICAgIHZhciB4ID0gZS50b3VjaGVzWzBdLnBhZ2VYO1xuICAgIHZhciB5ID0gZS50b3VjaGVzWzBdLnBhZ2VZO1xuICAgIHZhciBkeCA9IHN0YXJ0UG9zWCAtIHg7XG4gICAgdmFyIGR5ID0gc3RhcnRQb3NZIC0geTtcbiAgICB2YXIgZGlyO1xuICAgIGRpZE1vdmVkID0gdHJ1ZTtcbiAgICBlbGFwc2VkVGltZSA9IG5ldyBEYXRlKCkuZ2V0VGltZSgpIC0gc3RhcnRUaW1lO1xuICAgIGlmKE1hdGguYWJzKGR4KSA+PSAkLnNwb3RTd2lwZS5tb3ZlVGhyZXNob2xkICYmIGVsYXBzZWRUaW1lIDw9ICQuc3BvdFN3aXBlLnRpbWVUaHJlc2hvbGQpIHtcbiAgICAgIGRpciA9IGR4ID4gMCA/ICdsZWZ0JyA6ICdyaWdodCc7XG4gICAgfVxuICAgIC8vIGVsc2UgaWYoTWF0aC5hYnMoZHkpID49ICQuc3BvdFN3aXBlLm1vdmVUaHJlc2hvbGQgJiYgZWxhcHNlZFRpbWUgPD0gJC5zcG90U3dpcGUudGltZVRocmVzaG9sZCkge1xuICAgIC8vICAgZGlyID0gZHkgPiAwID8gJ2Rvd24nIDogJ3VwJztcbiAgICAvLyB9XG4gICAgaWYoZGlyKSB7XG4gICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG4gICAgICBvblRvdWNoRW5kLmFwcGx5KHRoaXMsIGFyZ3VtZW50cyk7XG4gICAgICAkKHRoaXMpXG4gICAgICAgIC50cmlnZ2VyKCQuRXZlbnQoJ3N3aXBlJywgZSksIGRpcilcbiAgICAgICAgLnRyaWdnZXIoJC5FdmVudChgc3dpcGUke2Rpcn1gLCBlKSk7XG4gICAgfVxuICB9XG5cbn1cblxuZnVuY3Rpb24gb25Ub3VjaFN0YXJ0KGUpIHtcblxuICBpZiAoZS50b3VjaGVzLmxlbmd0aCA9PSAxKSB7XG4gICAgc3RhcnRQb3NYID0gZS50b3VjaGVzWzBdLnBhZ2VYO1xuICAgIHN0YXJ0UG9zWSA9IGUudG91Y2hlc1swXS5wYWdlWTtcbiAgICBzdGFydEV2ZW50ID0gZTtcbiAgICBpc01vdmluZyA9IHRydWU7XG4gICAgZGlkTW92ZWQgPSBmYWxzZTtcbiAgICBzdGFydFRpbWUgPSBuZXcgRGF0ZSgpLmdldFRpbWUoKTtcbiAgICB0aGlzLmFkZEV2ZW50TGlzdGVuZXIoJ3RvdWNobW92ZScsIG9uVG91Y2hNb3ZlLCBmYWxzZSk7XG4gICAgdGhpcy5hZGRFdmVudExpc3RlbmVyKCd0b3VjaGVuZCcsIG9uVG91Y2hFbmQsIGZhbHNlKTtcbiAgfVxufVxuXG5mdW5jdGlvbiBpbml0KCkge1xuICB0aGlzLmFkZEV2ZW50TGlzdGVuZXIgJiYgdGhpcy5hZGRFdmVudExpc3RlbmVyKCd0b3VjaHN0YXJ0Jywgb25Ub3VjaFN0YXJ0LCBmYWxzZSk7XG59XG5cbmZ1bmN0aW9uIHRlYXJkb3duKCkge1xuICB0aGlzLnJlbW92ZUV2ZW50TGlzdGVuZXIoJ3RvdWNoc3RhcnQnLCBvblRvdWNoU3RhcnQpO1xufVxuXG5jbGFzcyBTcG90U3dpcGUge1xuICBjb25zdHJ1Y3RvcigkKSB7XG4gICAgdGhpcy52ZXJzaW9uID0gJzEuMC4wJztcbiAgICB0aGlzLmVuYWJsZWQgPSAnb250b3VjaHN0YXJ0JyBpbiBkb2N1bWVudC5kb2N1bWVudEVsZW1lbnQ7XG4gICAgdGhpcy5wcmV2ZW50RGVmYXVsdCA9IGZhbHNlO1xuICAgIHRoaXMubW92ZVRocmVzaG9sZCA9IDc1O1xuICAgIHRoaXMudGltZVRocmVzaG9sZCA9IDIwMDtcbiAgICB0aGlzLiQgPSAkO1xuICAgIHRoaXMuX2luaXQoKTtcbiAgfVxuXG4gIF9pbml0KCkge1xuICAgIHZhciAkID0gdGhpcy4kO1xuICAgICQuZXZlbnQuc3BlY2lhbC5zd2lwZSA9IHsgc2V0dXA6IGluaXQgfTtcbiAgICAkLmV2ZW50LnNwZWNpYWwudGFwID0geyBzZXR1cDogaW5pdCB9O1xuXG4gICAgJC5lYWNoKFsnbGVmdCcsICd1cCcsICdkb3duJywgJ3JpZ2h0J10sIGZ1bmN0aW9uICgpIHtcbiAgICAgICQuZXZlbnQuc3BlY2lhbFtgc3dpcGUke3RoaXN9YF0gPSB7IHNldHVwOiBmdW5jdGlvbigpe1xuICAgICAgICAkKHRoaXMpLm9uKCdzd2lwZScsICQubm9vcCk7XG4gICAgICB9IH07XG4gICAgfSk7XG4gIH1cbn1cblxuLyoqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKipcbiAqIEFzIGZhciBhcyBJIGNhbiB0ZWxsLCBib3RoIHNldHVwU3BvdFN3aXBlIGFuZCAgICAqXG4gKiBzZXR1cFRvdWNoSGFuZGxlciBzaG91bGQgYmUgaWRlbXBvdGVudCwgICAgICAgICAgKlxuICogYmVjYXVzZSB0aGV5IGRpcmVjdGx5IHJlcGxhY2UgZnVuY3Rpb25zICYgICAgICAgICpcbiAqIHZhbHVlcywgYW5kIGRvIG5vdCBhZGQgZXZlbnQgaGFuZGxlcnMgZGlyZWN0bHkuICAqXG4gKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKi9cblxuVG91Y2guc2V0dXBTcG90U3dpcGUgPSBmdW5jdGlvbigkKSB7XG4gICQuc3BvdFN3aXBlID0gbmV3IFNwb3RTd2lwZSgkKTtcbn07XG5cbi8qKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqXG4gKiBNZXRob2QgZm9yIGFkZGluZyBwc2V1ZG8gZHJhZyBldmVudHMgdG8gZWxlbWVudHMgKlxuICoqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKi9cblRvdWNoLnNldHVwVG91Y2hIYW5kbGVyID0gZnVuY3Rpb24oJCkge1xuICAkLmZuLmFkZFRvdWNoID0gZnVuY3Rpb24oKXtcbiAgICB0aGlzLmVhY2goZnVuY3Rpb24oaSxlbCl7XG4gICAgICAkKGVsKS5iaW5kKCd0b3VjaHN0YXJ0IHRvdWNobW92ZSB0b3VjaGVuZCB0b3VjaGNhbmNlbCcsIGZ1bmN0aW9uKGV2ZW50KSAge1xuICAgICAgICAvL3dlIHBhc3MgdGhlIG9yaWdpbmFsIGV2ZW50IG9iamVjdCBiZWNhdXNlIHRoZSBqUXVlcnkgZXZlbnRcbiAgICAgICAgLy9vYmplY3QgaXMgbm9ybWFsaXplZCB0byB3M2Mgc3BlY3MgYW5kIGRvZXMgbm90IHByb3ZpZGUgdGhlIFRvdWNoTGlzdFxuICAgICAgICBoYW5kbGVUb3VjaChldmVudCk7XG4gICAgICB9KTtcbiAgICB9KTtcblxuICAgIHZhciBoYW5kbGVUb3VjaCA9IGZ1bmN0aW9uKGV2ZW50KXtcbiAgICAgIHZhciB0b3VjaGVzID0gZXZlbnQuY2hhbmdlZFRvdWNoZXMsXG4gICAgICAgICAgZmlyc3QgPSB0b3VjaGVzWzBdLFxuICAgICAgICAgIGV2ZW50VHlwZXMgPSB7XG4gICAgICAgICAgICB0b3VjaHN0YXJ0OiAnbW91c2Vkb3duJyxcbiAgICAgICAgICAgIHRvdWNobW92ZTogJ21vdXNlbW92ZScsXG4gICAgICAgICAgICB0b3VjaGVuZDogJ21vdXNldXAnXG4gICAgICAgICAgfSxcbiAgICAgICAgICB0eXBlID0gZXZlbnRUeXBlc1tldmVudC50eXBlXSxcbiAgICAgICAgICBzaW11bGF0ZWRFdmVudFxuICAgICAgICA7XG5cbiAgICAgIGlmKCdNb3VzZUV2ZW50JyBpbiB3aW5kb3cgJiYgdHlwZW9mIHdpbmRvdy5Nb3VzZUV2ZW50ID09PSAnZnVuY3Rpb24nKSB7XG4gICAgICAgIHNpbXVsYXRlZEV2ZW50ID0gbmV3IHdpbmRvdy5Nb3VzZUV2ZW50KHR5cGUsIHtcbiAgICAgICAgICAnYnViYmxlcyc6IHRydWUsXG4gICAgICAgICAgJ2NhbmNlbGFibGUnOiB0cnVlLFxuICAgICAgICAgICdzY3JlZW5YJzogZmlyc3Quc2NyZWVuWCxcbiAgICAgICAgICAnc2NyZWVuWSc6IGZpcnN0LnNjcmVlblksXG4gICAgICAgICAgJ2NsaWVudFgnOiBmaXJzdC5jbGllbnRYLFxuICAgICAgICAgICdjbGllbnRZJzogZmlyc3QuY2xpZW50WVxuICAgICAgICB9KTtcbiAgICAgIH0gZWxzZSB7XG4gICAgICAgIHNpbXVsYXRlZEV2ZW50ID0gZG9jdW1lbnQuY3JlYXRlRXZlbnQoJ01vdXNlRXZlbnQnKTtcbiAgICAgICAgc2ltdWxhdGVkRXZlbnQuaW5pdE1vdXNlRXZlbnQodHlwZSwgdHJ1ZSwgdHJ1ZSwgd2luZG93LCAxLCBmaXJzdC5zY3JlZW5YLCBmaXJzdC5zY3JlZW5ZLCBmaXJzdC5jbGllbnRYLCBmaXJzdC5jbGllbnRZLCBmYWxzZSwgZmFsc2UsIGZhbHNlLCBmYWxzZSwgMC8qbGVmdCovLCBudWxsKTtcbiAgICAgIH1cbiAgICAgIGZpcnN0LnRhcmdldC5kaXNwYXRjaEV2ZW50KHNpbXVsYXRlZEV2ZW50KTtcbiAgICB9O1xuICB9O1xufTtcblxuVG91Y2guaW5pdCA9IGZ1bmN0aW9uICgkKSB7XG5cbiAgaWYodHlwZW9mKCQuc3BvdFN3aXBlKSA9PT0gJ3VuZGVmaW5lZCcpIHtcbiAgICBUb3VjaC5zZXR1cFNwb3RTd2lwZSgkKTtcbiAgICBUb3VjaC5zZXR1cFRvdWNoSGFuZGxlcigkKTtcbiAgfVxufTtcblxuZXhwb3J0IHtUb3VjaH07XG5cblxuXG4vLyBXRUJQQUNLIEZPT1RFUiAvL1xuLy8gLi9ub2RlX21vZHVsZXMvZm91bmRhdGlvbi1zaXRlcy9qcy9mb3VuZGF0aW9uLnV0aWwudG91Y2guanMiLCIndXNlIHN0cmljdCc7XG5cbmltcG9ydCAkIGZyb20gJ2pxdWVyeSc7XG5pbXBvcnQgeyBvbkxvYWQsIEdldFlvRGlnaXRzIH0gZnJvbSAnLi9mb3VuZGF0aW9uLmNvcmUudXRpbHMnO1xuaW1wb3J0IHsgTWVkaWFRdWVyeSB9IGZyb20gJy4vZm91bmRhdGlvbi51dGlsLm1lZGlhUXVlcnknO1xuaW1wb3J0IHsgUGx1Z2luIH0gZnJvbSAnLi9mb3VuZGF0aW9uLmNvcmUucGx1Z2luJztcbmltcG9ydCB7IFRyaWdnZXJzIH0gZnJvbSAnLi9mb3VuZGF0aW9uLnV0aWwudHJpZ2dlcnMnO1xuXG4vKipcbiAqIFN0aWNreSBtb2R1bGUuXG4gKiBAbW9kdWxlIGZvdW5kYXRpb24uc3RpY2t5XG4gKiBAcmVxdWlyZXMgZm91bmRhdGlvbi51dGlsLnRyaWdnZXJzXG4gKiBAcmVxdWlyZXMgZm91bmRhdGlvbi51dGlsLm1lZGlhUXVlcnlcbiAqL1xuXG5jbGFzcyBTdGlja3kgZXh0ZW5kcyBQbHVnaW4ge1xuICAvKipcbiAgICogQ3JlYXRlcyBhIG5ldyBpbnN0YW5jZSBvZiBhIHN0aWNreSB0aGluZy5cbiAgICogQGNsYXNzXG4gICAqIEBuYW1lIFN0aWNreVxuICAgKiBAcGFyYW0ge2pRdWVyeX0gZWxlbWVudCAtIGpRdWVyeSBvYmplY3QgdG8gbWFrZSBzdGlja3kuXG4gICAqIEBwYXJhbSB7T2JqZWN0fSBvcHRpb25zIC0gb3B0aW9ucyBvYmplY3QgcGFzc2VkIHdoZW4gY3JlYXRpbmcgdGhlIGVsZW1lbnQgcHJvZ3JhbW1hdGljYWxseS5cbiAgICovXG4gIF9zZXR1cChlbGVtZW50LCBvcHRpb25zKSB7XG4gICAgdGhpcy4kZWxlbWVudCA9IGVsZW1lbnQ7XG4gICAgdGhpcy5vcHRpb25zID0gJC5leHRlbmQoe30sIFN0aWNreS5kZWZhdWx0cywgdGhpcy4kZWxlbWVudC5kYXRhKCksIG9wdGlvbnMpO1xuICAgIHRoaXMuY2xhc3NOYW1lID0gJ1N0aWNreSc7IC8vIGllOSBiYWNrIGNvbXBhdFxuXG4gICAgLy8gVHJpZ2dlcnMgaW5pdCBpcyBpZGVtcG90ZW50LCBqdXN0IG5lZWQgdG8gbWFrZSBzdXJlIGl0IGlzIGluaXRpYWxpemVkXG4gICAgVHJpZ2dlcnMuaW5pdCgkKTtcblxuICAgIHRoaXMuX2luaXQoKTtcbiAgfVxuXG4gIC8qKlxuICAgKiBJbml0aWFsaXplcyB0aGUgc3RpY2t5IGVsZW1lbnQgYnkgYWRkaW5nIGNsYXNzZXMsIGdldHRpbmcvc2V0dGluZyBkaW1lbnNpb25zLCBicmVha3BvaW50cyBhbmQgYXR0cmlidXRlc1xuICAgKiBAZnVuY3Rpb25cbiAgICogQHByaXZhdGVcbiAgICovXG4gIF9pbml0KCkge1xuICAgIE1lZGlhUXVlcnkuX2luaXQoKTtcblxuICAgIHZhciAkcGFyZW50ID0gdGhpcy4kZWxlbWVudC5wYXJlbnQoJ1tkYXRhLXN0aWNreS1jb250YWluZXJdJyksXG4gICAgICAgIGlkID0gdGhpcy4kZWxlbWVudFswXS5pZCB8fCBHZXRZb0RpZ2l0cyg2LCAnc3RpY2t5JyksXG4gICAgICAgIF90aGlzID0gdGhpcztcblxuICAgIGlmKCRwYXJlbnQubGVuZ3RoKXtcbiAgICAgIHRoaXMuJGNvbnRhaW5lciA9ICRwYXJlbnQ7XG4gICAgfSBlbHNlIHtcbiAgICAgIHRoaXMud2FzV3JhcHBlZCA9IHRydWU7XG4gICAgICB0aGlzLiRlbGVtZW50LndyYXAodGhpcy5vcHRpb25zLmNvbnRhaW5lcik7XG4gICAgICB0aGlzLiRjb250YWluZXIgPSB0aGlzLiRlbGVtZW50LnBhcmVudCgpO1xuICAgIH1cbiAgICB0aGlzLiRjb250YWluZXIuYWRkQ2xhc3ModGhpcy5vcHRpb25zLmNvbnRhaW5lckNsYXNzKTtcblxuICAgIHRoaXMuJGVsZW1lbnQuYWRkQ2xhc3ModGhpcy5vcHRpb25zLnN0aWNreUNsYXNzKS5hdHRyKHsgJ2RhdGEtcmVzaXplJzogaWQsICdkYXRhLW11dGF0ZSc6IGlkIH0pO1xuICAgIGlmICh0aGlzLm9wdGlvbnMuYW5jaG9yICE9PSAnJykge1xuICAgICAgICAkKCcjJyArIF90aGlzLm9wdGlvbnMuYW5jaG9yKS5hdHRyKHsgJ2RhdGEtbXV0YXRlJzogaWQgfSk7XG4gICAgfVxuXG4gICAgdGhpcy5zY3JvbGxDb3VudCA9IHRoaXMub3B0aW9ucy5jaGVja0V2ZXJ5O1xuICAgIHRoaXMuaXNTdHVjayA9IGZhbHNlO1xuICAgIHRoaXMub25Mb2FkTGlzdGVuZXIgPSBvbkxvYWQoJCh3aW5kb3cpLCBmdW5jdGlvbiAoKSB7XG4gICAgICAvL1dlIGNhbGN1bGF0ZSB0aGUgY29udGFpbmVyIGhlaWdodCB0byBoYXZlIGNvcnJlY3QgdmFsdWVzIGZvciBhbmNob3IgcG9pbnRzIG9mZnNldCBjYWxjdWxhdGlvbi5cbiAgICAgIF90aGlzLmNvbnRhaW5lckhlaWdodCA9IF90aGlzLiRlbGVtZW50LmNzcyhcImRpc3BsYXlcIikgPT0gXCJub25lXCIgPyAwIDogX3RoaXMuJGVsZW1lbnRbMF0uZ2V0Qm91bmRpbmdDbGllbnRSZWN0KCkuaGVpZ2h0O1xuICAgICAgX3RoaXMuJGNvbnRhaW5lci5jc3MoJ2hlaWdodCcsIF90aGlzLmNvbnRhaW5lckhlaWdodCk7XG4gICAgICBfdGhpcy5lbGVtSGVpZ2h0ID0gX3RoaXMuY29udGFpbmVySGVpZ2h0O1xuICAgICAgaWYgKF90aGlzLm9wdGlvbnMuYW5jaG9yICE9PSAnJykge1xuICAgICAgICBfdGhpcy4kYW5jaG9yID0gJCgnIycgKyBfdGhpcy5vcHRpb25zLmFuY2hvcik7XG4gICAgICB9IGVsc2Uge1xuICAgICAgICBfdGhpcy5fcGFyc2VQb2ludHMoKTtcbiAgICAgIH1cblxuICAgICAgX3RoaXMuX3NldFNpemVzKGZ1bmN0aW9uICgpIHtcbiAgICAgICAgdmFyIHNjcm9sbCA9IHdpbmRvdy5wYWdlWU9mZnNldDtcbiAgICAgICAgX3RoaXMuX2NhbGMoZmFsc2UsIHNjcm9sbCk7XG4gICAgICAgIC8vVW5zdGljayB0aGUgZWxlbWVudCB3aWxsIGVuc3VyZSB0aGF0IHByb3BlciBjbGFzc2VzIGFyZSBzZXQuXG4gICAgICAgIGlmICghX3RoaXMuaXNTdHVjaykge1xuICAgICAgICAgIF90aGlzLl9yZW1vdmVTdGlja3koKHNjcm9sbCA+PSBfdGhpcy50b3BQb2ludCkgPyBmYWxzZSA6IHRydWUpO1xuICAgICAgICB9XG4gICAgICB9KTtcbiAgICAgIF90aGlzLl9ldmVudHMoaWQuc3BsaXQoJy0nKS5yZXZlcnNlKCkuam9pbignLScpKTtcbiAgICB9KTtcbiAgfVxuXG4gIC8qKlxuICAgKiBJZiB1c2luZyBtdWx0aXBsZSBlbGVtZW50cyBhcyBhbmNob3JzLCBjYWxjdWxhdGVzIHRoZSB0b3AgYW5kIGJvdHRvbSBwaXhlbCB2YWx1ZXMgdGhlIHN0aWNreSB0aGluZyBzaG91bGQgc3RpY2sgYW5kIHVuc3RpY2sgb24uXG4gICAqIEBmdW5jdGlvblxuICAgKiBAcHJpdmF0ZVxuICAgKi9cbiAgX3BhcnNlUG9pbnRzKCkge1xuICAgIHZhciB0b3AgPSB0aGlzLm9wdGlvbnMudG9wQW5jaG9yID09IFwiXCIgPyAxIDogdGhpcy5vcHRpb25zLnRvcEFuY2hvcixcbiAgICAgICAgYnRtID0gdGhpcy5vcHRpb25zLmJ0bUFuY2hvcj09IFwiXCIgPyBkb2N1bWVudC5kb2N1bWVudEVsZW1lbnQuc2Nyb2xsSGVpZ2h0IDogdGhpcy5vcHRpb25zLmJ0bUFuY2hvcixcbiAgICAgICAgcHRzID0gW3RvcCwgYnRtXSxcbiAgICAgICAgYnJlYWtzID0ge307XG4gICAgZm9yICh2YXIgaSA9IDAsIGxlbiA9IHB0cy5sZW5ndGg7IGkgPCBsZW4gJiYgcHRzW2ldOyBpKyspIHtcbiAgICAgIHZhciBwdDtcbiAgICAgIGlmICh0eXBlb2YgcHRzW2ldID09PSAnbnVtYmVyJykge1xuICAgICAgICBwdCA9IHB0c1tpXTtcbiAgICAgIH0gZWxzZSB7XG4gICAgICAgIHZhciBwbGFjZSA9IHB0c1tpXS5zcGxpdCgnOicpLFxuICAgICAgICAgICAgYW5jaG9yID0gJChgIyR7cGxhY2VbMF19YCk7XG5cbiAgICAgICAgcHQgPSBhbmNob3Iub2Zmc2V0KCkudG9wO1xuICAgICAgICBpZiAocGxhY2VbMV0gJiYgcGxhY2VbMV0udG9Mb3dlckNhc2UoKSA9PT0gJ2JvdHRvbScpIHtcbiAgICAgICAgICBwdCArPSBhbmNob3JbMF0uZ2V0Qm91bmRpbmdDbGllbnRSZWN0KCkuaGVpZ2h0O1xuICAgICAgICB9XG4gICAgICB9XG4gICAgICBicmVha3NbaV0gPSBwdDtcbiAgICB9XG5cblxuICAgIHRoaXMucG9pbnRzID0gYnJlYWtzO1xuICAgIHJldHVybjtcbiAgfVxuXG4gIC8qKlxuICAgKiBBZGRzIGV2ZW50IGhhbmRsZXJzIGZvciB0aGUgc2Nyb2xsaW5nIGVsZW1lbnQuXG4gICAqIEBwcml2YXRlXG4gICAqIEBwYXJhbSB7U3RyaW5nfSBpZCAtIHBzZXVkby1yYW5kb20gaWQgZm9yIHVuaXF1ZSBzY3JvbGwgZXZlbnQgbGlzdGVuZXIuXG4gICAqL1xuICBfZXZlbnRzKGlkKSB7XG4gICAgdmFyIF90aGlzID0gdGhpcyxcbiAgICAgICAgc2Nyb2xsTGlzdGVuZXIgPSB0aGlzLnNjcm9sbExpc3RlbmVyID0gYHNjcm9sbC56Zi4ke2lkfWA7XG4gICAgaWYgKHRoaXMuaXNPbikgeyByZXR1cm47IH1cbiAgICBpZiAodGhpcy5jYW5TdGljaykge1xuICAgICAgdGhpcy5pc09uID0gdHJ1ZTtcbiAgICAgICQod2luZG93KS5vZmYoc2Nyb2xsTGlzdGVuZXIpXG4gICAgICAgICAgICAgICAub24oc2Nyb2xsTGlzdGVuZXIsIGZ1bmN0aW9uKGUpIHtcbiAgICAgICAgICAgICAgICAgaWYgKF90aGlzLnNjcm9sbENvdW50ID09PSAwKSB7XG4gICAgICAgICAgICAgICAgICAgX3RoaXMuc2Nyb2xsQ291bnQgPSBfdGhpcy5vcHRpb25zLmNoZWNrRXZlcnk7XG4gICAgICAgICAgICAgICAgICAgX3RoaXMuX3NldFNpemVzKGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgICAgICAgX3RoaXMuX2NhbGMoZmFsc2UsIHdpbmRvdy5wYWdlWU9mZnNldCk7XG4gICAgICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgICAgX3RoaXMuc2Nyb2xsQ291bnQtLTtcbiAgICAgICAgICAgICAgICAgICBfdGhpcy5fY2FsYyhmYWxzZSwgd2luZG93LnBhZ2VZT2Zmc2V0KTtcbiAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICB9KTtcbiAgICB9XG5cbiAgICB0aGlzLiRlbGVtZW50Lm9mZigncmVzaXplbWUuemYudHJpZ2dlcicpXG4gICAgICAgICAgICAgICAgIC5vbigncmVzaXplbWUuemYudHJpZ2dlcicsIGZ1bmN0aW9uKGUsIGVsKSB7XG4gICAgICAgICAgICAgICAgICAgIF90aGlzLl9ldmVudHNIYW5kbGVyKGlkKTtcbiAgICB9KTtcblxuICAgIHRoaXMuJGVsZW1lbnQub24oJ211dGF0ZW1lLnpmLnRyaWdnZXInLCBmdW5jdGlvbiAoZSwgZWwpIHtcbiAgICAgICAgX3RoaXMuX2V2ZW50c0hhbmRsZXIoaWQpO1xuICAgIH0pO1xuXG4gICAgaWYodGhpcy4kYW5jaG9yKSB7XG4gICAgICB0aGlzLiRhbmNob3Iub24oJ211dGF0ZW1lLnpmLnRyaWdnZXInLCBmdW5jdGlvbiAoZSwgZWwpIHtcbiAgICAgICAgICBfdGhpcy5fZXZlbnRzSGFuZGxlcihpZCk7XG4gICAgICB9KTtcbiAgICB9XG4gIH1cblxuICAvKipcbiAgICogSGFuZGxlciBmb3IgZXZlbnRzLlxuICAgKiBAcHJpdmF0ZVxuICAgKiBAcGFyYW0ge1N0cmluZ30gaWQgLSBwc2V1ZG8tcmFuZG9tIGlkIGZvciB1bmlxdWUgc2Nyb2xsIGV2ZW50IGxpc3RlbmVyLlxuICAgKi9cbiAgX2V2ZW50c0hhbmRsZXIoaWQpIHtcbiAgICAgICB2YXIgX3RoaXMgPSB0aGlzLFxuICAgICAgICBzY3JvbGxMaXN0ZW5lciA9IHRoaXMuc2Nyb2xsTGlzdGVuZXIgPSBgc2Nyb2xsLnpmLiR7aWR9YDtcblxuICAgICAgIF90aGlzLl9zZXRTaXplcyhmdW5jdGlvbigpIHtcbiAgICAgICBfdGhpcy5fY2FsYyhmYWxzZSk7XG4gICAgICAgaWYgKF90aGlzLmNhblN0aWNrKSB7XG4gICAgICAgICBpZiAoIV90aGlzLmlzT24pIHtcbiAgICAgICAgICAgX3RoaXMuX2V2ZW50cyhpZCk7XG4gICAgICAgICB9XG4gICAgICAgfSBlbHNlIGlmIChfdGhpcy5pc09uKSB7XG4gICAgICAgICBfdGhpcy5fcGF1c2VMaXN0ZW5lcnMoc2Nyb2xsTGlzdGVuZXIpO1xuICAgICAgIH1cbiAgICAgfSk7XG4gIH1cblxuICAvKipcbiAgICogUmVtb3ZlcyBldmVudCBoYW5kbGVycyBmb3Igc2Nyb2xsIGFuZCBjaGFuZ2UgZXZlbnRzIG9uIGFuY2hvci5cbiAgICogQGZpcmVzIFN0aWNreSNwYXVzZVxuICAgKiBAcGFyYW0ge1N0cmluZ30gc2Nyb2xsTGlzdGVuZXIgLSB1bmlxdWUsIG5hbWVzcGFjZWQgc2Nyb2xsIGxpc3RlbmVyIGF0dGFjaGVkIHRvIGB3aW5kb3dgXG4gICAqL1xuICBfcGF1c2VMaXN0ZW5lcnMoc2Nyb2xsTGlzdGVuZXIpIHtcbiAgICB0aGlzLmlzT24gPSBmYWxzZTtcbiAgICAkKHdpbmRvdykub2ZmKHNjcm9sbExpc3RlbmVyKTtcblxuICAgIC8qKlxuICAgICAqIEZpcmVzIHdoZW4gdGhlIHBsdWdpbiBpcyBwYXVzZWQgZHVlIHRvIHJlc2l6ZSBldmVudCBzaHJpbmtpbmcgdGhlIHZpZXcuXG4gICAgICogQGV2ZW50IFN0aWNreSNwYXVzZVxuICAgICAqIEBwcml2YXRlXG4gICAgICovXG4gICAgIHRoaXMuJGVsZW1lbnQudHJpZ2dlcigncGF1c2UuemYuc3RpY2t5Jyk7XG4gIH1cblxuICAvKipcbiAgICogQ2FsbGVkIG9uIGV2ZXJ5IGBzY3JvbGxgIGV2ZW50IGFuZCBvbiBgX2luaXRgXG4gICAqIGZpcmVzIGZ1bmN0aW9ucyBiYXNlZCBvbiBib29sZWFucyBhbmQgY2FjaGVkIHZhbHVlc1xuICAgKiBAcGFyYW0ge0Jvb2xlYW59IGNoZWNrU2l6ZXMgLSB0cnVlIGlmIHBsdWdpbiBzaG91bGQgcmVjYWxjdWxhdGUgc2l6ZXMgYW5kIGJyZWFrcG9pbnRzLlxuICAgKiBAcGFyYW0ge051bWJlcn0gc2Nyb2xsIC0gY3VycmVudCBzY3JvbGwgcG9zaXRpb24gcGFzc2VkIGZyb20gc2Nyb2xsIGV2ZW50IGNiIGZ1bmN0aW9uLiBJZiBub3QgcGFzc2VkLCBkZWZhdWx0cyB0byBgd2luZG93LnBhZ2VZT2Zmc2V0YC5cbiAgICovXG4gIF9jYWxjKGNoZWNrU2l6ZXMsIHNjcm9sbCkge1xuICAgIGlmIChjaGVja1NpemVzKSB7IHRoaXMuX3NldFNpemVzKCk7IH1cblxuICAgIGlmICghdGhpcy5jYW5TdGljaykge1xuICAgICAgaWYgKHRoaXMuaXNTdHVjaykge1xuICAgICAgICB0aGlzLl9yZW1vdmVTdGlja3kodHJ1ZSk7XG4gICAgICB9XG4gICAgICByZXR1cm4gZmFsc2U7XG4gICAgfVxuXG4gICAgaWYgKCFzY3JvbGwpIHsgc2Nyb2xsID0gd2luZG93LnBhZ2VZT2Zmc2V0OyB9XG5cbiAgICBpZiAoc2Nyb2xsID49IHRoaXMudG9wUG9pbnQpIHtcbiAgICAgIGlmIChzY3JvbGwgPD0gdGhpcy5ib3R0b21Qb2ludCkge1xuICAgICAgICBpZiAoIXRoaXMuaXNTdHVjaykge1xuICAgICAgICAgIHRoaXMuX3NldFN0aWNreSgpO1xuICAgICAgICB9XG4gICAgICB9IGVsc2Uge1xuICAgICAgICBpZiAodGhpcy5pc1N0dWNrKSB7XG4gICAgICAgICAgdGhpcy5fcmVtb3ZlU3RpY2t5KGZhbHNlKTtcbiAgICAgICAgfVxuICAgICAgfVxuICAgIH0gZWxzZSB7XG4gICAgICBpZiAodGhpcy5pc1N0dWNrKSB7XG4gICAgICAgIHRoaXMuX3JlbW92ZVN0aWNreSh0cnVlKTtcbiAgICAgIH1cbiAgICB9XG4gIH1cblxuICAvKipcbiAgICogQ2F1c2VzIHRoZSAkZWxlbWVudCB0byBiZWNvbWUgc3R1Y2suXG4gICAqIEFkZHMgYHBvc2l0aW9uOiBmaXhlZDtgLCBhbmQgaGVscGVyIGNsYXNzZXMuXG4gICAqIEBmaXJlcyBTdGlja3kjc3R1Y2t0b1xuICAgKiBAZnVuY3Rpb25cbiAgICogQHByaXZhdGVcbiAgICovXG4gIF9zZXRTdGlja3koKSB7XG4gICAgdmFyIF90aGlzID0gdGhpcyxcbiAgICAgICAgc3RpY2tUbyA9IHRoaXMub3B0aW9ucy5zdGlja1RvLFxuICAgICAgICBtcmduID0gc3RpY2tUbyA9PT0gJ3RvcCcgPyAnbWFyZ2luVG9wJyA6ICdtYXJnaW5Cb3R0b20nLFxuICAgICAgICBub3RTdHVja1RvID0gc3RpY2tUbyA9PT0gJ3RvcCcgPyAnYm90dG9tJyA6ICd0b3AnLFxuICAgICAgICBjc3MgPSB7fTtcblxuICAgIGNzc1ttcmduXSA9IGAke3RoaXMub3B0aW9uc1ttcmduXX1lbWA7XG4gICAgY3NzW3N0aWNrVG9dID0gMDtcbiAgICBjc3Nbbm90U3R1Y2tUb10gPSAnYXV0byc7XG4gICAgdGhpcy5pc1N0dWNrID0gdHJ1ZTtcbiAgICB0aGlzLiRlbGVtZW50LnJlbW92ZUNsYXNzKGBpcy1hbmNob3JlZCBpcy1hdC0ke25vdFN0dWNrVG99YClcbiAgICAgICAgICAgICAgICAgLmFkZENsYXNzKGBpcy1zdHVjayBpcy1hdC0ke3N0aWNrVG99YClcbiAgICAgICAgICAgICAgICAgLmNzcyhjc3MpXG4gICAgICAgICAgICAgICAgIC8qKlxuICAgICAgICAgICAgICAgICAgKiBGaXJlcyB3aGVuIHRoZSAkZWxlbWVudCBoYXMgYmVjb21lIGBwb3NpdGlvbjogZml4ZWQ7YFxuICAgICAgICAgICAgICAgICAgKiBOYW1lc3BhY2VkIHRvIGB0b3BgIG9yIGBib3R0b21gLCBlLmcuIGBzdGlja3kuemYuc3R1Y2t0bzp0b3BgXG4gICAgICAgICAgICAgICAgICAqIEBldmVudCBTdGlja3kjc3R1Y2t0b1xuICAgICAgICAgICAgICAgICAgKi9cbiAgICAgICAgICAgICAgICAgLnRyaWdnZXIoYHN0aWNreS56Zi5zdHVja3RvOiR7c3RpY2tUb31gKTtcbiAgICB0aGlzLiRlbGVtZW50Lm9uKFwidHJhbnNpdGlvbmVuZCB3ZWJraXRUcmFuc2l0aW9uRW5kIG9UcmFuc2l0aW9uRW5kIG90cmFuc2l0aW9uZW5kIE1TVHJhbnNpdGlvbkVuZFwiLCBmdW5jdGlvbigpIHtcbiAgICAgIF90aGlzLl9zZXRTaXplcygpO1xuICAgIH0pO1xuICB9XG5cbiAgLyoqXG4gICAqIENhdXNlcyB0aGUgJGVsZW1lbnQgdG8gYmVjb21lIHVuc3R1Y2suXG4gICAqIFJlbW92ZXMgYHBvc2l0aW9uOiBmaXhlZDtgLCBhbmQgaGVscGVyIGNsYXNzZXMuXG4gICAqIEFkZHMgb3RoZXIgaGVscGVyIGNsYXNzZXMuXG4gICAqIEBwYXJhbSB7Qm9vbGVhbn0gaXNUb3AgLSB0ZWxscyB0aGUgZnVuY3Rpb24gaWYgdGhlICRlbGVtZW50IHNob3VsZCBhbmNob3IgdG8gdGhlIHRvcCBvciBib3R0b20gb2YgaXRzICRhbmNob3IgZWxlbWVudC5cbiAgICogQGZpcmVzIFN0aWNreSN1bnN0dWNrZnJvbVxuICAgKiBAcHJpdmF0ZVxuICAgKi9cbiAgX3JlbW92ZVN0aWNreShpc1RvcCkge1xuICAgIHZhciBzdGlja1RvID0gdGhpcy5vcHRpb25zLnN0aWNrVG8sXG4gICAgICAgIHN0aWNrVG9Ub3AgPSBzdGlja1RvID09PSAndG9wJyxcbiAgICAgICAgY3NzID0ge30sXG4gICAgICAgIGFuY2hvclB0ID0gKHRoaXMucG9pbnRzID8gdGhpcy5wb2ludHNbMV0gLSB0aGlzLnBvaW50c1swXSA6IHRoaXMuYW5jaG9ySGVpZ2h0KSAtIHRoaXMuZWxlbUhlaWdodCxcbiAgICAgICAgbXJnbiA9IHN0aWNrVG9Ub3AgPyAnbWFyZ2luVG9wJyA6ICdtYXJnaW5Cb3R0b20nLFxuICAgICAgICBub3RTdHVja1RvID0gc3RpY2tUb1RvcCA/ICdib3R0b20nIDogJ3RvcCcsXG4gICAgICAgIHRvcE9yQm90dG9tID0gaXNUb3AgPyAndG9wJyA6ICdib3R0b20nO1xuXG4gICAgY3NzW21yZ25dID0gMDtcblxuICAgIGNzc1snYm90dG9tJ10gPSAnYXV0byc7XG4gICAgaWYoaXNUb3ApIHtcbiAgICAgIGNzc1sndG9wJ10gPSAwO1xuICAgIH0gZWxzZSB7XG4gICAgICBjc3NbJ3RvcCddID0gYW5jaG9yUHQ7XG4gICAgfVxuXG4gICAgdGhpcy5pc1N0dWNrID0gZmFsc2U7XG4gICAgdGhpcy4kZWxlbWVudC5yZW1vdmVDbGFzcyhgaXMtc3R1Y2sgaXMtYXQtJHtzdGlja1RvfWApXG4gICAgICAgICAgICAgICAgIC5hZGRDbGFzcyhgaXMtYW5jaG9yZWQgaXMtYXQtJHt0b3BPckJvdHRvbX1gKVxuICAgICAgICAgICAgICAgICAuY3NzKGNzcylcbiAgICAgICAgICAgICAgICAgLyoqXG4gICAgICAgICAgICAgICAgICAqIEZpcmVzIHdoZW4gdGhlICRlbGVtZW50IGhhcyBiZWNvbWUgYW5jaG9yZWQuXG4gICAgICAgICAgICAgICAgICAqIE5hbWVzcGFjZWQgdG8gYHRvcGAgb3IgYGJvdHRvbWAsIGUuZy4gYHN0aWNreS56Zi51bnN0dWNrZnJvbTpib3R0b21gXG4gICAgICAgICAgICAgICAgICAqIEBldmVudCBTdGlja3kjdW5zdHVja2Zyb21cbiAgICAgICAgICAgICAgICAgICovXG4gICAgICAgICAgICAgICAgIC50cmlnZ2VyKGBzdGlja3kuemYudW5zdHVja2Zyb206JHt0b3BPckJvdHRvbX1gKTtcbiAgfVxuXG4gIC8qKlxuICAgKiBTZXRzIHRoZSAkZWxlbWVudCBhbmQgJGNvbnRhaW5lciBzaXplcyBmb3IgcGx1Z2luLlxuICAgKiBDYWxscyBgX3NldEJyZWFrUG9pbnRzYC5cbiAgICogQHBhcmFtIHtGdW5jdGlvbn0gY2IgLSBvcHRpb25hbCBjYWxsYmFjayBmdW5jdGlvbiB0byBmaXJlIG9uIGNvbXBsZXRpb24gb2YgYF9zZXRCcmVha1BvaW50c2AuXG4gICAqIEBwcml2YXRlXG4gICAqL1xuICBfc2V0U2l6ZXMoY2IpIHtcbiAgICB0aGlzLmNhblN0aWNrID0gTWVkaWFRdWVyeS5pcyh0aGlzLm9wdGlvbnMuc3RpY2t5T24pO1xuICAgIGlmICghdGhpcy5jYW5TdGljaykge1xuICAgICAgaWYgKGNiICYmIHR5cGVvZiBjYiA9PT0gJ2Z1bmN0aW9uJykgeyBjYigpOyB9XG4gICAgfVxuICAgIHZhciBfdGhpcyA9IHRoaXMsXG4gICAgICAgIG5ld0VsZW1XaWR0aCA9IHRoaXMuJGNvbnRhaW5lclswXS5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKS53aWR0aCxcbiAgICAgICAgY29tcCA9IHdpbmRvdy5nZXRDb21wdXRlZFN0eWxlKHRoaXMuJGNvbnRhaW5lclswXSksXG4gICAgICAgIHBkbmdsID0gcGFyc2VJbnQoY29tcFsncGFkZGluZy1sZWZ0J10sIDEwKSxcbiAgICAgICAgcGRuZ3IgPSBwYXJzZUludChjb21wWydwYWRkaW5nLXJpZ2h0J10sIDEwKTtcblxuICAgIGlmICh0aGlzLiRhbmNob3IgJiYgdGhpcy4kYW5jaG9yLmxlbmd0aCkge1xuICAgICAgdGhpcy5hbmNob3JIZWlnaHQgPSB0aGlzLiRhbmNob3JbMF0uZ2V0Qm91bmRpbmdDbGllbnRSZWN0KCkuaGVpZ2h0O1xuICAgIH0gZWxzZSB7XG4gICAgICB0aGlzLl9wYXJzZVBvaW50cygpO1xuICAgIH1cblxuICAgIHRoaXMuJGVsZW1lbnQuY3NzKHtcbiAgICAgICdtYXgtd2lkdGgnOiBgJHtuZXdFbGVtV2lkdGggLSBwZG5nbCAtIHBkbmdyfXB4YFxuICAgIH0pO1xuXG4gICAgdmFyIG5ld0NvbnRhaW5lckhlaWdodCA9IHRoaXMuJGVsZW1lbnRbMF0uZ2V0Qm91bmRpbmdDbGllbnRSZWN0KCkuaGVpZ2h0IHx8IHRoaXMuY29udGFpbmVySGVpZ2h0O1xuICAgIGlmICh0aGlzLiRlbGVtZW50LmNzcyhcImRpc3BsYXlcIikgPT0gXCJub25lXCIpIHtcbiAgICAgIG5ld0NvbnRhaW5lckhlaWdodCA9IDA7XG4gICAgfVxuICAgIHRoaXMuY29udGFpbmVySGVpZ2h0ID0gbmV3Q29udGFpbmVySGVpZ2h0O1xuICAgIHRoaXMuJGNvbnRhaW5lci5jc3Moe1xuICAgICAgaGVpZ2h0OiBuZXdDb250YWluZXJIZWlnaHRcbiAgICB9KTtcbiAgICB0aGlzLmVsZW1IZWlnaHQgPSBuZXdDb250YWluZXJIZWlnaHQ7XG5cbiAgICBpZiAoIXRoaXMuaXNTdHVjaykge1xuICAgICAgaWYgKHRoaXMuJGVsZW1lbnQuaGFzQ2xhc3MoJ2lzLWF0LWJvdHRvbScpKSB7XG4gICAgICAgIHZhciBhbmNob3JQdCA9ICh0aGlzLnBvaW50cyA/IHRoaXMucG9pbnRzWzFdIC0gdGhpcy4kY29udGFpbmVyLm9mZnNldCgpLnRvcCA6IHRoaXMuYW5jaG9ySGVpZ2h0KSAtIHRoaXMuZWxlbUhlaWdodDtcbiAgICAgICAgdGhpcy4kZWxlbWVudC5jc3MoJ3RvcCcsIGFuY2hvclB0KTtcbiAgICAgIH1cbiAgICB9XG5cbiAgICB0aGlzLl9zZXRCcmVha1BvaW50cyhuZXdDb250YWluZXJIZWlnaHQsIGZ1bmN0aW9uKCkge1xuICAgICAgaWYgKGNiICYmIHR5cGVvZiBjYiA9PT0gJ2Z1bmN0aW9uJykgeyBjYigpOyB9XG4gICAgfSk7XG4gIH1cblxuICAvKipcbiAgICogU2V0cyB0aGUgdXBwZXIgYW5kIGxvd2VyIGJyZWFrcG9pbnRzIGZvciB0aGUgZWxlbWVudCB0byBiZWNvbWUgc3RpY2t5L3Vuc3RpY2t5LlxuICAgKiBAcGFyYW0ge051bWJlcn0gZWxlbUhlaWdodCAtIHB4IHZhbHVlIGZvciBzdGlja3kuJGVsZW1lbnQgaGVpZ2h0LCBjYWxjdWxhdGVkIGJ5IGBfc2V0U2l6ZXNgLlxuICAgKiBAcGFyYW0ge0Z1bmN0aW9ufSBjYiAtIG9wdGlvbmFsIGNhbGxiYWNrIGZ1bmN0aW9uIHRvIGJlIGNhbGxlZCBvbiBjb21wbGV0aW9uLlxuICAgKiBAcHJpdmF0ZVxuICAgKi9cbiAgX3NldEJyZWFrUG9pbnRzKGVsZW1IZWlnaHQsIGNiKSB7XG4gICAgaWYgKCF0aGlzLmNhblN0aWNrKSB7XG4gICAgICBpZiAoY2IgJiYgdHlwZW9mIGNiID09PSAnZnVuY3Rpb24nKSB7IGNiKCk7IH1cbiAgICAgIGVsc2UgeyByZXR1cm4gZmFsc2U7IH1cbiAgICB9XG4gICAgdmFyIG1Ub3AgPSBlbUNhbGModGhpcy5vcHRpb25zLm1hcmdpblRvcCksXG4gICAgICAgIG1CdG0gPSBlbUNhbGModGhpcy5vcHRpb25zLm1hcmdpbkJvdHRvbSksXG4gICAgICAgIHRvcFBvaW50ID0gdGhpcy5wb2ludHMgPyB0aGlzLnBvaW50c1swXSA6IHRoaXMuJGFuY2hvci5vZmZzZXQoKS50b3AsXG4gICAgICAgIGJvdHRvbVBvaW50ID0gdGhpcy5wb2ludHMgPyB0aGlzLnBvaW50c1sxXSA6IHRvcFBvaW50ICsgdGhpcy5hbmNob3JIZWlnaHQsXG4gICAgICAgIC8vIHRvcFBvaW50ID0gdGhpcy4kYW5jaG9yLm9mZnNldCgpLnRvcCB8fCB0aGlzLnBvaW50c1swXSxcbiAgICAgICAgLy8gYm90dG9tUG9pbnQgPSB0b3BQb2ludCArIHRoaXMuYW5jaG9ySGVpZ2h0IHx8IHRoaXMucG9pbnRzWzFdLFxuICAgICAgICB3aW5IZWlnaHQgPSB3aW5kb3cuaW5uZXJIZWlnaHQ7XG5cbiAgICBpZiAodGhpcy5vcHRpb25zLnN0aWNrVG8gPT09ICd0b3AnKSB7XG4gICAgICB0b3BQb2ludCAtPSBtVG9wO1xuICAgICAgYm90dG9tUG9pbnQgLT0gKGVsZW1IZWlnaHQgKyBtVG9wKTtcbiAgICB9IGVsc2UgaWYgKHRoaXMub3B0aW9ucy5zdGlja1RvID09PSAnYm90dG9tJykge1xuICAgICAgdG9wUG9pbnQgLT0gKHdpbkhlaWdodCAtIChlbGVtSGVpZ2h0ICsgbUJ0bSkpO1xuICAgICAgYm90dG9tUG9pbnQgLT0gKHdpbkhlaWdodCAtIG1CdG0pO1xuICAgIH0gZWxzZSB7XG4gICAgICAvL3RoaXMgd291bGQgYmUgdGhlIHN0aWNrVG86IGJvdGggb3B0aW9uLi4uIHRyaWNreVxuICAgIH1cblxuICAgIHRoaXMudG9wUG9pbnQgPSB0b3BQb2ludDtcbiAgICB0aGlzLmJvdHRvbVBvaW50ID0gYm90dG9tUG9pbnQ7XG5cbiAgICBpZiAoY2IgJiYgdHlwZW9mIGNiID09PSAnZnVuY3Rpb24nKSB7IGNiKCk7IH1cbiAgfVxuXG4gIC8qKlxuICAgKiBEZXN0cm95cyB0aGUgY3VycmVudCBzdGlja3kgZWxlbWVudC5cbiAgICogUmVzZXRzIHRoZSBlbGVtZW50IHRvIHRoZSB0b3AgcG9zaXRpb24gZmlyc3QuXG4gICAqIFJlbW92ZXMgZXZlbnQgbGlzdGVuZXJzLCBKUy1hZGRlZCBjc3MgcHJvcGVydGllcyBhbmQgY2xhc3NlcywgYW5kIHVud3JhcHMgdGhlICRlbGVtZW50IGlmIHRoZSBKUyBhZGRlZCB0aGUgJGNvbnRhaW5lci5cbiAgICogQGZ1bmN0aW9uXG4gICAqL1xuICBfZGVzdHJveSgpIHtcbiAgICB0aGlzLl9yZW1vdmVTdGlja3kodHJ1ZSk7XG5cbiAgICB0aGlzLiRlbGVtZW50LnJlbW92ZUNsYXNzKGAke3RoaXMub3B0aW9ucy5zdGlja3lDbGFzc30gaXMtYW5jaG9yZWQgaXMtYXQtdG9wYClcbiAgICAgICAgICAgICAgICAgLmNzcyh7XG4gICAgICAgICAgICAgICAgICAgaGVpZ2h0OiAnJyxcbiAgICAgICAgICAgICAgICAgICB0b3A6ICcnLFxuICAgICAgICAgICAgICAgICAgIGJvdHRvbTogJycsXG4gICAgICAgICAgICAgICAgICAgJ21heC13aWR0aCc6ICcnXG4gICAgICAgICAgICAgICAgIH0pXG4gICAgICAgICAgICAgICAgIC5vZmYoJ3Jlc2l6ZW1lLnpmLnRyaWdnZXInKVxuICAgICAgICAgICAgICAgICAub2ZmKCdtdXRhdGVtZS56Zi50cmlnZ2VyJyk7XG4gICAgaWYgKHRoaXMuJGFuY2hvciAmJiB0aGlzLiRhbmNob3IubGVuZ3RoKSB7XG4gICAgICB0aGlzLiRhbmNob3Iub2ZmKCdjaGFuZ2UuemYuc3RpY2t5Jyk7XG4gICAgfVxuICAgIGlmICh0aGlzLnNjcm9sbExpc3RlbmVyKSAkKHdpbmRvdykub2ZmKHRoaXMuc2Nyb2xsTGlzdGVuZXIpXG4gICAgaWYgKHRoaXMub25Mb2FkTGlzdGVuZXIpICQod2luZG93KS5vZmYodGhpcy5vbkxvYWRMaXN0ZW5lcilcblxuICAgIGlmICh0aGlzLndhc1dyYXBwZWQpIHtcbiAgICAgIHRoaXMuJGVsZW1lbnQudW53cmFwKCk7XG4gICAgfSBlbHNlIHtcbiAgICAgIHRoaXMuJGNvbnRhaW5lci5yZW1vdmVDbGFzcyh0aGlzLm9wdGlvbnMuY29udGFpbmVyQ2xhc3MpXG4gICAgICAgICAgICAgICAgICAgICAuY3NzKHtcbiAgICAgICAgICAgICAgICAgICAgICAgaGVpZ2h0OiAnJ1xuICAgICAgICAgICAgICAgICAgICAgfSk7XG4gICAgfVxuICB9XG59XG5cblN0aWNreS5kZWZhdWx0cyA9IHtcbiAgLyoqXG4gICAqIEN1c3RvbWl6YWJsZSBjb250YWluZXIgdGVtcGxhdGUuIEFkZCB5b3VyIG93biBjbGFzc2VzIGZvciBzdHlsaW5nIGFuZCBzaXppbmcuXG4gICAqIEBvcHRpb25cbiAgICogQHR5cGUge3N0cmluZ31cbiAgICogQGRlZmF1bHQgJyZsdDtkaXYgZGF0YS1zdGlja3ktY29udGFpbmVyJmd0OyZsdDsvZGl2Jmd0OydcbiAgICovXG4gIGNvbnRhaW5lcjogJzxkaXYgZGF0YS1zdGlja3ktY29udGFpbmVyPjwvZGl2PicsXG4gIC8qKlxuICAgKiBMb2NhdGlvbiBpbiB0aGUgdmlldyB0aGUgZWxlbWVudCBzdGlja3MgdG8uIENhbiBiZSBgJ3RvcCdgIG9yIGAnYm90dG9tJ2AuXG4gICAqIEBvcHRpb25cbiAgICogQHR5cGUge3N0cmluZ31cbiAgICogQGRlZmF1bHQgJ3RvcCdcbiAgICovXG4gIHN0aWNrVG86ICd0b3AnLFxuICAvKipcbiAgICogSWYgYW5jaG9yZWQgdG8gYSBzaW5nbGUgZWxlbWVudCwgdGhlIGlkIG9mIHRoYXQgZWxlbWVudC5cbiAgICogQG9wdGlvblxuICAgKiBAdHlwZSB7c3RyaW5nfVxuICAgKiBAZGVmYXVsdCAnJ1xuICAgKi9cbiAgYW5jaG9yOiAnJyxcbiAgLyoqXG4gICAqIElmIHVzaW5nIG1vcmUgdGhhbiBvbmUgZWxlbWVudCBhcyBhbmNob3IgcG9pbnRzLCB0aGUgaWQgb2YgdGhlIHRvcCBhbmNob3IuXG4gICAqIEBvcHRpb25cbiAgICogQHR5cGUge3N0cmluZ31cbiAgICogQGRlZmF1bHQgJydcbiAgICovXG4gIHRvcEFuY2hvcjogJycsXG4gIC8qKlxuICAgKiBJZiB1c2luZyBtb3JlIHRoYW4gb25lIGVsZW1lbnQgYXMgYW5jaG9yIHBvaW50cywgdGhlIGlkIG9mIHRoZSBib3R0b20gYW5jaG9yLlxuICAgKiBAb3B0aW9uXG4gICAqIEB0eXBlIHtzdHJpbmd9XG4gICAqIEBkZWZhdWx0ICcnXG4gICAqL1xuICBidG1BbmNob3I6ICcnLFxuICAvKipcbiAgICogTWFyZ2luLCBpbiBgZW1gJ3MgdG8gYXBwbHkgdG8gdGhlIHRvcCBvZiB0aGUgZWxlbWVudCB3aGVuIGl0IGJlY29tZXMgc3RpY2t5LlxuICAgKiBAb3B0aW9uXG4gICAqIEB0eXBlIHtudW1iZXJ9XG4gICAqIEBkZWZhdWx0IDFcbiAgICovXG4gIG1hcmdpblRvcDogMSxcbiAgLyoqXG4gICAqIE1hcmdpbiwgaW4gYGVtYCdzIHRvIGFwcGx5IHRvIHRoZSBib3R0b20gb2YgdGhlIGVsZW1lbnQgd2hlbiBpdCBiZWNvbWVzIHN0aWNreS5cbiAgICogQG9wdGlvblxuICAgKiBAdHlwZSB7bnVtYmVyfVxuICAgKiBAZGVmYXVsdCAxXG4gICAqL1xuICBtYXJnaW5Cb3R0b206IDEsXG4gIC8qKlxuICAgKiBCcmVha3BvaW50IHN0cmluZyB0aGF0IGlzIHRoZSBtaW5pbXVtIHNjcmVlbiBzaXplIGFuIGVsZW1lbnQgc2hvdWxkIGJlY29tZSBzdGlja3kuXG4gICAqIEBvcHRpb25cbiAgICogQHR5cGUge3N0cmluZ31cbiAgICogQGRlZmF1bHQgJ21lZGl1bSdcbiAgICovXG4gIHN0aWNreU9uOiAnbWVkaXVtJyxcbiAgLyoqXG4gICAqIENsYXNzIGFwcGxpZWQgdG8gc3RpY2t5IGVsZW1lbnQsIGFuZCByZW1vdmVkIG9uIGRlc3RydWN0aW9uLiBGb3VuZGF0aW9uIGRlZmF1bHRzIHRvIGBzdGlja3lgLlxuICAgKiBAb3B0aW9uXG4gICAqIEB0eXBlIHtzdHJpbmd9XG4gICAqIEBkZWZhdWx0ICdzdGlja3knXG4gICAqL1xuICBzdGlja3lDbGFzczogJ3N0aWNreScsXG4gIC8qKlxuICAgKiBDbGFzcyBhcHBsaWVkIHRvIHN0aWNreSBjb250YWluZXIuIEZvdW5kYXRpb24gZGVmYXVsdHMgdG8gYHN0aWNreS1jb250YWluZXJgLlxuICAgKiBAb3B0aW9uXG4gICAqIEB0eXBlIHtzdHJpbmd9XG4gICAqIEBkZWZhdWx0ICdzdGlja3ktY29udGFpbmVyJ1xuICAgKi9cbiAgY29udGFpbmVyQ2xhc3M6ICdzdGlja3ktY29udGFpbmVyJyxcbiAgLyoqXG4gICAqIE51bWJlciBvZiBzY3JvbGwgZXZlbnRzIGJldHdlZW4gdGhlIHBsdWdpbidzIHJlY2FsY3VsYXRpbmcgc3RpY2t5IHBvaW50cy4gU2V0dGluZyBpdCB0byBgMGAgd2lsbCBjYXVzZSBpdCB0byByZWNhbGMgZXZlcnkgc2Nyb2xsIGV2ZW50LCBzZXR0aW5nIGl0IHRvIGAtMWAgd2lsbCBwcmV2ZW50IHJlY2FsYyBvbiBzY3JvbGwuXG4gICAqIEBvcHRpb25cbiAgICogQHR5cGUge251bWJlcn1cbiAgICogQGRlZmF1bHQgLTFcbiAgICovXG4gIGNoZWNrRXZlcnk6IC0xXG59O1xuXG4vKipcbiAqIEhlbHBlciBmdW5jdGlvbiB0byBjYWxjdWxhdGUgZW0gdmFsdWVzXG4gKiBAcGFyYW0gTnVtYmVyIHtlbX0gLSBudW1iZXIgb2YgZW0ncyB0byBjYWxjdWxhdGUgaW50byBwaXhlbHNcbiAqL1xuZnVuY3Rpb24gZW1DYWxjKGVtKSB7XG4gIHJldHVybiBwYXJzZUludCh3aW5kb3cuZ2V0Q29tcHV0ZWRTdHlsZShkb2N1bWVudC5ib2R5LCBudWxsKS5mb250U2l6ZSwgMTApICogZW07XG59XG5cbmV4cG9ydCB7U3RpY2t5fTtcblxuXG5cbi8vIFdFQlBBQ0sgRk9PVEVSIC8vXG4vLyAuL25vZGVfbW9kdWxlcy9mb3VuZGF0aW9uLXNpdGVzL2pzL2ZvdW5kYXRpb24uc3RpY2t5LmpzIiwiJ3VzZSBzdHJpY3QnO1xuXG5pbXBvcnQgJCBmcm9tICdqcXVlcnknO1xuaW1wb3J0IHsgTW90aW9uIH0gZnJvbSAnLi9mb3VuZGF0aW9uLnV0aWwubW90aW9uJztcbmltcG9ydCB7IFBsdWdpbiB9IGZyb20gJy4vZm91bmRhdGlvbi5jb3JlLnBsdWdpbic7XG5pbXBvcnQgeyBSZWdFeHBFc2NhcGUgfSBmcm9tICcuL2ZvdW5kYXRpb24uY29yZS51dGlscyc7XG5pbXBvcnQgeyBUcmlnZ2VycyB9IGZyb20gJy4vZm91bmRhdGlvbi51dGlsLnRyaWdnZXJzJztcblxuLyoqXG4gKiBUb2dnbGVyIG1vZHVsZS5cbiAqIEBtb2R1bGUgZm91bmRhdGlvbi50b2dnbGVyXG4gKiBAcmVxdWlyZXMgZm91bmRhdGlvbi51dGlsLm1vdGlvblxuICogQHJlcXVpcmVzIGZvdW5kYXRpb24udXRpbC50cmlnZ2Vyc1xuICovXG5cbmNsYXNzIFRvZ2dsZXIgZXh0ZW5kcyBQbHVnaW4ge1xuICAvKipcbiAgICogQ3JlYXRlcyBhIG5ldyBpbnN0YW5jZSBvZiBUb2dnbGVyLlxuICAgKiBAY2xhc3NcbiAgICogQG5hbWUgVG9nZ2xlclxuICAgKiBAZmlyZXMgVG9nZ2xlciNpbml0XG4gICAqIEBwYXJhbSB7T2JqZWN0fSBlbGVtZW50IC0galF1ZXJ5IG9iamVjdCB0byBhZGQgdGhlIHRyaWdnZXIgdG8uXG4gICAqIEBwYXJhbSB7T2JqZWN0fSBvcHRpb25zIC0gT3ZlcnJpZGVzIHRvIHRoZSBkZWZhdWx0IHBsdWdpbiBzZXR0aW5ncy5cbiAgICovXG4gIF9zZXR1cChlbGVtZW50LCBvcHRpb25zKSB7XG4gICAgdGhpcy4kZWxlbWVudCA9IGVsZW1lbnQ7XG4gICAgdGhpcy5vcHRpb25zID0gJC5leHRlbmQoe30sIFRvZ2dsZXIuZGVmYXVsdHMsIGVsZW1lbnQuZGF0YSgpLCBvcHRpb25zKTtcbiAgICB0aGlzLmNsYXNzTmFtZSA9ICcnO1xuICAgIHRoaXMuY2xhc3NOYW1lID0gJ1RvZ2dsZXInOyAvLyBpZTkgYmFjayBjb21wYXRcblxuICAgIC8vIFRyaWdnZXJzIGluaXQgaXMgaWRlbXBvdGVudCwganVzdCBuZWVkIHRvIG1ha2Ugc3VyZSBpdCBpcyBpbml0aWFsaXplZFxuICAgIFRyaWdnZXJzLmluaXQoJCk7XG5cbiAgICB0aGlzLl9pbml0KCk7XG4gICAgdGhpcy5fZXZlbnRzKCk7XG4gIH1cblxuICAvKipcbiAgICogSW5pdGlhbGl6ZXMgdGhlIFRvZ2dsZXIgcGx1Z2luIGJ5IHBhcnNpbmcgdGhlIHRvZ2dsZSBjbGFzcyBmcm9tIGRhdGEtdG9nZ2xlciwgb3IgYW5pbWF0aW9uIGNsYXNzZXMgZnJvbSBkYXRhLWFuaW1hdGUuXG4gICAqIEBmdW5jdGlvblxuICAgKiBAcHJpdmF0ZVxuICAgKi9cbiAgX2luaXQoKSB7XG4gICAgdmFyIGlucHV0O1xuICAgIC8vIFBhcnNlIGFuaW1hdGlvbiBjbGFzc2VzIGlmIHRoZXkgd2VyZSBzZXRcbiAgICBpZiAodGhpcy5vcHRpb25zLmFuaW1hdGUpIHtcbiAgICAgIGlucHV0ID0gdGhpcy5vcHRpb25zLmFuaW1hdGUuc3BsaXQoJyAnKTtcblxuICAgICAgdGhpcy5hbmltYXRpb25JbiA9IGlucHV0WzBdO1xuICAgICAgdGhpcy5hbmltYXRpb25PdXQgPSBpbnB1dFsxXSB8fCBudWxsO1xuICAgIH1cbiAgICAvLyBPdGhlcndpc2UsIHBhcnNlIHRvZ2dsZSBjbGFzc1xuICAgIGVsc2Uge1xuICAgICAgaW5wdXQgPSB0aGlzLiRlbGVtZW50LmRhdGEoJ3RvZ2dsZXInKTtcbiAgICAgIC8vIEFsbG93IGZvciBhIC4gYXQgdGhlIGJlZ2lubmluZyBvZiB0aGUgc3RyaW5nXG4gICAgICB0aGlzLmNsYXNzTmFtZSA9IGlucHV0WzBdID09PSAnLicgPyBpbnB1dC5zbGljZSgxKSA6IGlucHV0O1xuICAgIH1cblxuICAgIC8vIEFkZCBBUklBIGF0dHJpYnV0ZXMgdG8gdHJpZ2dlcnM6XG4gICAgdmFyIGlkID0gdGhpcy4kZWxlbWVudFswXS5pZCxcbiAgICAgICR0cmlnZ2VycyA9ICQoYFtkYXRhLW9wZW5+PVwiJHtpZH1cIl0sIFtkYXRhLWNsb3Nlfj1cIiR7aWR9XCJdLCBbZGF0YS10b2dnbGV+PVwiJHtpZH1cIl1gKTtcblxuICAgIC8vIC0gYXJpYS1leHBhbmRlZDogYWNjb3JkaW5nIHRvIHRoZSBlbGVtZW50IHZpc2liaWxpdHkuXG4gICAgJHRyaWdnZXJzLmF0dHIoJ2FyaWEtZXhwYW5kZWQnLCAhdGhpcy4kZWxlbWVudC5pcygnOmhpZGRlbicpKTtcbiAgICAvLyAtIGFyaWEtY29udHJvbHM6IGFkZGluZyB0aGUgZWxlbWVudCBpZCB0byBpdCBpZiBub3QgYWxyZWFkeSBpbiBpdC5cbiAgICAkdHJpZ2dlcnMuZWFjaCgoaW5kZXgsIHRyaWdnZXIpID0+IHtcbiAgICAgIGNvbnN0ICR0cmlnZ2VyID0gJCh0cmlnZ2VyKTtcbiAgICAgIGNvbnN0IGNvbnRyb2xzID0gJHRyaWdnZXIuYXR0cignYXJpYS1jb250cm9scycpIHx8ICcnO1xuXG4gICAgICBjb25zdCBjb250YWluc0lkID0gbmV3IFJlZ0V4cChgXFxcXGIke1JlZ0V4cEVzY2FwZShpZCl9XFxcXGJgKS50ZXN0KGNvbnRyb2xzKTtcbiAgICAgIGlmICghY29udGFpbnNJZCkgJHRyaWdnZXIuYXR0cignYXJpYS1jb250cm9scycsIGNvbnRyb2xzID8gYCR7Y29udHJvbHN9ICR7aWR9YCA6IGlkKTtcbiAgICB9KTtcbiAgfVxuXG4gIC8qKlxuICAgKiBJbml0aWFsaXplcyBldmVudHMgZm9yIHRoZSB0b2dnbGUgdHJpZ2dlci5cbiAgICogQGZ1bmN0aW9uXG4gICAqIEBwcml2YXRlXG4gICAqL1xuICBfZXZlbnRzKCkge1xuICAgIHRoaXMuJGVsZW1lbnQub2ZmKCd0b2dnbGUuemYudHJpZ2dlcicpLm9uKCd0b2dnbGUuemYudHJpZ2dlcicsIHRoaXMudG9nZ2xlLmJpbmQodGhpcykpO1xuICB9XG5cbiAgLyoqXG4gICAqIFRvZ2dsZXMgdGhlIHRhcmdldCBjbGFzcyBvbiB0aGUgdGFyZ2V0IGVsZW1lbnQuIEFuIGV2ZW50IGlzIGZpcmVkIGZyb20gdGhlIG9yaWdpbmFsIHRyaWdnZXIgZGVwZW5kaW5nIG9uIGlmIHRoZSByZXN1bHRhbnQgc3RhdGUgd2FzIFwib25cIiBvciBcIm9mZlwiLlxuICAgKiBAZnVuY3Rpb25cbiAgICogQGZpcmVzIFRvZ2dsZXIjb25cbiAgICogQGZpcmVzIFRvZ2dsZXIjb2ZmXG4gICAqL1xuICB0b2dnbGUoKSB7XG4gICAgdGhpc1sgdGhpcy5vcHRpb25zLmFuaW1hdGUgPyAnX3RvZ2dsZUFuaW1hdGUnIDogJ190b2dnbGVDbGFzcyddKCk7XG4gIH1cblxuICBfdG9nZ2xlQ2xhc3MoKSB7XG4gICAgdGhpcy4kZWxlbWVudC50b2dnbGVDbGFzcyh0aGlzLmNsYXNzTmFtZSk7XG5cbiAgICB2YXIgaXNPbiA9IHRoaXMuJGVsZW1lbnQuaGFzQ2xhc3ModGhpcy5jbGFzc05hbWUpO1xuICAgIGlmIChpc09uKSB7XG4gICAgICAvKipcbiAgICAgICAqIEZpcmVzIGlmIHRoZSB0YXJnZXQgZWxlbWVudCBoYXMgdGhlIGNsYXNzIGFmdGVyIGEgdG9nZ2xlLlxuICAgICAgICogQGV2ZW50IFRvZ2dsZXIjb25cbiAgICAgICAqL1xuICAgICAgdGhpcy4kZWxlbWVudC50cmlnZ2VyKCdvbi56Zi50b2dnbGVyJyk7XG4gICAgfVxuICAgIGVsc2Uge1xuICAgICAgLyoqXG4gICAgICAgKiBGaXJlcyBpZiB0aGUgdGFyZ2V0IGVsZW1lbnQgZG9lcyBub3QgaGF2ZSB0aGUgY2xhc3MgYWZ0ZXIgYSB0b2dnbGUuXG4gICAgICAgKiBAZXZlbnQgVG9nZ2xlciNvZmZcbiAgICAgICAqL1xuICAgICAgdGhpcy4kZWxlbWVudC50cmlnZ2VyKCdvZmYuemYudG9nZ2xlcicpO1xuICAgIH1cblxuICAgIHRoaXMuX3VwZGF0ZUFSSUEoaXNPbik7XG4gICAgdGhpcy4kZWxlbWVudC5maW5kKCdbZGF0YS1tdXRhdGVdJykudHJpZ2dlcignbXV0YXRlbWUuemYudHJpZ2dlcicpO1xuICB9XG5cbiAgX3RvZ2dsZUFuaW1hdGUoKSB7XG4gICAgdmFyIF90aGlzID0gdGhpcztcblxuICAgIGlmICh0aGlzLiRlbGVtZW50LmlzKCc6aGlkZGVuJykpIHtcbiAgICAgIE1vdGlvbi5hbmltYXRlSW4odGhpcy4kZWxlbWVudCwgdGhpcy5hbmltYXRpb25JbiwgZnVuY3Rpb24oKSB7XG4gICAgICAgIF90aGlzLl91cGRhdGVBUklBKHRydWUpO1xuICAgICAgICB0aGlzLnRyaWdnZXIoJ29uLnpmLnRvZ2dsZXInKTtcbiAgICAgICAgdGhpcy5maW5kKCdbZGF0YS1tdXRhdGVdJykudHJpZ2dlcignbXV0YXRlbWUuemYudHJpZ2dlcicpO1xuICAgICAgfSk7XG4gICAgfVxuICAgIGVsc2Uge1xuICAgICAgTW90aW9uLmFuaW1hdGVPdXQodGhpcy4kZWxlbWVudCwgdGhpcy5hbmltYXRpb25PdXQsIGZ1bmN0aW9uKCkge1xuICAgICAgICBfdGhpcy5fdXBkYXRlQVJJQShmYWxzZSk7XG4gICAgICAgIHRoaXMudHJpZ2dlcignb2ZmLnpmLnRvZ2dsZXInKTtcbiAgICAgICAgdGhpcy5maW5kKCdbZGF0YS1tdXRhdGVdJykudHJpZ2dlcignbXV0YXRlbWUuemYudHJpZ2dlcicpO1xuICAgICAgfSk7XG4gICAgfVxuICB9XG5cbiAgX3VwZGF0ZUFSSUEoaXNPbikge1xuICAgIHZhciBpZCA9IHRoaXMuJGVsZW1lbnRbMF0uaWQ7XG4gICAgJChgW2RhdGEtb3Blbj1cIiR7aWR9XCJdLCBbZGF0YS1jbG9zZT1cIiR7aWR9XCJdLCBbZGF0YS10b2dnbGU9XCIke2lkfVwiXWApXG4gICAgICAuYXR0cih7XG4gICAgICAgICdhcmlhLWV4cGFuZGVkJzogaXNPbiA/IHRydWUgOiBmYWxzZVxuICAgICAgfSk7XG4gIH1cblxuICAvKipcbiAgICogRGVzdHJveXMgdGhlIGluc3RhbmNlIG9mIFRvZ2dsZXIgb24gdGhlIGVsZW1lbnQuXG4gICAqIEBmdW5jdGlvblxuICAgKi9cbiAgX2Rlc3Ryb3koKSB7XG4gICAgdGhpcy4kZWxlbWVudC5vZmYoJy56Zi50b2dnbGVyJyk7XG4gIH1cbn1cblxuVG9nZ2xlci5kZWZhdWx0cyA9IHtcbiAgLyoqXG4gICAqIFRlbGxzIHRoZSBwbHVnaW4gaWYgdGhlIGVsZW1lbnQgc2hvdWxkIGFuaW1hdGVkIHdoZW4gdG9nZ2xlZC5cbiAgICogQG9wdGlvblxuICAgKiBAdHlwZSB7Ym9vbGVhbn1cbiAgICogQGRlZmF1bHQgZmFsc2VcbiAgICovXG4gIGFuaW1hdGU6IGZhbHNlXG59O1xuXG5leHBvcnQge1RvZ2dsZXJ9O1xuXG5cblxuLy8gV0VCUEFDSyBGT09URVIgLy9cbi8vIC4vbm9kZV9tb2R1bGVzL2ZvdW5kYXRpb24tc2l0ZXMvanMvZm91bmRhdGlvbi50b2dnbGVyLmpzIl0sInNvdXJjZVJvb3QiOiIifQ==