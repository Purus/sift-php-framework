<?php

$app = 'frontend';
if (!include(dirname(__FILE__).'/../bootstrap/functional.php'))
{
  return;
}

$b = new sfTestBrowser();


$b->get('/download/data')
  ->with('response')->begin()        
    ->isStatusCode(200)
    ->isHeader('content-type', 'text-plain')
  ->end();

