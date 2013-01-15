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
  get('/component/index')->
  isStatusCode(200)->
  checkResponseElement('body', '/The truth is that Jesus is Lord/i')
;

$b->
  get('/component/disabled')->
  isStatusCode(200)->
  checkResponseElement('body', '/^\s+$/i');

$b->
  get('/component/multi')->
  isStatusCode(200)->
  checkResponseElement('body', '/The truth is that Jesus is Lord The truth is that Jesus is the only Savior/i');

