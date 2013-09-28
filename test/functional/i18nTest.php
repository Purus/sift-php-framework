<?php

$app = 'i18n';
if (!include(dirname(__FILE__).'/../bootstrap/functional.php'))
{
  return;
}

sfToolkit::clearDirectory(sfConfig::get('sf_root_cache_dir'));

$b = new sfTestBrowser();

// default culture (cs_CZ)
$b->
  get('/')
  ->with('response')->begin()
  ->isStatusCode(200)
  ->checkElement('#action', '/an english sentence/i')
  ->checkElement('#template', '/an english sentence/i')
  ->end()
  ->with('request')->begin()
    ->isParameter('module', 'i18n')
    ->isParameter('action', 'index')
  ->end()
  ->with('user')->begin()
    ->isCulture('cs_CZ')
  ->end();

$b->
  get('/fr_FR/i18n/index')
  ->with('request')->begin()
    ->isParameter('module', 'i18n')
    ->isParameter('action', 'index')
    ->isParameter('sf_culture', 'fr_FR')
  ->end()
    ->with('user')->begin()
      ->isCulture('fr_FR')
  ->end()
  ->with('response')->begin()
  ->isStatusCode(200)->
  // messages in the global directories
  checkElement('#action', '/une phrase en français/i')->
  checkElement('#template', '/une phrase en français/i')->
  // messages in the module directories
  checkElement('#action_local', '/une phrase locale en français/i')->
  checkElement('#template_local', '/une phrase locale en français/i')->

  // messages in another global catalogue
  checkElement('#action_other', '/une autre phrase en français/i')->
  checkElement('#template_other', '/une autre phrase en français/i')->

  // messages in another module catalogue
  checkElement('#action_other_local', '/une autre phrase locale en français/i')->
  checkElement('#template_other_local', '/une autre phrase locale en français/i')
  ->end();

// messages for a module plugin
$b->
  get('/fr_FR/sfI18NPlugin/index')
  ->with('response')->begin()->
    isStatusCode(200)->
    checkElement('#action', '/une phrase en français/i')->
    checkElement('#template', '/une phrase en français/i')->
    checkElement('#action_local', '/une phrase locale en français/i')->
    checkElement('#template_local', '/une phrase locale en français/i')
  ->end()
  ->with('request')->begin()->
    isParameter('module', 'sfI18NPlugin')->
    isParameter('action', 'index')
  ->end()
  ->with('user')->begin()->
    isCulture('fr_FR')->
  end();

// sfToolkit::clearDirectory(sfConfig::get('sf_root_cache_dir'));