<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormI18nNumber represent a input for number inputs with culture specific formatting.
 *
 * @package    Sift
 * @subpackage validator
 */
class sfWidgetFormI18nNumber extends sfWidgetFormInput
{
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * type: The widget type (text by default)
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);
    $this->addOption('culture', null);
  }

  /**
   * @param  string $name        The element name
   * @param  string $value       The value displayed in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    if(!is_null($value) && is_numeric($value))
    {
      // trim zeros
      $value += 0;

      $numberFormat = new sfI18nNumberFormatter($this->getCulture());
      $value = $numberFormat->format($value);
    }

    return parent::render($name, $value, $attributes, $errors);
  }

}
