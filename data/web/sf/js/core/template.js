/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Handlebars provides the power necessary to let you build semantic templates effectively with no frustration.
 * Mustache templates are compatible with Handlebars, so you can take a Mustache template, import it into Handlebars, and start taking advantage of the extra Handlebars features.
 *
 * @name Handlebars
 * @version 1.0.0
 * @class
 */

(function(window, $, Handlebars)
{
  'use strict';

  /**
   * Holds templates
   *
   * @type Array
   */
  var templateMap = {};

  /**
   * Default options
   *
   * @type Object
   */
  var options = {
    // Should an error be thrown if an attempt is made to render a non-existent template.  If false, the
    // operation will fail silently.
    warnOnMissingTemplates: true,
    // Should an error be thrown if an attempt is made to overwrite a template which has already been added.
    // If true the original template will be overwritten with the new value.
    allowOverwrite: true,
    // The 'type' attribute which you use to denoate a Handlebars/Mustache Template in the DOM; eg:
    // `<script type="text/html" id="my-template"></script>`
    domTemplateTypes: ['text/html', 'text/x-handlebars-template'],
    // Specifies the `dataType` attribute used when external templates are loaded.
    externalTemplateDataType: 'text'
  };

  /**
   * Returns true if the supplied templateName has been added.
   *
   * @param {String} templateName name of the template
   * @name has
   * @memberOf Template
   */
  function has(templateName)
  {
    return templateMap[templateName] !== void 0;
  };

  /**
   * Registers a template so that it can be used by render method.
   *
   * @param {String} templateName A name which uniquely identifies this template.
   * @param {String|Function} tpl The HTML (or precompiled function) which makes us the template; this will be rendered by Handlebar when render() is invoked.
   * @throws If options.allowOverwrite is false and the templateName has already been registered.
   * @name add
   * @memberOf Template
   */
  function add(templateName, tpl)
  {
    if(!options.allowOverwrite && has(templateName))
    {
      throw new Exception('TemplateName: ' + templateName + ' is already mapped.');
    }

    var compiled = false;
    // this is a compiled template
    if(typeof tpl === 'function')
    {
      compiled = true;
    }
    else
    {
      tpl = $.trim(tpl);
    }

    templateMap[templateName] = {
      compiled: compiled,
      tpl : tpl
    };
  };

  /**
   * Adds one or more templates from the DOM using either the supplied templateElementIds or by retrieving all script
   * tags of the 'domTemplateTypes'.  Templates added in this fashion will be registered with their elementId value.
   *
   * @param [...templateElementIds] List of element id's present on the DOM which contain templates to be added;
   * if none are supplied all script tags that are of the same type as the `options.domTemplateTypes`
   * configuration value will be added.
   *
   * @name addFromDom
   * @memberOf Template
   */
  function addFromDom()
  {
    var templateElementIds;

    // If no args are supplied, all script blocks will be read from the document.
    if(arguments.length === 0)
    {
      // prepare expression
      var searchExp = [];
      for(var i = 0; i < options.domTemplateTypes.length; i++)
      {
        searchExp.push('script[type="' + options.domTemplateTypes[i] + '"]');
      }

      templateElementIds = $(searchExp.join(',')).map(function()
      {
        return this.id;
      });
    }
    else
    {
      templateElementIds = $.makeArray(arguments);
    }

    $.each(templateElementIds, function()
    {
      var templateElement = document.getElementById(this);

      if(templateElement === null)
      {
        throw new Exception('No such elementId: #' + this);
      }
      else
      {
        add(this, $(templateElement).html());
      }
    });
  };

  /**
   * Removes a template, the removed Template object will be returned.
   *
   * @param {String} templateName The name of the previously registered template that you wish to remove.
   * @returns {Object} which represents the the template.
   *
   * @name remove
   * @memberOf Template
   */
  function remove(templateName)
  {
    var result = templateMap[templateName];
    delete templateMap[templateName];
    return result;
  };

  /**
   * Removes all templates.
   *
   * @name clear
   * @memberOf Template
   */
  function clear()
  {
    templateMap = {};
  };

  /**
   * Renders a previously added template using the supplied templateData object.  Note if the supplied
   * templateName doesn't exist an empty String will be returned.
   *
   * @param {String} templateName Template name to render
   * @param {Object} templateData One or more JavaScript objects which will be used to render the template.
   * @name render
   * @memberOf Template
   */
  function render(templateName, templateData)
  {
    if(!has(templateName))
    {
      if(options.warnOnMissingTemplates)
      {
        throw new Exception('No template registered for: ' + templateName);
      }
      return '';
    }

    var tpl;

    if(!templateMap[templateName].compiled)
    {
      // we don't care about compilation caching, since it is cached by Handlebars
      tpl = Handlebars.compile(templateMap[templateName].tpl);
    }
    else
    {
      // see: http://yuilibrary.com/yui/docs/api/classes/Handlebars.html#method_template
      tpl = Handlebars.template(templateMap[templateName].tpl);
    }

    return tpl(templateData);
  };

  /**
   * Loads the external templates located at the supplied URL and registers them for later use. This method
   * returns a jQuery Promise and also support an `onComplete` callback.
   *
   * @param {String} url URL of the external template file to load.
   * @param {Function} onComplete Optional callback function which will be invoked when the templates from the supplied URL have been loaded and are ready for use.
   * @returns {jQuery Object} deferred promise which will complete when the templates have been loaded and are ready for use.
   * @name load
   * @memberOf Template
   */
  function load(url, onComplete)
  {
    return $.ajax({
      url: url,
      dataType: options.externalTemplateDataType
    }).done(function(templates)
    {
      $(templates).filter('script').each(function(i, el)
      {
        add(el.id, $(el).html());
      });
      if($.isFunction(onComplete))
      {
        onComplete();
      }
    });
  }

  /**
   * Returns an array of template names which have been registered and can be retrieved via
   * Template.render() or $(element).template().
   *
   * @name templates
   * @memberOf Template
   */
  function templates()
  {
    return $.map(templateMap, function(value, key)
    {
      return key;
    });
  }

  /**
   * Javascript templating made easy
   *
   * @class
   * @name Template
   * @requires Handlebars
   * @link https://github.com/jonnyreeves/jquery-Mustache
   */
  var Template = function()
  {
    return {
      options: options,
      load: load,
      add: add,
      addFromDom: addFromDom,
      remove: remove,
      clear: clear,
      render: render,
      templates: templates
    };
  }();

  // export to window
  window.Template = Template;

  /**
   * Renders one or more viewModels into the current jQuery element.
   *
   * @param {String} templateName The name of the template you wish to render, Note that the template must have been previously loaded and / or added.
   * @param {Object} templateData One or more JavaScript objects which will be used to render the template.
   * @param {Object} options Array of rendering options
   * @class
   * @memberOf jQuery.fn
   * @requires Template
   */
  $.fn.template = function(templateName, templateData, options)
  {
    var settings = $.extend({
      method: 'append'
    }, options);

    /**
     * @ignore
     */
    var renderTemplate = function(obj, viewModel)
    {
      $(obj)[settings.method](render(templateName, viewModel));
    };

    return this.each(function()
    {
      var element = this;
      // Render a collection of viewModels.
      if ($.isArray(templateData))
      {
        $.each(templateData, function()
        {
          renderTemplate(element, this);
        });
      }
      // Render a single viewModel.
      else
      {
        renderTemplate(element, templateData);
      }
    });
  };

  // this is a custom event which is
  // triggered before attaching application behaviors
  $(window).bind('setup', function()
  {
    Template.addFromDom();
  });

}(window, window.jQuery, window.Handlebars));