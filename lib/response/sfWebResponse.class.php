<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebResponse class.
 *
 * This class manages web reponses. It supports cookies and headers management.
 *
 * @package    Sift
 * @subpackage response
 */
class sfWebResponse extends sfResponse
{
  /**
   * Replace title mode
   */
  const TITLE_MODE_REPLACE = 'REPLACE';

  /**
   * Prepend title mode
   */
  const TITLE_MODE_PREPEND = 'PREPEND';

  /**
   * Append title mode
   */
  const TITLE_MODE_APPEND  = 'APPEND';

  /**
   * Array of cookies
   *
   * @var array
   */
  protected $cookies = array();

  /**
   * Status code
   *
   * @var integer
   */
  protected $statusCode = 200;

  /**
   * Status text
   *
   * @var string
   */
  protected $statusText = 'OK';

  /**
   * Header only flag
   *
   * @var boolean
   */
  protected $headerOnly  = false;

  /**
   * Array of default options
   * 
   * @var array 
   */
  protected $defaultOptions = array(
    'title_mode' => self::TITLE_MODE_PREPEND,
    'charset' => 'UTF-8',
  );

  /**
   * Array of known statuses
   *
   * @var array
   */
  protected $statusTexts = array(
    100 => 'Continue',
    101 => 'Switching Protocols',
    200 => 'OK',
    201 => 'Created',
    202 => 'Accepted',
    203 => 'Non-Authoritative Information',
    204 => 'No Content',
    205 => 'Reset Content',
    206 => 'Partial Content',
    300 => 'Multiple Choices',
    301 => 'Moved Permanently',
    302 => 'Found',
    303 => 'See Other',
    304 => 'Not Modified',
    305 => 'Use Proxy',
    306 => '(Unused)',
    307 => 'Temporary Redirect',
    400 => 'Bad Request',
    401 => 'Unauthorized',
    402 => 'Payment Required',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    406 => 'Not Acceptable',
    407 => 'Proxy Authentication Required',
    408 => 'Request Timeout',
    409 => 'Conflict',
    410 => 'Gone',
    411 => 'Length Required',
    412 => 'Precondition Failed',
    413 => 'Request Entity Too Large',
    414 => 'Request-URI Too Long',
    415 => 'Unsupported Media Type',
    416 => 'Requested Range Not Satisfiable',
    417 => 'Expectation Failed',
    418 => 'I\m a teapot',
    500 => 'Internal Server Error',
    501 => 'Not Implemented',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Timeout',
    505 => 'HTTP Version Not Supported',
    507 => 'Insufficient Storage',
    509 => 'Bandwidth Limit Exceeded'
  );

  /**
   * Sets if the response consist of just HTTP headers.
   *
   * @param boolean $value The flag
   * @return sfWebResponse
   */
  public function setHeaderOnly($value = true)
  {
    $this->headerOnly = (boolean) $value;
    return $this;
  }

  /**
   * Returns if the response must only consist of HTTP headers.
   *
   * @return boolean returns true if, false otherwise
   */
  public function isHeaderOnly()
  {
    return $this->headerOnly;
  }

  /**
   * Sets a cookie.
   *
   * @param string $name HTTP header name
   * @param string $value Value for the cookie
   * @param string $expire Cookie expiration period
   * @param string $path Path
   * @param string $domain Domain name
   * @param boolean $secure If secure
   * @param boolean $httpOnly If uses only HTTP
   *
   * @throws sfException If fails to set the cookie
   * @return sfWebResponse
   */
  public function setCookie($name, $value, $expire = null, $path = '/', $domain = '', $secure = false, $httpOnly = false)
  {
    if($expire !== null)
    {
      if(is_numeric($expire))
      {
        $expire = (int) $expire;
      }
      else
      {
        $expire = strtotime($expire);
        if ($expire === false || $expire == -1)
        {
          throw new sfException('Your expire parameter is not valid.');
        }
      }
    }

    $this->cookies[] = array(
      'name'     => $name,
      'value'    => $value,
      'expire'   => $expire,
      'path'     => $path,
      'domain'   => $domain,
      'secure'   => $secure ? true : false,
      'httpOnly' => $httpOnly,
    );

    return $this;
  }

  /**
   * Sets a compressed cookie.
   *
   * @param string $name Cookie name
   * @param string $value Value for the cookie
   * @param string $expire Cookie expiration period
   * @param string $path Path
   * @param string $domain Domain name
   * @param boolean $secure If secure
   * @param boolean $httpOnly If uses only HTTP
   * @throws sfException If fails to set the cookie
   */
  public function setCompressedCookie($name, $value, $expire = null, $path = '/', $domain = '', $secure = false, $httpOnly = false)
  {
    $value = sfSafeUrl::encode(gzcompress($value, 9));
    return $this->setCookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
  }

  /**
   * Sets response status code.
   *
   * @param string $code HTTP status code
   * @param string $name HTTP status text
   * @return sfWebResponse
   */
  public function setStatusCode($code, $name = null)
  {
    $this->statusCode = $code;
    $this->statusText = null !== $name ? $name : $this->statusTexts[$code];
    return $this;
  }

  /**
   * Retrieves status code for the current web response.
   *
   * @return string Status code
   */
  public function getStatusCode()
  {
    return $this->statusCode;
  }

  /**
   * Status text fot the current web response.
   *
   * @return string
   */
  public function getStatusText()
  {
    return $this->statusTexts[$this->statusCode];
  }

  /**
   * Array of HTTP status texts
   *
   * @return array
   */
  public function getStatusTexts()
  {
    return $this->statusTexts;
  }

  /**
   * Sets a HTTP header.
   *
   * @param string $name HTTP header name
   * @param string $value Value
   * @param boolean $replace Replace for the value
   * @return sfWebResponse
   */
  public function setHttpHeader($name, $value, $replace = true)
  {
    $name = $this->normalizeHeaderName($name);

    if('Content-Type' == $name)
    {
      if($replace || !$this->getHttpHeader('Content-Type', null))
      {
        $this->setContentType($value);
      }
      return;
    }

    if(!$replace)
    {
      $current = $this->getParameter($name, '', 'sift/response/http/headers');
      $value = ($current ? $current.', ' : '').$value;
    }

    $this->setParameter($name, $value, 'sift/response/http/headers');
    return $this;
  }

  /**
   * Gets HTTP header current value.
   *
   * @param string $name The header name
   * @param string $default The default value to returnn if the header is not set
   * @return array
   */
  public function getHttpHeader($name, $default = null)
  {
    return $this->getParameter($this->normalizeHeaderName($name), $default, 'sift/response/http/headers');
  }

  /**
   * Has a HTTP header.
   *
   * @param string $name The header name
   * @return boolean
   */
  public function hasHttpHeader($name)
  {
    return $this->hasParameter($this->normalizeHeaderName($name), 'sift/response/http/headers');
  }

  /**
   * Sets response content type.
   *
   * @param string $value Content type
   * @return sfWebResponse
   */
  public function setContentType($value)
  {
    // add charset if needed (only on text content)
    if(false === stripos($value, 'charset') && (0 === stripos($value, 'text/') ||
      strlen($value) - 3 === strripos($value, 'xml')))
    {
      $value .= '; charset='.sfConfig::get('sf_charset');
    }

    $this->setParameter('Content-Type', $value, 'sift/response/http/headers');
    return $this;
  }

  /**
   * Returns charset
   *
   * @return string
   */
  public function getCharset()
  {
    return $this->getOption('charset');
  }

  /**
   * Gets response content type.
   *
   * @return array
   */
  public function getContentType()
  {
    return $this->getHttpHeader('Content-Type', 'text/html; charset='.$this->getCharset());
  }

  /**
   * Send HTTP headers and cookies.
   *
   */
  public function sendHttpHeaders()
  {
    $headers = $this->getParameterHolder()->getAll('sift/response/http/headers');

    // status
    $status = 'HTTP/1.0 '.$this->statusCode.' '.$this->statusText;
    header($status);

    if(substr(php_sapi_name(), 0, 3) == 'cgi')
    {
      // fastcgi servers cannot send this status information because it was sent by them already due to the HTT/1.0 line
      // so we can safely unset them.
      unset($headers['Status']);
    }

    if(sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->info(sprintf('{sfResponse} Sent status "%s"', $status));
    }

    // headers
    foreach ($headers as $name => $value)
    {
      header($name.': '.$value);
      if (sfConfig::get('sf_logging_enabled') && $value != '')
      {
        sfLogger::getInstance()->info(sprintf('{sfResponse} Sent header "%s": "%s"', $name, $value));
      }
    }

    // cookies
    foreach($this->cookies as $cookie)
    {
      setrawcookie($cookie['name'], $cookie['value'], $cookie['expire'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httpOnly']);
      if(sfConfig::get('sf_logging_enabled'))
      {
        sfLogger::getInstance()->info(sprintf('{sfResponse} Sent cookie "%s":"%s"', $cookie['name'], $cookie['value']));
      }
    }
  }

  /**
   * Sends headers and content. Responsible for executing content if it is a callable.
   *
   */
  public function sendContent()
  {
    if(!$this->headerOnly)
    {
      if(is_string($this->content))
      {
        parent::sendContent();
      }
      elseif(sfToolkit::isCallable($this->content, false, $callableName))
      {
        if(sfConfig::get('sf_logging_enabled'))
        {
          sfLogger::getInstance()->info(sprintf('{sfResponse} Calling callable "%s"', $callableName));
        }
        call_user_func($this->content);
      }
    }
  }

  /**
   * Sends the HTTP headers and the content.
   *
   */
  public function send()
  {
    $this->dispatcher->notify(new sfEvent('response.send', array('response' => $this)));

    if(!sfToolkit::isCallable($this->content))
    {
      $this->sendHttpHeaders();
    }

    $this->sendContent();
  }

  /**
   * Send 256 blank characters (spaces) to force "update" of page.
   *
   * Some versions of IE (7.0 for example) will not update the page
   * if less than 256 bytes are recieved.
   */
  public function hardFlush()
  {
    echo str_repeat(' ', 256);
    flush();
  }

  /**
   * Retrieves a normalized Header.
   *
   * @param string $name Header name
   * @return string Normalized header
   */
  protected function normalizeHeaderName($name)
  {
    return preg_replace('/\-(.)/e', "'-'.strtoupper('\\1')", strtr(ucfirst(strtolower($name)), '_', '-'));
  }

  /**
   * Retrieves a formated date.
   *
   * @param string $timestamp Timestamp
   * @param string $type Format type
   * @return string Formated date
   * @throws InvalidArgumentException If $type if not valid
   */
  public static function getDate($timestamp, $type = 'rfc1123')
  {
    $type = strtolower($type);

    if($type == 'rfc1123')
    {
      return substr(gmdate('r', $timestamp), 0, -5).'GMT';
    }
    elseif($type == 'rfc1036')
    {
      return gmdate('l, d-M-y H:i:s ', $timestamp).'GMT';
    }
    elseif($type == 'asctime')
    {
      return gmdate('D M j H:i:s', $timestamp);
    }
    else
    {
      throw new InvalidArgumentException('The second getDate() method parameter must be one of: rfc1123, rfc1036 or asctime');
    }
  }

  /**
   * Adds vary to a http header.
   *
   * @param string $header HTTP header
   * @return sfWebResponse
   */
  public function addVaryHttpHeader($header)
  {
    $vary = $this->getHttpHeader('Vary');
    $currentHeaders = array();
    if($vary)
    {
      $currentHeaders = preg_split('/\s*,\s*/', $vary);
    }
    $header = $this->normalizeHeaderName($header);
    if(!in_array($header, $currentHeaders))
    {
      $currentHeaders[] = $header;
      $this->setHttpHeader('Vary', implode(', ', $currentHeaders));
    }
    return $this;
  }

  /**
   * Adds an control cache http header.
   *
   * @param string $name HTTP header
   * @param string $value Value for the http header
   * @return sfWebResponse
   */
  public function addCacheControlHttpHeader($name, $value = null)
  {
    $cacheControl = $this->getHttpHeader('Cache-Control');
    $currentHeaders = array();
    if($cacheControl)
    {
      foreach(preg_split('/\s*,\s*/', $cacheControl) as $tmp)
      {
        $tmp = explode('=', $tmp);
        $currentHeaders[$tmp[0]] = isset($tmp[1]) ? $tmp[1] : null;
      }
    }
    $currentHeaders[strtr(strtolower($name), '_', '-')] = $value;

    $headers = array();
    foreach($currentHeaders as $key => $value)
    {
      $headers[] = $key.(null !== $value ? '='.$value : '');
    }
    $this->setHttpHeader('Cache-Control', implode(', ', $headers));
    return $this;
  }

  /**
   * Retrieves meta headers for the current web response.
   *
   * @return string Meta headers
   */
  public function getHttpMetas()
  {
    return $this->getParameterHolder()->getAll('helper/asset/auto/httpmeta');
  }

  /**
   * Adds meta headers to the current web response.
   *
   * @param string $key Key to replace
   * @param string $value Value for the replacement
   * @param boolean $replace Replace or not
   * @return sfWebResponse
   */
  public function addHttpMeta($key, $value, $replace = true)
  {
    $key = $this->normalizeHeaderName($key);

    // set HTTP header
    $this->setHttpHeader($key, $value, $replace);
    if('Content-Type' == $key)
    {
      $value = $this->getContentType();
    }
    elseif(!$replace)
    {
      $current = $this->getParameter($key, '', 'helper/asset/auto/httpmeta');
      $value = ($current ? $current.', ' : '').$value;
    }

    $this->setParameter($key, $value, 'helper/asset/auto/httpmeta');

    return $this;
  }

  /**
   * Retrieves all meta headers for the current web response.
   *
   * @return array List of meta headers
   */
  public function getMetas()
  {
    return $this->getParameterHolder()->getAll('helper/asset/auto/meta');
  }

  /**
   * Retrieves stylesheets for the current web response.
   *
   * @param string $position Position
   * @return array Array of stylesheets
   */
  public function getStylesheets($position = '')
  {
    return $this->getParameterHolder()->getAll('helper/asset/auto/stylesheet'.($position ? '/'.$position : ''));
  }

  /**
   * Returns all javascripts
   *
   * @return array
   */
  public function getAllStylesheets()
  {
    $stylesheets = array();
    foreach(array('first', '', 'last') as $position)
    {
      foreach($this->getStylesheets($position) as $file => $options)
      {
        $stylesheets[$file] = $options;
      }
    }
    return $stylesheets;
  }

  /**
   * Adds an stylesheet to the current web response.
   *
   * @param string $css Stylesheet
   * @param string $position Position
   * @param array $options Stylesheet options
   * @return sfWebResponse
   */
  public function addStylesheet($css, $position = '', $options = array())
  {
    $this->setParameter($css, $options, 'helper/asset/auto/stylesheet'.($position ? '/'.$position : ''));
    return $this;
  }

  /**
   * Removes a stylesheet from the current web response.
   *
   * @param string $file The stylesheet file to remove
   * @return sfWebResponse
   */
  public function removeStylesheet($file)
  {
    foreach(array('first', '', 'last') as $position)
    {
      $this->getParameterHolder()->remove($file, 'helper/asset/auto/stylesheet'.($position ? '/'.$position : ''));
    }
    return $this;
  }

  /**
   * Retrieves javascript code from the current web response.
   *
   * @param string $position Position '', 'last', 'first'
   * @return string array
   */
  public function getJavascripts($position = '')
  {
    return $this->getParameterHolder()->getAll('helper/asset/auto/javascript'.($position ? '/'.$position : ''));
  }

  /**
   * Returns all javascripts
   *
   * @return array
   */
  public function getAllJavascripts()
  {
    $javascripts = array();
    foreach(array('first', '', 'last') as $position)
    {
      foreach($this->getJavascripts($position) as $file => $options)
      {
        $javascripts[$file] = $options;
      }
    }
    return $javascripts;
  }

  /**
   * Adds javascript code to the current web response.
   *
   * @param string $js Javascript code
   * @param string $position Position
   * @param array $options Javascript options
   * @return sfWebResponse
   */
  public function addJavascript($js, $position = '', $options = array())
  {
    $this->setParameter($js, $options, 'helper/asset/auto/javascript'.($position ? '/'.$position : ''));
    return $this;
  }

  /**
   * Removes a JavaScript file from the current web response.
   *
   * @param string $file The Javascript file to remove
   * @return sfWebResponse
   */
  public function removeJavascript($file)
  {
    foreach(array('first', '', 'last') as $position)
    {
      $this->getParameterHolder()->remove($file, 'helper/asset/auto/javascript'.($position ? '/'.$position : ''));
    }
    return $this;
  }

  /**
   * Clear all assets. Discovery links, javascripts and stylesheets.
   *
   * @return sfWebResponse
   */
  public function resetAssets()
  {
    $this->clearAutoDiscoveryLinks();
    $this->clearJavascripts();
    $this->clearStylesheets();
    return $this;
  }

  /**
   * Retrieves cookies from the current web response.
   *
   * @return array Cookies
   */
  public function getCookies()
  {
    $cookies = array();
    foreach($this->cookies as $cookie)
    {
      $cookies[$cookie['name']] = $cookie;
    }
    return $cookies;
  }

  /**
   * Retrieves HTTP headers from the current web response.
   *
   * @return array HTTP headers
   */
  public function getHttpHeaders()
  {
    return $this->getParameterHolder()->getAll('sift/response/http/headers');
  }

  /**
   * Cleans HTTP headers from the current web response.
   *
   * @return sfWebResponse
   */
  public function clearHttpHeaders()
  {
    $this->getParameterHolder()->removeNamespace('sift/response/http/headers');
    return $this;
  }

  /**
   * Retrieves title for the current web response.
   *
   * @param boolean $includeGlobal true, for including global title
   * @return string
   * @throw InvalidArgumentException If title mode is invalid
   */
  public function getTitle($includeGlobal = true)
  {
    $title = trim($this->getParameter('title', '', 'helper/asset/auto/title'));

    $global_title = sfConfig::get('app_title_name');

    if($includeGlobal && $global_title)
    {
      $mode = $this->getTitleMode();
      $separator = trim(sfConfig::get('app_title_separator', '~'));

      if(!empty($title) && !empty($global_title) && $title != $global_title)
      {
        switch($mode)
        {
          case self::TITLE_MODE_APPEND:
            $title = sprintf('%s %s %s', $global_title, $separator, $title);
          break;

          case self::TITLE_MODE_PREPEND:
            $title = sprintf('%s %s %s', $title, $separator, $global_title);
          break;

          case self::TITLE_MODE_REPLACE:
            // do nothing
          break;
        }
      }
      elseif(!empty($global_title))
      {
        $title = $global_title;
      }
    }

    if(empty($title))
    {
      // old style titles -> used from metas
      $title = $this->getParameter('title', '', 'helper/asset/auto/meta');
    }

    // SEO stuff
    $max = sfConfig::get('app_title_max_length', 80);

    if($max && ($length = mb_strlen($title, sfConfig::get('sf_charset'))) > $max)
    {
      if(sfConfig::get('sf_logging_enabled'))
      {
        sfLogger::getInstance()->notice(
                sprintf('{SEO} Page title is too long (%s chars, maximum is %s). Please consider shorter title.', $length, $max));
      }

      // lets do some magic with the title, if configured
      if(sfConfig::get('app_title_auto_trim', false))
      {
        $title = myText::truncate($title, $max, ' ...');
        $length = mb_strlen($title, sfConfig::get('sf_charset'));
        if(sfConfig::get('sf_logging_enabled'))
        {
          sfLogger::getInstance()->info(sprintf('{SEO} Page title has been auto trimmed to %s chars (maximum is %s).', $length, $max));
        }
      }
    }
    return $title;
  }

  /**
   * Sets title for the current web response.
   *
   * @param string $title Title name
   * @param boolean $escape true, for escaping the title
   * @param boolean $replace true, for allowing to overwrite the title
   * @param boolean $use_i18n true, for allowing to translate the title
   * @return sfWebResponse
   */
  public function setTitle($title, $escape = true, $replace = true, $use_i18n = false)
  {
    $old_title = $this->getTitle(false);
    if($replace || empty($old_title))
    {
      if($use_i18n && sfConfig::get('sf_i18n'))
      {
        $title = __($title);
      }
      if($escape)
      {
        $title = htmlspecialchars($title, ENT_QUOTES, sfConfig::get('sf_charset'));
      }
      $this->setParameter('title', $title, 'helper/asset/auto/title');
    }
    return $this;
  }


  /**
   * Sets title mode
   *
   * @param string $policy (Replace, prepend, append)
   * @throws InvalidArgumentException
   * @return sfWebResponse
   */
  public function setTitleMode($policy)
  {
    $policy = strtoupper($policy);
    if(in_array($policy, array(self::TITLE_MODE_APPEND, self::TITLE_MODE_PREPEND, self::TITLE_MODE_REPLACE)))
    {
      $this->setParameter('mode', $policy, 'helper/asset/auto/title');
    }
    else
    {
      throw new InvalidArgumentException(sprintf('Invalid title mode "%s" given. Valid modes are: %s', $policy,
          join(',',  array(self::TITLE_MODE_APPEND, self::TITLE_MODE_PREPEND, self::TITLE_MODE_REPLACE))));
    }
    return $this;
  }

  /**
   * Returns current title policy
   *
   * @return string
   */
  public function getTitleMode()
  {
    return $this->getParameter('mode', '', 'helper/asset/auto/title');
  }

  /**
   * Adds a meta header to the current web response. (removed i18n call to translate metas)
   * This seems a little bit odd to me.
   *
   * @param string $key Name of the header
   * @param string $value Meta header to be set
   * @param boolean $replace true if it's replaceable
   * @param boolean $escape true for escaping the header
   * @return sfWebResponse
   */
  public function addMeta($key, $value, $replace = true, $escape = true)
  {
    $key = strtolower($key);

    if($escape)
    {
      $value = htmlspecialchars($value, ENT_QUOTES, sfConfig::get('sf_charset'));
    }

    if($replace || !$this->getParameter($key, null, 'helper/asset/auto/meta'))
    {
      $this->setParameter($key, $value, 'helper/asset/auto/meta');
    }

    return $this;
  }

  /**
   * Set id attribute for body tag
   *
   * @param string $id Body id
   * @param boolean $replace Replace existing id?
   * @return sfWebResponse
   */
  public function setBodyId($id, $replace = true)
  {
    $exists = $this->getParameter('id', false, 'helper/asset/auto/body');
    if($exists && !$replace)
    {
      return;
    }
    $this->setParameter('id', $id, 'helper/asset/auto/body');
    return $this;
  }

  /**
   * Get id attribute for body tag
   *
   * @param string $default Default value to return when the body id is set
   * @return string
   */
  public function getBodyId($default = null)
  {
    return $this->getParameter('id', $default, 'helper/asset/auto/body');
  }

  /**
   * Add onLoad event to body tag
   *
   * @param string Javascript function or code to run
   * @return sfWebResponse
   */
  public function addBodyOnLoad($command)
  {
    $onLoad = $this->getParameter('onload', array(), 'helper/asset/auto/body');
    $onLoad[] = $command;
    $this->setParameter('onload', $onLoad, 'helper/asset/auto/body');
    return $this;
  }

  /**
   * Get onLoad events for body tag
   *
   * @return array
   */
  public function getBodyOnLoad()
  {
    return $this->getParameter('onload', array(), 'helper/asset/auto/body');
  }

  /**
   * Clear body on load events
   *
   * @return sfWebResponse
   */
  public function clearBodyOnLoad()
  {
    $this->setParameter('onload', array(), 'helper/asset/auto/body');
    return $this;
  }

  /**
   * Add onLoad event to body tag
   *
   * @param string $command Javascript function or code to run
   * @return sfWebResponse
   */
  public function addBodyOnUnLoad($command)
  {
    $onLoad = $this->getParameter('onunload', array(), 'helper/asset/auto/body');
    $onLoad[] = $command;
    $this->setParameter('onunload', $onLoad, 'helper/asset/auto/body');
    return $this;
  }

  /**
   * Get onLoad events for body tag
   *
   * @return array
   */
  public function getBodyOnUnLoad()
  {
    return $this->getParameter('onunload', array(), 'helper/asset/auto/body');
  }

  /**
   * Clear body on load events
   *
   * @return sfWebResponse
   */
  public function clearBodyOnUnLoad()
  {
    $this->setParameter('onunload', array(), 'helper/asset/auto/body');
    return $this;
  }

  /**
   * Adds CSS class to HTML body element
   *
   * @param string $class CSS class to remove
   * @return sfWebResponse
   */
  public function addBodyClass($class)
  {
    $classes = $this->getParameter('classes', array(), 'helper/asset/auto/body');
    $classes[] = $class;
    $classes = array_unique($classes);
    $this->setParameter('classes', $classes, 'helper/asset/auto/body');
    return $this;
  }

  /**
   * Removes CSS class assigned to body
   *
   * @param string $class or array of CSS classes to remove
   * @return sfWebResponse
   */
  public function removeBodyClass($class)
  {
    $classes = $this->getParameter('classes', array(), 'helper/asset/auto/body');
    if(!is_array($class))
    {
      $class = array($class);
    }
    foreach($class as $c)
    {
      if(isset($classes[$c]))
      {
        unset($classes[$c]);
      }
    }
    $this->setParameter('classes', $classes, 'helper/asset/auto/body_class');
    return $this;
  }

  /**
   * Clears body classes
   *
   * @return sfWebResponse
   */
  public function clearBodyClasses()
  {
    $this->setParameter('classes', array(), 'helper/asset/auto/body');
    return $this;
  }

  /**
   * Gets CSS classes for body tag
   *
   * @return array Array of CSS classes
   */
  public function getBodyClasses()
  {
    return $this->getParameter('classes', array(), 'helper/asset/auto/body');
  }

  /**
   * Adds auto discovery links to response.
   *
   * Autodiscovery link is something like:
   * <link rel="alternate" type="application/rss+xml" title="RSS" href="http://www.curenthost.com/module/feed" />
   *
   * @param string $url Url of the feed (not routing rule!)
   * @param string $type Feed type ('rss', 'atom')
   * @param array additional HTML compliant <link> tag parameters
   * @return sfWebResponse
   */
  public function addAutoDiscoveryLink($url, $type = 'rss', $tag_options = array())
  {
    $link = array(
      'url'         => $url,
      'type'        => $type,
      'tag_options' => $tag_options,
    );
    // url is the key, so we preserve not to include the links more than once
    $this->setParameter($url, $link, 'helper/asset/auto/discovery_links');
    return $this;
  }

  /**
   * Clears autodiscovery links
   *
   * @return sfWebResponse
   */
  public function clearAutoDiscoveryLinks()
  {
    $this->getParameterHolder()->removeNamespace('helper/asset/auto/discovery_links');
    return $this;
  }

  /**
   * Retrieve autodiscovery links
   *
   * @return array Array of auto discovery links
   */
  public function getAutoDiscoveryLinks()
  {
    return $this->getParameterHolder()->getAll('helper/asset/auto/discovery_links');
  }

  /**
   * Adds meta description to response
   *
   * @param $description string Description
   * @param $replace boolean Replace existing description?
   * @return sfWebResponse
   */
  public function addMetaDescription($description, $replace = false)
  {
    // add the description to response
    $current = $this->getMeta('description');
    if($current && !$replace)
    {
      $description = $current . ' ' . $description;
    }
    $this->addMeta('description', trim($description));
    return $this;
  }

  /**
   * Sets meta description
   *
   * @param $description string Description
   * @return sfWebResponse
   */
  public function setMetaDescription($description)
  {
    $this->addMetaDescription($description, true);
    return $this;
  }

  /**
   * Clears meta description
   *
   * @return sfWebResponse
   */
  public function clearMetaDescription()
  {
    $this->setParameter('description', null, 'helper/asset/auto/meta');
    return $this;
  }

  /**
   * Sets SEO parameters to response.
   *
   * @param array $seo array(title, description (or meta_description), keywords(or meta_keywords), title_mode)
   * @param boolean $override Override existing values?
   * @return sfWebResponse
   */
  public function setSeo(array $seo, $override = false)
  {
    if(array_key_exists('title', $seo))
    {
      $this->setTitle($seo['title']);
    }

    if(array_key_exists('description', $seo))
    {
      $override ? $this->setMetaDescription($seo['description'])
                : $this->addMetaDescription($seo['description']);
    }
    // meta description is also valid
    elseif(array_key_exists('meta_description', $seo))
    {
      $override ? $this->setMetaDescription($seo['meta_description'])
                :  $this->addMetaDescription($seo['meta_description']);
    }

    if(array_key_exists('keywords', $seo))
    {
      $override ? $this->setMetaKeywords($seo['keywords'])
                : $this->addMetaDescription($seo['keywords']);
    }
    // meta description is also valid
    elseif(array_key_exists('meta_keywords', $seo))
    {
      $override ? $this->setMetaKeywords($seo['meta_keywords'])
                : $this->addMetaDescription($seo['meta_keywords']);
    }

    if(array_key_exists('title_mode', $seo))
    {
      $this->setTitleMode($seo['title_mode']);
    }

    return $this;
  }

  /**
   * Adds meta keywords to response. Any previous keywords are not loosed.
   *
   * @param string $keywords List of keywords
   * @param boolean $preserveUniqueness Preserve keywords uniqueness?
   * @return sfWebResponse
   */
  public function addMetaKeywords($keywords, $preserveUniqueness = true)
  {
    // get current meta keywords
    $current  = explode(',', $this->getMetaKeywords());
    $keywords = explode(',', $keywords);

    // merge the arrays
    $all = array_merge($current, $keywords);
    // trim the values
    $all = array_map('trim', $all);

    if($preserveUniqueness)
    {
      $all = array_unique($all);
    }

    // replace the keywords with new set
    $this->addMeta('keywords', trim(join(',', $all), ','), true);

    return $this;
  }

  /**
   * Set meta keywords to response. Previous keywords are cleared.
   *
   * @param string $keywords
   * @return sfWebResponse
   */
  public function setMetaKeywords($keywords)
  {
    $keywords = explode(',', $keywords);
    $keywords = array_map('trim', $keywords);
    // replace the keywords with new set
    $this->addMeta('keywords', trim(join(',', $keywords), ','), true);
    return $this;
  }

  /**
   * Clears meta keywords
   *
   * @return sfWebResponse
   */
  public function clearMetaKeywords()
  {
    $this->setParameter('keywords', null, 'helper/asset/auto/meta');
    return $this;
  }

  /**
   * Retrieves meta keywords from response. Alias for ->getMeta('keywords');
   *
   * @return string
   */
  public function getMetaKeywords()
  {
    return $this->getMeta('keywords');
  }

  /**
   * Gets meta tag
   *
   * @param $name Name of meta tag (ie. keywords, description...)
   * @param $default Default value to return if there is no value for meta tag
   * @return string The meta
   */
  public function getMeta($name, $default = null)
  {
    return $this->getParameter($name, $default, 'helper/asset/auto/meta');
  }

  /**
   * Clears javascript files from the current web response.
   *
   * @return sfWebResponse
   */
  public function clearJavascripts()
  {
    $namespaces = array(
      'helper/asset/auto/javascript',
      'helper/asset/auto/javascript/last',
      'helper/asset/auto/javascript/first',
    );

    foreach($namespaces as $namespace)
    {
      $this->getParameterHolder()->removeNamespace($namespace);
    }

    return $this;
  }

  /**
   * Set canonical URL to response
   *
   * @param string $url The canonical URL
   * @return sfWebResponse
   */
  public function setCanonicalUrl($url)
  {
    $this->setParameter('canonical', $url, 'helper/asset/auto/canonical');
    return $this;
  }

  /**
   * Set canonical URL to response
   *
   * @param string Current canonical url
   * @return string
   */
  public function getCanonicalUrl()
  {
    return $this->getParameter('canonical', null, 'helper/asset/auto/canonical');
  }

  /**
   * Clears canonical URL from response
   *
   * @return sfWebResponse
   */
  public function clearCanonicalUrl()
  {
    $this->setParameter('canonical', null, 'helper/asset/auto/canonical');
    return $this;
  }

  /**
   * Clears stylesheet files from the current web response.
   *
   * @return sfWebResponse
   */
  public function clearStylesheets()
  {
    $namespaces = array(
      'helper/asset/auto/stylesheet',
      'helper/asset/auto/stylesheet/last',
      'helper/asset/auto/stylesheet/first',
    );
    foreach($namespaces as $namespace)
    {
      $this->getParameterHolder()->removeNamespace($namespace);
    }
    return $this;
  }

  /**
   * Retrieves slots from the current web response.
   *
   * @return array Array of slots
   */
  public function getSlots()
  {
    return $this->getParameter('slots', array(), 'helper/view/slot');
  }

  /**
   * Sets a slot content.
   *
   * @param string $name Slot name (unique name)
   * @param string $content Content
   */
  public function setSlot($name, $content)
  {
    $slots = $this->getSlots();
    $slots[$name] = $content;
    $this->setParameter('slots', $slots, 'helper/view/slot');
    return $this;
  }

  /**
   * Has the response given slot?
   *
   * @param string $name The slot name
   * @return boolean
   */
  public function hasSlot($name)
  {
    return array_key_exists($name, $this->getSlots());
  }

  /**
   * Returns slot
   *
   * @param string $name
   * @param string $default
   * @return mixed
   */
  public function getSlot($name, $default = null)
  {
    $slots = $this->getSlots();
    return array_key_exists($name, $slots) ?$slots[$name] : $default;
  }


  /**
   * Clears slots
   *
   * @return sfWebResponse
   */
  public function clearSlots()
  {
    $this->getParameterHolder()->removeNamespace('helper/view/slot');
    return $this;
  }

  /**
   * Merges all properties from a given sfWebResponse object to the current one.
   *
   * @param sfWebResponse $response An sfWebResponse instance
   * @return sfWebResponse
   */
  public function merge(sfWebResponse $response)
  {
    $parameterHolder = $response->getParameterHolder();
    foreach($parameterHolder->getNamespaces() as $namespace)
    {
      $value = $this->getParameterHolder()->getAll($namespace);

      switch($namespace)
      {
        // special care for slots
        case 'helper/view/slot':
          $value = sfToolkit::arrayDeepMerge($value, $parameterHolder->getAll($namespace));
        break;

        default:
          $value = array_merge($value, $parameterHolder->getAll($namespace));
        break;
      }

      $this->getParameterHolder()->removeNamespace($namespace);
      $this->getParameterHolder()->add($value, $namespace);
    }

    return $this;
  }

  /**
   * Copies properties from the given $response
   *
   * @param sfResponse Response instance
   * @return sfWebResponse
   */
  public function mergeProperties(sfWebResponse $response)
  {
    $this->parameterHolder = clone $response->getParameterHolder();
    return $this;
  }

  /**
   * Executes the shutdown procedure.
   */
  public function shutdown()
  {
  }

  /**
   * @see sfResponse
   */
  public function serialize()
  {
    return serialize(array($this->content, $this->statusCode, $this->statusText, $this->parameterHolder, $this->headerOnly));
  }

  /**
   * @see sfResponse
   */
  public function unserialize($serialized)
  {
    list($this->content, $this->statusCode, $this->statusText, $this->parameterHolder, $this->headerOnly) = unserialize($serialized);
  }

}
