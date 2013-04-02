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
 * X-editable allows to create editable elements on your page. It can be used with any engine
 * (bootstrap, jquery-ui, jquery only) and includes both popup and inline modes.
 * See {@link http://vitalets.github.com/x-editable/}
 *
 * @name Xeditable
 * @requires jQuery
 * @requires jQueryUI
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
   * @see Application.setupForms()
   */
  Application.behaviors.coreSetupForms = function(context)
  {
    Application.setupForms(context);
  };

  /**
   * Behavior for editable actions.
   *
   * @param {DOM element} context Context
   * @memberOf Application.behaviors
   * @see Application.setupEditableActions()
   */
  Application.behaviors.coreSetupEditableActions = function(context)
  {
    Application.setupEditableActions(context);
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
    Application.setupTextareas(context);
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

  /**
   * Returns an array of editable options for an element
   *
   * @param {jQuery object} $element
   * @returns {options} Array of options
   */
  Application.getEditableOptions = function($element)
  {
    var options = {
      // where is the widget placed?
      // https://github.com/twitter/bootstrap/issues/1411
      placement: Application.widgetPlacement,
      // bootstrap popover placement
      container: 'body',
      mode:      'popup',
      emptytext: __('Empty')
    };

    var type = $element.data('type');

    // we have to decide what options:
    // see: http://vitalets.github.com/x-editable/docs.html
    // text|textarea|select|date|checklist
    switch(type)
    {
      case 'date':
        options.datepicker = Application.getDateWidgetOptions($element);
      break;
    }

    return options;
  };

  /**
   * On save callback for editable. If server returned new value it will be
   * replaced.
   *
   * @param {jQuery event} e
   * @param {Object} params Parameters
   */
  Application.editableOnSaveCallback = function(e, params)
  {
    // assuming server response: '{success: true}'
    if(params.response && params.response.success)
    {
      // we have a value from server, lets update it
      if(typeof params.response.value !== 'undefined')
      {
        // this is date object
        if(params.newValue instanceof Date)
        {
          params.newValue = new Date(params.response.value);
        }
        else
        {
          params.newValue = params.response.value;
        }
      }
    }
  };

  /**
   * Setup editable features using X-editable
   *
   * @param {DOM element} context
   * @requires JqueryUI
   * @requires Xeditable
   */
  Application.setupEditableActions = function(context)
  {
    $('a.editable-action', context).each(function()
    {
      var $element = $(this);
      // prepare options for X-editable
      var options = $.extend({}, Application.getEditableOptions($element), $element.data('editableOptions') || {});
      $element.editable(options).on('save', Application.editableOnSaveCallback);
    });
  };

  /**
   * Returns options for rich editor. Should be implemented in custom extension
   * to Application javascript or inside sfJsApi module. See @js_form_setup route (and sfJsApi module)
   * which generates the configuration dynamically from rich_editor.yml configuration file.
   *
   * @returns {Object}
   */
  Application.getRichEditorOptions = Application.getRichEditorOptions || function()
  {
    return {};
  };

  /**
   * Rich editor setup for $element with given options. Default editor is CKEDITOR, 
   * but can be customized to use any available editor. 
   * See the documentation for more information on this topic.
   *
   * @param {jQuery object} $element
   * @param {Object} options Array of options
   */
  Application.setupRichEditor = function($element, options)
  {
    use_package('editor', function()
    {
      var editor = CKEDITOR.replace($element.get(0), options);
      
      // this is triggered inside
      // validation ErrorPlacement function
      // see sfFormJavascriptValidation::getErrorPlacementExpression()      
      $element.on('myfocus.from_error_label', function(e, label)
      {
        editor.focus();
        if(label)
        {
          label.hide();
        }
      });

      // Focus on the editor when corresponding label has been clicked
      $('label[for="' + $element.attr('name') + '"]').click(function(e)
      {
        editor.focus();
      });

      editor.on('afterCommandExec', function(e)       
      {
        e.editor.updateElement();
        $element.trigger('change');
      });
      
      editor.on('blur', function(e)
      {
        $element.trigger('blur');
      });

    });    
  };

  /**
   * Setup textareas to be replaced with CKEditor if requested.
   *
   * @param {DOM element} context
   * @requires CKEditor
   */
  Application.setupTextareas = function(context)
  {
    $('textarea.rich', context).each(function()
    {
      var $element = $(this);
      // options for editor
      var options = $.extend({}, Application.getRichEditorOptions(), $element.data('editorOptions') || {});
      Application.setupRichEditor($element, options);
    });
  };

  if(typeof window.Application === 'undefined')
  {
    window.Application = Application;
  }

}(window.Application));