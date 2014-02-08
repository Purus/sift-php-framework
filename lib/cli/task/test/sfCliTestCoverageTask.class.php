<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Outputs test code coverage.
 *
 * @package Sift
 * @subpackage cli_task
 */
class sfCliTestCoverageTask extends sfCliBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCliCommandArgument('test_name', sfCliCommandArgument::REQUIRED, 'A test file name or a test directory'),
      new sfCliCommandArgument('lib_name', sfCliCommandArgument::REQUIRED, 'A lib file name or a lib directory for wich you want to know the coverage'),
    ));

    $this->addOptions(array(
      new sfCliCommandOption('detailed', null, sfCliCommandOption::PARAMETER_NONE, 'Output detailed information'),
    ));

    $this->namespace = 'test';
    $this->name = 'coverage';
    $this->briefDescription = 'Outputs test code coverage';

    $scriptName = $this->environment->get('script_name');

    $this->detailedDescription = <<<EOF
The [test:coverage|INFO] task outputs the code coverage
given a test file or test directory
and a lib file or lib directory for which you want code
coverage:

  [$scriptName test:coverage test/unit/model lib/model|INFO]

To output the lines not covered, pass the [--detailed|INFO] option:

  [$scriptName test:coverage --detailed test/unit/model lib/model|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    require_once $this->environment->get('sf_sift_lib_dir').'/vendor/lime/lime.php';

    $coverage = $this->getCoverage($this->getTestHarness(array('force_colors' => isset($options['color']) && $options['color'])), $options['detailed']);

    $testFiles = $this->getFiles($this->environment->get('sf_root_dir').'/'.$arguments['test_name']);
    $max = count($testFiles);
    foreach ($testFiles as $i => $file)
    {
      $this->logSection('coverage', sprintf('running %s (%d/%d)', $file, $i + 1, $max));
      $coverage->process($file);
    }

    $coveredFiles = $this->getFiles($this->environment->get('sf_root_dir').'/'.$arguments['lib_name']);
    $coverage->output($coveredFiles);
  }

  protected function getTestHarness($harnessOptions = array())
  {
    require_once dirname(__FILE__).'/sfLimeHarness.class.php';

    $harness = new sfLimeHarness($harnessOptions);

    $project = $this->commandApplication->getProject();
    $harness->addPlugins(array_map(array($project, 'getPluginConfiguration'), $project->getPlugins()));
    $harness->base_dir = $this->environment->get('sf_root_dir');

    return $harness;
  }

  protected function getCoverage(lime_harness $harness, $detailed = false)
  {
    $coverage = new lime_coverage($harness);
    $coverage->verbose = $detailed;
    $coverage->base_dir = $this->environment->get('sf_root_dir');

    return $coverage;
  }

  protected function getFiles($directory)
  {
    if (is_dir($directory))
    {
      return sfFinder::type('file')->name('*.php')->in($directory);
    }
    else if (file_exists($directory))
    {
      return array($directory);
    }
    else
    {
      throw new sfCliCommandException(sprintf('File or directory "%s" does not exist.', $directory));
    }
  }
}
