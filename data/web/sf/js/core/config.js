/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Configuration utility object
 *
 * Retrieves configuration properties defined in configuration array
 *
 * @class
 * @name Config
 * @requires jQuery
 * @static
 */
(function($, window) {

  'use strict';

  var Config = function() {

    /**
     * Default configuration
     *
     * @memberOf Config
     * @name configuration
     * @inner
     */
    var configuration = {
      debug: false,
      log: false
    };

    return {

      /**
       * Adds configuration settings
       *
       * @param {Object} data
       * @methodOf Config
       * @name add
       */
      add: function(data) {
        jQuery.extend(configuration, data);
      },

      /**
       * Set configuration setting
       *
       * @param {Object} name
       * @param {Object} value
       * @memberOf Config
       * @name set
       */
      set: function(name, value) {
        configuration[name] = value;
      },

      /**
       * Retrieve configuration setting
       *
       * @param {Object} name
       * @param {Object} default_value
       * @memberOf Config
       * @name get
       */
      get: function(name, default_value) {
        if (configuration && configuration[name])
        {
          return configuration[name];
        }
        return default_value;
      }
    };

  }();

  // push to window
  window.Config = Config;

}(window.jQuery, window));
