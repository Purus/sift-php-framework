<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDatabase is a base abstraction class that allows you to setup any type of
 * database connection via a configuration file.
 *
 * @package    Sift
 * @subpackage database
 */
abstract class sfDatabase implements sfIService {

  protected
    $connection = null,
    $parameterHolder = null,
    $resource = null;

  /**
   * Constructor
   *
   * @param array $parameters
   */
  public function __construct($parameters = array())
  {
    $this->parameterHolder = new sfParameterHolder();
    $this->initialize($parameters);
  }

  /**
   * Connects to the database.
   *
   * @throws sfDatabaseException If a connection could not be created
   */
  abstract function connect();

  /**
   * Retrieves the database connection associated with this sfDatabase implementation.
   *
   * When this is executed on a Database implementation that isn't an
   * abstraction layer, a copy of the resource will be returned.
   *
   * @return mixed A database connection
   *
   * @throws sfDatabaseException If a connection could not be retrieved
   */
  public function getConnection()
  {
    if($this->connection == null)
    {
      $this->connect();
    }

    return $this->connection;
  }

  /**
   * Retrieves a raw database resource associated with this sfDatabase implementation.
   *
   * @return mixed A database resource
   *
   * @throws sfDatabaseException If a resource could not be retrieved
   */
  public function getResource()
  {
    if($this->resource == null)
    {
      $this->connect();
    }

    return $this->resource;
  }

  /**
   * Initializes this sfDatabase object.
   *
   * @param array An associative array of initialization parameters
   *
   * @return bool true, if initialization completes successfully, otherwise false
   *
   * @throws sfInitializationException If an error occurs while initializing this sfDatabase object
   */
  public function initialize($parameters = array())
  {
    foreach($parameters as $p => &$value)
    {
      $value = $this->replaceEnvironmentVariables($value);
    }

    if(!$this->parameterHolder)
    {
      $this->parameterHolder = new sfParameterHolder();
    }

    $this->parameterHolder->clear();
    $this->parameterHolder->add($parameters);
  }

  /**
   * Gets the parameter holder for this object.
   *
   * @return sfParameterHolder A sfParameterHolder instance
   */
  public function getParameterHolder()
  {
    return $this->parameterHolder;
  }

  /**
   * Gets the parameter associated with the given key.
   *
   * This is a shortcut for:
   *
   * <code>$this->getParameterHolder()->get()</code>
   *
   * @param string The key name
   * @param string The default value
   * @param string The namespace to use
   *
   * @return string The value associated with the key
   *
   * @see sfParameterHolder
   */
  public function getParameter($name, $default = null, $ns = null)
  {
    return $this->parameterHolder->get($name, $default, $ns);
  }

  /**
   * Returns true if the given key exists in the parameter holder.
   *
   * This is a shortcut for:
   *
   * <code>$this->getParameterHolder()->has()</code>
   *
   * @param string The key name
   * @param string The namespace to use
   *
   * @return boolean true if the given key exists, false otherwise
   *
   * @see sfParameterHolder
   */
  public function hasParameter($name, $ns = null)
  {
    return $this->parameterHolder->has($name, $ns);
  }

  /**
   * Sets the value for the given key.
   *
   * This is a shortcut for:
   *
   * <code>$this->getParameterHolder()->set()</code>
   *
   * @param string The key name
   * @param string The value
   * @param string The namespace to use
   *
   * @see sfParameterHolder
   */
  public function setParameter($name, $value, $ns = null)
  {
    $this->parameterHolder->set($name, $value, $ns);
  }

  /**
   * Replaces environment identifiers in a value.
   *
   * If the value is an array replacements are made recursively.
   *
   * @param mixed The value on which to run the replacement procedure
   *
   * @return string The new value
   */
  protected function replaceEnvironmentVariables(&$value)
  {
    if(is_array($value))
    {
      array_walk_recursive($value, array($this, 'replaceEnvironmentVariablesCallback'));
    }
    elseif(is_string($value))
    {
      $value = preg_replace_callback('/%ENV_(.+?)%/', array($this, 'replaceEnvironmentVariablesCallback'), $value);
    }

    return $value;
  }

  /**
   * Searches the enviroment variables and returns its value. Using:
   *
   * 1) superglobal $_SERVER array
   * 2) getenv() function
   *
   * @param array $matches
   * @return string
   * @see replaceEnvironmentVariablesCallback
   */
  protected function replaceEnvironmentVariablesCallback($matches)
  {
    $name = $matches[1];
    if(isset($_SERVER[$name]))
    {
      return $_SERVER[$name];
    }
    else
    {
      if(getenv($name) !== false)
      {
        return getenv($name);
      }
    }
  }

}
