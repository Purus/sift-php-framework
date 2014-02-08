<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFormManager class is a utility class for retrieving forms
 *
 * @package    Sift
 * @subpackage form
 */
class sfFormManager
{
  /**
   * Returns form instance based on its name.
   *
   * @param string $name
   * @return myForm
   */
  public static function getForm($name, $defaults = array(), $options = array(),
                                 $CSRFSecret = null)
  {
    $formClass = $name.'Form';
    if (!class_exists($formClass = $name.'Form') && !class_exists($formClass = $name)) {
      throw new sfException(sprintf('The form manager has no "%s" form.', $formClass));
    }

    return new $formClass($defaults, $options, $CSRFSecret);
  }

}
