<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');
require_once($_test_dir.'/unit/sfCoreMock.class.php');

$t = new lime_test(8, new lime_output_color());

class myComponent extends sfComponent
{
  function execute() {}
}

$context = new sfContext();

// ->initialize()
$t->diag('->initialize()');
$component = new myComponent();
$t->is($component->getContext(), null, '->initialize() takes a sfContext object as its first argument');
$component->initialize($context);
$t->is($component->getContext(), $context, '->initialize() takes a sfContext object as its first argument');

// ->getContext()
$t->diag('->getContext()');
$component->initialize($context);
$t->is($component->getContext(), $context, '->getContext() returns the current context');

// ->getRequest()
$t->diag('->getRequest()');
$component->initialize($context);
$t->is($component->getRequest(), $context->getRequest(), '->getRequest() returns the current request');

// ->getResponse()
$t->diag('->getResponse()');
$component->initialize($context);
$t->is($component->getResponse(), $context->getResponse(), '->getResponse() returns the current response');

// __set()
$t->diag('__set()');
$component->foo = array();
$component->foo[] = 'bar';
$t->is($component->foo, array('bar'), '__set() populates component variables');

// new methods via sfEventDispatcher
require_once($_test_dir.'/unit/sfEventDispatcherTest.class.php');
$dispatcherTest = new sfEventDispatcherTest($t);
$dispatcherTest->launchTests($context->getEventDispatcher(), $component, 'component');
