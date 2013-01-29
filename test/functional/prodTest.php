<?php

$app = 'frontend';
$debug = false;
if(!include(dirname(__FILE__).'/../bootstrap/functional.php'))
{
  return;
}
  
$b = new sfTestBrowser();

// default main page (without cache)
$b->
  get('/')->
  with('request')->begin()->
    isParameter('module', 'default')->
    isParameter('action', 'index')->
  end()->
  with('response')->begin()->
    isStatusCode(200)->
    checkElement('body', '/congratulations/i')->
  end()
;

// default main page (with cache)
$b->
  get('/')->
  with('request')->begin()->
    isParameter('module', 'default')->
    isParameter('action', 'index')->
  end()->
  with('response')->begin()->
    isStatusCode(200)->
    checkElement('body', '/congratulations/i')->
  end()
;

// 404
$b->
  get('/nonexistant')->
  with('request')->begin()->
    isForwardedTo('default', 'error404')->
  end()->
  with('response')->begin()->
    isStatusCode(404)->
    checkElement('body', '!/congratulations/i')->
    checkElement('link[href="/css/main.css"]')->
  end()
;

$b->
  get('/nonexistant/')->
  with('request')->begin()->
    isForwardedTo('default', 'error404')->
  end()->
  with('response')->begin()->
    isStatusCode(404)->
    checkElement('body', '!/congratulations/i')->
    checkElement('link[href="/css/main.css"]')->
  end()
;

// unexistant action
$b->
  get('/default/nonexistantaction')->
  with('request')->begin()->
    isForwardedTo('default', 'error404')->
  end()->
  with('response')->begin()->
    isStatusCode(404)->
    checkElement('body', '!/congratulations/i')->
    checkElement('link[href="/css/main.css"]')->
  end()
;
