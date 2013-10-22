/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Cache panel
 *
 * @param {WebDebug} WebDebug
 */
(function(WebDebug)
{
  if(!WebDebug)
  {
    return;
  }

  var $ = WebDebug.$;

  /**
   * WebDebugCache
   *
   * @param {Array} options
   * @returns {WebDebugCache}
   */
  var WebDebugCache = function() {};

  WebDebugCache.prototype =
  {
    constructor: WebDebugCache,

    /**
     * Setups the extension
     *
     * @param {WebDebug} WebDebugInstance
     */
    setup: function(WebDebugInstance)
    {
      this.$panel = $('#web-debug-panel-cache');
      this.$cachedFragments = $('div.web-debug-cached-fragment');

      // toolbar is not closed,
      // we need to show the cached fragments
      if(!WebDebugInstance.isToolbarClosed())
      {
        this.showAllCachedFragments();
      }

      this.$cachedFragments.each(function(i, element)
      {
        var $this = $(element);
        $this.find('a.web-debug-cache-toggler').click(function(e)
        {
          var $link = $(this);
          var $parent = $link.parent();
          $parent.find('div.web-debug-cache-info:first').toggleClass('hidden');
          $link.toggleClass('opened');
          e.preventDefault();
        });
      });

      WebDebugInstance.$toolbar.bind('opened', $.proxy(this.showAllCachedFragments, this));
      WebDebugInstance.$toolbar.bind('closed', $.proxy(this.hideAllCachedFragments, this));

      // find all links inside this panel
      this.$contentViewLinks = $('.web-debug-cache-toggler', this.$panel);
      this.$contentViewLinks.click(function(e)
      {
        var $this = $(this);
        // find the corresponding dom element
        var cacheId = $this.data('cacheId');
        var win = window.open('', '_web_debug_cache_content', 'width=500,height=500,resizable=yes,scrollbars=yes');
        if(!win)
        {
          return;
        }

        var doc = win.document;
        doc.write('<!DOCTYPE html><title>Cache: #'+ cacheId +'</title><meta charset="utf-8"><style>' + $('#web-debug-style').html() + '<\/style><body class="web-debug-cache-content-preview">');
        var $cacheContent = $('#web-debug-cache-' + cacheId + ' .web-debug-cached-content');
        if($cacheContent.length)
        {
          doc.body.innerHTML = '<pre><code>' + WebDebug.Highlighter.highlight($cacheContent.html()) + '<\/code><\/pre>';
        }

        doc.write('<\/body><\/html>');
        win.document.close();
        win.focus();
        e.preventDefault();
      });
    },

    hideAllCachedFragments : function()
    {
      this.$cachedFragments.addClass('hidden').removeClass('visible');
    },

    showAllCachedFragments : function()
    {
      this.$cachedFragments.removeClass('hidden').addClass('visible');
    }

  };

  // export
  WebDebug.Extensions.Cache = new WebDebugCache();

}(window.WebDebug));