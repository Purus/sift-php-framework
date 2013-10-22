/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @class WebDebug
 */
(function($, window)
{
  /**
   * Adds a filter to the filters stack
   *
   * @param {Array} filters
   * @param {String} attribute
   * @param {String} value
   * @returns {Array}
   */
  function addFilter(filters, attribute, value)
  {
    var found = false;
    for(var i = 0; i < filters.length; i++)
    {
      if(filters[i].attribute === attribute)
      {
        found = i;
        break;
      }
    }
    if(found === false)
    {
      filters.push({
        attribute: attribute,
        value: value
      });
    }
    else
    {
      filters[found] = {
        attribute: attribute,
        value: value
      };
    }
    return filters;
  };

  /**
   * Filters the element based on the filtefCOOrs
   *
   * @param {type} element
   * @param {type} filters
   * @returns {_L11.filterRowCallback.result|Boolean}
   */
  function filterRowCallback(element, filters)
  {
    var $element = $(element);
    var result = true;
    for(var i = 0; i < filters.length; i++)
    {
      var f = filters[i];
      // handle special filters
      switch(f.attribute)
      {
        case '__contains__':
          if(f.value === '')
          {
            // skip
            continue;
          }
          else
          {
            var search = f.value.toLowerCase().split(' ');
            var text = $element.find('.web-debug-log-message-holder').text().
                    toLowerCase();
            for(var s = 0; s < search.length; ++s)
            {
              if(text.indexOf(search[s]) === -1)
              {
                result = false;
              }
            }
          }
          break;

        default:
          var v = $element.data('log' + WebDebug.Util.ucFirst(f.attribute));
          if(f.value === '__all__')
          {
            continue;
          }
          else if(v && v !== f.value)
          {
            result = false;
          }
          break;
      }
    }
    return result;
  };

  //~//BASE64//~//
  //~//COOKIE//~//
  //~//LOCAL_STORAGE//~//
  //~//HIGHLIGHTER//~//

  var WebDebug = function(debugHtml, options)
  {
    this.options = $.extend(options || {}, WebDebug.defaultOptions);

    var debugHtml = Base64.decode(debugHtml);

    this.$element = $('#web-debug');

    this.$element.html(debugHtml);

    this.$toolbar = $('#web-debug-toolbar');
    this.$toolbarPanels = this.$toolbar.find('a');
    this.$toolbarTogglers = this.$toolbar.find('a.web-debug-toolbar-toggler');
    this.$panels = this.$element.find('.web-debug-panel');
    this.$status = this.$element.find('#web-debug-toolbar-status');

    if(WebDebug.Storage.get('closed'))
    {
      this.closeToolbar();
    }

    this._setup();
    this._setupDataFilters();
    this._setupTogglers();

    var debug = this;
    // initialize the extensions
    $.each(WebDebug.Extensions, function(index, extension)
    {
      window.setTimeout(function()
      {
        extension.setup(debug);
      }, 0);
    });

  };

  /**
   * Closes the toolbar
   */
  WebDebug.prototype.closeToolbar = function()
  {
    this.$toolbar.addClass('closed');
    this.$panels.hide();
    this.$toolbarPanels.removeClass('active');
    // hide toolbar items except logo and status!
    this.$toolbar.find('ul > li')
                    .not('#web-debug-logo')
                    .not('#web-debug-toolbar-status')
                    .hide();
    this.$toolbar.trigger('closed');
  };

  /**
   * Shows the toolbar
   */
  WebDebug.prototype.openToolbar = function()
  {
    this.$toolbar.removeClass('closed');
    this.$toolbar.find('ul > li')
                    .not('#web-debug-logo')
                    .not('#web-debug-toolbar-status')
                    .show();
    this.$toolbar.trigger('opened');
  };

  /**
   * Is the toolbar visible?
   *
   * @returns {Boolean}
   */
  WebDebug.prototype.isToolbarClosed = function()
  {
    return this.$toolbar.hasClass('closed');
  };

  /**
   * Filter for filtering table rows
   *
   */
  WebDebug.prototype.filter = [];

  /**
   * Default options
   *
   */
  WebDebug.defaultOptions = {
    'cookie': {
      name: 'web_debug' // name of the cookie
    },
    'htmlValidator' : {
      url: 'http://html5.validator.nu/'
    }
  };

  /**
   * Warning status
   */
  WebDebug.STATUS_WARNING = 'warning';

  /**
   * Error status
   */
  WebDebug.STATUS_ERROR = 'error';

  /**
   * Status ok
   */
  WebDebug.STATUS_OK = 'ok';

  /**
   * Constructor
   */
  WebDebug.prototype.constructor = WebDebug;

  /**
   * Setups the web debug
   */
  WebDebug.prototype._setup = function()
  {
    var that = this;
    //  setup toolbar panels
    this.$toolbarPanels.click(function(e)
    {
      var $this = $(this);
      var panel = $('#web-debug-panel-' + $this.data('panel'));
      if(!panel.length)
      {
        return;
      }
      that.$toolbarPanels.removeClass('active');
      // panel is not visible
      if(!panel.is(':visible'))
      {
        that.$panels.hide();
        $this.addClass('active');
        panel.show();
      }
      else
      {
        that.$panels.hide();
      }

      e.preventDefault();
    });

    /**
     * Toolbar togglers - opens / closes the toolbar
     *
     */
    this.$toolbarTogglers.click(function(e)
    {
      e.preventDefault();
      if(that.isToolbarClosed())
      {
        that.openToolbar();
        WebDebug.Storage.remove('closed');
      }
      else
      {
        that.closeToolbar();
        WebDebug.Storage.set('closed', true);
      }
    });
  };

  WebDebug.prototype._setupDataFilters = function()
  {
    this.$panels.find('input.data-filter').keyup(function(e)
    {
      var $target = $(this).parents('table:first');
      var filters = addFilter($target.data('selectiveFilter') || [],
              '__contains__', this.value);
      $target.data('selectiveFilter', filters);

      // we do not want nested tables
      // http://stackoverflow.com/questions/3087412/jquery-select-table-cells-without-selecting-from-a-nested-table
      $target.children('tbody').children('tr')
              .hide().filter(function(index, element)
      {
        return filterRowCallback(element, filters);
      }).show();
    });

    this.$panels.find('a.data-filter').click(function(e)
    {
      var $this = $(this);
      var filter = $this.data('filter');
      if(!filter)
      {
        return;
      }

      var $target = $('#' + filter.target);
      if(!$target.length)
      {
        return;
      }

      $this.parents('ul:first').find('a.data-filter')
              .removeClass('active');
      $this.addClass('active');

      var $rows = $target.children('tbody').children('tr');

      var filters = addFilter($target.data('selectiveFilter')
              || [], filter.attribute, filter.value);
      $target.data('selectiveFilter', filters);

      $rows.hide().filter(function(index, element)
      {
        return filterRowCallback(element, filters);
      }).show();

      e.preventDefault();
    });
  };

  WebDebug.prototype._setupTogglers = function()
  {
    this.$panels.find('a.web-debug-toggler,a.web-debug-backtrace-toggler').
            click(function(e)
    {
      var $this = $(this);
      var target = $this.data('target');

      // this is not a link to id nor to class
      if(target.indexOf('#') === -1 && target.indexOf('.') === -1)
      {
        var $target = $this.parent().find('.' + target + ':first');
      }
      else
      {
        var $target = $(target + ':first');
      }

      if($target.length)
      {
        $target.toggleClass('hidden');
        $this.toggleClass('active');
      }
      e.preventDefault();
    });
  };

  /**
   *
   * @param {String} status
   * @returns
   */
  WebDebug.prototype.setStatus = function(status)
  {
    switch(status)
    {
      case WebDebug.STATUS_OK:
        this.$status.addClass('success');
      break;

      case WebDebug.STATUS_WARNING:
        this.$status.addClass('warning');
      break;

      case WebDebug.STATUS_ERROR:
        this.$status.addClass('error');
      break;
    }
  };

  // Utilities
  WebDebug.Util = {};

  /**
   * Ucfirst the string
   *
   * @param {strinh} string
   * @returns {String}
   */
  WebDebug.Util.ucFirst = function(string)
  {
    return string.charAt(0).toUpperCase() + string.slice(1);
  };

  /**
   * Escapes HTML
   *
   * @param {String} text
   * @returns string
   * @link http://stackoverflow.com/questions/1787322/htmlspecialchars-equivalent-in-javascript
   */
  WebDebug.Util.escapeHtml = function(text)
  {
    return text.replace(/&/g, "&amp;").replace(/</g, "&lt;")
            .replace(/>/g, "&gt;").replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
  };

  // Provide jQuery to extensions
  WebDebug.$ = $;
  // Extensions list
  WebDebug.Extensions = {};
  // Code highlighter
  WebDebug.Highlighter = Highlighter;
  // Base64 encoder/decoder
  WebDebug.Base64 = Base64;
  // Cookie (required for LocalStorage fallback)
  WebDebug.Cookie = Cookie;
  // Storage
  WebDebug.Storage = new LocalStorage('_web_debug');
  // The object instance of WebDebug
  WebDebug.Instance = null;

  /**
   * Html validator wrapper for Validator.nu API service
   *
   * @param {string} content
   * @param {string} contentType
   * @param {jQuery Object} $resultHolder
   * @param {jQuery Object} $panelInfo
   * @returns {HtmlValidator}
   * @link http://wiki.whatwg.org/wiki/Validator.nu_Web_Service_Interface
   */
  var HtmlValidator = function(content, contentType, $resultHolder, $panelInfo)
  {
    this.content = WebDebug.Base64.decode(content);
    this.contentType = contentType;
    this.errors = [];
    this.url = '';
    this.$resultHolder = $resultHolder;
    this.$panelInfo = $panelInfo;
  };

  HtmlValidator.prototype = {
    constructor: HtmlValidator,
    /**
     * Setup the validator
     *
     * @param {WebDebug} WebDebugInstance
     * @returns {void}
     */
    setup: function(WebDebugInstance)
    {
      this.url = WebDebugInstance.options.htmlValidator.url;
      if(!this.url)
      {
        return;
      }
      this.makeRequest();
    },
    displayResult: function()
    {
      if(this.errors.length === 0)
      {
        this.$panelInfo.html('<span class="success">OK</span>');
      }
      else
      {
        this.$panelInfo.html('<span class="warning">'+ this.errors.length + '</span>');
        if(WebDebug.Instance)
        {
          WebDebug.Instance.setStatus(WebDebug.STATUS_WARNING);
        }
      }
      // clear
      this.$resultHolder.html('');
      var html = [];
      var lines = [];
      html.push('<table>');
      if(this.errors.length === 0)
      {
        html.push('<tr><td><h3 class="success">Html is valid.</h3></td></tr>');
      }
      else
      {
        html.push('<thead><tr><td></td><td>Error</td><td>Extract</td></tr></thead>');
        for(var i = 0, count = this.errors.length; i < count; i++)
        {
          var error = this.errors[i];
          html.push("<tr><td>#" + (i + 1) + "</td><td><strong>"
                  + error.message
                  + "</strong><br />line: " + error.lastLine + ", column:"
                  + error.lastColumn + "</td><td><pre><code>"
                  + WebDebug.Util.escapeHtml(error.extract)
                  + "</code></pre></td></tr>");
          lines.push(error.lastLine);
        }
        html.push('<tr><td colspan="3"><h2>Source</h2>');
      }

      html.push('</td></tr>');
      html.push('</table>');
      this.$resultHolder.html(html.join("\n"));
    },
    /**
     * Makes the request
     *
     * @link https://gist.github.com/xjamundx/3902535
     */
    makeRequest: function()
    {
      // not supported
      if(typeof FormData === 'undefined')
      {
        this.$panelInfo.html('<span class="">n/a</span>');
        return;
      }

      // emulate form post
      var formData = new FormData();
      formData.append('out', 'json');
      // formData.append('showsource', 'yes');
      formData.append('content', this.content);
      var that = this;

      // make ajax call
      $.ajax({
        url: that.url,
        data: formData,
        dataType: "json",
        type: "POST",
        processData: false,
        contentType: false,
        success: function(data)
        {
          var errors = [];
          for(var i = 0, count = data.messages.length; i < count; i++)
          {
            // http://wiki.whatwg.org/wiki/Validator.nu_JSON_Output#Message_objects
            var message = data.messages[i];
            if(message.type === 'error')
            {
              // this is not an error!
              if(message.message.indexOf('X-Ua-Compatible') !== -1)
              {
                continue;
              }
              errors.push(message);
            }
            that.errors = errors;
          }
          // that.validatedSource = data.source.code;
          that.displayResult();
        },
        error: function()
        {
          that.$panelInfo.html('<span class="warning">Error</span>');
        }
      });
    }

  };

  // assing to debug object
  // The extension is created afterwards
  WebDebug.HtmlValidator = HtmlValidator;

  // export
  window.WebDebug = WebDebug;

}(window.jQuery, window));
