/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A JavaScript library for globalization and localization. Enables complex culture-aware number and date parsing
 * and formatting, including the raw culture information for hundreds of different languages and countries,
 * as well as an extensible system for localization. See {@link https://github.com/jquery/globalize}
 *
 * @name Globalize
 * @requires jQuery
 * @class
 */

/**
 * Handles string translations
 *
 * @class
 * @name I18n
 * @requires jQuery
 * @requires Globalize
 * @static
 */
(function($, window) {

  'use strict';

  var I18n = function() {

    /**
     * Translations holder
     */
    var I18nTranslations = [];

    var replaceArgs = function(str, from, to) {
      // replace all occurencies of 'from' by 'to'
      // g: replace all
      // possible parammeters
      //   i: ignorecase
      return str.replace(from, to, 'g');
    };

    /**
     * Formats number choice. Its Js variant of PHP's version.
     *
     * @param {String} text_string
     * @param {Array} replace_hash
     * @param {Numver} number
     * @returns {String}
     * @description Portovat php kod ze sfNumberChoice
     */
    function formatNumberChoice(text_string, replace_hash, number)
    {
      // translate it first
      text_string = __(text_string);

      var pattern = new RegExp("\\["+number+"\\]\s?([^\|]*)");

      function replace_in_string(text_string, replace_hash)
      {
        for(var i in replace_hash)
        {
          text_string = text_string.replace(new RegExp(i), replace_hash[i]);
        }
        return text_string;
      }

      var matches = text_string.match(pattern);
      if(matches != null)
      {
        return replace_in_string(matches[1], replace_hash);
      }
      else
      {
        pattern = /\[else\]\s?([^\|]*)/;
        matches = text_string.match(pattern);
        if(matches != null)
        {
          return replace_in_string(matches[1], replace_hash);
        }
        else
        {
          return 'not found';
        }
      }
    }

    return {

      /**
       * Formats number choice
       *
       * @methodOf I18n
       * @name formatNumberChoice
       */
      formatNumberChoice: formatNumberChoice,

      /**
       * Returns translation, if translation dictionary
       * exists and has a translation.
       *
       * @param {String} str String to be translated
       * @param {Array} params Array of parameters
       * @methodOf I18n
       * @name translate
       */
      translate: function(str, params) {

        if(I18nTranslations && I18nTranslations[str])
        {
          var str = I18nTranslations[str];
        }
        if(params)
        {
          for(var param in params)
          {
            var value = params[param];
            str = replaceArgs(str, param, value);
          }
        }
        return str;
      },

      /**
       * Sets translation
       *
       * @param {Array} translations Array of translations
       * @methodOf I18n
       * @name setTranslation
       */
      setTranslation: function(translations)
      {
        I18nTranslations = translations;
      },

      /**
       * Adds translations
       *
       * @param {Array} translations Array of translations
       * @methodOf I18n
       * @name addTranslation
       */
      addTranslation: function(translations)
      {
        $.extend(I18nTranslations, translations);
      }

      // Add other methods from jQuery globalize!

    };

  }();

  /**
   * Translation function. This is an alias for I18n.translate
   *
   * @param {String} str String to be translated
   * @param {Array} params Array of parameters
   * @name __
   * @see I18n.translate
   * @global
   */
  function __(str, params)
  {
    return I18n.translate(str, params);
  }

  window.I18n = I18n;
  window.__ = __;

}(window.jQuery, window));
