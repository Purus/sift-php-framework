<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormDate represents a date widget.
 *
 * @package    Sift
 * @subpackage form_widget
 * */
class sfWidgetFormDate extends sfWidgetFormInputText
{
  /**
   * Date formatter holder
   *
   * @var sfI18nDateFormatter
   */
  protected $dateFormatter;

  /**
   * Constructs the widget
   *
   * Available options:
   *
   *  * format_pattern: Pattern format (default to "d" ie. short date pattern)
   *
   * @param array $options Array of options
   * @param array $attributes Array of attributes
   */
  public function __construct($options = array(), $attributes = array())
  {
    $this->addRequiredOption('culture');

    // default pattern
    $this->addOption('format_pattern', isset($options['format_pattern']) ? $options['format_pattern'] : 'd');

    parent::__construct($options, $attributes);

    $this->dateFormatter = new sfI18nDateFormatter($this->getOption('culture'));
    $pattern = $this->dateFormatter->getInputPattern($this->getOption('format_pattern'));

    $this->addOption('input_pattern', $pattern);
    $this->setAttribute('class', 'date');
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
    $pattern = $this->getOption('input_pattern');

    if($value)
    {
      // we will try to format it
      try
      {
        $value = $this->dateFormatter->format($value, $pattern);
      }
      catch(sfException $e)
      {
      }
    }

    return parent::render($name, $value, $attributes, $errors);
  }

}
