<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(8);

$rootDir = realpath(dirname(__FILE__).'/../../functional/fixtures/project');
$pluginRoot = realpath($rootDir.'/plugins/sfAutoloadPlugin');

// ->getRootDir() ->guessName()
$t->diag('->getRootDir() ->guessName()');

class myFrontApplication extends sfApplication {}
class myProject extends sfProject {}

$options = array(
    'sf_root_dir' => $rootDir,
    'sf_sift_lib_dir' => $sf_sift_lib_dir,
    'sf_sift_data_dir' => $sf_sift_data_dir,
    'sf_app' => 'frontend'
);

$application = new myFrontApplication('dev', true, $options);

$t->is($application->getName(), 'frontend', '->getName() returns application name');
$t->is($application instanceof sfApplication, true, 'Application is instance of sfApplication');

$t->isa_ok($application->getPlugins(), 'array', '->getPlugins() returns array of plugins');
$t->is(count($application->getPlugins()), 4, '->getPlugins() returns array of plugins');

foreach($application->getPlugins() as $plugin)
{
  $t->is($plugin instanceof sfPlugin, true, 'All plugins are instances of sfPlugin class');
}

