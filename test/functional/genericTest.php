<?php

$app = 'frontend';
if (!include(dirname(__FILE__).'/../bootstrap/functional.php'))
{
  return;
}

$b = new sfTestBrowser();

// default main page
$b->
  get('/')->
  with('response')->begin()
  ->isStatusCode(200)
  ->checkElement('body', '/congratulations/i')
  ->contains('<!--[if lte IE 10]><link rel="stylesheet" type="text/css" media="screen,projection,tv" href="/css/ie8.css" /><![endif]-->')
  ->end()
    ->with('request')->begin()
    ->isParameter('module', 'default')
    ->isParameter('action', 'index')
  ->end();

// default 404
$b->
  get('/nonexistant')->
  with('request')->begin()
    ->isForwardedTo('default', 'error404')
  ->end()
  ->with('response')->begin()
    ->isStatusCode(404)
    ->checkElement('body', '!/congratulations/i')
  ->end();

// unexistant action
$b->
  get('/default/nonexistantaction')
  ->with('request')->begin()
    ->isForwardedTo('default', 'error404')
  ->end()
  ->with('response')->begin()
  ->isStatusCode(404)
  ->end();


// available
//sfConfig::set('sf_available', false);
//
//$b->
//  get('/')
//  ->with('request')->begin()
//    ->isForwardedTo('default', 'unavailable')
//  ->end()
//  ->with('response')->begin()
//    ->isStatusCode(200)
//    ->checkElement('body', '/unavailable/i')
//    ->checkElement('body', '!/congratulations/i')
//  ->end();
//
//sfConfig::set('sf_available', true);

// module.yml: enabled
$b->
  get('/configModuleDisabled')
  ->with('request')->begin()
    ->isForwardedTo('default', 'disabled')
  ->end()
  ->with('response')->begin()
    ->isStatusCode(200)->
    checkElement('body', '/module is unavailable/i')->
    checkElement('body', '!/congratulations/i')
    ->end();

// view.yml: has_layout
$b->
  get('/configViewHasLayout/withoutLayout')
    ->with('response')->begin()
    ->isStatusCode(200)
    ->checkElement('body', '/no layout/i')
    ->checkElement('head title', false)
  ->end();

// security.yml: is_secure
$b->
  get('/configSecurityIsSecure')
  ->with('request')->begin()
    ->isForwardedTo('default', 'login')
  ->end()
  ->with('response')->begin()
    ->isStatusCode(200)
    ->checkElement('body', '/Login Required/i')
    // check that there is no double output caused by the forwarding in a filter
    ->checkElement('body', 1)
  ->end();

// security.yml: case sensitivity
$b->
  get('/configSecurityIsSecureAction/index')
  ->with('request')->begin()
  ->isForwardedTo('default', 'login')
  ->end()
  ->with('response')->begin()
  ->isStatusCode(200)
  ->checkElement('body', '/Login Required/i')
  ->end();


$b->
  get('/configSecurityIsSecureAction/Index')
  ->with('request')->begin()
  ->isForwardedTo('default', 'login')
  ->end()
  ->with('response')->begin()
  ->isStatusCode(200)
  ->checkElement('body', '/Login Required/i')
  ->end();

// settings.yml: max_forwards
$b->
  get('/configSettingsMaxForwards/selfForward')->
    with('response')->begin()
    ->isStatusCode(500)
    ->throwsException(null, '/Too many forwards have been detected for this request/i')
  ->end();

// filters.yml: add a filter
$b->
  get('/configFiltersSimpleFilter')
  ->with('response')->begin()
    ->isStatusCode(200)
    ->checkElement('body', '/in a filter/i')
    ->checkElement('body', '!/congratulation/i')
  ->end();

// css and js inclusions
$b->
  get('/assetInclusion/index')
  ->with('response')->begin()
    ->isStatusCode(200)
    ->checkElement('head link[rel="stylesheet"]', false)
    ->checkElement('head script[type="text/javascript"]', false)
  ->end();


// libraries autoloading
$b->
  get('/autoload/index')
  ->with('response')->begin()
    ->isStatusCode(200)
    ->checkElement('#lib1', 'pong')
    ->checkElement('#lib2', 'pong')
    ->checkElement('#lib3', 'pong')
    ->checkElement('#lib4', 'nopong')
  ->end();

// libraries autoloading in a plugin
$b->
  get('/autoloadPlugin/index')->
  with('response')->begin()
  ->isStatusCode(200)
  ->checkElement('#lib1', 'pong')
  ->checkElement('#lib2', 'pong')
  ->checkElement('#lib3', 'pong')
  ->end();


// renderText
$b->
  get('/renderText')
  ->with('response')->begin()
    ->isStatusCode(200)
    ->contains('foo')
  ->end();

// view.yml when changing template
$b->
  get('/view')
  ->with('response')->begin()
  ->isStatusCode(200)
  ->isResponseHeader('Content-Type', 'text/html; charset=utf-8')
  ->checkElement('head title', 'foo title')
  ->end();

// view.yml with other than default content-type
$b->
  get('/view/plain')
  ->with('response')->begin()
    ->isStatusCode(200)
    ->isHeader('Content-Type', 'text/plain; charset=utf-8')
    ->contains('<head>')
    ->contains('plaintext')
  ->end();


// view.yml with other than default content-type and no layout
$b->
  get('/view/image')
    ->with('response')->begin()
    ->isStatusCode(200)
    ->isHeader('Content-Type', 'image/jpg')
    ->responseContains('image')
    ->end();

// view.yml with layout configured as constant asbolute path
$b->
  get('/view/absolute')
    ->with('response')->begin()
    ->isStatusCode(200)
    ->isHeader('Content-Type', 'text/html; charset=utf-8')
    ->checkElement('#admin-layout', 'Absolute path works ok!')
    ->end();

// getPresentationFor()
$b->
  get('/presentation')
    ->with('response')->begin()
    ->isStatusCode(200)
    ->checkElement('#foo1', 'foo')
    ->checkElement('#foo2', 'foo')
    ->checkElement('#foo3', 'foo')
  ->end();

// view.yml an asset package
$b->
  get('/assetPackage')
    ->with('request')
      ->begin()
        ->isParameter('module', 'assetPackage')
        ->isParameter('action', 'index')
      ->end()
    ->with('response')
      ->begin()
        ->isStatusCode(200)
        ->checkElement('head script[type="text/javascript"]', 7)
        ->checkElement('head link[rel="stylesheet"]', 2)
        ->checkElement('head script[src="/sf/js/core/i18n/cs_CZ.min.js"]', 1)
      ->end();

$b->get('/assetPackage', array('sf_culture' => 'fr_FR'))
    ->with('request')
      ->begin()
        ->isParameter('module', 'assetPackage')
        ->isParameter('action', 'index')
      ->end()
    ->with('response')
      ->begin()
        ->isStatusCode(200)
        ->responseContains('fr_FR')
        ->checkElement('head script[type="text/javascript"]', 7)
        ->checkElement('head link[rel="stylesheet"]', 2)
        ->checkElement('head script[src="/sf/js/core/i18n/fr_FR.min.js"]', 1)
      ->end();

$b->get('/browser/redirectWithAdditionalGetParameters', array('sf_culture' => 'fr_FR'))
    ->with('request')
      ->begin()
        ->isParameter('module', 'browser')
        ->isParameter('action', 'redirectWithAdditionalGetParameters')
      ->end()
    ->with('response')
      ->begin()
        ->isRedirected(true, 'http://localhost/index.php/browser/redirectTarget1?foo=bar&sf_culture=fr_FR')
        ->isStatusCode(301)
        ->responseContains('ok')
      ->end();