<?php
/*
 * This file is part of the Sift package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFormEnhancer enhanced form widgets and validators with configured attributes.
 *
 * @package    Sift
 * @subpackage form
 */
class sfFormEnhancer extends sfConfigurable {

  /**
   * Array of already enhanced forms
   *
   * @var array
   */
  protected $enhanced = array();

  /**
   * Constructs the object
   *
   * @param array $options Array of options
   */
  public function __construct($options = array())
  {
    parent::__construct($options);
  }

  /**
   * Enhances a form.
   *
   * @param sfForm $form
   */
  public function enhance(sfForm $form)
  {
    $this->enhanceFormFields($form->getFormFieldSchema(), get_class($form), $form->getEmbeddedForms());
    $this->enhanced[] = $form;
  }

  /**
   * Enhances form fields.
   *
   * @param sfFormFieldSchema $fieldSchema    Form fields to enhance
   * @param string            $formClass      The name of the form class these fields are from
   * @param array             $embeddedForms  An array of forms embedded in the fields' form
   */
  protected function enhanceFormFields(sfFormFieldSchema $fieldSchema, $formClass, array $embeddedForms = array())
  {
    // loop through the fields and apply the global configuration
    foreach($fieldSchema as $field)
    {
      if($field instanceof sfFormFieldSchema)
      {
        if(isset($embeddedForms[$field->getName()]))
        {
          $form = $embeddedForms[$field->getName()];
          $this->enhanceFormFields($field, get_class($form), $form->getEmbeddedForms());
        }
        else
        {
          $this->enhanceFormFields($field, $formClass);
        }
      }

      $this->enhanceWidget($field->getWidget());

      if($field->hasError())
      {
        $validator = $field->getError()->getValidator();

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
    }

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
   */
  public function enhanceWidget(sfWidget $widget)
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