<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormInteger represents an HTML text input tag for inputting ip addresses
 *
 * @package    Sift
 * @subpackage form_widget
 */
class sfWidgetFormIpAddress extends sfWidgetFormInputText
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
    // Default CSS class
    $this->setAttribute('class', 'ip-address');
  }

}
