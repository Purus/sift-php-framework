<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormTreeSelectCheckbox represents an array of checkboxes in tree structure.
 *
 * @package    Sift
 * @subpackage form_widget
 */
class sfWidgetFormTreeSelectCheckbox extends sfWidgetFormSelectCheckbox
{
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * choices:         An array of possible choices (required)
   *  * class:           The class to use for the main <div> tag (or tag specified in template)
   *  * separator:       The separator to use between each input checkbox
   *  * formatter:       A callable to call to format the checkbox choices
   *                     The formatter callable receives the widget and the array of inputs as arguments
   *  * template:        The template to use for rendering
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetFormChoiceBase
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    $this->setOption('class', 'tree');
    $this->setOption('formatter', array($this, 'formatter'));
    $this->setOption('template', '<div class="%class%">%choices%</div>');
    $this->setOption('translate_choices', false);
    $this->addOption('input_type', 'checkbox');
  }

  /**
   * Renders the widget.
   *
   * @param  string $name        The element name
   * @param  string $value       The value selected in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    if ('[]' != substr($name, -2))
    {
      $name .= '[]';
    }

    if($this->getOption('multiple'))
    {
      if (null === $value)
      {
        $value = array();
      }
    }

    // prepare classes to mark the tree
    $class = array($this->getOption('class'));

    if($this->getOption('input_type') == 'checkbox')
    {
      $class[] = 'multiple';
    }

    $class[] = sprintf('%s-%s', $this->getOption('class'), $this->getOption('input_type'));

    return strtr($this->getOption('template'), array(
      '%class%' => join(' ', array_unique($class)),
      '%choices%' => $this->formatChoices($name, $value, $this->getChoices(), $attributes)
    ));
  }

  protected function formatChoices($name, $value, $choices, $attributes)
  {
    // reset attributes, fixes problems with
    // attributes like "disabled" to appear in the label tag!
    $this->attributes = array();

    $inputs = array();
    foreach ($choices as $key => $option)
    {
      if($this->getOption('input_type') == 'radio')
      {
        $baseAttributes = array(
            'name' => substr($name, 0, -2),
            'type' => 'radio',
            'value' => self::escapeOnce($key),
            'id' => $id = $this->generateId($name, self::escapeOnce($key)),
        );
      }
      else
      {
        $baseAttributes = array(
          'name'  => $name,
          'type'  => $this->getOption('input_type'),
          'value' => self::escapeOnce($key),
          'id'    => $id = $this->generateId($name, self::escapeOnce($key)),
        );
      }

      switch($this->getOption('input_type'))
      {
        case 'checkbox':
          if ((is_array($value) && in_array(strval($key), $value)) ||
                  (is_string($value) && strval($key) == strval($value)))
          {
            $baseAttributes['checked'] = 'checked';
          }
        break;

        case 'radio':
          if(strval($key) == strval($value === false ? 0 : $value))
          {
            $baseAttributes['checked'] = 'checked';
          }
        break;
      }

      $labelAttributes = array(
        'for' => $id,
        'class' => 'inline'
      );

      if(sfWidget::isAriaEnabled())
      {
        $labelId = sprintf('%s_label', $id);
        // overwrite attribute!
        $attributes['aria-labelledby'] = $labelId;
        $labelAttributes['id'] = $labelId;
      }

      unset($attributes['multiple']);

      if($option instanceof sfMenu)
      {
        $labelName = $option->getName();
      }
      else
      {
        $labelName = $option['title'];
      }

      $inputs[$id] = array(
        'input' => $this->renderTag('input', array_merge($baseAttributes, $attributes)),
        'checked' => isset($baseAttributes['checked']) ? true : false,
        'label' => $this->renderContentTag('label', self::escapeOnce($labelName), $labelAttributes),
        'option' => $option,
        'children' => array()
      );

      if(isset($option['children']) && count($option['children']))
      {
        $inputs[$id]['children'] = $this->formatChoices($name, $value, $option['children'], $attributes);
      }
    }

    return call_user_func($this->getOption('formatter'), $this, $inputs);
  }

  public function formatter($widget, $inputs)
  {
    $attributes = array();
    $listAtttibutes = array();

    if(sfWidget::isAriaEnabled())
    {
      $attributes['role'] = 'list';
      $listAtttibutes['role'] = 'listitem';
    }

    $html = '';
    foreach ($inputs as $input)
    {
      $html .= sfHtml::tag('li', $listAtttibutes, true);
      $html .= $input['input'].$this->getOption('label_separator').$input['label'];

      if($input['children'])
      {
        $html .= $input['children'];
      }

      $html .= '</li>';
    }

    return $html ? $this->renderContentTag('ul', $html, $attributes) : '';
  }

}
