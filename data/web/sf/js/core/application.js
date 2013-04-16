/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Main application class, provides various methods for application specific tasks.
 *
 * @class
 * @static
 * @name Application
 * @requires jQuery
 * @requires Cookie
 */
(function($, window, Application) {

  /**
   * UX behaviors
   *
   * @memberOf Application
   * @name behaviors
   * @class
   * @static
   * @see attachBehaviors
   */
  Application = Application || {
    'behaviors': {}
  };

  /**
   * Set the variable that indicates if JavaScript behaviors should be applied
   *
   * @name jsEnabled
   * @memberOf Application
   */
  Application.jsEnabled = document.getElementsByTagName && document.createElement && document.createTextNode && document.documentElement && document.getElementById;

  /**
   * Attach all registered behaviors to a page element.
   *
   * Behaviors are event-triggered actions that attach to page elements, enhancing
   * default non-Javascript UIs. Behaviors are registered in the Application.behaviors
   * object as follows:
   * @code
   *    Application.behaviors.behaviorName = function() {
   *      ...
   *    };
   * @endcode
   *
   * Application.attachBehaviors is added below to the jQuery ready event and so
   * runs on initial page load. Developers implementing AHAH/AJAX in their
   * solutions should also call this function after new page content has been
   * loaded, feeding in an element to be processed, in order to attach all
   * behaviors to the new content.
   *
   * Behaviors should use a class in the form behaviorName-processed to ensure
   * the behavior is attached only once to a given element. (Doing so enables
   * the reprocessing of given elements, which may be needed on occasion despite
   * the ability to limit behavior attachment to a particular element.)
   *
   * @param {DomElement} context
   *   An element to attach behaviors to. If none is given, the document element
   *   is used.
   * @static
   */
  Application.attachBehaviors = function(context)
  {
    context = context || document;
    if(Application.jsEnabled)
    {
      // Execute all of them.
      $.each(Application.behaviors, function() {
        this(context);
      });
    }
  };

  /**
   * Get window width minus scrollbars width
   *
   * @returns {Integer} Returns window width in pixels
   */
  Application.getWindowWidth = function() {

    var width = $(window).width();
    // firefox substracts scrollbar automatically
    // var is_chrome = /chrome/.test( navigator.userAgent.toLowerCase());
    // if(!$.browser.mozilla/* && !is_chrome*/)
    // {
    width -= Application.getScrollbarWidth();
    //}
    return width;
  };

  /**
   * Get window height minus scrollbars height
   *
   * @returns {Integer} Returns window height in pixels
   */
  Application.getWindowHeight = function()
  {
    var height = $(window).height();
    // if(!$.browser.mozilla)
    //{
    height -= Application.getScrollbarWidth();
    //}
    return height;
  };

  Application.getHorizontalScrollbarHeight = function()
  {
    var elementHeight = $(document).height();
    var scrollPosition = $(document).height() + $(document).scrollTop();
    return (elementHeight == scrollPosition);
  }

  var scrollbarWidth = 0;

  /**
   * Returns scrollbar width
   *
   * @author Brandon Aaron (brandon.aaron@gmail.com)
   * @link http://brandonaaron.net
   */
  Application.getScrollbarWidth = function()
  {
    if(!scrollbarWidth) {
      if($.browser.msie) {
        var $textarea1 = $('<textarea cols="10" rows="2"></textarea>')
        .css({
          position: 'absolute',
          top: -1000,
          left: -1000
        }).appendTo('body'),
        $textarea2 = $('<textarea cols="10" rows="2" style="overflow: hidden;"></textarea>')
        .css({
          position: 'absolute',
          top: -1000,
          left: -1000
        }).appendTo('body');
        scrollbarWidth = $textarea1.width() - $textarea2.width();
        $textarea1.add($textarea2).remove();
      } else {
        var $div = $('<div />')
        .css({
          width: 100,
          height: 100,
          overflow: 'auto',
          position: 'absolute',
          top: -1000,
          left: -1000
        })
        .prependTo('body').append('<div />').find('div')
        .css({
          width: '100%',
          height: 200
        });
        scrollbarWidth = 100 - $div.width();
        $div.parent().remove();
      }
    }
    return scrollbarWidth;
  };

  /**
   * Encode special characters in a plain-text string for display as HTML.
   */
  Application.checkPlain = function(str) {
    str = String(str);
    var replace = {
      '&': '&amp;',
      '"': '&quot;',
      '<': '&lt;',
      '>': '&gt;'
    };
    for (var character in replace) {
      var regex = new RegExp(character, 'g');
      str = str.replace(regex, replace[character]);
    }
    return str;
  };

  /**
   * Freeze the current body height (as minimum height). Used to prevent
   * unnecessary upwards scrolling when doing DOM manipulations.
   */
  Application.freezeHeight = function () {
    Application.unfreezeHeight();
    var div = document.createElement('div');
    $(div).css({
      position: 'absolute',
      top: '0px',
      left: '0px',
      width: '1px',
      height: $('body').css('height')
    }).attr('id', 'freeze-height');
    $('body').append(div);
  };

  /**
   * Unfreeze the body height
   */
  Application.unfreezeHeight = function () {
    $('#freeze-height').remove();
  };

  /**
   * Get the text selection in a textarea.
   */
  Application.getSelection = function (element) {
    if (typeof(element.selectionStart) != 'number' && document.selection) {
      // The current selection
      var range1 = document.selection.createRange();
      var range2 = range1.duplicate();
      // Select all text.
      range2.moveToElementText(element);
      // Now move 'dummy' end point to end point of original range.
      range2.setEndPoint('EndToEnd', range1);
      // Now we can calculate start and end points.
      var start = range2.text.length - range1.text.length;
      var end = start + range1.text.length;
      return {
        'start': start,
        'end': end
      };
    }
    return {
      'start': element.selectionStart,
      'end': element.selectionEnd
      };
  };

  /**
   * Alerts information to the user.
   *
   * Rich version of builtin window.alert() function.
   *
   * @param string message Any valid HTML code to be displayed
   * @param string title of the modal box (default to "Alert")
   * @return void
   *
   **/
  Application.alert = function(message, title)
  {
    // do we have $ ui "modal" loaded?
    if($.isFunction($.fn.dialog))
    {
      $('body').append('<div id="application-dialog" />');
      $('#application-dialog').html('<div>' + message + '</div>').dialog(
      {
        dialogClass:  'ui-dropshadow',
        autoOpen:    true,
        minHeight:   50,
        modal:       true,
        closeText:   __('close'),
        resizable:   false,
        title:       (title ? title : __('Alert')),
        buttons: [
        {
          text: __('Ok'),
          click: function()
          {
            $(this).dialog('close');
            $('#application-dialog').remove();
          }
        }
        ]
      });
    }
    else
    {
      // fallback: default alert
      window.alert(message);
    }
  };

  /**
   * Displays confirmation
   *
   * Rich version of builtin window.alert() function.
   *
   * @param string message Any valid HTML code to be displayed
   * @param function callback Callback function which will be called when confirmed
   * @param string title of the modal box (default to "Confirmation")
   * @return void
   **/
  Application.confirm = function(message, callback, title)
  {
    // do we have jquery ui loaded?
    if($.isFunction($.fn.dialog))
    {
      var applicationDialog = $('#application-dialog');
      if(!applicationDialog.length)
      {
        applicationDialog = $('<div id="application-dialog" />');
        $('body').append(applicationDialog);
      }
      // save the callback for future use
      applicationDialog.data('callback', callback);
      applicationDialog.html('<div>' + message + '</div>').dialog(
      {
        dialogClass: 'ui-dropshadow',
        closeOnEscape: true,
        autoOpen: true,
        minHeight: 50,
        modal: true,
        closeText: __('close'),
        resizable: false,
        title: (title ? title : __('Confirmation')),
        buttons: [
        {
          text: __('Ok'),
          'class': 'btn btn-primary',
          click: function()
          {
            var that = $(this);
            var callback = $(this).data('callback');
            that.dialog('close');
            that.removeData('callback').remove();
            if($.isFunction(callback))
            {
              callback.apply();
            }
          }
        },
        {
          text: __('Cancel'),
          'class': 'btn',
          click: function()
          {
            $(this).dialog('close');
            applicationDialog.remove();
          }
        }
        ]
      }
    );
    }
    // fallback if jqueryui is not loaded, use builtin ugly confirmation
    else
    {
      var result = window.confirm(message);
      if(result)
      {
        if($.isFunction(callback))
        {
          callback.apply();
        }
      }
    }
  };

  /**
   * Behavior for confirmation links. You can make links to be confirmed by user
   * simply by adding "confirm" class to the link. If you provide "data-confirm"
   * HTML5 attribute the text from this attribute will be shown to the user. When no
   * data-confirm attribute is present default message will be used.
   *
   * @param {DOM element} context
   */
  Application.setupConfirmLinks = function(context)
  {
    $('a.confirm', context).click(function(e)
    {
      var that = $(this);
      var confirm = that.data('confirm') || __('Are you sure?')
      // FIXME: make icons configurable?
      Application.confirm('<i class="icon-exclamation-sign icon-large"></i> ' + confirm, function()
      {
        // we have a target
        var target = that.prop('target');
        if(target)
        {
          window.open(that.attr('href'), target);
        }
        else
        {
          window.location = that.attr('href');
        }
      });
      e.preventDefault();
    });
  };

  /**
   * Setup external links (will be opened in new window)
   *
   * @param {DOM element} context
   */
  Application.setupExternalLinks = function(context)
  {
    $('a.external', context).click(function(e)
    {
      $(this).prop('target', 'external');
    });
  };

  /**
   * Setup links
   *
   * @param {DOM element} context Context
   * @memberOf Application.behaviors
   * @see Application.coreSetupLinks()
   */
  Application.behaviors.coreSetupLinks = function(context)
  {
    Application.setupConfirmLinks(context);
    Application.setupExternalLinks(context);
  };

  /**
   * Detects timezone based on user time
   *
   * @see http://contagious.nu/2009/12/detecting-timezone-with-javascript-and-php/
   */
  Application.detectTimezone = function()
  {
    if(Cookie.get('timezone_offset'))
    {
      return;
    }

    var rightNow = new Date();
    var jan1 = new Date(rightNow.getFullYear(), 0, 1, 0, 0, 0, 0);
    var temp = jan1.toGMTString();
    var jan2 = new Date(temp.substring(0, temp.lastIndexOf(" ")-1));
    var std_time_offset = (jan1 - jan2) / (1000 * 60 * 60);

    var june1 = new Date(rightNow.getFullYear(), 6, 1, 0, 0, 0, 0);
    temp = june1.toGMTString();
    var june2 = new Date(temp.substring(0, temp.lastIndexOf(" ")-1));
    var daylight_time_offset = (june1 - june2) / (1000 * 60 * 60);
    var dst;
    if(std_time_offset == daylight_time_offset)
    {
      dst = '0'; // daylight savings time is NOT observed
    }
    else
    {
      dst = '1'; // daylight savings time is observed
    }

    Cookie.set('timezone_offset', daylight_time_offset);
    Cookie.set('timezone_daylightsavings', dst);
  };

  /**
   * Detect if user is accessing the application by mobile device
   *
   * @returns {Boolean} True if yes, false otherwise
   */
  Application.browserIsMobile = function()
  {
    return /mobile/i.test(navigator.userAgent);
  };

  /**
   * Refreshes Cufon.
   *
   * @see Cufon.refresh();
   * @requires Cufon
   * @deprecated Will be removed from core
   */
  Application.refreshCufon = function()
  {
    if(typeof Cufon === 'object')
    {
      Cufon.refresh();
    }
  };

  /**
   * Setups ajax requests. Using following configuration:
   * Handles 500, 401 and 403 error codes.
   *
   * * ajax_timeout (default -1 = no timeout)
   *
   */
  Application.setupAjax = function()
  {
    $.ajaxSetup({
      timeout: Config.get('ajax_timeout', -1)
    });

    $(document)
      .bind('ajaxStop', function() { $('body').removeClass('loading'); })
      .bind('ajaxStart', function() { $('body').addClass('loading'); })
      .bind('ajaxError', function(e, jqxhr, settings, exception)
      {
        var textStatus = jqxhr.statusText;
        // catch errors like: parsererror, error, timeout
        if(textStatus == 'parsererror' ||
           textStatus == 'error' ||
           textStatus == 'timeout')
        {
          // HTTP response code
          var responseCode = jqxhr.status;
          var url          = settings.url;
          var cookies      = document.cookie;

          Logger.logToServer({
            error:  'Ajax error',
            status: textStatus,
            url: url,
            responseText: jqxhr.responseText,
            code: responseCode,
            agent: navigator.userAgent,
            cookies: cookies,
            referer: document.referrer,
            jqueryVersion: $.fn.jquery
          });
        }

        if(textStatus == 'timeout')
        {
          Application.alert(__('The server is too busy at the moment.'));
          return;
        }

        var result = false;
        try
        {
          result = $.parseJSON(jqxhr.responseText);
        }
        catch(e)
        {
          result = false;
        }

        if(jqxhr.status == 500)
        {
          if(result && result.html)
          {
            Application.alert(result.html);
          }
        }
        // not authorized
        else if(jqxhr.status == 401)
        {
          if(result && result.html)
          {
            if(result.redirect)
            {
              Application.confirm(result.html, function()
              {
                window.location.href = result.redirect;
              });
            }
            else
            {
              Application.alert(result.html);
            }
          }
          else // fallback
          {
            var url = Config.get('login_url');
            if(url)
            {
              Application.confirm(__('This action requires to be logged in and you are not logged in. Redirect to login page?'), function()
              {
                window.location.href = url;
              });
            }
            else
            {
              Application.alert(__('This action requires to be logged in and you are not logged in. Please login.'));
            }
          }
        }
      });

  };

  /**
   * Setup application
   *
   */
  Application.setup = function()
  {
    // Global Killswitch on the <html> element
    if(Application.jsEnabled)
    {
      // Global Killswitch on the <html> element
      $(document.documentElement).addClass('js');

      // setup ajax
      Application.setupAjax();

      // Attach all behaviors.
      $(document).ready(function()
      {
        var domain = Config.get('cookie_domain');
        var path   = Config.get('cookie_path');
        Cookie.set('has_js', 1, '', path, domain);
        // detect timezone
        Application.detectTimezone();
        // attach behaviours
        Application.attachBehaviors(this);
      });
    };

  };

  if(typeof window.Application === 'undefined')
  {
    window.Application = Application;
  }

  Application.setup();

}(window.jQuery, window, window.Application));
