<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Generates the crypt key for an application.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliGenerateCryptKeyTask extends sfCliBaseTask
{
  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCliCommandArgument('app', sfCliCommandArgument::OPTIONAL, 'The application name'),
    ));

    $this->namespace = 'security';
    $this->name = 'generate-crypt-key';
    $this->aliases = array('gencryptkey');

    $this->briefDescription = 'Generates crypt key (requires Openssl)';

    $scriptName = $this->environment->get('script_name');

    $this->detailedDescription = <<<EOF
The [security:generate-crypt-key|INFO] task generates security key for project or application.
(Requires Openssl)

  [{$scriptName} security:generate-crypt-key|INFO]

You can specify an [application|COMMENT]:

  [{$scriptName} security:generate-crypt-key front|INFO]
EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $filesystem = $this->getFilesystem();

    $app = false;
    if(isset($arguments['app']))
    {
      $this->checkAppExists($arguments['app']);
      $app = $arguments['app'];
    }

    $this->logSection($this->getFullName(), 'Generating crypt key...');

    try
    {
      $command = 'openssl rand -base64 2048';
      $commandOutput = $filesystem->execute($command);
      $newKey = $commandOutput[0];
    }
    catch(RuntimeException $e)
    {
      throw $e;
    }

    // safety check
    if(strlen(base64_decode($newKey)) !== 2048)
    {
      throw new sfException('Generated key has incorrect size, aborting.');
    }

    // we are generating key for application
    if($app)
    {
      $keyFilePath = $this->environment->get('sf_apps_dir') .
                     '/' . $app . '/' . $this->environment->get('sf_config_dir_name')
                     . '/crypt.key';
    }
    else
    {
      $keyFilePath = $this->environment->get('sf_root_dir') . '/' .
                    $this->environment->get('sf_config_dir_name') . '/crypt.key';
    }

    $keyFileExists = file_exists($keyFilePath);

    if($keyFileExists)
    {
      $backupFileName = 'crypt.key.' . time() . '.backup';
      $this->logSection($this->getFullName(), 'Key already exists. Moving old to backup.');
      $filesystem->rename($keyFilePath, dirname($keyFilePath).'/'.$backupFileName);
    }

    $keyFileHandle = fopen($keyFilePath, 'w');
    fwrite($keyFileHandle, $newKey);
    fclose($keyFileHandle);

    // change permission of newly created file to read for everyone
    $filesystem->chmod($keyFilePath, 0444);

    $this->logSection($this->getFullName(), 'Done.');
  }

}
