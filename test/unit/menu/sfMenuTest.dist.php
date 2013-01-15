<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');

$t = new lime_test(62);

sfLoader::loadHelpers('Tag', 'Url');

class sfMenuTest extends sfMenu
{
  public function renderLink()
  {
    return sprintf('<a href="%s">%s</a>', $this->getRoute(), $this->renderLabel());
  }
}

$t->info('Menu Structure');
$t->info('   rt1     rt2 ');
$t->info('  /  \      |  ');
$t->info('ch1   ch2  ch3 ');
$t->info('            |  ');
$t->info('           gc1 ');

$menu = new sfMenuTest('Test Menu');
$root1 = $menu->getChild('Root 1');
$child1 = $root1->addChild('Child 1');
$child2 = $root1->addChild('Child 2');

$root2 = $menu->getChild('Root 2');
$child3 = $root2->addChild('Child 3');
$grandchild1 = $child3->addChild('Grandchild 1');

$t->info('1 - Test the basics of the hierarchy');

$t->is($menu->getLevel(), -1, 'Test getLevel()');
$t->is($root1->getLevel(), 0, 'Test Root 1 level is 0');
$t->is($root2->getLevel(), 0, 'Test Root 2 level is 0');
$t->is($child3->getLevel(), 1, 'Test Child 3 level is 1');
$t->is($grandchild1->getLevel(), 2, 'Test Grandchild 1 level is 2');
$t->is($grandchild1->getPathAsString(), 'Test Menu > Root 2 > Child 3 > Grandchild 1', 'Test getPathAsString() on Grandchild 1');
$t->is(get_class($root1), 'sfMenuTest', 'Test children are created as same class as parent');

// array access
$t->is($menu['Root 1']['Child 1']->getName(), 'Child 1', 'Test getName()');

// getChildren(), removeChildren()
$children = $child3->getChildren();

$t->is(count($children), 1, '->getChildren() returns 1 menu item correctly');

// $t->is($children[0]->name, $grandchild1->name, '->getChildren() returns the correct menu item');


$child3->addChild('temporary');
$t->is(count($child3->getChildren()), 2, '->getChildren() reflects the newly added child');
$child3->removeChild('temporary');
$t->is(count($child3->getChildren()), 1, '->removeChildren() removes the child when calling it by its name');

$tempChild = $child3->addChild('temporary');
$t->is(count($child3->getChildren()), 2, '->getChildren() reflects the newly added child');
$child3->removeChild($tempChild);
$t->is(count($child3->getChildren()), 1, '->removeChildren() removes the child when referencing it via the menu object');



$child3->removeChild('fake');
$t->is(count($child3->getChildren()), 1, '->removeChildren() with a non-existent child does nothing');


// countable
$t->is(count($menu), $menu->count(), 'Test sfMenu Countable interface');
$t->is(count($root1), 2, 'Test sfMenu Countable interface');

$count = 0;
foreach ($root1 as $key => $value)
{
  $count++;
  $t->is($key, 'Child '.$count, 'Test iteratable');
  $t->is($value->getLabel(), 'Child '.$count, 'Test iteratable');
}

$t->is(get_class($menu['Root 2']), 'sfMenuTest', 'Test child "Root 2" is correct class type');

$t->info('Add another child and grandchild to Root 2');
$t->info('   rt1        rt2    ');
$t->info('  /  \       /   \   ');
$t->info('ch1   ch2  ch3   ch4 ');
$t->info('            |     |  ');
$t->info('           gc1   gc2 ');

$menu['Root 2']['Child 4']['Grandchild 2'];
$t->is((string) $menu['Root 2'], '<ul><li class="first">Child 3<ul><li class="first last">Grandchild 1</li></ul></li><li class="last">Child 4<ul><li class="first last">Grandchild 2</li></ul></li></ul>', 'Test __toString()');


$t->info('2 - Test routes, authentication');

$t->info('Add a third route to check routes, authentication');
$t->info('   rt1        rt2        rt3   ');
$t->info('  /  \       /   \        |    ');
$t->info('ch1   ch2  ch3   ch4   w/route ');
$t->info('            |     |            ');
$t->info('           gc1   gc2           ');

$menu['Root 3']['With Route']->setRoute('http://www.google.com');
$t->is((string) $menu['Root 3'], '<ul><li class="first last"><a href="http://www.google.com">With Route</a></li></ul>', 'Test __toString() with a route');

$t->is($menu['Root 3']->hasChildren(), true, 'Test hasChildren() on Root 3');

$menu['Root 3']['With Route']->requiresAuth(true);
$t->is((string) $menu['Root 3'], '', 'Test requiresAuth()');
$t->is($menu['Root 3']->hasChildren(), false, 'Test hasChildren() on Root 3 when user has no access to With Route');

$user = sfContext::getInstance()->getUser();
$user->isAuthenticated(true);
$t->is($user->isAuthenticated(), true, 'Test isAuthenticated()');

//$menu['Root 3']['With Route']->setCredentials('superuser');

$t->is($menu['Root 3']['With Route']->checkUserAccess($user), true, 'Test checkUserAccess()');

$t->is((string) $menu['Root 3'], '<ul><li class="first last"><a href="http://www.google.com">With Route</a></li></ul>', 'Test authentication');
// $menu->setCredentials('superuser');
// $menu->requiresNoAuth(true);

$t->is((string) $menu, '<ul><li class="first last"><a href="http://www.google.com">With Route</a></li></ul>', 'Test requiresNoAuth()');
$t->is($menu['Root 3']['With Route']->getParent()->getLabel(), $menu['Root 3']->getLabel(), 'Test getLabel()');

$t->info('3 - Test isCurrent(), toArray() and child calls');

$t->info('Add a 4th root with child and make it current (~ for current)');
$t->info('   rt1        rt2        rt3      rt4 ');
$t->info('  /  \       /   \        |        |  ');
$t->info('ch1   ch2  ch3   ch4   w/route  ~Test ');
$t->info('            |     |                   ');
$t->info('           gc1   gc2                  ');

$menu['Root 4']['Test']->isCurrent(true);
$t->is($menu['Root 4']->toArray(), array(
  'name' => 'Root 4',
  'level' => 0,
  'is_current' => false,
  'options' => array(),
  'children' => array(
    'Test' => array(
      'name' => 'Test',
      'level' => 1,
      'is_current' => true,
      'options' => array()
    )
  )
), 'Test toArray()');

$test = new sfMenuTest('Test');
$test->fromArray($menu['Root 4']->toArray());
$t->is($test->toArray(), $menu['Root 4']->toArray(), 'Test fromArray()');
$t->is($menu['Root 4']['Test']->getPathAsString(), 'Test Menu > Root 4 > Test', 'Test getPathAsString()');
$t->is($menu->getFirstChild()->getName(), 'Root 1', 'Test getFirstChild()');
$t->is($menu->getLastChild()->getName(), 'Root 4', 'Test getLastChild()');


$t->info('4 - Test some positional functions');
$t->info('     root1     ');
$t->info('    /  |  \    ');
$t->info('first mid last ');
$menu = new sfMenuTest('Test Menu');
$root1 = $menu->getChild('Root 1');
$first = $root1->addChild('Child 1');
$middle = $root1->addChild('Child 2');
$last = $root1->addChild('Child 3');

$t->is($first->isFirst(), true, 'Test isFirst()');
$t->is($last->isLast(), true, 'Test isLast()');
$t->is($middle->isFirst(), false, 'Test isFirst()');
$t->is($middle->isLast(), false, 'Test isLast()');
$t->is($first->getNum(), 1, 'Test getNum()');
$t->is($middle->getNum(), 2, 'Test getNum()');
$t->is($last->getNum(), 3, 'Test getNum()');
