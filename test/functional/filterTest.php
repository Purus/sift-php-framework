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

// asset packaging and output compression
$b->info('gzip compression')
  ->setHttpHeader('Accept-Encoding', 'gzip, deflate')
  ->get('/filter/packageAssets')
    ->with('response')->begin()
      ->isStatusCode(200)
      ->isHeader('Content-Encoding', 'gzip')
      ->isHeader('Content-Type', '~text/html~')
      ->contains('Jesus is Lord')
      ->matches('~<script type="text/javascript" src="/min/\d+/f=/js/custom.js~')
      ->matches('~href="/min/\d+/f=/css/main.css,/cache/css/[a-z0-9]+/[a-z0-9]+.min.css,/css/custom/two.css"~')
    ->end()
    ->with('request')->begin()
      ->isParameter('module', 'filter')
      ->isParameter('action', 'packageAssets')
    ->end();

// asset packaging and output compression
$b->info('deflate compression')
  ->setHttpHeader('Accept-Encoding', 'deflate')
  ->get('/filter/packageAssets')
    ->with('response')->begin()
      ->isStatusCode(200)
      ->isHeader('Content-Encoding', 'deflate')
      ->isHeader('Content-Type', '~text/html~')
      ->contains('Jesus is Lord')
      // javascripts are minified
      ->matches('~<script type="text/javascript" src="/min/\d+/f=/~')
      // stylesheets are minified
      ->matches('~href="/min/\d+/f=/css/main.css,/cache/css/[a-z0-9]+/[a-z0-9]+.min.css,/css/custom/two.css"~')
    ->end()
    ->with('request')->begin()
      ->isParameter('module', 'filter')
      ->isParameter('action', 'packageAssets')
    ->end();