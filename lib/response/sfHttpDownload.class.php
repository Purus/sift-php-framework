<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfHttpDownload provides methods for downloading files over network.
 * Supports section downloading.
 *
 * ### Available options
 *
 * - `use_resume`: [boolean] Use download resume?
 * - `buffer_size`: [integer] Buffer size for file reasing in bytes (default to 2MB)
 * - `cache_control`: [string] The available controls are: `public`, `private`, `private_no_expire`, `nocache`
 * - `client_lifetime`: [integer] Max age of cache on the client side in seconds
 * - `vary`: [array] Vary headers
 * - `etag`: [string|false] Custom etag, if false Etag will be disabled
 * - `speed_limit`: [integer|false] False to disable or value in MB/s
 * - `content_disposition`: [string]: Disposition either `attachment` or `inline`
 * - `filename`: [string]: Name of the filename
 *
 * ### Dispatched events
 *
 * |---------------------------------------------------------------------------
 * | Event name              | When dispatched           | Parameters
 * |---------------------------------------------------------------------------
 * | `download.before_send`  | before sending the file   | downloader, response
 * | `download.finished`     | when succesfully finished | downloader
 * | `download.aborted`      | when download aborted     | downloader
 *
 * 1) **`download.before_send`**
 *
 * Dispatched before sending the file (data).
 *
 * ## Parameters
 *
 * - **download**: sfHttpDownload (current download instance)
 * - **response**: sfWebResponse (current web response instance)
 *
 * 2) **`download.finished`**
 *
 * Dispatched when file was downloaded successfully.
 *
 * ## Parameters
 *
 *  - download: sfHttpDownload (current download instance)
 *
 * 3) **`download.aborted`**
 *
 * Dispatched when user aborted the download.
 *
 * ## Parameters
 *
 * - download: sfHttpDownload (current download instance)
 *
 * ### Warning
 *
 * If you want to track the finished downloads do not use
 * resume. When using resume, the `download.aborted` events are fired
 * for all of the downloaded chunks!
 *
 * @package    Sift
 * @subpackage response
 */
class sfHttpDownload extends sfConfigurable implements sfIEventDispatcherAware, sfILoggerAware {

  /**
   * Donwload attachment
   */
  const DOWNLOAD_ATTACHMENT = 'attachment';

  /**
   * Download inline
   */
  const DOWNLOAD_INLINE = 'inline';

  /**
   * Permits caching by proxies and the client
   *
   */
  const CACHE_CONTROL_PUBLIC = 'public';

  /**
   * Disallow caching by proxies and permits the client to cache the contents
   */
  const CACHE_CONTROL_PRIVATE = 'private';

  /**
   * Download may not be cached.
   */
  const CACHE_CONTROL_PRIVATE_NO_EXPIRE = 'private_no_expire';

  /**
   * Disallow any client/proxy caching
   */
  const CACHE_CONTROL_NO_CACHE = 'nocache';

  /**
   * Default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    'use_resume' => true,
    // Buffer size in bytes (default to 2MB)
    'buffer_size' => 2097152,
    // Cache control
    'cache_control' => self::CACHE_CONTROL_PUBLIC,
    // Max age of cache on the client side in seconds
    'client_lifetime' => 0,
    // vary headers
    'vary' => array(),
    // Custom etag, if false Etag will be disabled
    'etag' => null,
    // Speed limit? False to disable or value in MB/s
    'speed_limit' => false,
    // content disposition of the download
    'content_disposition' => self::DOWNLOAD_ATTACHMENT
  );

  /**
   * Path to file for download
   *
   * @see sfHttpDownload::setFile()
   * @var string
   */
  protected $file;

  /**
   * Data for download
   *
   * @see sfHttpDownload::setData()
   * @var string
   */
  protected $data;

  /**
   * Size of download
   *
   * @var integer
   */
  protected $size = 0;

  /**
   * Last modified timestamp
   *
   * @var integer
   */
  protected $lastModified = 0;

  /**
   * Is this a range request?
   *
   * @var boolean
   */
  protected $isRangeRequest = false;

  /**
   * Total bandwidth has been used for this download
   *
   * @var integer
   */
  protected $bandwidth = 0;

  /**
   * sfResponse
   *
   * @var sfResponse
   */
  protected $response;

  /**
   * The logger instance
   *
   * @var sfILogger
   */
  protected $logger;

  /**
   * The storage instance
   *
   * @var sfIStorage
   */
  protected $storage;

  /**
   * The request instance
   *
   * @var sfWebRequest
   */
  protected $request;

  /**
   * Aborted flag
   *
   * @var boolean
   */
  protected $aborted = false;

  /**
   * Valid options
   *
   * @var array
   */
  protected $validOptions = array(
    'speed_limit',
    'content_disposition',
    'filename',
    'use_resume',
    'buffer_size',
    'cache_control',
    'client_lifetime',
    'vary',
    'etag',
    'content_type', 'mime' // mime is for BC
  );

  /**
   * Constructs the downloader
   *
   * @param array $options
   * @param sfWebRequest $request
   * @param sfWebResponse $response
   * @param sfEventDispatcher $dispatcher
   * @param sfILogger $logger
   */
  public function __construct($options = array(), sfWebRequest $request,
      sfWebResponse $response, sfEventDispatcher $dispatcher = null, sfILogger $logger = null)
  {
    $this->setEventDispatcher($dispatcher);
    $this->setRequest($request);
    $this->setResponse($response);
    $this->setLogger($logger);
    parent::__construct($options);
  }

  /**
   * Setups the downloader.
   *
   */
  public function setup()
  {
    $options = $this->getOptions();

    if(isset($options['speed_limit']))
    {
      $this->limitSpeed($options['speed_limit']);
    }

    if(isset($options['filename']))
    {
      $this->setFilename($options['filename']);
    }

    if(isset($options['content_disposition']))
    {
      $this->setContentDisposition($options['content_disposition']);
    }

    if(isset($options['use_resume']))
    {
      $this->useResume($options['use_resume']);
    }

    // BC compatibility
    if(isset($options['mime']))
    {
      $this->setContentType($options['mime']);
    }
    elseif(isset($options['content_type']))
    {
      $this->setContentType($options['content_type']);
    }

    if(isset($options['etag']))
    {
      $this->setEtag($options['etag']);
    }

    if(isset($options['allow_cache']))
    {
      $this->allowCache($options['allow_cache']);
    }

    if(isset($options['cache_control']))
    {
      $this->setCacheControl($options['cache_control']);
    }

    if(isset($options['client_lifetime']))
    {
      $this->setClientLifetime($options['client_lifetime']);
    }

    if(isset($options['vary']))
    {
      $this->setVary($options['vary']);
    }
  }

  /**
   * Prepare the sending.
   *
   * * cleans output buffers
   * * disables time_limit for the script (only when allowed by the environment)
   * * sets ignore user abort
   */
  protected function prepareSending()
  {
    // we need it for abort detection
    ignore_user_abort(true);

    // set the time limit
    if(!sfToolkit::setTimeLimit(0))
    {
      $this->log('Script execution time limit could not be set.', sfILogger::WARNING);
    }

    $this->prepareResponse();
  }

  /**
   * Prepare headers for the response
   *
   * @return array
   */
  protected function prepareResponse()
  {
    $this->response->clearHttpHeaders();

    switch(($cacheControl = $this->getCacheControl()))
    {
      // cache is allowed for this control types:
      case self::CACHE_CONTROL_PUBLIC:
      case self::CACHE_CONTROL_PRIVATE:
      case self::CACHE_CONTROL_PRIVATE_NO_EXPIRE:

        $isCached = false;

        if(($modifiedSince = $this->getRequest()->getHttpHeader('IF_MODIFIED_SINCE')))
        {
          if($this->lastModified == strtotime(current(explode(';', $modifiedSince))))
          {
            $isCached = true;
          }
        }

        if(($etag = $this->getEtag()) !== false && ($noneMatch = $this->getRequest()->getHttpHeader('IF_NONE_MATCH')))
        {
          if(!$etag)
          {
            $etag = $this->generateETag();
          }
          $isCached = $this->compareAsterisk($noneMatch, $etag);
        }

        // the download is cached on client side, return
        if($isCached)
        {
          $this->log('Download is cached, setting 304 (not modified) header.', sfILogger::INFO);
          $this->response->setStatusCode(304);
          $this->response->setHeaderOnly(true);
          return;
        }

      break;
    }

    $this->response->setHttpHeader('Content-Disposition',
        sprintf('%s; filename="%s"', $this->getContentDisposition(), $this->fixFilename($this->getFilename() ? $this->getFilename() : $this->guessFilename())));

    if(!($contentType = $this->getContentType()))
    {
      $contentType = $this->guessContentType();
    }

    $this->response->setHttpHeader('Content-Type', $contentType);

    if($this->useResume())
    {
      $this->response->setHttpHeader('Accept-Ranges', 'bytes');
    }

    $lifetime = $this->getClientLifetime();

    // cache control
    switch($cacheControl)
    {
      // Public cache control:
      // Expires: (sometime in the future, according to client_lifetime)
      // Cache-Control: public, no-transform, max-age=(sometime in the future, according to client_lifetime)
      // Last-Modified: (the timestamp of when the download was last saved)
      // Etag: etag set or generated etag from the downloaded file or data
      case self::CACHE_CONTROL_PUBLIC:

        $this->response->addCacheControlHttpHeader('public, no-transform, must-revalidate');

        if($lifetime)
        {
          $this->response->addCacheControlHttpHeader('max-age', $lifetime);
          $this->response->addCacheControlHttpHeader('s-maxage', $lifetime);
          $this->response->setHttpHeader('Expires', $this->response->getDate(time() + $lifetime));
        }

        $this->response->setHttpHeader('Last-Modified', $this->response->getDate($this->getLastModified()));

        if(($etag = $this->getEtag()) !== false)
        {
          $this->response->setHttpHeader('ETag', $etag ? $etag : $this->generateETag());
        }

      break;

      // Private cache control:
      // Expires: 10 years before now
      // Cache-Control: private, no-transform, must-revalidate, proxy-revalidate, max-age=(client_lifetime), pre-check=(client_lifetime)
      // Last-Modified: (the timestamp of when the download was last modified)
      // Etag: etag set or generated etag from the downloaded file or data
      case self::CACHE_CONTROL_PRIVATE:

        $this->response->addCacheControlHttpHeader('private, no-transform, must-revalidate, proxy-revalidate');

        if($lifetime)
        {
          $this->response->addCacheControlHttpHeader('max-age', $lifetime);
          $this->response->addCacheControlHttpHeader('smax-age', $lifetime);
          $this->response->addCacheControlHttpHeader('pre-check', $lifetime);
        }

        $this->response->setHttpHeader('Expires', $this->response->getDate(strtotime('-10 years')));
        $this->response->setHttpHeader('Last-Modified', $this->response->getDate($this->getLastModified()));

        if(($etag = $this->getEtag()) !== false)
        {
          $this->response->setHttpHeader('ETag', $etag ? $etag : $this->generateETag());
        }

      break;

      // Cache-Control: private, no-transform, must-revalidate, max-age=(client_lifetime in the future), pre-check=(client_lifetime in the future)
      // Last-Modified: (the timestamp of when the download was last modified)
      case self::CACHE_CONTROL_PRIVATE_NO_EXPIRE:

        $this->response->addCacheControlHttpHeader('private, no-transform, must-revalidate, proxy-revalidate');

        if($lifetime)
        {
          $this->response->addCacheControlHttpHeader('max-age', $lifetime);
          $this->response->addCacheControlHttpHeader('smax-age', $lifetime);
          $this->response->addCacheControlHttpHeader('pre-check', $lifetime);
        }

        if(($etag = $this->getEtag()) !== false)
        {
          $this->response->setHttpHeader('ETag', $etag ? $etag : $this->generateETag());
        }

        $this->response->setHttpHeader('Last-Modified', $this->response->getDate($this->getLastModified()));

      break;

      // Expires: 10 years before now
      // Cache-Control: no-store, no-cache, must-revalidate, proxy-revalidate, post-check=0, pre-check=0
      // Etag: is not set
      case self::CACHE_CONTROL_NO_CACHE:

        $this->response->addCacheControlHttpHeader('no-store, no-cache, no-transform, must-revalidate, proxy-revalidate, post-check=0, pre-check=0');
        $this->response->setHttpHeader('Expires', $this->response->getDate(strtotime('-10 years')));

      break;

    }

    // vary headers
    // This causes most of browsers
    if(($vary = $this->getVary()))
    {
      foreach($vary as $varyHeader)
      {
        $this->response->addVaryHttpHeader($varyHeader);
      }
    }
  }

  /**
   * Sets the Etag
   *
   * @param string $etag The Etag
   * @return sfHttpDownload
   */
  public function setETag($etag)
  {
    $this->setOption('etag', $etag);
    return $this;
  }

  /**
   * Returns the Etag
   *
   * @return string|null
   */
  public function getEtag()
  {
    return $this->getOption('etag');
  }

  /**
   * Set the vary headers
   *
   * @param array|string $vary Vary headers
   * @return sfHttpDownload
   */
  public function setVary($vary)
  {
    if(!is_array($vary))
    {
      $vary = array($vary);
    }
    $this->setOption('vary', $vary);
    return $this;
  }

  /**
   * Returns the vary headers
   *
   * @return array
   */
  public function getVary()
  {
    return $this->getOption('vary');
  }

  /**
   * Send the download.
   */
  public function send()
  {
    $this->prepareSending();
    $this->preSend();

    if(!$this->response->isHeaderOnly())
    {
      $this->sendDownload();
    }
    else
    {
      $this->response->sendHttpHeaders();
    }

    $this->postSend();
  }

  /**
   * Sends the download. This is used as as callback for the response
   *
   * @throws LogicException If called directly
   */
  protected function sendDownload()
  {
    // do some clean up
    // cleanup all buffers
    if(!sfConfig::get('sf_test'))
    {
      if(ob_get_level())
      {
        while(@ob_end_clean());
      }
      ob_implicit_flush(true);
    }

    $this->doDownload();
  }

  /**
   * Pre send
   *
   * @return void
   */
  protected function preSend()
  {
    $this->dispatchEvent('download.before_send', array('response' => $this->getResponse()));
    $this->log('Sending download.');
  }

  /**
   * Post send
   *
   * @return void
   */
  protected function postSend()
  {
    // something has been sent
    if($this->getBandwidth() > 0 || $this->response->isHeaderOnly())
    {
      $this->log('Post send, connection status {status}', sfILogger::DEBUG, array('status' => connection_status()));
      if(!$this->aborted)
      {
        $this->log('Download successfully finished.');
        $this->dispatchEvent('download.finished', array(
          'range_request' => $this->isRangeRequest()
        ));
      }
      else
      {
        $this->log('Sending aborted by user.');
        $this->dispatchEvent('download.aborted', array(
          'range_request' => $this->isRangeRequest()
        ));
      }
    }
  }

  /**
   * Does the download job
   *
   * @return void
   */
  protected function doDownload()
  {
    $startPoint = 0;
    $endPoint = $this->size - 1;

    // do we use resume?
    if($this->useResume() &&
        ($range = $this->getRequest()->getHttpHeader('RANGE')))
    {
      // this does not look like valid range
      if(($range = $this->getStartEndPointsFromRange($range)) !== false)
      {
        list($startPoint, $endPoint) = $range;
      }
      // invalid range
      else
      {
        $this->log('Range request invalid "{range}".', sfILogger::WARNING, array(
          'range' => $this->getRequest()->getHttpHeader('RANGE')
        ));

        $this->response->clearHttpHeaders();
        $this->response->setStatusCode(416);
        $this->response->setHttpHeader('Content-Range', sprintf('bytes */%s', $this->size));
        $this->response->setHeaderOnly(true);
        $this->response->sendHttpHeaders();
        return;
      }

      $this->response->setStatusCode(206);
      $this->response->setHttpHeader('Status', '206 Partial Content');
      $this->response->setHttpHeader('Content-Range', sprintf('bytes %s-%s/%s', $startPoint, $endPoint, $this->size));
      $this->response->setHttpHeader('Content-Length', ($endPoint - $startPoint + 1));
      // mark as range request
      $this->isRangeRequest = true;
    }
    else
    {
      $this->response->setHttpHeader('Content-Length', $this->size);
    }

    if($this->file)
    {
      // open file
      $handle = fopen($this->file, 'rb');
    }
    else
    {
      sfStringStreamWrapper::register();
      $handle = fopen('string://', 'r+');
      fputs($handle, $this->data);
    }

    rewind($handle);

    if($startPoint > 0)
    {
      fseek($handle, $startPoint);
    }

    // reset bandwith
    $this->bandwidth = 0;
    $this->remaining = $this->size;

    // send the headers
    $this->response->sendHttpHeaders();

    if(($speedLimit = $this->limitSpeed()))
    {
      $this->log('Limitting speed to {limit}', sfILogger::INFO, array('limit' => $this->formatBytes($this->getBufferSize())));
    }

    $this->log('Sending total {size}', sfILogger::INFO, array('size' => $this->formatBytes($this->remaining)));

    $currentPosition = $startPoint;
    while(!feof($handle) && $currentPosition <= $endPoint)
    {
      if(connection_aborted() || connection_status() != CONNECTION_NORMAL)
      {
        $this->aborted = true;
        break;
      }

      $chunkSize = $this->getBufferSize();
      if($currentPosition + $chunkSize > $endPoint + 1)
      {
        $chunkSize = $endPoint - $currentPosition + 1;
      }

      echo fread($handle, $chunkSize);
      flush();

      $this->bandwidth += $chunkSize;
      $this->remaining -= $chunkSize;

      $this->log('Sent {bytes} B', sfILogger::DEBUG, array('bytes' => $this->formatBytes($this->bandwidth)));

      // increment download point
      $currentPosition += $chunkSize;
      // speed limit
      if($speedLimit)
      {
        $this->sleep(1);
      }
    }

    fclose($handle);
  }

  /**
   * Sets or gets the speed limit
   *
   * @param false|integer $limit The speed limit in bytes per second
   * @param string $unit The speed limit unit (MB, kB...)
   * @return boolean
   */
  public function limitSpeed($limit = null)
  {
    $value = $this->getOption('speed_limit');
    if(!is_null($limit))
    {
      // disable speed limit
      if($limit === false)
      {
        $this->setOption('speed_limit', false);
        // reset the buffer size?
        $this->setBufferSize($this->defaultOptions['buffer_size']);
      }
      else
      {
        $this->setOption('speed_limit', true);
        $this->setBufferSize(round($limit * 1000 * 1000));
      }
    }
    return $value;
  }

  /**
   * Set path to file for download.
   *
   * @param string $file Absolute path to file for download
   * @return sfHttpDownload
   * @throws sfFileException If the file does not exist or is not readable
   */
  public function setFile($file)
  {
    if(!is_readable($file))
    {
      throw new sfFileException(sprintf('File "%s" does not exist or is not readable.', $file));
    }

    $this->file = $file;
    $this->size = filesize($file);
    $this->setLastModified(filemtime($file));
    return $this;
  }

  /**
   * Set or gets the resume mode. Returns old status.
   *
   * @param boolean $flag
   * @return boolean Old value
   */
  public function useResume($flag = null)
  {
    $value = $this->getOption('use_resume');
    if(!is_null($flag))
    {
      $this->setOption('use_resume', (bool) $flag);
    }
    return $value;
  }

  /**
   * Set size of buffer
   *
   * The amount of bytes specified as buffer size is the maximum amount
   * of data read at once from resources or files. The default size is 2M
   * (2097152 bytes).
   *
   * @param integer $bytes Amount of bytes to use as buffer.
   * @return sfHttpDownload
   * @throws InvalidArgumentException If $bytes is not greater than 0 bytes.
   */
  public function setBufferSize($bytes)
  {
    if(0 >= $bytes)
    {
      throw new InvalidArgumentException(sprintf('Buffer size must be greater than 0 bytes ("%s" given)', $bytes));
    }
    $this->setOption('buffer_size', $bytes);
    return $this;
  }

  /**
   * Returns the buffer size
   *
   * @return integer
   */
  public function getBufferSize()
  {
    return $this->getOption('buffer_size');
  }

  /**
   * Returns total number of bytes this download took
   * @return integer
   */
  public function getBandwidth()
  {
    return $this->bandwidth;
  }

  /**
   * Gets or sets the option whether to allow client caching
   *
   * If set to true (default) we'll send some headers that are commonly
   * used for caching purposes like ETag, Cache-Control and Last-Modified.
   *
   * If caching is disabled, we'll send the download no matter if it
   * would actually be cached at the client side.
   *
   * @param boolean $flag whether to allow caching
   * @return boolean The old value
   */
  public function allowCache($flag = null)
  {
    $value = $this->getOption('allow_cache');
    if(!is_null($flag))
    {
      $this->setOption('allow_cache', (bool)$flag);
    }
    return $value;
  }

  /**
   * Set last mofification of the file or data.
   *
   * This is usually determined by filemtime() in sfHttpDownload::setFile()
   * If you set raw data for download with sfHttpDownload::setData() and you
   * want do send an appropiate "Last-Modified" header, you should call this
   * method.
   *
   * @param int|DateTime $lastModified The unix timestamp or DateTime object
   * @return sfHttpDownload
   */
  public function setLastModified($lastModified)
  {
    if($lastModified instanceof DateTime)
    {
      $lastModified = $lastModified->format('U');
    }
    elseif($lastModified instanceof sfDate)
    {
      $lastModified = $lastModified->format('U');
    }

    if($lastModified <= 0)
    {
      throw new InvalidArgumentException(sprintf('Invalid last modified time "%s" given. The value should be greater than zero', $last_modified));
    }

    $this->lastModified = $lastModified;
    return $this;
  }

  /**
   * Returns the last modified time
   *
   * @return integer
   */
  public function getLastModified()
  {
    return $this->lastModified;
  }

  /**
   * Set content disposition. Inline or attachment
   *
   * @param string $disposition whether to send the download inline or as attachment
   * @param string $filename The filename to display in the browser's download window
   * @throws InvalidArgumentException If the disposition is not valid
   */
  public function setContentDisposition($disposition, $filename = null)
  {
    switch($disposition = strtolower($disposition))
    {
      case self::DOWNLOAD_ATTACHMENT:
      case self::DOWNLOAD_INLINE:
        $this->setOption('content_disposition', $disposition);
        if($filename)
        {
          $this->setFilename($filename);
        }
      break;

      default:
        throw new InvalidArgumentException(sprintf('Invalid content disposition "%s" given', $disposition));
    }

    return $this;
  }

  /**
   * Returns the content disposition
   *
   * @return string
   */
  public function getContentDisposition()
  {
    return $this->getOption('content_disposition');
  }

  /**
   * Sets content type of the download
   *
   * @param string $contentType The content mime type
   * @return sfHttpDownload
   * @throws InvalidArgumentException If the content type is not valid
   */
  public function setContentType($contentType)
  {
    if(!preg_match('#^[-\w\+]+/[-\w\+.]+$#', $contentType))
    {
      throw new InvalidArgumentException(sprintf('Invalid content type "%s" given.', $contentType));
    }
    $this->setOption('content_type', $contentType);
    return $this;
  }

  /**
   * Returns the content type
   *
   * @return string
   */
  public function getContentType()
  {
    return $this->getOption('content_type');
  }

  /**
   * Sets the filename
   *
   * @param string $filename The filename of the download
   * @return sfHttpDownload
   */
  public function setFilename($filename)
  {
    $this->setOption('filename', $filename);
    return $this;
  }

  /**
   * Returns the filename
   *
   * @return string
   */
  public function getFilename()
  {
    return $this->getOption('filename');
  }

  /**
   * Returns the file size
   *
   * @return integer
   */
  public function getFileSize()
  {
    return $this->size;
  }

  /**
   * Is range request?
   *
   * @return boolean
   */
  public function isRangeRequest()
  {
    return $this->isRangeRequest;
  }

  /**
   * Set data for download
   *
   * @param string $data raw data to send
   * @return sfHttpDownload
   */
  public function setData($data)
  {
    $this->data = $data;
    $this->size = strlen($data);
    if(!$this->lastModified)
    {
      $this->setLastModified(time());
    }
    return $this;
  }

  /**
   * Sets cache control
   *
   * If set to 'private' proxies shouldn't cache the response.
   * This setting defaults to 'public' and affects only cached responses.
   *
   * @param string|array $cacheControl private or public
   * @return sfHttpDownload
   * @throw InvalidArgumentException If the cache control is not valid
   */
  public function setCacheControl($cacheControl)
  {
    if(!in_array($cacheControl, array(
      self::CACHE_CONTROL_PUBLIC,
      self::CACHE_CONTROL_PRIVATE,
      self::CACHE_CONTROL_PRIVATE_NO_EXPIRE,
      self::CACHE_CONTROL_NO_CACHE
    )))
    {
      throw new InvalidArgumentException(sprintf('Invalid cache control "%s" given. Valid controls are: %s.', $cacheControl,
        '"' . join('", "', array(
            self::CACHE_CONTROL_PUBLIC,
            self::CACHE_CONTROL_PRIVATE,
            self::CACHE_CONTROL_PRIVATE_NO_EXPIRE,
            self::CACHE_CONTROL_NO_CACHE
          )) . '"'
      ));
    }

    $this->setOption('cache_control', $cacheControl);
    return $this;
  }

  /**
   * Sets the cache max age
   *
   * @param integer $lifetime
   * @return sfHttpDownload
   * @throws InvalidArgumentException
   */
  public function setClientLifetime($lifetime)
  {
    if($lifetime < 0)
    {
      throw new InvalidArgumentException(sprintf('Invalid client lifetime value "%s" given. Should be greater than zero', $lifetime));
    }
    $this->setOption('client_lifetime', $lifetime);
    return $this;
  }

  /**
   * Returns the client lifetime
   *
   * @return integer
   */
  public function getClientLifetime()
  {
    return $this->getOption('client_lifetime');
  }

  /**
   * Returns the cache control
   *
   * @return array
   */
  public function getCacheControl()
  {
    return $this->getOption('cache_control');
  }

  /**
   * Returns the cache max age
   *
   * @return integer
   */
  public function getCacheMaxAge()
  {
    return $this->getOption('cache_max_age');
  }

  /**
   * Sets the response
   *
   * @param sfWebResponse $response
   * @return sfHttpDownload
   */
  public function setResponse(sfWebResponse $response)
  {
    $this->response = $response;
    return $this;
  }

  /**
   * Returns the response
   *
   * @return sfWebResponse
   */
  public function getResponse()
  {
    return $this->response;
  }

  /**
   * Sets the request
   *
   * @param sfWebRequest $request
   * @return sfHttpDownload
   */
  public function setRequest(sfWebRequest $request)
  {
    $this->request = $request;
    return $this;
  }

  /**
   * Returns the request
   *
   * @return sfRequest|null
   */
  public function getRequest()
  {
    return $this->request;
  }

  /**
   * Sets the event dispatcher
   *
   * @param sfEventDispatcher $dispatcher
   * @return sfHttpDownload
   */
  public function setEventDispatcher(sfEventDispatcher $dispatcher = null)
  {
    $this->dispatcher = $dispatcher;
    return $this;
  }

  /**
   * Returns the event dispatcher
   *
   * @return sfEventDispatcher|null
   */
  public function getEventDispatcher()
  {
    return $this->dispatcher;
  }

  /**
   * Sets the logger
   *
   * @param sfILogger $logger
   * @return sfHttpDownload
   */
  public function setLogger(sfILogger $logger = null)
  {
    $this->logger = $logger;
    return $this;
  }

  /**
   * Returns the logger
   *
   * @return sfILogger|null
   */
  public function getLogger()
  {
    return $this->logger;
  }


  /**
   * Guesses the filename of the download
   *
   * @return string
   */
  protected function guessFilename()
  {
    if($this->file)
    {
      return basename($this->file);
    }
    else
    {
      return sprintf('download-%s.bin', time());
    }
  }

  /**
   * Guesses the content type
   *
   * @return string
   */
  protected function guessContentType()
  {
    $contentType = 'application/octet-stream';
    if($this->file)
    {
      $contentType = sfMimeType::getTypeFromFile($this->file);
    }
    elseif($this->data)
    {
      $contentType = sfMimeType::getTypeFromString($this->data);
    }
    elseif(($filename = $this->getFilename()))
    {
      $contentType = sfMimeType::getTypeFromExtension($filename);
    }
    return $contentType;
  }

  /**
   * Generates the ETag
   *
   * @return string
   */
  protected function generateETag()
  {
    if($this->data)
    {
      $md5 = md5($this->data);
    }
    else
    {
      $fst = stat($this->file);
      $md5 = md5($fst['mtime'] . '=' . $fst['size']);
    }
    return '"' . $md5 . '-' . crc32($md5) . '"';
  }

  /**
   * Compare against an asterisk or check for equality
   *
   * @param string $variable key for the array
   * @param string $compare string to compare
   * @return boolean
   */
  protected function compareAsterisk($variable, $compare)
  {
    foreach(array_map('trim', explode(',', $variable)) as $request)
    {
      if($request === '*' || $request === $compare)
      {
        return true;
      }
    }
    return false;
  }

  /**
   * Returns the start and end points from the range request
   *
   * @param string $range
   * @return array|false Returns false if the range is invalid
   */
  protected function getStartEndPointsFromRange($range)
  {
    //if(!preg_match('/^bytes=((\d*-\d*,? ?)+)$/', $range))
    if(strpos($range, 'bytes=') !== 0)
    {
      return false;
    }

    $range = explode('-', substr($range, strlen('bytes=')));
    $startPoint = intval($range[0]);
    $endPoint = !empty($range[1]) ? intval($range[1]) : $this->size;

    if($startPoint < $endPoint)
    {
      return array($startPoint, $endPoint);
    }

    return false;
  }

  /**
   * Fixes filename for Internet Explorer. Converts the Utf-8 name to
   * windows-1250 charset
   *
   * @param string $fileName
   * @param string $inputEncoding
   * @return string The fixed filename
   */
  protected function fixFilename($fileName, $inputEncoding = 'UTF-8')
  {
    $userAgent = $this->getRequest()->getHttpHeader('USER_AGENT');
    if($userAgent && function_exists('iconv') && preg_match('|MSIE\s*[1-8]|', $userAgent))
    {
      $fileName = iconv($inputEncoding, 'windows-1250', $fileName);
    }
    return $fileName;
  }

  /**
   * Dispatches event using the event dispatcher
   *
   * @param string $eventName
   * @param array $parameters Array of parameters for the event
   */
  protected function dispatchEvent($eventName, $parameters = array())
  {
    if(!$this->dispatcher)
    {
      return;
    }

    if(!isset($parameters['download']))
    {
      $parameters['download'] = $this;
    }

    $this->dispatcher->notify(new sfEvent($eventName, $parameters));
  }

  /**
   * Logs the message
   *
   * @param string $message The message to log
   * @param integer $level The log level
   * @param array $context
   * @return sfHttpDownload
   */
  protected function log($message, $level = sfILogger::INFO, array $context = array())
  {
    if($this->logger)
    {
      $this->logger->log(sprintf('{sfHttpDownload} %s', $message), $level, $context);
    }
    return $this;
  }

  /**
   * Sleep for $seconds second(s)
   *
   * @param integer $seconds Number of second to sleep
   */
  protected function sleep($seconds = 1)
  {
    $this->log('Sleeping for {seconds}s', sfILogger::DEBUG, array('seconds' => $seconds));
    usleep($seconds * 1000000);
  }

  /**
   * Formats bytes to kB, MB...
   *
   * @param integer $bytes
   * @return string The formatted value
   */
  protected function formatBytes($bytes, $precision = 2)
  {
    // human readable format -- powers of 1024
    $unit = array('B','kB','MB','GB','TB','PB','EB');
    return sprintf('%s %s', @round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), $precision), $unit[$i]);
  }

}
