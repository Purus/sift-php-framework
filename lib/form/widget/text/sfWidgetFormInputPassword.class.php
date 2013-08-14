<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormInputPassword represents a password HTML input tag.
 *
 * @package    Sift
 * @subpackage form_widget
 */
class sfWidgetFormInputPassword extends sfWidgetFormInput {

  /**
   * Configures the current widget.
   *
   * Available options:
   *
   *  * always_render_empty: true if you want the input value to be always empty when rendering (true by default)
   *  * strength_meter: true will render the widget with strength meter attached
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetFormInput
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    $this->addOption('always_render_empty', true);

    $this->addOption('strength_meter', false);
    $this->setOption('type', 'password');
  }

  /**
   * Renders the widget.
   *
   * @param  string $name        The element name
   * @param  string $value       The password stored in this widget, will be masked by the browser.
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $meter = '';
    if($this->getOption('strength_meter'))
    {
      $meter = $this->getStrengthMeterHtml();
    }

    $html = parent::render($name, $this->getOption('always_render_empty') ? null : $value, $attributes, $errors);

    return $html . ($meter ? ("\n" . $meter) : '');
  }

  /**
   * Return strength meter HTML code
   *
   * @return string
   */
  protected function getStrengthMeterHtml()
  {
    return '<div class="password-strength-meter"><div class="password-strength-meter-bar"></div></div>';
  }

}
