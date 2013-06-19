/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Tree manager is a javascript tree manager
 *
 * @name TreeManager
 * @requires jQuery
 * @requires jQueryUI
 * @requires Tools
 * @class
 */
(function(window, $, Application)
{
  var TreeManager = function(element, options)
  {
    // element
    this.$element = $(element);
    this.id = this.$element.prop('id') || '';

    // options
    this.options = $.extend(true, {}, $.fn.treeManager.defaults, options);

    Application.prepareTreeWidget(this.$element);
    // attach behaviors to the element
    var options = Application.getTreeWidgetOptions(this.$element);

    // custom options are:
    // url: url which is called when user manipulated the tree wich dnd
    // drag and drop is enabled if url is set
    if(options.url && !options.dnd)
    {
      // create drag and drop options
      options.dnd = {
        preventVoidMoves : true, // Prevent dropping nodes 'before self', etc.
        onDragStart : $.proxy(this.onDragStartHandler, this),
        onDragOver: $.proxy(this.onDragOverHandler, this),
        onDrop : $.proxy(this.onDropHandler, this),
        onDragEnter : $.proxy(this.onDragEnterHandler, this),
        autoExpandMS : 1000
      };
    }

    // make it tree, pass all options, since they do not collide,
    // and dynatree does not care about invalid options
    this.$element.dynatree(options);
  };

  TreeManager.prototype = {
    constructor: TreeManager,

    /**
     *
     * @param {Object} node
     * @returns {Boolean}
     */
    onDragStartHandler: function(node)
    {
      return true;
    },

    onDragEnterHandler : function(node, sourceNode)
    {
      /** sourceNode may be null for non-dynatree droppables.
       *  Return false to disallow dropping on node. In this case
       *  onDragOver and onDragLeave are not called.
       *  Return 'over', 'before, or 'after' to force a hitMode.
       *  Return ['before', 'after'] to restrict available hitModes.
       *  Any other return value will calc the hitMode from the cursor position.
       */
      return true;
    },

    onDragOverHandler : function(node, sourceNode, hitMode)
    {
      /**
       * Return false to disallow dropping this node.
       */
      // Prevent dropping a parent below it's own child
      if(node.isDescendantOf(sourceNode))
      {
        return false;
      }
      return true;
    },

    onDropHandler : function(node, sourceNode, hitMode, ui, draggable)
    {
      var sortUrl = this.options.url;
      if(!sortUrl)
      {
        return;
      }

      var ajaxOptions = $.extend({}, this.options.ajaxSettings, {
        url: sortUrl,
        data: {
          source: sourceNode.data.key,
          target: node.data.key,
          type: hitMode
        },
        // DISABLE async, so we can refresh the tree based on the result!
        // There is no "revert" in the dynatree API
        async: false,
        success: function(data)
        {
          if(data.success)
          {
            sourceNode.move(node, hitMode);
            // expand the drop target
            sourceNode.makeVisible(true);
            sourceNode.expand(true);
          }
        }
      });

      // make ajax request
      $.ajax(ajaxOptions);
    }
  };

  /**
   * Tree manager jquery plugin
   *
   * @param {String} option
   * @returns {Mixed}
   * @memberOf jQuery.fn
   */
  $.fn.treeManager = function(option)
  {
    return this.each(function()
    {
      var $this = $(this);
      var data = $this.data('treeManager');
      var options = typeof option === 'object' && option;

      if(!data)
      {
        $this.data('treeManager', (data = new TreeManager(this, options)));
      }
      if (typeof option === 'string')
      {
        data[option]();
      }
    });
  };

  // defaults
  $.fn.treeManager.defaults = {
    ajaxSettings: {
      type: 'POST',
      dataType: 'json'
    }
  };

  $.fn.treeManager.Constructor = TreeManager;

}(window, window.jQuery, window.Application));