/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function($, Application) {

  Application = Application || {
    'behaviors': {}
  };

  /**
   * Widget placement function for Twitter Bootstrap popovers and tooltips.
   *
   * @param {DOM element} popover
   * @param {DOM element} element
   * @returns {String} top|bottom|left|right
   * @link https://github.com/twitter/bootstrap/issues/1411
   */
  Application.widgetPlacement = function(popover, element)
  {
    var $popover = $(popover);
    if($popover.is(':hidden'))
    {
      return;
    }

    var $element = $(element);
    var windowWidth = $(window).width();
    if(windowWidth < 500)
    {
      return 'bottom';
    }

    var pos = $.extend({
    }, $element.offset(), {
      width: element.offsetWidth,
      height: element.offsetHeight
    });

    var actualHeight = $popover.height();
    var actualWidth = $popover.width();

    var boundTop = $(document).scrollTop();
    var boundLeft = $(document).scrollLeft();
    var boundRight = boundLeft + $(window).width();
    var boundBottom = boundTop + $(window).height();

    var isWithinBounds = function(elementPosition) {
      return boundTop < elementPosition.top && boundLeft < elementPosition.left && boundRight > (elementPosition.left + actualWidth) && boundBottom > (elementPosition.top + actualHeight);
    };

    var elementAbove = {
      top: pos.top - actualHeight,
      left: pos.left + pos.width / 2 - actualWidth / 2
    };

    var elementBelow = {
      top: pos.top + pos.height,
      left: pos.left + pos.width / 2 - actualWidth / 2
    };

    var elementLeft = {
      top: pos.top + pos.height / 2 - actualHeight / 2,
      left: pos.left - actualWidth
    };

    var elementRight = {
      top: pos.top + pos.height / 2 - actualHeight / 2,
      left: pos.left + pos.width
    };

    var above = isWithinBounds(elementAbove);
    var below = isWithinBounds(elementBelow);
    var left = isWithinBounds(elementLeft);
    var right = isWithinBounds(elementRight);

    if(above) {
      return "top";
    } else {
      if(below) {
        return "bottom";
      } else {
        if(left) {
          return "left";
        } else {
          if(right) {
            return "right";
          } else {
            return "right";
          }
        }
      }
    }
  };

  if(typeof window.Application === 'undefined')
  {
    window.Application = Application;
  }

}(window.jQuery, window.Application));
