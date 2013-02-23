<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfCoreMock.class.php');

$t = new lime_test(7);

try 
{
  $widget = new sfWidgetFormComponent();
  $t->fail('Widget requires "component" option');
}
catch(RuntimeException $e)
{
  $t->pass('Widget requires "component" option');
}

function get_component($module, $name, $vars = array(), $viewName = null)
{
  $v = array();
  foreach($vars as $var => $value)
  {
    $v[] = sprintf('%s=%s', $var, $value);
  }
  return sprintf('RENDERED COMPONENT %s/%s (%s)%s', $module, $name, join(", ", $v), $viewName);
}

$widget = new sfWidgetFormComponent(array('component' => 'myTestModule/component'));

$t->isa_ok($widget->render('test'), 'string', 'widget renders the component');

$t->is($widget->render('test'), 'RENDERED COMPONENT myTestModule/component (name=test, value=, errors=Array)', 'widget renders the component');

$widget = new sfWidgetFormComponent('myTestModule/component', array('a' => 'b'));

$t->isa_ok($widget->render('test'), 'string', 'widget renders the component');

$t->is($widget->render('test'), 'RENDERED COMPONENT myTestModule/component (a=b, name=test, value=, errors=Array)', 'widget renders the component');

// short option
$widget = new sfWidgetFormComponent(array('component' => 'myTestModule/component', 'view_name' => 'myPHP'), array('a' => 'b'));
$t->is($widget->render('test'), 'RENDERED COMPONENT myTestModule/component (a=b, name=test, value=, errors=Array)myPHP', 'widget renders the component using custom view_name');

// array option
$widget = new sfWidgetFormComponent(array('component' => array('myTestModule', 'component'), 'view_name' => 'myPHP'), array('a' => 'b'));
$t->is($widget->render('test'), 'RENDERED COMPONENT myTestModule/component (a=b, name=test, value=, errors=Array)myPHP', 'widget renders the component using custom view_name');
