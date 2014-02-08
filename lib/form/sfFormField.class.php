<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFormField represents a widget bind to a name and a value.
 *
 * @package    Sift
 * @subpackage form
 */
class sfFormField
{
  protected static $toStringException = null;

  protected $form   = null,
    $widget = null,
    $parent = null,
    $name   = '',
    $value  = null,
    $error  = null,
    $validator = null;

  /**
   * Constructor.
   *
   * @param sfWidgetForm     $widget A sfWidget instance
   * @param sfFormField      $parent The sfFormField parent instance (null for the root widget)
   * @param string           $name   The field name
   * @param string           $value  The field value
   * @param sfValidatorError $error  A sfValidatorError instance
   */
  public function __construct(sfForm $form,
          sfWidgetForm $widget, sfFormField $parent = null, $name, $value, sfValidatorError $error = null)
  {
    $this->form   = $form;
    $this->widget = $widget;
    $this->parent = $parent;
    $this->name   = $name;
    $this->value  = $value;
    $this->error  = $error;
  }

  /**
   * Returns sfForm instance
   *
   * @return sfForm
   */
  public function getForm()
  {
    return $this->form;
  }

  /**
   * Returns the string representation of this form field.
   *
   * @return string The rendered field
   */
  public function __toString()
  {
    try {
      return $this->render();
    } catch (Exception $e) {
      self::setToStringException($e);

      // we return a simple Exception message in case the form framework is used out of Sift framework
      return 'Exception: '.$e->getMessage();
    }
  }

  /**
   * Returns true if a form thrown an exception in the __toString() method
   *
   * This is a hack needed because PHP does not allow to throw exceptions in __toString() magic method.
   *
   * @return boolean
   */
  public static function hasToStringException()
  {
    return null !== self::$toStringException;
  }

  /**
   * Gets the exception if one was thrown in the __toString() method.
   *
   * This is a hack needed because PHP does not allow to throw exceptions in __toString() magic method.
   *
   * @return Exception
   */
  public static function getToStringException()
  {
    return self::$toStringException;
  }

  /**
   * Sets an exception thrown by the __toString() method.
   *
   * This is a hack needed because PHP does not allow to throw exceptions in __toString() magic method.
   *
   * @param Exception $e The exception thrown by __toString()
   */
  public static function setToStringException(Exception $e)
  {
    if (null === self::$toStringException) {
      self::$toStringException = $e;
    }
  }

  /**
   * Renders the form field.
   *
   * @param array $attributes An array of HTML attributes
   *
   * @return string The rendered widget
   */
  public function render($attributes = array())
  {
    $attributes = $this->prepareAttributes($attributes);

    if ($this->parent) {
      return $this->parent->getWidget()->renderField($this->name, $this->value, $attributes, $this->error);
    } else {
      return $this->widget->render($this->name, $this->value, $attributes, $this->error, null, $this->widget->getAttributes(), $this->widget);
    }
  }

  /**
   * Renders the widget for javascript template
   *
   * @return string
   */
  public function renderForJavascriptTemplate()
  {
    if ($this->parent) {
      return $this->parent[$this->name]->getWidget()->renderForJavascriptTemplate();
    } else {
      return $this->widget->renderFormJavascriptTemplate();
    }
  }

  /**
   * Returns a formatted row.
   *
   * The formatted row will use the parent widget schema formatter.
   * The formatted row contains the label, the field, the error and
   * the help message.
   *
   * @param array  $attributes An array of HTML attributes to merge with the current attributes
   * @param string $label      The label name (not null to override the current value)
   * @param string $help       The help text (not null to override the current value)
   *
   * @return string The formatted row
   */
  public function renderRow($attributes = array(), $label = null, $help = null)
  {
    if (null === $this->parent) {
      throw new LogicException(sprintf('Unable to render the row for "%s".', $this->name));
    }

    $attributes = $this->prepareAttributes($attributes);

    $field = $this->parent->getWidget()->renderField($this->name, $this->value, !is_array($attributes) ? array() : $attributes, $this->error);

    $error = $this->error instanceof sfValidatorErrorSchema ? $this->error->getGlobalErrors() : $this->error;

    $help = null === $help ? $this->parent->getWidget()->getHelp($this->name) : $help;

    if (!isset($attributes['id'])) {
      $attributes['id'] = $this->getId();
    }

    $this->setupFormatter();

    return strtr($this->parent->getWidget()->getFormFormatter()->formatRow(
            $this->isLabelable() ? $this->renderLabel($label) : $this->renderLabelName(), $field, $error, $help, null,
            array_merge($this->parent->getWidget()->getAttributes(), $attributes),
            $this->widget), array('%hidden_fields%' => ''));
  }

  protected function prepareAttributes($attributes)
  {
    if ($this->parent) {
      $formFormatter = $this->parent->getWidget()->getFormFormatter();
    } else {
      $formFormatter = $this->widget->getFormFormatter();
    }

    /* @var $formFormatter sfWidgetFormSchemaFormatter */
    $errorCssClass = $formFormatter->getErrorCssClass();

    if ($this->error && $errorCssClass) {
      $classes = array($errorCssClass);
      if (isset($attributes['class'])) {
        $classes[] = $attributes['class'];
      }
      $attributes['class'] = trim(implode(' ',
        array_merge(explode(' ', $this->widget->getAttribute('class')), $classes)));
    }

    if (sfWidget::isAriaEnabled()) {
      $widgetName = $formFormatter->getWidgetSchema()->generateName($this->name);
      $id = $formFormatter->getWidgetSchema()->generateId($widgetName);

      if (!$this->isHidden() && !isset($attributes['aria-labelledby'])) {
        $attributes['aria-labelledby'] = sprintf('%s_label', $id);
      }

      if ($this->error) {
        $attributes['aria-invalid'] = 'true';
      }

      if($this->widget->hasOption('disabled')
        && $this->widget->getOption('disabled'))
      {
        $attributes['aria-disabled'] = 'true';
      }

      if (($validator = $this->getValidator())) {
        if($validator->hasOption('required')
            && $validator->getOption('required'))
        {
          $reflection = new sfReflectionClass(get_class($this->widget));
          // Handle Special case for checkboxes
          // Checkboxes does not work as expected
          // at least with HTML5 "required" attribute
          // Aria not tested!
          // http://stackoverflow.com/questions/5884582/hml5-required-attribute-on-multiple-checkboxes-with-the-same-name
          if(!$reflection->isSubclassOfOrIsEqual(array(
              'sfWidgetFormSelectCheckbox',
              'sfWidgetFormInputCheckbox',
              'sfWidgetFormChoiceMany',
              'sfWidgetFormSelectRadio',
          )))
          {
            if (!isset($attributes['aria-required'])) {
              $attributes['aria-required'] = 'true';
            }
          }
        }
      }
    }

    return $attributes;
  }

  /**
   * Returns a formatted error list.
   *
   * The formatted list will use the parent widget schema formatter.
   *
   * @param array Array of attributes
   * @return string The formatted error list
   */
  public function renderError($attributes = array())
  {
    if (null === $this->parent) {
      throw new LogicException(sprintf('Unable to render the error for "%s".', $this->name));
    }

    $this->setupFormatter();

    if ($this->parent) {
      $formFormatter = $this->parent->getWidget()->getFormFormatter();
    } else {
      $formFormatter = $this->widget->getFormFormatter();
    }

    $baseAttributes = array();

    if (!isset($attributes['for'])) {
      $baseAttributes['for'] = $this->getId();
    }

    if (sfWidget::isAriaEnabled()) {
      if (!isset($attributes['role'])) {
        $attributes['role'] = 'alert';
      }
      if (!isset($attributes['id'])) {
        $attributes['id'] = sprintf('%s_label', $this->getId());
      }
    }

    $attributes = array_merge($baseAttributes, $attributes);

    $error = $this->getWidget() instanceof sfWidgetFormSchema ? $this->getWidget()->getGlobalErrors($this->error) : $this->error;

    return $this->parent->getWidget()->getFormFormatter()->formatErrorsForRow($error, $attributes);
  }

  /**
   * Returns the help text.
   *
   * @return string The help text
   */
  public function renderHelp()
  {
    if (null === $this->parent) {
      throw new LogicException(sprintf('Unable to render the help for "%s".', $this->name));
    }

    return $this->parent->getWidget()->getFormFormatter()->formatHelp($this->parent->getWidget()->getHelp($this->name));
  }

  /**
   * Returns the help text.
   *
   * @return string The help text
   */
  public function getHelp()
  {
    if (null === $this->parent) {
      throw new LogicException(sprintf('Unable to render the help for "%s".', $this->name));
    }

    return ($this->parent->getWidget()->getHelp($this->name));
  }

  /**
   * Returns the label tag.
   *
   * @param string $label      The label name (not null to override the current value)
   * @param array  $attributes Optional html attributes
   *
   * @return string The label tag
   */
  public function renderLabel($label = null, $attributes = array())
  {
    if (null === $this->parent) {
      throw new LogicException(sprintf('Unable to render the label for "%s".', $this->name));
    }

    if (null !== $label) {
      $currentLabel = $this->parent->getWidget()->getLabel($this->name);
      $this->parent->getWidget()->setLabel($this->name, $label);
    }

    // setups formatter
    $this->setupFormatter();

    $html = $this->parent->getWidget()->getFormFormatter()->generateLabel($this->name, $attributes);

    if (null !== $label) {
      $this->parent->getWidget()->setLabel($this->name, $currentLabel);
    }

    return $html;
  }

  /**
   * Is the field labelable?
   *
   * @return boolean
   */
  public function isLabelable()
  {
    return $this->getWidget()->isLabelable();
  }

  /**
   * Returns the label name given a widget name.
   *
   * @return string The label name
   */
  public function renderLabelName()
  {
    if (null === $this->parent) {
      throw new LogicException(sprintf('Unable to render the label name for "%s".', $this->name));
    }

    $this->setupFormatter();

    return $this->parent->getWidget()->getFormFormatter()->generateLabelName($this->name);
  }

  /**
   * Returns the name attribute of the widget.
   *
   * @return string The name attribute of the widget
   */
  public function renderName()
  {
    return $this->parent ? $this->parent->getWidget()->generateName($this->name) : $this->name;
  }

  /**
   * Returns the id attribute of the widget.
   *
   * @return string The id attribute of the widget
   */
  public function renderId()
  {
    return $this->widget->generateId($this->parent ? $this->parent->getWidget()->generateName($this->name) : $this->name, $this->value);
  }

  /**
   * Returns the id attribute of the widget. Just an alias for renderId()
   *
   * @return string The id attribute of the widget
   * @see renderId()
   */
  public function getId()
  {
    return $this->renderId();
  }

  /**
   * Returns true if the widget is hidden.
   *
   * @return Boolean true if the widget is hidden, false otherwise
   */
  public function isHidden()
  {
    return $this->widget->isHidden();
  }

  /**
   * Returns the widget name.
   *
   * @return mixed The widget name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Returns the widget value.
   *
   * @return mixed The widget value
   */
  public function getValue()
  {
    return $this->value;
  }

  /**
   * Sets the widget value.
   *
   * @return sfFormWidget
   */
  public function setValue($value)
  {
    $this->value = $value;

    return $this;
  }

  /**
   * Returns the wrapped widget.
   *
   * @return sfWidget A sfWidget instance
   */
  public function getWidget()
  {
    return $this->widget;
  }

  /**
   * Returns the parent form field.
   *
   * @return sfFormField A sfFormField instance
   */
  public function getParent()
  {
    return $this->parent;
  }

  /**
   * Returns the error for this field.
   *
   * @param boolean $asTranslatedString Return as translated string? Usefull for display inside template
   * @return sfValidatorError A sfValidatorError instance
   */
  public function getError($asTranslatedString = false)
  {
    if ($asTranslatedString) {
      return $this->error ? $this->form->translate($this->error->getMessageFormat(), $this->error->getArguments()) : '';
    }

    return $this->error;
  }

  /**
   * Returns true is the field has an error.
   *
   * @return Boolean true if the field has some errors, false otherwise
   */
  public function hasError()
  {
    return null !== $this->error && count($this->error);
  }

  public function setValidator(sfValidatorBase $validator)
  {
    $this->validator = $validator;
  }

  public function getValidator()
  {
    return $this->validator;
  }

  protected function setupFormatter()
  {
    $formatter = $this->parent->getWidget()->getFormFormatter();
    /* @var $formatter sfWidgetFormSchemaFormatter */
    if ($this->getValidator() && !$formatter->hasValidator($this->name)) {
      $formatter->setValidator($this->name, $this->getValidator());
    }
  }

}
