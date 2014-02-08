<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormNumber represents an HTML text input tag for inputting float values
 *
 * @package    Sift
 * @subpackage form_widget
 */
class sfWidgetFormPrice extends sfWidgetFormI18nNumber
{
  /**
   * Configures the current widget.
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    $this->setAttribute('class', 'price');
  }

  /**
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    if(is_numeric($value))
    {
      $value += 0;
    }

    return parent::render($name, $value, $attributes, $errors);
  }

}
