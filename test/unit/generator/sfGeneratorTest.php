<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');
require_once($_test_dir.'/unit/sfCoreMock.class.php');

$t = new lime_test(4, new lime_output_color());

class myGenerator extends sfGenerator
{
  public function generate($params = array()) {}
}

class myFakeGenerator {}

$manager = new sfGeneratorManager(dirname(__FILE__).'/tmp');

$manager->generate('myGenerator', array(
));

$generator = new myGenerator($manager);
// new methods via sfEventDispatcher

try {

  $manager->generate('myInvalidGenerator', array());
  $t->pass('exception is thrown when generator class does not exist');
}
catch(InvalidArgumentException $e)
{
  $t->pass('exception is thrown when generator class does not exist');
}

try {

  $manager->generate('myFakeGenerator', array());
  $t->pass('exception is thrown when generator does not implement sfIGenerator interface');
}
catch(InvalidArgumentException $e)
{
  $t->pass('exception is thrown when generator does not implement sfIGenerator interface');
}


$dispatcher = new sfEventDispatcher();
sfCore::$dispatcher = $dispatcher;
require_once($_test_dir.'/unit/sfEventDispatcherTest.class.php');
$dispatcherTest = new sfEventDispatcherTest($t);
$dispatcherTest->launchTests($dispatcher, $generator, 'generator');
