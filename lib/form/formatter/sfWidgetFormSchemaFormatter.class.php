<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormSchemaFormatter allows to format a form schema with HTML formats.
 *
 * @package    Sift
 * @subpackage form_formatter
 */
abstract class sfWidgetFormSchemaFormatter
{
  protected static
    $translationCallable       = null;

  protected
    $rowFormat                 = '',
    $helpFormat                = '%help%',
    $errorRowFormat            = '%errors%',
    $errorListFormatInARow     = "  <ul class=\"error_list\">\n%errors%  </ul>\n",
    $errorRowFormatInARow      = "    <li>%error%</li>\n",
    $namedErrorRowFormatInARow = "    <li>%name%: %error%</li>\n",
    $requiredLabelCssClass     = 'required',
    $requiredLabelFormat       = '%label_name% <span class="%css_class%"><span>*</span></span>',
    // rendering of sfFormFieldGroups
    $fieldGroupFormat          = '<fieldset><legend>%group_name%</legend>%field_rows%</fieldset>',
    $decoratorFormat           = '',
    $errorCssClass             = '',
    $widgetSchema              = null,
    $validatorSchema           = null,
    $translationCatalogue      = null;

  /**
   * Constructor
   *
   * @param sfWidgetFormSchema $widgetSchema
   */
  public function __construct(sfWidgetFormSchema $widgetSchema)
  {
    $this->setWidgetSchema($widgetSchema);
  }

  public function formatRow($label, $field, $errors = array(), $help = '', $hiddenFields = null,
          $widgetAttributes = array(), sfWidgetForm $widget = null)
  {
    return strtr($this->getRowFormat(), array(
      '%label%'         => $label,
      '%field%'         => $field,
      '%error%'         => $this->formatErrorsForRow($errors, $widgetAttributes),
      '%help%'          => $this->formatHelp($help),
      '%field_id%'      => isset($widgetAttributes['id']) ? $widgetAttributes['id'] : '',
      '%hidden_fields%' => null === $hiddenFields ? '%hidden_fields%' : $hiddenFields,
    ));
  }

  /**
   * Translates a string using an i18n callable, if it has been provided
   *
   * @param  mixed  $subject     The subject to translate
   * @param  array  $parameters  Additional parameters to pass back to the callable
   * @return string
   */
  public function __($subject, $parameters = array())
  {
    if (false === $subject)
    {
      return false;
    }

    if (null === self::$translationCallable)
    {
      // replace object with strings
      foreach ($parameters as $key => $value)
      {
        if (is_object($value) && method_exists($value, '__toString'))
        {
          $parameters[$key] = $value->__toString();
        }
      }

      return strtr($subject, $parameters);
    }

    $catalogue = $this->getTranslationCatalogue();

    if (self::$translationCallable instanceof sfCallable)
    {
      return self::$translationCallable->call($subject, $parameters, $catalogue);
    }

    return call_user_func(self::$translationCallable, $subject, $parameters, $catalogue);
  }

  /**
   * Alias for __
   *
   * @see __
   */
  public function translate($subject, $parameters = array())
  {
    return $this->__($subject, $parameters);
  }

  /**
   * Returns the current i18n callable
   *
   * @return mixed
   */
  static public function getTranslationCallable()
  {
    return self::$translationCallable;
  }

  /**
   * Sets a callable which aims to translate form labels, errors and help messages
   *
   * @param  mixed  $callable
   *
   * @throws InvalidArgumentException if an invalid php callable or sfCallable has been provided
   */
  static public function setTranslationCallable($callable)
  {
    if (!$callable instanceof sfCallable && !sfToolkit::isCallable($callable))
    {
      throw new InvalidArgumentException('Provided i18n callable should be either an instance of sfCallable or a valid PHP callable');
    }

    self::$translationCallable = $callable;
  }

  static public function removeTranslationCallable()
  {
    self::$translationCallable = null;
  }

  public function formatHelp($help)
  {
    if (!$help)
    {
      return '';
    }

    return strtr($this->getHelpFormat(), array('%help%' => $this->translate($help)));
  }

  /**
   * Error element
   *
   * @return string
   */
  public function getErrorElement()
  {
    return $this->errorElement;
  }

  public function formatErrorRow($errors)
  {
    if (null === $errors || !$errors)
    {
      return '';
    }

    return strtr($this->getErrorRowFormat(), array('%errors%' => $this->formatErrorsForRow($errors)));
  }

  public function formatErrorsForRow($errors, $widgetAttributes = array())
  {
    if (null === $errors || !$errors)
    {
      return '';
    }

    if (!is_array($errors))
    {
      $errors = array($errors);
    }

    isset($widgetAttributes['class']) ?
      $widgetAttributes['class'] .= ' ' . $this->errorCssClass :
      $widgetAttributes['class'] = $this->errorCssClass;

    return strtr($this->getErrorListFormatInARow(), array(
            '%field_id%' => isset($widgetAttributes['for']) ? $widgetAttributes['for'] : '',
            '%errors%' => implode('', $this->unnestErrors($errors, '', $widgetAttributes))
            ));
  }

  /**
   * Generates a label for the given field name.
   *
   * @param  string $name        The field name
   * @param  array  $attributes  Optional html attributes for the label tag
   *
   * @return string The label tag
   */
  public function generateLabel($name, $attributes = array())
  {
    $is_required = false;

    if($this->validatorSchema && isset($this->validatorSchema[$name]))
    {
      $validator = $this->validatorSchema[$name];
      /* @var $validator sfValidatorBase */
      if($this->validatorMarkedFieldAsRequired($validator))
      {
        $is_required = true;
      }
    }
    elseif(isset($this->validators[$name]))
    {
      $validator = $this->validators[$name];
      /* @var $validator sfValidatorBase */
      if($this->validatorMarkedFieldAsRequired($validator))
      {
        $is_required = true;
      }
    }

    $labelName = $this->generateLabelName($name);
    if(false === $labelName)
    {
      return '';
    }

    if (!isset($attributes['for']) && $this->widgetSchema[$name]->isLabelable())
    {
      $attributes['for'] = $this->widgetSchema->generateId($this->widgetSchema->generateName($name));
    }

    if($is_required)
    {
      if(isset($attributes['required_label_class']))
      {
        $class = $attributes['required_label_class'];
        unset($attributes['required_label_class']);
      }
      else
      {
        $class = $this->requiredLabelCssClass;
      }

      $labelName = strtr($this->requiredLabelFormat, array(
        '%label_name%' => $labelName,
        '%css_class%'  => $class,
      ));

      // add class also to the label itself
      isset($attributes['class']) ? $attributes['class'] .= ' ' . $class :
        $attributes['class'] = $class;
    }

    if(sfWidget::isAriaEnabled() && !isset($attributes['id']) && isset($attributes['for']))
    {
      $attributes['id'] = sprintf('%s_label', $attributes['for']);
    }

    return $this->widgetSchema->renderContentTag('label', $labelName, $attributes);
  }

  /**
   * Checks if validator has required option set
   *
   * @param sfValidatorBase $validator
   * @return boolean
   */
  public function validatorMarkedFieldAsRequired(sfValidatorBase $validator)
  {
    if($validator->getOption('required'))
    {
      return true;
    }

    if($validator instanceof sfValidatorAnd || $validator instanceof sfValidatorOr)
    {
      foreach($validator->getValidators() as $validator)
      {
        if($this->validatorMarkedFieldAsRequired($validator))
        {
          return true;
        }
      }
    }

    return false;
  }

  /**
   * Used for displaying bellow the form
   * to the user what is used to display required fields
   * in the form
   *
   * @return string
   */
  public function getRequiredLabelMarkup()
  {
    return strtr($this->requiredLabelFormat, array(
      '%label_name%' => '',
      '%css_class%' => $this->requiredLabelCssClass));
  }

  /**
   * Generates the label name for the given field name.
   *
   * @param  string $name  The field name
   *
   * @return string The label name
   */
  public function generateLabelName($name)
  {
    $label = $this->widgetSchema->getLabel($name);

    if (!$label && false !== $label)
    {
      $label = str_replace('_', ' ', ucfirst('_id' == substr($name, -3) ? substr($name, 0, -3) : $name));
    }

    return $this->translate($label);
  }

  /**
   * Get i18n catalogue name
   *
   * @return string
   */
  public function getTranslationCatalogue()
  {
    return $this->translationCatalogue;
  }

  /**
   * Set an i18n catalogue name
   *
   * @param  string  $catalogue
   *
   * @throws InvalidArgumentException when the catalogue is not a string
   */
  public function setTranslationCatalogue($catalogue)
  {
    if (!is_string($catalogue))
    {
      throw new InvalidArgumentException('Catalogue name must be a string');
    }

    $this->translationCatalogue = $catalogue;
  }

  protected function unnestErrors($errors, $prefix = '', $widgetAttributes = array())
  {
    $newErrors = array();

    $attributes = array();
    foreach($widgetAttributes as $a => $v)
    {
      $attributes[] = sprintf('%s="%s"', $a, htmlspecialchars($v, ENT_NOQUOTES,
              sfWidget::getCharset(), false));
    }

    $attributes = join(' ', $attributes);

    foreach ($errors as $name => $error)
    {
      $fieldId = isset($widgetAttributes['id']) ? $widgetAttributes['id'] : '';
      if ($error instanceof ArrayAccess || is_array($error))
      {
        $newErrors = array_merge($newErrors,
                $this->unnestErrors($error, ($prefix ? $prefix.' > ' : '').$name),
                $widgetAttributes
                );
      }
      else
      {
        if ($error instanceof sfValidatorError)
        {
          $err = $this->translate($error->getMessageFormat(), $error->getArguments());
        }
        else
        {
          $err = $this->translate($error);
        }

        if (!is_integer($name))
        {
          $newErrors[] = strtr($this->getNamedErrorRowFormatInARow(),
                  array('%error%' => $err,
                        '%name%' => ($prefix ? $prefix.' > ' : '').$name,
                        '%field_id%' => $fieldId,
                        '%attributes%' => $attributes
                        ));
        }
        else
        {
          $newErrors[] = strtr($this->getErrorRowFormatInARow(),
                  array('%error%' => $err,
                        '%field_id%' => $fieldId,
                        '%attributes%' => $attributes
                      ));
        }
      }
    }

    return $newErrors;
  }

  public function setRowFormat($format)
  {
    $this->rowFormat = $format;
  }

  public function getRowFormat()
  {
    return $this->rowFormat;
  }

  public function setErrorRowFormat($format)
  {
    $this->errorRowFormat = $format;
  }

  public function getErrorRowFormat()
  {
    return $this->errorRowFormat;
  }

  public function setErrorListFormatInARow($format)
  {
    $this->errorListFormatInARow = $format;
  }

  public function getErrorListFormatInARow()
  {
    return $this->errorListFormatInARow;
  }

  public function setErrorRowFormatInARow($format)
  {
    $this->errorRowFormatInARow = $format;
  }

  public function getErrorRowFormatInARow()
  {
    return $this->errorRowFormatInARow;
  }

  public function setNamedErrorRowFormatInARow($format)
  {
    $this->namedErrorRowFormatInARow = $format;
  }

  public function getNamedErrorRowFormatInARow()
  {
    return $this->namedErrorRowFormatInARow;
  }

  public function setDecoratorFormat($format)
  {
    $this->decoratorFormat = $format;
  }

  public function getDecoratorFormat()
  {
    return $this->decoratorFormat;
  }

  public function setHelpFormat($format)
  {
    $this->helpFormat = $format;
  }

  public function getHelpFormat()
  {
    return $this->helpFormat;
  }

  /**
   * Sets the widget schema associated with this formatter instance.
   *
   * @param sfWidgetFormSchema $widgetSchema A sfWidgetFormSchema instance
   */
  public function setWidgetSchema(sfWidgetFormSchema $widgetSchema)
  {
    $this->widgetSchema = $widgetSchema;
  }

  /**
   * Returns sfWidgetFormSchema instance
   *
   * @return sfWidgetFormSchema
   */
  public function getWidgetSchema()
  {
    return $this->widgetSchema;
  }

  /**
   * Sets the widget schema associated with this formatter instance.
   *
   * @param sfValidatorSchema $validatorSchema A sfValidatorSchema instance
   */
  public function setValidatorSchema(sfValidatorSchema $validatorSchema)
  {
    $this->validatorSchema = $validatorSchema;
  }

  /**
   * Return sfValidatorSchema instance
   *
   * @return sfValidatorSchema
   */
  public function getValidatorSchema()
  {
    return $this->validatorSchema;
  }

  /**
   * Sets the validator for field $name.
   *
   * @param sfValidatorSchema $validatorSchema A sfValidatorSchema instance
   */
  public function setValidator($name, sfValidatorBase $validator)
  {
    $this->validators[$name] = $validator;
  }

  /**
   * Return sfValidatorBase instance or false
   *
   * @return sfValidatorBase
   */
  public function getValidator($name)
  {
    return $this->validators[$name];
  }

  /**
   * Return is validator for the field name exists in this decorator
   *
   * @return boolean
   */
  public function hasValidator($name)
  {
    return isset($this->validators[$name]);
  }

  /**
   * Returns CSS class for errors
   *
   * @return string
   */
  public function getErrorCssClass()
  {
    return $this->errorCssClass;
  }

  /**
   * Returns field grou format
   *
   * @return string
   */
  public function getFieldGroupFormat()
  {
    return $this->fieldGroupFormat;
  }

}
