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
  ->hasSent(1)
  //->debug()
  ->checkBody('This is a test email')
  ->checkBody('<strong>This is an html version</strong>', 'html')
  ->hasAttachment('foo.pdf')
  ->end()
    ->with('request')->begin()
    ->isParameter('module', 'mailer')
    ->isParameter('action', 'index')
  ->end()
  ->with('response')
    ->begin()
    ->isStatusCode(200)
    ->checkElement('body', '/The email has been sent/i')
    ->end();

// default main page
$b->
  get('/mailer/convert')->
  with('mailer')->begin()
  ->hasSent(1)
  //->debug()
  ->isHeader('X-Auto-Converted')
  ->checkBody('This is an html version')
  ->checkBody('<strong>This is an html version</strong>', 'html')
  ->end()
    ->with('request')->begin()
    ->isParameter('module', 'mailer')
    ->isParameter('action', 'convert')
  ->end()
  ->with('response')
    ->begin()
    ->isStatusCode(200)
    ->checkElement('body', '/The email has been sent/i')
    ->end();

// default main page
$b->
  get('/mailer/partial')->
  with('mailer')->begin()
  ->hasSent(1)
  ->checkBody('Hi Foobar')
  ->checkBody('<div>Hi <strong>Foobar</strong></div>', 'html')
  ->end()
    ->with('request')->begin()
    ->isParameter('module', 'mailer')
    ->isParameter('action', 'partial')
  ->end()
  ->with('response')
    ->begin()
    ->isStatusCode(200)
    ->checkElement('body', '/The email has been sent/i')
    ->end();