<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * Provides utility methods for Google Analytics
 * 
 * @package Sift
 * @subpackage analytics
 */
class sfGoogleAnalytics {

  const SCOPE_VISITOR_LEVEL = 1;
  const SCOPE_SESSION_LEVEL = 2;
  const SCOPE_PAGE_LEVEL = 3;
  
  const STORAGE_NAMESPACE = 'google_analytics';
  
  /**
   * Where in the network is the entry point for the tracking?
   * 
   * @var string
   */
  const GIF_LOCATION = 'http://www.google-analytics.com/__utm.gif';

  const COOKIE_NAME  = '__utma';
  
  const MOBILE_COOKIE_NAME = '__utmmobile';
  
  /**
   * Sets a custom variable with the supplied name, value,
   * and scope for the variable.
   * There is a 64-byte character limit for the name and value combined.
   *
   * @param string $name Required. The name for the custom variable.
   * @param string $value Required. The value for the custom variable.
   * @param string $scope Optional. The scope used for the custom variable.
   *                      Possible values are 1 for visitor-level,
   *                      2 for sesson-level, and 3  for page-level.
   * @param integer $slot Required. The slot used for the custom variable. Possible values are 1-5, inclusive.
   * @see http://code.google.com/intl/cs/apis/analytics/docs/gaJS/gaJSApiBasicConfiguration.html#_gat.GA_Tracker_._setCustomVar
   */
  public static function setTrackingVariable($name, $value, $scope = null, $slot = 1)
  {
    $response  = sfContext::getInstance()->getResponse();
    $variables = $response->getParameter('tracking_variables', array(), self::STORAGE_NAMESPACE);
    
    if(strlen($name.$value) > 64
        && sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->warning(sprintf('{sfGoogleAnalytics} Tracking variable "%s" with value "" exceeded limit.', $name, $value));
    }

    $i = count($variables)-1;

    $variables[$i] = array(
      'name'  => $name,
      'value' => $value,
      'slot'  => $slot,
    );

    if(!is_null($scope))
    {
      $variables[$i]['scope'] = $scope;
    }
    
    $response->setParameter('tracking_variables', $variables, self::STORAGE_NAMESPACE);
  }
    
  /**
   * Remotelly tracks the page view. 
   * 
   * @param string $documentPath
   * @param string $referer
   * @param string $ua User account ID
   * @param boolean $mobile Track mobile access?
   */
  public static function trackPageViewRemote($documentPath, $referer = '', $ua = null, 
          $mobile = false)
  {
    if(is_null($ua))
    {
      $ua = sfConfig::get('app_google_analytics_ua');
    }
    
    // we have mobile request, check if UA is mobile
    if($mobile)
    {
      // UA-31116947-1 -> MO-31116947-1
      $ua = preg_replace('/^(UA)+/', 'MO', $ua);
    }
    
    $request    = sfContext::getInstance()->getRequest();
    $response   = sfContext::getInstance()->getResponse();
    
    $domainName = str_replace('m.', '.', $request->getHttpHeader('server_name', ''));
    
    $timestamp = time();
    $referer   = $request->getReferer() ? $request->getReferer() : '-';
    $userAgent = $request->getUserAgent();
    $ip        = $request->getIp();
    
    $guidHeader = '';
    foreach(array('x_dcmguid', 'x_up_subno', 'x_jphone_uid', 'x_um_uid') as $header)
    {
      $value = $request->getHttpHeader($header);
      if($value)
      {
        $guidHeader = $value;
        break;
      }
    }

    // Try and get visitor cookie from the request.
    $cookie = $request->getCookie($mobile ? self::MOBILE_COOKIE_NAME : self::COOKIE_NAME);
    
    if(sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->err('Cookie: '. var_export($cookie, true));
    }
    
    $visitorId = self::getVisitorId($guidHeader, $ua, $userAgent, $cookie);

    if(sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->err('Visitor ID: '. $visitorId);
    }
    
    if($mobile)
    {
      // Always try and add the cookie to the response.
      setrawcookie(
          self::MOBILE_COOKIE_NAME,
          $visitorId,
          $timestamp + 63072000,
        '/', $domainName);
    }
    
    $params = array(
      'utmwv' => '4.4sh',
      'utmn'  => self::getRandomNumber(),
      'utmhn' => $domainName,
      'utmr'  => $referer,  
      'utmp'  => $documentPath,
      'utmac' => $ua,
      'utmcc' => '__utma=999.999.999.999.999.1;', // what is this?
      'utmvid' => $visitorId,
      'utmip'  => $ip
    );
    
    $headers = array(
      'User-Agent' => $userAgent,
      'Accept-Language' => $request->getHttpHeader('accept_language')        
    );
    
    $browser = self::sendRequest(self::GIF_LOCATION, $params, $headers);

    return !$browser->responseIsError();
  }
  
  public static function sendRequest($uri, $params = array(), $headers = array())
  {
    $browser = new sfWebBrowser();
    
    if($params)
    {
      $uri .= ((false !== strpos($uri, '?')) ? '&' : '?') . http_build_query($params, '', '&'); 
    }
    
    if(sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->err('Calling uri: '. $uri); 
    }
    
    return $browser->get($uri, array(), $headers);
  }
  
  /**
   * Generate a visitor id for this hit.
   * If there is a visitor id in the cookie, use that, otherwise
   * use the guid if we have one, otherwise use a random number.
   * 
   * @param string $guid
   * @param string $account
   * @param string $userAgent
   * @param string $cookie
   * @return string 
   */
  public static function getVisitorId($guid, $account, $userAgent, $cookie)
  {
    // If there is a value in the cookie, don't change it.
    if(!empty($cookie))
    {
      return $cookie;
    }

    $message = '';
    if(!empty($guid))
    {
      // Create the visitor id using the guid.
      $message = $guid . $account;
    }
    else
    {
      // otherwise this is a new user, create a new random id.
      $message = $userAgent . uniqid(self::getRandomNumber(), true);
    }

    $md5String = md5($message);
    return "0x" . substr($md5String, 0, 16);
  }  
  
  /**
   * Get a random number string.
   * @return string
   */
  private static function getRandomNumber() 
  {
    return rand(0, 0x7fffffff);
  }
  
  /**
   * Adds Custom Campaign parameters to your URLs. 
   * 
   * @param string $url
   * @param string $campaignSource Use to identify a search engine, newsletter name, or other source. 
   * @param string $campaignMedium Use to identify a medium such as email or cost-per- click. 
   * @param string $campaignName Use to identify a specific product promotion or strategic campaign. 
   * @return string
   * @link http://support.google.com/analytics/bin/answer.py?hl=en&answer=1033867
   */
  public static function tagUrl($url, $campaignSource, $campaignMedium, $campaignName)
  {
    $analyticsOptions = sfConfig::get('app_google_analytics_options', array());
    
    $trackParams = htmlspecialchars(
            sprintf('utm_source=%s&utm_medium=%s&utm_campaign=%s', 
                    $campaignSource, 
                    $campaignMedium, 
                    $campaignName));
    
    if(isset($analyticsOptions['allow_anchor']) 
        && $analyticsOptions['allow_anchor'])
    {
      $trackParams = (strpos($url, '#') === false) ? ('#' . $trackParams)
                      : ('&' . $trackParams);
    }
    else
    {      
      $trackParams = (strpos($url, '?') === false) ? ('?' . $trackParams) 
                      : ('&' . $trackParams);
    }
    
    return $url . $trackParams;    
  }
  
}