<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');

$t = new lime_test(14, new lime_output_color());

class myValidator extends sfValidator
{
  function execute (&$value, &$error) {}
}

$context = new sfContext();
$validator = new myValidator();
$validator->initialize($context);

// ->getContext()
$t->diag('->getContext()');
$validator->initialize($context);
$t->is($validator->getContext(), $context, '->getContext() returns the current context');

// parameter holder proxy
require_once($_test_dir.'/unit/sfParameterHolderTest.class.php');
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($validator, 'parameter');
