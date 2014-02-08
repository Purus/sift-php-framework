<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormInputFile represents an upload input tag.
 *
 * @package    Sift
 * @subpackage form_widget
 */
class sfWidgetFormInputFile extends sfWidgetFormInput
{
  /**
   * Configures the current widget.
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetFormInput
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    // single or multiple uploads?
    $this->addOption('multiple', false);
    // input options
    $this->setOption('type', 'file');
    $this->setOption('needs_multipart', true);
  }

  /**
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    if ($this->getOption('multiple')) {
      if ('[]' != substr($name, -2)) {
        $name .= '[]';
      }
    }

    // prepare attributes
    // multiple is valid only for HTML5
    if ($this->getOption('multiple') && !sfWidget::isXhtml()) {
      // FIXME: in HTML5 the valid attibute is something like <input multiple>
      $attributes['multiple'] = 'multiple';
    }

    return parent::render($name, $value, $attributes, $errors);
  }

}
