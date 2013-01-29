<?php

include(dirname(__FILE__).'/../../bootstrap/functional.php');

$browser = new sfTestFunctional();

$browser->
  get('/##MODULE_NAME##/index')->

  with('request')->begin()->
    isParameter('module', '##MODULE_NAME##')->
    isParameter('action', 'index')->
  end()->

  with('response')->begin()->
    isStatusCode(200)->
    checkElement('body', '!/##MODULE_NAME## created/')->
  end();
