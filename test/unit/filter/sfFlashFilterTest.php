<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../sfServiceContainerMock.php');

$t = new lime_test(6, new lime_output_color());

class sfContext
{
  public $user = null;

  public function getUser()
  {
    return $this->user;
  }
}

class myUser extends sfUser
{
}

class myComponent extends sfComponent
{
  public $user = null;

  public function execute()
  {
  }

  public function getUser()
  {
    return $this->user;
  }
}

class firstTestFilter extends sfFilter
{
  public $t = null;
  public $user = null;

  public function execute(sfFilterChain $filterChain)
  {
    $t  = $this->t;
    $user = $this->user;

    // sfFlashFilter has executed code before its call to filterChain->execute()
    $t->is($user->getAttribute('previous_request', true, 'sift/flash/remove'), true, '->execute() flags flash variables to be removed after request execution');
    $t->is($user->getAttribute('every_request', true, 'sift/flash/remove'), true, '->execute() flags flash variables to be removed after request execution');

    $filterChain->execute();

    // action execution
    $component = new myComponent();
    $component->user = $user;
    $component->setFlash('this_request', 'foo', 'sift/flash');
    $component->setFlash('every_request', 'foo', 'sift/flash');
  }
}

class lastTestFilter extends sfFilter
{
  public $t = null;
  public $user = null;

  public function execute(sfFilterChain $filterChain)
  {
    $t  = $this->t;
    $user = $this->user;

    // sfFlashFilter has executed no code

    // register some flash from previous request
    $user->setAttribute('previous_request', 'foo', 'sift/flash');
    $user->setAttribute('every_request', 'foo', 'sift/flash');

    $filterChain->execute();

    // sfFlashFilter has executed all its code
    $t->ok(!$user->hasAttribute('previous_request', 'sift/flash'), '->execute() removes flash variables that have been tagged before');
    $t->ok(!$user->hasAttribute('previous_request', 'sift/flash/remove'), '->execute() removes flash variables that have been tagged before');
    $t->is($user->getAttribute('this_request', null, 'sift/flash'), 'foo', '->execute() keeps current request flash variables');
    $t->is($user->getAttribute('every_request', null, 'sift/flash'), 'foo', '->execute() flash variables that have been overriden in current request');
  }
}

$context = new sfContext();
$user = new myUser($serviceContainer);
$context->user = $user;

$filterChain = new sfFilterChain($context);

$filter = new lastTestFilter();
$filter->t = $t;
$filter->user = $user;
$filter->initialize($context);
$filterChain->register($filter);

$filter = new sfFlashFilter();
$filter->initialize($context);
$filterChain->register($filter);

$filter = new firstTestFilter();
$filter->t = $t;
$filter->user = $user;
$filter->initialize($context);
$filterChain->register($filter);

$filterChain->execute();
