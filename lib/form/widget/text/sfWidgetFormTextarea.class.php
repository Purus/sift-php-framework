<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormTextarea represents a textarea HTML tag.
 *
 * @package    Sift
 * @subpackage form_widget
 */
class sfWidgetFormTextarea extends sfWidgetForm {

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
    $this->addOption('rich', false);
    $this->setAttribute('rows', 4);
    $this->setAttribute('cols', 30);
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
    if($rich = $this->getOption('rich'))
    {
      $class = 'rich';
      if(isset($attributes['class']))
      {
        $attributes['class'] = join(' ', array_unique(array_merge(
            explode(' ', $attributes['class']), array($class))));
      }
      else
      {
        $attributes['class'] = $class;
      }
    }

    return $this->renderContentTag('textarea', self::escapeOnce($value), array_merge(array('name' => $name), $attributes));
  }

}
