<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfObjectCallbackDefinition defines a object which will be created when needed.
 * This is useful when there is a need to initiate the object only on demand not beforehand.
 *
 * @package    Sift
 * @subpackage util
 */
class sfObjectCallbackDefinition
{
  protected $class        = null,
    $file         = null,
    $constructor  = null,
    $shared       = true,
    $arguments    = array(),
    $calls        = array(),
    $configurator = null;

  /**
   * Creates the definition from array
   *
   * @param array $array
   * @param string $class The class to return
   * @return sfObjectCallbackDefinition
   * @throws InvalidArgumentException
   */
  public static function createFromArray(array $array, $class = __CLASS__)
  {
    if (!isset($array['class'])) {
      throw new InvalidArgumentException('Missing "class" key in the object definition');
    }

    $definition = new $class($array['class'], (isset($array['arguments']) ? $array['arguments'] : array()));

    if (isset($array['constructor'])) {
      $definition->setConstructor($array['constructor']);
    }

    if (isset($array['file'])) {
      $definition->setFile(self::replacePath($array['file']));
    }

    if (isset($array['calls'])) {
      $definition->setMethodCalls($array['calls']);
    }

    if (isset($array['configurator'])) {
      $definition->setConfigurator($array['configurator']);
    }

    if (isset($array['shared'])) {
      $definition->setShared($array['shared']);
    }

    return $definition;
  }

  public static function replacePath($path)
  {
    if (!sfToolkit::isPathAbsolute($path)) {
      // not an absolute path so we'll prepend to it
      $path = sfConfig::get('sf_app_dir') . '/' . $path;
    }

    return $path;
  }

  /**
   * Constructor.
   *
   * @param string $class     The object class
   * @param array  $arguments An array of arguments to pass to the object constructor
   */
  public function __construct($class, array $arguments = array())
  {
    $this->class = $class;
    $this->arguments = $arguments;
  }

  /**
   * Sets the constructor method.
   *
   * @param  string              $method The method name
   * @return sfObjectCallbackDefinition The current instance
   */
  public function setConstructor($method)
  {
    $this->constructor = $method;

    return $this;
  }

  /**
   * Gets the constructor method.
   *
   * @return sfObjectCallbackDefinition The constructor method name
   */
  public function getConstructor()
  {
    return $this->constructor;
  }

  /**
   * Sets the object class.
   *
   * @param  string $class The object class
   *
   * @return sfObjectCallbackDefinition The current instance
   */
  public function setClass($class)
  {
    $this->class = $class;

    return $this;
  }

  /**
   * Sets the constructor method.
   *
   * @return string The object class
   */
  public function getClass()
  {
    return $this->class;
  }

  /**
   * Sets the constructor arguments to pass to the object constructor.
   *
   * @param  array               $arguments An array of arguments
   *
   * @return sfObjectCallbackDefinition The current instance
   */
  public function setArguments(array $arguments)
  {
    $this->arguments = $arguments;

    return $this;
  }

  /**
   * Adds a constructor argument to pass to the object constructor.
   *
   * @param  mixed               $argument An argument
   *
   * @return sfObjectCallbackDefinition The current instance
   */
  public function addArgument($argument)
  {
    $this->arguments[] = $argument;

    return $this;
  }

  /**
   * Gets the constructor arguments to pass to the object constructor.
   *
   * @return array The array of arguments
   */
  public function getArguments()
  {
    return $this->arguments;
  }

  /**
   * Sets the methods to call after object initialization.
   *
   * @param  array               $calls An array of method calls
   *
   * @return sfObjectCallbackDefinition The current instance
   */
  public function setMethodCalls(array $calls = array())
  {
    $this->calls = array();
    foreach ($calls as $call) {
      $this->addMethodCall($call[0], $call[1]);
    }

    return $this;
  }

  /**
   * Adds a method to call after object initialization.
   *
   * @param  string              $method    The method name to call
   * @param  array               $arguments An array of arguments to pass to the method call
   *
   * @return sfObjectCallbackDefinition The current instance
   */
  public function addMethodCall($method, array $arguments = array())
  {
    $this->calls[] = array($method, $arguments);

    return $this;
  }

  /**
   * Gets the methods to call after object initialization.
   *
   * @return  array An array of method calls
   */
  public function getMethodCalls()
  {
    return $this->calls;
  }

  /**
   * Sets a file to require before creating the object.
   *
   * @param  string              $file A full pathname to include
   *
   * @return sfObjectCallbackDefinition The current instance
   */
  public function setFile($file)
  {
    $this->file = $file;

    return $this;
  }

  /**
   * Gets the file to require before creating the object.
   *
   * @return string The full pathname to include
   */
  public function getFile()
  {
    return $this->file;
  }

  /**
   * Sets if the object must be shared or not.
   *
   * @param  Boolean             $shared Whether the object must be shared or not
   *
   * @return sfObjectCallbackDefinition The current instance
   */
  public function setShared($shared)
  {
    $this->shared = (Boolean) $shared;

    return $this;
  }

  /**
   * Returns true if the object must be shared.
   *
   * @return Boolean true if the object is shared, false otherwise
   */
  public function isShared()
  {
    return $this->shared;
  }

  /**
   * Sets a configurator to call after the object is fully initialized.
   *
   * @param  mixed               $callable A PHP callable
   *
   * @return sfObjectCallbackDefinition The current instance
   */
  public function setConfigurator($callable)
  {
    $this->configurator = $callable;

    return $this;
  }

  /**
   * Gets the configurator to call after the object is fully initialized.
   *
   * @return mixed The PHP callable to call
   */
  public function getConfigurator()
  {
    return $this->configurator;
  }

}
