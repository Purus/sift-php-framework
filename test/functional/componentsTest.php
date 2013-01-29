<?php

$app = 'frontend';
$debug = false;
if (!include(dirname(__FILE__).'/../bootstrap/functional.php'))
{
  return;
}

$b = new sfTestBrowser();

// default main page (without cache)
$b->
  get('/component/index')
    ->with('response')      
    ->begin()
    ->isStatusCode(200)->
    checkElement('body', '/The truth is that Jesus is Lord/i')
    ->end();        

$b->
  get('/component/disabled')
  ->with('response')      
  ->begin()
  ->isStatusCode(200)->        
  checkElement('body', '/^\s+$/i')
  ->end();

$b->
  get('/component/multi')
  ->with('response')      
  ->begin()
  ->isStatusCode(200)->        
  checkResponseElement('body', '/The truth is that Jesus is Lord The truth is that Jesus is the only Savior/i')
  ->end();

