<?php

$app = 'frontend';
if (!include(dirname(__FILE__).'/../bootstrap/functional.php'))
{
  return;
}

$b = new sfTestBrowser();
$b->initialize();

// filter
$b->
  get('/filter')->
  isStatusCode(200)->
  isRequestParameter('module', 'filter')->
  isRequestParameter('action', 'index')->
  checkResponseElement('div[class="before"]', 1)->
  checkResponseElement('div[class="after"]', 1)
;

// filter with a forward in the same module
$b->
  get('/filter/indexWithForward')->
  isStatusCode(200)->
  isRequestParameter('module', 'filter')->
  isRequestParameter('action', 'indexWithForward')->
  checkResponseElement('div[class="before"]', 2)->
  checkResponseElement('div[class="after"]', 1)
;
