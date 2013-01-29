<?php

$app = 'frontend';
if (!include(dirname(__FILE__).'/../bootstrap/functional.php'))
{
  return;
}

$b = new sfTestBrowser();

$b->
  post('/fillInFilter/forward', array('name' => 'fabien'))
  ->with('request')->begin()
      ->isParameter('module', 'fillInFilter')
      ->isParameter('action', 'forward')
  ->end()
  ->with('response')->begin()
    ->isStatusCode(200)
    ->checkElement('body div', 'foo')
  ->end();

$b->
  post('/fillInFilter/update', array('first_name' => 'fabien'))
  ->with('response')->begin()
    ->isStatusCode(200)
    ->checkElement('input[name="first_name"][value="fabien"]')
  ->end()
  ->with('request')->begin()
    ->isParameter('module', 'fillInFilter')
    ->isParameter('action', 'update')
  ->end();
