<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Launches functional tests.
 *
 * @package Sift
 * @subpackage cli_task
 */
class sfCliTestFunctionalTask extends sfCliTestBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCliCommandArgument('application', sfCliCommandArgument::REQUIRED, 'The application name'),
      new sfCliCommandArgument('controller', sfCliCommandArgument::OPTIONAL | sfCliCommandArgument::IS_ARRAY, 'The controller name'),
    ));

    $this->addOptions(array(
      new sfCliCommandOption('xml', null, sfCliCommandOption::PARAMETER_REQUIRED, 'The file name for the JUnit compatible XML log file'),
    ));

    $this->namespace = 'test';
    $this->name = 'functional';
    $this->briefDescription = 'Launches functional tests';

    $scriptName = $this->environment->get('script_name');

    $this->detailedDescription = <<<EOF
The [test:functional|INFO] task launches functional tests for a
given application:

  [$scriptName test:functional frontend|INFO]

The task launches all tests found in [test/functional/%application%|COMMENT].

If some tests fail, you can use the [--trace|COMMENT] option to have more
information about the failures:

  [$scriptName test:functional frontend -t|INFO]

You can launch all functional tests for a specific controller by
giving a controller name:

  [$scriptName test:functional frontend article|INFO]

You can also launch all functional tests for several controllers:

  [$scriptName test:functional frontend article comment|INFO]

The task can output a JUnit compatible XML log file with the [--xml|COMMENT]
options:

  [$scriptName test:functional --xml=log.xml|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $app = $arguments['application'];

    if (count($arguments['controller']))
    {
      $files = array();

      foreach ($arguments['controller'] as $controller)
      {
        $finder = sfFinder::type('file')->followLink()->name(basename($controller).'Test.php');
        $files = array_merge($files, $finder->in($this->environment->get('sf_test_dir').'/functional/'.$app.'/'.dirname($controller)));
      }

      if($allFiles = $this->filterTestFiles($files, $arguments, $options))
      {
        foreach ($allFiles as $file)
        {
          include($file);
        }
      }
      else
      {
        $this->logSection('functional', 'no controller found', null, 'ERROR');
      }
    }
    else
    {
      require_once dirname(__FILE__).'/sfLimeHarness.class.php';

      $h = new sfLimeHarness(array(
        'force_colors' => isset($options['color']) && $options['color'],
        'verbose'      => isset($options['trace']) && $options['trace'],
      ));

      $project = $this->commandApplication->getProject();

      $h->addPlugins(array_map(array($project, 'getPlugins'), $project->getPlugins()));

      $h->base_dir = $this->environment->get('sf_test_dir').'/functional/'.$app;

      // filter and register functional tests
      $finder = sfFinder::type('file')->followLink()->name('*Test.php');
      $h->register($this->filterTestFiles($finder->in($h->base_dir), $arguments, $options));

      $ret = $h->run() ? 0 : 1;

      if ($options['xml'])
      {
        file_put_contents($options['xml'], $h->to_xml());
      }

      return $ret;
    }
  }
}