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
class sfWidgetFormPrice extends sfWidgetFormNumber
{

  /**
   * Configures the current widget.
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetForm
   */
  public function __construct($options = array(), $attributes = array())
  {
    $this->addRequiredOption('culture');

    parent::__construct($options, $attributes);
  }

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

}
