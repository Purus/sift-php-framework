<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');

$t = new lime_test(18, new lime_output_color());

class myView extends sfView
{
  function execute () {}
  function configure () {}
  function getEngine () {}
  function render ($templateVars = null) {}
}

$context = new sfContext();
$view = new myView();
$view->initialize($context, '', '', '');

// ->getContext()
$t->diag('->getContext()');
$view->initialize($context, '', '', '');
$t->is($view->getContext(), $context, '->getContext() returns the current context');

// ->isDecorator() ->setDecorator()
$t->diag('->isDecorator() ->setDecorator()');
$t->is($view->isDecorator(), false, '->isDecorator() returns true if the current view have to be decorated');
$view->setDecorator(true);
$t->is($view->isDecorator(), true, '->setDecorator() sets the decorator status for the view');

// parameter holder proxy
require_once($_test_dir.'/unit/sfParameterHolderTest.class.php');
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($view, 'parameter');

// new methods via sfEventDispatcher
require_once($_test_dir.'/unit/sfEventDispatcherTest.class.php');
$dispatcherTest = new sfEventDispatcherTest($t);
$dispatcherTest->launchTests(sfCore::getEventDispatcher(), $view, 'view');