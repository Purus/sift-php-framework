<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');
require_once(dirname(__FILE__).'/../../../lib/helper/PartialHelper.php');

$t = new lime_test(10);

class MyTestPartialView extends sfPartialView
{
  public function render($templateVars = array())
  {
    return '==RENDERED==';
  }

  protected function preRenderCheck()
  {
  }

}



class defaultActions extends sfActions {}

$context = sfContext::getInstance();
$context->getServiceContainer()->getDependencies()->set('context', $context);

$context->actionStack = new sfActionStack();

$entry = $context->actionStack->addEntry('default', 'index', new defaultActions());
$entry->setViewInstance(new MyTestPartialView($context, 'default', 'index'));

$t->diag('get_partial()');

$t->is(get_partial('module/dummy', array(), 'MyTestPartial'), '==RENDERED==', 'get_partial() uses the class specified in partial_view_class for the given module');
$t->is(get_partial('MODULE/dummy', array(), 'MyTestPartial'), '==RENDERED==', 'get_partial() accepts a case-insensitive module name');

$t->diag('get_slot()');
$t->is(get_slot('foo', 'baz'), 'baz', 'get_slot() retrieves default slot content');
slot('foo', 'bar');
$t->is(get_slot('foo', 'baz'), 'bar', 'get_slot() retrieves slot content');

$t->diag('has_slot()');
$t->ok(has_slot('foo'), 'has_slot() checks if a slot exists');
$t->ok(!has_slot('doo'), 'has_slot() checks if a slot does not exist');

$t->diag('include_slot()');
ob_start();
include_slot('foo');
$t->is(ob_get_clean(), 'bar', 'include_slot() prints out the content of an existing slot');

ob_start();
include_slot('doo');
$t->is(ob_get_clean(), '', 'include_slot() does not print out the content of an unexisting slot');

ob_start();
include_slot('doo', 'zoo');
$t->is(ob_get_clean(), 'zoo', 'include_slot() prints out the default content specified for an unexisting slot');

$t->diag('get_component_slot()');

try {
  get_component_slot('sidebar');
  $t->fail('get_component_slot() throws an sfConfigurationException if component is not set');
}
catch(sfConfigurationException $e)
{
  $t->pass('get_component_slot() throws an sfConfigurationException if component is not set');
}

