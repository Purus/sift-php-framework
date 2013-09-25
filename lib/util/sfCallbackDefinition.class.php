<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCallbackDefinition defines a object or function which will be created when needed.
 *
 * @package    Sift
 * @subpackage util
 */
class sfCallbackDefinition extends sfObjectCallbackDefinition {

  /**
   * The function to call
   *
   * @var callable
   */
  protected $function;

  /**
   * Constructor.
   *
   * @param string $classOrFunction The text filter class or function
   * @param array  $arguments An array of arguments to pass to the object constructor
   */
  public function __construct($classOrFunction, array $arguments = array())
  {
    if(is_callable($classOrFunction))
    {
      $this->function = $classOrFunction;
    }
    else
    {
      $this->class = $classOrFunction;
    }
    $this->arguments = $arguments;
  }

  /**
   * Returns the function
   *
   * @return callable|null
   */
  public function getFunction()
  {
    return $this->function;
  }

  /**
   * Sets the function
   *
   * @param string|Closure $function
   * @return sfTextFilterDefinition
   * @throws InvalidArgumentException
   */
  public function setFunction($function)
  {
    if(!$this->isFunction($function))
    {
      throw new InvalidArgumentException('Invalid function given.');
    }
    $this->function = $function;
    return $this;
  }

  /**
   * Creates the definition from array
   *
   * @param array $array
   * @param string $class The class to return
   * @return sfTextFilterDefinition
   * @throws InvalidArgumentException If the array does not contain "class/function" definition
   */
  public static function createFromArray(array $array, $class = __CLASS__)
  {
    if(!isset($array['class']) && !isset($array['function']))
    {
      throw new InvalidArgumentException('Missing "class/function" key in the text filter definition');
    }

    $definition = new $class(isset($array['class']) ? $array['class'] : $array['function'], (isset($array['arguments']) ? $array['arguments'] : array()));

    if(isset($array['constructor']))
    {
      $definition->setConstructor($array['constructor']);
    }

    if(isset($array['file']))
    {
      $definition->setFile(self::replacePath($array['file']));
    }

    if(isset($array['calls']))
    {
      $definition->setMethodCalls($array['calls']);
    }

    if(isset($array['configurator']))
    {
      $definition->setConfigurator($array['configurator']);
    }

    if(isset($array['shared']))
    {
      $definition->setShared($array['shared']);
    }

    return $definition;
  }

  /**
   * Converts the definition to string
   *
   * @return string
   */
  public function __toString()
  {
    if(($function = $this->getFunction()))
    {
      return $function instanceof Closure ? 'Anonymous function' : $function;
    }
    return sprintf('%s::%s()', $this->getClass(),
        $this->getConstructor() ? $this->getConstructor() : 'filter');
  }
}
