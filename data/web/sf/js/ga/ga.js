/*
 * This file is part of the myCorePlugin package.
 * (c) 2010 Mishal.cz <mishal@mishal.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

var gaTracker = function()
{
   /**
   * Tracks event to Google Analytics server
   *
   * @param string Category name
   * @param string label or url
   */
  var trackEvent = function(category, label)
  {
    if(Config.get('debug'))
    {
      Logger.info('Tracking event: ' + category + ' ' + label);
      return;
    }
    try {
      _gaq.push(['_trackEvent', category, 'click', label]);
      sleep();
    }
    catch(e) {}
  };

  /**
   * Track page view to Google Analytics server
   *
   * @param string Url to track (will be converted to "/outgoing/url")
   */
  var trackPageView = function(url)
  {
    var page = '/outgoing/' + url;

    if(Config.get('debug'))
    {
      Logger.info('Tracking page view: ' + url);
      return;
    }

    try {
      _gaq.push(['_trackPageView', page]);
      sleep();
    }
    catch(e) {}
  };


  /**
   * Sleep for 300ms.
   *
   * Pause to allow google script to run
   */
  var sleep = function()
  {
    var date = new Date();
    var curDate = null;
    do {
      curDate = new Date();
    } while(curDate - date < 300);
  };
  
  // public API
  return {
    'trackEvent': trackEvent,
    'trackPageView': trackPageView
  }

}();


/**
 * Attach Google Analytics tracking to application
 *
 */
Application.behaviors.gaSetup = function(context) {

  // downloadble files
  var trackDownloadExtensions = '7z|aac|arc|arj|asf|asx|avi|bin|csv|doc|exe|flv|gif|gz|gzip|hqx|jar|jpe?g|js|mp(2|3|4|e?g)|mov(ie)?|msi|msp|pdf|phps|png|ppt|qtm?|ra(m|r)?|sea|sit|tar|tgz|torrent|txt|wav|wma|wmv|wpd|xls|xml|z|zip';
  var trackDownload           = true;
  var trackMailto             = true;
  var trackOutgoing           = true;
  // Expression to check for absolute internal links.
  var isInternal = new RegExp("^(https?):\/\/" + window.location.host, "i");

  // Expression to check for download links.
  var isDownload = new RegExp("\\.(" + trackDownloadExtensions + ")$", "i");
  
  // Attach onclick event to all links.
  $('a').live('click', function() {

    var that = $(this);

    // Is the clicked URL internal?
    if(isInternal.test(this.href)) {
      // Is download tracking activated and the file extension configured for download tracking?
      if (trackDownload && isDownload.test(this.href)) {
        // download link clicked.
        var extension = isDownload.exec(this.href);
        gaTracker.trackEvent('download', extension[1].toUpperCase(), this.href.replace(isInternal, ''));
      }
      else
      {
        // banners!
        if(that.attr('rel') == 'banner')
        {
          gaTracker.trackEvent('banner', that.attr('title') ? that.attr('title') : that.find('img:first').attr('alt'));
        }
        else
        {
          // Keep the internal URL for Google Analytics website overlay intact.
          var title = that.text();
          gaTracker.trackEvent(title, this.href.replace(isInternal, ''));
        }
      }
    }
    else {
      if(trackMailto && $(this).is("a[href^=mailto:]")) {
        // mailto link clicked.
        gaTracker.trackEvent('email', this.href.substring(7));
      }
      else if(trackOutgoing) {
        // external link clicked.
        gaTracker.trackPageView('outgoing', this.href)
      }
    }
  });
  
}