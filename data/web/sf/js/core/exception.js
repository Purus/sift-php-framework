/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @namespace
 * @name window
 * @description supplimental window methods.
 */

/**
 * Exception is an extension for Error object.
 *
 * @name Exception
 * @requires Logger
 * @class
 * @link http://www.nczonline.net/blog/2009/03/03/the-art-of-throwing-javascript-errors/
 * @link http://stackoverflow.com/questions/10382770/log-javascript-error
 * @link http://stackoverflow.com/questions/147891/javascript-exception-stack-trace
 */
(function(window, $)
{
  function Exception(message)
  {
    Error.call(this);
    this.name = 'Exception';
    this.message = message || '';
  };

  Exception.prototype = Error.prototype;

  // export to window
  window.Exception = Exception;

  /**
   * Logs the uncatched errors to server (If enabled)
   *
   * @param {String} message
   * @param {String} url
   * @param {Integer} line
   * @see http://www.w3.org/wiki/DOM/window.onerror
   */
  window.onerror = function(message, url, line)
  {
    if(typeof window.Logger === 'undefined')
    {
      return;
    }

    var cookies = window.document.cookie;
    var data = {
      error:  message,
      url: url || '(was empty) ' + window.location.href,
      line: line,
      agent: navigator.userAgent,
      cookies: cookies,
      referer: document.referrer,
      jqueryVersion: $.fn.jquery
    };

    // log to server
    window.Logger.logToServer(data);
  };


}(window, window.jQuery));
