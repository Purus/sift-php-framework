<?php

require_once(dirname(__FILE__).'/../../lib/util/sfToolkit.class.php');
sfToolkit::clearDirectory(dirname(__FILE__).'/fixtures/project/cache');

$app = 'frontend';
$debug = false;
if (!include(dirname(__FILE__).'/../bootstrap/functional.php'))
{
  return;
}

$b = new sfTestBrowser();
$b->initialize();

// default main page (without cache)
$b->get('/download/data')
  ->isStatusCode(200)
  ->isResponseHeader('content-type', 'text-plain');
