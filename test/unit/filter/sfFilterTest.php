<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');

$t = new lime_test(18, new lime_output_color());

class myFilter extends sfFilter
{
  public function execute(sfFilterChain $filterChain)
  {
    $filterChain->execute();
  }

  public function isFirstCall()
  {
    return parent::isFirstCall();
  }
}

$context = new sfContext();
$filter = new myFilter();

// ->initialize()
$t->diag('->initialize()');
$filter = new myFilter();
$t->is($filter->getContext(), null, '->initialize() takes a sfContext object as its first argument');
$filter->initialize($context, array('foo' => 'bar'));
$t->is($filter->getParameter('foo'), 'bar', '->initialize() takes an array of parameters as its second argument');

// ->getContext()
$t->diag('->getContext()');
$filter->initialize($context);
$t->is($filter->getContext(), $context, '->getContext() returns the current context');

// ->isFirstCall()
$t->diag('->isFirstCall()');
$t->is($filter->isFirstCall('beforeExecution'), true, '->isFirstCall() returns true if this is the first call with this argument');
$t->is($filter->isFirstCall('beforeExecution'), false, '->isFirstCall() returns false if this is not the first call with this argument');
$t->is($filter->isFirstCall('beforeExecution'), false, '->isFirstCall() returns false if this is not the first call with this argument');

$filter = new myFilter();
$filter->initialize($context);
$t->is($filter->isFirstCall('beforeExecution'), false, '->isFirstCall() returns false if this is not the first call with this argument');

// parameter holder proxy
require_once($_test_dir.'/unit/sfFlatParameterHolderTest.class.php');
$pht = new sfFlatParameterHolderProxyTest($t);
$pht->launchTests($filter, 'parameter');
