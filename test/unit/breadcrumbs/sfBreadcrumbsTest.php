<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');

$context = new sfContext();

$t = new lime_test(8);

$b = sfBreadcrumbs::getInstance();

$t->isa_ok($b, 'sfBreadcrumbs', 'getInstance() returns correct object.');

// call()

$t->diag('dropCrumb()');
$t->diag('->getCrumbs()');
$t->isa_ok($b->getCrumbs(), 'array', 'getCrumbs() returns array.');

$result = array();
$result[] = array(
  'name' => 'Home',
  'url' => '@homepage',
  'options' => array(),
);

$t->is_deeply($b->getCrumbs(), $result, 'getCrumbs() returns array.');

$t->is_deeply($b->getCrumbs(false), array(), 'getCrumbs() returns array without home.');

$t->diag('drop()');

$r = $b->drop('foobar', 'http://foobar.foo');

$t->isa_ok($r, 'sfBreadcrumbs', 'drop() provides fluent interface.');

$t->is_deeply($b->getCrumbs(false), array(array(
    'name' => 'foobar',
    'url'   => 'http://foobar.foo',
    'options' => array()    
)), 'drop() drops a crumb.');

$t->diag('clear()');

$b->clear();

$t->is_deeply($b->getCrumbs(false), array(), 'clear() clears all crumbs.');

$dispatcher = sfCore::getEventDispatcher();

$dispatcher->connect('breadcrumbs.get_crumbs', array(
    'myBreadcrumbsEventDispatcherTest', 
    'listen'));

$r = $b->drop('foobar', 'http://foobar.foo');

$t->is_deeply($b->getCrumbs(), array(
array(
  'name' => 'Home',
  'url' => '@homepage',
  'options' => array(), 
),    
array(
    'name' => 'foobar',
    'url'   => 'http://foobar.foo',
    'options' => array()    
),    
array(
      'name' => 'EVENT',
      'url' => '/EVENT',
      'options' => array()          
    )     
   
), 'event system works ok.');

class myBreadcrumbsEventDispatcherTest
{
  static public function listen(sfEvent $event, $value)
  {
    $value[] = array(
      'name' => 'EVENT',
      'url' => '/EVENT',
      'options' => array()          
    );
    return $value;
  }
}
