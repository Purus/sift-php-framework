/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function()
{
  // supported cross-browser selectors: #id  |  div  |  div.class  |  .class
  // Taken from Nette framework, remove unused code,
  // refactored the addClass, removeClass and hasClass methods
  var Query = function(selector) {
    if(typeof selector === "string") {
      selector = this._find(document, selector);

    } else if(!selector || selector.nodeType || selector.length === undefined
            || selector === window) {
      selector = [selector];
    }

    for(var i = 0, len = selector.length; i < len; i++) {
      if(selector[i]) {
        this[this.length++] = selector[i];
      }
    }
  };

  Query.factory = function(selector) {
    return new Query(selector);
  };

  Query.prototype.length = 0;

  Query.prototype.find = function(selector)
  {
    return new Query(this._find(this[0], selector));
  };

  Query.prototype._find = function(context, selector) {
    if(!context || !selector) {
      return [];

    } else if(document.querySelectorAll) {
      return context.querySelectorAll(selector);

    } else if(selector.charAt(0) === '#') { // #id
      return [document.getElementById(selector.substring(1))];

    } else { // div  |  div.class  |  .class
      selector = selector.split('.');
      var elms = context.getElementsByTagName(selector[0] || '*');

      if(selector[1]) {
        var list = [], pattern = new RegExp('(^|\\s)' + selector[1]
                + '(\\s|$)');
        for(var i = 0, len = elms.length; i < len; i++) {
          if(pattern.test(elms[i].className)) {
            list.push(elms[i]);
          }
        }
        return list;
      } else {
        return elms;
      }
    }
  };

  Query.prototype.dom = function() {
    return this[0];
  };

  Query.prototype.each = function(callback) {
    for(var i = 0; i < this.length; i++) {
      if(callback.apply(this[i]) === false) {
        break;
      }
    }
    return this;
  };

  // cross-browser event attach
  Query.prototype.bind = function(event, handler) {
    if(document.addEventListener && (event === 'mouseenter' || event
            === 'mouseleave')) { // simulate mouseenter & mouseleave using mouseover & mouseout
      var old = handler;
      event = event === 'mouseenter' ? 'mouseover' : 'mouseout';
      handler = function(e) {
        for(var target = e.relatedTarget; target; target = target.parentNode) {
          if(target === this) {
            return;
          } // target must not be inside this
        }
        old.call(this, e);
      };
    }

    return this.each(function() {
      var elem = this, // fixes 'this' in iE
              data = elem.nette ? elem.nette : elem.nette = {},
              events = data.events = data.events
              || {}; // use own handler queue

      if(!events[event]) {
        var handlers = events[event] = [],
                generic = function(e) { // dont worry, 'e' is passed in IE
          if(!e.target) {
            e.target = e.srcElement;
          }
          if(!e.preventDefault) {
            e.preventDefault = function() {
              e.returnValue = false;
            };
          }
          if(!e.stopPropagation) {
            e.stopPropagation = function() {
              e.cancelBubble = true;
            };
          }
          e.stopImmediatePropagation = function() {
            this.stopPropagation();
            i = handlers.length;
          };
          for(var i = 0; i < handlers.length; i++) {
            handlers[i].call(elem, e);
          }
        };

        if(document.addEventListener) { // non-IE
          elem.addEventListener(event, generic, false);
        } else if(document.attachEvent) { // IE < 9
          elem.attachEvent('on' + event, generic);
        }
      }

      events[event].push(handler);
    });
  };

  // adds class to element
  Query.prototype.addClass = function(className) {
    return this.each(function()
    {
      var tem, C = this.className.split(/\s+/), A = [];
      while(C.length)
      {
        tem = C.shift();
        if(tem && tem != className)
          A[A.length] = tem;
      }
      A[A.length] = className;
      this.className = A.join(' ');
    });
  };

  // removes class from element
  Query.prototype.removeClass = function(className)
  {
    return this.each(function()
    {
      var tem, C = this.className.split(/\s+/), A = [];
      while(C.length)
      {
        tem = C.shift();
        if(tem && tem == className)
        {
          // remove
          A[A.length] = '';
        }
        else
        {
          A[A.length] = tem;
        }
      }
      this.className = A.join(' ');
    });
  };

  // tests whether element has given class
  Query.prototype.hasClass = function(className)
  {
    var result = false;
    this.each(function()
    {
      var classes = this.className.split(/\s+/);
      while(classes.length)
      {
        var cls = classes.shift();
        if(cls == className)
        {
          result = true;
          // break the loop
          return false;
        }
      }
    });
    return result;
  };

  Query.prototype.show = function() {
    Query.displays = Query.displays || {};
    return this.each(function() {
      var tag = this.tagName;
      if(!Query.displays[tag]) {
        Query.displays[tag] = (new Query(document.body.appendChild(document.createElement(tag)))).css('display');
      }
      this.style.display = Query.displays[tag];
    });
  };

  Query.prototype.hide = function() {
    return this.each(function() {
      this.style.display = 'none';
    });
  };


  Query.prototype._trav = function(elem, selector, fce)
  {
    selector = selector.split('.');
    while(elem && !(elem.nodeType === 1 &&
            (!selector[0] || elem.tagName.toLowerCase() === selector[0]) &&
            (!selector[1] || (new Query(elem)).hasClass(selector[1])))) {
      elem = elem[fce];
    }
    return new Query(elem || []);
  };

  Query.prototype.closest = function(selector)
  {
    return this._trav(this[0], selector, 'parentNode');
  };

  Query.prototype.prev = function(selector) {
    return this._trav(this[0]
            && this[0].previousSibling, selector, 'previousSibling');
  };

  Query.prototype.next = function(selector)
  {
    return this._trav(this[0] && this[0].nextSibling, selector, 'nextSibling');
  };

  var $ = Query.factory;

  $(document).bind('click', function(e)
  {
    for(var link = e.target; link && (!link.tagName || link.className.indexOf('debug-dump-toggler') < 0); link = link.parentNode) {}
    if(!link)
    {
      return;
    }

    e.preventDefault();

    var $link = $(link);
    var ref = link.getAttribute('data-ref') || link.getAttribute('href', 2);
    var $dest = ref && ref !== '#' ? $(ref) : $link.next('');
    var collapsed = $dest.hasClass('collapsed');

    if(collapsed)
    {
      $link.addClass('opened');
      $dest.removeClass('collapsed');
    }
    else
    {
      $dest.addClass('collapsed');
      $link.removeClass('opened');
    }
  });

})();
