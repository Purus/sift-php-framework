/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Cookie utility class, for setting and removing browser cookies
 *
 * @class Cookie
 * @requires Config
 * @static
 */
var Cookie = function() {

  'use strict';

  return {
    /**
     * Sets cookie
     *
     * @param {String} name Name of the cookie
     * @param {String} value Value of the cookie
     * @param {Integer} daysToExpire Number of days for cookie expiration
     * @param {String} [path] Path of the cookie (Default is '/')
     * @param {String} [domain] Cookie domain (default is taken from confiuration setting "cookie_domain")
     * @param {boolean} [secure] Is the cookie secure?
     */
    set: function(name, value, daysToExpire, path, domain, secure) {

      var expire = '';
      if(daysToExpire != undefined) {
        var d = new Date();
        d.setTime(d.getTime() + (86400000 * parseInt(daysToExpire)));
        expire = '; expires=' + d.toGMTString();
      }

      if(path == undefined)
      {
        path = '/';
      }

      if(domain == undefined && typeof Config !== 'undefined')
      {
        domain = Config.get('cookie_domain');
      }

      (document.cookie = escape(name) + '=' + escape(value || '') + expire +
              ((path) ? "; path=" + path : "") +
              ((domain) ? "; domain=" + domain : "") +
              ((secure) ? "; secure" : ""));
    },
    /**
     * Returns cookie
     *
     * @param {String} name Name of the cookie
     * @returns {String|Null}
     */
    get: function(name)
    {
      var nameEQ = name + "=";
      var ca = document.cookie.split(';');
      for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while(c.charAt(0) == ' ')
          c = c.substring(1, c.length);
        if(c.indexOf(nameEQ) == 0)
          return unescape(c.substring(nameEQ.length, c.length));
      }
      return null;
    },
    /**
     * Erases the cookie
     *
     * @param {String} name Name of the cookie
     * @param {String} [path] Path of the cookie
     * @param {String} [domain] Cookie domain (default is taken from confiuration setting "cookie_domain")
     * @param {Boolean} [secure] Is the cookie secure?
     * @returns {Boolean}
     */
    erase: function(name, path, domain, secure) {
      try {
        var date = new Date();
        date.setTime(date.getTime() - (3600 * 1000));
        var expire = '; expires=' + date.toGMTString();
        document.cookie = escape(name) + '=' + expire +
                ((path) ? "; path=" + path : "") +
                ((domain) ? "; domain=" + domain : "") +
                ((secure) ? "; secure" : "");
      } catch(e) {
        return false;
      }
      return true;
    },
    /**
     * Removes cookie
     *
     * @param {String} name Name of the cookie to remove
     * @param {String} [path] Path of the cookie (Default is '/')
     * @param {String} [domain] Cookie domain (default is taken from confiuration setting "cookie_domain")
     * @param {Boolean} [secure] Is the cookie secure?
     * @returns {Boolean}
     */
    remove: function(name, path, domain, secure)
    {
      if(path == undefined)
      {
        path = '/';
      }
      if(domain == undefined &&  typeof Config !== 'undefined')
      {
        domain = Config.get('cookie_domain');
      }
      return Cookie.erase(name, path, domain, secure);
    },
    /**
     * Does the browser accept cookies?
     *
     * @returns {Boolean} True is yes, false otherwise
     */
    accept: function()
    {
      if(typeof navigator.cookieEnabled == 'boolean') {
        return navigator.cookieEnabled;
      }
      Cookie.set('_test', '1');
      return (Cookie.erase('_test') === true);
    }

  };

}();
