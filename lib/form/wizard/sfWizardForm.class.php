<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWizardForm represents a form for managing wizard like forms
 *
 * @package    Sift
 * @subpackage form
 * @see        http://stackoverflow.com/questions/3545398/how-to-validate-a-symfony-form-in-steps-instead-of-calling-form-isvalid
 */
class sfWizardForm extends myForm
{
  /**
   * Used for storing namespace names
   *
   * @var array
   */
  protected static $storageNamespaces = array();

  /**
   * Renders back button
   *
   * @param string $value
   * @param array $attributes An array of button attriutes
   * @return string
   */
  public function back($value = 'Back', $attributes = array())
  {
    $attributes = array_merge(array(
      'type' => 'submit',
      'name' => 'submit',
      'value' => 'back'
    ), $attributes);

    return $this->renderSubmitTag($value, $attributes);
  }

  /**
   * Sets storage namespace
   *
   * @param string $storageNamespace
   * @param string $formNameMask
   */
  public static function setStorageNamespace($storageNamespace,
          $formNameMask)
  {
    self::$storageNamespaces[$formNameMask] = $storageNamespace;
  }

  /**
   * Returns storage namespace name for given $formNameMask
   *
   * @param string $formNameMask
   * @return string
   */
  public static function getStorageNamespace($formNameMask = null)
  {
    if(is_null($formNameMask))
    {
      return current(self::$storageNamespaces);
    }
    return self::$storageNamespaces[$formNameMask];
  }

}
