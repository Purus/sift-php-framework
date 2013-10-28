<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Load the all of HTML Purifier right now.
// This increases performance with a slight hit to memory usage.
require_once dirname(__FILE__) . '/../vendor/htmlpurifier/HTMLPurifier.includes.php';

// Load the HTML Purifier auto loader
spl_autoload_register(array('HTMLPurifier_Bootstrap', 'autoload'));

/**
 * Extensions to Html purifier library
 *
 * @package Sift
 * @subpackage security
 */
class sfHtmlPurifier extends HTMLPurifier {

  /**
   * Array of instances
   *
   * @var array
   */
  protected static $instances = array();

  /**
   * Constructor
   * @param string $type The purifier type (strict, word...)
   * @throws sfConfigurationException If the configuration for given type is missing
   */
  public function __construct($type = 'strict')
  {
    // Create a new configuration object
    $config = HTMLPurifier_Config::createDefault();
    $settings = $this->loadSettings($type);
    if(!isset($settings[$type]))
    {
      throw new sfConfigurationException(sprintf('HTMLPurifier configuration for type "%s" is missing in your sanitize.yml file.', $type));
    }
    $config->loadArray($settings[$type]);
    parent::__construct($config);
  }

  /**
   * Loads settings from sanitize.yml file
   *
   * @return array
   */
  protected function loadSettings()
  {
    return include sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_config_dir_name').'/sanitize.yml');
  }

  /**
   * Filters an HTML snippet/document to be XSS-free and standards-compliant.
   *
   * @param string|array $html The HTML to purify
   * @param HTMLPurifier_Config $config object for this operation, if omitted,
   *                defaults to the config object specified during this
   *                object's construction. The parameter can also be any type
   *                that HTMLPurifier_Config::create() supports.
   * @return string|array Purified HTML
   */
  public function purify($html, $config = null)
  {
    if(sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
    {
      $timer = sfTimerManager::getTimer('html_purifier');
    }

    if(is_array($html))
    {
      $result = $this->purifyArray($html, $config);
    }
    else
    {
      $result = parent::purify($html, $config);
    }

    if(sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
    {
      $timer->addTime();
    }

    return $result;
  }

  /**
   * Singleton for enforcing just one HTML Purifier in your system for given type
   *
   * @param string $type The purifier type (strict, word...)
   */
  public static function instance($type = null)
  {
    if(!isset(self::$instances[$type]))
    {
      self::$instances[$type] = new sfHtmlPurifier($type);
    }
    return self::$instances[$type];
  }

  /**
   * Reset singleton instances of the purifier
   *
   */
  public static function reset()
  {
    self::$instances = array();
  }

}