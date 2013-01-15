<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(1, new lime_output_color());

$t->diag('->filterByEventListeners()');

$var = true;

function listenToEvent(sfEvent $event, $var)
{
  $var = false;
  return $var;
}

sfCore::getEventDispatcher()->connect('test', 'listenToEvent');

$filtered  = sfCore::filterByEventListeners($var, 'test');

$t->is($filtered, false, 'returns correct result');
