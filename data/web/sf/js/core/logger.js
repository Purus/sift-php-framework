/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* global Config: false */

/**
 * Browser debugging console.
 *
 * @name Console
 * @class
 */

/**
 * Logger - logs events to console and/or to server
 *
 * @name Logger
 * @class
 * @requires Config
 * @requires jQuery
 * @requires Console
 * @static
 */
(function($, window) {

  "use strict";

  var Logger = function()
  {
    /**
     * Is the logger enabled? Uses "debug" configuration setting.
     *
     * @function
     * @memberOf Logger
     * @return {Boolean} True is is enabled, false otherwise.
     * @see Config.get
     * @inner
     */
    var isEnabled = function()
    {
      if(Config.get('debug'))
      {
        return true;
      }
      return false;
    };

    /**
     * Has the browser console object?
     *
     * @function
     * @memberOf Logger
     * @return {Boolean} True is is yes, false otherwise.
     * @inner
     */
    var hasConsole = function()
    {
      // do we have console available?
      if(typeof window.console !== 'undefined')
      {
        return true;
      }
      return false;
    };

    /**
     * Is logging to server enabled? If configuration variables "log" and "log_url" are set.
     *
     * @memberOf Logger
     * @return {Boolean} True is is yes, false otherwise.
     * @inner
     */
    var isLogToServerEnabled = function()
    {
      if(Config.get('log') && Config.get('log_url'))
      {
        return true;
      }
      return false;
    };

    /**
     * Logs data to server backend using ajax (POST method) to "log_url" (taken from configuration).
     *
     * @memberOf Logger
     * @return {Boolean} True is is yes, false otherwise.
     * @inner
     */
    var logToServer = function(logData)
    {
      var log_url = Config.get('log_url');
      if(!log_url)
      {
        return;
      }
      // make post request
      $.ajax(
      {
        url:      log_url,
        dataType: 'json',
        type:     'POST',
        // skip global events
        global:   false,
        data:     logData,
        success:  logToServerCallback
      });
    };

    /**
     * Callback for successfull ajax request for logging the error.
     *
     * @memberOf Logger
     * @inner
     */
    var logToServerCallback = function(response)
    {
      // what to do ?
      // window.location = '/error.html';
    };

    return {

      /**
       * Logs a message with "info" priority
       *
       * @methodOf Logger
       * @name info
       */
      info: function()
      {
        Logger.log(arguments);
      },

      /**
       * Logs a message
       *
       * @methodOf Logger
       * @name log
       */
      log: function()
      {
        if(isEnabled() && hasConsole())
        {
          window.console.log(arguments.length > 1 ? Array.prototype.slice.call(arguments) : arguments[0]);
        }
      },

      /**
       * Logs a message with "debug" priority
       *
       * @methodOf Logger
       * @name debug
       */
      debug: function()
      {
        if(isEnabled() && hasConsole())
        {
          window.console.debug(arguments.length > 1 ? Array.prototype.slice.call(arguments) : arguments[0]);
        }
      },

      /**
       * Logs a message with "error" priority
       *
       * @methodOf Logger
       * @name error
       */
      error: function()
      {
        if(isEnabled() && hasConsole())
        {
          window.console.error(arguments.length > 1 ? Array.prototype.slice.call(arguments) : arguments[0]);
        }
      },

      /**
       * Logs a message to server
       *
       * @methodOf Logger
       * @name logToServer
       */
      logToServer: function(logData)
      {
        if(isLogToServerEnabled())
        {
          logToServer(logData);
        }
      },

      /**
       * Dumps a variable to console
       *
       * @methodOf Logger
       * @name varDump
       */
      varDump: function(obj)
      {
        if(typeof obj === "object")
        {
          return "Type: " + typeof(obj) + ((obj.constructor) ? "\nConstructor: " + obj.constructor : "") + "\nValue: " + obj;
        }
        else
        {
          return "Type: " + typeof(obj) + "\nValue: " + obj;
        }
      }
    };

  }();

  window.Logger = Logger;

  // Prevent errors when there is a console.log debug message left in the source
  // and the browser has no console
  // Taken from: http://stackoverflow.com/questions/7892509/is-there-a-way-to-log-to-console-without-breaking-code-under-ie
  if(!('console' in window) )
  {
    var names = ['log', 'debug', 'info', 'warn', 'error', 'assert', 'dir', 'dirxml', 'group', 'groupEnd', 'time', 'timeEnd', 'count', 'trace', 'profile', 'profileEnd'];
    window.console = {};
    for(var i = 0; i < names.length; ++i)
    {
      window.console[names[i]] = function(){};
    }
  }
  else
  {
    // if it exists but doesn't contain all the same methods....silly ie
    var names = ['log', 'debug', 'info', 'warn', 'error', 'assert', 'dir', 'dirxml', 'group', 'groupEnd', 'time', 'timeEnd', 'count', 'trace', 'profile', 'profileEnd'];
    for(var i = 0; i < names.length; ++i)
    {
      if(!window.console[names[i]])
      {
        window.console[names[i]] = function() {};
      }
    }
  };

}(window.jQuery, window));