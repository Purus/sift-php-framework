<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfParameterHolder provides a base class for managing parameters.
 *
 * Parameters, in this case, are used to extend classes with additional data
 * that requires no additional logic to manage.
 *
 * @package    Sift
 * @subpackage util
 */
class sfParameterHolder implements Serializable, sfIJsonSerializable
{
  /**
   * Default namespace
   *
   * @var string
   */
  protected $defaultNamespace = 'sift/default';

  /**
   * Array of parameters
   *
   * @var array
   */
  protected $parameters = array();

  /**
   * The constructor for sfParameterHolder.
   *
   * The default namespace may be overridden at initialization as follows:
   * <code>
   * <?php
   * $mySpecialPH = new sfParameterHolder('sift/special');
   * ?>
   * </code>
   */
  public function __construct($namespace = null)
  {
    if ($namespace) {
      $this->defaultNamespace = (string) $namespace;
    }
  }

  /**
   * Specify data which should be serialized to JSON
   *
   * @return array
   */
  public function jsonSerialize()
  {
    return $this->parameters;
  }

  /**
   * Get the default namespace value.
   *
   * The $defaultNamespace is defined as 'sift/default'.
   *
   * @return string The default namespace.
   */
  public function getDefaultNamespace()
  {
    return $this->defaultNamespace;
  }

  /**
   * Clear all parameters
   *
   * @return sfParameterHolder
   */
  public function clear()
  {
    $this->parameters = null;
    $this->parameters = array();

    return $this;
  }

  /**
   * Retrieve a parameter with an optionally specified namespace.
   *
   * An isolated namespace may be identified by providing a value for the third
   * argument.  If not specified, the default namespace 'sift/default' is
   * used.
   *
   * @param string A parameter name.
   * @param mixed  A default parameter value.
   * @param string A parameter namespace.
   *
   * @return mixed A parameter value, if the parameter exists, otherwise null.
   */
  public function & get($name, $default = null, $ns = null)
  {
    if (!$ns) {
      $ns = $this->defaultNamespace;
    }

    if (isset($this->parameters[$ns][$name])) {
      $value = & $this->parameters[$ns][$name];
    } else if (isset($this->parameters[$ns])) {
      $value = sfToolkit::getArrayValueForPath($this->parameters[$ns], $name, $default);
    } else {
      $value = $default;
    }

    return $value;
  }

  /**
   * Retrieve an array of parameter names from an optionally specified namespace.
   *
   * @param string A parameter namespace.
   *
   * @return array An indexed array of parameter names, if the namespace exists, otherwise null.
   */
  public function getNames($ns = null)
  {
    if (!$ns) {
      $ns = $this->defaultNamespace;
    }

    if (isset($this->parameters[$ns])) {
      return array_keys($this->parameters[$ns]);
    }

    return array();
  }

  /**
   * Retrieve an array of parameter namespaces.
   *
   * @return array An indexed array of parameter namespaces.
   */
  public function getNamespaces()
  {
    return array_keys($this->parameters);
  }

  /**
   * Retrieve an array of parameters, within a namespace.
   *
   * This method is limited to a namespace.  Without any argument,
   * it returns the parameters of the default namespace.  If a
   * namespace is passed as an argument, only the parameters of the
   * specified namespace are returned.
   *
   * @param string A parameter namespace.
   *
   * @return array An associative array of parameters.
   */
  public function & getAll($ns = null)
  {
    if (!$ns) {
      $ns = $this->defaultNamespace;
    }

    $parameters = array();

    if (isset($this->parameters[$ns])) {
      $parameters = $this->parameters[$ns];
    }

    return $parameters;
  }

  /**
   * Indicates whether or not a parameter exists.
   *
   * @param string A parameter name.
   * @param string A parameter namespace.
   *
   * @return bool true, if the parameter exists, otherwise false.
   */
  public function has($name, $ns = null)
  {
    if (!$ns) {
      $ns = $this->defaultNamespace;
    }

    if (false !== ($offset = strpos($name, '['))) {
      if (isset($this->parameters[$ns][substr($name, 0, $offset)])) {
        $array = $this->parameters[$ns][substr($name, 0, $offset)];

        while ($pos = strpos($name, '[', $offset)) {
          $end = strpos($name, ']', $pos);
          if ($end == $pos + 1) {
            // reached a []
            return true;
          } else if (!isset($array[substr($name, $pos + 1, $end - $pos - 1)])) {
            return false;
          }
          $array = $array[substr($name, $pos + 1, $end - $pos - 1)];
          $offset = $end;
        }

        return true;
      }
    } elseif (isset($this->parameters[$ns][$name])) {
      return true;
    }

    return false;
  }

  /**
   * Indicates whether or not A parameter namespace exists.
   *
   * @param string A parameter namespace.
   *
   * @return bool true, if the namespace exists, otherwise false.
   */
  public function hasNamespace($ns)
  {
    return isset($this->parameters[$ns]);
  }

  /**
   * Remove a parameter.
   *
   * @param string A parameter name.
   * @param string A parameter namespace.
   *
   * @return string A parameter value, if the parameter was removed, otherwise null.
   */
  public function & remove($name, $ns = null)
  {
    if (!$ns) {
      $ns = $this->defaultNamespace;
    }

    $retval = null;

    if (isset($this->parameters[$ns]) && isset($this->parameters[$ns][$name])) {
      $retval = & $this->parameters[$ns][$name];
      unset($this->parameters[$ns][$name]);
    }

    return $retval;
  }

  /**
   * Remove A parameter namespace and all of its associated parameters.
   *
   * @param string $ns A parameter namespace
   * @return null|mixed The removed value
   */
  public function &removeNamespace($ns = null)
  {
    if (!$ns) {
      $ns = $this->defaultNamespace;
    }

    $retval = null;

    if (isset($this->parameters[$ns])) {
      $retval = & $this->parameters[$ns];
      unset($this->parameters[$ns]);
    }

    return $retval;
  }

  /**
   * Set a parameter.
   *
   * If a parameter with the name already exists the value will be overridden.
   *
   * @param string $name A parameter name
   * @param mixed  $value A parameter value
   * @param string $ns A parameter namespace
   * @return sfParameterHolder
   */
  public function set($name, $value, $ns = null)
  {
    if (!$ns) {
      $ns = $this->defaultNamespace;
    }

    if (!isset($this->parameters[$ns])) {
      $this->parameters[$ns] = array();
    }

    $this->parameters[$ns][$name] = $value;

    return $this;
  }

  /**
   * Set a parameter by reference.
   *
   * If a parameter with the name already exists the value will be overridden.
   *
   * @param string $name A parameter name
   * @param mixed  $value A reference to a parameter value
   * @param string $ns A parameter namespace
   * @return sfParameterHolder
   */
  public function setByRef($name, &$value, $ns = null)
  {
    if (!$ns) {
      $ns = $this->defaultNamespace;
    }

    if (!isset($this->parameters[$ns])) {
      $this->parameters[$ns] = array();
    }

    $this->parameters[$ns][$name] = & $value;

    return $this;
  }

  /**
   * Set an array of parameters.
   *
   * If an existing parameter name matches any of the keys in the supplied
   * array, the associated value will be overridden.
   *
   * @param array $parameters An associative array of parameters and their associated values
   * @param string $ns A parameter namespace
   * @return sfParameterHolder
   */
  public function add($parameters, $ns = null)
  {
    if ($parameters === null) {
      return;
    }

    if (!$ns) {
      $ns = $this->defaultNamespace;
    }

    if (!isset($this->parameters[$ns])) {
      $this->parameters[$ns] = array();
    }

    foreach ($parameters as $key => $value) {
      $this->parameters[$ns][$key] = $value;
    }

    return $this;
  }

  /**
   * Set an array of parameters by reference.
   *
   * If an existing parameter name matches any of the keys in the supplied
   * array, the associated value will be overridden.
   *
   * @param array $parameters An associative array of parameters and references to their associated values.
   * @param string $ns A parameter namespace.
   * @return sfParameterHolder
   */
  public function addByRef(& $parameters, $ns = null)
  {
    if (!$ns) {
      $ns = $this->defaultNamespace;
    }

    if (!isset($this->parameters[$ns])) {
      $this->parameters[$ns] = array();
    }

    foreach ($parameters as $key => &$value) {
      $this->parameters[$ns][$key] = & $value;
    }

    return $this;
  }

  /**
   * Serialize the object
   *
   * @return string
   */
  public function serialize()
  {
    return serialize(array($this->defaultNamespace, $this->parameters));
  }

  /**
   * Unserialize the object
   *
   * @param string $serialized
   * @return void
   */
  public function unserialize($serialized)
  {
    list($this->defaultNamespace, $this->parameters) = unserialize($serialized);
  }

}
