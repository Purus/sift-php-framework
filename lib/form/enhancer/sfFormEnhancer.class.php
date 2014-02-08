<?php
/*
 * This file is part of the Sift package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFormEnhancer enhances form widgets and validators with configured attributes.
 *
 * @package    Sift
 * @subpackage form_enhancer
 */
class sfFormEnhancer extends sfConfigurable implements sfIFormEnhancer {

  /**
   * Array of already enhanced forms
   *
   * @var array
   */
  protected $enhanced = array();

  /**
   * Rturns an instance of form enhancer
   *
   * @param string $class
   * @param array $options Array of options
   * @return sfIFormEnhancer
   * @throws InvalidArgumentException If enhancer is invalid
   */
  public static function factory($class, $options = array())
  {
    $enhancerClass = sprintf('sfFormEnhancer%s', ucfirst($class));

    if(class_exists($enhancerClass))
    {
      $enhancer = new $enhancerClass($options);
    }
    elseif(class_exists($class))
    {
      $enhancer = new $class($options);
    }
    else
    {
      throw new InvalidArgumentException(sprintf('Enhancer "%s" does not exist.', $class));
    }

    if(!$enhancer instanceof sfIFormEnhancer)
    {
      throw new InvalidArgumentException(sprintf('Form enhancer "%s" does not implement sfIFormEnhancer interface.', $class));
    }

    return $enhancer;
  }

  /**
   * Enhances any forms before they're passed to the template.
   *
   * @param  sfEvent $event
   * @param  array   $variables
   *
   * @return array
   */
  public function filterTemplateVariables(sfEvent $event, array $variables)
  {
    foreach($variables as $variable)
    {
      if($variable instanceof sfForm &&
              !in_array($variable, $this->enhanced, true))
      {
        $this->enhance($variable);
      }
    }
    return $variables;
  }

  /**
   * Enhances a form.
   *
   * @param sfForm $form
   */
  public function enhance(sfForm $form)
  {
    $this->enhanceFormFields($form, $form->getFormFieldSchema(), get_class($form), $form->getEmbeddedForms());
    $this->enhanced[] = $form;
  }

  /**
   * Enhances form fields.
   *
   * @param sfFormFieldSchema $fieldSchema    Form fields to enhance
   * @param string            $formClass      The name of the form class these fields are from
   * @param array             $embeddedForms  An array of forms embedded in the fields' form
   */
  protected function enhanceFormFields(sfForm $form, sfFormFieldSchema $fieldSchema, $formClass, array $embeddedForms = array())
  {
    // loop through the fields and apply the global configuration
    foreach($fieldSchema as $name => $field)
    {
      if($field instanceof sfFormFieldSchema)
      {
        if(isset($embeddedForms[$field->getName()]))
        {
          $embededForm = $embeddedForms[$field->getName()];
          $this->enhanceFormFields($embededForm, $form->getFormFieldSchema(), get_class($embededForm), $embededForm->getEmbeddedForms());
        }
        else
        {
          $this->enhanceFormFields($form, $field, $formClass);
        }
      }

      $validator = null;

      if($form->hasValidator($name))
      {
        $validator = $form->getValidator($name);
        $this->enhanceValidator($validator);

        if($validator instanceof sfValidatorSchema)
        {
          if($preValidator = $validator->getPreValidator())
          {
            $this->enhanceValidator($preValidator, true);
          }
          if($postValidator = $validator->getPostValidator())
          {
            $this->enhanceValidator($postValidator, true);
          }
        }
      }

      $this->enhanceWidget($field->getWidget(), $validator);

    } // end foreach fields

    // loop through the form's lineage and apply configuration
    foreach(self::getLineage($formClass) as $class)
    {
      foreach($this->getOption(sprintf('forms.%s', $class), array()) as $name => $params)
      {
        // catch pre and post validators
        if(preg_match('/^_(pre|post)_validator$/', $name, $match))
        {
          $method = 'get' . ucwords($match[1]) . 'Validator';
          if(($error = $fieldSchema->getError()) && ($validator = $error->getValidator()->$method()))
          {
            $validator->setMessages(array_merge($validator->getMessages(), $params));
          }
          continue;
        }

        // this is invalid
        if(!isset($fieldSchema[$name]))
        {
          throw new InvalidArgumentException(sprintf('Invalid field "%s". Cannot enhance the form "%s".', $name, $formClass));
        }

        $field = $fieldSchema[$name];
        $widget = $field->getWidget();
        $validator = $field->hasError() ? $field->getError()->getValidator() : null;

        if(isset($params['label']))
        {
          $widget->setLabel($params['label']);
        }

        if(isset($params['help']))
        {
          $fieldSchema->getWidget()->setHelp($name, $params['help']);
        }

        if($validator && isset($params['errors']))
        {
          $validator->setMessages(array_merge($validator->getMessages(), $params['errors']));
        }

        if(isset($params['attributes']))
        {
          foreach($params['attributes'] as $name => $value)
          {
            if('class' == $name)
            {
              // non-destructive
              $widget->setAttribute($name, trim(implode(' ', array_merge(explode(' ', $widget->getAttribute('class')), array($value)))));
            }
            else
            {
              $widget->setAttribute($name, $value);
            }
          }
        }
      }
    }
  }

  /**
   * Enhances a widget.
   *
   * @param sfWidget $widget
   * @param sfValidatorBase $validator
   */
  public function enhanceWidget(sfWidget $widget, sfValidatorBase $validator = null)
  {
    foreach(self::getLineage($widget) as $class)
    {
      $config = $this->getOption(sprintf('widgets.%s', $class));
      if(is_array($config))
      {
        if(!isset($config['options']) && !isset($config['attributes']))
        {
          $config = array('attributes' => $config);
        }

        $config = array_merge(array('options' => array(), 'attributes' => array()), $config);

        foreach($config['options'] as $name => $value)
        {
          $widget->setOption($name, $value);
        }

        foreach($config['attributes'] as $name => $value)
        {
          $value = $this->getValue($name, $value, $widget, $validator);

          // skip attribute
          if($value === false)
          {
            continue;
          }

          if('class' == $name)
          {
            // non-destructive
            $widget->setAttribute($name, implode(' ', array_merge(explode(' ', $widget->getAttribute('class')), array($value))));
          }
          else
          {
            $widget->setAttribute($name, $value);
          }

        }
      }
    }
  }

  /**
   * Returns "real" value for the $value. Replaces constants with modifieds
   * and callbacks
   *
   * @param string $name
   * @param string $value
   * @param sfWidget $widget
   * @param sfValidatorBase $validator
   * @return string
   */
  protected function getValue($name, $value, $widget, $validator)
  {
    if(preg_match('/\!\!callback:\s?(.*+)/', $value, $matches))
    {
      if(strpos($value, '->') !== false)
      {
        list($object, $method) = explode('->', $matches[1]);
      }
      // static method call myClass::method
      elseif(strpos($matches[1], '::') !== false)
      {
        list($object, $method) = explode('::', $matches[1]);
      }
      else
      {
        $object = '$this';
        $method = $matches[1];
      }

      // sanitize method name
      $method = rtrim($method, '()');

      $callback = false;

      switch($object)
      {
        case 'widget':
        case '$widget':
          $callback = array($widget, $method);
          break;

        case 'this':
        case '$this':
        case 'enhancer':
        case '$enhancer':

          $callback = array($this, $method);
          break;

        default:

          $callback = array($object, $method);
          break;
      }

      if($callback && !is_callable($callback, false, $callableName))
      {
        throw new sfConfigurationException(sprintf('Invalid callback "%s" given for "%s". Key: "%s"', $callableName, get_class($widget), $name));
      }

      $value = call_user_func_array($callback, array(
        $widget, $validator, $this
      ));
    }

    // replace placeholders with modifiers
    $value = sfToolkit::replaceConstantsWithModifiers($value);

    return $value;
  }

  /**
   * Enhances a validator.
   *
   * @param sfValidatorBase $validator
   * @param boolean         $recursive Enhance validator schema recursively
   */
  public function enhanceValidator(sfValidatorBase $validator, $recursive = false)
  {
    foreach(self::getLineage($validator) as $class)
    {
      $config = $this->getOption(sprintf('validators.%s', $class));
      if(is_array($config))
      {
        if(!isset($config['options']) && !isset($config['messages']))
        {
          $config = array('messages' => $config);
        }

        $config = array_merge(array('options' => array(), 'messages' => array()), $config);

        foreach($config['options'] as $name => $value)
        {
          $validator->setOption($name, $value);
        }

        foreach($config['messages'] as $code => $message)
        {
          $validator->setMessage($code, $message);
        }
      }
    }

    if($recursive && $validator instanceof sfValidatorSchema)
    {
      foreach($validator->getFields() as $v)
      {
        $this->enhanceValidator($v, $recursive);
      }
    }

    if(method_exists($validator, 'getValidators'))
    {
      foreach($validator->getValidators() as $v)
      {
        $this->enhanceValidator($v, $recursive);
      }
    }
  }

  /**
   * Returns an object's lineage.
   *
   * @param  string|object $class
   *
   * @return array
   */
  static public function getLineage($class)
  {
    if(is_object($class))
    {
      $class = get_class($class);
    }

    $classes = array();
    do
    {
      $classes[] = $class;
    }
    while($class = get_parent_class($class));

    $lineage = array_reverse($classes);

    return $lineage;
  }

}