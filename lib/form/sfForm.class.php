<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfForm represents a form.
 *
 * A form is composed of a validator schema and a widget form schema.
 *
 * sfForm also takes care of CSRF protection by default.
 *
 * A CSRF secret can be any random string. If set to false, it disables the
 * CSRF protection, and if set to null, it forces the form to use the global
 * CSRF secret. If the global CSRF secret is also null, then a random one
 * is generated on the fly.
 *
 * @package    Sift
 * @subpackage form
 */
class sfForm implements ArrayAccess, Iterator, Countable
{
  protected static
    $CSRFSecret         = false,
    $CSRFFieldName      = '_csrf_token',
    $toStringException  = null,
    $counter            = 0;

  protected
    $widgetSchema    = null,
    $validatorSchema = null,
    $errorSchema     = null,
    $formFieldSchema = null,
    $formFields      = array(),
    $isBound         = false,
    $taintedValues   = array(),
    $taintedFiles    = array(),
    $values          = null,
    $defaults        = array(),
    $fieldNames      = array(),
    $options         = array(),
    $count           = 0,
    $localCSRFSecret = null,
    $embeddedForms   = array(),
    $name            = '',
    $key             = '',
    $groups          = array(),
    $user            = null,
    $translationCatalogue = false;

  /**
   * Constructor.
   *
   * @param array  $defaults    An array of field default values
   * @param array  $options     An array of options
   * @param string $CSRFSecret  A CSRF secret
   */
  public function __construct($defaults = array(), $options = array(), $CSRFSecret = null)
  {
    $this->setDefaults($defaults);
    $this->options = $options;
    $this->localCSRFSecret = $CSRFSecret;

    $this->validatorSchema = new sfValidatorSchema();
    $this->widgetSchema = new sfWidgetFormSchema();
    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->key = 'form_' . ++self::$counter;

    $this->setup();
    $this->configure();

    // setup translation catalogue
    if($this->translationCatalogue)
    {
      $this->setTranslationCatalogue($this->translationCatalogue);
    }

    $this->addCSRFProtection($this->localCSRFSecret);
    $this->resetFormFields();

    if(sfContext::hasInstance())
    {
      // generic event
      sfContext::getInstance()->getEventDispatcher()->notify(new sfEvent('form.post_configure', array(
        'form' => $this
      )));

      // specific event for current class name
      sfContext::getInstance()->getEventDispatcher()->notify(new sfEvent(sprintf('form.%s.post_configure',
        sfInflector::tableize(get_class($this))), array(
          'form' => $this
      )));
    }
  }

  /**
   * Returns a string representation of the form.
   *
   * @return string A string representation of the form
   *
   * @see render()
   */
  public function __toString()
  {
    try
    {
      return $this->render();
    }
    catch (Exception $e)
    {
      self::setToStringException($e);

      // we return a simple Exception message in case the form framework is used out of Sift framework.
      return 'Exception: '.$e->getMessage();
    }
  }

  /**
   * Configures the current form.
   */
  public function configure()
  {
  }

  /**
   * Setups the current form.
   *
   * This method is overridden by generator.
   *
   * If you want to do something at initialization, you have to override the configure() method.
   *
   * @see configure()
   */
  public function setup()
  {
  }

  /**
   * Renders the widget schema associated with this form.
   *
   * @param  array  $attributes  An array of HTML attributes
   *
   * @return string The rendered widget schema
   */
  public function render($attributes = array())
  {
    $formatter = $this->widgetSchema->getFormFormatter();

    if(!is_null($formatter))
    {
      $formatter->setValidatorSchema($this->getValidatorSchema());
      if(sfConfig::get('sf_i18n') && ($catalogue = $this->getTranslationCatalogue()))
      {
        $formatter->setTranslationCallable('__');
        $formatter->setTranslationCatalogue($catalogue);
      }
    }

    return $this->getFormFieldSchema()->render($attributes);
  }

  /**
   * Sets translation catalogue to this form
   *
   * @param string $catalogue Catalogue name. Can use constants and can be module/catalogue pair
   * @return sfForm
   * @throws sfException
   */
  public function setTranslationCatalogue($catalogue)
  {
    if($catalogue)
    {
      $catalogue = sfToolkit::replaceConstants($catalogue);
      if(!sfToolkit::isPathAbsolute($catalogue))
      {
        // we have to do some detection
        $parts = explode('/', $catalogue);
        if(count($parts) != 2)
        {
          throw new sfException(sprintf(
            'Invalid translation catalogue "%s" given to the form "%s"',
                  $catalogue, get_class($this)));
        }
        $moduleName = $parts[0];
        $catalogueName = $parts[1];
        $catalogue = sfLoader::getI18NDir($moduleName) . '/' . $catalogueName;
      }
    }
    $this->translationCatalogue = $catalogue;
    return $this;
  }

  public function getTranslationCatalogue()
  {
    return $this->translationCatalogue;
  }

  /**
   * Translates the string using form formatter
   *
   * @param string $str
   * @param array $params
   * @return string
   */
  public function __($str, $params = array())
  {
    return $this->widgetSchema->getFormFormatter()->translate($str, $params);
  }

  /**
   * Alias for __
   *
   * @see __
   */
  public function translate($str, $params = array())
  {
    return $this->__($str, $params);
  }

  /**
   * Sets translation callable to the form formatter.
   * This is an equal method of calling
   *
   * sfWidgetFormSchemaFormatter::setTranslationCallable($callable);
   *
   * @param mixed|sfCallable $callable
   * @return
   */
  public function setTranslationCallable($callable)
  {
    return $this->widgetSchema->getFormFormatter()->setTranslationCallable($callable);
  }

  /**
   * Renders the widget schema using a specific form formatter
   *
   * @param  string  $formatterName  The form formatter name
   * @param  array   $attributes     An array of HTML attributes
   *
   * @return string The rendered widget schema
   */
  public function renderUsing($formatterName, $attributes = array())
  {
    $currentFormatterName = $this->widgetSchema->getFormFormatterName();

    $this->widgetSchema->setFormFormatterName($formatterName);

    $output = $this->render($attributes);

    $this->widgetSchema->setFormFormatterName($currentFormatterName);

    return $output;
  }

  /**
   * Renders hidden form fields.
   *
   * @param boolean $recursive False will prevent hidden fields from embedded forms from rendering
   *
   * @return string
   *
   * @see sfFormFieldSchema
   */
  public function renderHiddenFields($recursive = true)
  {
    return $this->getFormFieldSchema()->renderHiddenFields($recursive);
  }

  /**
   * Return HTML tag for submit button.
   *
   * @param string $value
   * @param array $attributes
   * @return string
   */
  public function renderSubmitTag($value = 'Submit', $attributes = array())
  {
    if(isset($this->submitButtonNames[$value]))
    {
      $value = $this->submitButtonNames[$value];
    }

    $attributes = array_merge(array(
                'name' => 'submit',
                'value' => 'submit',
                'type' => 'submit'), $attributes);
    return sprintf('<button%s><span>%s</span></button>', $this->getWidgetSchema()->attributesToHtml($attributes), $value);
  }

  protected $submitButtonNames = array();

  public function setSubmitButtonNames($names)
  {
    $this->submitButtonNames = $names;
  }

  /**
   * Renders global errors associated with this form.
   *
   * @return string The rendered global errors
   */
  public function renderGlobalErrors()
  {
    return $this->widgetSchema->getFormFormatter()->formatErrorsForRow($this->getGlobalErrors());
  }

  /**
   * Returns true if the form has some global errors.
   *
   * @return Boolean true if the form has some global errors, false otherwise
   */
  public function hasGlobalErrors()
  {
    return (boolean) count($this->getGlobalErrors());
  }

  /**
   * Gets the global errors associated with the form.
   *
   * @return array An array of global errors
   */
  public function getGlobalErrors()
  {
    return $this->widgetSchema->getGlobalErrors($this->getErrorSchema());
  }

  /**
   * Returns an array of errors. Its simply collects all errors
   * from all widgets from this form
   *
   * @return array
   * @link http://icodesnip.com/snippet/php/get-all-symfony-form-errors
   */
  public function getErrors()
  {
    $errors = array();

    // individual widget errors
    foreach($this as $form_field)
    {
      if($form_field->hasError())
      {
        $error_obj = $form_field->getError();
        // @var $error_obj sfValidatorError
        if($error_obj instanceof sfValidatorErrorSchema)
        {
          foreach($error_obj->getErrors() as $error)
          {
            // @var $error sfValidatorError
            // if a field has more than 1 error, it'll be over-written
            $errors[] = $this->translate($error->getMessageFormat(), $error->getArguments());
          }
        }
        else
        {
          $errors[] = $this->translate($error_obj->getMessageFormat(), $error_obj->getArguments());
        }
      }
    }

    // global errors
    foreach($this->getGlobalErrors() as $validator_error)
    {
      $errors[] = $this->translate($validator_error->getMessageFormat(), $validator_error->getArguments());
    }

    return $errors;
  }

  /**
   * Binds the form with input values.
   *
   * It triggers the validator schema validation.
   *
   * @param array $taintedValues  An array of input values
   * @param array $taintedFiles   An array of uploaded files (in the $_FILES or $_GET format)
   */
  public function bind(array $taintedValues = null, array $taintedFiles = null)
  {
    $this->taintedValues = $taintedValues;
    $this->taintedFiles  = $taintedFiles;
    $this->isBound = true;
    $this->resetFormFields();

    if (null === $this->taintedValues)
    {
      $this->taintedValues = array();
    }

    if (null === $this->taintedFiles)
    {
      if ($this->isMultipart())
      {
        throw new InvalidArgumentException('This form is multipart, which means you need to supply a files array as the bind() method second argument.');
      }

      $this->taintedFiles = array();
    }

    try
    {
      $this->doBind(self::deepArrayUnion($this->taintedValues, self::convertFileInformation($this->taintedFiles)));
      $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

      // remove CSRF token
      unset($this->values[self::$CSRFFieldName]);
    }
    catch (sfValidatorErrorSchema $e)
    {
      $this->values = array();
      $this->errorSchema = $e;
    }
  }

  /**
   * Binds the current form validate it in one step.
   *
   * @param sfRequest $request
   * @return boolean
   */
  public function bindAndValid(sfRequest $request)
  {
    return $this->bindRequest($request)->isValid();
  }

  /**
   * Binds request to the form
   *
   * @param sfRequest $request
   * @return sfForm
   */
  public function bindRequest(sfRequest $request)
  {
    $nameFormat = $this->widgetSchema->getNameFormat();

    $validatorSchema = $this->validatorSchema;
    $allowExtra = $validatorSchema->getOption('allow_extra_fields');

    // we don't have the values as array but separated values
    if($nameFormat === false || $nameFormat == '%s')
    {
      // we assume that we want to bind post parameters
      if($request->isPost())
      {
        $params = $request->getPostParameters();
      }
      else
      {
        $all = $request->getParameterHolder()->getAll();
        unset($all['module'], $all['action']);
        $params = array_merge($all, $request->getGetParameters());
      }
      $files = $request->getFiles();
    }
    else
    {
      $params = $request->getParameter($this->name);
      $files = $request->getFiles($this->name);
    }

    // filter out extra parameters, since the form does not allow it!
    // FIXME: This should be configured via method parameter
    if(!$allowExtra)
    {
      foreach($params as $p => $v)
      {
        if(!isset($this->widgetSchema[$p]))
        {
          unset($params[$p]);
        }
      }
    }

    $this->bind($params, $files);

    return $this;
  }

  /**
   * Cleans and binds values to the current form.
   *
   * @param array $values A merged array of values and files
   */
  protected function doBind(array $values)
  {
    // filters the values via event dispatcher
    $values = sfCore::getEventDispatcher()->filter(
              new sfEvent('form.filter_values',
                array('form' => $this)), $values)->getReturnValue();
    try
    {
      $this->values = $this->validatorSchema->clean($values);
      if($this->values)
      {
        foreach($this->values as $field => $value)
        {
          if(!isset($this[$field])) continue;
          $this[$field]->setValue($value);
        }
      }
    }
    catch (sfValidatorError $error)
    {
      sfCore::getEventDispatcher()->notify(new sfEvent('form.validation_error',
              array('error' => $error, 'form' => $this)));
      throw $error;
    }
  }

  /**
   * Returns true if the form is bound to input values.
   *
   * @return Boolean true if the form is bound to input values, false otherwise
   */
  public function isBound()
  {
    return $this->isBound;
  }

  /**
   * Returns the submitted tainted values.
   *
   * @return array An array of tainted values
   */
  public function getTaintedValues()
  {
    if (!$this->isBound)
    {
      return array();
    }

    return $this->taintedValues;
  }

  /**
   * Returns true if the form is valid.
   *
   * It returns false if the form is not bound.
   *
   * @return Boolean true if the form is valid, false otherwise
   */
  public function isValid()
  {
    if (!$this->isBound)
    {
      return false;
    }

    return 0 == count($this->errorSchema);
  }

  /**
   * Returns true if the form has some errors.
   *
   * It returns false if the form is not bound.
   *
   * @return Boolean true if the form has no errors, false otherwise
   */
  public function hasErrors()
  {
    if (!$this->isBound)
    {
      return false;
    }

    return count($this->errorSchema) > 0;
  }

  /**
   * Returns the array of cleaned values.
   *
   * If the form is not bound, it returns an empty array.
   *
   * @return array An array of cleaned values
   */
  public function getValues()
  {
    return $this->isBound ? $this->values : array();
  }

  /**
   * Returns a cleaned value by field name.
   *
   * If the form is not bound, it will return null.
   *
   * @param  string  $field  The name of the value required
   * @return string  The cleaned value
   */
  public function getValue($field)
  {
    return ($this->isBound && isset($this->values[$field])) ? $this->values[$field] : null;
  }

  /**
   * Sets the name of the form
   *
   * @param string $name
   * @return sfForm
   */
  public function setName($name)
  {
    $this->name = $name;
    if($name)
    {
      $this->setNameFormat($name . '[%s]');
    }
    return $this;
  }

  /**
   * Sets name format for the widgetSchema. This is just an alias
   * for $this->widgetSchema->setNameFormat()
   *
   * @param string|false $format
   * @return sfForm
   */
  public function setNameFormat($format)
  {
    $this->widgetSchema->setNameFormat($format);
    return $this;
  }

  /**
   * Gets name format for the widgetSchema. This is just an alias
   * for $this->widgetSchema->getNameFormat()
   *
   * @return string
   */
  public function getNameFormat()
  {
    return $this->widgetSchema->getNameFormat();
  }

  /**
   * Returns the array name under which user data can retrieved.
   *
   * If the user data is not stored under an array, it returns false.
   *
   * @return string|boolean The name or false if the name format is not an array format
   */
  public function getName()
  {
    if ('[%s]' != substr($nameFormat = $this->widgetSchema->getNameFormat(), -4))
    {
      return false;
    }

    return str_replace('[%s]', '', $nameFormat);
  }

  /**
   * Returns form key (used when no id given to the form)
   *
   * @return string
   */
  public function getKey()
  {
    return $this->key;
  }

  /**
   * Gets the error schema associated with the form.
   *
   * @return sfValidatorErrorSchema A sfValidatorErrorSchema instance
   */
  public function getErrorSchema()
  {
    return $this->errorSchema;
  }

  /**
   * Embeds a sfForm into the current form.
   *
   * @param string $name       The field name
   * @param sfForm $form       A sfForm instance
   * @param string $decorator  A HTML decorator for the embedded form
   */
  public function embedForm($name, sfForm $form, $decorator = null)
  {
    $name = (string) $name;
    if (true === $this->isBound() || true === $form->isBound())
    {
      throw new LogicException('A bound form cannot be embedded');
    }

    $this->embeddedForms[$name] = $form;

    $form = clone $form;
    unset($form[self::$CSRFFieldName]);

    $widgetSchema = $form->getWidgetSchema();

    $this->setDefault($name, $form->getDefaults());

    $decorator = null === $decorator ? $widgetSchema->getFormFormatter()->getDecoratorFormat() : $decorator;

    $this->widgetSchema[$name] = new sfWidgetFormSchemaDecorator($widgetSchema, $decorator);
    $this->validatorSchema[$name] = $form->getValidatorSchema();

    $this->resetFormFields();
  }

  /**
   * Embeds a sfForm into the current form n times.
   *
   * @param string  $name             The field name
   * @param sfForm  $form             A sfForm instance
   * @param integer $n                The number of times to embed the form
   * @param string  $decorator        A HTML decorator for the main form around embedded forms
   * @param string  $innerDecorator   A HTML decorator for each embedded form
   * @param array   $options          Options for schema
   * @param array   $attributes       Attributes for schema
   * @param array   $labels           Labels for schema
   */
  public function embedFormForEach($name, sfForm $form, $n, $decorator = null, $innerDecorator = null, $options = array(), $attributes = array(), $labels = array())
  {
    if (true === $this->isBound() || true === $form->isBound())
    {
      throw new LogicException('A bound form cannot be embedded');
    }

    $this->embeddedForms[$name] = new sfForm();

    $form = clone $form;
    unset($form[self::$CSRFFieldName]);

    $widgetSchema = $form->getWidgetSchema();

    // generate default values
    $defaults = array();
    for ($i = 0; $i < $n; $i++)
    {
      $defaults[$i] = $form->getDefaults();

      $this->embeddedForms[$name]->embedForm($i, $form);
    }

    $this->setDefault($name, $defaults);

    $decorator = null === $decorator ? $widgetSchema->getFormFormatter()->getDecoratorFormat() : $decorator;
    $innerDecorator = null === $innerDecorator ? $widgetSchema->getFormFormatter()->getDecoratorFormat() : $innerDecorator;

    $this->widgetSchema[$name] = new sfWidgetFormSchemaDecorator(new sfWidgetFormSchemaForEach(new sfWidgetFormSchemaDecorator($widgetSchema, $innerDecorator), $n, $options, $attributes), $decorator);
    $this->validatorSchema[$name] = new sfValidatorSchemaForEach($form->getValidatorSchema(), $n);

    // generate labels
    for ($i = 0; $i < $n; $i++)
    {
      if (!isset($labels[$i]))
      {
        $labels[$i] = sprintf('%s (%s)', $this->widgetSchema->getFormFormatter()->generateLabelName($name), $i);
      }
    }

    $this->widgetSchema[$name]->setLabels($labels);

    $this->resetFormFields();
  }

  /**
   * Gets the list of embedded forms.
   *
   * @return array An array of embedded forms
   */
  public function getEmbeddedForms()
  {
    return $this->embeddedForms;
  }

  /**
   * Returns an embedded form.
   *
   * @param  string $name The name used to embed the form
   *
   * @return sfForm
   *
   * @throws InvalidArgumentException If there is no form embedded with the supplied name
   */
  public function getEmbeddedForm($name)
  {
    if (!isset($this->embeddedForms[$name]))
    {
      throw new InvalidArgumentException(sprintf('There is no embedded "%s" form.', $name));
    }

    return $this->embeddedForms[$name];
  }

  /**
   * Merges current form widget and validator schemas with the ones from the
   * sfForm object passed as parameter. Please note it also merge defaults.
   *
   * @param  sfForm   $form      The sfForm instance to merge with current form
   *
   * @throws LogicException      If one of the form has already been bound
   */
  public function mergeForm(sfForm $form)
  {
    if (true === $this->isBound() || true === $form->isBound())
    {
      throw new LogicException('A bound form cannot be merged');
    }

    $form = clone $form;
    unset($form[self::$CSRFFieldName]);

    $this->defaults = $form->getDefaults() + $this->defaults;

    foreach ($form->getWidgetSchema()->getPositions() as $field)
    {
      $this->widgetSchema[$field] = $form->getWidget($field);
    }

    foreach ($form->getValidatorSchema()->getFields() as $field => $validator)
    {
      $this->validatorSchema[$field] = $validator;
    }

    $this->getWidgetSchema()->setLabels($form->getWidgetSchema()->getLabels() + $this->getWidgetSchema()->getLabels());
    $this->getWidgetSchema()->setHelps($form->getWidgetSchema()->getHelps() + $this->getWidgetSchema()->getHelps());

    $this->mergePreValidator($form->getValidatorSchema()->getPreValidator());
    $this->mergePostValidator($form->getValidatorSchema()->getPostValidator());

    $this->resetFormFields();
  }

  /**
   * Merges a validator with the current pre validators.
   *
   * @param sfValidatorBase $validator A validator to be merged
   */
  public function mergePreValidator(sfValidatorBase $validator = null)
  {
    if (null === $validator)
    {
      return;
    }

    if (null === $this->validatorSchema->getPreValidator())
    {
      $this->validatorSchema->setPreValidator($validator);
    }
    else
    {
      $this->validatorSchema->setPreValidator(new sfValidatorAnd(array(
        $this->validatorSchema->getPreValidator(),
        $validator,
      )));
    }
  }

  /**
   * Merges a validator with the current post validators.
   *
   * @param sfValidatorBase $validator A validator to be merged
   */
  public function mergePostValidator(sfValidatorBase $validator = null)
  {
    if (null === $validator)
    {
      return;
    }

    if (null === $this->validatorSchema->getPostValidator())
    {
      $this->validatorSchema->setPostValidator($validator);
    }
    else
    {
      $this->validatorSchema->setPostValidator(new sfValidatorAnd(array(
        $this->validatorSchema->getPostValidator(),
        $validator,
      )));
    }
  }

  /**
   * Sets the validators associated with this form.
   *
   * @param array $validators An array of named validators
   *
   * @return sfForm The current form instance
   */
  public function setValidators(array $validators)
  {
    $this->setValidatorSchema(new sfValidatorSchema($validators));

    return $this;
  }

  /**
   * Set a validator for the given field name.
   *
   * @param string          $name      The field name
   * @param sfValidatorBase $validator The validator
   *
   * @return sfForm The current form instance
   */
  public function setValidator($name, sfValidatorBase $validator)
  {
    $this->validatorSchema[$name] = $validator;

    $this->resetFormFields();

    return $this;
  }

  /**
   * Gets a validator for the given field name.
   *
   * @param  string      $name      The field name
   *
   * @return sfValidatorBase $validator The validator
   */
  public function getValidator($name)
  {
    if (!isset($this->validatorSchema[$name]))
    {
      throw new InvalidArgumentException(sprintf('The validator "%s" does not exist.', $name));
    }

    return $this->validatorSchema[$name];
  }

  /**
   * Sets validator's option for given field name to newValue.
   * If deep option is true sets also the value in subvalidators.
   *
   * @param string $name The field name
   * @param string $option The option name
   * @param mixed $newValue New value
   * @param boolean $deep Go deep in subvalidators?
   * @return sfForm
   */
  public function setValidatorOption($name, $option, $newValue, $deep = true)
  {
    $this->__setValidatorOption($this->getValidator($name), $option, $newValue, $deep);
    return $this;
  }

  /**
   * Sets validator $option to $value.
   *
   * @param sfValidatorBase $validator
   * @param string $option The option name
   * @param mixed $value The value
   * @param boolean $deep Go deep in subvalidators?
   */
  protected function __setValidatorOption(sfValidatorBase $validator, $option, $value, $deep = true)
  {
    if($validator->hasOption($option))
    {
      $validator->setOption($option, $value);
    }
    if($deep && method_exists($validator, 'getValidators'))
    {
      foreach($validator->getValidators() as $subValidator)
      {
        $this->__setValidatorOption($subValidator, $option, $value, $deep);
      }
    }
  }

  /**
   * Checks if given field has any validator assigned
   *
   * @param string $name The field name
   * @return boolean
   */
  public function hasValidator($name)
  {
    return isset($this->validatorSchema[$name]);
  }

  /**
   * Sets the validator schema associated with this form.
   *
   * @param sfValidatorSchema $validatorSchema A sfValidatorSchema instance
   *
   * @return sfForm The current form instance
   */
  public function setValidatorSchema(sfValidatorSchema $validatorSchema)
  {
    $this->validatorSchema = $validatorSchema;

    $this->resetFormFields();

    return $this;
  }

  /**
   * Gets the validator schema associated with this form.
   *
   * @return sfValidatorSchema A sfValidatorSchema instance
   */
  public function getValidatorSchema()
  {
    return $this->validatorSchema;
  }

  /**
   * Sets the widgets associated with this form.
   *
   * @param array $widgets An array of named widgets
   *
   * @return sfForm The current form instance
   */
  public function setWidgets(array $widgets)
  {
    $this->setWidgetSchema(new sfWidgetFormSchema($widgets));
    // prevent loosing form name
    $this->setName($this->name);
    return $this;
  }

  /**
   * Set a widget for the given field name.
   *
   * @param string       $name   The field name
   * @param sfWidgetForm $widget The widget
   *
   * @return sfForm The current form instance
   */
  public function setWidget($name, sfWidgetForm $widget)
  {
    $this->widgetSchema[$name] = $widget;

    $this->resetFormFields();

    return $this;
  }

  /**
   * Switch the widget for the given field name. Preserves help and labels
   * for previous widget.
   *
   * @param string       $name   The field name
   * @param sfWidgetForm $widget The widget
   * @param boolean      $throwException Throw exception if given field name does not exist?
   *
   * @return sfForm The current form instance
   * @throws InvalidArgumentException If given widget does not exist
   */
  public function switchWidget($name, sfWidgetForm $widget, $throwException = true)
  {
    if(!isset($this->widgetSchema[$name]) && $throwException)
    {
      throw new InvalidArgumentException(sprintf('The widget "%s" does not exist.', $name));
    }

    $help = $this->widgetSchema->getHelp($name);
    $label = $this->widgetSchema->getLabel($name);

    $this->widgetSchema[$name] = $widget;

    // put it back
    $this->setHelp($name, $help);
    $this->setLabel($name, $label);

    return $this;
  }

  /**
   * Sets label for the field.
   * Is is a shortcut method for $this->widgetSchema->setLabel()
   *
   * @param string $field
   * @param string $label
   * @return sfWidget The current widget instance
   */
  public function setLabel($field, $label)
  {
    $this->widgetSchema->setLabel($field, $label);
    return $this;
  }

  /**
   * Sets the help for the field
   * Is is a shortcut method for $this->widgetSchema->setHelp()
   *
   * @param string $field
   * @param string $help
   * @return sfWidget The current widget instance
   */
  public function setHelp($field, $help)
  {
    $this->widgetSchema->setHelp($field, $help);
    return $this;
  }

  /**
   * Sets the post validator. This is a shortcut method for
   *
   * @param sfValidatorBase $validator sfValidatorBase instance
   * @return sfForm
   */
  public function setFinalValidator(sfValidatorBase $validator)
  {
    $this->validatorSchema->setPostValidator($validator);
    return $this;
  }

  /**
   * Returns post validator
   *
   * @return sfValidatorBase
   */
  public function getFinalValidator()
  {
    return $this->validatorSchema->getPostValidator();
  }

  /**
   * Sets the pre validator.
   *
   * @param sfValidatorBase $validator  An sfValidatorBase instance
   * @return sfForm
   */
  public function setPreValidator(sfValidatorBase $validator)
  {
    $this->validatorSchema->setPreValidator($validator);
    return $this;
  }

  /**
   * Returns the pre validator.
   *
   * @return sfValidatorBase A sfValidatorBase instance
   */
  public function getPreValidator()
  {
    return $this->validatorSchema->preValidator;
  }

  /**
   * Gets a widget for the given field name.
   *
   * @param  string       $name      The field name
   *
   * @return sfWidgetForm $widget The widget
   */
  public function getWidget($name)
  {
    if (!isset($this->widgetSchema[$name]))
    {
      throw new InvalidArgumentException(sprintf('The widget "%s" does not exist.', $name));
    }

    return $this->widgetSchema[$name];
  }

  /**
   * Sets the widget schema associated with this form.
   *
   * @param sfWidgetFormSchema $widgetSchema A sfWidgetFormSchema instance
   *
   * @return sfForm The current form instance
   */
  public function setWidgetSchema(sfWidgetFormSchema $widgetSchema)
  {
    $this->widgetSchema = $widgetSchema;

    $this->resetFormFields();

    return $this;
  }

  /**
   * Gets the widget schema associated with this form.
   *
   * @return sfWidgetFormSchema A sfWidgetFormSchema instance
   */
  public function getWidgetSchema()
  {
    return $this->widgetSchema;
  }

  /**
   * Gets the stylesheet paths associated with the form.
   *
   * @return array An array of stylesheet paths
   */
  public function getStylesheets()
  {
    return $this->widgetSchema->getStylesheets();
  }

  /**
   * Gets the JavaScript paths associated with the form.
   *
   * @return array An array of JavaScript paths
   */
  public function getJavaScripts()
  {
    return $this->widgetSchema->getJavaScripts();
  }

  /**
   * Final javascript validation
   *
   * @param sfFormJavascriptValidationRulesCollection $rules Validation rules
   * @param sfFormJavascriptValidationMessagesCollection $messages Validation messages
   */
  public function getJavascriptFinalValidation(sfFormJavascriptValidationRulesCollection &$rules,
      sfFormJavascriptValidationMessagesCollection &$messages)
  {
  }

  /**
   * Returns javascript error placement function. Used by placing validation
   * errors using javascript. Can return sfJsonExpression object.
   *
   * @return string
   */
  public function getJavascriptErrorPlacement()
  {
    return false;
  }

  /**
   * Returns the current form's options.
   *
   * @return array The current form's options
   */
  public function getOptions()
  {
    return $this->options;
  }

  /**
   * Sets an option value.
   *
   * @param string $name  The option name
   * @param mixed  $value The default value
   *
   * @return sfForm The current form instance
   */
  public function setOption($name, $value)
  {
    $this->options[$name] = $value;

    return $this;
  }

  /**
   * Gets an option value.
   *
   * @param string $name    The option name
   * @param mixed  $default The default value (null by default)
   *
   * @param mixed  The default value
   */
  public function getOption($name, $default = null)
  {
    return isset($this->options[$name]) ? $this->options[$name] : $default;
  }

  /**
   * Sets a default value for a form field.
   *
   * @param string $name    The field name
   * @param mixed  $default The default value
   *
   * @return sfForm The current form instance
   */
  public function setDefault($name, $default)
  {
    $this->defaults[$name] = $default;

    $this->resetFormFields();

    return $this;
  }

  /**
   * Gets a default value for a form field.
   *
   * @param string $name The field name
   *
   * @param mixed  The default value
   */
  public function getDefault($name)
  {
    return isset($this->defaults[$name]) ? $this->defaults[$name] : null;
  }

  /**
   * Returns true if the form has a default value for a form field.
   *
   * @param string $name The field name
   *
   * @param Boolean true if the form has a default value for this field, false otherwise
   */
  public function hasDefault($name)
  {
    return array_key_exists($name, $this->defaults);
  }

  /**
   * Sets the default values for the form.
   *
   * The default values are only used if the form is not bound.
   *
   * @param array $defaults An array of default values
   *
   * @return sfForm The current form instance
   */
  public function setDefaults($defaults)
  {
    $this->defaults = null === $defaults ? array() : $defaults;

    if ($this->isCSRFProtected())
    {
      $this->setDefault(self::$CSRFFieldName, $this->getCSRFToken($this->localCSRFSecret ? $this->localCSRFSecret : self::$CSRFSecret));
    }

    $this->resetFormFields();

    return $this;
  }

  /**
   * Gets the default values for the form.
   *
   * @return array An array of default values
   */
  public function getDefaults()
  {
    return $this->defaults;
  }

  /**
   * Adds CSRF protection to the current form.
   *
   * @param string $secret The secret to use to compute the CSRF token
   *
   * @return sfForm The current form instance
   */
  public function addCSRFProtection($secret = null)
  {
    if (null === $secret)
    {
      $secret = $this->localCSRFSecret;
    }

    if (false === $secret || (null === $secret && false === self::$CSRFSecret))
    {
      return $this;
    }

    if (null === $secret)
    {
      if (null === self::$CSRFSecret)
      {
        self::$CSRFSecret = md5(__FILE__.php_uname());
      }

      $secret = self::$CSRFSecret;
    }

    $token = $this->getCSRFToken($secret);

    $this->validatorSchema[self::$CSRFFieldName] = new sfValidatorCSRFToken(array('token' => $token));
    $this->widgetSchema[self::$CSRFFieldName] = new sfWidgetFormInputHidden();
    $this->setDefault(self::$CSRFFieldName, $token);

    return $this;
  }

  /**
   * Removes protection of the form
   *
   * @return sfForm
   */
  public function removeCsrfProtection()
  {
    $this->localCSRFSecret = false;
    if ($this->isCSRFProtected())
    {
      unset($this[self::$CSRFFieldName]);
    }
    return $this;
  }

  /**
   * Returns a CSRF token, given a secret.
   *
   * If you want to change the algorithm used to compute the token, you
   * can override this method.
   *
   * @param  string $secret The secret string to use (null to use the current secret)
   *
   * @return string A token string
   */
  public function getCSRFToken($secret = null)
  {
    if (null === $secret)
    {
      $secret = $this->localCSRFSecret ? $this->localCSRFSecret : self::$CSRFSecret;
    }

    return md5($secret.session_id().get_class($this));
  }

  /**
   * @return true if this form is CSRF protected
   */
  public function isCSRFProtected()
  {
    return null !== $this->validatorSchema[self::$CSRFFieldName];
  }

  /**
   * Sets the CSRF field name.
   *
   * @param string $name The CSRF field name
   */
  static public function setCSRFFieldName($name)
  {
    self::$CSRFFieldName = $name;
  }

  /**
   * Gets the CSRF field name.
   *
   * @return string The CSRF field name
   */
  static public function getCSRFFieldName()
  {
    return self::$CSRFFieldName;
  }

  /**
   * Enables CSRF protection for this form.
   *
   * @param string $secret A secret to use when computing the CSRF token
   */
  public function enableLocalCSRFProtection($secret = null)
  {
    $this->localCSRFSecret = null === $secret ? true : $secret;
  }

  /**
   * Disables CSRF protection for this form.
   */
  public function disableLocalCSRFProtection()
  {
    $this->localCSRFSecret = false;
  }

  /**
   * Enables CSRF protection for all forms.
   *
   * The given secret will be used for all forms, except if you pass a secret in the constructor.
   * Even if a secret is automatically generated if you don't provide a secret, you're strongly advised
   * to provide one by yourself.
   *
   * @param string $secret A secret to use when computing the CSRF token
   */
  static public function enableCSRFProtection($secret = null)
  {
    self::$CSRFSecret = $secret;
  }

  /**
   * Disables CSRF protection for all forms.
   */
  static public function disableCSRFProtection()
  {
    self::$CSRFSecret = false;
  }

  /**
   * Returns true if the form is multipart.
   *
   * @return Boolean true if the form is multipart
   */
  public function isMultipart()
  {
    return $this->widgetSchema->needsMultipartForm();
  }

  /**
   * Opens the form with <form /> tag with given route and options
   *
   * @param string $route
   * @param array $opt
   * @return string
   */
  public function open($route = null, $opt = array())
  {
    sfLoader::loadHelpers('Url', 'Asset');

    $opt = sfInputFilters::toArray($opt);

    $class = array();
    // form has file upload
    if($this->hasFileUpload())
    {
      $class[] = 'has-file-upload';
    }

    if(isset($opt['class']))
    {
      $class[] = $opt['class'];
      unset($opt['class']);
    }

    $defaults = array(
      'class' => join(' ', $class),
      'id' => $this->getKey(),
      'anchor' => false
    );

    $opt = array_merge($defaults, $opt);
    $url = '';

    if(!empty($route))
    {
      $url = url_for($route);
    }

    if(isset($opt['query_string']))
    {
      $url .= '?' . $opt['query_string'];
      unset($opt['query_string']);
    }

    if(array_key_exists('anchor', $opt))
    {
      if(!empty($opt['anchor']) && strpos($action, '#') === false)
      {
        $action .= '#' . (is_string($opt['anchor']) ? $opt['anchor'] : $this->getKey());
      }
      unset($opt['anchor']);
    }

    if(!isset($opt['method']))
    {
      $opt['method'] = 'post';
    }

    if(!isset($opt['accept_charset']))
    {
      $opt['accept-charset'] = strtoupper(sfConfig::get('sf_charset'));
    }
    else
    {
      $opt['accept-charset'] = strtoupper($opt['accept_charset']);
      unset($opt['accept_charset']);
    }

    return $this->renderFormTag($url, $opt);
  }

  /**
   * Closes the form.
   *
   * @return string
   */
  public function close()
  {
    return '</form>';
  }

  /**
   * Renders submit tag
   *
   * @param string $value
   * @param array $attributes
   * @return string
   */
  public function submit($value = 'Submit', $attributes = array())
  {
    return $this->renderSubmitTag($value, $attributes);
  }

  /**
   * Renders the form tag.
   *
   * This methods only renders the opening form tag.
   * You need to close it after the form rendering.
   *
   * This method takes into account the multipart widgets
   * and converts PUT and DELETE methods to a hidden field
   * for later processing.
   *
   * @param  string $url         The URL for the action
   * @param  array  $attributes  An array of HTML attributes
   *
   * @return string An HTML representation of the opening form tag
   */
  public function renderFormTag($url, array $attributes = array())
  {
    $attributes['action'] = $url;
    $attributes['method'] = isset($attributes['method']) ? strtolower($attributes['method']) : 'post';
    if ($this->isMultipart())
    {
      $attributes['enctype'] = 'multipart/form-data';
    }

    $html = '';
    if (!in_array($attributes['method'], array('get', 'post')))
    {
      $html = $this->getWidgetSchema()->renderTag('input', array('type' => 'hidden', 'name' => 'sf_method', 'value' => $attributes['method'], 'id' => false));
      $attributes['method'] = 'post';
    }

    return sprintf('<form%s>', $this->getWidgetSchema()->attributesToHtml($attributes)).$html;
  }

  public function resetFormFields()
  {
    $this->formFields = array();
    $this->formFieldSchema = null;
  }

  /**
   * Returns true if the bound field exists (implements the ArrayAccess interface).
   *
   * @param  string $name The name of the bound field
   *
   * @return Boolean true if the widget exists, false otherwise
   */
  public function offsetExists($name)
  {
    return isset($this->widgetSchema[$name]);
  }

  /**
   * Returns the form field associated with the name (implements the ArrayAccess interface).
   *
   * @param  string $name  The offset of the value to get
   *
   * @return sfFormField   A form field instance
   */
  public function offsetGet($name)
  {
    if (!isset($this->formFields[$name]))
    {
      if (!$widget = $this->widgetSchema[$name])
      {
        throw new InvalidArgumentException(sprintf('Widget "%s" does not exist.', $name));
      }

      if ($this->isBound)
      {
        $value = isset($this->taintedValues[$name]) ? $this->taintedValues[$name] : null;
      }
      else if (isset($this->defaults[$name]))
      {
        $value = $this->defaults[$name];
      }
      else
      {
        $value = $widget instanceof sfWidgetFormSchema ? $widget->getDefaults() : $widget->getDefault();
      }

      $class = $widget instanceof sfWidgetFormSchema ? 'sfFormFieldSchema' : 'sfFormField';

      $this->formFields[$name] = new $class($this, $widget, $this->getFormFieldSchema(), $name, $value, $this->errorSchema[$name]);
      if(isset($this->validatorSchema[$name]))
      {
        $this->formFields[$name]->setValidator($this->validatorSchema[$name]);
      }
    }

    return $this->formFields[$name];
  }

  /**
   * Throws an exception saying that values cannot be set (implements the ArrayAccess interface).
   *
   * @param string $offset (ignored)
   * @param string $value (ignored)
   *
   * @throws LogicException
   */
  public function offsetSet($offset, $value)
  {
    throw new LogicException('Cannot update form fields.');
  }

  /**
   * Removes a field from the form.
   *
   * It removes the widget and the validator for the given field.
   *
   * @param string $offset The field name
   */
  public function offsetUnset($offset)
  {
    unset(
      $this->widgetSchema[$offset],
      $this->validatorSchema[$offset],
      $this->defaults[$offset],
      $this->taintedValues[$offset],
      $this->values[$offset],
      $this->embeddedForms[$offset]
    );

    $this->resetFormFields();
  }

  /**
   * Removes all visible fields from the form except the ones given as an argument.
   *
   * Hidden fields are not affected.
   *
   * @param array   $fields  An array of field names
   * @param Boolean $ordered Whether to use the array of field names to reorder the fields
   */
  public function useFields(array $fields = array(), $ordered = true)
  {
    $hidden = array();

    foreach ($this as $name => $field)
    {
      if ($field->isHidden())
      {
        $hidden[] = $name;
      }
      else if (!in_array($name, $fields))
      {
        unset($this[$name]);
      }
    }

    if ($ordered)
    {
      $this->widgetSchema->setPositions(array_merge($fields, $hidden));
    }
  }

  /**
   * Returns a form field for the main widget schema.
   *
   * @return sfFormFieldSchema A sfFormFieldSchema instance
   */
  public function getFormFieldSchema()
  {
    if (null === $this->formFieldSchema)
    {
      $values = $this->isBound ? $this->taintedValues : $this->defaults + $this->widgetSchema->getDefaults();

      $this->formFieldSchema = new sfFormFieldSchema($this, $this->widgetSchema, null, null, $values, $this->errorSchema);
    }

    return $this->formFieldSchema;
  }

  /**
   * Resets the field names array to the beginning (implements the Iterator interface).
   */
  public function rewind()
  {
    $this->fieldNames = $this->widgetSchema->getPositions();

    reset($this->fieldNames);
    $this->count = count($this->fieldNames);
  }

  /**
   * Gets the key associated with the current form field (implements the Iterator interface).
   *
   * @return string The key
   */
  public function key()
  {
    return current($this->fieldNames);
  }

  /**
   * Returns the current form field (implements the Iterator interface).
   *
   * @return mixed The escaped value
   */
  public function current()
  {
    return $this[current($this->fieldNames)];
  }

  /**
   * Moves to the next form field (implements the Iterator interface).
   */
  public function next()
  {
    next($this->fieldNames);
    --$this->count;
  }

  /**
   * Returns true if the current form field is valid (implements the Iterator interface).
   *
   * @return boolean The validity of the current element; true if it is valid
   */
  public function valid()
  {
    return $this->count > 0;
  }

  /**
   * Returns the number of form fields (implements the Countable interface).
   *
   * @return integer The number of embedded form fields
   */
  public function count()
  {
    return count($this->getFormFieldSchema());
  }

  /**
   * Converts uploaded file array to a format following the $_GET and $POST naming convention.
   *
   * It's safe to pass an already converted array, in which case this method just returns the original array unmodified.
   *
   * @param  array $taintedFiles An array representing uploaded file information
   *
   * @return array An array of re-ordered uploaded file information
   */
  static public function convertFileInformation(array $taintedFiles)
  {
    $files = array();
    foreach ($taintedFiles as $key => $data)
    {
      $files[$key] = self::fixPhpFilesArray($data);
    }

    return $files;
  }

  static protected function fixPhpFilesArray($data)
  {
    $fileKeys = array('error', 'name', 'size', 'tmp_name', 'type');
    $keys = array_keys($data);
    sort($keys);

    if ($fileKeys != $keys || !isset($data['name']) || !is_array($data['name']))
    {
      return $data;
    }

    $files = $data;
    foreach ($fileKeys as $k)
    {
      unset($files[$k]);
    }
    foreach (array_keys($data['name']) as $key)
    {
      $files[$key] = self::fixPhpFilesArray(array(
        'error'    => $data['error'][$key],
        'name'     => $data['name'][$key],
        'type'     => $data['type'][$key],
        'tmp_name' => $data['tmp_name'][$key],
        'size'     => $data['size'][$key],
      ));
    }

    return $files;
  }

  /**
   * Returns true if a form thrown an exception in the __toString() method
   *
   * This is a hack needed because PHP does not allow to throw exceptions in __toString() magic method.
   *
   * @return boolean
   */
  static public function hasToStringException()
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
  static public function getToStringException()
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
  static public function setToStringException(Exception $e)
  {
    if (null === self::$toStringException)
    {
      self::$toStringException = $e;
    }
  }

  public function __clone()
  {
    $this->widgetSchema    = clone $this->widgetSchema;
    $this->validatorSchema = clone $this->validatorSchema;

    // we rebind the cloned form because Exceptions are not clonable
    if ($this->isBound())
    {
      $this->bind($this->taintedValues, $this->taintedFiles);
    }
  }

  /**
   * Merges two arrays without reindexing numeric keys.
   *
   * @param array $array1 An array to merge
   * @param array $array2 An array to merge
   *
   * @return array The merged array
   */
  static protected function deepArrayUnion($array1, $array2)
  {
    foreach ($array2 as $key => $value)
    {
      if (is_array($value) && isset($array1[$key]) && is_array($array1[$key]))
      {
        $array1[$key] = self::deepArrayUnion($array1[$key], $value);
      }
      else
      {
        $array1[$key] = $value;
      }
    }

    return $array1;
  }

  /**
   * Adds group with $name and $label of $fields to the form
   *
   * @param string $name
   * @param string $label
   * @param arrray $fields
   * @param integer $priority
   * @return sfForm
   */
  public function addGroup($name, $label, $fields, $priority = 1)
  {
    $this->groups[$name] = new sfFormFieldGroup($this, $label, $fields, $priority);
    return $this;
  }

  /**
   * Removes field group and all fields associated with the group
   *
   * @param string $name Name of the group
   */
  public function removeGroup($name)
  {
    try {
      $group = $this->getGroup($name);
      foreach($group->getFields() as $f => $field)
      {
        unset($this[$f]);
      }
      // remove the group
      unset($this->groups[$name]);
    }
    catch(Exception $e)
    {
    }
  }

  /**
   * Has field group?
   *
   * @param string $name
   */
  public function hasGroup($name)
  {
    return isset($this->groups[$name]);
  }

  /**
   * Returns field group
   *
   * @param string $name
   * @return sfFormFieldGroup sfFormFieldGroup
   * @throws If there is no group with the supplied name
   */
  public function getGroup($name)
  {
    if(!isset($this->groups[$name]))
    {
      throw new InvalidArgumentException(sprintf('Group "%s" does not exist.', $name));
    }
    return $this->groups[$name];
  }

  /**
   * Renders given group
   *
   * @param string $name Name of the group to render
   * @return string
   */
  public function renderGroup($name)
  {
    return $this->getGroup($name)->render();
  }

  /**
   * Does this form have groups?
   *
   * @return boolean
   */
  public function hasGroups()
  {
    return count($this->groups) !== 0;
  }

  /**
   * Returns groups of fields
   *
   * @return array
   */
  public function getGroups($sort = true)
  {
    if($sort)
    {
      uasort($this->groups, array($this, '_sortGroups'));
    }
    return $this->groups;
  }

  /**
   * Changes $fielName to hidden
   *
   * @param string $fieldName
   * @return sfForm
   */
  public function changeToHidden($fieldName)
  {
    $this->widgetSchema[$fieldName] = new sfWidgetFormInputHidden();
    return $this;
  }

  /**
   * Disables $fieldName
   *
   * @param string $fieldName
   * @return sfForm
   */
  public function changeToDisabled($fieldName)
  {
    $this->widgetSchema[$fieldName]->setAttribute('disabled', 'disabled');
    if(sfWidget::isAriaEnabled())
    {
      $this->widgetSchema[$fieldName]->setAttribute('aria-disabled', 'true');
    }
    return $this;
  }

  /**
   * Changes $fieldName to readonly mode
   *
   * @param string $fieldName
   * @return sfForm
   */
  public function changeToReadOnly($fieldName)
  {
    $this->widgetSchema[$fieldName]->setAttribute('readonly', 'readonly');
    if(sfWidget::isAriaEnabled())
    {
      $this->widgetSchema[$fieldName]->setAttribute('aria-readonly', 'true');
    }
    return $this;
  }

  /**
   * Changes $fieldName to email
   *
   * @param string $fieldName
   * @return sfForm
   */
  public function changeToEmail($fieldName)
  {
    $this->validatorSchema[$fieldName] = new sfValidatorEmail($this->validatorSchema[$fieldName]->getOptions());
    return $this;
  }

  /**
   * Returns required star HTML code
   *
   * @return string
   */
  public function getRequiredLabelMarkup()
  {
    return $this->widgetSchema->getFormFormatter()->getRequiredLabelMarkup();
  }

  /**
   * Sorts groups by the priority
   *
   * @param sfFormFieldGroup $a
   * @param sfFormFieldGroup $b
   * @return integer 1 or -1
   */
  protected function _sortGroups($a, $b)
  {
    // we skip equal, which does not mess with the order the groups have been
    // added to the stack
    return $a->getPriority() > $b->getPriority() ? -1 : 1;
  }

  /**
   * Does this form have any file upload?
   *
   * @return boolean
   */
  public function hasFileUpload()
  {
    foreach($this as $field)
    {
      if($field->getWidget() instanceof sfWidgetFormInputFile)
      {
        return true;
      }
    }
    return false;
  }

  /**
   * Returns sfUser instance
   *
   * @return sfUser
   */
  public function getUser()
  {
    return $this->user ? $this->user : sfContext::getInstance()->getUser();
  }

  /**
   * Sets user to this form
   *
   * @param sfUser $user
   * @return myFormBase
   */
  public function setUser(sfUser $user)
  {
    $this->user = $user;
    return $this;
  }

  /**
   *
   * @return type
   */
  public function debug()
  {
    if(sfConfig::get('sf_environment') != 'dev')
    {
      return;
    }

    $string = '';
    // debug errors
    foreach($this->getErrorSchema()->getErrors() as $key => $error)
    {
      if($key == $this->getCSRFFieldName()) continue;
      $string .= '<p>' . $key . ': ' . $error . '</p>';
    }
    return $string;
  }


  /**
   * Calls methods defined via sfEventDispatcher.
   *
   * @param string $method    The method name
   * @param array  $arguments The method arguments
   *
   * @return mixed The returned value of the called method
   */
  public function __call($method, $arguments)
  {
    $event = sfCore::getEventDispatcher()->notifyUntil(
            new sfEvent('form.method_not_found',
                    array('method' => $method,
                          'arguments' => $arguments)));
    /* @var $event sfEvent */
    if($event->isProcessed())
    {
      return $event->getReturnValue();
    }
    throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
  }

}
