/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Request Storage
 *
 * Provides a per request storage in memory. Implements the HTML5 web storage interface.
 *
 * Usage:
 *
 * Add item to storage:
 *
 * <pre>
 * RequestStorage.setItem("keyName", "value");
 * </pre>
 *
 * Get item from storage:
 *
 * <pre>
 * RequestStorage.getItem("keyName");
 * </pre>
 *
 * Remove item from storage:
 *
 * <pre>
 * RequestStorage.removeItem("keyName");
 * </pre>
 *
 * Clear all items from storage:
 *
 * <pre>
 * RequestStorage.clear();
 * </pre>
 *
 * Get storage size:
 *
 * <pre>
 * RequestStorage.length();
 * </pre>
 *
 * Get key from storage based on position:
 *
 * <pre>
 * RequestStorage.key(1);
 * </pre>
 *
 * @class
 * @author Adam Ayres
 * @link http://github.com/adamayres/jqueryplugins/tree/master/request-storage
 * @link http://dev.w3.org/html5/webstorage/
 */
var RequestStorage = function()
{
  var data = {};
  var dataKeyMap = [];

  return {
    length: 0,
    key: function(n) {
      return (typeof n === "number" && dataKeyMap.length >= n && n >= 0) ? dataKeyMap[n] : null;
    },
    getItem: function(key) {
      return data.hasOwnProperty(key) ? data[key] : null;
    },
    setItem: function(key, value) {
      if (!data.hasOwnProperty(key)) {
        this.length++;
        dataKeyMap.push(key);
      }
      data[key] = value;
    },
    removeItem: function(key) {
      if (data.hasOwnProperty(key)) {
        this.length--;
        for (var i = 0; i < dataKeyMap.length; i++) {
          if (dataKeyMap[i] == key) {
            dataKeyMap.splice(i, 1);
          }
        }
      }
      delete data[key];
    },
    clear: function() {
      data = {};
      dataKeyMap = [];
      this.length = 0;
    }
  };
}();
