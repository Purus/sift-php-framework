/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Dynatree is a jQuery plugin that allows to dynamically create html tree view controls using JavaScript.
 *
 * @name Dynatree
 * @requires jQuery
 * @requires jQueryUI
 * @requires Tools
 * @class
 * @link https://code.google.com/p/dynatree/
 */
(function(Application, $)
{
  if(typeof Application === 'undefined')
  {
    throw 'Application is required.'
  }

  if(typeof $.ui === 'undefined')
  {
    throw 'jQuery UI is required.'
  }

  if(typeof $.ui.dynatree === 'undefined')
  {
    throw 'Dynatree is required.'
  }

  if(typeof $.fn.treeManager === 'undefined')
  {
    throw 'TreeManager is required.'
  }

  /**
   * Handles the click. Toggles the selection of the node.
   *
   * @param {DynaTreeNode} node
   * @param {Event} event
   */
  var treeOnClickHandler = function(node, event)
  {
    // target can be:
    // Return the part of a node, that a click event occurred on.
    // Possible values: 'prefix' 'expander', 'checkbox', 'icon', 'title'. null is returned else.
    var target = node.getEventTargetType(event);
    var selectMode = node.tree.options.selectMode;
    if(!target || target === 'title')
    {
      switch(selectMode)
      {
        // single choice
        case 1:
          if(!node.isSelected())
          {
            node.select();
          }
        break;

        case 2:
        case 3:
          node.toggleSelect();
        break;
      }
    }
  };

  /**
   * Handles the selection of the node. Checks the input checkbox (or radio)
   *
   * @param {Boolean} select Select?
   * @param {DynaTreeNode} node
   */
  var treeOnSelectHandler = function(select, node)
  {
    // find the inputs, take only the first, so inputs in the nested uls are not touched
    var $input = $(node.li).find('input[type="checkbox"]:first,input[type="radio"]:first');
    if(select)
    {
      $input.attr('checked', 'checked').trigger('change');
    }
    else
    {
      $input.removeAttr('checked').trigger('change');
    }
  };

  /**
   * Prepares the state of the tree for Dynatree usage
   *
   * @param {jQuery Object} $tree Object
   */
  Application.prepareTreeWidget = function($tree)
  {
    $tree.find('input[type="checkbox"],input:radio').each(function()
    {
      var that = $(this);
      if(that.prop('checked'))
      {
        // assing classes for the parent li element
        that.parents('li:first').addClass('selected').addClass('expanded');
      }
    });
  };

  /**
   * Return an array of options for Dynatree. Creates default options and
   * merges them with options passes as "data-tree-options" attribute of the DOM element.
   *
   * @param {jQuery Object} $element Element
   * @return {Object}
   * @example
   *  &lt;div class="tree" data-tree-options="{checkbox:false}"&gt;
   *  &lt;ul&gt;
   *    &lt;li&gt;tree item&lt;/li&gt;
   *  &lt;/ul&gt;
   *  &lt;/div&gt;
   */
  Application.getTreeWidgetOptions = function($element)
  {
    // @see: http://wwwendt.de/tech/dynatree/doc/dynatree-doc.html
    var options = {
      debugLevel: 0,
      icon: false,
      persist: false,
      minExpandLevel: 2,
      activeVisible: true,
      selectionVisible: true,
      checkbox: false,
      // Set focus to first child, when expanding or lazy-loading.
      autoFocus: false,

      // tree manager extensions
      url: null
    };

    // we know that the tree is a widget
    if($element.hasClass('tree-checkbox')
        || $element.hasClass('tree-radio'))
    {
      options.checkbox = true;
    }

    // detect select mode
    // 1 : single, 2 : multi, 3 : multi-hierarchy
    var selectMode = 1;
    var checkboxClass = 'dynatree-radio';
    if($element.hasClass('multiple'))
    {
      selectMode = 2;
      checkboxClass = 'dynatree-checkbox';
    }

    options.selectMode = selectMode;
    options.classNames = {
      checkbox: checkboxClass
    };

    // extend the default options
    // with data-tree-options from element
    var options = $.extend({}, options, $element.data('treeOptions') || {});

    // additional checks
    if(options.checkbox)
    {
      options.onClick = treeOnClickHandler;
      options.onSelect = treeOnSelectHandler;
    }

    // drag and drop feature
    if(options.dnd)
    {
      // callbacks need to be functions
      if(typeof options.dnd.onDragStart === 'string')
      {
        options.dnd.onDragStart = Tools.getObject(options.dnd.onDragStart);
      }

      if(typeof options.dnd.onDragStop === 'string')
      {
        options.dnd.onDragStop = Tools.getObject(options.dnd.onDragStop);
      }

      if(typeof options.dnd.onDragOver === 'string')
      {
        options.dnd.onDragOver = Tools.getObject(options.dnd.onDragOver);
      }

      if(typeof options.dnd.onDragEnter === 'string')
      {
        options.dnd.onDragEnter = Tools.getObject(options.dnd.onDragEnter);
      }

      if(typeof options.dnd.onDragLeave === 'string')
      {
        options.dnd.onDragLeave = Tools.getObject(options.dnd.onDragLeave);
      }

      if(typeof options.dnd.onDrop === 'string')
      {
        options.dnd.onDrop = Tools.getObject(options.dnd.onDrop);
      }
    }

    return options;
  };

  /**
   * Setup tree lists
   *
   * @param {DOM element} context Context
   * @methodOf Application.behaviors
   * @requires Dynatree
   */
  Application.behaviors.setupTrees = function(context)
  {
    var tree = $('div.tree', context);

    // nothing to do
    if(!tree.length)
    {
      return;
    }

    // prepare tree selected and expanded states
    // based on the check state of the checkbox, radio input
    // see sfWidgetFomTreeChoice for the rendering options
    tree.each(function()
    {
      var $tree = $(this);
      // prepare tree for dynatree usage
      Application.prepareTreeWidget($tree);
      var options = Application.getTreeWidgetOptions($tree);
      // tree manager
      $tree.treeManager(options);
    });

  };

}(Application, window.jQuery));