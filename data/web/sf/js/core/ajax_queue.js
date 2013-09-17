/**
 * Ajax queue plugin for jQuery
 *
 * This pluging creates a new method which ensures only one AJAX request is running at a time.
 * It waits for the previous request(s) to finish before starting a new one using jQuery's built in queue.
 *
 * <pre>
 * jQuery.ajaxQueue(options)
 * </pre>
 *
 * Takes the same options as [jQuery.ajax](http://api.jquery.com/jQuery.ajax), and returns a promise.
 *
 * The return value is not a `jqXHR`, but it will behave like one.  The `abort()` method on the returned
 * object will remove the request from the queue if it has not begun, or pass it along to the jqXHR's
 * abort method once the request begins.
 *
 * @memberOf jQuery
 * @name ajaxQueue
 * @function
 * @author Corey Frang
 * @link https://github.com/gnarf/jquery-ajaxQueue
 * @requires jQuery
 */
(function($) {

// jQuery on an empty object, we are going to use this as our Queue
  var ajaxQueue = $({});

  $.ajaxQueue = function(ajaxOpts) {
    var jqXHR,
            dfd = $.Deferred(),
            promise = dfd.promise();

    // run the actual query
    function doRequest(next) {
      jqXHR = $.ajax(ajaxOpts);
      jqXHR.done(dfd.resolve)
              .fail(dfd.reject)
              .then(next, next);
    }

    // queue our ajax request
    ajaxQueue.queue(doRequest);

    // add the abort method
    promise.abort = function(statusText) {

      // proxy abort to the jqXHR if it is active
      if(jqXHR) {
        return jqXHR.abort(statusText);
      }

      // if there wasn't already a jqXHR we need to remove from queue
      var queue = ajaxQueue.queue(),
              index = $.inArray(doRequest, queue);

      if(index > -1) {
        queue.splice(index, 1);
      }

      // and then reject the deferred
      dfd.rejectWith(ajaxOpts.context || ajaxOpts, [promise, statusText, ""]);
      return promise;
    };

    return promise;
  };

})(jQuery);
