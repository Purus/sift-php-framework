<?php

$app = 'frontend';
if(!include(dirname(__FILE__).'/../bootstrap/functional.php'))
{
  return;
}

$b = new sfTestBrowser();

// default main page
$b->
  get('/mailer')->
  with('mailer')->begin()
  ->checkBody('This is a test email')
  ->checkBodyMultipart('<strong>This is an html version</strong>', 'text/html')
  ->end()
    ->with('request')->begin()
    ->isParameter('module', 'mailer')
    ->isParameter('action', 'index')
  ->end()
  ->with('response')
    ->begin()
    ->checkElement('body', '/The email has been sent/i')
    ->end();
