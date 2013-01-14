/**
 * Application core javascript
 *
 * Based on javascript Drupal code
 *
 * @author Mishal.cz <mishal at mishal dot cz>
 * @author Drupal team http://www.drupal.org
 * @version SVN:$Id$
 */

var Application = Application || {
  'behaviors': {}
};

/**
 * Set the variable that indicates if JavaScript behaviors should be applied
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
 * @param context
 *   An element to attach behaviors to. If none is given, the document element
 *   is used.
 */
Application.attachBehaviors = function(context) 
{
  context = context || document;
  if(Application.jsEnabled) 
  {
    // Execute all of them.
    jQuery.each(Application.behaviors, function() {
      this(context);
    });
  }
};

/**
 * Get window width minus scrollbars width
 *
 */
Application.getWindowWidth = function() {

  var width = jQuery(window).width();  
  // firefox substracts scrollbar automatically
  // var is_chrome = /chrome/.test( navigator.userAgent.toLowerCase());

  if(!jQuery.browser.mozilla/* && !is_chrome*/)
  {
    width -= Application.getScrollbarWidth();
  }
  return width;
};

/**
 * Get window height minus scrollbars height
 *
 */
Application.getWindowHeight = function() 
{
  var height = jQuery(window).height();
  if(!jQuery.browser.mozilla)
  {
    height -= Application.getScrollbarWidth();
  }
  return height;
};

Application.getHorizontalScrollbarHeight = function()
{
  var elementHeight = jQuery(document).height();
  var scrollPosition = jQuery(document).height() + jQuery(document).scrollTop();
  return (elementHeight == scrollPosition);
}

var scrollbarWidth = 0;

/**
 * Returns scrollbar width
 *
 * @copyright (c) 2008 Brandon Aaron (brandon.aaron@gmail.com)
 * @see http://brandonaaron.net)
 *
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
Application.getScrollbarWidth = function() {
  if(!scrollbarWidth) {
    if(jQuery.browser.msie) {
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
  // do we have jquery ui "modal" loaded?
  if(jQuery.isFunction(jQuery.fn.dialog))
  {
    jQuery('body').append('<div id="application-dialog" />');
    jQuery('#application-dialog').html('<div>' + message + '</div>').dialog(
    {
      dialogClass:  'ui-dropshadow',
      autoOpen:    true,
      minHeight:   50,
      modal:       true,
      closeText:   __('close'),
      resizable:   false,
      zIndex:      1111,
      title: '<span class="ui-icon ui-icon-alert left"></span> ' + (title ? title : __('Alert')),
      buttons: [ 
      {
        text: __('Ok'),
        click: function()
        {
          jQuery(this).dialog('close');
          jQuery('#application-dialog').remove();
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
  if(jQuery.isFunction(jQuery.fn.dialog))
  {
    jQuery('body').append('<div id="application-dialog" />');
    jQuery('#application-dialog').html('<div>' + message + '</div>').dialog(
    {
      dialogClass:  'ui-dropshadow',
      closeOnEscape: true,
      autoOpen:      true,
      minHeight:     50,
      modal:         true,
      closeText: __('close'),
      resizable: false,
      zIndex:      1111,      
      title: '<span class="ui-icon ui-icon-info left"></span> ' + (title ? title : __('Confirmation')),
      buttons: [
      {
        text: __('Ok'),
        click: function() 
        { 
          jQuery(this).dialog('close');
          jQuery('#application-dialog').remove();          
          if(jQuery.isFunction(callback)) 
          {   
            callback.apply();
          }
        }
      },
      {
        text: __('Cancel'),
        click: function()
        {
          jQuery(this).dialog('close');
          jQuery('#application-dialog').remove();          
        }
      }
      ]
    });
  }
  // fallback if jqueryui is not loaded, use builtin ugly confirmation
  else
  {  
    var result = window.confirm(message);  
    if(result)
    {
      if(jQuery.isFunction(callback)) 
      {   
        callback.apply();
      }
    }
  }
};

/**
 * Detects timezone based on user 
 *
 * @see http://contagious.nu/2009/12/detecting-timezone-with-javascript-and-php/
 *
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

Application.browserIsMobile = function()
{  
  return /mobile/i.test(navigator.userAgent);
};

/**
 * Called after successfull ajax call
 *
 **/
Application.refreshCufon = function()
{  
  if(typeof Cufon != 'undefined') Cufon.refresh();  
};

/**
 * Configuration utility object
 *
 * Retrieves configuration properties
 * defined in Configuration array
 *
 */
var Config = function() {

  var configuration = {
    debug: false,
    log: false
  };
  
  return {
  
    /**
     * Adds configuration settings
     *
     * @param {Object} data
     */
    add: function(data) {
      jQuery.extend(configuration, data);
    },
    
    /**
     * Set configuration setting
     *
     * @param {Object} name
     * @param {Object} value
     */
    set: function(name, value) {
      configuration[name] = value;
    },
    
    /**
     * Retrieve configuration setting
     *
     * @param {Object} name
     * @param {Object} default_value
     */
    get: function(name, default_value) {
      if (configuration && configuration[name]) 
      {
        return configuration[name];
      }
      return default_value;
    }
  }
  
}();

/**
 * Logger
 *
 */
var Logger = function() 
{

  var isEnabled = function() 
  {
    if(Config.get('debug')) 
    {
      return true;
    }
    return false;
  };
  
  var hasConsole = function() 
  {
    // do we have console available?
    if(typeof console != 'undefined') 
    {
      return true;
    }
    return false;
  };
  
  var isLogToServerEnabled = function() 
  {
    if(Config.get('log') && Config.get('log_url')) 
    {
      return true;
    }
    return false;
  };
  
  var logToServer = function(logData) 
  {
    var log_url = Config.get('log_url');
    if(!log_url) 
    {
      return;
    }
    // make post request
    jQuery.ajax( 
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
  
  var logToServerCallback = function(response) 
  {
    // what to do ?
    // window.location = '/error.html';  
  };
  
  return {
    
    info: function() 
    {
      Logger.log(arguments);
    },
    
    log: function() 
    {
      if(isEnabled() && hasConsole()) 
      {
        console.log(arguments.length > 1 ? Array.prototype.slice.call(arguments) : arguments[0]);        
      }
    },   
    
    debug: function() 
    {
      if(isEnabled() && hasConsole()) 
      {
        console.debug(arguments.length > 1 ? Array.prototype.slice.call(arguments) : arguments[0]);        
      }
    },      
    
    error: function() 
    {
      if(isEnabled() && hasConsole()) 
      {
        console.error(arguments.length > 1 ? Array.prototype.slice.call(arguments) : arguments[0]);
      }      
    },
    
    logToServer: function(logData)
    {
      if(isLogToServerEnabled()) 
      {
        logToServer(logData);
      }      
    },
    
    varDump: function(obj) 
    {
      if(typeof obj == "object") 
      {
        return "Type: " + typeof(obj) + ((obj.constructor) ? "\nConstructor: " + obj.constructor : "") + "\nValue: " + obj;
      } 
      else 
      {
        return "Type: " + typeof(obj) + "\nValue: " + obj;
      }
    }    
  }
}();

/**
 * Tools
 *
 * various utility functions
 *
 */
var Tools = function() {

  /**
   * parseUri 1.2.2
   * 
   * @see http://blog.stevenlevithan.com/archives/parseuri
   * @copyright (c) Steven Levithan <stevenlevithan.com>
   * @license MIT License
   * 
   **/
  function parseUri(str) 
  {
    var	o = parseUri.options,
      m   = o.parser[o.strictMode ? "strict" : "loose"].exec(str),
      uri = {},
      i   = 14;

    while(i--) uri[o.key[i]] = m[i] || "";

    uri[o.q.name] = {};
    uri[o.key[12]].replace(o.q.parser, function($0, $1, $2) 
    {
      if($1) uri[o.q.name][$1] = $2;
    });

    return uri;
  };

  parseUri.options = {
    strictMode: false,
    key: ["source","protocol","authority","userInfo","user","password","host","port","relative","path","directory","file","query","anchor"],
    q:   {
      name:   "queryKey",
      parser: /(?:^|&)([^&=]*)=?([^&]*)/g
    },
    parser: {
      strict: /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
      loose:  /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/)?((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/
    }
  };
  
  return {
  
    /**
     * Build url (directory based)
     *
     * @param {Object} params
     * @param {String} argument separator (default is '/')
     * @param {String} join string (default is '/')
     */
    buildQuery: function(params, separator, join) {

      Logger.info(Logger.varDump(params));
      
      if (separator == undefined)
      {
        separator = '/';
      }
      if(join == undefined)
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

    // http://stackoverflow.com/questions/619240
    encodeHashComponent: function(x) {
      return encodeURIComponent(x).split('%').join('+');
    },

    decodeHashComponent: function (x) {
      return decodeURIComponent(x.split('+').join('%'));
    },

    getHashParameters : function(separator) {
      if(!separator)
      {
        separator = '&';
      }
      var parts = location.hash.substring(1).split(separator);
      var pars= {};
      for(var i= parts.length; i-->0;) {
        var kv = parts[i].split('=');
        var k = kv[0];
        if(k == '') continue;
        var v = kv.slice(1).join('=');
        pars[Tools.decodeHashComponent(k)]= Tools.decodeHashComponent(v);
      }
      return pars;
    },

    // public API
    parseUri: parseUri
    
  }
}();


/**
 * I18n classs
 *
 * Handles string translations
 *
 */
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

  // FIXME: portovat php kod ze sfNumberChoice
  function formatNumberChoice(text_string, replace_hash, number) 
  { 
    // translate it first
    text_string = __(text_string);
    
    var pattern = new RegExp("\\["+number+"\\]\s?([^\|]*)");
    
    function replace_in_string(text_string, replace_hash) 
    {    
      for(var i in replace_hash)
        text_string = text_string.replace(new RegExp(i), replace_hash[i]); 
      return (text_string);
    }
 
    var matches = text_string.match(pattern);
    if(matches != null) return replace_in_string(matches[1], replace_hash);
    else 
    {
      pattern = /\[else\]\s?([^\|]*)/;
      matches = text_string.match(pattern);
    if(matches != null) return replace_in_string(matches[1], replace_hash);
    else return "not found";
    }
  };
  
  return {
  
    formatNumberChoice: formatNumberChoice,

    /**
     * Returns translation, if translation dictionary
     * exists and has a translation.
     */
    translate: function(str, params) {
      
      if(I18nTranslations && I18nTranslations[str])
      {
        var str = I18nTranslations[str];
      }
      
      if(params)
      {
        for(param in params)
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
     */
    setTranslation: function(translations)
    {      
      I18nTranslations = translations;
    },

    addTranslation: function(translations)
    {      
      jQuery.extend(I18nTranslations, translations);      
    }

  };
}();

/**
 * Cookie
 *
 */
var Cookie = function() {
  
  var version = '1.1';
  
  return {

    /**
     * Sets cookie
     *
     */
    set: function(name, value, daysToExpire, path, domain, secure) {
      var expire = '';

      if (daysToExpire != undefined) {       
        var d = new Date();
        d.setTime(d.getTime() + (86400000 * parseInt(daysToExpire)));
        expire = '; expires=' + d.toGMTString();
      }
      
      if(path == undefined)
      {
        path = '/';
      }

      if(domain == undefined)
      {
        domain = Config.get('cookie_domain');
      }

      return (document.cookie = escape(name) + '=' + escape(value || '') + expire +
        ((path) ? "; path=" + path : "") +
        ((domain) ? "; domain=" + domain : "") +
        ((secure) ? "; secure" : ""));
    },
  
    get: function(name)
    {
      var nameEQ = name + "=";
      var ca = document.cookie.split(';');
      for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return unescape(c.substring(nameEQ.length,c.length));
      }
      return null;
    },

    erase: function(name, path, domain, secure) {
      try {
        var date = new Date();
        date.setTime(date.getTime() - (3600 * 1000));
        var expire = '; expires=' + date.toGMTString();
        document.cookie = escape(name) + '=' + expire +
        ((path) ? "; path=" + path : "") +
        ((domain) ? "; domain=" + domain : "") +
        ((secure) ? "; secure" : "");
      } catch (e) {
        return false;
      }
      return true;
    },

    remove: function(name, path, domain, secure)
    {
      if(path == undefined)
      {
        path = '/';
      }
      if(domain == undefined)
      {
        domain = Config.get('cookie_domain');
      }
      return Cookie.erase(name, path, domain, secure);
    },

    accept: function() 
    {
      if (typeof navigator.cookieEnabled == 'boolean') {
        return navigator.cookieEnabled;
      }
      Cookie.set('_test', '1');
      return (Cookie.erase('_test') === true);
    },

    /**
     * Return cookie utility version
     */
    getVersion: function()
    {
      return version;
    }
    
  };
  
}();

/**
 * Translation function
 *
 * @param {String} str
 * @param {Array} params
 */
function __(str, params)
{
  return I18n.translate(str, params);
};

// Global Killswitch on the <html> element
if(Application.jsEnabled) {

  // Global Killswitch on the <html> element
  jQuery(document.documentElement).addClass('js');
  
  // Attach all behaviors.
  jQuery(document).ready(function() 
  {
    var domain = Config.get('cookie_domain');
    var path   = Config.get('cookie_path');
    // set cookie, session timeout
    Cookie.set('has_js', 1, '', path, domain);
    // detect timezone
    Application.detectTimezone();
    // attach behaviours
    Application.attachBehaviors(this);
  });
    
  // setup ajax requests
  jQuery.ajaxSetup({
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
        
        Logger.logToServer(
        {
          error:  'Ajax error',
          status: textStatus, 
          url: url,
          responseText: jqxhr.responseText,
          code: responseCode,
          agent: navigator.userAgent, 
          cookies: cookies,
          referer: document.referrer,
          jqueryVersion: jQuery.fn.jquery
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
        result = jQuery.parseJSON(jqxhr.responseText);
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
          Application.alert(__('This action requires to be logged in and you are not logged in. Redirect to login page?'));
          if(url = Config.get('login_url'))
          {
            window.location.href = url;
          }
        }
      }
    });

};

// Add string.trim() function since IE doesn't support this
// Trim function is used in logParams function
// Code from: http://stackoverflow.com/questions/2308134/trim-in-javascript-not-working-in-ie
if(typeof String.prototype.trim !== 'function') 
{
  String.prototype.trim = function() 
  {
    return this.replace(/^\s+|\s+$/g, ''); 
  }
}

/* core jQuery extensions */
(function($) 
{
  /**
   * This utility function works like removeClass()
   * but removes classes that matches given pattern
   */
  $.fn.removeMatchedClasses = function(pattern) 
  {  
    return this.each(function(i) 
    {  
      var element = $(this);
      var classes = element.attr('class').split(/\s+/);      
      for(var c = 0; c < classes.length; c++) 
      {    
        var className = classes[c];
        if(className.match(pattern)) 
        {      
          element.removeClass(className);
        }
      }
    });
  };
  
  /** 
   * jQuery.values: get or set all of the name/value pairs from child input controls
   * 
   * This is modified version of the function found on stackoverflow. This serializes 
   * element ids instead of its names.
   * 
   * @argument data {array} If included, will populate all child controls.
   * @returns element if data was provided, or array of values if not
   * @see http://stackoverflow.com/questions/1489486/jquery-plugin-to-serialize-a-form-and-also-restore-populate-the-form/1490431#1490431
   */
  $.fn.values = function(data) {

    var els = $(this).find(':input').get();

    if(typeof data != 'object') {
      // return all data
      data = {};

      $.each(els, function() {
        if (this.id && !this.disabled && (/*this.checked||*/
          /select|textarea|input/i.test(this.nodeName)
          || /text|hidden|password/i.test(this.type))) 
        {          
          data[this.id] = ($(this).is(':checkbox') || $(this).is(':radio')) 
                          ? $(this).is(':checked') : $(this).val();
        }
      });
      return data;
    } 
    else {
      $.each(els, function() {
        if (this.id && data[this.id] !== undefined) 
        {
          if(this.type == 'checkbox' || this.type == '' || $(this).is(':radio')) 
          {
            $(this).prop('checked', data[this.id]);
            // this.checked = (data[this.id] == $(this).val());        
          } else {
            $(this).val(data[this.id]);
          }
          $(this).trigger('change');
        }
      });
      return $(this);
    }
  };
  
  /**
   * Advanced equalHeight plugin
   * 
   * @see http://css-tricks.com/equal-height-blocks-in-rows/
   * @version 1.0
   */
  $.fn.equalHeights = function()
  {    
    var currentTallest = 0,
        currentRowStart = 0,
        rowDivs = new Array();
   
    function setConformingHeight(el, newHeight) 
    {
      // set the height to something new, but remember the original height in case things change
      el.data("originalHeight", (el.data("originalHeight") == undefined) ? (el.height()) : (el.data("originalHeight")));
      el.height(newHeight);
    };

    function getOriginalHeight(el) 
    {
      // if the height has changed, send the originalHeight
      return (el.data("originalHeight") == undefined) ? (el.height()) : (el.data("originalHeight"));
    };
    
    return this.each(function()
    {
      // "caching"
      var $el = $(this);
      var topPosition = $el.position().top;		
      if(currentRowStart != topPosition) 
      {
        // we just came to a new row.  Set all the heights on the completed row
        for(var currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) 
        {
          setConformingHeight(rowDivs[currentDiv], currentTallest);
        }
        
        // set the variables for the new row
        rowDivs.length = 0; // empty the array
        currentRowStart = topPosition;
        currentTallest = getOriginalHeight($el);
        rowDivs.push($el);
        
      } 
      else 
      {		
        // another div on the current row.  Add it to the list and check if it's taller
        rowDivs.push($el);
        currentTallest = (currentTallest < getOriginalHeight($el)) ? (getOriginalHeight($el)) : (currentTallest);		
  		}
      
    	// do the last row
			for(var currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) 
      {
        setConformingHeight(rowDivs[currentDiv], currentTallest);      
      }
      
    });
    
  }; // end equalHeights
  
})(jQuery);
