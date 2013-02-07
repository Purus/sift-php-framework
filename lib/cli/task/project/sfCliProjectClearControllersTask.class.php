<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Clears all non production environment controllers.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliProjectClearControllersTask extends sfCliBaseTask {

  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->namespace = 'project';
    $this->name = 'clear-controllers';
    $this->briefDescription = 'Clears all non production environment controllers';

    $scriptName = $this->environment->get('script_name');

    $this->detailedDescription = <<<EOF
The [project:clear-controllers|INFO] task clears all non production environment
controllers:

  [{$scriptName} project:clear-controllers|INFO]

You can use this task on a production server to remove all front
controller scripts except the production ones.

If you have two applications named [front|COMMENT] and [admin|COMMENT],
you have four default controller scripts in [web/|COMMENT]:

  [index.php
  front_dev.php
  admin.php
  admin_dev.php|INFO]

After executing the [project:clear-controllers|COMMENT] task, two front
controller scripts are left in [web/|COMMENT]:

  [index.php
  admin.php|INFO]

Those two controllers are safe because debug mode and the web debug
toolbar is disabled.
EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $finder = sfFinder::type('file')->maxdepth(1)->name('*.php');
    foreach($finder->in($this->environment->get('sf_web_dir')) as $controller)
    {
      $content = file_get_contents($controller);
      preg_match('/\'SF_APP\',[\s]*\'(.*)\'\)/', $content, $found_app);
      preg_match('/\'SF_ENVIRONMENT\',[\s]*\'(.*)\'\)/', $content, $env);

      if(isset($found_app[1]) && isset($env[1]) && $env[1] != 'prod')
      {
        $this->logSection($this->getFullName(), sprintf('Clear "%s" (%s, %s environment)', 
                          basename($controller), 
                          $found_app[1], $env[1]));
        $this->getFilesystem()->remove($controller);
      }
    }
  }

}
