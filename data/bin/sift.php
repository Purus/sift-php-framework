<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if(!isset($sf_sift_lib_dir))
{
  die('Invalid usage of the command line script. Missing $sf_sift_lib_dir setting');
}

require_once($sf_sift_lib_dir.'/autoload/sfCoreAutoload.class.php');
sfCoreAutoload::register();

try
{ 
  sfConfig::add(array(
    'sf_root_dir'      => getcwd(),
    'sf_sift_lib_dir'  => $sf_sift_lib_dir,
    'sf_sift_data_dir' => $sf_sift_data_dir,  
    'script_name' => './sift'   // FIXME: script name
  ));

  sfCore::initConfiguration();

  $environment = new sfCliTaskEnvironment(sfConfig::getAll());
  $application = new sfCliRootCommandApplication($environment);  
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
