<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFormJavascriptValidation class provides javascript validation for forms
 *
 * @package Sift
 * @subpackage form_javascript
 */
class sfFormJavascriptValidation {

  /**
   * Required
   */
  const REQUIRED = 'required';

  /**
   * Remote
   */
  const REMOTE   = 'remote';

  /**
   * Min length
   */
  const MIN_LENGTH = 'minlength';

  /**
   * Max length
   */
  const MAX_LENGTH = 'maxlength';

  /**
   * Range length
   */
  const RANGE_LENGTH = 'rangelength';

  /**
   * Minimum
   */
  const MIN = 'min';

  /**
   * Maximum
   */
  const MAX = 'max';

  /**
   * Range
   */
  const RANGE = 'range';

  /**
   * Email
   */
  const EMAIL = 'email';

  /**
   * Url
   */
  const URL   = 'url';

  /**
   * File extension
   */
  const FILE_EXTENSION = 'accept';

  /**
   * Number
   */
  const NUMBER = 'number';

  /**
   * Equal to
   */
  const EQUAL_TO = 'equalTo';

  /**
   * Digits
   *
   */
  const DIGITS = 'digits';

  /**
   * Credit card
   */
  const CREDIT_CARD = 'creditcard';

  /**
   * Min words
   */
  const MIN_WORDS   = 'minWords';

  /**
   * Max words
   */
  const MAX_WORDS   = 'maxWords';

  /**
   * Range words
   */
  const RANGE_WORDS = 'rangeWords';

  /**
   * File size
   */
  const FILE_SIZE   = 'fileSize';

  /**
   * Custom callback
   */
  const CUSTOM_CALLBACK = 'customCallback';

  /**
   * Not equal to
   */
  const NOT_EQUAL_TO = 'notEqualTo';

  /**
   * Regex pattern
   */
  const REGEX_PATTERN = 'regexPattern';

  /**
   * Regex negative pattern
   */
  const REGEX_PATTERN_NEGATIVE = 'regexPatternNegative';

  /**
   * Password strength
   */
  const PASSWORD_STRENGTH = 'passwordStrength';

  /**
   * Array of all valid options for jQuery validate plugin
   *
   * @var array
   * @see http://docs.jquery.com/Plugins/Validation/validate#options
   */
  protected static $validOptions = array(
    // form id
    'id',
    // Use this element type to create error messages and to look for existing
    // error messages. The default, "label", has the advantage of creating
    // a meaningful link between error message and invalid field using the
    // for attribute (which is always used, no matter the element type).
    // Default: "label"
    'error_element',

    // Use this class to create error labels, to look for existing error
    // labels and to add it to invalid elements.
    // Default: "form-error"
    'error_class',

    // This class is added to an element after it was validated and considered valid.
    // Default: "form-valid"
    'valid_class',

    // Hide and show this container when validating.
    // jQuery selector
    'error_container',

     // Wrap error labels with the specified element. Useful in combination
     // with errorLabelContainer to create a list of error messages.
    'wrapper',

     // Hide and show this container when validating.
     // jQuery selector
    'error_label_container',

    // A custom message display handler. Gets the map of errors as the first
    // argument and and array of errors as the second, called in the context
    // of the validator object. The arguments contain only those elements
    // currently validated, which can be a single element when doing
    // validation onblur/keyup. You can trigger
    // (in addition to your own messages) the default behaviour by
    // calling this.defaultShowErrors().
    'show_errors',

    'success',
    'highlight',
    'unhighlight',
    'ignore_title',

    // Callback for custom code when an invalid form is submitted.
    // Called with a event object as the first argument, and the
    // validator as the second.
    'invalid_handler',

    // Callback for handling the actual submit when the form is valid.
    // Gets the form as the only argument. Replaces the default submit.
    // The right place to submit a form via Ajax after its validated.
    'submit_handler',

    // Elements to ignore when validating, simply filtering them out.
    // jQuery's not-method is used, therefore everything that is accepted
    // by not() can be passed as this option. Inputs of type submit and
    // reset are always ignored, so are disabled elements.
    'ignore',

    'error_placement',

    // Validate the form on submit. Set to false to use only
    // other events for validation.
    //   Default: true
    'onsubmit',

    // Validate elements (except checkboxes/radio buttons) on blur.
    // If nothing is entered, all rules are skipped, except when the
    // field was already marked as invalid.
    // Default: true
    'onfocusout',

    // Validate elements on keyup. As long as the field is not marked as
    // invalid, nothing happens. Otherwise, all rules are checked
    // on each key up event.
    // Default: true
    'onkeyup',

    // Validate checkboxes and radio buttons on click.
    // Default: true
    'onclick',

    // Focus the last active or first invalid element on submit
    // via validator.focusInvalid(). The last active element is the one
    // that had focus when the form was submitted, avoiding to steal its focus.
    // If there was no element focused, the first one in the form gets it,
    // unless this option is turned off.
    // Default: true
    'focus_invalid',

    // If enabled, removes the errorClass from the invalid elements and hides
    // all errors messages whenever the element is focused. Avoid combination
    // with focusInvalid.
    // Default: false
    'focus_cleanup',

    // In case you use metadata for other plugins, too, you want to wrap your
    // validation rules into their own object that can be specified via
    // this option.
    'meta',

    // Enables debug mode. If true, the form is not submitted and certain
    // errors are displayed on the console (requires Firebug or Firebug lite).
    // Try to enable when a form is just submitted instead of validation
    // stopping the submit.
    'debug'
  );

  /**
   * Default options
   *
   * @var array
   */
  protected static $defaultOptions = array(
    'error_class' => 'form-error',
    'valid_class' => 'form-valid',
    'error_element' => 'label',
    'debug' => false,
    'focus_invalid' => false,
    'onkeyup' => false,
    // 'onclick' => false,
    'focus_cleanup' => true,
    // ignore elements which are marked with class ignore
    // or hidden inputs
    'ignore' => '.ignore,input[type="hidden"]'
  );

  /**
   * Generates javascript validation javascript code using jQuery plugin
   * "validate"
   *
   * @param sfForm $form
   * @param array $options
   * @param boolean $triggerValidation Trigger the validation programatically?
   * @return string
   * @see http://docs.jquery.com/Plugins/Validation
   */
  public static function getForForm(sfForm $form, $options = array(),
          $triggerValidation = false)
  {
    $options = sfInputFilters::toArray($options);
    $dispatcher = $form->getEventDispatcher();

    if(!isset($options['id']))
    {
      $options['id'] = $form->getKey();
    }

    $currentOptionKeys = array_keys(self::$validOptions);
    $optionKeys = array_keys($options);

    // check option names
    if($diff = array_diff($optionKeys, array_merge($currentOptionKeys, self::$validOptions)))
    {
      throw new InvalidArgumentException(sprintf('sfFormJavascriptValidation does not support the following options: \'%s\'.', implode('\', \'', $diff)));
    }

    $options = array_merge(self::$defaultOptions, $options);

    if(!isset($options['error_placement']))
    {
      try
      {
        $placement = $form->getJavascriptErrorPlacement();
        if($placement)
        {
          $options['error_placement'] = $placement;
        }
      }
      catch(sfException $e)
      {
      }
    }

    if(!isset($options['error_placement']))
    {
      $options['error_placement'] = self::getErrorPlacementExpression();

      if($dispatcher)
      {
        $options['error_placement'] = $dispatcher->filter(
            new sfEvent('form.javascript.validation.expression.error_placement'),
            $options['error_placement'])->getReturnValue();
      }
    }

    if(!isset($options['unhighlight']))
    {
      $options['unhighlight'] = self::getUnhighlightExpression();
      if($dispatcher)
      {
        $options['unhighlight'] = $dispatcher->filter(
            new sfEvent('form.javascript.validation.expression.unhighlight'),
            $options['unhighlight'])->getReturnValue();
      }
    }

    if(!isset($options['submit_handler']) && ($submitHandler = self::getSubmitHandlerExpression()))
    {
      $options['submit_handler'] = $submitHandler;
      if($dispatcher)
      {
        $options['submit_handler'] = $dispatcher->filter(
            new sfEvent('form.javascript.validation.expression.submit_handler'),
            $options['submit_handler'])->getReturnValue();
      }
    }

    $formId = $options['id'];

    list($rules, $messages) = self::getValidationRulesAndMessagesForForm($form);

    // we have no rules! nothing to do!
    if(!count($rules))
    {
      return '';
    }

    // validator javascript name
    $validatorJsVarName = sprintf('%sFormValidator',
            sfInflector::camelize(str_replace('-', '_', strtolower($formId))));
    $validatorJsVarName{0} = strtolower($validatorJsVarName{0});

    $js = array();

    // make the validator accessible from global context
    $js[] = sprintf('var %s;', $validatorJsVarName);

    $js[] = sprintf('Application.behaviors.setup%sFormValidation = function(context)',
              sfInflector::camelize(str_replace('-', '_', strtolower($formId))));
    $js[] = '{';
    $js[] = sprintf('  %s = $(\'#%s\').validate({', $validatorJsVarName, $formId);

    $js[] = sprintf('    rules: %s,', sfJson::encode($rules));
    $js[] = sprintf('    messages: %s,', sfJson::encode($messages));

    // valid options
    foreach(self::$validOptions as $option)
    {
      // skip options that are not set
      if(!isset($options[$option])) continue;

      switch($option)
      {
        case 'submit_handler':
          $js[] = (strpos($options[$option], 'function(') === false) ?
                    sprintf('    submitHandler: function(form) { %s },',
                      self::replacePlaceholders($options[$option], array(
                        'form_id' => $formId,
                      ))) :
                    sprintf('    submitHandler: %s ,',
                      self::replacePlaceholders(sfJson::encode($options[$option]), array(
                        'form_id' => $formId,
                      )));
        break;

        case 'invalid_handler':
          $js[] = (strpos($options[$option], 'function(') === false) ?
                    sprintf('    invalidHandler: function(e, validator) { %s },', $options[$option]) :
                    sprintf('    invalidHandler: %s ,', sfJson::encode($options[$option]));
        break;

        case 'error_placement':
          $js[] = (strpos($options[$option], 'function(') !== false
                    || ($options[$option] instanceof sfJsonExpression)) ?
                    sprintf('    errorPlacement: %s ,', sfJson::encode($options[$option])) :
                    sprintf('    errorPlacement: function(error, element) { %s },', $options[$option]);
        break;

        default:
          $option_name    = sfInflector::camelize($option);
          $option_name[0] = strtolower($option_name[0]);
          $js[] = sprintf('    %s: %s,', $option_name, sfJson::encode($options[$option]));
        break;
      }
    }

    // trim last comma
    $merged = rtrim(join("\n", $js), ',');

    $js   = array();
    $js[] = $merged;

    $js[] = '  });';

    if($triggerValidation)
    {
      $js[] = '// trigger the validation programatically';
      $js[] = sprintf('%s.form();', $validatorJsVarName);
    }

    // mark labels (or error elements) returned by server side
    // as generated by the validation plugin
    // and also as role="alert" for WAI ARIA support
    $js[] = sprintf('  $(\'%s\', $(\'#%s\')).attr(\'generated\', true)%s;',
            $options['error_element'], $formId,
            sfWidget::isAriaEnabled() ? ('.attr(\'role\', \'alert\')') : ''
    );

    $js[] = '};';

    return join("\n", $js);
  }

  /**
   * Retrieve validation rules and messages for the $form. Also includes validation
   * rules and messages for embedded forms. This is a recursive method.
   *
   * @param sfForm $form
   * @param string $embededFormName
   * @param sfForm $parentForm
   * @return array Array array(array $rules, array $messages)
   */
  public static function getValidationRulesAndMessagesForForm(sfForm $form,
          $embededFormName = null, sfForm $parentForm = null)
  {
    $rules = new sfFormJavascriptValidationRulesCollection();
    $messages = new sfFormJavascriptValidationMessagesCollection();

    // loop all fields
    foreach($form->getValidatorSchema()->getFields() as $field_name => $validator)
    {
      /* @var $validator sfWidgetValidator */
      // store original field_name
      $_fieldName = $field_name;

      // get the field names
      $field_name = sprintf($form->getWidgetSchema()->getNameFormat(), $field_name);

      // multiple values fix for checkboxes
      if($validator->hasOption('multiple')
          && $validator->getOption('multiple'))
      {
        $field_name .= '[]';
      }

      // we have to rewrite the name because its embeded in another form
      if($parentForm)
      {
        // we need to fix the field name if parent form is in deep array
        // due to form->embedFormForeach() which wraps the forms inside another sfForm form
        $field_name = str_replace(array('[[', ']]'), array('[', ']'),
                      sprintf($parentForm->getWidgetSchema()->getNameFormat(), $embededFormName)
                      . sprintf('[%s]', $_fieldName));
      }

      $skipToNext = false;

      // does validator know how to validate itself?
      if(is_callable(array($validator, 'getJavascriptValidationRules')))
      {
        $r = $validator->getJavascriptValidationRules();
        if($r && count($r))
        {
          $rules->append(new sfFormJavascriptValidationFieldRules($_fieldName, $field_name, $r));
        }
        $skipToNext = true;
      }

      // does validator know what validation messages to display?
      if(is_callable(array($validator, 'getJavascriptValidationMessages')))
      {
        $m = $validator->getJavascriptValidationMessages();
        if($m && count($m))
        {
          $messages->append(new sfFormJavascriptValidationFieldMessages($_fieldName, $field_name, $m));
        }
        $skipToNext = true;
      }

      if($validator instanceof sfValidatorSchemaForEach ||
         $validator instanceof sfValidatorSchema)
      {
        $skipToNext = true;
      }

      // continue to next field
      if($skipToNext)
      {
        continue;
      }
    }

    $embeded = $form->getEmbeddedForms();
    foreach($embeded as $embededFormName => $embededForm)
    {
      // this is a fix for embeding in foreach
      if(get_class($embededForm) == 'sfForm')
      {
        $embededForms = $embededForm->getEmbeddedForms();
        foreach($embededForms as $name => $embededForm2)
        {
          $embededFormName = sprintf('[%s][%s]', $embededFormName, $name);
          list($r, $m) = self::getValidationRulesAndMessagesForForm($embededForm2,
                    $embededFormName, $parentForm ? $parentForm : $form);
          $rules->merge($r);

          $messages->merge($m);
        }
      }
      else
      {
        list($r, $m) = self::getValidationRulesAndMessagesForForm($embededForm,
                  $embededFormName, $parentForm ? $parentForm : $form);
        $rules->merge($r);
        $messages->merge($m);
      }
    }

    // translation of messages
    foreach($messages as $field_name => $_field_messages)
    {
      foreach($_field_messages as $index => $message)
      {
        if($message->hasParameters())
        {
          $msg = new sfJsonExpression(sprintf("function(parameters, element) { return '%s'.replace('%%value%%', jQuery(element).val(), 'g'); }",
                        $form->translate($message->getMessage(), $message->getParameters())
                     ));
        }
        else
        {
          $msg = $form->translate($message->getMessage());
        }
        $message->setMessage($msg);
      }
    }

    // let the form do that it wants with the rules and messages
    $form->getJavascriptFinalValidation($rules, $messages);

    return array(
      $rules, $messages
    );
  }

  /**
   * Fixes validation message to be used by client side javascript. Replaces
   * all occurencies of %option% with its value
   *
   * @param sfValidatorBase $validator
   * @param string $message
   * @return string
   */
  public static function fixValidationMessage(sfValidatorBase $validator, $message)
  {
    $message = $validator->getMessage($message);

    // no placeholders found, simply return message
    if(strpos($message, '%') === false)
    {
      return $message;
    }

    // "%value%" is too long (%max_length% characters max).
    // match all %param%
    preg_match_all('/%[a-zA-Z_]+%/', $message, $matches);

    if(!count($matches))
    {
      return $message;
    }

    $params = array();
    foreach($matches[0] as $match)
    {
      $option = str_replace('%', '', $match);
      // skip value (it will be replaced by javascript by the input value)
      // skip also invalid options
      if($option == 'value' || !$validator->hasOption($option))
      {
        continue;
      }
      $params[$match] = $validator->getOption($option);
    }

    return new sfFormJavascriptValidationMessage($message, $params);
  }

  /**
   * Returns the default unhighlight expression
   *
   * @return sfJsonExpression
   */
  public static function getUnhighlightExpression()
  {
    $expression = new sfJsonExpression(sprintf(
'function(element, errorClass, validClass) {
  if(element.type === \'radio\')
  {
    public $element = this.findByName(element.name);
  }
  else
  {
    public $element = $(element);
  }

  var countInvalid = 0;
  for(i in this.invalid)
  {
    if(this.invalid.hasOwnProperty(i))
    {
      countInvalid++;
    }
  }

  var countSubmitted = 0;
  for(i in this.submitted)
  {
    if(this.submitted.hasOwnProperty(i))
    {
      countSubmitted++;
    }
  }

  var isInvalid = element.name in this.invalid;

  // validate action has not been performed on the form
  // return!
  if(!countInvalid && !countSubmitted)
  {
    return;
  }
  else if(!countInvalid && countSubmitted)
  {
    // already submitted as valid
    if(element.name in this.submitted)
    {
      isInvalid = false;
    }
  }

  if(!isInvalid)
  {
    $element.removeClass(errorClass).addClass(validClass)%s;

  }
  else
  {
    $(element).addClass(errorClass)%s;
  }

}', (sfWidgetForm::isAriaEnabled() ? '.removeAttr(\'aria-invalid\')' : ''),
    (sfWidgetForm::isAriaEnabled() ? '.attr(\'aria-invalid\', true)' : '')
  ));

    return $expression;
  }

  /**
   * Returns the default error_placement expression
   *
   *
   * @return sfJsonExpression
   */
  public static function getErrorPlacementExpression()
  {
    // base placement function
    // FIXME: javascript validation should know how is the input renderer
    // from form formatter!
    $placement = '
error.click(function(e)
{
  public $that = $(this);
  element.trigger(\'myfocus.from_error_label\', [$that]);
});
// place after element
var parent = element.parents(\'.form-field-wrapper:first\');
if(!parent.length)
{
  error.insertAfter(element);

  return;
}
error.appendTo(parent);
var offset = element.position();
var width = element.outerWidth();
var left = offset.left + width - error.outerWidth();
var top  = \'auto\';
error.css({
  top: top,
  left: Math.abs(left)
});
';

    if(sfWidgetForm::isAriaEnabled())
    {
      $expression = new sfJsonExpression(sprintf('function(error, element) {
          error.attr(\'role\', \'alert\');
          %s
      }', $placement));
    }
    else
    {
      $expression = new sfJsonExpression(sprintf('function(error, element) { %s }', $placement));
    }

    return $expression;
  }

  /**
   * Hook for rich editors to update the textareas here
   * before validation is performed on the inputs
   *
   * @link http://stackoverflow.com/questions/5126565/jquery-validation-of-textarea-integrated-with-ckeditor
   * @return sfJsonExpression
   */
  public static function getSubmitHandlerExpression()
  {
    $expression = '';

    return $expression;
  }

  /**
   * Replaces placeholders in given string
   *
   * @param string $string String containing placeholders
   * @param array $placeholders Associative Array of placeholder names and values
   * @return string
   */
  public static function replacePlaceholders($string, $placeholders)
  {
    $replacement = array();
    foreach($placeholders as $placeholder => $value)
    {
      $replacement[sprintf('%%%s%%', $placeholder)] = $value;
    }

    return strtr($string, $replacement);
  }

}
