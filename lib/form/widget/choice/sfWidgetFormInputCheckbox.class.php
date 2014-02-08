<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormInputCheckbox represents an HTML checkbox tag.
 *
 * @package    Sift
 * @subpackage form_widget
 */
class sfWidgetFormInputCheckbox extends sfWidgetFormInput
{
  /**
   * Constructor.
   *
   * Available options:
   *
   *  - value_attribute_value: The "value" attribute value to set for the checkbox
   *  - unchecked_submitable: Renders hidden field with unchecked state
   *
   * @param array  $options     An array of options
   * @param array  $attributes  An array of default HTML attributes
   *
   * @see sfWidgetFormInput
   */
  public function __construct($options = array(), $attributes = array())
  {
    $this->addOption('value_attribute_value');

    parent::__construct($options, $attributes);
  }

  /**
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetFormInput
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    $this->setOption('type', 'checkbox');

    // allows to submit the unchecked state of the checkbox
    // hidden input is rendered before the checkbox which holds the 0 value
    // when checkbox is checked, it overwrites the hidden input's value
    $this->addOption('unchecked_submitable', true);

    if (isset($attributes['value'])) {
      $this->setOption('value_attribute_value', $attributes['value']);
    }
  }

  /**
   * Renders the widget.
   *
   * @param  string $name        The element name
   * @param  string $value       The this widget is checked if value is not null
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    if ($value === '') {
      $value = null;
    }

    if (null !== $value && $value !== false) {
      $attributes['checked'] = 'checked';

      if (sfWidget::isAriaEnabled()) {
        $attributes['aria-checked'] = 'true';
      }
    }

    if (!isset($attributes['value']) && null !== $this->getOption('value_attribute_value')) {
      $attributes['value'] = $this->getOption('value_attribute_value');
    }

    $html = parent::render($name, null, $attributes, $errors);

    if ($this->getOption('unchecked_submitable')) {
      $html = $this->renderTag('input', array(
          'name' => $name,
          'id' => $this->generateId($name . '_unchecked', $value), // to prevent populating with id of the checkbox
          'type' => 'hidden',
          'value' => '' // empty value is send, but validators will fail
      )) . $html;
    }

    return $html;
  }

}
