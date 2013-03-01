<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');
require_once($_test_dir.'/unit/sfCoreMock.class.php');

$t = new lime_test(9, new lime_output_color());

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

$options = array(
  'foo' => 'bar',
  'second' => array(
    'foo' => 'bar'
  ),
  'third' => array(
    'nested' => array(
        'foo' => 'bar'
    ),
  )
);

$generator = new myGenerator($manager, $options);

$t->is($generator->getOption('foo'), 'bar', 'getOption() for simple option works ok');
$t->is($generator->getOption('second'), array('foo' => 'bar'), 'getOption() for nested option works ok');
$t->is($generator->getOption('second.foo'), 'bar', 'getOption() for nested option works ok');
$t->is($generator->getOption('third.nested.foo'), 'bar', 'getOption() for deeply nested option works ok');

// set option
$generator->setOption('third.nested.foo', 'bar2');
$t->is($generator->getOption('third.nested.foo'), 'bar2', 'setOption() for deeply nested option works ok');


$dispatcher = new sfEventDispatcher();
sfCore::$dispatcher = $dispatcher;
require_once($_test_dir.'/unit/sfEventDispatcherTest.class.php');
$dispatcherTest = new sfEventDispatcherTest($t);
$dispatcherTest->launchTests($dispatcher, $generator, 'generator');
