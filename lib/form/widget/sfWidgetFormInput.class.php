<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormInput represents an HTML input tag.
 *
 * @package    Sift
 * @subpackage form_widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class sfWidgetFormInput extends sfWidgetForm
{
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * type: The widget type
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addRequiredOption('type');
    $this->setOption('type', 'text');
    $this->addOption('size');    
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
    $baseAttributes = array(
      'type' => $this->getOption('type'), 
      'name' => $name, 
      'value' => $value
    );
    
    if($size = $this->getOption('size'))
    {
      $baseAttributes['size'] = $size;
    }
    
    return $this->renderTag('input', array_merge($baseAttributes, $attributes));
  }
  
}
