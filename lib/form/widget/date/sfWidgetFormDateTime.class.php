<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormDateTime represents a datetime widget.
 *
 * @package    Sift
 * @subpackage form_widget
 */
class sfWidgetFormDateTime extends sfWidgetFormDate {

  /**
   * Constructs the widget
   *
   * Available options:
   *
   * * format_pattern: long (G), short (g)
   *
   * @param array $options Array of options
   * @param array $attributes Array of attributes
   */
  public function __construct($options = array(), $attributes = array())
  {
    // default pattern is "short" ie without seconds
    if(!isset($options['format_pattern']))
    {
      $options['format_pattern'] = 'g';
    }

    switch($options['format_pattern'])
    {
      case 'short':
        $options['format_pattern'] = 'g';
        break;

      // long time pattern
      case 'long':
        $options['format_pattern'] = 'G';
        break;
    }

    parent::__construct($options, $attributes);

    $this->setAttribute('class', 'datetime');
  }

}
