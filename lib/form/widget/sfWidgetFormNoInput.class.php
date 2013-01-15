<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormInput represents an HTML text input tag.
 *
 * @package    Sift
 * @subpackage form_widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class sfWidgetFormNoInput extends sfWidgetForm
{
  /**
   * Renders thw widget
   * 
   * @param type $name
   * @param type $value
   * @param type $attributes
   * @param type $errors 
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    if(!isset($attributes['class']))
    {
      $attributes['class'] = 'form-no-input';
    }
    else
    {
      $attributes['class'] .= ' form-no-input';
    }
    
    $value = $this->encloseInSpanTag($value, $attributes); 
    
    return $value;
  }
  
}
