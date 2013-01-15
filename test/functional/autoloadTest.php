<?php

$app = 'frontend';
if (!include(dirname(__FILE__).'/../bootstrap/functional.php'))
{
  return;
}

$b = new sfTestBrowser();
$b->initialize();

$b->
  get('/autoload/myAutoload')->
  isStatusCode(200)->
  isRequestParameter('module', 'autoload')->
  isRequestParameter('action', 'myAutoload')->
  checkResponseElement('body div', 'foo')
;
