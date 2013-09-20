<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfHttpDonwload provides methods for downloading files over network.
 * Supports section downloading.
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
   * Public cache
   */
  const CACHE_PUBLIC = 'public';

  /**
   * Private cache
   */
  const CACHE_PRIVATE = 'private';

  /**
   * Default options
   *
   * @var array
   */
  protected $defaultOptions = array(
     // Use download resume?
    'use_resume' => true,
    // Buffer size in bytes (default to 2MB)
    'buffer_size' => 2000000,
    // Allow clients to cache the download?
    'allow_cache' => true,
    // Speed limit? False to disable or value in MB/s
    'speed_limit' => false,
    // Cache control (public, private)
    'cache_control' => self::CACHE_PUBLIC,
    // Max age of cache on the client side in seconds
    'cache_max_age' => 3600,
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
    'allow_cache',
    'cache_control',
    'cache_max_age',
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
   * Setups the downloader
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

    if(isset($options['cache_max_age']))
    {
      $this->setCacheMaxAge($options['cache_max_age']);
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
    $headers = array();
    $this->response->clearHttpHeaders();

    $statusCode = 200;
    $isHeaderOnly = false;

    $filename = $this->getFilename();
    $headers['Content-Disposition'] = sprintf('%s; filename="%s"', $this->getContentDisposition(),
        $this->fixFilename($filename ? $filename : $this->guessFilename()));

    if(!($contentType = $this->getContentType()))
    {
      if($this->file)
      {
        $contentType = sfMimeType::getTypeFromFile($this->file);
      }
      elseif($this->data)
      {
        $contentType = sfMimeType::getTypeFromString($this->data);
      }
      elseif($filename)
      {
        $contentType = sfMimeType::getTypeFromExtension($filename);
      }
    }

    $headers['Content-Type'] = $contentType;

    if($this->useResume())
    {
      $headers['Accept-Ranges'] = 'bytes';
    }
    else
    {
      $headers['Accept-Ranges'] = 'none';
    }

    $headers['Cache-Control'] = sprintf('%s, must-revalidate, max-age=%s', $this->getCacheControl(), $this->getCacheMaxAge());

    if($this->allowCache())
    {
      $etag = $this->getEtag() ? $this->getEtag() : $this->generateETag();
      $headers['ETag'] = $etag;

      $isCached = false;
      // timestamp
      if(($modifiedSince = $this->getRequest()->getHttpHeader('IF_MODIFIED_SINCE')))
      {
        if($this->lastModified == strtotime(current(explode(';', $modifiedSince))))
        {
          $isCached = true;
        }
      }

      // Etag
      if(($noneMatch = $this->getRequest()->getHttpHeader('IF_NONE_MATCH')))
      {
        $isCached = $this->compareAsterisk($noneMatch, $etag);
      }

      $headers['Pragma'] = 'cache';
      $headers['Last-Modified'] = $this->response->getDate($this->getLastModified());

      if($isCached)
      {
        // clear other headers
        $headers = array();
        $statusCode = 304;
        $isHeaderOnly = true;
      }
    }
    else
    {
      $headers['Pragma'] = 'no-cache';
    }

    $this->response->setStatusCode($statusCode);
    $this->response->isHeaderOnly($isHeaderOnly);
    foreach($headers as $header => $value)
    {
      $this->response->setHttpHeader($header, $value);
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
    $this->etag = $etag;
    return $this;
  }

  /**
   * Returns the Etag
   *
   * @return string|null
   */
  public function getEtag()
  {
    return $this->etag;
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
   * Pre send. Dispatches an event `download.before_send` with parameters:
   *
   *  * download: sfHttpDownload (current download instance)
   */
  protected function preSend()
  {
    $this->dispatchEvent('download.before_send');
    $this->log('Sending download.');
  }

  /**
   * Post send. Dispatches events:
   *
   * `download.finished` when file was downloaded successfully with parameters:
   *   * download: sfHttpDownload (current download instance)
   *
   * * `download.aborted` when user aborted the download with parameters:
   *   * download: sfHttpDownload (current download instance)
   *
   * Warning: If you want to track the finished downloads do not use
   * resume. When using resume, there are `download.aborted` events fired
   * for the chunks!
   *
   */
  protected function postSend()
  {
    // something has been sent
    if($this->bandwidth > 0)
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
        $this->log('Sending aborted by user.: '  . $this->remaining);
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
      if(!preg_match('/^bytes=((\d*-\d*,? ?)+)$/', $range))
      {
        $this->response->setStatusCode(416);
        $this->response->setHttpHeader('Content-Range', sprintf('bytes */%s', $this->size));
        $this->response->sendHttpHeaders();
        return;
      }

      $range = explode('-', substr($range, strlen('bytes=')));
      $startPoint = intval($range[0]);

      if($range[1] > 0)
      {
        $endPoint = $range[1];
      }

      if($startPoint > $endPoint)
      {
        $this->response->setStatusCode(416);
        $this->response->setHttpHeader('Content-Range', sprintf('bytes */%s', $this->size));
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
      $this->log('Limitting speed to {limit} MB/s', sfILogger::INFO, array('limit' => round($this->getBufferSize() / 1000 / 1000, 5)));
    }

    $this->log('Sending total {size} B', sfILogger::INFO, array('size' => $this->remaining));

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
      if(!sfConfig::get('sf_test'))
      {
        ob_flush();
      }

      $this->bandwidth += $chunkSize;
      $this->remaining -= $chunkSize;

      $this->log('Sent {bytes} B', sfILogger::DEBUG, array('bytes' => $this->bandwidth));

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
    $file = realpath($file);

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
      // what is this?
      return sprintf('download-%s.bin', time());
    }
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
   * Whether to allow proxies to cache
   *
   * If set to 'private' proxies shouldn't cache the response.
   * This setting defaults to 'public' and affects only cached responses.
   *
   * @param string $cacheControl private or public
   * @param integer $maxage Maximum age of the client cache entry
   * @return sfHttpDownload
   * @throws InvalidArgumentException If the cache control is invalid
   */
  public function setCacheControl($cacheControl, $maxAge = null)
  {
    switch($cacheControl = strtolower($cacheControl))
    {
      case self::CACHE_PUBLIC:
      case self::CACHE_PRIVATE:
        $this->setOption('cache_control', $cacheControl);
        if($maxAge)
        {
          $this->setCacheMaxAge($maxAge);
        }
      break;

      default:
        throw new InvalidArgumentException(sprintf('Invalid cache type "%s" given. This should be one of: "public" or "private".', $cache));
      break;
    }
    return $this;
  }

  /**
   * Sets the cache max age
   *
   * @param integer $maxAge
   * @return sfHttpDownload
   * @throws InvalidArgumentException
   */
  public function setCacheMaxAge($maxAge)
  {
    if($maxAge < 0)
    {
      throw new InvalidArgumentException(sprintf('Invalid cache max age "%s" given. Should be greater than zero', $maxAge));
    }
    $this->setOption('cache_max_age', $maxAge);
    return $this;
  }

  /**
   * Returns the cache control
   *
   * @return string
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

}
