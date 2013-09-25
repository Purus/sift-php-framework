<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfTextMacroCallableDefinition defines a text macro callback.
 * Callback functions can not only be simple functions, but also object methods,
 * including static class methods.
 *
 * @package    Sift
 * @subpackage text_filter
 */
class sfTextMacroCallbackDefinition extends sfCallbackDefinition {

  /**
   * Constructor.
   *
   * @param string $classOrFunction The text filter class or function
   * @param array  $arguments An array of arguments to pass to the object constructor
   */
  public function __construct($classOrFunction, array $arguments = array())
  {
    parent::__construct($classOrFunction, $arguments);

    if(($class = $this->getClass()))
    {
      if(!class_exists($class))
      {
        throw new InvalidArgumentException(sprintf('The given class "%s" does not exist.', $class));
      }

      $classImplements = class_implements($class);
      if(!(in_array('sfITextMacroFilter', $classImplements) || in_array('sfITextMacroWidget', $classImplements)))
      {
        throw new InvalidArgumentException(sprintf('The given class "%s" does not implement sfITextMacroFilter nor sfITextMacroWidget interface.', $class));
      }
    }
  }

  /**
   * @inheritdoc
   */
  public static function createFromArray(array $array, $class = __CLASS__)
  {
    return parent::createFromArray($array, $class);
  }

}
