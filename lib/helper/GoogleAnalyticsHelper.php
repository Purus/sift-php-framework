<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Google Analytics helper
 *
 * @package    Sift
 * @subpackage helper_analytics
 */

/**
 * Returns Google Analytics tracking code
 *
 * @param array $options Array of options
 * @return string
 */
function google_analytics_tracking_code($options = array())
{
  $options  = _parse_attributes($options);

  $response = sfContext::getInstance()->getResponse();
  $request  = sfContext::getInstance()->getRequest();

  if($response->getParameter('tracking_included', false, 'google_analytics'))
  {
    return '';
  }

  // exclude from google analytics cookie is set
  // FIXME: make this configurable
  if($request->getCookie('__ga_exclude'))
  {
    return '';
  }

  $result = array();
  $result[] = google_analytics_base_configuration();

  $js = array();
  // detect 404 page
  $module = sfContext::getInstance()->getModuleName();
  $action = sfContext::getInstance()->getActionName();
  if($module == sfConfig::get('sf_error_404_module')
     && $action ==  sfConfig::get('sf_error_404_action'))
  {
    $js[] = "_gaq.push(['_trackPageview', '/error404/?page=' + document.location.pathname + document.location.search + '&from=' + document.referrer]);";
  }
  else
  {
    $js[] = "_gaq.push(['_trackPageview']);";
  }

  $variables = $response->getParameter('tracking_variables', array(), 'google_analytics');
  foreach($variables as $name => $value)
  {
    $slot  = 1;
    $scope = 1; // the scope to visitor-level, 2 => page level
    $v     = $value;
    if(is_array($value))
    {
      $slot  = isset($value['slot']) ? (integer)$value['slot'] : 1;
      $name  = isset($value['name']) ? escape_javascript((string)$value['name']) : (string)$name;
      if(isset($value['value']))
      {
        $v = escape_javascript((string)$value['value']);
      }
      if(isset($value['scope']))
      {
        $scope = (integer)$value['scope'];
      }
    }
    $js[] = sprintf("_gaq.push(['_setCustomVar', %s, '%s', '%s', %s]);", $slot, $name, $v, $scope);
  }

  if(sfConfig::get('sf_debug'))
  {
    $js[] = '// commented out because running in debug mode!';
    $js[] = '/*';
  }

  $js[] = google_analytics_include_remote_javascript();

  if(sfConfig::get('sf_debug'))
  {
    $js[] = '*/';
  }

  $result[] = javascript_tag(join("\n", $js));

  return join("\n", $result). "\n";
}

/**
 * Returns base google analytics configuration
 *
 * @param array $options
 * @return string
 */
function google_analytics_base_configuration($options = array())
{
  $options = _parse_attributes($options);
  $options = array_merge(sfConfig::get('app_google_analytics_options', array()), $options);

  $js   = array();
  $js[] = "var _gaq = _gaq || [];";
  $js[] = sprintf("_gaq.push(['_setAccount', '%s']);", google_analytics_ua());

  if($allow_anchor = _get_option($options, 'allow_anchor'))
  {
    $js[] = "_gaq.push(['_setAllowAnchor', true]);";
  }

  if($domain = _get_option($options, 'domain_name'))
  {
    $js[] = sprintf("_gaq.push(['_setDomainName', '%s']);", $domain);
  }

  if($track_ip = _get_option($options, 'track_ip'))
  {
    $user = sfContext::getInstance()->getUser();
    $js[] = sprintf("_gaq.push(['_setCustomVar', 1, 'IP', '%s', 1]);", $user->getRealIp());
  }

  if($track_load_time = _get_option($options, 'track_load_time'))
  {
    $js[] = sprintf("_gaq.push(['_trackPageLoadTime']);");
  }

  // add tracking of links to the response
  use_package('google_analytics_tracker');

  $setupScripts = sfAssetPackage::getJavascripts('google_analytics_setup');

  $return = javascript_tag(join("\n", $js));

  foreach($setupScripts as $script)
  {
    $return .= "\n" . javascript_include_tag($script);
  }

  return $return;
}

function google_analytics_include_remote_javascript($src = null)
{
  $secure = sfContext::getInstance()->getRequest()->isSecure();

  // default src of ga javascript
  if(is_null($src))
  {
    if($secure)
    {
      $src = 'https://ssl.google-analytics.com/ga.js';
    }
    else
    {
      $src = 'http://www.google-analytics.com/ga.js';
    }
  }

  return sprintf("
(function() {
  var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true; ga.src = '%s';
  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();", escape_javascript($src));

}

/**
 * Sets a custom variable with the supplied name, value,
 * and scope for the variable.
 * There is a 64-byte character limit for the name and value combined.
 *
 * @param string $name Required. The name for the custom variable.
 * @param string $value Required. The value for the custom variable.
 * @param integer $scope Optional. The scope used for the custom variable.
 *                      Possible values are 1 for visitor-level,
 *                      2 for sesson-level, and 3  for page-level.
 * @param integer $slot Required. The slot used for the custom variable. Possible values are 1-5, inclusive.
 * @see http://code.google.com/intl/cs/apis/analytics/docs/gaJS/gaJSApiBasicConfiguration.html#_gat.GA_Tracker_._setCustomVar
 */
function google_analytics_set_tracking_variable($name, $value, $scope = null, $slot = 1)
{
  return sfGoogleAnalytics::setTrackingVariable($name, $value, $scope, $slot);
}

/**
 * Returns Google Analytics configured UA
 *
 * @return string
 */
function google_analytics_ua()
{
  $analytics_ua = sfConfig::get('app_google_analytics_ua');
  if(!$analytics_ua)
  {
    sfLogger::getInstance()->error('{GoogleAnalyticsHelper} Google Analytics is not configured properly. Missing "app_google_analytics_ua" configuration');
  }

  return $analytics_ua;
}

/**
 * Tags links for google analytics
 *
 * @param string $text
 * @param array $options
 * @return string
 */
function google_analytics_tag_links($text, $options = array())
{
  // get base options
  $options = _parse_attributes($options);
  $gaOptions = sfConfig::get('app_google_analytics_options', array());

  // utm_source
  if(!isset($options['source']))
  {
    $options['source'] = 'email';
  }

  if(!isset($options['medium']))
  {
    $options['medium'] = 'email';
  }

  if(!isset($options['campaign']))
  {
    $options['campaign'] = 'email';
  }

  $ga_options = array(
    'utm_source'    => $options['source'],
    'utm_medium'    => $options['medium'],
    'utm_campaign'  => $options['campaign']
  );

  $anchor = false;
  // urls should be rewritten to use anchor "#"
  if(isset($gaOptions['allow_anchor'])
    && $gaOptions['allow_anchor'])
  {
    $anchor = true;
  }

  $ga_options = http_build_query($ga_options, null, '&');

  if(isset($options['auto_link']) && $options['auto_link'])
  {
    $text = sfText::autoLink($text);
  }

  $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

  // Check if there is a url in the text
  if(preg_match($reg_exUrl, $text, $url))
  {
    if($anchor)
    {
      $url = ($url[0].'#'.$ga_options);
    }
    else
    {
      $url = strpos($url[0], '?') === false ?
              ($url[0].'?'.$ga_options) :($url[0].'&'.$ga_options);
    }
    // make the urls hyper links
    $text = preg_replace($reg_exUrl, $url, $text);
  }

  return $text;
}
