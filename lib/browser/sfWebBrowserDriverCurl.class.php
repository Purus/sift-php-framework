<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Curl driver for sfWebBrowser
 *
 * @package    Sift
 * @subpackage browser
 * @link http://cz1.php.net/curl
 */
class sfWebBrowserDriverCurl implements sfIWebBrowserDriver {

  protected $options = array();
  protected $curl = null;
  protected $headers = array();

  /**
   * Constructs curl driver instance
   *
   * Accepts an option of parameters passed to the PHP curl adapter:
   *
   *  ssl_verify  => [true/false]
   *  verbose     => [true/false]
   *  verbose_log => [true/false]
   *
   * Additional options are passed as curl options, under the form:
   *  userpwd => CURL_USERPWD
   *  timeout => CURL_TIMEOUT
   *  ...
   *
   * @param array $options Curl-specific options
   */
  public function __construct($options = array())
  {
    if(!extension_loaded('curl'))
    {
      throw new Exception('Curl extension not loaded');
    }

    $this->options = $options;
    $curl_options = $options;

    $this->curl = curl_init();

    // cookies
    if(isset($curl_options['cookies']))
    {
      if(isset($curl_options['cookies_file']))
      {
        $cookie_file = $curl_options['cookies_file'];
        unset($curl_options['cookies_file']);
      }
      else
      {
        $cookie_file = sfConfig::get('sf_data_dir') . '/_web_browser/curl/cookies.txt';
      }
      if(isset($curl_options['cookies_dir']))
      {
        $cookie_dir = $curl_options['cookies_dir'];
        unset($curl_options['cookies_dir']);
      }
      else
      {
        $cookie_dir = sfConfig::get('sf_data_dir') . '/_web_browser/curl';
      }
      if(!is_dir($cookie_dir))
      {
        if(!mkdir($cookie_dir, 0777, true))
        {
          throw new Exception(sprintf('Could not create directory "%s"', $cookie_dir));
        }
      }

      curl_setopt($this->curl, CURLOPT_COOKIESESSION, false);
      curl_setopt($this->curl, CURLOPT_COOKIEJAR, $cookie_file);
      curl_setopt($this->curl, CURLOPT_COOKIEFILE, $cookie_file);
      unset($curl_options['cookies']);
    }

    // default settings
    curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($this->curl, CURLOPT_AUTOREFERER, true);
    curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($this->curl, CURLOPT_FRESH_CONNECT, true);

    if(isset($curl_options['followlocation']))
    {
      curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, (boolean)$this->options['followlocation']);
      unset($curl_options['followlocation']);
    }

    // activate ssl certificate verification?
    if(isset($curl_options['ssl_verify_host']))
    {
      curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, (bool) $this->options['ssl_verify_host']);
      unset($curl_options['ssl_verify_host']);
    }

    if(isset($curl_options['ssl_verify']))
    {
      curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, (bool) $this->options['ssl_verify']);
      unset($curl_options['ssl_verify']);
    }

    if(isset($curl_options['proxy']))
    {
      curl_setopt($this->curl, CURLOPT_PROXY, $curl_options['proxy']);
      unset($curl_options['proxy']);
    }

    if(isset($curl_options['proxy_port']))
    {
      curl_setopt($this->curl, CURLOPT_PROXYPORT, $curl_options['proxy_port']);
      unset($curl_options['proxy_port']);
    }

    // verbose execution?
    if(isset($curl_options['verbose']))
    {
      curl_setopt($this->curl, CURLOPT_NOPROGRESS, false);
      curl_setopt($this->curl, CURLOPT_VERBOSE, true);
      unset($curl_options['cookies']);
    }

    if(isset($curl_options['verbose_log']))
    {
      if(isset($curl_options['log_dir']))
      {
        $dir = $curl_options['log_dir'];
        unset($curl_options['log_dir']);
      }
      else
      {
        $dir = sfConfig::get('sf_log_dir');
      }

      if(!is_dir($dir))
      {
        throw new InvalidArgumentException(sprintf('Log directory "%s" does not exist.', $dir));
      }

      $log_file = $dir . '/web_browser_curl_verbose.log';

      curl_setopt($this->curl, CURLOPT_VERBOSE, true);
      $this->fh = fopen($log_file, 'a+b');
      curl_setopt($this->curl, CURLOPT_STDERR, $this->fh);

      unset($curl_options['verbose_log']);
    }

    // Additional options
    foreach($curl_options as $key => $value)
    {
      $const = constant('CURLOPT_' . strtoupper($key));
      if(!is_null($const))
      {
        curl_setopt($this->curl, $const, $value);
      }
    }

    // response header storage - uses callback function
    curl_setopt($this->curl, CURLOPT_HEADERFUNCTION, array($this, 'readCurlheader'));
  }

  /**
   * Submits a request
   *
   * @param string  The request uri
   * @param string  The request method
   * @param array   The request parameters (associative array)
   * @param array   The request headers (associative array)
   *
   * @return sfWebBrowser The current browser object
   */
  public function call($browser, $uri, $method = 'GET', $parameters = array(), $headers = array())
  {
    // uri
    curl_setopt($this->curl, CURLOPT_URL, $uri);

    // request headers
    $m_headers = array_merge($browser->getDefaultRequestHeaders(), $browser->initializeRequestHeaders($headers));
    $request_headers = explode("\r\n", $browser->prepareHeaders($m_headers));
    curl_setopt($this->curl, CURLOPT_HTTPHEADER, $request_headers);

    // encoding support
    // this causes that the response is decoded right from this adapter!
    // which is wrong!
    if(isset($headers['Accept-Encoding']))
    {
      // curl_setopt($this->curl, CURLOPT_ENCODING, $headers['Accept-Encoding']);
    }

    // timeout support
    if(isset($this->options['Timeout']))
    {
      curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->options['Timeout']);
    }

    if(!empty($parameters))
    {
      if(!is_array($parameters))
      {
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $parameters);
      }
      else
      {
        // multipart posts (file upload support)
        $has_files = false;
        foreach($parameters as $name => $value)
        {
          if(is_array($value))
          {
            continue;
          }
          if(is_file($value))
          {
            $has_files = true;
            $parameters[$name] = '@' . realpath($value);
          }
        }
        if($has_files)
        {
          curl_setopt($this->curl, CURLOPT_POSTFIELDS, $parameters);
        }
        else
        {
          curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($parameters, '', '&'));
        }
      }
    }

    // handle any request method
    curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);

    $response = curl_exec($this->curl);

    if(curl_errno($this->curl))
    {
      throw new Exception(curl_error($this->curl), curl_errno($this->curl));
    }

    $requestInfo = curl_getinfo($this->curl);

    $browser->setResponseCode($requestInfo['http_code']);
    $browser->setResponseHeaders($this->headers);
    $browser->setResponseText($response);

    // clear response headers
    $this->headers = array();

    return $browser;
  }

  public function __destruct()
  {
    curl_close($this->curl);
  }

  protected function readCurlHeader($curl, $headers)
  {
    $this->headers[] = $headers;

    return strlen($headers);
  }

}
