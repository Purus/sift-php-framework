<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormInteger represents an HTML text input tag for inputting integer values
 *
 * @package    Sift
 * @subpackage form_widget
 */
class sfWidgetFormInteger extends sfWidgetFormInputText
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

    // HTML5 support
    if (sfConfig::get('sf_html5')) {
      $this->setOption('type', 'number');
    }

    // HTML5 attributes
    $this->addOption('min', null);
    $this->addOption('max', null);
    $this->addOption('step', null);

    // Default CSS class
    $this->setAttribute('class', 'integer');
  }

  /**
   * Renders the widget.
   *
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
    // we have HTML5 type
    if ($this->getOption('type') == 'number') {
      $baseAttributes = array();

      if (($min = $this->getOption('min')) !== null) {
        $baseAttributes['min'] = $min;
      }

      if (($max = $this->getOption('max')) !== null) {
        $baseAttributes['max'] = $max;
      }

      if (($step = $this->getOption('step')) !== null) {
        $baseAttributes['step'] = $step;
      }

      $attributes = array_merge($baseAttributes, $attributes);
    }

    return parent::render($name, $value, $attributes, $errors);
  }

}
