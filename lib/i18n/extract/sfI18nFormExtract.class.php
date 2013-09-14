<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// define dummy translation function
if(!function_exists('__'))
{

  /**
   * Translate function. Leaves the message untouched.
   *
   * @param string $text
   * @param array $args
   * @param string $catalogue
   * @return string
   */
  function __($string, $args = array(), $catalogue = 'messages')
  {
    return $string;
  }

}

/**
 * Extracts string from application
 *
 * @package    Sift
 * @subpackage i18n_extract
 * @see http://trac.symfony-project.org/browser/plugins/sfI18nFormExtractorPlugin
 * @see http://snippets.symfony-project.org/snippet/342
 */
class sfI18nFormExtract extends sfI18nExtract {

  protected
          $form,
          $catalogueName,
          $cataloguePath,
          $content;

  protected $strings = array();

  /**
   * Array of required options
   *
   * @var array
   */
  protected $requiredOptions = array(
      'form',
  );

  public function configure()
  {
    $class = $this->getOption('form');

    $reflection = $this->checkForm($class);

    if($reflection->isSubclassOf('sfII18nExtractableForm'))
    {
      $this->form = call_user_func(array($class, '__construct_i18n'));
    }
    else 
    {
      $this->form = $reflection->newInstanceArgs();
    }

    // where do the translations sit?
    $catalogue = $this->form->getTranslationCatalogue();
    if(!$catalogue)
    {
      throw new sfException(sprintf('The form "%s" has no translation catalogue assigned.', $class));
    }

    $this->catalogue = $catalogue;
    $this->catalogueName = basename($catalogue);
    $this->cataloguePath = dirname($catalogue);

    if(!is_dir($this->cataloguePath))
    {
      throw new sfConfigurationException(sprintf('The form catalogue specifies the path "%s" which does not exist.', $this->cataloguePath));
    }

    // load form contents
    $this->content = file_get_contents($reflection->getFileName());

    // disable translation
    $this->form->setTranslationCatalogue(false);
    $formatter = $this->form->getWidgetSchema()->getFormFormatter();

    // disable internal translations
    if($formatter)
    {
      $formatter->setTranslationCallable(array($this, 'formTranslationCallable'));
      $formatter->setTranslationCatalogue('');
    }
  }

  /**
   * Checks if the given form extends sfForm and is initializable (can be constructed)
   *
   * @param string $class
   * @return ReflectionClass
   * @throws sfCliCommandArgumentsException
   */
  protected function checkForm($class)
  {
    $reflection = new ReflectionClass($class);

    if(!$reflection->isSubclassOf('sfForm') || $reflection->isAbstract())
    {
      throw new sfCliCommandArgumentsException(sprintf('Form "%s" is not an instance of sfForm.', $class));
    }

    // this is not an extractable form
    if(!$reflection->isSubclassOf('sfII18nExtractableForm'))
    {
      // check if we can contruct the form,
      // how do the __contructor arguments look like?
      // are the optional or array based?
      $constructor = $reflection->getConstructor();
      $parameters = $constructor->getParameters();
      $cannotCreate = false;
      foreach($parameters as $parameter)
      {
        if($parameter->isOptional())
        {
          continue;
        }
        if(!$parameter->isArray())
        {
          $cannotCreate = true;
          break;
        }
      }
      if($cannotCreate)
      {
        throw new sfException(sprintf('The form "%s" cannot be extracted. Constructor arguments disallow standard way of extraction. Please implement sfII18nExtractable interface to the form.', $class));
      }
    }
    return $reflection;
  }

  /**
   * Extracts i18n strings.
   *
   */
  public function extract()
  {
    // empty
    $this->strings = array();

    // extract from file
    $extractor = new sfI18nPhpExtractor();

    // be carefull this contains strings
    // grouped by the domain
    $extractedMessages = $extractor->extract($this->content);

    foreach($extractedMessages as $domain => $messages)
    {
      if($domain === sfI18nExtract::UNKNOWN_DOMAIN)
      {
        $domain = $this->catalogue;
      }

      foreach($messages as $message)
      {
        $this->strings[$domain][] = $message;
      }
    }

    $this->form->setUser(new sfI18nExtractLoggedInUser());

    // reconfigure the form without translations
    // as logged in user with all credentials
    $this->form->configure();
    $this->form->setup();

    $this->processLabels();
    $this->processHelp();
    $this->processGroups();
    $this->processValues();
    $this->registerErrorMessages();

    // Extract again for anonymous user
    $this->form->setUser(new sfI18nExtractAnonymousUser());
    // reconfigure the form without translations
    // as anonymous user
    $this->form->configure();
    $this->form->setup();

    $this->processLabels();
    $this->processHelp();
    $this->processGroups();
    $this->processValues();
    $this->registerErrorMessages();

    foreach($this->strings as $domain => $strings)
    {
      foreach($strings as $id => $string)
      {
        if(empty($string))
        {
          unset($strings[$id]);
        }
      }
      $this->strings[$domain] = array_unique($strings);
    }

    return $this->strings;
  }

  private function registerErrorMessages()
  {
    $field_list = $this->form->getValidatorSchema()->getFields();
    foreach($field_list as $field)
    {
      $this->merge($field);
    }
    $this->merge($this->form->getValidatorSchema()->getPostValidator());
    $this->merge($this->form->getValidatorSchema()->getPreValidator());
  }

  private function merge($field)
  {
    if(!$field)
    {
      return;
    }
    if(method_exists($field, 'getActiveMessages') && method_exists($field, 'getValidators'))
    {
      $this->strings[$this->catalogue] = array_merge($this->strings[$this->catalogue], $field->getActiveMessages());
      foreach($field->getValidators() as $f)
      {
        $this->merge($f);
      }
    }
    elseif(method_exists($field, 'getActiveMessages'))
    {
      $this->strings[$this->catalogue] = array_merge($this->strings[$this->catalogue], $field->getActiveMessages());
    }
  }

  private function processLabels()
  {
    $labels = $this->form->getWidgetSchema()->getLabels();
    foreach($labels as $key => $value)
    {
      $this->strings[$this->catalogue][] = $value;
    }
  }

  private function processGroups()
  {
    $groups = $this->form->getGroups();
    foreach($groups as $group)
    {
      if($label = $group->getLabel())
      {
        $this->strings[$this->catalogue][] = $label;
      }
    }
  }

  private function processValuesValue($value)
  {
    if(is_array($value))
    {
      foreach($value as $vkey => $vvalue)
      {
        $this->processValuesValue($vvalue);
      }
    }
    else
    {
      $this->strings[$this->catalogue][] = $value;
    }
  }

  private function processValues()
  {
    $widgetSchema = $this->form->getWidgetSchema()->getFields();
    foreach($widgetSchema as $name => $widget)
    {
      if($widget instanceof sfWidgetFormChoiceBase)
      {
        // translate only if allowed
        if(!$widget->getOption('translate_choices'))
        {
          continue;
        }
        foreach($widget->getChoices() as $key => $value)
        {
          $this->processValuesValue($value);
        }
      }
    }
  }

  private function processHelp()
  {
    $helps = $this->form->getWidgetSchema()->getHelps();
    foreach($helps as $key => $value)
    {
      if(empty($value))
      {
        $this->strings[$this->catalogue][] = $key;
      }
      else
      {
        $this->strings[$this->catalogue][] = $value;
      }
    }
  }

  /**
   * Callble for the form. Returns the message untouched.
   *
   * @param string $message
   * @param array $parameters
   * @return string
   */
  public function formTranslationCallable($message, $parameters = array())
  {
    return $message;
  }

}
