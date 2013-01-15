<?php

$app = 'frontend';
if (!include(dirname(__FILE__).'/../bootstrap/functional.php'))
{
  return;
}

$b = new sfTestBrowser();
$b->initialize();

$b->
  post('/fillInFilter/forward', array('name' => 'fabien'))->
  isStatusCode(200)->
  isRequestParameter('module', 'fillInFilter')->
  isRequestParameter('action', 'forward')->
  checkResponseElement('body div', 'foo')
;

$b->
  post('/fillInFilter/update', array('first_name' => 'fabien'))->
  isStatusCode(200)->
  isRequestParameter('module', 'fillInFilter')->
  isRequestParameter('action', 'update')->
  checkResponseElement('input[name="first_name"][value="fabien"]')
;
