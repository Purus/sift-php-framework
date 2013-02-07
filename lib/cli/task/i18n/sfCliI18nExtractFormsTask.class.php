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
class sfCliI18nExtractFormsTask extends sfCliI18nExtractFormTask
{
  protected $formFiles = array();
  
  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCliCommandArgument('application', sfCliCommandArgument::REQUIRED, 'The application or plugin name'),
      new sfCliCommandArgument('culture', sfCliCommandArgument::REQUIRED, 'The target culture'),
    ));

    $this->addOptions(array(
      new sfCliCommandOption('exclude-global', null, sfCliCommandOption::PARAMETER_NONE, 'Exclude project wide forms?'),
      new sfCliCommandOption('display-new', null, sfCliCommandOption::PARAMETER_NONE, 'Output all new found strings'),
      new sfCliCommandOption('display-old', null, sfCliCommandOption::PARAMETER_NONE, 'Output all old strings'),
      new sfCliCommandOption('auto-save', null, sfCliCommandOption::PARAMETER_NONE, 'Save the new strings'),
      new sfCliCommandOption('auto-delete', null, sfCliCommandOption::PARAMETER_NONE, 'Delete old strings'),
    ));

    $this->namespace = 'i18n';
    $this->name = 'extract-forms';
    $this->briefDescription = 'Extracts i18n strings from forms from an application or plugin';

    $scriptName = $this->environment->get('script_name');
    
    $this->detailedDescription = <<<EOF
The [i18n:extract|INFO] task extracts i18n strings from a form:

  [{$scriptName} i18n:extract-forms myForm cs_CZ|INFO]

By default, the task only displays the number of new and old strings
it found in the form.

If you want to display the new strings, use the [--display-new|COMMENT] option:

  [{$scriptName} i18n:extract-forms --display-new myForm cs_CZ|INFO]

To save them in the i18n message catalogue, use the [--auto-save|COMMENT] option:

  [{$scriptName} i18n:extract-forms --auto-save myForm cs_CZ|INFO]

If you want to display strings that are present in the i18n messages
catalogue but are not found in the application (or plugin), use the 
[--display-old|COMMENT] option:

  [{$scriptName} i18n:extract-forms --display-old myForm cs_CZ|INFO]

To automatically delete old strings, use the [--auto-delete|COMMENT]

  [{$scriptName} i18n:extract-forms --auto-delete myForm cs_CZ|INFO]
EOF;
  }

  /**
   * @see sfCliTask
   */
  public function execute($arguments = array(), $options = array())
  {
    $application = $arguments['application'];
   
    // autoload all forms
    $this->loadForms();

    $this->checkAppExists($application);

    $dirs = array();
    
    // find all form in the application
    $finder = sfFinder::type('file')->name('*Form.class.php');
    
    // application specific forms
    if(is_dir($formPath = $this->environment->get('sf_apps_dir').'/' . $application . '/lib/form'))
    {
      $dirs[] = $formPath;      
    }
    
    if(!$options['exclude-global'])
    {
      if(is_dir($formPath = $this->environment->get('sf_root_dir').'/lib/form'))
      {
        $dirs[] = $formPath;      
      }
    }
    
    $dirs = array_unique($dirs);    
    $finder = sfFinder::type('file')->name('*Form.class.php');
    
    foreach($finder->in($dirs) as $file)   
    {
      $form = basename($file, '.class.php');
      
      $this->checkFormClass($form);
      
      $this->logSection($this->getFullName(), sprintf('Extracting strings from "%s"', $form));
      
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
    
  }
  
}
