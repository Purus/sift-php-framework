<?php

$app = 'frontend';
if(!include(dirname(__FILE__).'/../bootstrap/functional.php'))
{
  return;
}

$b = new sfTestBrowser();

// default main page
$b->
  get('/security')->
  with('response')->begin()
  ->isStatusCode(200)
  ->checkElement('#secured', 'yes')
  ->checkElement('#secured-credentials', 'foo_credential')
  ->checkElement('#secured-allowed', 'no')
  ->checkElement('#index', 'no')
  ->checkElement('#user-credentials', '')
  ->end()
    ->with('request')->begin()
    ->isParameter('module', 'security')
    ->isParameter('action', 'index')
  ->end()
  ->with('user')
    ->begin()
    ->hasCredential('foo_credential', false)
  ->end();

// default main page
$b->
  get('/security/credential')->
  with('response')->begin()
  ->isStatusCode(200)
  ->checkElement('#secured', 'yes')
  ->checkElement('#secured-credentials', 'foo_credential')
  ->checkElement('#secured-allowed', 'yes')
  ->checkElement('#index', 'no')
  ->checkElement('#user-credentials', 'foo_credential')
  ->end()
    ->with('request')->begin()
    ->isParameter('module', 'security')
    ->isParameter('action', 'credential')
  ->end()
  ->with('user')
    ->begin()
    ->hasCredential('foo_credential', true)
    ->isFlash('success', 'redirected')
  ->end();

// ajax request for secured page
$b->
  setHttpHeader('X-Requested-With', 'XMLHttpRequest')->
  get('/security/ajax')->
  with('response')->begin()
  ->isStatusCode(401)
  ->isHeader('Content-type', 'application/json')
  ->end()
    ->with('request')->begin()
    ->isParameter('module', 'security')
    ->isParameter('action', 'ajax')
  ->end();
