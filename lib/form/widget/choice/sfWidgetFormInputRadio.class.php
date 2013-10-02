<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormInputRadio represents an HTML <input type="radio"> tag.
 *
 * @package    Sift
 * @subpackage form_widget
 */
class sfWidgetFormInputRadio extends sfWidgetFormInput {

  /**
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetFormInput
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);
    $this->setOption('type', 'radio');
  }

  /**
   * Renders the widget.
   *
   * @param  string $name        The element name
   * @param  string $value       The value selected in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    if(!isset($attributes['id']))
    {
      $attributes['id'] = $this->generateId(sprintf('%s[]', $name), $value);
    }
    return parent::render($name, $value, $attributes, $errors);
  }

}
