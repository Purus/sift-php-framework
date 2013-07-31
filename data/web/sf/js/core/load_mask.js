/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * LoadMask for jQuery.
 *
 * Based on the by jquery.loadmask plugin by Sergiy Kovalchuk (c) 2009 (licensed under MIT)
 *
 * @requires jQuery
 * @requires jQueryUI
 * @requires I18n
 */
(function($)
{
  var LoadMask = function(element, options)
  {
    // options
    this.options = $.extend(true, {}, $.fn.setLoading.defaults, options);
    this.$element = $(element);
    this.$maskDiv = $('<div class="load-mask"></div>');
    this.$maskMessage = $('<div class="load-mask-msg">' + this.options.msg + '</div>');
    this.$maskDiv.append(this.$maskMessage);
    this.$element.append(this.$maskDiv);

    this.$element.addClass('load-masked');

    if(this.$element.css('position') === 'static')
    {
      this.$element.addClass('load-masked-relative');
    }

    if(this.options.overlaySize !== false)
    {
      if(this.options.overlaySize.height !== undefined)
      {
        this.$maskDiv.height(this.options.overlaySize.height)
      }

      if(this.options.overlaySize.width !== undefined)
      {
        this.$maskDiv.width(this.options.overlaySize.width)
      }
    }

    // try jQueryUI position
    try
    {
      this.$maskMessage.position({
        my: 'center',
        at: 'center',
        of: this.$maskDiv
      });
    }
    catch(e)
    {
    }

  };

  LoadMask.prototype = {
    constructor: LoadMask,
    /**
     * Set the visibility of the mask
     *
     * @param {Boolean} flag
     * @returns {void}
     */
    setVisible: function(flag)
    {
      if(flag)
      {
        this.$maskDiv.show();
      }
      else
      {
        this.$maskDiv.hide();
        this.destroy();
      }
    },

    /**
     * Set loading message
     *
     * @param {String} message
     * @returns {void}
     */
    setMessage: function(message)
    {
      this.$maskDiv.find('.load-mask-msg').html(message);
    },

    /**
     * Destroy the mask
     *
     * @returns {void}
     */
    destroy: function()
    {
      this.$element.removeClass('load-masked').removeClass('load-masked-relative');
      this.$maskMessage.remove();
      this.$maskDiv.remove();
      this.$element.removeData('loadMask');
    }
  };

  /**
   *
   * @param {Object|boolean} flagOrOptions
   */
  $.fn.setLoading = function(option, value)
  {
    var type = $.type(option);
    return this.each(function()
    {
      var $this = $(this);
      var data = $this.data('loadMask');
      var options = type === 'object' && option;

      if(!data)
      {
        $this.data('loadMask', (data = new LoadMask(this, options)));
      }

      if(type === 'string')
      {
        switch(option)
        {
          case 'message':
            data['setMessage'](value);
          break;

          default:
            data[option](value);
          break;
        }
      }
      else if(type === 'boolean')
      {
        data['setVisible'](option);
      }
    });
  };

  // defaults
  $.fn.setLoading.defaults = {
    msg: typeof __ === 'function' ? __('Loading...') : 'Loading...',
    // overlaySize:
    // {
    // width: 200
    // height: 100
    // }
    overlaySize: false
  };
  $.fn.setLoading.Constructor = LoadMask;

}(window.jQuery));