/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * See (http://jquery.com/).
 * @name jQuery
 * @class
 * See the jQuery Library  (http://jquery.com/) for full details.  This just
 * documents the function and classes that are added to jQuery by this plug-in.
 */

/**
 * See (http://jquery.com/)
 * @name fn
 * @class
 * See the jQuery Library  (http://jquery.com/) for full details.  This just
 * documents the function and classes that are added to jQuery by this plug-in.
 * @memberOf jQuery
 * @memberOf $
 */

/**
 * jQuery plugins which are considered as "must have" in the core.
 *
 * @requires jQuery
 */
(function($)
{
  /**
   * This utility function works like removeClass()
   * but removes classes that matches given pattern
   *
   * @class removeMatchedClasses
   * @memberOf jQuery.fn
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
   * Get or set all of the name/value pairs from child input controls
   *
   * This is modified version of the function found on stackoverflow. This serializes
   * element ids instead of its names.
   *
   * @argument {Array} data If included, will populate all child controls.
   * @returns {jQuery} If data was provided, or array of values if not
   * @see http://stackoverflow.com/questions/1489486/jquery-plugin-to-serialize-a-form-and-also-restore-populate-the-form/1490431#1490431
   * @memberOf jQuery.fn
   * @class values
   */
  $.fn.values = function(data) {

    var els = $(this).find(':input').get();
    if(typeof data !== 'object') {
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
   * @memberOf jQuery.fn
   * @class equalHeights
   */
  $.fn.equalHeights = function()
  {
    var currentTallest = 0,
        currentRowStart = 0,
        rowDivs = new Array();

    /**
     * @inner
     */
    function setConformingHeight(el, newHeight)
    {
      // set the height to something new, but remember the original height in case things change
      el.data("originalHeight", (el.data("originalHeight") == undefined) ? (el.height()) : (el.data("originalHeight")));
      el.height(newHeight);
    };

    /**
     * @inner
     */
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

})(window.jQuery);
