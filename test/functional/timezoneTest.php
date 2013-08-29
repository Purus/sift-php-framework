<?php

$app = 'frontend';
if(!include(dirname(__FILE__).'/../bootstrap/functional.php'))
{
  return;
}

$b = new sfTestBrowser();

// default main page
$b->
  clearCookies()->
  get('/timezone')->
  with('user')->begin()
    ->isTimezone('Europe/Prague')
  ->end()
    ->with('request')->begin()
    ->isParameter('module', 'timezone')
    ->isParameter('action', 'index')
  ->end()
  ->with('response')->begin()
    ->isStatusCode(200)
  ->end();

// now with cookies
$b->
  clearCookies()->
  setCookie('timezone_daylightsavings', 1)->
  setCookie('timezone_offset', 2)->
  get('/timezone')->
  with('user')->begin()
    ->isTimezone('Europe/Paris')
  ->end()
    ->with('request')->begin()
    ->hasCookie('timezone_offset')
    ->hasCookie('timezone_daylightsavings')
    ->isParameter('module', 'timezone')
    ->isParameter('action', 'index')
  ->end()
  ->with('response')->begin()
    ->isStatusCode(200)
  ->end();

$b->
  clearCookies()->
  // we set the name
  setCookie('timezone_name', 'Asia/Jerusalem')->
  setCookie('timezone_daylightsavings', 1)->
  setCookie('timezone_offset', 8)->
  get('/timezone')->
  with('user')->begin()
    ->isTimezone('Asia/Jerusalem')
  ->end()
    ->with('request')->begin()
    ->hasCookie('timezone_offset')
    ->hasCookie('timezone_daylightsavings')
    ->hasCookie('timezone_name')
    ->isParameter('module', 'timezone')
    ->isParameter('action', 'index')
  ->end()
  ->with('response')->begin()
    ->isStatusCode(200)
  ->end();


$b->
  clearCookies()->
  // we set the name
  setCookie('timezone_name', 'Im hacking this site')->
  setCookie('timezone_daylightsavings', 'Yes, i saved day light to a secret box')->
  setCookie('timezone_offset', 'What does this mean?')->
  get('/timezone')->
  with('user')->begin()
    ->isTimezone('Europe/Prague')
  ->end()
    ->with('request')->begin()
    ->hasCookie('timezone_offset')
    ->hasCookie('timezone_daylightsavings')
    ->hasCookie('timezone_name')
    ->isCookie('timezone_name', 'Im hacking this site')
    ->isParameter('module', 'timezone')
    ->isParameter('action', 'index')
  ->end()
  ->with('response')->begin()
    ->isStatusCode(200)
  ->end();