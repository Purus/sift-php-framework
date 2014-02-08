<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfRequestFiltersHolder provides a base class for managing parameters.
 *
 * Parameters, in this case, are used to extend classes with additional data
 * that requires no additional logic to manage.
 *
 * @package    Sift
 * @subpackage request
 */
class sfRequestFiltersHolder extends sfParameterHolder implements ArrayAccess, Countable {

  /**
   * @see sfParameterHolder
   */
  public function __construct($parameters = array(), $namespace = 'default')
  {
    $this->default_namespace = $namespace;

    if($parameters)
    {
      $this->add($parameters, $namespace);
    }
  }

  /**
   * OffsetSet ArrayAccess method
   *
   * @param string $key
   * @param mixed $value
   */
  public function offsetSet($key, $value)
  {
    $this->parameters[$this->default_namespace][$key] = $value;
  }

  /**
   * Returns key (ArrayAccess method)
   *
   * @param string $key
   * @return mixed
   * @throws Exception Throws an Exception if there is no such key
   */
  public function offsetGet($key)
  {
    $this->checkKey($key, true);
    return $this->parameters[$this->default_namespace][$key];
  }

  /**
   * Checks if key does exist (ArrayAccess method)
   *
   * @param string $key
   */
  public function offsetExists($key)
  {
    return $this->checkKey($key);
  }

  /**
   * Removes key (ArrayAccess method)
   *
   * @param string $key
   * @throws Exception Throws Exception is there is not such key
   */
  public function offsetUnset($key)
  {
    $this->checkKey($key, true);
    unset($this->parameters[$this->default_namespace][$key]);
  }

  /**
   * Returns number of filters in this holder
   *
   * @return integer
   */
  public function count()
  {
    return count($this->parameters);
  }

  /**
   * Checks if given key exists
   *
   * @param string $key
   * @param bool $exception Should exception be thrown or return false?
   */
  private function checkKey($key, $exception = false)
  {
    if(!isset($this->parameters[$this->default_namespace][$key]))
    {
      if($exception)
      {
        throw new Exception(sprintf('Item index "%s" does not exist!', $key));
      }
      else
      {
        return false;
      }
    }
    return true;
  }

}
