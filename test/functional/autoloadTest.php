<?php

$app = 'frontend';
if (!include(dirname(__FILE__).'/../bootstrap/functional.php'))
{
  return;
}

$b = new sfTestBrowser();

$b->
  get('/autoload/myAutoload')->  

  with('request')->begin()->
    isParameter('module', 'autoload')->
    isParameter('action', 'myAutoload')->
  end()
        
  ->with('response')->begin()->
    isStatusCode(200)
  ->end()->
        
  checkResponseElement('body div', 'foo')
;
