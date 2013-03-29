/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Provides various utility functions
 *
 * @name Tools
 * @requires jQuery
 * @class
 * @static
 */
(function($) {

  'use strict';

  var Tools = function() {

    /**
     * Comprehensively splits URIs, including splitting the query string into key/value pairs.
     *
     * @param {String} str String to be parsed
     * @see http://blog.stevenlevithan.com/archives/parseuri
     * @author Steven Levithan <stevenlevithan.com>
     * @inner
     */
    function parseUri(str)
    {
      var o = parseUri.options,
              m = o.parser[o.strictMode ? "strict" : "loose"].exec(str),
              uri = {
      },
              i = 14;

      while(i--)
        uri[o.key[i]] = m[i] || "";

      uri[o.q.name] = {
      };
      uri[o.key[12]].replace(o.q.parser, function($0, $1, $2)
      {
        if($1)
          uri[o.q.name][$1] = $2;
      });

      return uri;
    }

    /**
     * Array opt parse options
     *
     * @inner
     */
    parseUri.options = {
      strictMode: false,
      key: ["source", "protocol", "authority", "userInfo", "user", "password", "host", "port", "relative", "path", "directory", "file", "query", "anchor"],
      q: {
        name: "queryKey",
        parser: /(?:^|&)([^&=]*)=?([^&]*)/g
      },
      parser: {
        strict: /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
        loose: /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/)?((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/
      }
    };

    return {

      /**
       * Build url (directory based ie. with slash at the end)
       *
       * @param {Object} params
       * @param {String} [separator] argument separator (default is '/')
       * @param {String} [join] path join string (default is '/')
       * @methodOf Tools
       * @name buildQuery
       */
      buildQuery: function(params, separator, join) {

        if(typeof separator === 'undefined')
        {
          separator = '/';
        }
        if(typeof join === 'undefined')
        {
          join = '/';
        }
        var i = 0, tmp_arr = [];
        jQuery.each(params, function(key, val) {
          var key = encodeURIComponent(key);
          var val = encodeURIComponent(val.toString());
          tmp_arr[i++] = key + join + val;
        });
        return tmp_arr.join(separator);
      },

      /**
       * Encodes hash component
       *
       * @param {String} String to encode
       * @returns {String} Encoded string
       * @link http://stackoverflow.com/questions/619240
       * @methodOf Tools
       * @name encodeHashComponent
       */
      encodeHashComponent: function(x) {
        return encodeURIComponent(x).split('%').join('+');
      },

      /**
       * Decodes hash parameter
       *
       * @param {String} x Hash parameter
       * @returns {String}
       * @methodOf Tools
       * @name decodeHashComponent
       */
      decodeHashComponent: function(x) {
        return decodeURIComponent(x.split('+').join('%'));
      },

      /**
       * Returns hash parameters
       *
       * @param {String} [separator] String separator (default is '&')
       * @returns {Array}
       * @methodOf Tools
       * @name getHashParameters
       */
      getHashParameters: function(separator) {
        if(!separator)
        {
          separator = '&';
        }
        var parts = location.hash.substring(1).split(separator);
        var pars = {
        };
        for(var i = parts.length; i-- > 0; ) {
          var kv = parts[i].split('=');
          var k = kv[0];
          if(k == '')
            continue;
          var v = kv.slice(1).join('=');
          pars[Tools.decodeHashComponent(k)] = Tools.decodeHashComponent(v);
        }
        return pars;
      },

      /**
       * Comprehensively splits URIs, including splitting the query string into key/value pairs.
       *
       * @param {String} str String to be parsed
       * @methodOf Tools
       * @name parseUri
       */
      parseUri: parseUri
    };
  }();

  window.Tools = Tools;

}(window.jQuery));