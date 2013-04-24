/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Local storage implementation with fallback to cookie storage.
 *
 * Native HTML5 Storage is supported:
 *
 *  * IE 8.0+
 *  * Firefox 3.5+
 *  * Safari 4.0+
 *  * Chrome 4.0+
 *  * Opera 10.5+
 *  * iPhone 2.0+
 *  * Android 2.0+
 *
 * @class
 * @name LocalStorage
 * @requires Cookie
 * @link
 */
(function(window) {

  'use strict';

  var LocalStorage = function()
  {
    this.storageSupported = 'localStorage' in window && window.localStorage !== null;
  };

  LocalStorage.prototype = {

    constructor: LocalStorage,

    /**
     * Is native storage supported?
     *
     * @return {Boolean} True is native local storage is supported
     */
    isNativeStorageSupported : function()
    {
      return this.storageSupported;
    },

    /**
     * Sets a key with the given value to the local storage
     *
     * @param {String} name Name
     * @param {String} value Value
     *
     */
    set: function(name, value)
    {
      if(!this.isNativeStorageSupported())
      {
        Cookie.set(name, value);
      }
      else
      {
        window.localStorage.setItem(name, value);
      }
    },

    /**
     * Returns an item from the storage. If the item is not found,
     * returns defaultValue
     *
     * @param {String} name Name of the key
     * @param {String} defaultValue Default value to return if no value was not found
     */
    get: function(name, defaultValue)
    {
      if(!this.isNativeStorageSupported())
      {
        var value = Cookie.get(name);
      }
      else
      {
        var value = window.localStorage.getItem(name);
      }

      if(typeof value === 'undefined' || value === null)
      {
        return defaultValue;
      }

      return value;
    },

    /**
     * Removes an item with given name from the local storage
     *
     * @param {String} name Key name to remove from the storage
     */
    remove: function(name)
    {
      if(!this.isNativeStorageSupported())
      {
        Cookie.remove(name);
      }
      else
      {
        window.localStorage.removeItem(name);
      }
    },

    /**
     * Clears all items from local storage
     *
     */
    clear: function()
    {
      if(!this.isNativeStorageSupported())
      {
        return false;
      }
      else
      {
        return window.localStorage.clear();
      }
    },

    /**
     * Is the storage empty?
     *
     * @return {Boolean} True is the storage is empty
     */
    isEmpty: function()
    {
      if(!this.isNativeStorageSupported())
      {
        // WHAT to return?
        return;
      }
      return window.localStorage.length > 0;
    },

    /**
     * Returns all key names in the storage
     *
     * @return {Array} Key names
     */
    getNames: function()
    {
      if(!this.isNativeStorageSupported())
      {
        return [];
      }

      var names = [];

      for(var key in window.localStorage)
      {
        names.push(key);
      }

      return names;
    }
  };

  // export
  window.LocalStorage = new LocalStorage();

}(window));