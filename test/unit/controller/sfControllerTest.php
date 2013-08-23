<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');
require_once($_test_dir.'/unit/sfCoreMock.class.php');

$t = new lime_test(2, new lime_output_color());

class myController extends sfController
{
  function execute() {}
}

$context = new sfContext();
$controller = new myController($context);

// new methods via sfEventDispatcher
require_once($_test_dir.'/unit/sfEventDispatcherTest.class.php');
$dispatcherTest = new sfEventDispatcherTest($t);
$dispatcherTest->launchTests(sfCore::getEventDispatcher(), $controller, 'controller');