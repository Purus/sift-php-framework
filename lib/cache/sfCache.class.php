<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCache is an abstract class for all cache classes
 *
 * @package Sift
 * @subpackage cache
 */
abstract class sfCache extends sfConfigurable implements sfICache
{

  /**
   * Array of default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    'lifetime' => 86400,
    'automatic_cleaning_factor' => 500
  );

  /**
   * Valid options for the cache
   *
   * @var array
   */
  protected $validOptions = array(
    'lifetime', 'automatic_cleaning_factor', 'prefix'
  );

  /**
   * Cache factory
   *
   * @param string $driver
   * @param array $driverOptions
   * @return sfCache
   * @throws LogicException
   */
  public static function factory($driver, $driverOptions = array())
  {
    $driverClass = sprintf('sf%sCache', ucfirst($driver));
    $driverObj = false;
    if(class_exists($driverClass))
    {
      $driverObj = new $driverClass($driverOptions);
    }
    elseif(class_exists(($driverClass = $driver)))
    {
      $driverObj = new $driverClass($driverOptions);
    }

    if(!$driverObj)
    {
      throw new LogicException(sprintf('Invalid cache driver "%s" (class: %s) given.', $driver, $driverClass));
    }
    elseif(!$driverObj instanceof sfICache)
    {
      throw new LogicException(sprintf('Cache driver "%s" (class: %s) does not implement sfICache.', $driver, $driverClass));
    }

    return $driverObj;
  }

  /**
   * Constructor
   *
   * @param array $options
   */
  public function __construct($options = array())
  {
    if(is_object($options))
    {
      if(is_callable(array($options, 'toArray')))
      {
        $options = $options->toArray();
      }
      else
      {
        throw new InvalidArgumentException(sprintf('Options for "%s" must be an array or a object with ->toArray() method', get_class($this)));
      }
    }

    $options = array_merge(array(
      'prefix' => md5(dirname(__FILE__)),
    ), $options);

    parent::__construct($options);
  }

  /**
   * Returns refresh time for given lifetime
   *
   * @param integer $lifetime life time (in seconds)
   * @return integer
   */
  public function getRefreshTime($lifetime)
  {
    return time() - $lifetime;
  }

  /**
   * Computes lifetime.
   *
   * @param integer $lifetime Lifetime in seconds
   *
   * @return integer Lifetime in seconds
   */
  public function getLifetime($lifetime)
  {
    return null === $lifetime ? $this->getOption('lifetime') : $lifetime;
  }

  /**
   * @see sfICache
   */
  public function getMany($keys)
  {
    $data = array();
    foreach($keys as $key)
    {
      $data[$key] = $this->get($key);
    }

    return $data;
  }

  /**
   * @see sfICache
   */
  public function getCacheBackend()
  {
  }

  /**
   * Converts a pattern to a regular expression.
   *
   * A pattern can use some special characters:
   *
   *  - * Matches a namespace (foo:*:bar)
   *  - ** Matches one or more namespaces (foo:**:bar)
   *
   * @param string $pattern A pattern
   * @return string A regular expression
   */
  protected function patternToRegexp($pattern)
  {
    $regexp = str_replace(
      array('\\*\\*', '\\*'),
      array('.+?',    '[^'.preg_quote(sfICache::SEPARATOR, '#').']+'),
      preg_quote($pattern, '#')
    );

    return '#^'.$regexp.'$#';
  }

}
