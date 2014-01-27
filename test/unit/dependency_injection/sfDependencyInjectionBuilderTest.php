<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');
require_once(dirname(__FILE__) . '/stubs/Book.php');
require_once(dirname(__FILE__) . '/stubs/Dummy.php');
require_once(dirname(__FILE__) . '/stubs/DummyExtended.php');
require_once(dirname(__FILE__) . '/stubs/NonInst.php');
require_once(dirname(__FILE__) . '/stubs/Something.php');
require_once(dirname(__FILE__) . '/stubs/Apple.php');
require_once(dirname(__FILE__) . '/stubs/Banana.php');
require_once(dirname(__FILE__) . '/stubs/Pear.php');

$t = new lime_test(13, new lime_output_color());

$container = new sfServiceContainer(new sfNoCache());
$dependencies = new sfDependencyInjectionDependencies($container);
$maps = new sfDependencyInjectionMaps();

$dependencies->set('apple', new Apple());
$dependencies->set('pear', new Pear());
$dependencies->set('banana', new Banana());

$className = 'stdClass';

$builder = new sfDependencyInjectionBuilder($className, $dependencies, $maps);
$object = $builder->constructObject();

$t->isa_ok($object, 'stdClass', 'buildObject() works for standard classes');

$builder = new sfDependencyInjectionBuilder('Dummy', $dependencies, $maps);
$object = $builder->constructObject();

$t->isa_ok($object, 'Dummy', 'buildObject() works for classes with @inject doc comments');
$t->isa_ok($object->getApple(), 'Apple', 'object is injected with method');
$t->isa_ok($object->pear, 'Pear', 'object is injected with public property');
$t->isa_ok($object->getConstructorArg(), 'Banana', 'Banana instance is passed to the constructor');
$t->isa_ok($object->getForcedVar(), 'Something', 'forcedVariable works ok');

try
{
  $builder = new sfDependencyInjectionBuilder('NonInst', $dependencies, $maps);
  $object = $builder->constructObject();
  $t->fail('construction of non initializable object throws an sfInitializationException exception');
}
catch(sfInitializationException $e)
{
  $t->pass('construction of non initializable object throws an sfInitializationException exception');
}

$builder = new sfDependencyInjectionBuilder('Something', $dependencies, $maps);
$object = $builder->constructObject();

$t->isa_ok($object, 'Something', 'buildObject() works for classes with @inject doc comments');

$t->diag('with inheritance');

$builder = new sfDependencyInjectionBuilder('DummyExtended', $dependencies, $maps);
$object = $builder->constructObject();

$t->isa_ok($object, 'DummyExtended', 'buildObject() works for classes with @inject doc comments');
$t->isa_ok($object->getApple(), 'Apple', 'object is injected with method');
$t->isa_ok($object->pear, 'Pear', 'object is injected with public property');
$t->isa_ok($object->getConstructorArg(), 'Banana', 'Banana instance is passed to the constructor');
$t->isa_ok($object->getForcedVar(), 'Something', 'forcedVariable works ok');
