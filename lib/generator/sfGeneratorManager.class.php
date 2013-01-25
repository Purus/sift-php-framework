<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfGeneratorManager helps generate classes, views and templates for scaffolding, admin interface, ...
 *
 * @package    Sift
 * @subpackage generator
 */
class sfGeneratorManager
{
  protected $cache = null;

  /**
   * Initializes the sfGeneratorManager instance.
   */
  public function initialize()
  {
    // create cache instance
    $this->cache = new sfFileCache(sfConfig::get('sf_module_cache_dir'));
    $this->cache->initialize(array('lifetime' => 86400 * 365 * 10, 'automatic_cleaning_factor' => 0));
    $this->cache->setSuffix('');
  }

  /**
   * Returns the current sfCache implementation instance.
   *
   * @return sfCache A sfCache implementation instance
   */
  public function getCache()
  {
    return $this->cache;
  }

  /**
   * Generates classes and templates for a given generator class.
   *
   * @param string The generator class name
   * @param array  An array of parameters
   *
   * @return string The cache for the configuration file
   */
  public function generate($generator_class, $param)
  {
    $generator = new $generator_class();
    $generator->initialize($this);
    $data = $generator->generate($param);

    return $data;
  }
}
