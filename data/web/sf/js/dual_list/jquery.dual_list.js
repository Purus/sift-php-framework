/*
 * This file is part of the Sift package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function($) {

  /**
   * DualList constructor
   *
   * @param {DOM element} element
   * @param {Object} options
   * @class
   * @name DualList
   * @version 1.0
   */
  var DualList = function(element, options)
  {
    this.$element = $(element);

    this.options = $.extend(true, {}, $.fn.dualList.defaults, options);

    this.$associated = this.$element.find(this.getClassName('associated', true));
    this.$available  = this.$element.find(this.getClassName('available', true));

    // http://codejquery1.appspot.com/question/5080f3354f1eba38a4cd71b6

    // available ul
    this.$availableList = this.$available.find(this.getClassName('items', true) + ' ul');
    // associated ul
    this.$associatedList = this.$associated.find(this.getClassName('items', true) + ' ul');

    // buttons
    this.$moveToAssociated = this.$element.find(this.getClassName('move-associated', true));
    this.$moveToAvailable = this.$element.find(this.getClassName('move-available', true));

    this.$selectAllButton = this.$element.find(this.getClassName('select-all', true));
    this.$unSelectAllButton = this.$element.find(this.getClassName('unselect-all', true));
    this.$invertSelectionButton = this.$element.find(this.getClassName('invert-selection', true));

    // filter inputs
    this.$associatedFilterInput = this.$associated.find(this.getClassName('filters', true) + ' input[type="text"]');
    this.$availableFilterInput = this.$available.find(this.getClassName('filters', true)  + ' input[type="text"]');

    // filter buttons
    this.$associatedFilterButton = this.$associated.find(this.getClassName('filters', true) + ' :button');
    this.$availableFilterButton = this.$available.find(this.getClassName('filters', true) + ' :button');

    // resizable?
    if(this.options.resizableEnabled)
    {
      this.setupResizable();
    }

    // setup core events, like adding and removing associated items
    this.setupEvents();

    // setup list items (associated and available)
    this.setupListItems();

  };

  /**
   * Comparator
   *
   * function(a, b)   where a is compared to b
   */
  var ItemComparator = {
    /**
     * Naive general implementation
     */
    standard: function(a, b)
    {
      if (a > b) return 1;
      if (a < b) return -1;
      return 0;
    },

    /**
     * Alphabetical compare
     *
     * @param {type} a
     * @param {type} b
     * @returns {}
     */
    alphabetical: function(a, b)
    {
      return $(a).text().toUpperCase().localeCompare($(b).text().toUpperCase());
    },

    position: function(a, b)
    {
      return ItemComparator.standard($(a).data('position'), $(b).data('position'));
    }

  };

  DualList.prototype = {

    constructor: DualList,

    timers: [],

    /**
     * Return class name with baseClass prefix (separated with dash)
     *
     * @param {String} className
     * @param {Boolean} botPrefix Include dot (used for jQuery selector)
     * @returns {String}
     */
    getClassName : function(className, dotPrefix)
    {
      return dotPrefix ?
            ('.' +
            this.options.baseClass + '-' + className) :
            this.options.baseClass + '-' + className;
    },

    /**
     * Setup core events:
     *
     *  * associated_added.dual_list
     *  * associated_removed.dual_list
     *
     * @returns
     */
    setupEvents: function()
    {
      // element add/remove events
      this.$element.on('associated_added.dual_list', $.proxy(this.handleAssociatedAdded, this));
      this.$element.on('associated_removed.dual_list', $.proxy(this.handleAssociatedRemoved, this));

      // button clicks
      this.$moveToAssociated.on('click', $.proxy(this.moveToAssociatedClicked, this, this.$moveToAssociated));
      this.$moveToAvailable.on('click', $.proxy(this.moveToAvailableClicked, this, this.$moveToAvailable));

      // filter inputs
      this.$associatedFilterInput.on('keypress', $.proxy(this.handleFilterInputKeyPressed, this,
                                              this.$associatedFilterInput, this.$associatedList))
                                 .on('keyup', $.proxy(this.handleFilterInputKeyUp, this,
                                              this.$associatedFilterInput, this.$associatedList));

      this.$availableFilterInput.on('keypress', $.proxy(this.handleFilterInputKeyPressed, this,
                                              this.$availableFilterInput, this.$availableList))
                                 .on('keyup', $.proxy(this.handleFilterInputKeyUp, this,
                                              this.$availableFilterInput, this.$availableList));

      this.$availableFilterButton.on('click', $.proxy(this.handleFilterButtonClicked, this,
                                              this.$availableFilterInput, this.$availableList));

      this.$associatedFilterButton.on('click', $.proxy(this.handleFilterButtonClicked, this,
                                              this.$associatedFilterInput, this.$associatedList));

      this.$selectAllButton.on('click', $.proxy(this.handleSelectAllButtonClicked, this));
      this.$unSelectAllButton.on('click', $.proxy(this.handleUnSelectAllButtonClicked, this));
      this.$invertSelectionButton.on('click', $.proxy(this.handleInvertSelectionButtonClicked, this));
    },

    /**
     * Handle filter button click. Resets the input and triggers keyup event
     *
     * @param {jQuery Object} $input
     */
    handleFilterButtonClicked: function($input, $list)
    {
      $input.val('').trigger('change');
      this.filterList($input, $list);

      this.updateCounts();
    },

    /**
     * Handle event when user pressed key inside the filter
     *
     * @param {jQuery Object} $input
     * @param {jQuery Object} $list
     * @param {jQuery.Event} event
     */
    handleFilterInputKeyPressed: function($input, $list, event)
    {
      // Enter keycode pressed
      if(event.keyCode === 13)
      {
        e.preventDefault();
      }
    },

    /**
     * Handle event when user pressed a key
     *
     * @param {jQuery Object} $input
     * @param {jQuery Object} $list
     */
    handleFilterInputKeyUp: function($input, $list)
    {
      this.filterList($input, $list);
      this.updateCounts();
    },

    /**
     * Filters list based on the input value.
     * Code taken from from John Resig's liveUpdate script
     *
     * @param {jQuery Object} $input
     * @param {jQuery Object} $list
     */
    filterList: function($input, $list)
    {
      var rows = $list.children('li');
      var cache = rows.map(function()
      {
        // support for truncated values
        var $element = $(this);
        var title = $element.prop('title');
        if(title)
        {
          return title.toLowerCase();
        }
        return $element.text().toLowerCase();
      });

      var term = $.trim($input.val().toLowerCase());
      var scores = [];

      if(!term)
      {
        rows.show();
      }
      else
      {
        rows.hide();
        cache.each(function(i)
        {
          if(this.indexOf(term) > -1)
          {
            scores.push(i);
          }
        });
        $.each(scores, function()
        {
          $(rows[this]).show();
        });
      }
    },

    /**
     * Handle event when "select all" button has been clicked
     *
     * @param {jQuery.Event} e
     */
    handleSelectAllButtonClicked: function(e)
    {
      var $button = $(e.target);

      // we have universal handler for associated and available list
      // FIXME: performance issue?
      var list = $button.parents(this.getClassName('header', true) + ':first')
                  .parent().find(this.getClassName('items', true) + ' ul li:visible');

      var that = this;
      list.each(function()
      {
        $(this).addClass(that.options.selectedClass);
      });

      e.preventDefault();
    },

    /**
     * Handle event when "unselect all" button has been clicked
     *
     * @param {jQuery.Event} e
     */
    handleUnSelectAllButtonClicked: function(e)
    {
      var $button = $(e.target);

      var list = $button.parents(this.getClassName('header', true) + ':first')
                  .parent().find(this.getClassName('items', true) + ' ul li:visible');

      var that = this;
      list.each(function()
      {
        $(this).removeClass(that.options.selectedClass);
      });

      e.preventDefault();
    },

    /**
     * Handle event when "inverse selection" button has been clicked
     *
     * @param {jQuery Object} $button
     * @param {jQuery.Event} e
     */
    handleInvertSelectionButtonClicked: function(e)
    {
      var $button = $(e.target);

      var list = $button.parents(this.getClassName('header', true) + ':first')
                  .parent().find(this.getClassName('items', true) + ' ul li:visible');

      var that = this;
      list.each(function()
      {
        $(this).toggleClass(that.options.selectedClass);
      });

      e.preventDefault();
    },

    /**
     * Handle the event when associated item is addded to the list
     *
     * @param {jQuery Event} e
     * @param {jQuery object} $item
     */
    handleAssociatedAdded : function(e, $item)
    {
      this.updateCounts();
    },

    /**
     * Handle the event when associated item is removed from the list
     *
     * @param {jQuery Event} e
     * @param {jQuery Object} $item Item(s) which have been removed
     * @param {Boolean} doNotSort True is not to sort the available list
     */
    handleAssociatedRemoved : function(e, $item, doNotSort)
    {
      // sort available list
      if(!doNotSort)
      {
        this.sortAvailableList();
      }
    },

    /**
     * Returns item comparator function or null
     *
     * @returns {Function|Null}
     */
    getComparator: function()
    {
      return this.options.sortMethod ? typeof this.options.sortMethod === 'function'
              ? this.options.sortMethod
              : ItemComparator[this.options.sortMethod]
              : null;
    },

    /**
     * Sorts available list using the sorting mechanism
     *
     * @see http://stackoverflow.com/questions/1134976/how-may-i-sort-a-list-alphabetically-using-jquery
     */
    sortAvailableList: function()
    {
      var $items = this.$availableList.children('li');
      var comparator = this.getComparator();

      if(!comparator)
      {
        return;
      }

      $items.sort(comparator);
      this._updateSort(0, $items);
    },

    _updateSort: function(isFirstChunk, $items)
    {
      var i;
      var itemsCount = $items.length;
      var iLast = Math.min(isFirstChunk + this.options.batchSize, itemsCount);
      var timerId = 'sort_available';

      // first chunk
      if(isFirstChunk === 0)
      {
        if(typeof this.timers[timerId] !== 'undefined')
        {
          clearTimeout(this.timers[timerId]);
          delete this.timers[timerId];
        }
        this.$available.addClass('loading');
      }

      for(i = isFirstChunk; i < iLast; i++)
      {
        this.$availableList.append($items[i]);
      }

      var that = this;
      if(iLast < $items.length)
      {
        this.timers[timerId] = setTimeout(function()
        {
          that._updateSort(iLast, $items);
        }, 0);
      }
      else
      {
        // clean up
        delete this.timers[timerId];
        this.$available.removeClass('loading');

        this.updateCounts();
      }
    },

    /**
     * Updates information counts
     *
     */
    updateCounts: function()
    {
      var associated = this.$associatedList.children('li');
      var available = this.$availableList.children('li');

      var associatedCount = associated.length;
      var associatedHiddenCount = associated.filter(':hidden').length;

      var availableCount = available.length;
      var availableHiddenCount = available.filter(':hidden').length;

      // we assume that the filter is active when there are hidden
      var associatedText = associatedCount;
      if(associatedHiddenCount)
      {
        associatedText = (associatedCount - associatedHiddenCount) + ' / ' + associatedCount;
      }

      var availableText = availableCount;
      if(availableHiddenCount)
      {
        availableText = (availableCount - availableHiddenCount) + ' / ' + availableCount;
      }

      this.$element.find(this.getClassName('associated-count', true)).text(associatedText);
      this.$element.find(this.getClassName('available-count', true)).text(availableText);
    },

    /**
     * Setup the items to be resizable
     *
     * @returns
     */
    setupResizable : function()
    {
      var resizableOptions = this.options.resizable;
      if(resizableOptions.minHeight === false)
      {
        resizableOptions.minHeight = this.$available.find(this.getClassName('items', true)).outerHeight();
      }

      // FIXME: append the handle dynamically, not depend on HTML markup

      // available items
      this.$available.find(this.getClassName('items', true)).resizable(
        $.extend({}, resizableOptions, {
          handles: { s: this.$available.find(this.getClassName('resizable-handle', true)) },
          alsoResize: this.$associated.find(this.getClassName('items', true))
        })
      );

      // associated items
      this.$associated.find(this.getClassName('items', true)).resizable(
          $.extend({}, resizableOptions, {
          handles: { s: this.$associated.find(this.getClassName('resizable-handle', true)) },
          alsoResize: this.$available.find(this.getClassName('items', true))
        })
      );
    },

    /**
     * Handle the event when user clicked on the "moveToAvailable" button
     *
     * @param {jQuery Object} Button which has been clicked
     * @param {jQuery.Event} e jQuery event
     */
    moveToAvailableClicked: function($button, e)
    {
      var itemsToMove = this.$associatedList.find('.' + this.options.selectedClass);
      if(!itemsToMove.length)
      {
        // FIXME: give feedback to user?
        return;
      }
      this.moveToAvailable(itemsToMove);
    },

    /**
     * Handle the event when user clicked on the "moveToAssociated" button
     *
     * @param {jQuery Object} Button which has been clicked
     * @param {jQuery.Event} e jQuery event
     */
    moveToAssociatedClicked: function($button, e)
    {
      var itemsToMove = this.$availableList.find('.' + this.options.selectedClass);
      if(!itemsToMove.length)
      {
        // FIXME: give feedback to user?
        return;
      }
      this.moveToAssociated(itemsToMove);
    },

    /**
     * Move item(s) to the available list
     *
     * @param {jQuery Object} $items
     */
    moveToAvailable: function($items, doNotSort)
    {
      this._moveTo(0, $items, false);
    },

    /**
     * Move item(s) to the available list
     *
     * @param {jQuery Object} $items
     */
    moveToAssociated: function($items)
    {
      this._moveTo(0, $items, true);
    },

    _moveTo: function(isFirstChunk, items, toAssociated)
    {
      var i;
      var itemsCount = items.length;
      var iLast = Math.min(isFirstChunk + this.options.batchSize, itemsCount);
      var timerId = 'move_to' + (toAssociated ? '_associated' : '_available');

      // first chunk
      if(isFirstChunk === 0)
      {
        if(typeof this.timers[timerId] !== 'undefined')
        {
          clearTimeout(this.timers[timerId]);
          delete this.timers[timerId];
        }

        // we will have a large amount of work
        if(itemsCount > this.options.batchSize)
        {
          this.$associated.addClass('loading');
          this.$available.addClass('loading');
        }
      }

      for(i = isFirstChunk; i < iLast; i++)
      {
        if(toAssociated)
        {
          this.$associatedList.append(items[i]);
          this.markAsAssociated($(items[i]));
        }
        else
        {
          this.$availableList.append(items[i]);
          this.markAsAvailable($(items[i]));
        }
      }

      var that = this;
      if(iLast < items.length)
      {
        this.timers[timerId] = setTimeout(function()
        {
          that._moveTo(iLast, items, toAssociated);
        }, 0);
      }
      else
      {
        // clean up
        delete this.timers[timerId];

        if(itemsCount > this.options.batchSize)
        {
          this.$associated.removeClass('loading');
          this.$available.removeClass('loading');
        }

        if(toAssociated)
        {
          this.$element.trigger('associated_added.dual_list', [items]);
        }
        else
        {
          this.$element.trigger('associated_removed.dual_list', [items]);
        }
      }
    },

    /**
     * Mark item as associated
     *
     * @param {jQuery Object} $items
     */
    markAsAssociated: function($items)
    {
      // mark the item as associated
      $items.data('associated', true).removeClass(this.options.selectedClass)
      // check the checkbox
      $items.find('input:checkbox').prop('checked', true).trigger('change');
    },

    markAsAvailable: function($items)
    {
      $items.removeData('associated').removeClass(this.options.selectedClass);
      $items.find('input:checkbox').prop('checked', false).trigger('change');
    },

    /**
     * Create button for moving the item from available to associated list
     *
     * @returns {jQuery Object} Created button
     */
    getItemExchangeButton: function()
    {
      var button = $('<button type="button" class="' + this.options.buttons.exchangeClass + ' ' +
                        this.getClassName('item-toggle') + '"><i class="' + this.options.icons.itemExchange + '"></i></button>');
      return button;
    },

    /**
     * Handle the event when item exchange button has been clicked
     *
     * @param {jQuery Object} $element The list item
     * @param {jQuery.Event} e
     */
    handleItemExchangeButtonClicked : function(e)
    {
      var $element = $(e.target).parents('li:first');
      this.moveItem($element);
      e.stopPropagation();
      e.preventDefault();
    },

    /**
     * Create sortable handle for item
     *
     * @returns {jQuery Object} Created handle
     */
    getSortableHandle: function()
    {
      return $('<div class="' + this.getClassName('sortable-handle') + '"><i class="' + this.options.icons.itemSort + '"></i></div>');
    },

    /**
     * Handle the event when item has been clicked
     *
     * @param {jQuery Object} $item Clicked item
     * @param {jQuery.Event} event Event
     */
    handleItemClicked: function($item, event)
    {
      $item.toggleClass(this.options.selectedClass);
    },

    /**
     * Moves item to the right place based on where it is. (associated or available)
     *
     * @param {jQuery Object} $item Item
     */
    moveItem: function($item)
    {
      if($item.data('associated'))
      {
        // move back
        this.moveToAvailable($item);
      }
      else
      {
        // move back
        this.moveToAssociated($item);
      }
    },

    /**
     * Setup item events
     *
     * @param {jQuery Object $item
     */
    setupItemEvents: function($item)
    {
      // click event
      $item.on('click.dual_list', $.proxy(this.handleItemClicked, this, $item));
    },

    /**
     * Setups items in a batch
     *
     * @param {Integer} iFirst
     * @param {jQuery Object} $items
     * @param {Boolan} isAssociated
     */
    _setupItems : function(iFirst, $items, isAssociated)
    {
      var i;
      var itemsCount = $items.length;
      var iLast = Math.min(iFirst + this.options.batchSize, itemsCount);
      var timerId = 'setup_items' + (isAssociated ? '_associated' : '_available');

      // nothing to do
      if(itemsCount === 0)
      {
        return;
      }

      if(typeof this.button === 'undefined')
      {
        this.button = this.getItemExchangeButton();
      }

      if(this.options.sortableEnabled && typeof this.sortableHandle === 'undefined')
      {
        this.sortableHandle = this.getSortableHandle();
      }

      for(i = iFirst; i < iLast; i++)
      {
        var $item = $($items[i]);

        // mark it as associated
        if(isAssociated)
        {
          this.markAsAssociated($item);
        }

        // exchange button
        this.button.clone().on('click', $.proxy(this.handleItemExchangeButtonClicked, this)).appendTo($item);

        // if sortable, append the handle
        if(this.options.sortableEnabled)
        {
          this.sortableHandle.clone().prependTo($item);
        }

        this.setupItemEvents($item);
      }

      var that = this;
      if(iLast < $items.length)
      {
        this.timers[timerId] = window.setTimeout(function()
        {
          // recursive call
          that._setupItems(iLast, $items, isAssociated);
        }, 0);
      }
      else
      {
        // clean up
        delete this.timers[timerId];
        if(!isAssociated)
        {
          delete this.button;
          delete this.sortableHandle;
        }
      }
    },

    /**
     * Setups lists (available and associated)
     *
     */
    setupListItems: function()
    {
      if(this.options.sortableEnabled)
      {
        // var sortableHandle = this.getSortableHandle();
        var sortableOptions = $.extend({}, {
          handle: this.getClassName('sortable-handle', true),
          axis: 'y',
          appendTo: document.body,
          cursor: 'move',
          forceHelperSize: true,
          placeholder: this.getClassName('sortable-placeholder')
        }, this.options.sortable);
        // make the list sortable
        this.$associatedList.sortable(sortableOptions);
      }

      this.$availableList.disableSelection();
      this.$associatedList.disableSelection();

      // setup items
      this._setupItems(0, this.$availableList.children('li'), false);
      this._setupItems(0, this.$associatedList.children('li'), true);
    }
  };

  /**
   *
   * @param {Object} option
   * @returns {Mixed}
   */
  $.fn.dualList = function(option)
  {
    return this.each(function()
    {
      var $this = $(this);
      var data = $this.data('dualList');
      var options = typeof option === 'object' && option;
      if (!data)
      {
        $this.data('dualList', (data = new DualList(this, options)));
      }
      if (typeof option === 'string')
      {
        data[option]();
      }
    });
  };

  // default options
  $.fn.dualList.defaults = {
    // are the associated items sortable?
    sortableEnabled: true,
    // options for sortable
    sortable: {},
    batchSize: 100,
    // buttons
    buttons: {
      // class name for the exchange button
      exchangeClass: 'btn btn-mini'
    },
    // takes position from data-position attribute of the list item (li element)
    // see ItemComparator
    sortMethod: 'position',
    // sortMethod: null,
    // icons
    icons: {
      // class name for the i element of the exchange button
      itemExchange: 'icon-exchange',
      itemSort: 'icon-sort'
    },

    // minimum height of the lists when resizable
    // resizable feature
    resizableEnabled: true,
    // options for resizable
    resizable: {
      minHeight: false
    },
    // case css class, see the docs on the required markup
    selectedClass: 'selected',
    baseClass: 'dual-list'
  };

  $.fn.dualList.Constructor = DualList;

}(window.jQuery));
