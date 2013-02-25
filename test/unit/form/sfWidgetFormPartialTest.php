<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfCoreMock.class.php');

$t = new lime_test(6);

try 
{
  $widget = new sfWidgetFormPartial();
  $t->fail('Widget requires "partial" option');
}
catch(RuntimeException $e)
{
  $t->pass('Widget requires "partial" option');
}

function get_partial($name, $vars = array(), $viewName = null)
{
  $v = array();
  foreach($vars as $var => $value)
  {
    $v[] = sprintf('%s=%s', $var, $value);
  }
  return sprintf('RENDERED PARTIAL %s (%s)%s', $name, join(", ", $v), $viewName);
}

$widget = new sfWidgetFormPartial(array('partial' => 'myPartial'));

$t->isa_ok($widget->render('test'), 'string', 'widget renders the partial');

$t->is($widget->render('test'), 'RENDERED PARTIAL myPartial (name=test, value=, errors=Array)', 'widget renders the partial');

$widget = new sfWidgetFormPartial('myPartial', array('a' => 'b'));

$t->isa_ok($widget->render('test'), 'string', 'widget renders the partial');

$t->is($widget->render('test'), 'RENDERED PARTIAL myPartial (a=b, name=test, value=, errors=Array)', 'widget renders the partial');

$widget = new sfWidgetFormPartial(array('partial' => 'myPartial', 'view_name' => 'myPHP'), array('a' => 'b'));

$t->is($widget->render('test'), 'RENDERED PARTIAL myPartial (a=b, name=test, value=, errors=Array)myPHP', 'widget renders the partial using custom view_name');
