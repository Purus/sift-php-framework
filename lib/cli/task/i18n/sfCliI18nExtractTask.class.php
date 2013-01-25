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
class sfCliI18nExtractTask extends sfCliBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCliCommandArgument('application', sfCliCommandArgument::REQUIRED, 'The application or plugin name'),
      new sfCliCommandArgument('culture', sfCliCommandArgument::REQUIRED, 'The target culture'),
    ));

    $this->addOptions(array(
      new sfCliCommandOption('display-new', null, sfCliCommandOption::PARAMETER_NONE, 'Output all new found strings'),
      new sfCliCommandOption('display-old', null, sfCliCommandOption::PARAMETER_NONE, 'Output all old strings'),
      new sfCliCommandOption('auto-save', null, sfCliCommandOption::PARAMETER_NONE, 'Save the new strings'),
      new sfCliCommandOption('auto-delete', null, sfCliCommandOption::PARAMETER_NONE, 'Delete old strings'),
    ));

    $this->namespace = 'i18n';
    $this->name = 'extract';
    $this->briefDescription = 'Extracts i18n strings from application or plugin';

    $scriptName = $this->environment->get('script_name');
    
    $this->detailedDescription = <<<EOF
The [i18n:extract|INFO] task extracts i18n strings from your project files
for the given application (or plugin) and target culture:

  [{$scriptName} i18n:extract front cs_CZ|INFO]

By default, the task only displays the number of new and old strings
it found in the current project.

If you want to display the new strings, use the [--display-new|COMMENT] option:

  [{$scriptName} i18n:extract --display-new front cs_CZ|INFO]

To save them in the i18n message catalogue, use the [--auto-save|COMMENT] option:

  [{$scriptName} i18n:extract --auto-save front cs_CZ|INFO]

If you want to display strings that are present in the i18n messages
catalogue but are not found in the application (or plugin), use the 
[--display-old|COMMENT] option:

  [{$scriptName} i18n:extract --display-old front cs_CZ|INFO]

To automatically delete old strings, use the [--auto-delete|COMMENT]

  [{$scriptName} i18n:extract --auto-delete front cs_CZ|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    $application = $arguments['application'];
    
    $plugin = false;
    
    // this is a plugin
    if(preg_match('|Plugin$|', $application))
    {
      $this->checkPluginExists($application);
      $plugin = $application;
      $dir = $this->environment->get('sf_plugins_dir') . '/' . $plugin;
    }
    else
    {
      $this->checkAppExists($application);
      $dir = $this->environment->get('sf_apps_dir') . '/' . $application;
    }
    
    $culture = $arguments['culture'];

    if($plugin)
    {
      $this->logSection($this->getFullName(), sprintf('Extracting i18n strings for the "%s" plugin ("%s")', $plugin, $culture));
    }
    else
    {
      $this->logSection($this->getFullName(), sprintf('Extracting i18n strings for the "%s" application ("%s")', $application, $culture));
    }
      
    $extract = new sfI18nApplicationExtract(array(
        'app_dir' => $dir,
        'root_dir' => $this->environment->get('sf_root_dir'),
        'culture' => $culture
    ));
      
    // do the extraction
    $extract->extract();

    $this->logSection('i18n', sprintf('found "%d" new i18n strings', $extract->getNewMessagesCount()));
    $this->logSection('i18n', sprintf('found "%d" old i18n strings', $extract->getOldMessagesCount()));

    if ($options['display-new'])
    {
      $this->logSection('i18n', sprintf('display new i18n strings', $extract->getNewMessagesCount()));
      foreach ($extract->getNewMessages() as $domain => $messages)
      {
        if(count($messages))
        {
          // $this->log('               '.$domain."\n");
        }
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
        if(count($messages))
        {
          // $this->log('               '.$domain."\n");
        }
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
