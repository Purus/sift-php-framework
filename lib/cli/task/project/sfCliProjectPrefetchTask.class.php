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
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addArguments(array(
        new sfCliCommandArgument('app', sfCliCommandArgument::OPTIONAL | sfCliCommandArgument::IS_ARRAY, 'The application name'),
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
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $applications = $arguments['app'];

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

    $sf_sift_lib_dir = $this->environment->get('sf_sift_lib_dir');
    $sf_sift_data_dir = $this->environment->get('sf_sift_data_dir');
    
    foreach($options['hostname'] as $hostname)
    {
      $testFile = tempnam(sys_get_temp_dir(), 'prefetch');
      file_put_contents($testFile, <<<EOF
<?php
// This is a separated process to prefetch the application

define('SF_ROOT_DIR', '$rootDir');
require_once(SF_ROOT_DIR.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php');
require_once \$sf_sift_lib_dir.'/autoload/sfCoreAutoload.class.php';    
sfCoreAutoload::register();

sfCore::bootstrap(\$sf_sift_lib_dir, \$sf_sift_data_dir);

// hackish way of doing things :)
\$_SERVER['SERVER_NAME'] = '$hostname';
  
sfContext::createInstance(
  sfCore::getApplication('$application', 'prod', false)
);

\$uris = array('/');
\$browser = new sfPrefetchBrowser('$hostname', '$remoteAddress');
  
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
        var_dump($result);
        $this->logSection($this->getFullName(), 'Error prefetching app.', null, 'ERROR');
      }
    }
  }

}
