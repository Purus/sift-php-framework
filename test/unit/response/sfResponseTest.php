<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');
require_once($_test_dir.'/unit/sfCoreMock.class.php');

class myResponse extends sfResponse
{
  function shutdown() {}
}

class fakeResponse
{
}

$t = new lime_test(19, new lime_output_color());

$context = sfContext::getInstance();

$response = new myResponse(array('foo' => 'bar'));

$t->is($response->getParameter('foo'), 'bar', '->initialize() takes an array of parameters as its second argument');

$response = new myResponse();

// ->getContent() ->setContent()
$t->diag('->getContent() ->setContent()');
$t->is($response->getContent(), null, '->getContent() returns the current response content which is null by default');
$response->setContent('test');
$t->is($response->getContent(), 'test', '->setContent() sets the response content');

// ->sendContent()
$t->diag('->sendContent()');
ob_start();
$response->sendContent();
$content = ob_get_clean();
$t->is($content, 'test', '->sendContent() output the current response content');

// parameter holder proxy
require_once($_test_dir.'/unit/sfParameterHolderTest.class.php');
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($response, 'parameter');

// new methods via sfEventDispatcher
require_once($_test_dir.'/unit/sfEventDispatcherTest.class.php');
$dispatcherTest = new sfEventDispatcherTest($t);
$dispatcherTest->launchTests(sfCore::getEventDispatcher(), $response, 'response');
