<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Build cli application
 *
 * @package Sift
 * @subpackage build
 */

$sf_sift_lib_dir = realpath(dirname(__FILE__) . '/../../lib');
$sf_sift_data_dir = realpath(dirname(__FILE__) . '/../../data');

require_once($sf_sift_lib_dir.'/autoload/sfCoreAutoload.class.php');
sfCoreAutoload::register();

require_once(dirname(__FILE__).'/tasks/sfCliBuildCommandApplication.class.php');
require_once(dirname(__FILE__).'/tasks/sfCliBuildBaseTask.class.php');
require_once($sf_sift_lib_dir.'/cli/task/list/sfCliListTask.class.php');
require_once($sf_sift_lib_dir.'/cli/task/help/sfCliHelpTask.class.php');

$environment = new sfCliTaskEnvironment(array(
  'sf_sift_version'  => sfCore::getVersion(),
  'sf_sift_name'     => 'Sift',

  'sf_sift_lib_dir'  => $sf_sift_lib_dir,
  'sf_sift_data_dir' => $sf_sift_data_dir,
  'sf_sift_test_dir' => realpath(dirname(__FILE__).'/../../test/'),
  'script_name'      => sprintf('./%s', basename($_SERVER['PHP_SELF'])),

  'project_root_dir' => realpath(dirname(__FILE__) . '/../../'),
  'root_dir'         => getcwd(),
  'build_task_dir'   => dirname(__FILE__) . '/tasks',
  'i18n_data_dir'    => realpath($sf_sift_data_dir . '/i18n'),
  // where are build data?
  'build_data_dir'   => realpath(dirname(__FILE__) . '/../../build')
));

try
{
  $application = new sfCliBuildCommandApplication($environment);
  $statusCode = $application->run();
}
catch(Exception $e)
{
  if(!isset($application))
  {
    throw $e;
  }

  $application->renderException($e);
  $statusCode = $e->getCode();

  exit(is_numeric($statusCode) && $statusCode ? $statusCode : 1);
}

exit(is_numeric($statusCode) ? $statusCode : 0);
