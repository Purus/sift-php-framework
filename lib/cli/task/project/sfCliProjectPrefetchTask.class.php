<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Deploys a project to another server.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliProjectPrefetchTask extends sfCliBaseTask {

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
        new sfCliCommandArgument('application', sfCliCommandArgument::OPTIONAL | sfCliCommandArgument::IS_ARRAY, 'The application name'),
    ));

    $this->addOptions(array(
        new sfCliCommandOption('hostname', 'h', sfCliCommandOption::PARAMETER_OPTIONAL | sfCliCommandOption::IS_ARRAY, 'Hostname'),
        new sfCliCommandOption('remote-addr', 'r', sfCliCommandOption::PARAMETER_OPTIONAL, 'Remote address', '127.0.0.1')
    ));

    $this->namespace = 'project';
    $this->name = 'prefetch';
    $this->briefDescription = 'Prefetches the application in production environment';

    $scriptName = $this->environment->get('script_name');

    $this->detailedDescription = <<<EOF
The [project:prefetch|INFO] prefetches the application in production environment:

  [{$scriptName} project:prefetch front|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $applications = $arguments['application'];

    if(count($applications))
    {
      foreach($applications as $application)
      {
        $this->checkAppExists($application);
      }
    }
    else
    {
      // find applications
      $applications = sfFinder::type('dir')->maxdepth(0)
                        ->relative()->in($this->environment->get('sf_apps_dir'));
    }

    if(!count($options['hostname']))
    {
      $options['hostname'][] = 'localhost';
    }

    foreach($applications as $application)
    {
      $this->logSection($this->getFullName(), sprintf('Prefetching "%s"', $application));
      $this->prefetchApplication($application, $options);
    }

    $this->logSection($this->getFullName(), 'Done.');
  }

  protected function prefetchApplication($application, $options)
  {
    $rootDir = $this->environment->get('sf_root_dir');

    $remoteAddress = $options['remote-addr'];

    foreach($options['hostname'] as $hostname)
    {
      $testFile = tempnam(sys_get_temp_dir(), 'prefetch');
      file_put_contents($testFile, <<<EOF
<?php
// This is a separated process to prefetch the application

define('SF_ROOT_DIR',    '$rootDir');
define('SF_APP',         '$application');
define('SF_ENVIRONMENT', 'prod');
define('SF_DEBUG',       false);

\$app_config = SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.
               DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php';

// hackish way of doing things :)
\$_SERVER['SERVER_NAME'] = '$hostname';

require_once(\$app_config);
  
\$uris = array('/');
\$browser = new sfBrowser('$hostname', '$remoteAddress');
foreach(\$uris as \$uri)
{
  \$browser->get(\$uri);
}

echo 'OK';
EOF
      );

      ob_start();
      passthru(sprintf('%s %s 2>&1', escapeshellarg($this->getPhpCli()), escapeshellarg($testFile)), $return);
      $result = ob_get_clean();
      unlink($testFile);

      if($result == 'OK')
      {
        $this->logSection($this->getFullName(), 'Prefetching ok.');
      }
      else
      {
        $this->logSection($this->getFullName(), 'Error prefetching app.', null, 'ERROR');
      }
    }
  }

}
