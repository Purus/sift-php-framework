<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(2);


function modifyCallback(sfEvent $event)
{
    $event->setProcessed(true);
    $event->setReturnValue('yep');
}

$dispatcher = new sfEventDispatcher();
$dispatcher->connect('plugin.method_not_found', 'modifyCallback');

$plugin = new sfGenericPlugin(new sfGenericProject(array(
    'sf_sift_lib_dir' => $sf_sift_lib_dir,
    'sf_sift_data_dir' => $sf_sift_data_dir,
    'sf_root_dir' => dirname(__FILE__),
), $dispatcher), 'test', dirname(__FILE__));

$t->is($plugin->getRootDir(), dirname(__FILE__));

$t->is($plugin->callMyMethod(), 'yep');
