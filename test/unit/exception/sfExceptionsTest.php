<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(40, new lime_output_color());

foreach (array(
  'action', 'autoload', 'cache', 'configuration', 'context', 'controller', 'database', 
  'error404', 'factory', 'file', 'filter', 'forward', 'initialization', 'parse', 'render', 'security',
  'stop', 'storage', 'validator', 'view'
) as $class)
{
  $class = sprintf('sf%sException', ucfirst($class));
  $e = new $class();
  $t->is($e->getName(), $class, sprintf('"%s" exception name is "%s"', $class, $class));
  $t->is(get_parent_class($e), 'sfException', sprintf('"%s" inherits from sfException', $class));
}