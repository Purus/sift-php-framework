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
class sfCliI18nExtractTask extends sfCliI18nBaseTask
{
  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCliCommandArgument('app', sfCliCommandArgument::REQUIRED, 'The application or plugin name'),
      new sfCliCommandArgument('culture', sfCliCommandArgument::REQUIRED, 'The target culture'),
    ));

    $this->addOptions(array(
      new sfCliCommandOption('display-new', null, sfCliCommandOption::PARAMETER_NONE, 'Output all new found strings'),
      new sfCliCommandOption('display-old', null, sfCliCommandOption::PARAMETER_NONE, 'Output all old strings'),
      new sfCliCommandOption('auto-save', null, sfCliCommandOption::PARAMETER_NONE, 'Save the new strings'),
      new sfCliCommandOption('auto-delete', null, sfCliCommandOption::PARAMETER_NONE, 'Delete old strings'),
      new sfCliCommandOption('connection', null, sfCliCommandOption::PARAMETER_OPTIONAL, 'Connection name', 'mock'),
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
   * @see sfCliTask
   */
  public function execute($arguments = array(), $options = array())
  {
    list($application, $dir, $isPlugin) = $this->getApplicationOrPlugin($arguments['app']);

    $culture = $arguments['culture'];
    if($isPlugin)
    {
      $this->logSection($this->getFullName(), sprintf('Extracting i18n strings for the "%s" plugin ("%s")', $application, $culture));
    }
    else
    {
      $this->logSection($this->getFullName(), sprintf('Extracting i18n strings for the "%s" application ("%s")', $application, $culture));
    }

    // we need to create the context first
    // because its setting current connection to the default one
    $this->createContextInstance($this->getFirstApplication());

    // clears events
    sfCore::getEventDispatcher()->clear();

    if($options['connection'])
    {
      $connection = $this->getDatabase($options['connection']);
      $connection->connect();
    }

    $extract = new sfI18nApplicationExtract(array(
        'app_dir' => $dir,
        'root_dir' => $this->environment->get('sf_root_dir'),
        'culture' => $culture
    ));

    // do the extraction
    $extract->extract();

    $this->logSection($this->getFullName(), sprintf('Found "%d" new i18n strings', $extract->getNewMessagesCount()));
    $this->logSection($this->getFullName(), sprintf('Found "%d" old i18n strings', $extract->getOldMessagesCount()));

    if ($options['display-new'])
    {
      $this->logSection($this->getFullName(), sprintf('display new i18n strings', $extract->getNewMessagesCount()));
      $found = 0;
      foreach ($extract->getNewMessages() as $domain => $messages)
      {
        if(count($messages))
        {
          $this->logSection($this->getFullName(), 'Domain: '. $this->replacePaths($domain));
        }
        foreach($messages as $message)
        {
          $this->log('                '.$message);
          $found++;
        }
      }
      if(!$found)
      {
        $this->logSection($this->getFullName(), 'No new messages to be displayed.');
      }
    }

    if ($options['auto-save'])
    {
      $this->logSection($this->getFullName(), 'Saving new i18n strings');

      $extract->saveNewMessages();
    }

    if ($options['display-old'])
    {
      $this->logSection($this->getFullName(), sprintf('display old i18n strings', $extract->getOldMessagesCount()));
      $found = 0;
      foreach($extract->getOldMessages() as $domain => $messages)
      {
        if(count($messages))
        {
          $this->logSection($this->getFullName(), 'Domain: ' . $this->replacePaths($domain));
        }
        foreach($messages as $message)
        {
          $this->log('                '.$message);
          $found++;
        }
      }

      if(!$found)
      {
        $this->logSection($this->getFullName(), 'No old messages to be displayed.');
      }
    }

    if ($options['auto-delete'])
    {
      $this->logSection($this->getFullName(), 'Deleting old i18n strings');
      $extract->deleteOldMessages();
    }

  }

  /**
   * Replaces path to be display in the console
   *
   * @param string $path
   * @return string
   */
  protected function replacePaths($path)
  {
    return str_replace(array(
      $this->environment->get('sf_plugins_dir'),
      $this->environment->get('sf_root_dir')
    ), array(
      '%SF_PLUGINS_DIR%',
      '%SF_ROOT_DIR%'
    ), $path);
  }
}
