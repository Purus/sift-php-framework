/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Flash messages
 *
 * @methodOf Application.behaviors
 * @name flash
 * @deprecated This is deprecated, and will be removed in future
 * @class
 * @function
 * @static
 */
Application.behaviors.flash = function(context) {

  $('div.flash-notice,div.flash-success,div.flash-error,div.request-error', context).each(function()
  {
    var that   = $(this);
    var parent = that.parent();

    parent.addClass('ui-widget');

    that.addClass('ui-corner-all');

    if(that.hasClass('flash-notice') || that.hasClass('flash-success'))
    {
      that.addClass('ui-state-highlight').find('p').prepend('<span class="ui-icon ui-icon-info"><span /></span>').end()
    }
    else if(that.hasClass('flash-error') || that.hasClass('request-error'))
    {
      that.addClass('ui-state-error').find('p').prepend('<span class="ui-icon ui-icon-alert"><span /></span>');
    }

    // create close link
    var link = $('<a class="flash-close ui-icon ui-icon-closethick right" href="#" title="' + __('close') + '">' + __('close') + '</a>');
    link.click(function(e)
    {
      that.fadeOut('slow', function()
      {
        that.remove();
      });

      e.preventDefault();
    });

    that.find('p').prepend(link);
  });

};
