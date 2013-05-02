<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * sfSanitizer sanitizes string using HTML Purifier
 *
 * @package    Sift
 * @subpackage security
 */
class sfSanitizer {

  // The following constants allow for nice looking callbacks to static methods
  const sanitize = 'sfSanitizer::sanitize';

  /**
   * @var  HTMLPurifier  singleton instances of the HTML Purifier object
   */
  protected static $htmlpurifier = array();

  /**
   * Returns the singleton instance of HTML Purifier. If no instance has
   * been created, a new instance will be created. Configuration options
   * for HTML Purifier can be set in `APPPATH/config/purifier.php` in the
   * "settings" key.
   *
   * $purifier = sfSanitizer::getHtmlPurifier();
   *
   * @return  HTMLPurifier
   */
  public static function getHtmlPurifier($type = 'strict')
  {
    if(!isset(self::$htmlpurifier[$type]))
    {
      // Load the all of HTML Purifier right now.
      // This increases performance with a slight hit to memory usage.
      require_once sfConfig::get('sf_sift_lib_dir') . '/vendor/htmlpurifier/HTMLPurifier.includes.php';

      // Load the HTML Purifier auto loader
      require_once sfConfig::get('sf_sift_lib_dir') . '/vendor/htmlpurifier/HTMLPurifier.auto.php';

      // Create a new configuration object
      $config = HTMLPurifier_Config::createDefault();

      // load from yaml based on the type
      $settings = include sfConfigCache::getInstance()->checkConfig('config/sanitize.yml');

      if(!isset($settings[$type]))
      {
        throw new sfConfigurationException(sprintf('{sfSanitizer} HTMLPurifier configuration for type "%s" is missing in your sanitize.yml file.', $type));
      }

      // Load the settings
      $config->loadArray($settings[$type]);

      // Create the purifier instance
      self::$htmlpurifier[$type] = new HTMLPurifier($config);
    }

    return self::$htmlpurifier[$type];
  }

  /**
   * Sanitizes the $string using $type configuration configured in
   * sanitize.yml
   * 
   * @param string|array $string
   * @param string $type
   * @return string
   */
  public static function sanitize($string, $type = 'strict')
  {
    return self::xssClean($string, $type);
  }

  /**
   * Removes broken HTML and XSS from text using [HTMLPurifier](http://htmlpurifier.org/).
   *
   * $text = sfSanitizer::xssClean('message');
   *
   * The original content is returned with all broken HTML and XSS removed.
   *
   * @param   mixed   text to clean, or an array to clean recursively
   * @return  mixed
   */
  public static function xssClean($str, $type = 'strict')
  {
    if(is_array($str))
    {
      foreach($str as $i => $s)
      {
        // Recursively clean arrays
        $str[$i] = self::xssClean($s, $type);
      }
      return $str;
    }
    // Load HTML Purifier
    $purifier = self::getHtmlPurifier($type);
    // Clean the HTML and return it
    return $purifier->purify($str);
  }

  /**
   * __callstatic only on PHP 5.3+
   *
   * Provides methods like: sfSanitizer::strict('string') which is the same as
   * sfSanitizer::sanitize('string', 'strict');
   *
   * @param string $m method
   * @param string $a arguments
   */
  static function __callstatic($m, $a)
  {
    array_push($a, $m);
    return call_user_func_array(array('sfSanitizer', 'sanitize'), $a);
  }

}
