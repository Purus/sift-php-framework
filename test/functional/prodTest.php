<?php

require_once(dirname(__FILE__).'/../../lib/util/sfToolkit.class.php');
sfToolkit::clearDirectory(dirname(__FILE__).'/fixtures/project/cache');

$app = 'frontend';
$debug = false;
if (!include(dirname(__FILE__).'/../bootstrap/functional.php'))
{
  return;
}

$b = new sfTestBrowser();
$b->initialize();

// default main page (without cache)
$b->
  get('/')->
  isStatusCode(200)->
  isRequestParameter('module', 'default')->
  isRequestParameter('action', 'index')->
  checkResponseElement('body', '/congratulations/i')
;

// default main page (with cache)
$b->
  get('/')->
  isStatusCode(200)->
  isRequestParameter('module', 'default')->
  isRequestParameter('action', 'index')->
  checkResponseElement('body', '/congratulations/i')
;
