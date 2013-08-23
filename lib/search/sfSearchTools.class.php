<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * File containing the sfSearchTools class.
 *
 * @package Sift
 * @subpackage search 
 */
class sfSearchTools {

  /**
   * Configuration array holder
   *    
   * @var array
   * @see getConfig
   */
  static $yamlConfig;

  /**
   * Highlightes given words within text
   * 
   * @param string $text Text which contains the $expression
   * @param sfSearchQueryExpression $expression
   * @link http://cz.php.net/manual/en/function.str-ireplace.php#87417
   * @return string
   */  
  public static function highlight($text, sfSearchQueryExpression $expression)
  {
    $keywords = $expression->collectWords();
    $phrases = array();
    foreach($keywords as $k)
    {
      // strip wildcards
      $k = str_replace(array('*'), '', $k);
      // skip empty values
      if(empty($k)) 
      {
        continue;
      }
      $phrases[] = trim($k);
    }
    return count($phrases) ? sfText::highlight($text, $phrases, '<span class="search-highlighted">\1</span>') : $text;
  }
  
  /**
   * Keeps an optional HTTP scheme and the domain name, plus 8 characters after the domain name. 
   * Also keeps the last 8 characters. 
   * Only trims the URL if at least 4 characters are to be removed.
   * 
   * @param string $url
   * @return string 
   */
  public static function truncateUrl($url)
  {
    return preg_replace(
      '#^ (?>((?:.*:/+)?[^/]+/.{8})) .{4,} (.{8}) $#x',
      '$1...$2',
      $url
    );    
  }
  
  /**
   * Returns relevancy signed with percentage sign (%)
   * 
   * @param float $relevancy
   * @return string
   */  
  public static function formatRelevancy($relevancy)
  {
    $relevancy = (round($relevancy, 3) * 100);
    if($relevancy > 100)
    {
      $relevancy = 100;
    }
    return $relevancy.'%';    
  }
  
  /**
   * Encodes query parameter
   *
   * @param string $q
   * @return string
   */
  public static function encodeSearchString($q)
  {
    $q = explode(' ', trim(strip_tags($q)));
    $q = array_map('trim', $q);
    
    $tmp = array();
    foreach($q as $_q)
    {
      if(empty($_q))
        continue;
      $tmp[] = $_q;
    }
    $q = join(' ', $tmp);
    return trim(str_replace(array('/', '?', '.'), array('', urlencode('%3F'), urlencode('%2E')), $q));
  }

  /**
   *
   * @return sfSearchQueryParser 
   */
  public static function getSearchQueryParser()
  {
    // FIXME: make this configurable via config    
    return new sfSearchQueryParser(new sfSearchQueryLexer());
  }

  /**
   * Decodes query parameter
   *
   * @param string $q
   * @return string
   */
  public static function decodeSearchString($q)
  {
    return urldecode(urldecode($q));
  }

  /**
   * Escapes search string
   * 
   * @param string $string
   * @return string
   */
  public static function escapeSearchString($string)
  {
    return trim(strip_tags(str_replace(array('/', '?', '&'), '', $string)));
  }

  /**
   * Generates the Url
   * 
   * @param string $internalUrl
   * @param boolean $absolute
   */
  public static function generateUrl($internal_uri, $absolute = false)
  {
    static $controller;
    if(!isset($controller))
    {
      $controller = sfContext::getInstance()->getController();
    }
    return $controller->genUrl($internal_uri, $absolute);
  }

  /**
   * Is given source valid?
   * 
   * @param string $source
   * @return boolean
   */
  public static function isValidSource($source)
  {
    return true;
  }

  /**
   * Loads config from search_sources.yml configuration file
   * 
   * @return array
   */
  public static function getConfig()
  {
    if(!(self::$yamlConfig))
    {
      self::$yamlConfig = include sfConfigCache::getInstance()->checkConfig('config/search_sources.yml');
    }
    return self::$yamlConfig;
  }

  public static function getMaxPerPageLimit($source = null)
  {
    $maxPerPage = sfConfig::get('app_search_max_per_page', 10);

    // we try to get per source setting
    if($source && $source instanceof mySearchSource)
    {
      $maxPerPage = $source->getMaxPerPage();
    }

    return $maxPerPage;
  }

  public static function sortSources($a, $b)
  {
    $priorityA = $a->getPriority();    
    $priorityB = $b->getPriority();
    
    if($priorityA == $priorityB)
    {
      return 0;
    }
    
    return $priorityA < $priorityB ? -1 : 1;
  }

  /**
   * Returns a collection of search sources
   * 
   * @param sfUser $user User
   * @return mySearchSourceCollection
   * @throws sfException
   */
  public static function getSearchSources(sfUser $user = null)
  {
    $config = self::getConfig();
    $return = array();

    $i = 0;
    foreach($config['sources'] as $source => $params)
    {
      if(is_integer($source))
      {
        $name = $params;
      }
      else
      {
        $name = $source;
      }
      
      // skip disabled
      if(isset($params['enabled']) && !$params['enabled'])
      {
        continue;
      }
      
      if(isset($params['class']))
      {
        $class = $params['class'];
        unset($params['class']);
      }
      else
      {
        $class = sprintf('mySearchSource%s', ucfirst(sfInflector::classify($name)));
      }

      $options = array_merge(array(
          'culture' => sfContext::getInstance()->getUser()->getCulture()
              ), ($params) ? $params : array());
      
      if(!class_exists($class))
      {
        throw new sfException(sprintf('{sfSearchTools} Search source class "%s" does not exist. Try clearing cache.', $class));
      }

      $source = new $class($options);
      
      if(!$source instanceof sfISearchSource)
      {
        throw new sfException(sprintf('Invalid search source class "%s". Class has to implement sfISearchSource interface!', $class));
      }      
      
      // we make the check here, after creating of the source,
      // since source can get its isSecure setting from some other source
      // check against secure and credentials
      if($user)
      {
        // skip secure source! user has to be logged in
        if($source->isSecure() && !$user->isLoggedIn())
        {
          continue;
        }
        
        $credentials = $source->getCredentials();        
        // source requires credentials!
        // does the user have them?
        if($credentials && !$user->hasCredential($credentials))
        {
          continue;
        }
      }    
      
      // all passed, source is ok!
      $return[] = $source;
    }

    // sort sources
    uasort($return, array('sfSearchTools', 'sortSources'));

    return new sfSearchSourceCollection($return);
  }

  public static function getOpenSearchConfig()
  {
    $config = sfConfig::get('app_search_open_search');

    foreach($config as $name => $value)
    {
      $config[$name] = str_replace(
              array('%SERVER_NAME%'), array(ucfirst(sfContext::getInstance()->getRequest()->getHost())), $value);
    }

    return $config;
  }

}