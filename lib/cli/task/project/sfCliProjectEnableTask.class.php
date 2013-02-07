<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Enables an application in a given environment.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliProjectEnableTask extends sfCliBaseTask
{
  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCliCommandArgument('env', sfCliCommandArgument::REQUIRED, 'The environment name'),
      new sfCliCommandArgument('app', sfCliCommandArgument::OPTIONAL | sfCliCommandArgument::IS_ARRAY, 'The application name'),
    ));

    $this->namespace = 'project';
    $this->name = 'enable';
    $this->briefDescription = 'Enables an application in a given environment';

    $scriptName = $this->environment->get('script_name');
    
    $this->detailedDescription = <<<EOF
The [project:enable|INFO] task enables a specific environment:

  [{$scriptName} project:enable prod front|INFO]

You can also specify individual applications to be enabled in that
environment:

  [{$scriptName} project:enable prod front backend|INFO]
EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $applications = count($arguments['app']) ? $arguments['app'] : 
                    sfFinder::type('dir')->relative()->maxdepth(0)->in($this->environment->get('sf_apps_dir'));
    
    $env = $arguments['env'];

    foreach ($applications as $app)
    {
      $this->checkAppExists($app);
      
      $lockFile = $this->environment->get('sf_data_dir').'/'.$app.'_'.$env.'.lck';
      if (!file_exists($lockFile))
      {
        $this->logSection($this->getFullName(), sprintf('%s [%s] is currently ENABLED', $app, $env));
      }
      else
      {
        $this->getFilesystem()->remove($lockFile);
        
        $this->logSection($this->getFullName(), sprintf('%s [%s] has been ENABLED', $app, $env));
        
        $clearCache = new sfCliCacheClearTask($this->environment, $this->dispatcher, $this->formatter, $this->logger);
        $clearCache->setCommandApplication($this->commandApplication);
        $clearCache->run(array(), array('--app='.$app, '--env='.$env));        
      }
    }
  }
}
