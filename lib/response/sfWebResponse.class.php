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
  protected
    $cookies     = array(),
    $statusCode  = 200,
    $statusText  = 'OK',
    $statusTexts = array(
      '100' => 'Continue',
      '101' => 'Switching Protocols',
      '200' => 'OK',
      '201' => 'Created',
      '202' => 'Accepted',
      '203' => 'Non-Authoritative Information',
      '204' => 'No Content',
      '205' => 'Reset Content',
      '206' => 'Partial Content',
      '300' => 'Multiple Choices',
      '301' => 'Moved Permanently',
      '302' => 'Found',
      '303' => 'See Other',
      '304' => 'Not Modified',
      '305' => 'Use Proxy',
      '306' => '(Unused)',
      '307' => 'Temporary Redirect',
      '400' => 'Bad Request',
      '401' => 'Unauthorized',
      '402' => 'Payment Required',
      '403' => 'Forbidden',
      '404' => 'Not Found',
      '405' => 'Method Not Allowed',
      '406' => 'Not Acceptable',
      '407' => 'Proxy Authentication Required',
      '408' => 'Request Timeout',
      '409' => 'Conflict',
      '410' => 'Gone',
      '411' => 'Length Required',
      '412' => 'Precondition Failed',
      '413' => 'Request Entity Too Large',
      '414' => 'Request-URI Too Long',
      '415' => 'Unsupported Media Type',
      '416' => 'Requested Range Not Satisfiable',
      '417' => 'Expectation Failed',
      '500' => 'Internal Server Error',
      '501' => 'Not Implemented',
      '502' => 'Bad Gateway',
      '503' => 'Service Unavailable',
      '504' => 'Gateway Timeout',
      '505' => 'HTTP Version Not Supported',
    ),

    $headerOnly  = false;

  /**
   * Initializes this sfWebResponse.
   *
   * @param sfContext A sfContext instance
   * @return boolean true, if initialization completes successfully, otherwise false
   * @throws sfInitializationException If an error occurs while initializing this Response
   */
  public function initialize($context, $parameters = array())
  {
    parent::initialize($context, $parameters);

    if ('HEAD' == $context->getRequest()->getMethodName())
    {
      $this->setHeaderOnly(true);
    }

    // setup title policy and global title
    $mode = strtolower(sfConfig::get('app_title_mode', 'prepend'));
    $this->setTitleMode($mode);
  }

  /**
   * Sets if the response consist of just HTTP headers.
   *
   * @param boolean
   */
  public function setHeaderOnly($value = true)
  {
    $this->headerOnly = (boolean) $value;
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
   * @param string HTTP header name
   * @param string Value for the cookie
   * @param string Cookie expiration period
   * @param string Path
   * @param string Domain name
   * @param boolean If secure
   * @param boolean If uses only HTTP
   *
   * @throws sfException If fails to set the cookie
   */
  public function setCookie($name, $value, $expire = null, $path = '/', $domain = '', $secure = false, $httpOnly = false)
  {
    if ($expire !== null)
    {
      if (is_numeric($expire))
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
  }

  /**
   * Sets response status code.
   *
   * @param string HTTP status code
   * @param string HTTP status text
   *
   */
  public function setStatusCode($code, $name = null)
  {
    $this->statusCode = $code;
    $this->statusText = null !== $name ? $name : $this->statusTexts[$code];
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
   * @param string HTTP header name
   * @param string Value
   * @param boolean Replace for the value
   *
   */
  public function setHttpHeader($name, $value, $replace = true)
  {
    $name = $this->normalizeHeaderName($name);

    if ('Content-Type' == $name)
    {
      if ($replace || !$this->getHttpHeader('Content-Type', null))
      {
        $this->setContentType($value);
      }

      return;
    }

    if (!$replace)
    {
      $current = $this->getParameter($name, '', 'sift/response/http/headers');
      $value = ($current ? $current.', ' : '').$value;
    }

    $this->setParameter($name, $value, 'sift/response/http/headers');
  }

  /**
   * Gets HTTP header current value.
   *
   * @return array
   */
  public function getHttpHeader($name, $default = null)
  {
    return $this->getParameter($this->normalizeHeaderName($name), $default, 'sift/response/http/headers');
  }

  /**
   * Has a HTTP header.
   *
   * @return boolean
   */
  public function hasHttpHeader($name)
  {
    return $this->hasParameter($this->normalizeHeaderName($name), 'sift/response/http/headers');
  }

  /**
   * Sets response content type.
   *
   * @param string Content type
   *
   */
  public function setContentType($value)
  {
    // add charset if needed (only on text content)
    if (false === stripos($value, 'charset') && (0 === stripos($value, 'text/') || strlen($value) - 3 === strripos($value, 'xml')))
    {
      $value .= '; charset='.sfConfig::get('sf_charset');
    }

    $this->setParameter('Content-Type', $value, 'sift/response/http/headers');
  }

  public function getCharset()
  {
    return sfConfig::get('sf_charset');
  }

  /**
   * Gets response content type.
   *
   * @return array
   */
  public function getContentType()
  {
    return $this->getHttpHeader('Content-Type', 'text/html; charset='.sfConfig::get('sf_charset'));
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

    if (substr(php_sapi_name(), 0, 3) == 'cgi')
    {
      // fastcgi servers cannot send this status information because it was sent by them already due to the HTT/1.0 line
      // so we can safely unset them.
      unset($headers['Status']);
    }

    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->getContext()->getLogger()->info('{sfResponse} send status "'.$status.'"');
    }

    // headers
    foreach ($headers as $name => $value)
    {
      header($name.': '.$value);

      if (sfConfig::get('sf_logging_enabled') && $value != '')
      {
        $this->getContext()->getLogger()->info('{sfResponse} send header "'.$name.'": "'.$value.'"');
      }
    }

    // cookies
    foreach ($this->cookies as $cookie)
    {
      if (version_compare(phpversion(), '5.2', '>='))
      {
        setrawcookie($cookie['name'], $cookie['value'], $cookie['expire'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httpOnly']);
      }
      else
      {
        setrawcookie($cookie['name'], $cookie['value'], $cookie['expire'], $cookie['path'], $cookie['domain'], $cookie['secure']);
      }

      if (sfConfig::get('sf_logging_enabled'))
      {
        $this->getContext()->getLogger()->info('{sfResponse} send cookie "'.$cookie['name'].'": "'.$cookie['value'].'"');
      }
    }
  }

  /**
   * Sends headers and content. Responsible for executing content if it is
   * a callable.
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
        // clear output buffer
        while(ob_get_level())
        {
          ob_end_clean();
        }

        if(sfConfig::get('sf_logging_enabled'))
        {
          $this->getContext()->getLogger()->info(sprintf('{sfResponse} calling callable "%s"', $callableName));
        }

        call_user_func($this->content);
      }
    }
  }

  /**
   * Sends the HTTP headers and the content.
   */
  public function send()
  {
    sfCore::getEventDispatcher()->notify(
            new sfEvent('response.send', array('response' => &$this)));

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
   *
   */
  public function hardFlush()
  {
    echo str_repeat(' ', 256);
    flush();
  }

  /**
   * Retrieves a normalized Header.
   *
   * @param string Header name
   *
   * @return string Normalized header
   */
  protected function normalizeHeaderName($name)
  {
    return preg_replace('/\-(.)/e', "'-'.strtoupper('\\1')", strtr(ucfirst(strtolower($name)), '_', '-'));
  }

  /**
   * Retrieves a formated date.
   *
   * @param string Timestamp
   * @param string Format type
   *
   * @return string Formated date
   */
  public static function getDate($timestamp, $type = 'rfc1123')
  {
    $type = strtolower($type);

    if ($type == 'rfc1123')
    {
      return substr(gmdate('r', $timestamp), 0, -5).'GMT';
    }
    else if ($type == 'rfc1036')
    {
      return gmdate('l, d-M-y H:i:s ', $timestamp).'GMT';
    }
    else if ($type == 'asctime')
    {
      return gmdate('D M j H:i:s', $timestamp);
    }
    else
    {
      throw new sfParameterException('The second getDate() method parameter must be one of: rfc1123, rfc1036 or asctime');
    }
  }

  /**
   * Adds vary to a http header.
   *
   * @param string HTTP header
   */
  public function addVaryHttpHeader($header)
  {
    $vary = $this->getHttpHeader('Vary');
    $currentHeaders = array();
    if ($vary)
    {
      $currentHeaders = preg_split('/\s*,\s*/', $vary);
    }
    $header = $this->normalizeHeaderName($header);

    if (!in_array($header, $currentHeaders))
    {
      $currentHeaders[] = $header;
      $this->setHttpHeader('Vary', implode(', ', $currentHeaders));
    }
  }

  /**
   * Adds an control cache http header.
   *
   * @param string HTTP header
   * @param string Value for the http header
   */
  public function addCacheControlHttpHeader($name, $value = null)
  {
    $cacheControl = $this->getHttpHeader('Cache-Control');
    $currentHeaders = array();
    if ($cacheControl)
    {
      foreach (preg_split('/\s*,\s*/', $cacheControl) as $tmp)
      {
        $tmp = explode('=', $tmp);
        $currentHeaders[$tmp[0]] = isset($tmp[1]) ? $tmp[1] : null;
      }
    }
    $currentHeaders[strtr(strtolower($name), '_', '-')] = $value;

    $headers = array();
    foreach ($currentHeaders as $key => $value)
    {
      $headers[] = $key.(null !== $value ? '='.$value : '');
    }

    $this->setHttpHeader('Cache-Control', implode(', ', $headers));
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
   * @param string Key to replace
   * @param string Value for the replacement
   * @param boolean Replace or not
   */
  public function addHttpMeta($key, $value, $replace = true)
  {
    $key = $this->normalizeHeaderName($key);

    // set HTTP header
    $this->setHttpHeader($key, $value, $replace);

    if ('Content-Type' == $key)
    {
      $value = $this->getContentType();
    }
    else if (!$replace)
    {
      $current = $this->getParameter($key, '', 'helper/asset/auto/httpmeta');
      $value = ($current ? $current.', ' : '').$value;
    }

    $this->setParameter($key, $value, 'helper/asset/auto/httpmeta');
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
   * @param string Position
   *
   * @return string Stylesheets
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
   * @param string Stylesheet
   * @param string Position
   * @param string Stylesheet options
   */
  public function addStylesheet($css, $position = '', $options = array())
  {
    $this->setParameter($css, $options, 'helper/asset/auto/stylesheet'.($position ? '/'.$position : ''));
  }

  /**
   * Removes a stylesheet from the current web response.
   *
   * @param string $file The stylesheet file to remove
   */
  public function removeStylesheet($file)
  {
    foreach(array('first', '', 'last') as $position)
    {
      $this->getParameterHolder()->remove($file, 'helper/asset/auto/stylesheet'.($position ? '/'.$position : ''));
    }
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
   * @param string Javascript code
   * @param string Position
   * @param string Javascript options
   */
  public function addJavascript($js, $position = '', $options = array())
  {
    $this->setParameter($js, $options, 'helper/asset/auto/javascript'.($position ? '/'.$position : ''));
  }

  /**
   * Removes a JavaScript file from the current web response.
   *
   * @param string $file The Javascript file to remove
   */
  public function removeJavascript($file)
  {
    foreach(array('first', '', 'last') as $position)
    {
      $this->getParameterHolder()->remove($file, 'helper/asset/auto/javascript'.($position ? '/'.$position : ''));
    }
  }

  /**
   * Clear all assets. Discovery links, javascripts and stylesheets.
   *
   */
  public function resetAssets()
  {
    $this->clearAutoDiscoveryLinks();
    $this->clearJavascripts();
    $this->clearStylesheets();
  }

  /**
   * Retrieves cookies from the current web response.
   *
   * @return array Cookies
   */
  public function getCookies()
  {
    $cookies = array();
    foreach ($this->cookies as $cookie)
    {
      $cookies[$cookie['name']] = $cookie;
    }

    return $cookies;
  }

  /**
   * Retrieves HTTP headers from the current web response.
   *
   * @return string HTTP headers
   */
  public function getHttpHeaders()
  {
    return $this->getParameterHolder()->getAll('sift/response/http/headers');
  }

  /**
   * Cleans HTTP headers from the current web response.
   */
  public function clearHttpHeaders()
  {
    $this->getParameterHolder()->removeNamespace('sift/response/http/headers');
  }

  /**
   * Retrieves title for the current web response.
   *
   * @param boolean true, for including global title
   * @return string|throws exception Title or throws exception if misconfigured
   */
  public function getTitle($include_global = true)
  {
    $title = trim($this->getParameter('title', '', 'helper/asset/auto/title'));

    $global_title = sfConfig::get('app_title_name');

    if($include_global && $global_title)
    {
      $mode       = $this->getTitleMode();
      $separator  = trim(sfConfig::get('app_title_separator', '~'));

      if(!empty($title) && !empty($global_title) && $title != $global_title)
      {
        switch($mode)
        {
          case 'append':
            $title = sprintf('%s %s %s', $global_title, $separator, $title);
          break;

          case 'prepend':
            $title = sprintf('%s %s %s', $title, $separator, $global_title);
          break;

          case 'replace':
            // do nothing
          break;

          default:
            throw new sfException(sprintf('Title separator has been misconfigured. Invalid value "%s". Use "prepend" or "append" or "replace".', $mode));
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
        $title  = myText::truncate($title, $max, ' ...');
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
   * @param string Title name
   * @param boolean true, for escaping the title
   * @param boolean true, for allowing to overwrite the title
   * @param boolean true, for allowing to translate the title
   *
   */
  public function setTitle($title, $escape = true, $replace = true, $use_i18n = false)
  {
    $old_title = $this->getTitle(false);
    if($replace || empty($old_title))
    {
      if($use_i18n && sfConfig::get('sf_i18n'))
      {
        $title = $this->getContext()->getI18N()->__($title);
      }
      if($escape)
      {
        $title = htmlspecialchars($title, ENT_QUOTES, sfConfig::get('sf_charset'));
      }
      $this->setParameter('title', $title, 'helper/asset/auto/title');
    }
  }

  const TITLE_MODE_REPLACE = 'replace';
  const TITLE_MODE_PREPEND = 'prepend';
  const TITLE_MODE_APPEND  = 'append';

  /**
   *
   * @param string $policy
   *
   */
  public function setTitleMode($policy)
  {
    if(in_array($policy, array('replace', 'prepend', 'append')))
    {
      $this->setParameter('title_mode', constant('self::TITLE_MODE_'.strtoupper($policy)), 'helper/asset/auto/title');
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
    return $this->getParameter('title_mode', false, 'helper/asset/auto/title');
  }

  /**
   * Adds a meta header to the current web response. (removed i18n call to translate metas)
   * This seems a little bit odd to me.
   *
   * @param string Name of the header
   * @param string Meta header to be set
   * @param boolean true if it's replaceable
   * @param boolean true for escaping the header
   */
  public function addMeta($key, $value, $replace = true, $escape = true)
  {
    $key = strtolower($key);

    if($escape)
    {
      $value = htmlspecialchars($value, ENT_QUOTES, sfConfig::get('sf_charset'));
    }

    if ($replace || !$this->getParameter($key, null, 'helper/asset/auto/meta'))
    {
      $this->setParameter($key, $value, 'helper/asset/auto/meta');
    }
  }

  /**
   * Set id attribute for body tag
   *
   * @return void
   *
   */
  public function setBodyId($id, $replace = true)
  {
    $exists = $this->getParameter('id', false, 'helper/asset/auto/body');
    if($exists && !$replace)
    {
      return;
    }
    $this->setParameter('id', $id, 'helper/asset/auto/body');
  }

  /**
   * Get id attribute for body tag
   *
   * @param default default value to return when on Id is set
   * @return string
   */
  public function getBodyId()
  {
    return $this->getParameter('id', null, 'helper/asset/auto/body');
  }

  /**
   * Add onLoad event to body tag
   *
   * @param string javascript function or code to run
   * @return void
   */
  public function addBodyOnLoad($command)
  {
    $onLoad   = $this->getParameter('onload', array(), 'helper/asset/auto/body');
    $onLoad[] = $command;
    $this->setParameter('onload', $onLoad, 'helper/asset/auto/body');
  }

  /**
   * Get onLoad events for body tag
   *
   * @return array
   **/
  public function getBodyOnLoad()
  {
    return $this->getParameter('onload', array(), 'helper/asset/auto/body');
  }

  /**
   * Clear body on load events
   *
   * @return void
   */
  public function clearBodyOnLoad()
  {
    $this->setParameter('onload', array(), 'helper/asset/auto/body');
  }

  /**
   * Add onLoad event to body tag
   *
   * @param string javascript function or code to run
   * @return void
   */
  public function addBodyOnUnLoad($command)
  {
    $onLoad   = $this->getParameter('onunload', array(), 'helper/asset/auto/body');
    $onLoad[] = $command;
    $this->setParameter('onunload', $onLoad, 'helper/asset/auto/body');
  }

  /**
   * Get onLoad events for body tag
   *
   * @return array
   **/
  public function getBodyOnUnLoad()
  {
    return $this->getParameter('onunload', array(), 'helper/asset/auto/body');
  }

  /**
   * Clear body on load events
   *
   * @return void
   */
  public function clearBodyOnUnLoad()
  {
    $this->setParameter('onunload', array(), 'helper/asset/auto/body');
  }

  /**
   * Adds CSS class to HTML body element
   *
   * @param string CSS class to remove
   * @return array Array of CSS classes
   */
  public function addBodyClass($class)
  {
    $classes    = $this->getParameter('classes', array(), 'helper/asset/auto/body');
    $classes[]  = $class;
    $classes    = array_unique($classes);
    $this->setParameter('classes', $classes, 'helper/asset/auto/body');
  }

  /**
   * Removes CSS class assigned to body
   *
   * @param string or array of CSS classes to remove
   * @return array Array of CSS classes
   */
  public function removeBodyClass($class)
  {
    $classes    = $this->getParameter('classes', array(), 'helper/asset/auto/body');
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
  }

  /**
   * Clears body classes
   *
   * @return void
   * @deprecated
   */
  public function resetBodyClass()
  {
    if(sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->err('{myWebResponse} resetBodyClass is deprecated. Use clearBodyClasses() instead.');
    }
    $this->clearBodyClasses();
  }

  /**
   * Clears body classes
   *
   * @return void
   */
  public function clearBodyClasses()
  {
    $this->setParameter('classes', array(), 'helper/asset/auto/body');
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
   * Sets a compressed cookie.
   *
   * @param string Cookie name
   * @param string Value for the cookie
   * @param string Cookie expiration period
   * @param string Path
   * @param string Domain name
   * @param boolean If secure
   * @param boolean If uses only HTTP
   *
   * @throws sfException If fails to set the cookie
   */
  public function setCompressedCookie($name, $value, $expire = null, $path = '/', $domain = '', $secure = false, $httpOnly = false)
  {
    $value = sfSafeUrl::encode(gzcompress($value, 9));
    return $this->setCookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
  }

  /**
   * Adds auto discovery links to response
   *
   * Autodiscovery link is something like:
   * <link rel="alternate" type="application/rss+xml" title="RSS" href="http://www.curenthost.com/module/feed" />
   *
   * @param string url of the feed (not routing rule!)
   * @param string feed type ('rss', 'atom')
   * @param  array additional HTML compliant <link> tag parameters
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
  }

  /**
   * Clears autodiscovery links
   *
   * @return void
   */
  public function clearAutoDiscoveryLinks()
  {
    $this->getParameterHolder()->removeNamespace('helper/asset/auto/discovery_links');
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
   * @param $description String Description
   * @return void
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
  }

  /**
   * Sets meta description to response
   *
   * @param $description String Description
   * @return void
   */
  public function setMetaDescription($description)
  {
    $this->addMetaDescription($description, true);
  }

  /**
   * Clears meta description
   *
   * @return void
   */
  public function clearMetaDescription()
  {
    $this->setParameter('description', null, 'helper/asset/auto/meta');
  }

  /**
   * Sets SEO parameters to response
   *
   * @param array $seo
   * @param boolean $override Override values ? Default is false =
   *                                            add values to currently set
   */
  public function setSeo(array $seo, $override = false)
  {
    if(isset($seo['title']))
    {
      $this->setTitle($seo['title']);
    }

    if(isset($seo['description']))
    {
      $override ? $this->setMetaDescription($seo['description'])
                : $this->addMetaDescription($seo['description']);
    }
    // meta description is also valid
    elseif(isset($seo['meta_description']))
    {
      $override ? $this->setMetaDescription($seo['meta_description'])
                :  $this->addMetaDescription($seo['meta_description']);
    }

    if(isset($seo['keywords']))
    {
      $override ? $this->setMetaKeywords($seo['keywords'])
                : $this->addMetaDescription($seo['keywords']);
    }
    // meta description is also valid
    elseif(isset($seo['meta_keywords']))
    {
      $override ? $this->setMetaKeywords($seo['meta_keywords'])
                : $this->addMetaDescription($seo['meta_keywords']);
    }

    if(isset($seo['title_mode']))
    {
      $this->setTitleMode($seo['title_mode']);
    }

  }

  /**
   * Adds meta keywords to response
   *
   * @param $keywords String List of keywords
   * @param $preserve_uniqueness Boolean Preserve keywords uniqueness?
   */
  public function addMetaKeywords($keywords, $preserve_uniqueness = true)
  {
    // get current meta keywords
    $current  = explode(',', $this->getMetaKeywords());
    $keywords = explode(',', $keywords);

    // merge the arrays
    $all = array_merge($current, $keywords);
    // trim the values
    $all = array_map('trim', $all);

    if($preserve_uniqueness)
    {
      $all = array_unique($all);
    }

    // replace the keywords with new set
    $this->addMeta('keywords', trim(join(',', $all), ','), true);
  }

  /**
   * Set meta keywords to response
   *
   * @param string $keywords
   * @return void
   */
  public function setMetaKeywords($keywords)
  {
    $keywords = explode(',', $keywords);
    $keywords = array_map('trim', $keywords);

    // replace the keywords with new set
    $this->addMeta('keywords', trim(join(',', $keywords), ','), true);
  }

  /**
   * Clears meta keywords
   *
   * @return void
   */
  public function clearMetaKeywords()
  {
    $this->setParameter('keywords', null, 'helper/asset/auto/meta');
  }

  /**
   * Retrieves meta keywords from response
   *
   * @return void
   */
  public function getMetaKeywords()
  {
    return $this->getMeta('keywords');
  }

  /**
   * Gets meta tag
   *
   * @param $name Name of meta tag (ie. keywords, description...)
   * @param $default Default value to return if there is no value for tag
   *
   */
  public function getMeta($name, $default = null)
  {
    return $this->getParameter($name, $default, 'helper/asset/auto/meta');
  }

  /**
   * Clears javascript files from the current web response.
   *
   * @return void
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
  }

  /**
   * Set canonical URL to response
   *
   * @param string $url string
   * @return void
   */
  public function setCanonicalUrl($url)
  {
    $this->setParameter('canonical', $url, 'helper/asset/auto/canonical');
  }

  /**
   * Set canonical URL to response
   *
   * @param string Current canonical url
   * @return void
   */
  public function getCanonicalUrl()
  {
    return $this->getParameter('canonical', null, 'helper/asset/auto/canonical');
  }

  /**
   * Clears canonical URL from response
   *
   * @return void
   */
  public function clearCanonicalUrl()
  {
    $this->setParameter('canonical', null, 'helper/asset/auto/canonical');
  }

  /**
   * Clears stylesheet files from the current web response.
   *
   * @return void
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
  }

  /**
   * Copies a propertie to a new one.
   *
   * @param sfResponse Response instance
   */
  public function mergeProperties($response)
  {
    $this->parameterHolder = clone $response->getParameterHolder();
  }

  /**
   * Retrieves all objects handlers for the current web response.
   *
   * @return array Objects instance
   */
  public function __sleep()
  {
    return array('content', 'statusCode', 'statusText', 'parameterHolder');
  }

  /**
   * Reconstructs any result that web response instance needs.
   */
  public function __wakeup()
  {
  }

  /**
   * Executes the shutdown procedure.
   */
  public function shutdown()
  {
  }
}
