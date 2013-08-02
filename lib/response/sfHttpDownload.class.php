<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfHttpDonwload provides methods for downloading files over network 
 * (based on downloader class by Nguyen Quoc Bao
 * and HTTP_Download by Michael Wallner)
 * 
 * Supports section downloading.
 * 
 * Tested with:
 * - Reget
 * - FDM
 * - FlashGet
 * - GetRight
 * - DAP
 *
 * @package    Sift
 * @subpackage response
 */
class sfHttpDownload extends sfConfigurable {

  /**
   * Donwload attachment
   */
  const DOWNLOAD_ATTACHMENT = 'attachment';

  /**
   * Download inline
   */
  const DOWNLOAD_INLINE = 'inline';
 
  const CACHE_PUBLIC  = 'public';
  const CACHE_PRIVATE = 'private';
  
  /**
   * Path to file for download
   *
   * @see     sfHttpDownload::setFile()
   * @var     string
   */
  protected $file = null;

  /**
   * Data for download
   *
   * @see     sfHttpDownload::setData()
   * @var     string
   */
  protected $data = null;

  /**
   * Size of download
   *
   * @var integer
   */
  protected $size = 0;

  /**
   * Etag
   *
   * @var string
   */
  protected $etag = null;

  /**
   * Last modified
   *
   * @access  protected
   * @var     int
   */
  protected $lastModified = 0;
  
  /**
   * Is this a range request?
   * 
   * @var boolean 
   */
  protected $isRangeRequest = false;

  /**
   * Use download resume
   *
   * @var boolean
   */
  protected $useResume = true;

  /**
   * Automatically call "exit();" after download
   * 
   * @var boolean
   */
  protected $autoExit = true;
  protected $filename = null;

  /**
   * Buffer size (default to 20MB)
   *
   * @var integer
   */
  protected $bufferSize = 2097152;
  
  /**
   * Seek start (internal pointer)
   * 
   * @var integer 
   */
  protected $seekStart = 0;
  
  /**
   * Seek end (internal pointer)
   * 
   * @var integer 
   */
  protected $seekEnd = -1;

  /**
   * Total bandwidth has been used for this download
   * 
   * @var int
   */
  protected $bandwidth = 0;

  /**
   * LimitSpeed limit
   * 
   * @var float
   */
  protected $limitSpeed = 0;

  /**
   * Whether to allow caching of the download on the clients side
   *
   * @access  protected
   * @var     bool
   */
  protected $cache = true;

  /**
   * sfContext
   * 
   * @var sfContext holder 
   */
  protected $context;
  
  /**
   * sfWebResponse
   * 
   * @var sfWebResponse 
   */
  protected $response;
  
  /**
   * HTTP headers
   *
   * @access  protected
   * @var     array
   */
  protected $headers = array(
    'Pragma' => 'cache',
    'Cache-Control' => 'public, must-revalidate, max-age=0'
  );
  
  /**
   * Default options
   * 
   * @var array 
   */
  protected $defaultOptions = array();

  /**
   * Valid options
   * 
   * @var array 
   */
  protected $validOptions = array(
    'speed_limit', 'content_disposition', 'filename',
    'use_resume', 'mime', 'auto_exit', 'cache',
    'cache_control'
  );
    
  /**
   * Constructs the downloader
   * 
   * @param sfContext $context
   * @param array Array of options
   */
  public function __construct($options = array())
  {
    parent::__construct($options);

    $this->context  = sfContext::getInstance();
    $this->response = $this->context->getResponse();
    
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

    if(isset($options['mime']))
    {
      $this->setContentType($options['mime']);
    }

    if(isset($options['auto_exit']))
    {
      $this->autoExit($options['auto_exit']);
    }
    
    if(isset($options['cache']))
    {
      $this->setCache($options['cache']);
    }

    if(isset($options['cache_control']))
    {
      $this->setCacheControl($options['cache_control']);
    }

  }
  
  protected function initialize()
  {
    if($this->useResume() && isset($_SERVER['HTTP_RANGE']))
    {
      $seek_range = substr($_SERVER['HTTP_RANGE'], strlen('bytes='));
      $range = explode('-', $seek_range);

      if($range[0] > 0)
      {
        $this->seekStart = intval($range[0]);
      }

      if($range[1] > 0)
      {
        $this->seekEnd = intval($range[1]);
      }
      else
      {
        $this->seekEnd = -1;
      }

      $this->headers['Accept-Ranges'] = 'bytes';
      $this->isRangeRequest = true;
    }

    return true;
  }

  /**
   * Send 
   * 
   * @return boolean Returns true on success
   * @throws sfHttpDownloadException If HTTP headers were already sent
   */
  public function send()
  {    
    if(function_exists('headers_sent') 
            && headers_sent())
    {
      throw new sfHttpDownloadException('Headers already sent.');
    }

    // close session
    $this->context->getStorage()->shutdown();
    
    $this->initialize();
    
    sfCore::dispatchEvent('download.before_send', array('downloader' => $this));

    $this->log('Sending download.');

    $seek = $this->seekStart;
    $bufferSize = $this->bufferSize;

    // do some clean up
    // cleanup all buffers
    if(ob_get_level())
    {
      while(@ob_end_clean());
    }

    $old_status = ignore_user_abort(true);    
    sfToolkit::setTimeLimit(0);

    // reset bandwith
    $this->bandwidth = 0;

    $this->headers['ETag'] = $this->generateETag();
      
    if($this->cache)
    {
      if($this->isCached())
      {
        $this->sendHttpStatusCode(304);
        $this->sendHeaders();   
        $this->log('Sending finished. Sent status 304, Not modified.');
        sfCore::dispatchEvent('download.finished', array('downloader' => $this));        
      }
    }
    else
    {
      unset($this->headers['Last-Modified']);
      // force pragma
      $this->headers['Pragma'] = 'no-cache';
    }

    if(!isset($this->headers['Content-Disposition']))
    {
      $this->setContentDisposition();
    }
    
    if(!isset($this->headers['Content-Type']))
    {
      if($this->file)
      {
        $this->setContentType(sfMimeType::getTypeFromFile($this->file));
      }
      elseif($this->filename)
      {
        $this->setContentType(sfMimeType::getTypeFromExtension($this->filename));
      }
      else
      {        
        $this->setContentType(sfMimeType::getTypeFromString($this->data));
      }
    }    

    if($this->useResume() && $this->isRangeRequest())
    {
      // partial content
      $this->sendHttpStatusCode(206);
      $this->headers['Status'] = '206 Partial Content';
    }
    else
    {
      $this->sendHttpStatusCode(200);
      $this->headers['Content-Length'] = $this->size;
    }

    $size = $this->size;
    // download from a file
    if($this->file)
    {
      if($seek > ($size - 1))
      {
        $seek = 0;
      }
      // open file
      $res = fopen($this->file, 'rb');
      if($seek)
      {
        fseek($res, $seek);
      }
      if($this->seekEnd < $seek)
      {
        $this->seekEnd = $size - 1;
      }

      if($this->useResume() && $this->isRangeRequest())
      {
        $this->headers['Content-Range'] = sprintf('bytes %s-%s/%s', $seek, $this->seekEnd, $size);
        $this->headers['Content-Length'] = $this->seekEnd - $seek + 1;
      }

      $size = $this->seekEnd - $seek + 1;

      $this->sendHeaders();

      while(!(connection_aborted() || connection_status() == 1) && $size > 0)
      {
        if($size < $bufferSize)
        {
          echo fread($res, $size);
          $this->bandwidth += $size;
        }
        else
        {
          echo fread($res, $bufferSize);
          $this->bandwidth += $bufferSize;
        }

        $size -= $bufferSize;
        flush();

        if($this->limitSpeed)
        {
          $this->sleep();
        }
      }
      fclose($res);
    }
    // sending raw data
    elseif($this->data)
    {
      if($seek > ($size - 1))
      {
        $seek = 0;
      }
      if($this->seekEnd < $seek)
      {
        $this->seekEnd = $this->size - 1;
      }

      $this->data = substr($this->data, $this->seekStart, $this->seekEnd - $this->seekStart + 1);

      // send
      $this->sendHeaders();

      $size = $this->size;
      while(!connection_aborted() && $size > 0)
      {
        if($size < $bufferSize)
        {
          $this->bandwidth += $size;
        }
        else
        {
          $this->bandwidth += $bufferSize;
        }

        echo substr($this->data, 0, $bufferSize);
        $this->data = substr($this->data, $bufferSize);
        $size -= $bufferSize;
        flush();

        if($this->limitSpeed)
        {
          $this->sleep();
        }
      }
    }

    // restore old status
    ignore_user_abort($old_status);
    
    if(connection_status() != CONNECTION_NORMAL) 
    {
      $this->log('Connection lost.');
      sfCore::dispatchEvent('download.connection_lost', array('downloader' => $this));
      
      if(connection_aborted()) 
      {
        $this->log('Sending aborted by user.');
        sfCore::dispatchEvent('download.aborted', array('downloader' => $this));
      }      
    }
    else
    {
      $this->log('Sending finished.');
      sfCore::dispatchEvent('download.finished', array('downloader' => $this));
    }
      
    if($this->autoExit())
    {
      exit();
    }

    return true;
  }

  /**
   * Limit download limitSpeed for the file
   *
   * @param integer $limit
   */
  
  /**
   * 
   * @param integer $limit
   * @return sfHttpDownload
   */
  public function limitSpeed($limit)
  {
    $this->limitSpeed = true;
    $this->bufferSize = round($limit * 1024);
    return $this;
  }

  /**
   * Set path to file for download
   * 
   * The Last-Modified header will be set to files filemtime(), actually.
   * 
   * @param string $file Path to file for download
   * @return sfHttpDownload
   * @throws sfHttpDownloadException
   */
  public function setFile($file)
  {
    $file = realpath($file);
    
    if(!is_file($file))
    {
      throw new sfHttpDownloadException(sprintf('File "%s" does not exist or is not readable.', $file));
    }
    
    $this->setLastModified(filemtime($file));
    $this->file = $file;
    $this->size = filesize($file);
    return $this;
  }

  /**
   * Set resume mode. Returns old status.
   * 
   * @param boolean $flag
   * @return boolen Old value
   */
  public function useResume($flag = null)
  {
    if(is_null($flag))
    {
      return $this->useResume;
    }
    $old = $this->useResume;
    $this->useResume = (bool) $flag;
    return $old;
  }

  /**
   * Sets autoexit feature or returns current value
   * 
   * @param boolean $flag
   * @return boolean
   */
  public function autoExit($flag = null)
  {
    if(is_null($flag))
    {
      return $this->autoExit;
    }
    $old = $this->autoExit;
    $this->autoExit = (bool) $flag;
    return $old;
  }

  /**
   * Sleep for $seconds second
   * 
   * @param int $seconds Number of second to sleep
   */
  protected function sleep($seconds = 1)
  {
    if(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
    {
      com_message_pump($seconds * 1000);
    }
    else
    {
      usleep($seconds * 1000);
    }
  }

  /**
   * Set size of Buffer
   * 
   * The amount of bytes specified as buffer size is the maximum amount
   * of data read at once from resources or files.  The default size is 2M
   * (2097152 bytes).
   * 
   * @param int $bytes Amount of bytes to use as buffer.
   * @return sfHttpDownload
   * @throws sfHttpDownloadException If $bytes is not greater than 0 bytes.
   */
  public function setBufferSize($bytes = 2097152)
  {
    if(0 >= $bytes)
    {
      throw new sfHttpDownloadException(sprintf('Buffer size must be greater than 0 bytes ("%s" given)', $bytes));
    }
    $this->bufferSize = $bytes;
    return $this;
  }

  /**
   * Whether to allow caching
   * 
   * If set to true (default) we'll send some headers that are commonly
   * used for caching purposes like ETag, Cache-Control and Last-Modified.
   *
   * If caching is disabled, we'll send the download no matter if it
   * would actually be cached at the client side.
   *  
   * @param boolean $cache whether to allow caching
   * @return sfHttpDownload
   */
  public function setCache($cache)
  {
    $this->cache = (boolean)$cache;
    return $this;
  }
  
  /**
   * Set "Last-Modified"
   * 
   * This is usually determined by filemtime() in HTTP_Download::setFile()
   * If you set raw data for download with HTTP_Download::setData() and you
   * want do send an appropiate "Last-Modified" header, you should call this
   * method.
   * 
   * @param int|DateTime $last_modified unix timestamp of DateTime object
   * @return sfHttpDownload
   */
  public function setLastModified($last_modified)
  {
    if($last_modified instanceof DateTime)
    {
      $last_modified = $last_modified->format('U');
    }
    
    $last_modified = intval($last_modified);
    if($last_modified <= 0)
    {
      $last_modified = time();
    }
    
    $this->lastModified = $this->headers['Last-Modified'] = $last_modified;
    
    return $this;
  }

  /**
   * Set Content-Disposition header
   *
   * @access  public
   * @return  void
   * @param   string  $disposition    whether to send the download
   *                                  inline or as attachment
   * @param   string  $file_name      the filename to display in
   *                                  the browser's download window
   *
   * <b>Example:</b>
   * <code>
   * $downloader->setContentDisposition(
   *   sfHttpDownload::DOWNLOAD_ATTACHMENT,
   *   'download.tgz'
   * );
   * </code>
   */
  public function setContentDisposition($disposition = self::DOWNLOAD_ATTACHMENT, 
          $filename = null)
  {
    $cd = $disposition;
    
    if(isset($filename))
    {
      $cd .= '; filename="' . $filename . '"';
    }
    else
    {
      $cd .= '; filename="' . $this->getFilename() . '"';
    }

    $this->headers['Content-Disposition'] = $cd;
    return $this;
  }

  /**
   * Set content type of the download
   *
   * Default content type of the download will be 'application/x-octetstream'.
   * Returns PEAR_Error (HTTP_DOWNLOAD_E_INVALID_CONTENT_TYPE) if
   * $content_type doesn't seem to be valid.
   *
   * @access  public
   * @return  mixed   Returns true on success or PEAR_Error on failure.
   * @param   string  $content_type   content type of file for download
   */
  public function setContentType($content_type = 'application/x-octetstream')
  {
    if(!preg_match('#^[-\w\+]+/[-\w\+.]+$#', $content_type))
    {
      throw new sfHttpDownloadException("Invalid content type '$content_type' supplied.");
    }

    $this->headers['Content-Type'] = $content_type;
    
    return $this;
  }

  /**
   * Sets filename
   *
   * @param string $filename
   */
  public function setFilename($filename)
  {
    $this->filename = $filename;    
    return $this;
  }

  public function getFilename()
  {
    if($this->filename)
    {
      $filename = $this->filename;
    }
    elseif($this->file)
    {
      $filename = basename($this->file);
    }
    else
    {
      $filename = 'download-' . time();
    }
    
    return $this->fixFilenameForInternetExplorer($filename);
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
   * Set ETag
   * 
   * Sets a user-defined ETag for cache-validation.  The ETag is usually
   * generated by sfHttpDownload through its payload information.
   * 
   * @param string $etag
   * @return sfHttpDownload
   */
  public function setETag($etag = null)
  {
    $this->etag = (string) $etag;
    return $this;
  }

  /**
   * Generate ETag
   *
   * @access  protected
   * @return  string
   */
  protected function generateETag()
  {
    if(!$this->etag)
    {
      if($this->data)
      {
        $md5 = md5($this->data);
      }
      else
      {
        $fst = stat($this->file);
        $md5 = md5($fst['mtime'] . '=' . $fst['ino'] . '=' . $fst['size']);
      }
      $this->etag = '"' . $md5 . '-' . crc32($md5) . '"';
    }
    return $this->etag;
  }

  /**
   * Check if entity is cached
   *
   * @access  protected
   * @return  bool
   */
  protected function isCached()
  {
    return ((isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
            $this->lastModified == strtotime(@current($a = explode(
                            ';', $_SERVER['HTTP_IF_MODIFIED_SINCE'])))) ||
            (isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
            $this->compareAsterisk('HTTP_IF_NONE_MATCH', $this->etag))
            );
  }

  /**
   * Compare against an asterisk or check for equality
   *
   * @access  protected
   * @return  bool
   * @param   string  key for the $_SERVER array
   * @param   string  string to compare
   */
  protected function compareAsterisk($svar, $compare)
  {
    foreach(array_map('trim', explode(',', $_SERVER[$svar])) as $request)
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
   * @param string $cache private or public
   * @param integer $maxage maximum age of the client cache entry
   * @return sfHttpDownload
   * @throws InvalidArgumentException
   */
  public function setCacheControl($cache = self::CACHE_PUBLIC, $maxage = 0)
  {
    switch($cache = strtolower($cache))
    {
      case self::CACHE_PUBLIC:
      case self::CACHE_PRIVATE:
        $this->headers['Cache-Control'] = sprintf('%s, must-revalidate, max-age=%s', $cache, abs($maxage));
      break;
    
      default:
        throw new InvalidArgumentException(sprintf('Invalid cache type "%s" given. This should be one of: "public" or "private".', $cache));
      break;    
    }
    
    return $this;    
  }

  /**
   * Sends header with http code
   *
   * @param integer $code integer HTTP code
   */
  protected function sendHttpStatusCode($code)
  {
    $this->log(sprintf('Sending http status code: %s', $code));
    // http://stackoverflow.com/questions/4797274/how-to-send-a-status-code-in-php-without-maintaining-an-array-of-status-names
    header('x', true, $code);
  }

  /**
   * Sends headers
   *
   */
  protected function sendHeaders()
  {
    foreach($this->headers as $header => $value)
    {
      $header = sprintf('%s: %s', $header, $value);
      $this->log($header);
      header($header);    
    }
  }

  /**
   * Fixes filename for Internet explorer
   *
   * @param string $fileName
   * @param string $inputEncoding
   * @return string string
   */
  protected function fixFilenameForInternetExplorer($fileName, $inputEncoding = 'UTF-8')
  {
    if(isset($_SERVER['HTTP_USER_AGENT']) && function_exists('iconv'))
    {
      if(preg_match("|MSIE\s*[1-8]|", $_SERVER['HTTP_USER_AGENT']))
      {
        $fileName = iconv($inputEncoding, 'windows-1250', $fileName);
      }
    }
    return $fileName;
  }

  /**
   * Logs message using Sift logger 
   *
   * @param string $message string Message to log
   */
  protected function log($message)
  {
    if(sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->info(sprintf('{sfHttpDownload} %s', $message));
    }
  }

}
