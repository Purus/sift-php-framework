<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(28);

foreach (array(
  'cache', 'configuration', 'controller', 'database',
  'error404', 'factory', 'file', 'forward', 'initialization', 'parse', 'render', 'security',
  'stop', 'storage', 'view', 'webbrowserinvalidresponse',
  'phpError', 'lesscompiler', 'calendar', 'imagetransform', 'datetime', 'httpdownload',
  'plugindependency', 'plugin', 'pluginrecursivedependency', 'pluginrest',
  'clicommandarguments', 'clicommand'
) as $class)
{
  $class = sprintf('sf%sException', ucfirst($class));
  $e = new $class();
  $t->ok($e instanceof sfException, sprintf('"%s" inherits from sfException', $class));
}
