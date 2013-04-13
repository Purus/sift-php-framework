<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfReflection class extends builtin reflection class for additional utility methods
 * and reports information about a class.
 *
 * @package    Sift
 * @subpackage util
 */
class sfReflectionClass extends ReflectionClass {

  /**
   * Checks if the class is a subclass of a specified class or implements a specified interface.
   *
   * @param string|array|ReflectionClass|sfReflectionClass $class Class to check
   * @return boolean
   */
  public function isSubclassOf($class)
  {
    if(!is_array($class))
    {
      $class = array($class);
    }

    foreach($class as $c)
    {
      if(parent::isSubclassOf($c))
      {
        return true;
      }
    }

    return false;
  }

  /**
   * Checks if the class is a subclass of a specified class or implements a specified interface
   * or the name is equal.
   *
   * @param string|array|ReflectionClass|sfReflectionClass $class Class to check
   * @return boolean
   */
  public function isSubclassOfOrIsEqual($class)
  {
    if(!is_array($class))
    {
      $class = array($class);
    }

    $name = strtolower($this->getName());

    foreach($class as $c)
    {
      // first check if is equal
      if($name === strtolower($c))
      {
        return true;
      }

      if($this->isSubclassOf($c))
      {
        return true;
      }
    }
    return false;
  }

  /**
   * Returns parent class names
   *
   * @return array
   */
  public function getParentClassNames()
  {
    $parents = array();
    $parent = $this;

    while($parent = $parent->getParentClass())
    {
      $parents[] = $parent->getName();
    }

    return $parents;
  }

}
