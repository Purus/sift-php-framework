<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * Extracts i18n strings from php files.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliI18nExtractFormTask extends sfCliBaseTask
{
  protected $formFiles = array();
  
  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCliCommandArgument('form', sfCliCommandArgument::REQUIRED, 'The form class name'),
      new sfCliCommandArgument('culture', sfCliCommandArgument::REQUIRED, 'The target culture'),
    ));

    $this->addOptions(array(
      new sfCliCommandOption('display-new', null, sfCliCommandOption::PARAMETER_NONE, 'Output all new found strings'),
      new sfCliCommandOption('display-old', null, sfCliCommandOption::PARAMETER_NONE, 'Output all old strings'),
      new sfCliCommandOption('auto-save', null, sfCliCommandOption::PARAMETER_NONE, 'Save the new strings'),
      new sfCliCommandOption('auto-delete', null, sfCliCommandOption::PARAMETER_NONE, 'Delete old strings'),
    ));

    $this->namespace = 'i18n';
    $this->name = 'extract-form';
    $this->briefDescription = 'Extracts i18n strings from a form';

    $scriptName = $this->environment->get('script_name');
    
    $this->detailedDescription = <<<EOF
The [i18n:extract|INFO] task extracts i18n strings from a form:

  [{$scriptName} i18n:extract-form myForm cs_CZ|INFO]

By default, the task only displays the number of new and old strings
it found in the form.

If you want to display the new strings, use the [--display-new|COMMENT] option:

  [{$scriptName} i18n:extract-form --display-new myForm cs_CZ|INFO]

To save them in the i18n message catalogue, use the [--auto-save|COMMENT] option:

  [{$scriptName} i18n:extract-form --auto-save myForm cs_CZ|INFO]

If you want to display strings that are present in the i18n messages
catalogue but are not found in the application (or plugin), use the 
[--display-old|COMMENT] option:

  [{$scriptName} i18n:extract-form --display-old myForm cs_CZ|INFO]

To automatically delete old strings, use the [--auto-delete|COMMENT]

  [{$scriptName} i18n:extract-form --auto-delete myForm cs_CZ|INFO]
EOF;
  }

  /**
   * @see sfCliTask
   */
  public function execute($arguments = array(), $options = array())
  {
    $form = $arguments['form'];
   
    // autoload forms
    $this->loadForms();
    
    // form name does not end with "Form"
    if(!preg_match('/Form$/i', $form))
    {
      $form .= 'Form';
    }
    
    $this->checkFormClass($form);

    $extract = new sfI18nFormExtract(array(
      'culture' => $arguments['culture'],
      'form'    => $form
    ));

    $extract->extract();
    
    $this->logSection('i18n', sprintf('found "%d" new i18n strings', $extract->getNewMessagesCount()));
    $this->logSection('i18n', sprintf('found "%d" old i18n strings', $extract->getOldMessagesCount()));
    
    if ($options['display-new'])
    {
      $this->logSection('i18n', sprintf('display new i18n strings', $extract->getNewMessagesCount()));
      foreach ($extract->getNewMessages() as $domain => $messages)
      {
        foreach($messages as $message)
        {
          $this->log('               '.$message."\n");
        }        
      }
    }

    if ($options['auto-save'])
    {
      $this->logSection('i18n', 'saving new i18n strings');

      $extract->saveNewMessages();
    }

    if ($options['display-old'])
    {
      $this->logSection('i18n', sprintf('display old i18n strings', $extract->getOldMessagesCount()));
      foreach($extract->getOldMessages() as $domain => $messages)
      {
        foreach($messages as $message)
        {
          $this->log('               '.$message."\n");
        }
      }
    }

    if ($options['auto-delete'])
    {
      $this->logSection('i18n', 'deleting old i18n strings');

      $extract->deleteOldMessages();
    }
    
  }
  
  /**
   * Checks if we can extract from the form
   * 
   * @param string $form
   * @throws sfCliCommandArgumentsException
   */
  protected function checkFormClass($formClass)
  {
    if(!class_exists($formClass))
    {
      throw new sfCliCommandArgumentsException(sprintf('Form "%s" does not exist', $formClass));
    }

    $class = new ReflectionClass($formClass);
    if($class->isAbstract())
    {
      throw new sfCliCommandArgumentsException(sprintf('Form "%s" is abstract. Cannot extract i18n strings.', $formClass));
    }    
  }
  
  /**
   * Loads all available tasks.
   *
   * Looks for tasks in the symfony core, the current project and all project plugins.
   *
   * @param sfProjectConfiguration $configuration The project configuration
   */
  public function loadForms()
  {
    // core forms
    $dirs = array($this->environment->get('sf_sift_lib_dir').'/lib/form');

    // application specific
    foreach(glob($this->environment->get('sf_root_dir').'/apps/*') as $path)
    {
      if(is_dir($formPath = $path.'/lib/form'))
      {
        $dirs[] = $formPath;
      }
    }
    
    // plugin tasks
    foreach(glob($this->environment->get('sf_root_dir').'/plugins/*') as $path)
    {
      if(is_dir($formPath = $path.'/lib/form'))
      {
        $dirs[] = $formPath;
      }
    }

    if(is_dir($taskPath = $this->environment->get('sf_root_dir').'/lib/form'))
    {
      $dirs[] = $taskPath;      
    }
    
    $dirs = array_unique($dirs);
    
    $finder = sfFinder::type('file')->name('*Form.class.php');
    
    foreach($finder->in($dirs) as $file)   
    {
      $this->formFiles[basename($file, '.class.php')] = $file;
    }
    
    // register local autoloader for tasks
    spl_autoload_register(array($this, 'autoloadForm'));
  }  
  
  /**
   * Autoloads a form class
   *
   * @param  string  $class  The form class name
   *
   * @return boolean
   */
  public function autoloadForm($class)
  {
    if(isset($this->formFiles[$class]))
    {
      require_once $this->formFiles[$class];
      return true;
    }
    return false;
  }
  
}
