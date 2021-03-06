<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormDateRange represents a date range widget.
 *
 * @package    Sift
 * @subpackage form_widget
 */
class sfWidgetFormDateRange extends sfWidgetForm {

  /**
   * Configures the current widget.
   *
   * Available options:
   *
   *  * from_date:   The from date widget (required)
   *  * to_date:     The to date widget (required)
   *  * template:    The template to use to render the widget
   *                 Available placeholders: %from_date%, %to_date%
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addRequiredOption('from');
    $this->addRequiredOption('to');

    $this->addOption('template', 'from %from% to %to%');
  }

  /**
   * Renders the widget.
   *
   * @param  string $name        The element name
   * @param  string $value       The date displayed in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $value = array_merge(array('from' => '', 'to' => '', 'is_empty' => ''), is_array($value) ? $value : array());

    return strtr($this->translate($this->getOption('template')), array(
        '%from%' => $this->getOption('from')->render($name . '[from]', $value['from']),
        '%to%' => $this->getOption('to')->render($name . '[to]', $value['to']),
    ));
  }

  /**
   * Gets the stylesheet paths associated with the widget.
   *
   * @return array An array of stylesheet paths
   */
  public function getStylesheets()
  {
    return array_unique(array_merge($this->getOption('from')->getStylesheets(), $this->getOption('to')->getStylesheets()));
  }

  /**
   * Gets the JavaScript paths associated with the widget.
   *
   * @return array An array of JavaScript paths
   */
  public function getJavaScripts()
  {
    return array_unique(array_merge($this->getOption('from')->getJavaScripts(), $this->getOption('to')->getJavaScripts()));
  }

}
