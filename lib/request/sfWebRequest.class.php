<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebRequest class.
 *
 * This class manages web requests. It parses input from the request and store them as parameters.
 * sfWebRequest is able to parse request with routing support enabled.
 *
 * @package    Sift
 * @subpackage request
 */
class sfWebRequest extends sfRequest {

  /**
   * A list of languages accepted by the browser.
   *
   * @var array
   */
  protected $languages;

  /**
   * A list of charsets accepted by the browser
   *
   * @var array
   */
  protected $charsets;

  /**
   * @var array  List of content types accepted by the client.
   */
  protected $acceptableContentTypes;

  /**
   * Path info array
   *
   * @var array
   */
  protected $pathInfoArray = null;

  protected $relativeUrlRoot = null;
  protected $filesInfos;
  protected $getParameters,
      $postParameters = array();
  protected $ip = null;
  protected $ip_forwarded_for = false;
  protected $fixedFileArray = false;

  /**
   * Initializes this sfRequest.
   *
   * @param sfEventDispatcher $dispatcher The dispatcher
   * @param array $parameters An associative array of initialization parameters
   * @param array $attributes An associative array of initialization attributes
   * @inject event_dispatcher
   */
  public function __construct(sfEventDispatcher $dispatcher, $parameters = array(), $attributes = array())
  {
    parent::__construct($dispatcher, $parameters, $attributes);

    if(isset($_SERVER['REQUEST_METHOD']))
    {
      switch($_SERVER['REQUEST_METHOD'])
      {
        case 'GET':
          $this->setMethod(self::GET);
          break;

        case 'POST':
          $this->setMethod(self::POST);
          break;

        case 'PUT':
          $this->setMethod(self::PUT);
          break;

        case 'DELETE':
          $this->setMethod(self::DELETE);
          break;

        case 'HEAD':
          $this->setMethod(self::HEAD);
          break;

        default:
          $this->setMethod(self::GET);
      }
    }
    else
    {
      // set the default method
      $this->setMethod(self::GET);
    }

    // load parameters from GET/PATH_INFO/POST
    $this->loadParameters();

    // GET parameters
    $this->getParameters = get_magic_quotes_gpc() ? sfToolkit::stripslashesDeep($_GET) : $_GET;
    // POST parameters
    $this->postParameters = get_magic_quotes_gpc() ? sfToolkit::stripslashesDeep($_POST) : $_POST;
  }

  /**
   * Returns true if the request is a XMLHttpRequest.
   *
   * It works if your JavaScript library set an X-Requested-With HTTP header.
   * Works with Prototype, Mootools, jQuery, and perhaps others.
   *
   * @return Boolean true if the request is an XMLHttpRequest, false otherwise
   */
  public function isAjax()
  {
    return $this->isXmlHttpRequest();
  }

  /**
   * Returns user IP address
   *
   * @return  string User IP address
   */
  public function getIp()
  {
    if($this->ip != null)
    {
      return $this->ip;
    }
    $ip = $this->getHttpHeader('REMOTE_ADDR', '');
    $this->ip = $this->getFirstIp($ip);
    return $this->ip;
  }

  /**
   * Returns IP address if user is behind proxy server
   *
   * @return  string User IP address
   */
  public function getIpForwardedFor()
  {
    if($this->ip_forwarded_for != false &&
        $this->ip_forwarded_for != null)
    {
      return $this->ip_forwarded_for;
    }

    $check = array(
        'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED', 'HTTP_VIA', 'HTTP_X_COMING_FROM', 'HTTP_COMING_FROM',
        'HTTP_CLIENT_IP'
    );

    $ip = null;
    foreach($check as $c)
    {
      $s = $this->getHttpHeader($c, '');
      if($s)
      {
        foreach(explode(',', $s) as $my_ip)
        {
          $my_ip = trim($my_ip);
          if($this->isValidIp($my_ip))
          {
            $ip = $my_ip;
            break;
          }
        }
      }
    }
    $this->ip_forwarded_for = $ip;
    return $this->ip_forwarded_for;
  }

  /**
   * Utility method. Returns first IP address of string
   *
   * @return  string IP address
   */
  protected function getFirstIp($ips)
  {
    return sfSecurity::getFirstIp($ips);
  }

  /**
   * Checks given IP adress for validity
   * (Private IPs are considered as invalid)
   *
   * @param string IP address
   * @return boolean
   */
  public function isValidIp($ip)
  {
    return sfSecurity::isIpValid($ip);
  }

  /**
   * Gets a cookie value.
   *
   * @return mixed
   */
  public function getCookie($name, $defaultValue = null, $compressed = false)
  {
    $retval = $defaultValue;

    if(isset($_COOKIE[$name]))
    {
      $retval = get_magic_quotes_gpc() ? stripslashes($_COOKIE[$name]) : $_COOKIE[$name];
      if($compressed)
      {
        $retval = @gzuncompress(sfSafeUrl::decode($retval));
        if(!$retval)
        {
          $retval = $defaultValue;
        }
      }
    }
    return $retval;
  }

  public function getCookies()
  {
    if(get_magic_quotes_gpc())
    {
      $tmp = array();
      foreach($_COOKIE as $name => $value)
      {
        $tmp[$name] = sfToolkit::stripslashesDeep($value);
      }
      return $tmp;
    }
    else
    {
      return $_COOKIE;
    }
    return $_COOKIE;
  }

  /**
   * Gets a compressed cookie value. This is an alias method.
   *
   * @return mixed
   */
  public function getCompressedCookie($name, $defaultValue = null)
  {
    return $this->getCookie($name, $defaultValue, true);
  }

  /**
   * Gets base server domain. Usefull for sharing cookies and session across domains
   *
   * @return mixed
   */
  public function getBaseDomain()
  {
    $domain = $this->getHttpHeader('server_name', '');
    if(empty($domain) || preg_match('/(([0-1]?[0-9]{1,2}\.)|(2[0-4][0-9]\.)|(25[0-5]\.)){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))/', $domain))
    {
      // empty or all numeric = IP address, don't try to cut it any further
      return $domain;
    }
    $domain = preg_replace('~^([a-z]+://)?([^:/#]+)(.*)$~i', '\\2', $domain);
    // get the base domain up to 3 levels (x.y.tld):
    // NOTE: "_" is not really valid, but for Windows it is..
    // TODO: this should also handle IDN "raw" domains with umlauts..
    if(!preg_match('~  ( \w (\w|-|_)* \. ){0,2}   \w (\w|-|_)*  $~ix', $domain, $matches))
    {
      return '';
    }
    $base_domain = $matches[0];
    // remove any www*. prefix:
    $base_domain = preg_replace('~^(www (\w|-|_)* \. )~xi', '', $base_domain);
    return $base_domain;
  }

  public function getHostname()
  {
    $ip = $this->getIp();
    $host = gethostbyname($ip);
    if($ip !== $host)
    {
      return $host;
    }
  }

  /**
   * Returns user agent
   *
   * @return string
   */
  public function getUserAgent()
  {
    return $this->getHttpHeader('user_agent');
  }

  /**
   * Returns HTTP protocol (http or https)
   *
   * @return string
   */
  public function getProtocol()
  {
    $pathInfo = $this->getPathInfoArray();
    return strtolower(substr($pathInfo['SERVER_PROTOCOL'], 0, 5)) == 'https' ? 'https' : 'http';
  }

  /**
   * Returns true if the request is POST request
   *
   * @return boolean
   */
  public function isPost()
  {
    return ($this->getMethod() == sfRequest::POST);
  }

  /**
   * Returns true if the request is GET request
   * @return boolean
   */
  public function isGet()
  {
    return ($this->getMethod() == sfRequest::GET);
  }

  /**
   * Checks if the request method is the given one.
   *
   * @param  string $method  The method name
   *
   * @return bool true if the current method is the given one, false otherwise
   */
  public function isMethod($method)
  {
    return strtoupper($method) == $this->getMethod();
  }

  public function getGetParameters()
  {
    return $this->getParameters;
  }

  public function getPostParameters()
  {
    return $this->postParameters;
  }

  /**
   * Returns the value of a GET parameter.
   *
   * @param  string $name     The GET parameter name
   * @param  string $default  The default value
   *
   * @return string The GET parameter value
   */
  public function getGetParameter($name, $default = null)
  {
    if(isset($this->getParameters[$name]))
    {
      return $this->getParameters[$name];
    }
    else
    {
      return sfToolkit::getArrayValueForPath($this->getParameters, $name, $default);
    }
  }

  /**
   * Returns the value of a POST parameter.
   *
   * @param  string $name     The POST parameter name
   * @param  string $default  The default value
   *
   * @return string The POST parameter value
   */
  public function getPostParameter($name, $default = null)
  {
    if(isset($this->postParameters[$name]))
    {
      return $this->postParameters[$name];
    }
    else
    {
      return sfToolkit::getArrayValueForPath($this->postParameters, $name, $default);
    }
  }

  /**
   * Retrieves an array of file information.
   *
   * @param string A file name
   *
   * @return array An associative array of file information, if the file exists, otherwise null
   */
  public function getFile($name)
  {
    $file = ($this->hasFile($name) ? $this->getFileValues($name) : null);
    return sfUploadedFile::create($file);
  }

  /**
   * Retrieves a file error.
   *
   * @param string A file name
   *
   * @return int One of the following error codes:
   *
   *             - <b>UPLOAD_ERR_OK</b>        (no error)
   *             - <b>UPLOAD_ERR_INI_SIZE</b>  (the uploaded file exceeds the
   *                                           upload_max_filesize directive
   *                                           in php.ini)
   *             - <b>UPLOAD_ERR_FORM_SIZE</b> (the uploaded file exceeds the
   *                                           MAX_FILE_SIZE directive that
   *                                           was specified in the HTML form)
   *             - <b>UPLOAD_ERR_PARTIAL</b>   (the uploaded file was only
   *                                           partially uploaded)
   *             - <b>UPLOAD_ERR_NO_FILE</b>   (no file was uploaded)
   */
  public function getFileError($name)
  {
    return ($this->hasFile($name) ? $this->getFileValue($name, 'error') : UPLOAD_ERR_NO_FILE);
  }

  /**
   * Retrieves a file name.
   *
   * @param string A file nam.
   *
   * @return string A file name, if the file exists, otherwise null
   */
  public function getFileName($name)
  {
    return ($this->hasFile($name) ? $this->getFileValue($name, 'name') : null);
  }

  /**
   * Retrieves an array of file names.
   *
   * @return array An indexed array of file names
   */
  public function getFileNames()
  {
    return array_keys($_FILES);
  }

  /**
   * Retrieves an array of files. $_FILES
   *
   * @return array An associative array of files
   */
  public function getFilesSuperGlobal()
  {
    return $_FILES;
  }

  /**
   * Retrieves an array of files.
   *
   * @param  string $key  A key
   * @return array  An associative array of files
   */
  public function getFiles($key = null)
  {
    if(false === $this->fixedFileArray)
    {
      $this->fixedFileArray = self::convertFileInformation($_FILES);
    }

    return null === $key ? $this->fixedFileArray : (isset($this->fixedFileArray[$key]) ? $this->fixedFileArray[$key] : array());
  }

  /**
   * Converts uploaded file array to a format following the $_GET and $POST naming convention.
   *
   * It's safe to pass an already converted array, in which case this method just returns the original array unmodified.
   *
   * @param  array $taintedFiles An array representing uploaded file information
   *
   * @return array An array of re-ordered uploaded file information
   */
  static public function convertFileInformation(array $taintedFiles)
  {
    $files = array();
    foreach($taintedFiles as $key => $data)
    {
      $files[$key] = self::fixPhpFilesArray($data);
    }

    return $files;
  }

  static protected function fixPhpFilesArray($data)
  {
    $fileKeys = array('error', 'name', 'size', 'tmp_name', 'type');
    $keys = array_keys($data);
    sort($keys);

    if($fileKeys != $keys || !isset($data['name']) || !is_array($data['name']))
    {
      return $data;
    }

    $files = $data;
    foreach($fileKeys as $k)
    {
      unset($files[$k]);
    }
    foreach(array_keys($data['name']) as $key)
    {
      $files[$key] = self::fixPhpFilesArray(array(
              'error' => $data['error'][$key],
              'name' => $data['name'][$key],
              'type' => $data['type'][$key],
              'tmp_name' => $data['tmp_name'][$key],
              'size' => $data['size'][$key],
      ));
    }

    return $files;
  }

  /**
   * Retrieves a file path.
   *
   * @param string A file name
   *
   * @return string A file path, if the file exists, otherwise null
   */
  public function getFilePath($name)
  {
    return ($this->hasFile($name) ? $this->getFileValue($name, 'tmp_name') : null);
  }

  /**
   * Retrieve a file size.
   *
   * @param string A file name
   *
   * @return int A file size, if the file exists, otherwise null
   */
  public function getFileSize($name)
  {
    return ($this->hasFile($name) ? $this->getFileValue($name, 'size') : null);
  }

  /**
   * Retrieves a file type.
   *
   * This may not be accurate. This is the mime-type sent by the browser
   * during the upload.
   *
   * @param string A file name
   *
   * @return string A file type, if the file exists, otherwise null
   */
  public function getFileType($name)
  {
    return ($this->hasFile($name) ? $this->getFileValue($name, 'type') : null);
  }

  /**
   * Indicates whether or not a file exists.
   *
   * @param string A file name
   *
   * @return boolean true, if the file exists, otherwise false
   */
  public function hasFile($name)
  {
    if(strpos($name, '['))
    {
      return !is_null(sfToolkit::getArrayValueForPath($this->filesInfos, $name));
    }
    else
    {
      return isset($this->filesInfos[$name]);
    }
  }

  /**
   * Indicates whether or not a file error exists.
   *
   * @param string A file name
   *
   * @return boolean true, if the file error exists, otherwise false
   */
  public function hasFileError($name)
  {
    return ($this->hasFile($name) ? ($this->getFileValue($name, 'error') != UPLOAD_ERR_OK) : false);
  }

  /**
   * Indicates whether or not any file errors occured.
   *
   * @return boolean true, if any file errors occured, otherwise false
   */
  public function hasFileErrors()
  {
    foreach($this->getFileNames() as $name)
    {
      if($this->hasFileError($name) === true)
      {
        return true;
      }
    }

    return false;
  }

  /**
   * Indicates whether or not any files exist.
   *
   * @return boolean true, if any files exist, otherwise false
   */
  public function hasFiles()
  {
    return (count($_FILES) > 0);
  }

  /**
   * Retrieves a file value.
   *
   * @param string A file name
   * @param string Value to search in the file
   *
   * @return string File value
   */
  public function getFileValue($name, $key)
  {
    $fileInfos = $this->getFileValues($name);

    return isset($fileInfos[$key]) ? $fileInfos[$key] : null;
  }

  /**
   * Retrieves all the values from a file.
   *
   * @param string A file name
   *
   * @return array Associative list of the file values
   */
  public function getFileValues($name)
  {
    if(strpos($name, '['))
    {
      return sfToolkit::getArrayValueForPath($this->filesInfos, $name);
    }
    else
    {
      return isset($this->filesInfos[$name]) ? $this->filesInfos[$name] : null;
    }
  }

  /**
   * Retrieves an extension for a given file.
   *
   * @param string A file name
   *
   * @return string Extension for the file
   */
  public function getFileExtension($name)
  {
    static $mimeTypes = null;

    $fileType = $this->getFileType($name);

    if(!$fileType)
    {
      return '.bin';
    }

    if(is_null($mimeTypes))
    {
      $mimeTypes = unserialize(file_get_contents(sfConfig::get('sf_sift_data_dir') . '/data/mime_types.dat'));
    }

    return isset($mimeTypes[$fileType]) ? '.' . $mimeTypes[$fileType] : '.bin';
  }

  /**
   * Returns the array that contains all request information ($_SERVER or $_ENV).
   *
   * This information is stored in the [sf_path_info_array] constant.
   *
   * @return  array Path information
   */
  public function getPathInfoArray()
  {
    if(!$this->pathInfoArray)
    {
      // parse PATH_INFO
      switch(sfConfig::get('sf_path_info_array'))
      {
        case 'SERVER':
          $this->pathInfoArray = & $_SERVER;
          break;

        case 'ENV':
        default:
          $this->pathInfoArray = & $_ENV;
      }
    }

    return $this->pathInfoArray;
  }

  /**
   * Retrieves the uniform resource identifier for the current web request.
   *
   * @return string Unified resource identifier
   */
  public function getUri()
  {
    $pathArray = $this->getPathInfoArray();

    // for IIS with rewrite module (IIFR, ISAPI Rewrite, ...)
    if('HTTP_X_REWRITE_URL' == sfConfig::get('sf_path_info_key'))
    {
      $uri = isset($pathArray['HTTP_X_REWRITE_URL']) ? $pathArray['HTTP_X_REWRITE_URL'] : '';
    }
    else
    {
      $uri = isset($pathArray['REQUEST_URI']) ? $pathArray['REQUEST_URI'] : '';
    }

    return $this->isAbsUri() ? $uri : $this->getUriPrefix() . $uri;
  }

  /**
   * See if the client is using absolute uri
   *
   * @return boolean true, if is absolute uri otherwise false
   */
  public function isAbsUri()
  {
    $pathArray = $this->getPathInfoArray();

    return preg_match('/^http/', $pathArray['REQUEST_URI']);
  }

  /**
   * Returns Uri prefix, including protocol, hostname and server port.
   *
   * @return string Uniform resource identifier prefix
   */
  public function getUriPrefix()
  {
    $pathArray = $this->getPathInfoArray();
    if($this->isSecure())
    {
      $standardPort = '443';
      $protocol = 'https';
    }
    else
    {
      $standardPort = '80';
      $protocol = 'http';
    }

    $host = explode(":", $this->getHost());
    if(count($host) == 1)
    {
      $host[] = isset($pathArray['SERVER_PORT']) ? $pathArray['SERVER_PORT'] : '';
    }

    if($host[1] == $standardPort || empty($host[1]))
    {
      unset($host[1]);
    }

    return $protocol . '://' . implode(':', $host);
  }

  /**
   * Retrieves the path info for the current web request.
   *
   * @return string Path info
   */
  public function getPathInfo()
  {
    $pathInfo = '';

    $pathArray = $this->getPathInfoArray();

    // simulate PATH_INFO if needed
    $sf_path_info_key = sfConfig::get('sf_path_info_key');
    if(!isset($pathArray[$sf_path_info_key]) || !$pathArray[$sf_path_info_key])
    {
      if(isset($pathArray['REQUEST_URI']))
      {
        $script_name = $this->getScriptName();
        $uri_prefix = $this->isAbsUri() ? $this->getUriPrefix() : '';
        $pathInfo = preg_replace('/^' . preg_quote($uri_prefix, '/') . '/', '', $pathArray['REQUEST_URI']);
        $pathInfo = preg_replace('/^' . preg_quote($script_name, '/') . '/', '', $pathInfo);
        $prefix_name = preg_replace('#/[^/]+$#', '', $script_name);
        $pathInfo = preg_replace('/^' . preg_quote($prefix_name, '/') . '/', '', $pathInfo);
        $pathInfo = preg_replace('/\??' . preg_quote($pathArray['QUERY_STRING'], '/') . '$/', '', $pathInfo);
      }
    }
    else
    {
      $pathInfo = $pathArray[$sf_path_info_key];
      if($sf_relative_url_root = $this->getRelativeUrlRoot())
      {
        $pathInfo = preg_replace('/^' . str_replace('/', '\\/', $sf_relative_url_root) . '\//', '', $pathInfo);
      }
    }

    // for IIS
    if(isset($_SERVER['SERVER_SOFTWARE']) && false !== stripos($_SERVER['SERVER_SOFTWARE'], 'iis') && $pos = stripos($pathInfo, '.php'))
    {
      $pathInfo = substr($pathInfo, $pos + 4);
    }

    if(!$pathInfo)
    {
      $pathInfo = '/';
    }

    return $pathInfo;
  }

  /**
   * Loads GET, PATH_INFO and POST data into the parameter list.
   *
   */
  protected function loadParameters()
  {
    // merge GET parameters
    if(get_magic_quotes_gpc())
    {
      $_GET = sfToolkit::stripslashesDeep($_GET);
    }
    $this->getParameterHolder()->addByRef($_GET);

    $pathInfo = $this->getPathInfo();
    if($pathInfo)
    {
      // routing map defined?
      $r = sfRouting::getInstance();
      if($r->hasRoutes())
      {
        $results = $r->parse($pathInfo);
        if($results !== null)
        {
          $this->getParameterHolder()->addByRef($results);
        }
        else
        {
          $this->setParameter('module', sfConfig::get('sf_error_404_module'));
          $this->setParameter('action', sfConfig::get('sf_error_404_action'));
        }
      }
      else
      {
        $array = explode('/', trim($pathInfo, '/'));
        $count = count($array);

        for($i = 0; $i < $count; $i++)
        {
          // see if there's a value associated with this parameter,
          // if not we're done with path data
          if($count > ($i + 1))
          {
            $this->getParameterHolder()->setByRef($array[$i], $array[++$i]);
          }
        }
      }
    }

    $this->filesInfos = $this->convertFileInformation($_FILES);

    // merge POST parameters
    if(get_magic_quotes_gpc())
    {
      $_POST = sfToolkit::stripslashesDeep((array) $_POST);
    }
    $this->getParameterHolder()->addByRef($_POST);

    // move Sift parameters in a protected namespace (parameters prefixed with _sf_)
    foreach($this->getParameterHolder()->getAll() as $key => $value)
    {
      if(0 === stripos($key, '_sf_'))
      {
        $this->getParameterHolder()->remove($key);
        $this->setParameter(substr($key, 1), $value, self::PROTECTED_NAMESPACE);
      }
    }

    if(sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->info(sprintf('{sfRequest} Request parameters %s', str_replace("\n", '', var_export($this->getParameterHolder()->getAll(), true))));
    }
  }

  /**
   * Moves an uploaded file.
   *
   * @param string A file name
   * @param string An absolute filesystem path to where you would like the
   *               file moved. This includes the new filename as well, since
   *               uploaded files are stored with random names
   * @param int    The octal mode to use for the new file
   * @param boolean   Indicates that we should make the directory before moving the file
   * @param int    The octal mode to use when creating the directory
   *
   * @return boolean true, if the file was moved, otherwise false
   *
   * @throws <b>sfFileException</b> If a major error occurs while attempting to move the file
   */
  public function moveFile($name, $file, $fileMode = 0666, $create = true, $dirMode = 0777)
  {
    if($this->hasFile($name) && $this->getFileValue($name, 'error') == UPLOAD_ERR_OK && $this->getFileValue($name, 'size') > 0)
    {
      // get our directory path from the destination filename
      $directory = dirname($file);

      if(!is_readable($directory))
      {
        $fmode = 0777;

        if($create && !@mkdir($directory, $dirMode, true))
        {
          // failed to create the directory
          $error = 'Failed to create file upload directory "%s"';
          $error = sprintf($error, $directory);

          throw new sfFileException($error);
        }

        // chmod the directory since it doesn't seem to work on
        // recursive paths
        @chmod($directory, $dirMode);
      }
      else if(!is_dir($directory))
      {
        // the directory path exists but it's not a directory
        $error = 'File upload path "%s" exists, but is not a directory';
        $error = sprintf($error, $directory);

        throw new sfFileException($error);
      }
      else if(!is_writable($directory))
      {
        // the directory isn't writable
        $error = 'File upload path "%s" is not writable';
        $error = sprintf($error, $directory);

        throw new sfFileException($error);
      }

      if(@move_uploaded_file($this->getFileValue($name, 'tmp_name'), $file))
      {
        // chmod our file
        @chmod($file, $fileMode);

        return true;
      }
    }

    return false;
  }

  /**
   * Returns referer.
   *
   * @return  string
   */
  public function getReferer()
  {
    $pathArray = $this->getPathInfoArray();

    return isset($pathArray['HTTP_REFERER']) ? $pathArray['HTTP_REFERER'] : '';
  }

  /**
   * Returns current host name.
   *
   * @return  string
   */
  public function getHost()
  {
    $pathArray = $this->getPathInfoArray();

    return isset($pathArray['HTTP_X_FORWARDED_HOST']) ? $pathArray['HTTP_X_FORWARDED_HOST'] : (isset($pathArray['HTTP_HOST']) ? $pathArray['HTTP_HOST'] : '');
  }

  /**
   * Returns current script name.
   *
   * @return  string
   */
  public function getScriptName()
  {
    $pathArray = $this->getPathInfoArray();

    return isset($pathArray['SCRIPT_NAME']) ? $pathArray['SCRIPT_NAME'] : (isset($pathArray['ORIG_SCRIPT_NAME']) ? $pathArray['ORIG_SCRIPT_NAME'] : '');
  }

  /**
   * Returns request method.
   *
   * @return  string
   */
  public function getMethodName()
  {
    $pathArray = $this->getPathInfoArray();

    return isset($pathArray['REQUEST_METHOD']) ? $pathArray['REQUEST_METHOD'] : 'GET';
  }

  /**
   * Gets a list of languages acceptable by the client browser
   *
   * @return array Languages ordered in the user browser preferences
   */
  public function getLanguages()
  {
    if($this->languages)
    {
      return $this->languages;
    }

    if(!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
    {
      return array();
    }

    $languages = $this->splitHttpAcceptHeader($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    foreach($languages as $lang)
    {
      if(strstr($lang, '-'))
      {
        $codes = explode('-', $lang);
        if($codes[0] == 'i')
        {
          // Language not listed in ISO 639 that are not variants
          // of any listed language, which can be registerd with the
          // i-prefix, such as i-cherokee
          if(count($codes) > 1)
          {
            $lang = $codes[1];
          }
        }
        else
        {
          for($i = 0, $max = count($codes); $i < $max; $i++)
          {
            if($i == 0)
            {
              $lang = strtolower($codes[0]);
            }
            else
            {
              $lang .= '_' . strtoupper($codes[$i]);
            }
          }
        }
      }

      $this->languages[] = $lang;
    }

    return $this->languages;
  }

  /**
   * Gets a list of charsets acceptable by the client browser.
   *
   * @return array List of charsets in preferable order
   */
  public function getCharsets()
  {
    if($this->charsets)
    {
      return $this->charsets;
    }

    if(!isset($_SERVER['HTTP_ACCEPT_CHARSET']))
    {
      return array();
    }

    $this->charsets = $this->splitHttpAcceptHeader($_SERVER['HTTP_ACCEPT_CHARSET']);

    return $this->charsets;
  }

  /**
   * Gets a list of content types acceptable by the client browser
   *
   * @return array Languages ordered in the user browser preferences
   */
  public function getAcceptableContentTypes()
  {
    if($this->acceptableContentTypes)
    {
      return $this->acceptableContentTypes;
    }

    if(!isset($_SERVER['HTTP_ACCEPT']))
    {
      return array();
    }

    $this->acceptableContentTypes = $this->splitHttpAcceptHeader($_SERVER['HTTP_ACCEPT']);

    return $this->acceptableContentTypes;
  }

  /**
   * Returns true if the request is a XMLHttpRequest.
   *
   * It works if your JavaScript library set an X-Requested-With HTTP header.
   * Works with Prototype, Mootools, jQuery, and perhaps others.
   *
   * @return Boolean true if the request is an XMLHttpRequest, false otherwise
   */
  public function isXmlHttpRequest()
  {
    return strtolower($this->getHttpHeader('X-Requested-With')) == 'xmlhttprequest';
  }

  public function getHttpHeader($name, $prefix = 'http')
  {
    if($prefix)
    {
      $prefix = strtoupper($prefix) . '_';
    }

    $name = $prefix . strtoupper(strtr($name, '-', '_'));

    $pathArray = $this->getPathInfoArray();

    return isset($pathArray[$name]) ? sfToolkit::stripslashesDeep($pathArray[$name]) : null;
  }

  /**
   * Returns true if the current request is secure (HTTPS protocol).
   *
   * @return boolean
   */
  public function isSecure()
  {
    $pathArray = $this->getPathInfoArray();

    return (
        (isset($pathArray['HTTPS']) && (strtolower($pathArray['HTTPS']) == 'on' || strtolower($pathArray['HTTPS']) == 1)) ||
        (isset($pathArray['HTTP_X_FORWARDED_PROTO']) && strtolower($pathArray['HTTP_X_FORWARDED_PROTO']) == 'https')
        );
  }

  /**
   * Retrieves relative root url.
   *
   * @return string URL
   */
  public function getRelativeUrlRoot()
  {
    if($this->relativeUrlRoot === null)
    {
      $this->relativeUrlRoot = sfConfig::get('sf_relative_url_root', preg_replace('#/[^/]+\.php5?$#', '', $this->getScriptName()));
    }

    return $this->relativeUrlRoot;
  }

  /**
   * Sets the relative root url for the current web request.
   *
   * @param string Value for the url
   */
  public function setRelativeUrlRoot($value)
  {
    $this->relativeUrlRoot = $value;
  }

  /**
   * Executes the shutdown procedure.
   *
   */
  public function shutdown()
  {

  }

  /**
   * Splits an HTTP header for the current web request.
   *
   * @param string Header to split
   */
  public function splitHttpAcceptHeader($header)
  {
    $values = array();
    foreach(array_filter(explode(',', $header)) as $value)
    {
      // Cut off any q-value that might come after a semi-colon
      if($pos = strpos($value, ';'))
      {
        $q = (float) trim(substr($value, $pos + 3));
        $value = trim(substr($value, 0, $pos));
      }
      else
      {
        $q = 1;
      }

      $values[$value] = $q;
    }

    arsort($values);

    return array_keys($values);
  }

  /**
   * serialize
   * @return string
   */
  public function serialize()
  {
    $vars = get_object_vars($this);
    return serialize($vars);
  }

  /**
   * @param string $serialized
   * @return void
   */
  public function unserialize($serialized)
  {
    $vars = unserialize($serialized);
    foreach($vars as $var => $value)
    {
      $this->$var = $value;
    }
  }

}
