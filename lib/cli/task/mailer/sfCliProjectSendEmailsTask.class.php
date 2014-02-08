<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Send emails stored in a queue.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfProjectSendEmailsTask extends sfCliBaseTask
{
  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCliCommandOption('application', null, sfCliCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCliCommandOption('env', null, sfCliCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCliCommandOption('message-limit', null, sfCliCommandOption::PARAMETER_OPTIONAL, 'The maximum number of messages to send', 0),
      new sfCliCommandOption('time-limit', null, sfCliCommandOption::PARAMETER_OPTIONAL, 'The time limit for sending messages (in seconds)', 0),
    ));

    $this->namespace = 'mailer';
    $this->name = 'send-emails';
    $this->aliases = array('flush-mail-queue');

    $this->briefDescription = 'Sends emails stored in a queue';

    $scriptName = $this->environment->get('script_name');

    $this->detailedDescription = <<<EOF
The [project:send-emails|INFO] sends emails stored in a queue:

  [{$scriptName} project:send-emails|INFO]

You can limit the number of messages to send:

  [{$scriptName} project:send-emails --message-limit=10|INFO]

Or limit to time (in seconds):

  [{$scriptName} project:send-emails --time-limit=10|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // we have to bind to an application
    if(!isset($options['application']))
    {
      $application = $this->getFirstApplication();
    }
    else
    {
      $application = $options['application'];
    }

    $env = $options['env'];

    $testFile = tempnam(sys_get_temp_dir(), 'prefetch');
    $rootDir  = $this->environment->get('sf_root_dir');
    $messageLimit = $options['message-limit'];
    $timeLimit = $options['time-limit'];

    $this->logSection($this->getFullName(), 'Preparing...');

    file_put_contents($testFile, <<<EOF
<?php
// This is a separated process to send mail in the queue

define('SF_ROOT_DIR',    '{$rootDir}');
define('SF_APP',         '{$application}');
define('SF_ENVIRONMENT', '{$env}');
define('SF_DEBUG',       true);

\$app_config = SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.
               DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php';

// disable error reporting, we need clean output
error_reporting(0);

require_once(\$app_config);

\$mailer = sfContext::getInstance()->getMailer();
try
{
  \$spool = \$mailer->getSpool();
  \$spool->setMessageLimit({$messageLimit});
  \$spool->setTimeLimit({$timeLimit});
  \$sent = \$mailer->flushQueue();
}
catch(LogicException \$e)
{
  echo 'SPOOL DISABLED';
  die(1);
}

echo \$sent;

EOF
);

    ob_start();
    passthru(sprintf('%s %s 2>&1', escapeshellarg($this->getPhpCli()), escapeshellarg($testFile)), $return);
    $result = ob_get_clean();

    // remove the file
    unlink($testFile);

    if($result == 'SPOOL DISABLED')
    {
      $this->logSection($this->getFullName(), 'Spool is disabled. Cannot send emails.');
      $this->logSection($this->getFullName(), 'Check your mail.yml configuration.');
    }
    elseif(preg_match('/\d+/', $result, $matches))
    {
      $this->logSection($this->getFullName(), sprintf('Done. Sent %s emails', $matches[0]));
    }
  }

}
