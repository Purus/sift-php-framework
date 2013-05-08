/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function(window, $)
{
  /**
   * Various utility functions
   *
   * @name Tools
   * @class
   * @static
   */
  var Tools = function() {

    /**
     * Splits any well-formed URI into its parts
     *
     * @name parseUri
     * @author Steven Levithan <stevenlevithan.com>
     * @see http://blog.stevenlevithan.com/archives/parseuri
     * @memberOf Tools
     */
    function parseUri(str)
    {
      var o = parseUri.options,
              m = o.parser[o.strictMode ? "strict" : "loose"].exec(str),
              uri = {},
              i = 14;

      while (i--)
        uri[o.key[i]] = m[i] || "";

      uri[o.q.name] = {};
      uri[o.key[12]].replace(o.q.parser, function($0, $1, $2)
      {
        if ($1)
          uri[o.q.name][$1] = $2;
      });

      return uri;
    }
    ;

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
       * Build url (directory based)
       *
       * @param {Object} params
       * @param {String} argument separator (default is '/')
       * @param {String} join string (default is '/')
       * @memberOf Tools
       * @name buildQuery
       */
      buildQuery: function(params, separator, join) {

        if (separator == undefined)
        {
          separator = '/';
        }
        if (join == undefined)
        {
          join = '/';
        }
        var i = 0, tmp_arr = [];
        $.each(params, function(key, val)
        {
          var key = encodeURIComponent(key);
          var val = encodeURIComponent(val.toString());
          tmp_arr[i++] = key + join + val;
        });
        return tmp_arr.join(separator);
      },

      /**
       * Encodes hash component
       *
       * @param {String} x
       * @returns {String}
       * @link http://stackoverflow.com/questions/619240
       * @memberOf Tools
       * @name encodeHashComponent
       */
      encodeHashComponent: function(x)
      {
        return encodeURIComponent(x).split('%').join('+');
      },
      /**
       * Decodes hash component
       *
       * @param {String} x
       * @returns {String}
       * @link http://stackoverflow.com/questions/619240
       * @memberOf Tools
       * @name decodeHashComponent
       */
      decodeHashComponent: function(x)
      {
        return decodeURIComponent(x.split('+').join('%'));
      },
      /**
       * Returns hash parameters
       *
       * @param {String} x
       * @returns {String}
       * @link http://stackoverflow.com/questions/619240
       * @memberOf Tools
       * @name getHashParameters
       */
      getHashParameters: function(separator) {
        if (!separator)
        {
          separator = '&';
        }
        var parts = window.location.hash.substring(1).split(separator);
        var pars = {};
        for (var i = parts.length; i-- > 0; ) {
          var kv = parts[i].split('=');
          var k = kv[0];
          if (k == '')
            continue;
          var v = kv.slice(1).join('=');
          pars[Tools.decodeHashComponent(k)] = Tools.decodeHashComponent(v);
        }
        return pars;
      },
      // export to public API
      parseUri: parseUri
    };

  }();

  // export to window
  window.Tools = Tools;

  /**
   * Avoiding the "Script taking too long"
   *
   * @example
   *
   * // this is a sample method which causes "script taking too long"
   * // initdata is just an array of numbers (a very very large array)
   * var test1 = new Array(initdata.length);
   * for(var i = 0; i < initdata.length; i++)
   * {
   *   // Double each item in the initdata array
   *   test1[i]  = initdata[i] * 2;
   * }
   * continueOperations();
   *
   * // This routine can be rewritten to use RepeatingOperation class
   * var test2 = new Array(initdata.length);
   * var i = 0;
   * var ro = new RepeatingOperation(function()
   * {
   *   test2[i] = initdata[i] * 2;
   *   if(++i < initdata.length)
   *   {
   *     ro.step();
   *   }
   *   else
   *   {
   *    continueOperations();
   *   }
   * }, 100);
   *
   * ro.step();
   *
   * @param {Function} op Function which takes a long time to run
   * @param {Integer} yieldEveryIteration How many iteration in one batch should be run?
   * @link  http://www.picnet.com.au/blogs/Guido/post/2010/03/04/How-to-prevent-Stop-running-this-script-message-in-browsers
   * @name RepeatingOperation
   * @class
   */
  var RepeatingOperation = function(op, yieldEveryIteration)
  {
    var count = 0;
    var instance = this;
    /**
     * @ignore
     */
    this.step = function(args)
    {
      if (++count >= yieldEveryIteration)
      {
        count = 0;
        window.setTimeout(function()
        {
          op(args);
        }, 1, []);
        return;
      }
      op(args);
    };
  };

  // export to window
  window.RepeatingOperation = RepeatingOperation;

}(window, window.jQuery));
