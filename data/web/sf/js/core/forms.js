/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * jQuery UI is a curated set of user interface interactions, effects, widgets, and themes built on
 * top of the jQuery JavaScript Library. See {@link http://http://jqueryui.com/}
 *
 * @name jQueryUI
 * @requires jQuery
 * @class
 */

/**
 * @fileOverview This file contains form setup. Widget are transformed to rich widgets.
 */
(function(Application) {

  Application = Application || {
    'behaviors' : {}
  };

  /**
   * Behavior for forms.
   *
   * @param {DOM element} context Context
   * @memberOf Application.behaviors
   */
  Application.behaviors.coreSetupForms = function(context)
  {
    Application.setupForms(context);
  };

  /**
   * Setup forms.
   *
   * @param {DOM element} context
   * @see Application.setupTimeWidgets
   * @see Application.setupDateWidgets
   * @see Application.setupDateTimeWidgets
   * @see Application.setupNumberWidgets
   */
  Application.setupForms = function(context)
  {
    Application.setupTimeWidgets(context);
    Application.setupDateWidgets(context);
    Application.setupDateTimeWidgets(context);
    Application.setupNumberWidgets(context);
  };

  /**
   * Returns default datepicker options
   *
   * @param {String} [culture] User culture
   * @return {Object} Array of options
   * @name getDatePickerOptions
   * @function
   * @methodOf Application
   * @requires I18n
   * @requires Globalize
   */
  Application.getDatePickerOptions = function(culture)
  {
    var cultureData = Globalize.culture(culture ? culture : Config.get('culture'));
    return {
      firstDay: cultureData.calendar.firstDay,
      monthNames: cultureData.calendar.months.names,
      monthNamesShort: cultureData.calendar.months.names,
      dayNames: cultureData.calendar.days.names,
      dayNamesShort: cultureData.calendar.days.namesAbbr,
      dayNamesMin: cultureData.calendar.days.namesShort,
      currentText: __('Today'),
      closeText: __('Close'),
      nextText: __('Next'),
      prevText: __('Previous'),
      hideIfNoPrevNext: true,
      changeYear: false,
      changeMonth: false,
      showOtherMonths: false
    };
  };

  /**
   * Returns default datepicker options
   *
   * @param {String} [culture] User culture
   * @return {Object} Array of options
   * @name getDateTimePickerOptions
   * @function
   * @methodOf Application
   */
  Application.getDateTimePickerOptions = function(culture)
  {
    var cultureData = Globalize.culture(culture ? culture : Config.get('culture'));
    return $.extend({}, Application.getDatePickerOptions(), {
      timeOnlyTitle: __('Choose time'),
      timeText: __('Time'),
      hourText: __('Hours'),
      minuteText: __('Minutes'),
      secondText: __('Seconds'),
      millisecText: __('Miliseconds'),
      timezoneText: __('Timezone'),
      currentText: __('Now'),
      closeText: __('Close'),
      timeFormat: cultureData.calendar.patterns['t'],
      amNames: cultureData.calendar.AM,
      pmNames: cultureData.calendar.PM,
      isRTL: false,
      changeYear: false,
      changeMonth: false,
      showOtherMonths: false
    });
  };

  /**
   * Setups time widgets
   *
   * @param {DOM element} [context] Context
   * @requires JsAPI
   * @requires jQueryUI
   */
  Application.setupTimeWidgets = function(context)
  {

  };

  /**
   * Setups datewidgets
   *
   * @param {DOM element} [context] Context
   * @requires JsAPI
   * @requires jQueryUI
   */
  Application.setupDateWidgets = function(context)
  {
    var dateWidgets =  $('input.date', context).not('.hasDatePicker');

    if(!dateWidgets.length)
    {
      return;
    }

    // date picker inputs
    use_package('ui', function()
    {
      dateWidgets.each(function()
      {
        var that = $(this);
        that.datepicker(Application.getDateWidgetOptions(that));
      });
    });
  };

  /**
   * Returns options for date widget using data attribute
   * "data-datepicker-options" of the $element and default options.
   *
   * @description
   * Initialize the widget with the options from the element,
   * defaulting to an empty object if the data attribute isn't set.
   *
   * @example
   *  &lt;input type="text" class="date" data-datepicker-options="{format:'d.m.Y'}" /&gt;
   *
   * @param {jQuery object} $element JQuery object
   * @returns {Object} Array of options for the $element
   * @see getDatePickerOptions()
   */
  Application.getDateWidgetOptions = function($element)
  {
    var options = $.extend({}, Application.getDatePickerOptions(), $element.data('datepickerOptions') || {});
    // evaluate the expression
    if(options.minDate)
    {
      try { options.minDate =  eval('(' + options.minDate + ')'); }
      catch(e) {}
    }
    if(options.maxDate)
    {
      try { options.maxDate =  eval('(' + options.maxDate + ')'); }
      catch(e) {}
    }

    return options;
  };

  /**
   * Setups widgets for date -> will be converted to datepicker
   *
   * @param {DOM element} [context] Context
   * @requires jQueryUI
   */
  Application.setupDateTimeWidgets = function(context)
  {
    var dateTimeInputs = $('input.datetime', context).not('.hasDatePicker');

    // are there any datetimepickers?
    if(!dateTimeInputs.length)
    {
      return;
    }

    // we load datetime_picker package
    use_package('ui', function()
    {
      // callback to be called when all assets from the package are loaded
      dateTimeInputs.each(function()
      {
        var options = $.extend({}, Application.getDateTimePickerOptions(), $(this).data('datetimepickerOptions') || {});
        // evaluate the expression
        if(options.minDate)
        {
          try { options.minDate =  eval('(' + options.minDate + ')'); }
          catch(e) {}
        }
        if(options.maxDate)
        {
          try { options.maxDate =  eval('(' + options.maxDate + ')'); }
          catch(e) {}
        }

        // create datetimepicker
        $(this).datetimepicker(options);

      });
    });
  };

  /**
   * Setups widget for number inputs
   * @param {DOM element} [context] Context
   * @requires Config
   */
  Application.setupNumberWidgets = function(context)
  {
    var numberInputs = $('input.number,input.integer,input.price', context);

    if(!numberInputs.length)
    {
      return;
    }

    // date picker inputs
    use_package('ui', function()
    {
      numberInputs.each(function()
      {
        var culture = $(this).data('culture') ? $(this).data('culture') : Config.get('culture');
        // fix for globalize culture format cs-CZ is Sift's cs_CZ
        culture = culture.replace('_', '-');
        $(this).spinner($.extend({
          culture : culture
        }, $(this).data('spinnerOptions') || {}));
      });
    });
  };

  if(typeof window.Application === 'undefined')
  {
    window.Application = Application;
  }

}(window.Application));