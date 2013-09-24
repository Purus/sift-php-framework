<?php

$app = 'frontend';
if (!include(dirname(__FILE__).'/../bootstrap/functional.php'))
{
  return;
}

$b = new sfTestBrowser();

// filter
$b->
  get('/filter')
  ->with('response')
  ->begin()
  ->isStatusCode(200)
  ->checkElement('div[class="before"]', 1)
  ->checkElement('div[class="after"]', 1)
  ->end()
  ->with('request')->begin()->
    isParameter('module', 'filter')->
    isParameter('action', 'index')->
  end();

$b->
  info('filter width "disabled_for" for current module/action')
  ->get('/filter/disabled')
  ->with('response')
  ->begin()
  ->isStatusCode(200)
  ->checkElement('div[class="before"]', 0)
  ->checkElement('div[class="after"]', 0)
  ->end()
  ->with('request')->begin()->
    isParameter('module', 'filter')->
    isParameter('action', 'disabled')->
  end();

// filter with a forward in the same module
$b->
  get('/filter/indexWithForward')
    ->with('response')->begin()
      ->isStatusCode(200)
      ->checkElement('div[class="before"]', 2)
      ->checkElement('div[class="after"]', 1)    
    ->end()
    ->with('request')->begin()
      ->isParameter('module', 'filter')
      ->isParameter('action', 'indexWithForward')
    ->end();
