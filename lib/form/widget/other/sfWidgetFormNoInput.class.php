<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormInput represents no input tag. Simply renders the value enclosed in span tag.
 *
 * @package    Sift
 * @subpackage form_widget
 */
class sfWidgetFormNoInput extends sfWidgetForm
{
  /**
   * Configures the current widget.
   *
   *  * Available options:
   *
   *  * tag: To which tag enclose the value? Default is "span"
   *  * value_renderer: Callback function to call to render the value (can be sfCallback object)
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetFormInput
   */
  public function configure($options = array(), $attributes = array())
  {
    $this->addOption('tag', 'span');
    $this->addOption('value_renderer', null);
  }

  /**
   * @see sfWidget
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    if (!isset($attributes['class'])) {
      $attributes['class'] = 'form-no-input';
    } else {
      $attributes['class'] .= ' form-no-input';
    }

    if ($renderer = $this->getOption('value_renderer')) {
      if ($renderer instanceof sfCallable) {
        $value = $renderer->call($value);
      } else {
        $value = call_user_func($renderer, $value);
      }
    }

    return $this->encloseInTag($value, $attributes, $this->getOption('tag'));
  }

  /**
   * Is this widget labelable?
   *
   * @return boolean
   */
  public function isLabelable()
  {
    return false;
  }

}
