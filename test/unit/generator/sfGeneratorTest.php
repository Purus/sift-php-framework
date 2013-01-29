<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');
require_once($_test_dir.'/unit/sfCoreMock.class.php');

$t = new lime_test(2, new lime_output_color());

class myGenerator extends sfGenerator
{
  public function generate($params = array()) {}
}

$manager = new sfGeneratorManager();

$generator = new myGenerator();
$generator->initialize($manager);

$dispatcher = new sfEventDispatcher();
sfCore::$dispatcher = $dispatcher;

// new methods via sfEventDispatcher
require_once($_test_dir.'/unit/sfEventDispatcherTest.class.php');
$dispatcherTest = new sfEventDispatcherTest($t);
$dispatcherTest->launchTests($dispatcher, $generator, 'generator');

