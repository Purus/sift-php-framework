<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfTextFilterDefinition defines a text filter
 *
 * @package    Sift
 * @subpackage text_filter
 */
class sfTextFilterCallbackDefinition extends sfCallbackDefinition {

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
      // class does not implement sfITextFilter interface!
      if(!in_array('sfITextFilter', class_implements($class)))
      {
        throw new InvalidArgumentException(sprintf('The given class "%s" does not implement sfITextFilterInterface.', $class));
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
