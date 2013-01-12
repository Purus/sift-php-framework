<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Utility class for checking module/action and route and url security and credentials
 * values. Provides also methods for checking IP validity or whitelisting.
 * 
 * Based on the sfActionCredentialsGetterPlugin by Ronald B. Cemer.
 * http://www.symfony-project.org/plugins/sfActionCredentialsGetterPlugin
 * 
 * @author Mishal <mishal at mishal dot cz>
 * @package Sift
 * @subpackage security
 */
class sfSecurity {

  /**
   * Default options for ip checker methods
   *
   * @var array
   */
  protected static $defaultIpCheckOptions = array(
    // The wildcard
    'wildcard' => '*',
    // IP address separator
    'separator' => '.',
    // Regex to match IP ranges
    'range_regex' => '/(\d-\d)/',
    // Detect IP range in CIDR format
    'cidr_regex' => '/^([^\/]+)\/([^\/]+)$/',
    // Range start and end characters
    'range_sentinels' => array('(', ')'),
    // Range delimiter
    'rangeDelimiter' => '-',
  );
  
  /**
   * Simple cache holder
   * 
   * @var array
   * @access protected
   */
  protected static $securityByModule = array();
  
  /**
   * Checks if given module and action is secure
   * 
   * @param string $module
   * @param string $action
   * @return boolean 
   */
  public static function isActionSecure($module, $action)
  {
    return self::getModuleSecurityValue($module, $action, 'is_secure', false);
  }
  
  /**
   * Returns credentials for given module and action
   * 
   * @param string $module
   * @param string $action
   * @return mixed array of credentials or null
   */
  public static function getActionCredentials($module, $action)
  {
    return self::getModuleSecurityValue($module, $action, 'credentials');
  }
  
  /**
   * Checks if given route is secure
   * 
   * @param string $route
   * @return mixed
   * @throw sfException in debug mode when route does not exist 
   */
  public static function isRouteSecure($route)
  {
    $parsed = self::getModuleActionFromRoute($route);
    if(!$parsed)
    {
      return null;
    }
    list($module, $action) = $parsed;
    return self::getModuleSecurityValue($module, $action, 'is_secure', false);
  }  
  
  /**
   * Returns credentials for given route
   * 
   * @param string $route
   * @return mixed array or null
   */
  public static function getRouteCredentials($route)
  {
    $parsed = self::getModuleActionFromRoute($route);
    if(!$parsed)
    {
      return null;
    }
    list($module, $action) = $parsed;
    return self::getModuleSecurityValue($module, $action, 'credentials');
  }

  /**
   * Returns credentials for given url
   * 
   * @param string $url
   * @return mixed array or null
   */
  public static function getUrlCredentials($url)
  {
    // non local urls are always unsecure
    if(!self::isLocalUrl($url))
    {
      return null;
    }
    
    $parsed = self::getModuleActionFromUrl($url);
    if(!$parsed)
    {
      return null;
    }
    list($module, $action) = $parsed;
    return self::getModuleSecurityValue($module, $action, 'credentials');    
  }
  
  /**
   * Checks if given url is secure
   * 
   * @param string $url
   * @return mixed array or null
   */
  public static function isUrlSecure($url)
  {
    // non local urls are always unsecure
    if(!self::isLocalUrl($url))
    {
      return false;
    }
    
    $parsed = self::getModuleActionFromUrl($url);
    if(!$parsed)
    {
      return null;
    }
    list($module, $action) = $parsed;
    return self::getModuleSecurityValue($module, $action, 'is_secure', false); 
  }
  
  /**
   * Checks if user is allowed to execute given action
   * 
   * @param sfBasicSecurityUser $user
   * @param string $module
   * @param string $action
   * @return boolean 
   */
  public static function isUserAllowedToExecuteAction(sfBasicSecurityUser $user, $module, $action)
  {
    $isAuthenticated = $user->isAuthenticated();
    $isSuperAdmin    = $isAuthenticated && $user->isSuperAdmin();
    
    if($isSuperAdmin || (!self::isActionSecure($module, $action)))
    {
      return true;
    }    
    if(($isAuthenticated) && ($user->hasCredential(self::getActionCredentials($module, $action))))
    {
      return true;
    }
    return false;
  }

  /**
   * Checks if user is allowed to execute given route
   * 
   * @param sfBasicSecurityUser $user
   * @param string $route
   * @return boolean 
   */
  public static function isUserAllowedToExecuteRoute(sfBasicSecurityUser $user, $route)
  {
    $isAuthenticated = $user->isAuthenticated();
    $isSuperAdmin    = $isAuthenticated && $user->isSuperAdmin();
    
    if($isSuperAdmin || (!self::isRouteSecure($route)))
    {
      return true;
    }    
    
    if(($isAuthenticated) && ($user->hasCredential(self::getRouteCredentials($route))))
    {
      return true;
    }
    
    return false;
  }
  
  /**
   * Checks if user is allowed to execute given url
   * 
   * @param sfBasicSecurityUser $user
   * @param string $url
   * @return boolean 
   */
  public static function isUserAllowedToExecuteUrl(sfBasicSecurityUser $user, $url)
  {
    // non local urls are always executable 
    if(!self::isLocalUrl($url))
    {
      return true;
    }  
    
    $isAuthenticated = $user->isAuthenticated();
    $isSuperAdmin    = $isAuthenticated && $user->isSuperAdmin();
    
    if($isSuperAdmin || (!self::isUrlSecure($url)))
    {
      return true;
    }    
    if(($isAuthenticated) && ($user->hasCredential(self::getUrlCredentials($url))))
    {
      return true;
    }
    return false;
  }
  
  /**
   * Returns security value for given module and action from security.yml
   * 
   * @param string $module
   * @param string $action
   * @param string $name Value name, ie. is_secure, credentials
   * @param mixed $default Default value to return back
   * @return mixed array or null 
   */
  protected static function getModuleSecurityValue($module, $action, $name, $default = null)
  {
    if(!isset(self::$securityByModule[$module]))
    {      
      $result = new sfSecurityCheckResult($module);      
      self::$securityByModule[$module] = $result->getSecurity();      
    }

    $action = strtolower($action);
    
    if(isset(self::$securityByModule[$module][$action][$name]))
    {
      return self::$securityByModule[$module][$action][$name];
    }
    else if(isset(self::$securityByModule[$module]['all'][$name]))
    {
      return self::$securityByModule[$module]['all'][$name];
    }
    return $default;
  }
  
  /**
   * Returns an array ($module, $action) parsed from the given route or false
   * when the route could not be parsed using sfRouting class. 
   * It throws and exception in debug mode.
   * 
   * @param string $route
   * @return array
   * @throw sfException Throws an exception in debug mode
   */
  protected static function getModuleActionFromRoute($route)
  {
    try 
    {
      $route = sfRouting::getInstance()->getRouteByName($route);
    }
    catch(sfException $e)
    {
      if(sfConfig::get('sf_debug'))
      {
        throw $e;
      }
      return false;
    }
    return array($route[4]['module'], $route[4]['action']);    
  }

  /**
   * Returns an array ($module, $action) parsed from the given url or false
   * 
   * @param string $url
   * @return mixed array of module, action or false 
   */
  protected static function getModuleActionFromUrl($url)
  {
    // do not mess with routing, simply copy routes to new object
    $r = new sfRouting();
    
    // disable logging so log is not polluted
    $oldSetting = sfConfig::get('sf_logging_enabled');
    sfConfig::set('sf_logging_enabled', false);
    
    $r->setRoutes(sfRouting::getInstance()->getRoutes());

    // get current path info array
    $pathInfo = sfContext::getInstance()->getRequest()->getPathInfoArray();
    
    $url = str_replace(array(
      'http://', 'https://', $pathInfo['HTTP_HOST'], $pathInfo['SCRIPT_NAME']
      ), '', $url);

    $route = $r->parse($url);
    
    sfConfig::set('sf_logging_enabled', $oldSetting);
    
    if(!is_null($route) && $route['module'] && $route['action'])
    {
      return array($route['module'], $route['action']);      
    }
    
    return false;
  }

  /**
   * Validates urls used for login redirects. Solves "open redirect"
   * phishing attacks to get users to visit malicious sites without realizing it.
   *
   * @param string $url
   * @return boolean 
   * 
   * @see http://cwe.mitre.org/data/definitions/601.html
   */
  public static function isRedirectUrlValid($url, $validDomains = array())
  {
    // url is empty
    if(empty($url))
    {
      return false;
    }

    $host = sfContext::getInstance()->getRequest()->getHost();
    $protocol = sfContext::getInstance()->getRequest()->getProtocol();
    $parsed = parse_url($url);

    if(is_array($parsed))
    {
      // valid are only common web schemas
      if(!isset($parsed['scheme']) || 
        !in_array($parsed['scheme'], array('http', 'https', 'ftp', 'ftps')))
      {
        return false;
      }

      // is listed in valid domains or base host is listed
      if(in_array($parsed['host'], $validDomains) ||
         in_array(self::getBaseDomain($parsed['host']), $validDomains))
      {
        return true;
      }

      // same domain is still valid
      if($parsed['host'] == $host ||
        // or same base domains
        self::getBaseDomain($parsed['host']) == self::getBaseDomain($host))
      {
        return true;
      }
    }

    return false;
  }
  
  /**
   * Returns base domain
   * 
   * @return string 
   */
  protected static function getBaseDomain($domain)
  {
    return sfToolkit::getBaseDomain($domain);
  }
  
  /**
   * Checks if given IP matched whitelisted IPs 
   * (whitelisted entries can be in wildcard format, specific IPs, or CIDR format)
   *
   * sfSecurity::isIpInWhitelist('10.0.0.1', array(
   *  '10.0.0.1',
   *  '10.*',
   *  '10.0.0.0/8'));
   * 
   * @param string $ip
   * @param array $whitelist
   * @param array $options
   * @return boolean
   */
  public static function isIpInWhitelist($ip, $whitelist = array(), 
          $options = array())
  {
    $options = sfToolkit::arrayDeepMerge(self::$defaultIpCheckOptions, $options);

    foreach($whitelist as $ipAddress)
    {
      if($ip == $ipAddress)
      {
        return true;
      }
      elseif(strpos($ipAddress, $options['wildcard']) !== false)
      {
        $wildcardIp = str_replace($options['wildcard'], '', $ipAddress);
        if(strpos($ip, $wildcardIp) === 0)
        {
          return true;
        }
      }
      elseif(preg_match($options['range_regex'], $ipAddress) == 1)
      {
        $exploded = explode($options['separator'], $ipAddress);
        $range = array_pop($exploded);
        $range = str_replace($options['range_sentinels'], '', $range);
        $ipStart = implode($options['separator'], $exploded);

        if(strpos($ip, $ipStart) === 0)
        {
          list($rangeStart, $rangeEnd) = explode($options['range_delimiter'], $range);
          for($i = $rangeStart; $i <= $rangeEnd; $i++)
          {
            $checkIp = implode($options['separator'], array($ipStart, $i));
            if($ip == $checkIp)
            {
              return true;
            }
          }
        }
      }
      elseif(preg_match($options['cidr_regex'], $ipAddress, $ms))
      {
        $mask = 0xFFFFFFFF << (32 - $ms[2]);
        return (ip2long($ip) & $mask) == (ip2long($ms[1]) & $mask);
      }
    }
    return false;
  }
  
  /**
   * Checks given IP adress for validity
   * (Private IPs are considered as invalid)
   *
   * @param string IP address
   * @return boolean
   */
  public static function isIpValid($ip)
  {
    $reserved_ips = array(
      array('0.0.0.0', '2.255.255.255'),
      array('10.0.0.0', '10.255.255.255'),
      array('127.0.0.0', '127.255.255.255'),
      array('169.254.0.0', '169.254.255.255'),
      array('172.16.0.0', '172.31.255.255'),
      array('192.0.2.0', '192.0.2.255'),
      array('192.168.0.0', '192.168.255.255'),
      array('255.255.255.0', '255.255.255.255')
    );
    $ipnum = ip2long($ip);
    if($ipnum !== false && (long2ip($ipnum) === $ip))
    {
      foreach($reserved_ips as $r)
      {
        $min = ip2long($r[0]);
        $max = ip2long($r[1]);
        if((ip2long($ip) >= $min) && (ip2long($ip) <= $max))
        {
          return false;
        }
      }
      return true;
    }
    return false;
  }
  
  /**
   * Utility method. Returns first IP address of string
   *
   * @return  string IP address
   */
  public static function getFirstIp($ips)
  {
    if(($pos = strpos($ips, ',')) != false)
    {
      return substr($ips, 0, $pos);
    }
    else
    {
      return $ips;
    }
  }
  
  /**
   * Returns true or false if given url is local
   * 
   * @param string $url
   * @return boolean 
   */
  protected static function isLocalUrl($url)
  {
    $parsedUrl = parse_url($url);
    if($parsedUrl)
    {
      if(isset($parsedUrl['host']))
      {
        $host = sfContext::getInstance()->getRequest()->getHost();
        if($parsedUrl['host'] == $host)
        {
          return true;
        }
      }
      // only path is set
      elseif(isset($parsedUrl['path']))
      {
        return true;
      }  
    }
    return false;
  }
  
}

